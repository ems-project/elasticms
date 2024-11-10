import type {DefaultTheme} from 'vitepress'

const sidebar: DefaultTheme.SidebarMulti = {
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
            text: 'Admin',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/ems/admin/environment-variables'},
                {text: 'Commands', link: '/ems/admin/commands'},
            ]
        },
        {
            text: 'Web',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/ems/web/environment-variables'},
                {text: 'Commands', link: '/ems/web/commands'},
            ]
        },
        {
            text: 'CLI',
            collapsed: true,
            items: [
                {text: 'Environment variables', link: '/ems/cli/environment-variables'},
                {text: 'Commands', link: '/ems/cli/commands'},
            ]
        },
        {
            text: 'Common',
            collapsed: true,
            items: [
                {text: 'Commands', link: '/ems/common/commands'},
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
