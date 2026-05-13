import { TiptapModule } from '../types.ts'
import { TiptapEditor } from '../editor.ts'
import IconRuler from '@tabler/icons/outline/ruler.svg?raw'
import IconRulerOff from '@tabler/icons/outline/ruler-off.svg?raw'

const SHOW_BLOCKS_CLASS = 'tiptap-show-blocks'

export const showBlocksModule: TiptapModule = {
    toolbarGroup: 'tools',
    toolbar: [
        {
            name: 'showBlocks',
            icon: IconRuler,
            tooltip: 'tools_show_Blocks',
            order: 99,
            command: (editor: TiptapEditor) => {
                editor.tiptap.view.dom.classList.toggle(SHOW_BLOCKS_CLASS)
                const active = editor.tiptap.view.dom.classList.contains(SHOW_BLOCKS_CLASS)

                const button = editor.toolbar.getButton('showBlocks')
                if (button) {
                    button.innerHTML = active ? IconRulerOff : IconRuler
                    button.title = active ? 'Hide Blocks' : 'Show Blocks'
                }
            },
            isActive: (editor: TiptapEditor) => {
                return editor.tiptap.view.dom.classList.contains(SHOW_BLOCKS_CLASS)
            }
        }
    ],
    isEnabled: (wysiwygProfile) =>
        wysiwygProfile.config.extraPlugins?.includes('showblocks') ?? false
}
