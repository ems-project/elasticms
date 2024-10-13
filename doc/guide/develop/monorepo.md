# Monorepo

The monorepo includes the following applications:

- [Demo application](https://github.com/ems-project/elasticms-demo)
- [Admin application](https://github.com/ems-project/elasticms-admin)
- [Web application](https://github.com/ems-project/elasticms-web)
- [CLI application](https://github.com/ems-project/elasticms-cli)

It also contains the following bundles:

| Name                                                                          | Description                                            |
|-------------------------------------------------------------------------------|--------------------------------------------------------|
| [EMSAdminUIBundle](https://github.com/ems-project/EMSAdminUIBundle)           | Frontend bundle for the admin application              |
| [EMSClientHelperBundle](https://github.com/ems-project/EMSClientHelperBundle) | Used in the web application for frontend functionality |
| [EMSCommonBundle](https://github.com/ems-project/EMSCommonBundle)             | Provides shared logic and utilities used across all applications                      |
| [EMSCoreBundle](https://github.com/ems-project/EMSCoreBundle)                 | Exclusively used in the admin application              |
| [EMSFormBundle](https://github.com/ems-project/EMSFormBundle)                 | Provides support for building web forms                |
| [EMSSubmissionBundle](https://github.com/ems-project/EMSSubmissionBundle)     | Handles form submissions                               |
| [helpers](https://github.com/ems-project/helpers)                             | A collection of utility functions                      |
| [xliff](https://github.com/ems-project/xliff)                                 | Manages XLIFF translations                             |

For building our mono repository we used
the [symplify/monorepo-builder](https://github.com/symplify/monorepo-builder).

```
vendor/bin/monorepo-builder validate
```
