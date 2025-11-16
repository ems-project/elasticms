import type {DefaultTheme} from 'vitepress'

const sidebar: DefaultTheme.SidebarMulti = {
    '/': [
        {
            text: 'Getting started',
            collapsed: false,
            items: [
                { text: 'Quick start', link: '/getting-started/quick-start' },
                { text: 'Setup your computer', link: '/getting-started/local-dev' },
                { text: 'Dev environment', link: '/getting-started/dev-env' },
                { text: 'Contributing', link: '/getting-started/contributing' },
            ]
        },
        {
            text: 'Site building',
            collapsed: true,
            items: [
                { text: 'Twig', link: '/site-building/twig' },
            ]
        },
        {
            text: 'ElasticMS Admin',
            collapsed: false,
            link: '/elasticms-admin/index',
            items: [
                { text: 'Environment variables', link: '/elasticms-admin/environment-variables' },
                { text: 'Commands', link: '/elasticms-admin/commands/commands' },
                { text: 'Jobs', link: '/elasticms-admin/commands/jobs' },
                {
                    text: 'ContentType',
                    collapsed: true,
                    items: [
                        { text: 'Config', link: '/elasticms-admin/contentType/contentType' },
                        { text: 'Form', link: '/elasticms-admin/contentType/form' },
                        { text: 'File preview', link: '/elasticms-admin/contentType/file-preview' }
                    ]
                },
                { text: 'Dashboard', link: '/elasticms-admin/dashboard/dashboard' },
                { text: 'Environment', link: '/elasticms-admin/environment/environment' },
                { text: 'User', link: '/elasticms-admin/user/user' },
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
        },
        {
            text: 'ElasticMS CLI',
            link: '/elasticms-cli/index',
            items: [
                { text: 'Environment variables', link: '/elasticms-cli/parameters' },
                { text: "Common's commands", link: '/elasticms-cli/commands' },
                { text: 'Web Audit', link: '/elasticms-cli/audit' },
                { text: 'Update documents', link: '/elasticms-cli/documents' },
                { text: 'Migrate Web', link: '/elasticms-cli/migrate-web' },
                { text: 'Media File', link: '/elasticms-cli/media-file' },
            ]
        },
        {
            text: 'Bundles',
            collapsed: false,
            items: [
                {
                    text: 'Client Helper',
                    collapsed: true,
                    link: '/dev/client-helper-bundle/index',
                    items: [
                        { text: 'Environment', link: '/dev/client-helper-bundle/environment' },
                        { text: 'Routing', link: '/dev/client-helper-bundle/routing' },
                        { text: 'Search', link: '/dev/client-helper-bundle/search' },
                        {
                            text: 'Twig',
                            link: '/dev/client-helper-bundle/twig',
                            items: [
                                { text: 'Filters', link: '/dev/client-helper-bundle/Twig/filters' },
                                { text: 'Functions', link: '/dev/client-helper-bundle/Twig/functions' },
                            ]
                        },
                    ]
                },
                {
                    text: 'Common',
                    collapsed: true,
                    link: '/dev/common-bundle/index',
                    items: [
                        { text: 'Twig', link: '/dev/common-bundle/twig' },
                        { text: 'Commands', link: '/dev/common-bundle/commands' },
                        { text: 'Core API', link: '/dev/common-bundle/core-api' },
                        { text: 'Helpers', link: '/dev/common-bundle/helpers' },
                        { text: 'JSON Menu Nested', link: '/dev/common-bundle/json-menu-nested' },
                        { text: 'JSON Menu', link: '/dev/common-bundle/json-menu' },
                        { text: 'Metrics', link: '/dev/common-bundle/metrics' },
                        { text: 'Processors', link: '/dev/common-bundle/processors' },
                        { text: 'Spreadsheet', link: '/dev/common-bundle/spreadsheet' },
                        { text: 'Storages', link: '/dev/common-bundle/storages' },
                    ]
                },
                {
                    text: 'Core',
                    collapsed: true,
                    link: '/dev/core-bundle/index',
                    items: [
                        { text: 'Concepts', link: '/dev/core-bundle/elasticms' },
                        { text: 'Install', link: '/dev/core-bundle/install' },
                        { text: 'Commands', link: '/dev/core-bundle/commands' },
                        { text: 'API', link: '/dev/core-bundle/api' },
                        { text: "Content Type's actions", link: '/dev/core-bundle/content-types/actions' },
                        { text: "Content Type's views", link: '/dev/core-bundle/content-types/views' },
                        { text: "Content Type's transformers", link: '/dev/core-bundle/content-types/transformers' },
                        { text: 'Routes', link: '/dev/core-bundle/routes' },
                        { text: 'Tips & Tricks', link: '/dev/core-bundle/tricks' },
                        {
                            text: 'Twig',
                            collapsed: true,
                            items: [
                                { text: 'Component', link: '/dev/core-bundle/twig/component' },
                                { text: 'Core', link: '/dev/core-bundle/twig/core' },
                                { text: 'Datatable', link: '/dev/core-bundle/twig/datatable' },
                                { text: 'Extractor', link: '/dev/core-bundle/twig/extractor' },
                                { text: 'I18N', link: '/dev/core-bundle/twig/i18n' },
                                { text: 'JSON Menu Nested', link: '/dev/core-bundle/twig/json-menu-nested' },
                                { text: 'Revision', link: '/dev/core-bundle/twig/revision' },
                                { text: 'Styling', link: '/dev/core-bundle/twig/styling' },
                            ]
                        },
                    ]
                },
                {
                    text: 'Form',
                    collapsed: true,
                    link: '/dev/form-bundle/index',
                    items: [
                        { text: 'Installation', link: '/dev/form-bundle/install' },
                        { text: 'Configuration', link: '/dev/form-bundle/config' },
                        { text: 'How to', link: '/dev/form-bundle/example' },
                        { text: 'Handle Data', link: '/dev/form-bundle/handlers' },
                        { text: 'Fields', link: '/dev/form-bundle/fields' },
                        { text: 'Validations', link: '/dev/form-bundle/validations' },
                        { text: 'Tips', link: '/dev/form-bundle/tips' },
                    ]
                },
                {
                    text: 'Submission',
                    collapsed: true,
                    link: '/dev/submission-bundle/index',
                    items: [
                        { text: 'Twig', link: '/dev/submission-bundle/twig' },
                        {
                            text: 'Handlers',
                            collapsed: true,
                            items: [
                                { text: 'email', link: '/dev/submission-bundle/handlers/email' },
                                { text: 'HTTP', link: '/dev/submission-bundle/handlers/http' },
                                { text: 'Multipart', link: '/dev/submission-bundle/handlers/multipart' },
                                { text: 'PDF', link: '/dev/submission-bundle/handlers/pdf' },
                                { text: 'ServiceNow', link: '/dev/submission-bundle/handlers/service-now' },
                                { text: 'SFTP', link: '/dev/submission-bundle/handlers/sftp' },
                                { text: 'Soap', link: '/dev/submission-bundle/handlers/soap' },
                                { text: 'Zip', link: '/dev/submission-bundle/handlers/zip' },
                            ]
                        }
                    ]
                },
            ]
        },
        {
            text: 'Recipes',
            collapsed: true,
            items: [
                { text: 'Export as word', link: '/recipes/export-as-word' },
                { text: 'File structure', link: '/recipes/websites/file-structure' },
                { text: 'Hierarchy to JSON Netsed', link: '/recipes/hierarchy-to-json-netsed' },
                { text: 'Store Data', link: '/recipes/store-data' },
                { text: 'Postprocessing', link: '/recipes/postprocessing' },
            ]
        },
        {
            text: 'Packages',
            collapsed: true,
            items: [
                {
                    text: 'Helper',
                    link: '/dev/helpers/index',
                    items: [
                        { text: 'ArrayHelper', link: '/dev/helpers/array-helper' },
                        { text: 'Standards', link: '/dev/helpers/standard' },
                    ]
                },
                { text: 'Xliff', link: '/dev/xliff/index' },
            ]
        },
        {
            text: 'Contributors',
            collapsed: true,
            items: [
                { text: 'Migrate a repository', link: '/maintainers/migrate-repo' },
                { text: 'Migrate to elasticsearch 7', link: '/maintainers/migrade-to-es7' },
            ]
        }
    ]
}

export default sidebar



