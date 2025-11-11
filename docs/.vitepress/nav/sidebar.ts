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
        {
            text: 'Site building',
            collapsed: true,
            items: [
                {text: 'Twig', link: '/site-building/twig'},
            ]
        },
        {
            text: 'ElasticMS Admin',
            collapsed: false,
            link: '/elasticms-admin/index',
            items: [
                { text: 'Environment variables', link: '/elasticms-admin/environment-variables'},
                { text: 'Commands', link: '/elasticms-admin/commands/commands'},
                { text: 'Jobs', link: '/elasticms-admin/commands/jobs'},
                {
                    text: 'ContentType',
                    collapsed: true,
                    items: [
                        { text: 'Config', link: '/elasticms-admin/contentType/contentType' },
                        { text: 'Form', link: '/elasticms-admin/contentType/form' },
                        { text: 'File preview', link: '/elasticms-admin/contentType/file-preview' }
                    ]
                },
                { text: 'Dashboard', link: '/elasticms-admin/dashboard/dashboard'},
                { text: 'Environment', link: '/elasticms-admin/environment/environment'},
                { text: 'User', link: '/elasticms-admin/user/user'},
                { text: 'WYSIWYG', link: '/elasticms-admin/wysiwyg/wysiwyg' },
                { text: 'Async (Messenger, Mercure)', link: '/elasticms-admin/async' },
            ]
        },
        {
            text: 'ElasticMS Web',
            link: '/elasticms-web/index',
            items: [
                { text: 'Environment variables', link: '/elasticms-web/parameters' },
                { text: 'Security', link: '/elasticms-web/security' },
            ]
        }
    ]
}

export default sidebar;



