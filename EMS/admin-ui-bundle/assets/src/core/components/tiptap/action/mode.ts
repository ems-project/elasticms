import IconSource from '@tabler/icons/outline/code.svg?raw'
import IconMaximize from '@tabler/icons/outline/maximize.svg?raw'
import IconMinimize from '@tabler/icons/outline/minimize.svg?raw'
import { ToolbarAction } from '../types.ts'

export const modeActions: ToolbarAction[] = [
    {
        name: 'Source',
        group: 'mode',
        icon: IconSource,
        tooltip: 'Source Code',
        command: (e) => {
            if (!e.textarea || !e.toolbar) return

            const container = e.toolbar.container.closest('.wysiwyg-container')
            if (!container?.classList.contains('wysiwyg-container')) return

            e.isSourceView = !e.isSourceView
            container.classList.toggle('is-source-mode', e.isSourceView)

            if (e.isSourceView) {
                e.textarea.value = e.tiptap.getHTML()
                e.toolbar.setDisabled(true, ['Source', 'Maximize'])

                console.debug(e.textarea.value)
            } else {
                e.tiptap.commands.setContent(e.textarea.value)
                e.toolbar.setDisabled(false, ['Source', 'Maximize'])
            }
        },
        isActive: (e) => e.isSourceView
    },
    {
        name: 'Maximize',
        group: 'tools',
        icon: IconMaximize,
        tooltip: 'Maximize',
        command: (e) => {
            if (!e.toolbar) return
            const container = e.toolbar.container.closest('.wysiwyg-container')
            if (!container?.classList.contains('wysiwyg-container')) return

            e.isMaximized = !e.isMaximized
            container.classList.toggle('is-maximized', e.isMaximized)

            const btn = container.querySelector('[data-action="Maximize"]') as HTMLElement
            btn.innerHTML = e.isMaximized ? IconMinimize : IconMaximize

            document.body.style.overflow = e.isMaximized ? 'hidden' : ''
        },
        isActive: (e) => e.isMaximized
    }
]
