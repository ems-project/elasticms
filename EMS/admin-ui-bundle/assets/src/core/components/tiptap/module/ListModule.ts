import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import IconBulletList from '@tabler/icons/outline/list.svg?raw'
import IconOrderedList from '@tabler/icons/outline/list-numbers.svg?raw'
import { TiptapModule } from '../types.ts'

export const ListModule: TiptapModule = {
    name: 'list',
    extensions: [BulletList, OrderedList, ListItem],
    groups: {
        list: ['bulletList', 'orderedList']
    },
    actions: {
        bulletList: {
            icon: IconBulletList,
            tooltip: 'Bullet List',
            command: (e) => e.tiptap.chain().focus().toggleBulletList().run(),
            isActive: (e) => e.tiptap.isActive('bulletList')
        },
        orderedList: {
            icon: IconOrderedList,
            tooltip: 'Numbered List',
            command: (e) => e.tiptap.chain().focus().toggleOrderedList().run(),
            isActive: (e) => e.tiptap.isActive('orderedList')
        }
    }
}
