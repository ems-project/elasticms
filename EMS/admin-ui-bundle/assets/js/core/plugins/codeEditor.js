import $ from 'jquery'
import ace from 'ace-builds/src-noconflict/ace'
import 'ace-builds/webpack-resolver'
export default class CodeEditor {
  load (target) {
    this.loadEditors(target)
    this.loadAceThemePickers(target)
    this.loadAceModePickers(target)
  }

  loadEditors (target) {
    const self = this
    const codeEditors = target.getElementsByClassName('ems-code-editor')
    for (let i = 0; i < codeEditors.length; i++) {
      const codeDiv = $(codeEditors[i])
      let pre = codeEditors[i]
      let hiddenField = codeDiv
      let disabled = true

      if (pre.tagName === 'DIV') {
        pre = codeDiv.find('pre').get(0)
        hiddenField = codeDiv.find('input')
        disabled = hiddenField.data('disabled')
      }

      let language = hiddenField.data('language')
      language = language || 'ace/mode/twig'

      let theme = hiddenField.data('theme')
      theme = theme || 'ace/theme/chrome'

      let maxLines = 15
      if (hiddenField.data('max-lines') && hiddenField.data('max-lines') > 0) {
        maxLines = hiddenField.data('max-lines')
      }

      let minLines = 1
      if (hiddenField.data('min-lines') && hiddenField.data('min-lines') > 0) {
        minLines = hiddenField.data('min-lines')
      }

      const editor = ace.edit(pre, {
        mode: language,
        readOnly: disabled,
        maxLines,
        minLines,
        theme
      })

      editor.on('change', function (e) {
        hiddenField.val(editor.getValue())
        if (typeof self.onChangeCallback === 'function') {
          self.onChangeCallback()
        }
      })

      editor.commands.addCommands([{
        name: 'fullscreen',
        bindKey: { win: 'F11', mac: 'Esc' },
        exec: function (editor) {
          if (codeDiv.hasClass('panel-fullscreen')) {
            editor.setOption('maxLines', maxLines)
            codeDiv.removeClass('panel-fullscreen')
            editor.setAutoScrollEditorIntoView(false)
          } else {
            editor.setOption('maxLines', Infinity)
            codeDiv.addClass('panel-fullscreen')
            editor.setAutoScrollEditorIntoView(true)
          }

          editor.resize()
        }
      }, {
        name: 'showKeyboardShortcuts',
        bindKey: { win: 'Ctrl-Alt-h', mac: 'Command-Alt-h' },
        exec: function (editor) {
          self.getAceConfig().loadModule('ace/ext/keybinding_menu', function (module) {
            module.init(editor)
            editor.showKeyboardShortcuts()
          })
        }
      }])
    }
  }

  static getAceConfig () {
    if (!this.aceConfig) {
      this.aceConfig = ace.require('ace/config')
      this.aceConfig.init()
    }
    console.log(this.aceConfig)
    return this.aceConfig
  }

  getModules (startingWith) {
    const filteredModule = []
    const modules = ace.config.all().$moduleUrls
    for (const [key] of Object.entries(modules)) {
      if (!key.startsWith(startingWith)) {
        continue
      }
      let caption = key.substring(startingWith.length).replaceAll('_', ' ')
      caption = caption.charAt(0).toUpperCase() + caption.slice(1)
      filteredModule.push({
        id: key,
        text: caption
      })
    }
    return filteredModule
  }

  loadAceThemePickers (target) {
    const codeEditorThemeField = $(target).find('.code_editor_theme_ems')
    if (codeEditorThemeField.length === 0) {
      return
    }
    const modes = this.getModules('ace/theme/')
    codeEditorThemeField.select2({
      data: modes,
      placeholder: 'Select a theme'
    })
  }

  loadAceModePickers (target) {
    const codeEditorModeField = $(target).find('.code_editor_mode_ems')
    if (codeEditorModeField.length === 0) {
      return
    }
    const modes = this.getModules('ace/mode/')
    codeEditorModeField.select2({
      data: modes,
      placeholder: 'Select a language'
    })
  }
}
