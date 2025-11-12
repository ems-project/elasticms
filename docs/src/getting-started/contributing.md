# Contributing

Create a fork from our [elasticms](https//github.com/ems-project/elasticms) mono repository.

## Pull requests

Submitted pull requests will be validated by elasticms developers and merged when approved.

Add a good description, detailed commit messages and multiple small pull requests is preferred.

### Code Quality

Quality off the pull requests is insured by:

- [PHP Coding Standards Fixer](https://cs.symfony.com)
- [PHPStan](https://phpstan.org/)
- [PHPUnit](https://phpunit.de/)

Our composer has aliases defined for checking before submitting pull request. 

```bash
composer phpcs
composer phpstan
composer phpunit
```

### Target branch

Bugfixes should always target the lowest branch possible.

New features should target the default branch.

### Title

The pull request title should follow the git [conventional commits](https://www.conventionalcommits.org/) standard.
This is forced with a GitHub action.

On merge all commits are squashed and the PR title is used as commit message.
Sub commits inside the pull request are not required to follow the conventional commits standard.

#### Examples
* fix(user): update profile 500 error
* fix(user): change password not updating
* feat(wysiwyg): media library support

| type     | section (changelog)      | description                                                                                             |
|----------|--------------------------|---------------------------------------------------------------------------------------------------------|
| feat     | Features                 | A new feature                                                                                           |
| fix      | Bug Fixes                | A bug fix                                                                                               |
| docs     | Documentation            | Documentation only changes                                                                              |
| style    | Styles                   | Changes that do not affect the meaning of the code (white-space formatting missing semi-colons etc)     |
| refactor | Code Refactoring         | A code change that neither fixes a bug nor adds a feature                                               |
| perf     | Performance Improvements | A code change that improves performance                                                                 |
| test     | Tests                    | Adding missing tests or correcting existing tests                                                       |
| build    | Builds                   | Changes that affect the build system or external dependencies (example scopes gulp broccoli npm)        |
| ci       | Continuous Integrations  | Changes to our CI configuration files and scripts (example scopes Travis Circle BrowserStack SauceLabs) |
| chore    | Chores                   | Other changes that don't modify src or test files                                                       |
| revert   | Reverts                  | Reverts a previous commit                                                                               |

## Rector

[Rector](https://getrector.org/) is also available for ensuring that PHP and Symfony best practices are followed.

`````bash
composer rector
`````

## Monorepo builder

For building our mono repository we used the [symplify/monorepo-builder](https://github.com/symplify/monorepo-builder).

```
vendor/bin/monorepo-builder validate
```
