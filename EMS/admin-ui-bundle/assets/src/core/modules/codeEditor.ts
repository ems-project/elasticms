import type { Ace } from 'ace-builds'
import type { EmsModule } from '../types/emsModule'
import { ChangeEvent } from '../events/ChangeEvent'

type AceModule = typeof import('ace-builds')

const SELECTOR = '.ems-code-editor'
const DEFAULT_MAX_LINES = 15

export default class CodeEditor implements EmsModule {
    public async load(target: Document | HTMLElement): Promise<void> {
        const containers = target.querySelectorAll<HTMLElement>(SELECTOR)

        if (containers.length === 0) {
            return
        }

        const ace = await import('ace-builds')
        await import('ace-builds/esm-resolver')

        containers.forEach((container) => this.init(ace, container))
    }

    private init(ace: AceModule, container: HTMLElement): void {
        const pre = container.tagName === 'DIV' ? container.querySelector<HTMLElement>('pre') : container

        if (!pre) {
            console.warn('PRE tag not found')
            return
        }

        if (pre.classList.contains('ace_editor')) {
            return
        }

        const input = container.querySelector('input')
        const maxLines = Number.parseInt(container.dataset.maxLines ?? `${DEFAULT_MAX_LINES}`)
        const minLines = container.dataset.minLines

        const editor = ace.edit(pre, {
                mode: container.dataset.language ?? 'ace/mode/twig',
                theme: container.dataset.theme ?? 'ace/theme/chrome',
                readOnly: input === null,
                maxLines,
                ...(minLines ? { minLines: Number.parseInt(minLines) } : {}),
                useWorker: false,
            })

        ;(pre as any)._aceEditor = editor

        if (input) {
            editor.on('change', () => {
                input.value = editor.getValue()
                new ChangeEvent(input).dispatch()
            })
        }

        editor.commands.addCommands([
            {
                name: 'fullscreen',
                bindKey: { win: 'F11', mac: 'Esc' },
                exec: (editor: Ace.Editor) => this.toggleFullscreen(editor, pre, maxLines),
            },
            {
                name: 'showKeyboardShortcuts',
                bindKey: { win: 'Ctrl-Alt-h', mac: 'Command-Alt-h' },
                exec: (editor: Ace.Editor) => {
                    ace.config.loadModule('ace/ext/keybinding_menu', (module) => {
                        module.init(editor)
                        ;(editor as any).showKeyboardShortcuts()
                    })
                },
            },
        ])
    }

    private toggleFullscreen(editor: Ace.Editor, pre: HTMLElement, maxLines: number): void {
        const fullscreen = pre.classList.toggle('panel-fullscreen')

        editor.setOption('maxLines', fullscreen ? Infinity : maxLines)
        editor.setAutoScrollEditorIntoView(fullscreen)
        editor.resize()
    }
}