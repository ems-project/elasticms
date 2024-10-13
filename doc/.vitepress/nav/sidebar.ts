import type { DefaultTheme } from 'vitepress'

const sidebar: DefaultTheme.SidebarItem[] = {
  '/': [
    {
      text: 'Guide',
      collapsed: true,
      items: [
        {text: 'Introduction', link: '/guide/introduction' },
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
      text: 'Develop',
      collapsed: true,
      items: [
        {text: 'Monorepo', link: '/guide/develop/monorepo' },
        {text: 'Contributing', link: '/guide/develop/contributing'},
        {text: 'Setup environment', link: '/guide/develop/environment' },
      ]
    }
  ]
}

export default sidebar;
