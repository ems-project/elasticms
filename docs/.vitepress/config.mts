import {withMermaid} from 'vitepress-plugin-mermaid'

import sidebar from "./nav/sidebar";
import navbar from "./nav/navbar";

export default withMermaid({
    title: "ElasticMS",
    description: "Documentation",
    ignoreDeadLinks: true,
    head: [
        ['link', {rel: 'icon', href: '/favicon.ico'}]
    ],
    markdown: {
        lineNumbers: true
    },
    vite: {
        build: {
            chunkSizeWarningLimit: 1500
        },
        resolve: {
            alias: {
                dayjs: "dayjs/",
            }
        }
    },
    srcDir: './src',
    themeConfig: {
        logo: '/logo.png',
        nav: navbar,
        sidebar: sidebar,
        outline: 'deep',
        editLink: {
            pattern: 'https://github.com/ems-project/elasticms/edit/5.x/doc/:path',
            text: 'Edit this page on GitHub'
        },
        socialLinks: [
            {icon: 'github', link: 'https://github.com/ems-project/elasticms'}
        ],
        search: {
            provider: 'local'
        }
    },
    mermaid: {
        securityLevel: "loose",
            theme: "default",
            flowchart: { htmlLabels: true },
    },
    mermaidPlugin: {
        class: "mermaid my-class",
    },
});