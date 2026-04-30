import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import IconBulletedList from '@tabler/icons/outline/list.svg?raw'
import IconNumberedList from '@tabler/icons/outline/list-numbers.svg?raw'
import { TiptapModule } from '../types.ts'

export const listModule: TiptapModule = {
    extensions: [ListItem],
    group: 'list',
    toolbar: [
        {
            extensions: [OrderedList],
            name: 'NumberedList',
            icon: IconNumberedList,
            tooltip: 'Insert/Remove Numbered List',
            command: (e) => e.tiptap.chain().focus().toggleOrderedList().run(),
            isActive: (e) => e.tiptap.isActive('orderedList')
        },
        {
            extensions: [BulletList],
            name: 'BulletedList',
            icon: IconBulletedList,
            tooltip: 'Insert/Remove Bulleted List',
            command: (e) => e.tiptap.chain().focus().toggleBulletList().run(),
            isActive: (e) => e.tiptap.isActive('bulletList')
        }
    ]
}

