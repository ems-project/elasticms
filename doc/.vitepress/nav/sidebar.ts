import type {DefaultTheme} from 'vitepress'

const sidebar: DefaultTheme.SidebarItem[] = {
    '/': [
        {
            text: 'Guide',
            collapsed: true,
            items: [
                {text: 'Introduction', link: '/guide/introduction'},
                {text: 'Getting Started', link: '/guide/getting-started'},
            ]
        },
        {
            text: 'Release',
            collapsed: true,
            items: [
                {text: 'Upgrade 5.x', link: '/release/upgrade-5x'},
                {text: 'Upgrade 4.x', link: '/release/upgrade-4x'},
                {text: 'Upgrade', link: '/release/upgrade'},
            ]
        },
        {
            text: 'EMS - Admin',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/app/admin/environment-variables'},
            ]
        },
        {
            text: 'EMS - Web',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/app/web/environment-variables'},
            ]
        },
        {
            text: 'EMS - CLI',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/app/cli/environment-variables'},
            ]
        },
        {
            text: 'Develop',
            collapsed: true,
            items: [
                {text: 'Monorepo', link: '/develop/monorepo'},
                {text: 'Contributing', link: '/develop/contributing'},
                {text: 'Setup environment', link: '/develop/environment'},
            ]
        }
    ]
}

export default sidebar;
