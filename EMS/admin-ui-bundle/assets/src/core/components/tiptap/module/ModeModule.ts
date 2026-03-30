import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconMaximize from '@tabler/icons/outline/maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/minimize.svg?raw'
import { TiptapModule } from '../types.ts'

export const ModeModule: TiptapModule = {
    name: 'mode',
    extensions: [],
    groups: {
        mode: ['source', 'maximize']
    },
    actions: {
        source: {
            icon: IconSource,
            tooltip: 'Source Code',
            command: (e) => {
                const textarea = e.textarea
                if (null === textarea) return

                const container = e.toolbar.element.parentElement
                if (!container?.classList.contains('wysiwyg-container')) return

                e.isSourceView = !e.isSourceView
                container.classList.toggle('is-source-mode', e.isSourceView)

                if (e.isSourceView) {
                    textarea.value = e.tiptap.getHTML()
                    e.toolbar.setDisabled(true, ['source', 'maximize'])
                } else {
                    e.tiptap.commands.setContent(textarea.value)
                    e.toolbar.setDisabled(false, ['source', 'maximize'])
                }
            },
            isActive: (e) => e.isSourceView
        },
        maximize: {
            icon: IconMaximize,
            tooltip: 'Maximize / Fullscreen',
            command: (e) => {
                const container = e.toolbar.element.parentElement
                if (!container?.classList.contains('wysiwyg-container')) return

                e.isMaximized = !e.isMaximized
                container.classList.toggle('is-maximized', e.isMaximized)

                const btn = container.querySelector('[data-action="maximize"]') as HTMLElement
                btn.innerHTML = e.isMaximized ? IconMinimize : IconMaximize

                document.body.style.overflow = e.isMaximized ? 'hidden' : ''
            },
            isActive: (e) => e.isMaximized
        }
    }
}
