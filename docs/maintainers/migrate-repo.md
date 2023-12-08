
# Migrate a repository into the monorepo

## First steps ([source](https://medium.com/lgtm/migrating-to-the-monorepo-582106142654))

```bash
git remote add elasticms-admin git@github.com:ems-project/EMSCoreBundle.git
git fetch elasticms-admin
git read-tree --prefix=elasticms-admin -u elasticms-admin/4.x
git commit -m "Migrate elasticms-admin repo to EMS/elasticms-admin"
```

## Second steps ([source](https://tomasvotruba.com/blog/2020/06/15/how-to-create-monorepo-from-existing-repositories-in-7-steps/))

```bash
vendor/bin/monorepo-builder merge
```

Then resolve composer conflicts.

```bash
composer update
```

Add the repo's `src` and `tests` folders to the `.php-cs-fixer.dist.php` file. Run `composer phpcs`.

Add the repo's `src` folder to the `phpstan.neon.dist` file. Run `composer phpstan`.

Add the repo's `src` and `tests` folders to the `phpunit.xml.dist` file. Run `composer phpunit`.

Run `composer rector`.
