import { defineConfig } from 'vitepress'
import sidebar from "./nav/sidebar";
import navbar from "./nav/navbar";

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "ElasticMS",
  description: "Documentation",
  ignoreDeadLinks: true,
  head: [
    ['link', { rel: 'icon', href: '/favicon.ico' }]
  ],
  markdown: {
      lineNumbers: true
  },
  themeConfig: {
    logo: '/logo.png',
    nav: navbar,
    sidebar: sidebar,
    socialLinks: [
      { icon: 'github', link: 'https://github.com/ems-project/elasticms' }
    ],
    search: {
      provider: 'local'
    }
  }
})
