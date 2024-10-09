/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module link/ui/linkactionsview
 */
import { ButtonView, View, ViewCollection, FocusCycler } from 'ckeditor5/src/ui.js'
import { FocusTracker, KeystrokeHandler } from 'ckeditor5/src/utils.js'
import { icons } from 'ckeditor5/src/core.js'
import '@ckeditor/ckeditor5-ui/theme/components/responsive-form/responsiveform.css'
import ajaxRequest from '../../../../components/ajaxRequest'
import '../../theme/linkactions.css'
import unlinkIcon from '../../theme/icons/unlink.svg'
import Link from '../../../link'
/**
 * The link actions view class. This view displays the link preview, allows
 * unlinking or editing the link.
 */
export default class LinkActionsView extends View {
  /**
   * @inheritDoc
   */
  constructor(locale) {
    super(locale)
    /**
     * Tracks information about DOM focus in the actions.
     */
    this.focusTracker = new FocusTracker()
    /**
     * An instance of the {@link module:utils/keystrokehandler~KeystrokeHandler}.
     */
    this.keystrokes = new KeystrokeHandler()
    /**
     * A collection of views that can be focused in the view.
     */
    this._focusables = new ViewCollection()
    const t = locale.t
    this.previewButtonView = this._createPreviewButton()
    this.unlinkButtonView = this._createButton(t('Unlink'), unlinkIcon, 'unlink')
    this.editButtonView = this._createButton(t('Edit link'), icons.pencil, 'edit')
    this.set('href', undefined)
    this._focusCycler = new FocusCycler({
      focusables: this._focusables,
      focusTracker: this.focusTracker,
      keystrokeHandler: this.keystrokes,
      actions: {
        // Navigate fields backwards using the Shift + Tab keystroke.
        focusPrevious: 'shift + tab',
        // Navigate fields forwards using the Tab key.
        focusNext: 'tab'
      }
    })
    this.setTemplate({
      tag: 'div',
      attributes: {
        class: ['ck', 'ck-link-actions', 'ck-responsive-form'],
        // https://github.com/ckeditor/ckeditor5-link/issues/90
        tabindex: '-1'
      },
      children: [this.previewButtonView, this.editButtonView, this.unlinkButtonView]
    })
  }

  /**
   * @inheritDoc
   */
  render() {
    super.render()
    const childViews = [this.previewButtonView, this.editButtonView, this.unlinkButtonView]
    childViews.forEach((v) => {
      // Register the view as focusable.
      this._focusables.add(v)
      // Register the view in the focus tracker.
      this.focusTracker.add(v.element)
    })
    // Start listening for the keystrokes coming from #element.
    this.keystrokes.listenTo(this.element)
  }

  /**
   * @inheritDoc
   */
  destroy() {
    super.destroy()
    this.focusTracker.destroy()
    this.keystrokes.destroy()
  }

  /**
   * Focuses the fist {@link #_focusables} in the actions.
   */
  focus() {
    this._focusCycler.focusFirst()
  }

  /**
   * Creates a button view.
   *
   * @param label The button label.
   * @param icon The button icon.
   * @param eventName An event name that the `ButtonView#execute` event will be delegated to.
   * @returns The button view instance.
   */
  _createButton(label, icon, eventName) {
    const button = new ButtonView(this.locale)
    button.set({
      label,
      icon,
      tooltip: true
    })
    button.delegate('execute').to(this, eventName)
    return button
  }

  /**
   * Creates a link href preview button.
   *
   * @returns The button view instance.
   */
  _createPreviewButton() {
    const self = this
    const button = new ButtonView(this.locale)
    const bind = this.bindTemplate
    const t = this.t
    button.set({
      withText: true,
      tooltip: t('Open link in new tab')
    })
    button.extendTemplate({
      attributes: {
        class: ['ck', 'ck-link-actions__preview'],
        href: bind.to('href', (href) => self._getEmsUrl(href)),
        target: bind.to('href', (href) => (!href || href.startsWith('#') ? '' : '_blank')),
        rel: 'noopener noreferrer'
      }
    })
    button.bind('label').to(this, 'href', (href) => self._getEmsLabel(href))
    button.bind('isEnabled').to(this, 'href', (href) => !!href)
    button.template.tag = 'a'
    button.template.eventListeners = {}
    return button
  }

  _getEmsLabel(href) {
    const emsLink = new Link(href)
    const t = this.t
    const self = this
    if (emsLink.isEmsLink()) {
      switch (emsLink.linkType) {
        case 'object': {
          ajaxRequest
            .get(document.body.dataset.emsLinkInfo, { link: href })
            .success((response) => {
              self.previewButtonView.label = response.label
            })
            .fail(() => {
              self.previewButtonView.label = t('Label not found')
            })

          return t('Label loading...')
        }
        case 'asset':
          return emsLink.name
      }
    }
    return href || t('This link has no URL')
  }

  _getEmsUrl(href) {
    const emsLink = new Link(href)
    return emsLink.getUrl()
  }
}
