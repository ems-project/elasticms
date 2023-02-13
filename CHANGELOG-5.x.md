# Changelog 5.x

## 5.2.4 (2023-02-13)
### Bug Fixes
* fix(common): ems_webalize accii folding german by @Davidmattei in https://github.com/ems-project/elasticms/pull/322
* fix(docker): expose cluster by @Davidmattei in https://github.com/ems-project/elasticms/pull/308
* fix(jsonMenuNested): label not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/316
* fix(revision): paste not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/315

## 5.2.3 (2023-02-01)
### Bug Fixes
* fix(core): emsco_skip_notification twig function by @theus77 in https://github.com/ems-project/elasticms/pull/306

## 5.2.2 (2023-01-30)
### Bug Fixes
* fix(admin-api): do not backup jobs by @theus77 in https://github.com/ems-project/elasticms/pull/293
* fix(admin-api): update views and actions keep id by @Davidmattei in https://github.com/ems-project/elasticms/pull/296
* fix(core-twig): in_my_circles ($circles) must be of type array|string by @Davidmattei in https://github.com/ems-project/elasticms/pull/301
* fix(jsonMenu): add convert to nested helper function by @Davidmattei in https://github.com/ems-project/elasticms/pull/299
* fix(jsonMenu): recompute and already assigned by @Davidmattei in https://github.com/ems-project/elasticms/pull/295
* fix(recompute): deep recompute skip deleted fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/297
* fix(revision): detail broken if field named 'children' by @Davidmattei in https://github.com/ems-project/elasticms/pull/298

## 5.2.1 (2023-01-25)
### Bug Fixes
* fix(core): ajax modal 'enter' in textarea closes modal  by @Davidmattei in https://github.com/ems-project/elasticms/pull/286
* fix(core): ajax modal pick file browser by @Davidmattei in https://github.com/ems-project/elasticms/pull/288
* fix(submission): allow reply to header in email request/handler by @theus77 in https://github.com/ems-project/elasticms/pull/285
* fix(submission): skip submit by @theus77 in https://github.com/ems-project/elasticms/pull/287

## 5.2.0 (2023-01-24)
### Features
* feat(audit): delete non updated audit documents by @theus77 in https://github.com/ems-project/elasticms/pull/256
* feat(cli): save audit by @theus77 in https://github.com/ems-project/elasticms/pull/267
* feat(common): ems_ascii_folding by @theus77 in https://github.com/ems-project/elasticms/pull/262
* feat(dataTable): public datatable routes by @theus77 in https://github.com/ems-project/elasticms/pull/280
* feat(emsch-local): ems asset by @theus77 in https://github.com/ems-project/elasticms/pull/251
* feat(helper): html standard with sanitizer by @Davidmattei in https://github.com/ems-project/elasticms/pull/260
* feat(media lib): defaultValue and searchQuery by @Davidmattei in https://github.com/ems-project/elasticms/pull/268
* feat(revision): delete by query by @theus77 in https://github.com/ems-project/elasticms/pull/254
* feat(skeleton): redirect to path (BinaryFileResponse) by @theus77 in https://github.com/ems-project/elasticms/pull/252
* feat(submission): multipart handler new endpoint option headers by @theus77 in https://github.com/ems-project/elasticms/pull/261
* feat(submission): skip submit by @theus77 in https://github.com/ems-project/elasticms/pull/273
* feat(wyiswyg): paste html cleaner by @Davidmattei in https://github.com/ems-project/elasticms/pull/264
### Bug Fixes
* fix(audit): duplicate ouuids by @theus77 in https://github.com/ems-project/elasticms/pull/272
* fix(audit): redirect to non parsable url by @theus77 in https://github.com/ems-project/elasticms/pull/279
* fix(cli): ../ url by @theus77 in https://github.com/ems-project/elasticms/pull/253
* fix(cli): avoid deleting in dry-run and just logs thing in case of error o… by @theus77 in https://github.com/ems-project/elasticms/pull/266
* fix(cli): define all timeouts in cache manager by @theus77 in https://github.com/ems-project/elasticms/pull/255
* fix(cli): dont log password in audit documents by @theus77 in https://github.com/ems-project/elasticms/pull/257
* fix(cli): require doctrine annotations by @Davidmattei in https://github.com/ems-project/elasticms/pull/246
* fix(ems_webalize): don't drop number by default by @theus77 in https://github.com/ems-project/elasticms/pull/283
* fix(jsonMenuNested): copy all broken by @Davidmattei in https://github.com/ems-project/elasticms/pull/281
* fix(rebuild): publishing multiple revisions in default env by @Davidmattei in https://github.com/ems-project/elasticms/pull/282
### Code Refactoring
* refactor(demo): improved demo's robots.txt by @theus77 in https://github.com/ems-project/elasticms/pull/250
* refactor(ui): new logo by @theus77 in https://github.com/ems-project/elasticms/pull/278
### Builds
* build: add demo to release by @Davidmattei in https://github.com/ems-project/elasticms/pull/284
### Chores
* chore(docker): improve dev docker by @Davidmattei in https://github.com/ems-project/elasticms/pull/265

## 5.1.2 (2023-01-06)
### Bug Fixes
* fix(common/jmn): string spec for label on construct by @coppee in https://github.com/ems-project/elasticms/pull/249
* fix(datalinks): referrer-ems-id by @Davidmattei in https://github.com/ems-project/elasticms/pull/248
* fix(cli): require doctrine annotations by @Davidmattei in https://github.com/ems-project/elasticms/pull/246
* fix: allow numbers, bool and null in importData by @theus77 in https://github.com/ems-project/elasticms/pull/242
### Code Refactoring
* refactor: better raw data by @theus77 in https://github.com/ems-project/elasticms/pull/243

## 5.1.1 (2023-01-04)
### Bug Fixes
* fix: psr/simple cache 2.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/239

## 5.1.0 (2023-01-03)
### Features
* feat(action): import data action by @Davidmattei in https://github.com/ems-project/elasticms/pull/235
* feat(action): support export spreadsheet by @Davidmattei in https://github.com/ems-project/elasticms/pull/230
* feat(demo): create minio demo's bucket at docker compose up by @theus77 in https://github.com/ems-project/elasticms/pull/218
* feat(demo): npx script by @theus77 in https://github.com/ems-project/elasticms/pull/217
* feat(ui): ajax modal enter submit by @Davidmattei in https://github.com/ems-project/elasticms/pull/233
* feat: audit with htpassword by @theus77 in https://github.com/ems-project/elasticms/pull/210
* feat: export only configs or only documents in backup command by @theus77 in https://github.com/ems-project/elasticms/pull/214
* feat: left menu create job link by @theus77 in https://github.com/ems-project/elasticms/pull/195
* feat: opensearch 2 by @theus77 in https://github.com/ems-project/elasticms/pull/192
* feat: schema rename script by @theus77 in https://github.com/ems-project/elasticms/pull/206
* feat: webalize file name in order to avoid upper chars in assets url by @theus77 in https://github.com/ems-project/elasticms/pull/213
### Bug Fixes
* fix(admin/job): returns 500 exception by @Davidmattei in https://github.com/ems-project/elasticms/pull/211
* fix(api/admin): logging by @Davidmattei in https://github.com/ems-project/elasticms/pull/212
* fix(content-type): remove error on creation by @Davidmattei in https://github.com/ems-project/elasticms/pull/222
* fix(contenttype): ems:contenttype:export walkRecursive by @Davidmattei in https://github.com/ems-project/elasticms/pull/219
* fix(environment): 500 error legacy search not granted publication by @Davidmattei in https://github.com/ems-project/elasticms/pull/224
* fix(notification): treat notification not refreshing by @Davidmattei in https://github.com/ems-project/elasticms/pull/234
* fix(tasks): planned ids array_values by @Davidmattei in https://github.com/ems-project/elasticms/pull/208
* fix(unpublish): count all environments (include default) by @Davidmattei in https://github.com/ems-project/elasticms/pull/223
* fix(web-audit): not parsable url during web audit by @theus77 in https://github.com/ems-project/elasticms/pull/207
* fix(xliff): inserter empty tags by @theus77 in https://github.com/ems-project/elasticms/pull/231
* fix: catch json_decode error by @coppee in https://github.com/ems-project/elasticms/pull/227
* fix: debug demo's overviews by @theus77 in https://github.com/ems-project/elasticms/pull/193
* fix: ensure we have an object for the reverse_nested key. by @theus77 in https://github.com/ems-project/elasticms/pull/237
* fix: target path redirect not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/220
### Code Refactoring
* refactor(controller): remove argument for setContainer by @Davidmattei in https://github.com/ems-project/elasticms/pull/229
* refactor(routing): xml files by @Davidmattei in https://github.com/ems-project/elasticms/pull/221
* refactor: docker from demo by @theus77 in https://github.com/ems-project/elasticms/pull/215
* refactor: don't call rector in phpall (too heavy) by @theus77 in https://github.com/ems-project/elasticms/pull/216
### Tests
* test: baseline first test case by @theus77 in https://github.com/ems-project/elasticms/pull/225
### Builds
* build(release): add changelog 5.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/228
### Continuous Integrations
* ci(release): improve code and changeLog command by @Davidmattei in https://github.com/ems-project/elasticms/pull/198
### Chores
* chore(demo): update npm by @theus77 in https://github.com/ems-project/elasticms/pull/232

## 5.0.1 (2022-12-21)
### Bug Fixes
* fix: cache missed or hit in the emsco flashbag by @theus77 in https://github.com/ems-project/elasticms/pull/197
* fix: double ems.command.admin.get by @Davidmattei in https://github.com/ems-project/elasticms/pull/199
* fix: noinit options by @theus77 in https://github.com/ems-project/elasticms/pull/196

## 5.0.0 (2022-12-20)
### Features
* feat(media-library): add media library component by @Davidmattei in https://github.com/ems-project/elasticms/pull/161
* feat(web-migration): array to json object by @IsaMic in https://github.com/ems-project/elasticms/pull/168
* feat: already connected by @theus77 in https://github.com/ems-project/elasticms/pull/180
* feat: audit description by @theus77 in https://github.com/ems-project/elasticms/pull/152
* feat: audit referer label by @theus77 in https://github.com/ems-project/elasticms/pull/163
* feat: backup from CLI by @theus77 in https://github.com/ems-project/elasticms/pull/181
* feat: elk8 by @theus77 in https://github.com/ems-project/elasticms/pull/187
* feat: get version command by @theus77 in https://github.com/ems-project/elasticms/pull/184
* feat: new copywriter role by @theus77 in https://github.com/ems-project/elasticms/pull/170
* feat: switch default env command by @theus77 in https://github.com/ems-project/elasticms/pull/146
* feat: tasks version by @Davidmattei in https://github.com/ems-project/elasticms/pull/185
### Bug Fixes
* fix: $emptyExtractor by @theus77 in https://github.com/ems-project/elasticms/pull/148
* fix: int translation's key => string by @theus77 in https://github.com/ems-project/elasticms/pull/189
* fix: label key might be an integer by @theus77 in https://github.com/ems-project/elasticms/pull/166
* fix: options can be null by @theus77 in https://github.com/ems-project/elasticms/pull/165
* fix: protected attached indexes by @theus77 in https://github.com/ems-project/elasticms/pull/153
### Code Refactoring
* refactor(file-upload): remove bootstrap-fileinput by @Davidmattei in https://github.com/ems-project/elasticms/pull/160
* refactor: demo by @theus77 in https://github.com/ems-project/elasticms/pull/169
* refactor: json by @theus77 in https://github.com/ems-project/elasticms/pull/183
### Builds
* build: enable splitter for demo by @Davidmattei in https://github.com/ems-project/elasticms/pull/172
* build: release improvements by @Davidmattei in https://github.com/ems-project/elasticms/pull/191
### Chores
* chore: add CODEOWNERS file by @Davidmattei in https://github.com/ems-project/elasticms/pull/177
* chore: add autolabeler config by @Davidmattei in https://github.com/ems-project/elasticms/pull/173
* chore: composer validation by @Davidmattei in https://github.com/ems-project/elasticms/pull/190
* chore: documentation repositories by @Davidmattei in https://github.com/ems-project/elasticms/pull/171
* chore: moved to doc project by @theus77 in https://github.com/ems-project/elasticms/pull/182
* chore: no pull requests for demo (readonly) by @Davidmattei in https://github.com/ems-project/elasticms/pull/176
* chore: php 8.1 by @Davidmattei in https://github.com/ems-project/elasticms/pull/149
