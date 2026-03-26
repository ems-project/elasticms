import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import { TiptapModule } from '../types.ts'

export const ListModule: TiptapModule = {
    name: 'list',
    extensions: [BulletList, OrderedList, ListItem],
    groups: {
        list: ['bulletList', 'orderedList']
    },
    actions: {
        bulletList: {
            icon: 'bulletList',
            tooltip: 'Bullet List',
            command: (e) => e.chain().focus().toggleBulletList().run(),
            isActive: (e) => e.isActive('bulletList')
        },
        orderedList: {
            icon: 'orderedList',
            tooltip: 'Numbered List',
            command: (e) => e.chain().focus().toggleOrderedList().run(),
            isActive: (e) => e.isActive('orderedList')
        }
    }
}
