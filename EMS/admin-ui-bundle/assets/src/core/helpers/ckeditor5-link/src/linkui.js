/**
 * @license Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
/**
 * @module link/linkui
 */
import { Plugin } from 'ckeditor5/src/core.js'
import { ClickObserver } from 'ckeditor5/src/engine.js'
import { ButtonView, ContextualBalloon } from 'ckeditor5/src/ui.js'
import { isWidget } from 'ckeditor5/src/widget.js'
import LinkActionsView from './ui/linkactionsview.js'
import { EMS_SELECT_LINK_EVENT } from '../../../events/selectLinkEvent'
import { isLinkElement, LINK_KEYSTROKE } from './utils.js'
import linkIcon from '../theme/icons/link.svg'
import CkeModal from '../../ckeModal'
import Link from '../../link'
const VISUAL_SELECTION_MARKER_NAME = 'link-ui'
/**
 * The link UI plugin. It introduces the `'link'` and `'unlink'` buttons and support for the <kbd>Ctrl+K</kbd> keystroke.
 *
 * It uses the
 * {@link module:ui/panel/balloon/contextualballoon~ContextualBalloon contextual balloon plugin}.
 */
export default class LinkUI extends Plugin {
  constructor() {
    super(...arguments)
    this.actionsView = null
    this.formView = null
    this.formModal = null
  }

  /**
   * @inheritDoc
   */
  static get requires() {
    return [ContextualBalloon]
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'LinkUI'
  }

  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor
    editor.editing.view.addObserver(ClickObserver)
    this._balloon = editor.plugins.get(ContextualBalloon)
    // Create toolbar buttons.
    this._createToolbarLinkButton()
    this._enableBalloonActivators()
    // Renders a fake visual selection marker on an expanded selection.
    editor.conversion.for('editingDowncast').markerToHighlight({
      model: VISUAL_SELECTION_MARKER_NAME,
      view: {
        classes: ['ck-fake-link-selection']
      }
    })
    // Renders a fake visual selection marker on a collapsed selection.
    editor.conversion.for('editingDowncast').markerToElement({
      model: VISUAL_SELECTION_MARKER_NAME,
      view: {
        name: 'span',
        classes: ['ck-fake-link-selection', 'ck-fake-link-selection_collapsed']
      }
    })
  }

  /**
   * @inheritDoc
   */
  destroy() {
    super.destroy()
    if (this.actionsView) {
      this.actionsView.destroy()
    }
  }

  /**
   * Creates views.
   */
  _createViews() {
    this.actionsView = this._createActionsView()
    this._createFormModal()
  }

  _createModal() {
    const t = this.editor.t
    this.formModal = new CkeModal('initLink', t('Insert a link'))
  }

  /**
   * Creates the {@link module:link/ui/linkactionsview~LinkActionsView} instance.
   */
  _createActionsView() {
    const editor = this.editor
    const actionsView = new LinkActionsView(editor.locale)
    const linkCommand = editor.commands.get('link')
    const unlinkCommand = editor.commands.get('unlink')
    actionsView.bind('href').to(linkCommand, 'value')
    actionsView.editButtonView.bind('isEnabled').to(linkCommand)
    actionsView.unlinkButtonView.bind('isEnabled').to(unlinkCommand)
    // Execute unlink command after clicking on the "Edit" button.
    this.listenTo(actionsView, 'edit', () => {
      this._addFormModal()
    })
    // Execute unlink command after clicking on the "Unlink" button.
    this.listenTo(actionsView, 'unlink', () => {
      editor.execute('unlink')
      this._hideUI()
    })
    // Close the panel on esc key press when the **actions have focus**.
    actionsView.keystrokes.set('Esc', (data, cancel) => {
      this._hideUI()
      cancel()
    })
    // Open the form view on Ctrl+K when the **actions have focus**..
    actionsView.keystrokes.set(LINK_KEYSTROKE, (data, cancel) => {
      this._addFormModal()
      cancel()
    })
    return actionsView
  }

  _createFormModal() {
    const editor = this.editor
    document.addEventListener(EMS_SELECT_LINK_EVENT, (event) => {
      editor.execute('link', event.detail.href, {}, event.detail.target)
      this._hideUI()
    })
  }

  /**
   * Creates a toolbar Link button. Clicking this button will show
   * a {@link #_balloon} attached to the selection.
   */
  _createToolbarLinkButton() {
    const editor = this.editor
    const linkCommand = editor.commands.get('link')
    const t = editor.t
    editor.ui.componentFactory.add('link', (locale) => {
      const button = new ButtonView(locale)
      button.isEnabled = true
      button.label = t('Link')
      button.icon = linkIcon
      button.keystroke = LINK_KEYSTROKE
      button.tooltip = true
      button.isToggleable = true
      // Bind button to the command.
      button.bind('isEnabled').to(linkCommand, 'isEnabled')
      button.bind('isOn').to(linkCommand, 'value', (value) => !!value)
      // Show the panel on button click.
      this.listenTo(button, 'execute', () => this._showUI(true))
      return button
    })
  }

  /**
   * Attaches actions that control whether the balloon panel containing the
   * {@link #formView} should be displayed.
   */
  _enableBalloonActivators() {
    const editor = this.editor
    const viewDocument = editor.editing.view.document
    // Handle click on view document and show panel when selection is placed inside the link element.
    // Keep panel open until selection will be inside the same link element.
    this.listenTo(viewDocument, 'click', () => {
      const parentLink = this._getSelectedLinkElement()
      if (parentLink) {
        // Then show panel but keep focus inside editor editable.
        this._showUI()
      }
    })
    // Handle the `Ctrl+K` keystroke and show the panel.
    editor.keystrokes.set(LINK_KEYSTROKE, (keyEvtData, cancel) => {
      // Prevent focusing the search bar in FF, Chrome and Edge. See https://github.com/ckeditor/ckeditor5/issues/4811.
      cancel()
      if (editor.commands.get('link').isEnabled) {
        this._showUI(true)
      }
    })
  }

  /**
   * Adds the {@link #actionsView} to the {@link #_balloon}.
   *
   * @internal
   */
  _addActionsView() {
    if (!this.actionsView) {
      this._createViews()
    }
    if (this._areActionsInPanel) {
      return
    }
    this._balloon.add({
      view: this.actionsView,
      position: this._getBalloonPositionData()
    })
  }

  _addFormModal() {
    if (!this.formModal) {
      this._createModal()
    }
    const editor = this.editor
    const linkCommand = editor.commands.get('link')
    const value = linkCommand.value || ''
    const target = undefined !== linkCommand.target ? linkCommand.target : null
    const link = new Link(value)
    this.formModal.show({
      url: link.href,
      target,
      content: editor.getData()
    })
  }

  /**
   * Shows the correct UI type. It is either {@link #formView} or {@link #actionsView}.
   *
   * @internal
   */
  _showUI(forceVisible = false) {
    if (!this.formModal) {
      this._createModal()
    }
    // When there's no link under the selection, go straight to the editing UI.
    if (!this._getSelectedLinkElement()) {
      // Show visual selection on a text without a link when the contextual balloon is displayed.
      // See https://github.com/ckeditor/ckeditor5/issues/4721.
      this._showFakeVisualSelection()
      this._addActionsView()
      // Be sure panel with link is visible.
      if (forceVisible) {
        this._balloon.showStack('main')
      }
      this._addFormModal()

      // If there's a link under the selection...
    } else {
      // Go to the editing UI if actions are already visible.
      if (this._areActionsVisible) {
        this._addFormModal()

        // Otherwise display just the actions UI.
      } else {
        this._addActionsView()
      }
      // Be sure panel with link is visible.
      if (forceVisible) {
        this._balloon.showStack('main')
      }
    }
    // Begin responding to ui#update once the UI is added.
    this._startUpdatingUI()
  }

  _hideUI() {
    if (!this._isUIInPanel) {
      return
    }
    if (this.formModal.isVisible()) {
      return
    }
    const editor = this.editor
    this.stopListening(editor.ui, 'update')
    this.stopListening(this._balloon, 'change:visibleView')
    // Make sure the focus always gets back to the editable _before_ removing the focused form view.
    // Doing otherwise causes issues in some browsers. See https://github.com/ckeditor/ckeditor5-link/issues/193.
    editor.editing.view.focus()
    // Then remove the actions view because it's beneath the form.
    this._balloon.remove(this.actionsView)
    this._hideFakeVisualSelection()
  }

  /**
   * Makes the UI react to the {@link module:ui/editorui/editorui~EditorUI#event:update} event to
   * reposition itself when the editor UI should be refreshed.
   *
   * See: {@link #_hideUI} to learn when the UI stops reacting to the `update` event.
   */
  _startUpdatingUI() {
    const editor = this.editor
    const viewDocument = editor.editing.view.document
    let prevSelectedLink = this._getSelectedLinkElement()
    let prevSelectionParent = getSelectionParent()
    const update = () => {
      const selectedLink = this._getSelectedLinkElement()
      const selectionParent = getSelectionParent()
      // Hide the panel if:
      //
      // * the selection went out of the EXISTING link element. E.g. user moved the caret out
      //   of the link,
      // * the selection went to a different parent when creating a NEW link. E.g. someone
      //   else modified the document.
      // * the selection has expanded (e.g. displaying link actions then pressing SHIFT+Right arrow).
      //
      // Note: #_getSelectedLinkElement will return a link for a non-collapsed selection only
      // when fully selected.
      if (
        (prevSelectedLink && !selectedLink) ||
        (!prevSelectedLink && selectionParent !== prevSelectionParent)
      ) {
        this._hideUI()

        // Update the position of the panel when:
        //  * link panel is in the visible stack
        //  * the selection remains in the original link element,
        //  * there was no link element in the first place, i.e. creating a new link
      } else if (this._isUIVisible) {
        // If still in a link element, simply update the position of the balloon.
        // If there was no link (e.g. inserting one), the balloon must be moved
        // to the new position in the editing view (a new native DOM range).
        this._balloon.updatePosition(this._getBalloonPositionData())
      }
      prevSelectedLink = selectedLink
      prevSelectionParent = selectionParent
    }
    function getSelectionParent() {
      return viewDocument.selection.focus
        .getAncestors()
        .reverse()
        .find((node) => node.is('element'))
    }
    this.listenTo(editor.ui, 'update', update)
    this.listenTo(this._balloon, 'change:visibleView', update)
  }

  /**
   * Returns `true` when {@link #actionsView} is in the {@link #_balloon}.
   */
  get _areActionsInPanel() {
    return !!this.actionsView && this._balloon.hasView(this.actionsView)
  }

  /**
   * Returns `true` when {@link #actionsView} is in the {@link #_balloon} and it is
   * currently visible.
   */
  get _areActionsVisible() {
    return !!this.actionsView && this._balloon.visibleView === this.actionsView
  }

  /**
   * Returns `true` when {@link #actionsView} or {@link #formView} is in the {@link #_balloon}.
   */
  get _isUIInPanel() {
    return this._areActionsInPanel
  }

  /**
   * Returns `true` when {@link #actionsView} or {@link #formView} is in the {@link #_balloon} and it is
   * currently visible.
   */
  get _isUIVisible() {
    return this._areActionsVisible
  }

  /**
   * Returns positioning options for the {@link #_balloon}. They control the way the balloon is attached
   * to the target element or selection.
   *
   * If the selection is collapsed and inside a link element, the panel will be attached to the
   * entire link element. Otherwise, it will be attached to the selection.
   */
  _getBalloonPositionData() {
    const view = this.editor.editing.view
    const model = this.editor.model
    const viewDocument = view.document
    let target
    if (model.markers.has(VISUAL_SELECTION_MARKER_NAME)) {
      // There are cases when we highlight selection using a marker (#7705, #4721).
      const markerViewElements = Array.from(
        this.editor.editing.mapper.markerNameToElements(VISUAL_SELECTION_MARKER_NAME)
      )
      const newRange = view.createRange(
        view.createPositionBefore(markerViewElements[0]),
        view.createPositionAfter(markerViewElements[markerViewElements.length - 1])
      )
      target = view.domConverter.viewRangeToDom(newRange)
    } else {
      // Make sure the target is calculated on demand at the last moment because a cached DOM range
      // (which is very fragile) can desynchronize with the state of the editing view if there was
      // any rendering done in the meantime. This can happen, for instance, when an inline widget
      // gets unlinked.
      target = () => {
        const targetLink = this._getSelectedLinkElement()
        return targetLink
          ? // When selection is inside link element, then attach panel to this element.
            view.domConverter.mapViewToDom(targetLink)
          : // Otherwise attach panel to the selection.
            view.domConverter.viewRangeToDom(viewDocument.selection.getFirstRange())
      }
    }
    return { target }
  }

  /**
   * Returns the link {@link module:engine/view/attributeelement~AttributeElement} under
   * the {@link module:engine/view/document~Document editing view's} selection or `null`
   * if there is none.
   *
   * **Note**: For a non–collapsed selection, the link element is returned when **fully**
   * selected and the **only** element within the selection boundaries, or when
   * a linked widget is selected.
   */
  _getSelectedLinkElement() {
    const view = this.editor.editing.view
    const selection = view.document.selection
    const selectedElement = selection.getSelectedElement()
    // The selection is collapsed or some widget is selected (especially inline widget).
    if (selection.isCollapsed || (selectedElement && isWidget(selectedElement))) {
      return findLinkElementAncestor(selection.getFirstPosition())
    } else {
      // The range for fully selected link is usually anchored in adjacent text nodes.
      // Trim it to get closer to the actual link element.
      const range = selection.getFirstRange().getTrimmed()
      const startLink = findLinkElementAncestor(range.start)
      const endLink = findLinkElementAncestor(range.end)
      if (!startLink || startLink !== endLink) {
        return null
      }
      // Check if the link element is fully selected.
      if (view.createRangeIn(startLink).getTrimmed().isEqual(range)) {
        return startLink
      } else {
        return null
      }
    }
  }

  /**
   * Displays a fake visual selection when the contextual balloon is displayed.
   *
   * This adds a 'link-ui' marker into the document that is rendered as a highlight on selected text fragment.
   */
  _showFakeVisualSelection() {
    const model = this.editor.model
    model.change((writer) => {
      const range = model.document.selection.getFirstRange()
      if (model.markers.has(VISUAL_SELECTION_MARKER_NAME)) {
        writer.updateMarker(VISUAL_SELECTION_MARKER_NAME, { range })
      } else {
        if (range.start.isAtEnd) {
          const startPosition = range.start.getLastMatchingPosition(
            ({ item }) => !model.schema.isContent(item),
            { boundaries: range }
          )
          writer.addMarker(VISUAL_SELECTION_MARKER_NAME, {
            usingOperation: false,
            affectsData: false,
            range: writer.createRange(startPosition, range.end)
          })
        } else {
          writer.addMarker(VISUAL_SELECTION_MARKER_NAME, {
            usingOperation: false,
            affectsData: false,
            range
          })
        }
      }
    })
  }

  /**
   * Hides the fake visual selection created in {@link #_showFakeVisualSelection}.
   */
  _hideFakeVisualSelection() {
    const model = this.editor.model
    if (model.markers.has(VISUAL_SELECTION_MARKER_NAME)) {
      model.change((writer) => {
        writer.removeMarker(VISUAL_SELECTION_MARKER_NAME)
      })
    }
  }
}
/**
 * Returns a link element if there's one among the ancestors of the provided `Position`.
 *
 * @param View position to analyze.
 * @returns Link element at the position or null.
 */
function findLinkElementAncestor(position) {
  return position.getAncestors().find((ancestor) => isLinkElement(ancestor)) || null
}
