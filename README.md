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
git remote add form-bundle git@github.com:ems-project/EMSFormBundle.git
git fetch form-bundle
git read-tree --prefix=EMS/form-bundle -u form-bundle/4.x
git commit -m "Migrate form-bundle repo to src/form-bundle"
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





