# Changelog 4.x

## 4.3.3 (2022-12-21)
### Bug Fixes
* fix: cache missed or hit in the emsco flashbag by @theus77 in https://github.com/ems-project/elasticms/pull/197
* fix: int translation's key => string by @theus77 in https://github.com/ems-project/elasticms/pull/189
### Builds
* build: release improvements by @Davidmattei in https://github.com/ems-project/elasticms/pull/191

## 4.3.2 (2022-12-19)
### Bug Fixes
* fix: json_decode can't get null and empty string value by @coppee in https://github.com/ems-project/elasticms/pull/174
* fix: ldap api by @Davidmattei in https://github.com/ems-project/elasticms/pull/186
* fix: not defined icon by @theus77 in https://github.com/ems-project/elasticms/pull/175
* fix: post processing json encoding by @Davidmattei in https://github.com/ems-project/elasticms/pull/159

## 4.3.1 (2022-12-05)
### Features
* feat: dev env by @theus77 in https://github.com/ems-project/elasticms/pull/144
* feat: dev env by @theus77 in https://github.com/ems-project/elasticms/pull/145
* feat: limit tika file size by @theus77 in https://github.com/ems-project/elasticms/pull/142
### Bug Fixes
* fix(chore): rebuild env form broken by @Davidmattei in https://github.com/ems-project/elasticms/pull/157
* fix(core): Upload asset $status accessed before initialization by @Davidmattei in https://github.com/ems-project/elasticms/pull/156
* fix: json syntax error with empty string by @coppee in https://github.com/ems-project/elasticms/pull/150
### Chores
* chore(docker): env file update by @coppee in https://github.com/ems-project/elasticms/pull/151
* chore: config git by @theus77 in https://github.com/ems-project/elasticms/pull/155

## 4.3.0 (2022-11-29)
### Features
* feat: ems_dom_crawler filter by @theus77 in https://github.com/ems-project/elasticms/pull/131
* feat: rector by @theus77 in https://github.com/ems-project/elasticms/pull/2
### Bug Fixes
* fix(cli): div by zero in web audit status by @theus77 in https://github.com/ems-project/elasticms/pull/26
* fix(elasticms-cli): 404 on head by @theus77 in https://github.com/ems-project/elasticms/pull/25
* fix(environment): rebuild & reindex detach revisions by @Davidmattei in https://github.com/ems-project/elasticms/pull/143
* fix: channels not working searchConfig invalid json by @Davidmattei in https://github.com/ems-project/elasticms/pull/139
* fix: ckeditor all contentTypes by @Davidmattei in https://github.com/ems-project/elasticms/pull/141
* fix: load vendor from mono admin/cli/web by @Davidmattei in https://github.com/ems-project/elasticms/pull/136
* fix: managedAlias total is nullable by @Davidmattei in https://github.com/ems-project/elasticms/pull/138
* fix: no lighthouse audit on assets (useless and heavy) by @theus77 in https://github.com/ems-project/elasticms/pull/3
* fix: notification dashboard by @Davidmattei in https://github.com/ems-project/elasticms/pull/137
* fix: postprocessing json syntax error by @Davidmattei in https://github.com/ems-project/elasticms/pull/140
### Code Refactoring
* refactor: change app namespaces by @theus77 in https://github.com/ems-project/elasticms/pull/134
* refactor: commit_template.txt from maintainers repo by @theus77 in https://github.com/ems-project/elasticms/pull/133
* refactor: helper::Text::superTrim by @theus77 in https://github.com/ems-project/elasticms/pull/130
* refactor: migrate core bundle by @theus77 in https://github.com/ems-project/elasticms/pull/21
* refactor: migrate docs to the documentation repository by @theus77 in https://github.com/ems-project/elasticms/pull/132
* refactor: migrate elasticm web + xlliff by @theus77 in https://github.com/ems-project/elasticms/pull/20
* refactor: migrate elasticms admin by @theus77 in https://github.com/ems-project/elasticms/pull/23
* refactor: migrate emsch repo by @theus77 in https://github.com/ems-project/elasticms/pull/4
* refactor: migrate form bundle by @theus77 in https://github.com/ems-project/elasticms/pull/5
* refactor: migrate submission bundle by @theus77 in https://github.com/ems-project/elasticms/pull/6
* refactor: rector core by @theus77 in https://github.com/ems-project/elasticms/pull/22
### Chores
* chore(github): no pull requests action by @Davidmattei in https://github.com/ems-project/elasticms/pull/127
* chore(mono): phpstan, phpunit, php-cs-fixer by @theus77 in https://github.com/ems-project/elasticms/pull/1
* chore: readonly repositories no ci by @Davidmattei in https://github.com/ems-project/elasticms/pull/129
* chore: release ci by @Davidmattei in https://github.com/ems-project/elasticms/pull/135
