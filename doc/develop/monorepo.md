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

## Migration

Steps for migrating a repository into the monorepo.

1. Add the code
    ```bash
    git remote add elasticms-admin git@github.com:ems-project/EMSCoreBundle.git
    git fetch elasticms-admin
    git read-tree --prefix=elasticms-admin -u elasticms-admin/4.x
    git commit -m "Migrate elasticms-admin repo to EMS/elasticms-admin"
    ```
2. Merge composer
    ```bash
    vendor/bin/monorepo-builder merge
    ```
3. Resolve composer conflicts
4. Composer update
    ```bash
    composer update
    ```
5. Update tools
   * Add the repo's `src` and `tests` folders to `.php-cs-fixer.dist.php`
   * Add the repo's `src` folder to `phpstan.neon.dist`
   * Add the repo's `src` and `tests` folders to `phpunit.xml.dist`
6. Run tools
    ```bash
    composer phpcs
    composer phpstan
    composer phpunit
    composer rector
    ```
