# User

For managing elasticms user you are required to have the role `USER_MANAGEMENT`, or CLI access.

<!-- TOC -->
* [User](#user)
  * [Properties](#properties)
  * [Options](#options)
  * [Roles](#roles)
  * [Circles](#circles)
<!-- TOC -->

## Properties
| Property                     | Description                                                        |
|------------------------------|--------------------------------------------------------------------|
| username                     | Login username                                                     |
| password                     | Login password                                                     |
| e-mail                       | Used for forget password and notifications                         |
| e-mail notification          | Disable email notification with this checkbox                      |
| display name                 | Name to display in elasticms                                       |
| enabled                      | Disable user with this checkbox                                    |
| roles                        | Collection of [roles](#Roles)                                      |
| circles                      | Collection of [circles](#Circles)                                  |
| last login                   | Date time when the user last logged in                             |
| locale                       | Locale used for the UI (only support currently `EN`)               | 
| locale preferred             | Translate notifications, views, dashboard in user preferred locale |
| sidebar collapse             | Checkbox for making the sidebar collapsed by default               |
| sidebar mini                 | Checkbox for mini sidebar                                          |
| layout boxed                 | Checkbox for boxed layout                                          |
| WYSIWYG profile              | see [WYSIWYG profiles](../wysiwyg/wysiwyg.md#profiles)             |
| allowed to configure WYSIWYG | user can overwrite WYSIWYG options                                 |

## Options

Since version 4.2 users also have options, in the feature we will extend and migrate properties.
These options can be managed by the user (profile) or by the user manager.

| Option        | Default | Description                                                                  |
|---------------|---------|------------------------------------------------------------------------------|
| simplified ui | false   | If enabled: hide save as draft and copy/paste functionality on revision edit |

## Roles

Users can have multiple roles, and always have at least the `USER`.

| Role            | Description                        | Hierarchy                  |
|-----------------|------------------------------------|----------------------------|
| USER            | Default role                       |                            |
| AUTHOR          | Group authors                      | USER                       |       
| FORM_CRM        | **Granted** form submissions       | USER                       |
| TASK_MANAGER    | **Granted** manage revision tasks  | USER                       |
| REVIEWER        | Group reviewers                    | AUTHOR                     |
| TRADUCTOR       | Group traductors                   | REVIEWER                   |
| AUDITOR         | **Granted** audit tab on revisions | REVIEWER                   |
| PUBLISHER       | Group publishers                   | TRADUCTOR                  |
| WEBMASTER       | Group webmasters                   | PUBLISHER                  |
| USER_MANAGEMENT | **Granted** user management        |                            |
| COPY_PASTE      | **Granted** copy/paste revisions   |                            |
| ALLOW_ALIGN     | **Granted** align environments     |                            |
| DEFAULT_SEARCH  | **Granted** create default search  |                            |
| USER_READ       | **Granted** read user profiles     |                            |
| API             | **Granted** API access             |                            |
| SUPER           | **Granted** json edit revisions    |                            |
| ADMIN           | **Granted** all above actions      | WEBMASTER, USER_MANAGEMENT |
| SUPER_ADMIN     | **Granted** all actions            | ADMIN                      |

## Circles

@todo add documentation