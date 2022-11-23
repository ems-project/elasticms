# elasticms
elasticMS's monorepo

[Coding standards](https://github.com/ems-project/elasticms-client/blob/main/doc/coding_standards.md)

## Composer commands

* `composer phpcs`: Apply the coding standards
* `composer phpstan`: Scans codebase and looks for both obvious & tricky bugs
* `composer phpunit`: Runs unit tests suite 
* `composer phpall`: Runs all previous commands



# Migrate a repo

## First steps ([source](https://medium.com/lgtm/migrating-to-the-monorepo-582106142654))

```bash
git remote add persephone git@github.com:lgtm/persephone
git fetch persephone
git read-tree --prefix=src/persephone --u persephone/master
git commit -m "Migrate persephone repo to src/persephone"
git commit -m "Migrate persephone repo to src/persephone"
```

## Second steps ([source](https://tomasvotruba.com/blog/2020/06/15/how-to-create-monorepo-from-existing-repositories-in-7-steps/))

```bash
vendor/bin/monorepo-builder merge
```

Then resolve composer conflicts.

Add the repo's `src` and `test` folders to the `.php-cs-fixer.dist.php` file. Run `composer phpcs`.

Add the repo's `src` folder to the `phpstan.neon.dist` file. Run `composer phpstan`.






