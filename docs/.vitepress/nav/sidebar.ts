import type {DefaultTheme} from 'vitepress'

const sidebar: DefaultTheme.SidebarMulti = {
    '/': [
        {
            text: 'Getting started',
            collapsed: false,
            items: [
                {text: 'Quick start', link: '/getting-started/quick-start'},
                {text: 'Setup your computer', link: '/getting-started/local-dev'},
                {text: 'Dev environment', link: '/getting-started/dev-env'},
                {text: 'Contributing', link: '/getting-started/contributing'},
            ]
        },
    ]
}

export default sidebar;

