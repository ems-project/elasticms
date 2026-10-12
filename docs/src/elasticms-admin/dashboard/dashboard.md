# Dashboard

Dashboards are customizable views that can apply to many content types at the same times.

## Properties

| Property         | Description                                                     |
| ---------------- | --------------------------------------------------------------- |
| name             | Webalized for generating the url                                |
| icon             | Display icon in menu, dropdown                                  |
| label            | Display label                                                   |
| color            | Display color                                                   |
| sidebarMenu      | Checkbox to display a dashboard's link in the sidebar menu      |
| notificationMenu | Checkbox to display a dashboard's link in the notification menu |
| type             | See [Dashboard types](#dashboard-types)                         |
| landingPage      | Checkbox use dashboard as landing page                          |
| quickSearch      | Checkbox use dashboard as quickSearch                           |
| role             | Grant access by role                                            |

## Dashboard types

### Type export

Generate a file export

| Property        | Description                            |
| --------------- | -------------------------------------- |
| body            | Twig template for file content         |
| filename        | Twig template for file name            |
| mimetype        | Optional file mime type                |
| fileDisposition | File disposition: Attachment or Inline |

### Type revision task

Enable the elasticms task dashboard, without extra configuration.
`@todo add documentation tasks with link here`

### Type template

Create a full customized dashboard.

| Property | Description                                                     |
| -------- | --------------------------------------------------------------- |
| body     | Twig template, inject in content block                          |
| header   | Twig template, inject at the end of the header tag (css)        |
| footer   | Twig template, inject at the end of the footer tag (javascript) |

### Type Matomo

Embed a Matomo dashboard in ElasticMS. Configure the following properties:

| Property        | Description                                                         |
| --------------- | ------------------------------------------------------------------- |
| matomoUrl       | Matomo base URL, for example `https://analytics.example.com/matomo` |
| matomoSiteId    | Matomo site identifier                                              |
| matomoTokenAuth | Matomo view-only authentication token                               |

The generated URL embeds the Matomo dashboard for the previous day. A view-only Matomo token is
required. The token is included in the iframe URL and is therefore visible to the browser.

Create a dedicated Matomo user with `View` access to the site, then generate its token in
**Administration > Personal > Security**. A token with write or administrator permissions is not
suitable for this embed.

## Upcoming dashboards

Here a list of dashboard types that we may develop in the future:

- logs: a tools to have access to logs (filtered fir the current user or severity or not)
- structure: A structure tools that organise documents in a structure (i.e. linked to a path_en
  field)
- calendar
- maps
- gantt
- notification
- tasks
- jobs
- link/shortcut
- shopping basket
- redirect
- ...
