# AGENTS.md

Guidance for coding agents working in this repository.

## Repository Overview

This is the ElasticMS monorepo. It contains Symfony applications, reusable EMS bundles, a demo project, documentation, Docker tooling, and release tooling.

Main areas:

- `elasticms-admin/`: Symfony admin application.
- `elasticms-web/`: Symfony web application.
- `elasticms-cli/`: Symfony CLI application.
- `EMS/*-bundle/`: Symfony bundles.
- `EMS/helpers/`: shared helper library.
- `demo/`: demo frontend and demo configuration.
- `docs/`: documentation site.
- `docker/`: local development services.
- `build/`: release/build automation.

The root `composer.json` is the source of truth for PHP dependencies, autoloading, and validation commands. The project targets PHP `^8.5` and Symfony `7.4.*`.

## Recent Context

Recent work in this checkout includes:

- branch merges up to `7.2`;
- a sandbox Docker setup added through merges, followed by `chore: delete sandbox.env`;
- media library file-browser work touching admin UI assets, Twig templates, and core bundle media-library code;
- generated admin UI public assets and manifests updated by asset builds;
- documentation updates under `docs/src`.

Current local untracked files observed during setup were `.DS_Store` files and `.idea/`. Treat them as user/local environment files and do not modify or remove them unless explicitly asked.

## Working Rules

- Prefer small, targeted changes that match the existing Symfony and bundle structure.
- Do not refactor unrelated code while fixing a specific issue.
- Preserve public APIs and serialized formats unless the task explicitly calls for a breaking change.
- Use existing helpers and value objects before introducing new abstractions.
- Keep generated assets, manifests, lock files, and changelogs untouched unless they are part of the requested change.
- Do not revert user changes or local untracked files.
- Use `rg` for searches and inspect nearby tests before changing behavior.

## PHP Conventions

- All PHP source files should use `declare(strict_types=1);`.
- Follow the root PHP CS Fixer config in `config/php-cs-fixer.php`.
- Static analysis is configured at PHPStan level 8 in `config/phpstan.neon.dist`.
- Prefer typed properties, explicit return types, and narrow exceptions.
- Avoid broad `mixed` usage unless it mirrors an existing boundary such as decoded JSON, external payloads, or Symfony container configuration.
- When adding tests, place them in the matching package test namespace:
  - `EMS\\CoreBundle\\Tests\\` for `EMS/core-bundle/tests/`;
  - `EMS\\CommonBundle\\Tests\\` for `EMS/common-bundle/tests/`;
  - `EMS\\Helpers\\Tests\\` for `EMS/helpers/tests/`;
  - and similarly for other packages.

## Validation Commands

Run the narrowest useful checks first, then broaden if the change has cross-package impact.

Common commands:

```bash
composer phpstan
composer phpcs
composer phpunit
composer phpunit-admin
composer phpunit-cli
composer phpunit-web
composer lint
composer monorepo-validate
make check
```

For a single PHP file, also use:

```bash
php -l path/to/file.php
```

For focused PHPUnit runs, use the root config or the matching app config:

```bash
php vendor/bin/phpunit -c config/phpunit.xml.dist path/to/Test.php
php vendor/bin/phpunit -c elasticms-admin/phpunit.xml.dist path/to/Test.php
```

If dependencies or services are missing locally, report the exact command attempted and the failure. Do not silently skip validation.

## Docker And Local Services

The Makefile drives the local development environment.

Useful targets:

```bash
make init
make start
make stop
make sandbox
make server-start/admin
make server-start/web
make server-stop/admin
make server-stop/web
make demo
```

Local URLs from the Makefile:

- Admin: `http://localhost:8881`
- Web: `http://localhost:8882`
- CLI server: `http://localhost:8883`

The Docker project directory is `docker/`. Avoid committing local environment files generated from `*.dist` unless the task is explicitly about environment templates.

## Assets And Frontend

Admin UI assets live mainly under `EMS/admin-ui-bundle/assets`.

Use Makefile targets for asset work:

```bash
make assets-install
make assets-build
make assets-dev
```

Demo frontend commands are wrapped by:

```bash
make demo-npm/"install"
make demo-npm/"run build"
make demo-npm-watch
```

When changing frontend source that produces hashed files under `public/`, ensure the generated assets and manifest are updated only when required by the task.

## Documentation

Docs are under `docs/src`.

Useful commands:

```bash
make docs
make docs-build
make docs-format
make docs-lint
```

Keep docs changes colocated with the related feature or upgrade note. The current docs root is `docs/src`, not the old `docs/dev` path.

## Release And Branch Notes

The root changelog files are version-line specific:

- `CHANGELOG-4.x.md`
- `CHANGELOG-5.x.md`
- `CHANGELOG-6.x.md`
- `CHANGELOG-7.x.md`

Only update changelogs when the requested work requires release notes. Branch merges can touch large generated asset sets; review diffs carefully before attributing those files to the current task.

## Sandbox Note

In some agent environments, sandboxed shell execution may fail before commands run with an error about `bwrap` and unprivileged user namespaces. If that happens, rerun required read-only inspections or validations with the appropriate approval instead of inventing results.
