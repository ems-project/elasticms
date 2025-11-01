import type {DefaultTheme} from 'vitepress'

const sidebar: DefaultTheme.SidebarMulti = {
    '/': [
        {
            text: 'Getting started',
            collapsed: true,
            items: [
                {text: 'Quick start', link: '/guide/introduction'},
            ]
        },
    ]
}

export default sidebar;
