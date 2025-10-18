# Dashboard

Dashboards are customizable views that can apply to many content types at the same times.

<!-- TOC -->
* [Dashboard](#dashboard)
  * [Properties](#properties)
  * [Dashboard types](#dashboard-types)
    * [Type export](#type-export)
    * [Type revision task](#type-revision-task)
    * [Type template](#type-template)
  * [Upcoming dashboards](#upcoming-dashboards)
<!-- TOC -->

## Properties 

| Property         | Description                                                     |
|------------------|-----------------------------------------------------------------|
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
|-----------------|----------------------------------------|
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
|----------|-----------------------------------------------------------------|
| body     | Twig template, inject in content block                          |
| header   | Twig template, inject at the end of the header tag (css)        |
| footer   | Twig template, inject at the end of the footer tag (javascript) |

## Upcoming dashboards

Here a list of dashboard types that we may  develop in the future:

- logs: a tools to have access to logs (filtered fir the current user or severity or not)
- analytics: integration with analytics tools such Google Analytics, Matomo, ...
- structure: A structure tools that organise documents in a structure (i.e. linked to a path_en field)
- calendar
- maps
- gantt
- advance search
- notification
- tasks
- jobs
- link/shortcut
- shopping basket
- redirect
- ...

