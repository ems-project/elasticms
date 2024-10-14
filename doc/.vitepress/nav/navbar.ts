import type { DefaultTheme } from 'vitepress'

const navbar: DefaultTheme.NavItem[] = [
  { text: 'Home', link: '/' },
  {
    text: 'Guide',
    items: [
      {text: 'Getting Started', link: '/guide/getting-started'},
    ]
  },
  {
    text: 'Releases',
    items: [
      {text: 'Upgrade 5.x', link: '/release/upgrade-5x'},
      {text: 'Notes 5.x', link: 'https://github.com/ems-project/elasticms/blob/5.x/CHANGELOG-5.x.md'},
    ]
  },
]

export default navbar;
