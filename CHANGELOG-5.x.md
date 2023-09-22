# Changelog 5.x

## 5.9.3 (2023-09-17)
### Bug Fixes
* fix(core/api): data not consumed error by @Davidmattei in https://github.com/ems-project/elasticms/pull/608
* fix(core/api): data not consumed multipex fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/612
* fix(core/api): default versions (must always work) by @theus77 in https://github.com/ems-project/elasticms/pull/605
* fix(core/api): index/update refresh wait by @Davidmattei in https://github.com/ems-project/elasticms/pull/610
* fix(core/api): no flash messages by @Davidmattei in https://github.com/ems-project/elasticms/pull/611
* fix(core/field): jsonMenuNestedLink fieldType by @Davidmattei in https://github.com/ems-project/elasticms/pull/613
* fix(core/field): placeholder empty string when not defined by @theus77 in https://github.com/ems-project/elasticms/pull/607
* fix(core/jmn): jmn component improvements and bugfixes by @Davidmattei in https://github.com/ems-project/elasticms/pull/609

## 5.9.2 (2023-09-11)
### Features
* feat(cli): retry on error by @theus77 in https://github.com/ems-project/elasticms/pull/604
* feat(common/fileReader): hash file support and implementation in cli import by @theus77 in https://github.com/ems-project/elasticms/pull/601
* feat(core/api):  new index endpoint in order to init/update or merge/finalize by @theus77 in https://github.com/ems-project/elasticms/pull/602
* feat(core/component): improve json menu nested by @Davidmattei in https://github.com/ems-project/elasticms/pull/595
### Bug Fixes
* fix(core): typo synfony -> symfony by @theus77 in https://github.com/ems-project/elasticms/pull/600
* fix(core/cli): indexed file mapping by @theus77 in https://github.com/ems-project/elasticms/pull/603
* fix(core/dataTable): correct horizontal scroll bar by @Davidmattei in https://github.com/ems-project/elasticms/pull/599
* fix(demo): add template news.ems_link.twig by @theus77 in https://github.com/ems-project/elasticms/pull/598

## 5.9.1 (2023-09-04)
### Bug Fixes
* fix: edit dashboard by @theus77 in https://github.com/ems-project/elasticms/pull/591
### Chores
* chore(demo): implement json_menu_nested component (dashboard) by @theus77 in https://github.com/ems-project/elasticms/pull/592

## 5.9.0 (2023-08-27)
### Features
* feat(admin/ui): bootstrap 5 user profile and forms by @theus77 in https://github.com/ems-project/elasticms/pull/539
* feat(cli/audit): add base url by @theus77 in https://github.com/ems-project/elasticms/pull/589
* feat(core): json menu nested component by @Davidmattei in https://github.com/ems-project/elasticms/pull/577
* feat(core): version mapping and translate analyzer languages by @theus77 in https://github.com/ems-project/elasticms/pull/583
* feat(core/tasks): rework task flow in revision by @Davidmattei in https://github.com/ems-project/elasticms/pull/570
* feat(demo): update configs and skeleton by @theus77 in https://github.com/ems-project/elasticms/pull/579
### Code Refactoring
* refactor(core/admin-ui): copy twigs by @theus77 in https://github.com/ems-project/elasticms/pull/586
* refactor(core/dataTable): all tables should be types by @Davidmattei in https://github.com/ems-project/elasticms/pull/571
* refactor(core/template): controllers use the template_namespace variable (adminUI) by @theus77 in https://github.com/ems-project/elasticms/pull/585

## 5.8.1 (2023-08-18)
### Bug Fixes
* fix(cli/media-sync): typo in warning only metadata file by @theus77 in https://github.com/ems-project/elasticms/pull/572
* fix(common): typo runtime exception by @theus77 in https://github.com/ems-project/elasticms/pull/578
* fix(common/ems-html): print urls keep anchors and empty links by @Davidmattei in https://github.com/ems-project/elasticms/pull/568
* fix(common/emsLink): support json encode by @Davidmattei in https://github.com/ems-project/elasticms/pull/576
* fix(core/dataLink): js issue by @Davidmattei in https://github.com/ems-project/elasticms/pull/584
* fix(demo): debug emsch_error route by @theus77 in https://github.com/ems-project/elasticms/pull/580
* fix(demo/make): init setup should call start by @theus77 in https://github.com/ems-project/elasticms/pull/575
* fix(docker): pg_rename_schema script by @theus77 in https://github.com/ems-project/elasticms/pull/574
### Chores
* chore(make): mysql support and demo load by @Davidmattei in https://github.com/ems-project/elasticms/pull/569

## 5.8.0 (2023-08-01)
### Features
* feat(cli/media-sync): new options by @Davidmattei in https://github.com/ems-project/elasticms/pull/555
* feat(common/api): file endpoint download method + TempFile helper by @Davidmattei in https://github.com/ems-project/elasticms/pull/559
* feat(core/content-type): datalink tooltip in edit mode by @Davidmattei in https://github.com/ems-project/elasticms/pull/567
* feat(core/job): tagged jobs by @Davidmattei in https://github.com/ems-project/elasticms/pull/550
* feat(core/twig): new emsco_get filter (deprecated data filter) by @Davidmattei in https://github.com/ems-project/elasticms/pull/565
* feat(web): security form login (coreApi) by @Davidmattei in https://github.com/ems-project/elasticms/pull/543
* feat: redirect to controller by @theus77 in https://github.com/ems-project/elasticms/pull/562
### Bug Fixes
* fix(cli/webToElasticms): expression array_merge reorder keys by @IsaMic in https://github.com/ems-project/elasticms/pull/558
* fix(common): avoid loading file in memory (use TempFile) by @theus77 in https://github.com/ems-project/elasticms/pull/560
* fix(common): non recursive folder creation by @theus77 in https://github.com/ems-project/elasticms/pull/561
* fix(helper): tempFile load from stream not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/564
### Code Refactoring
* refactor(adminUI): a11y and theme color and tika hello by @theus77 in https://github.com/ems-project/elasticms/pull/537
* refactor(adminUI): add .gitattributes by @Davidmattei in https://github.com/ems-project/elasticms/pull/533
* refactor(adminUI): bootstrap 5 by @theus77 in https://github.com/ems-project/elasticms/pull/531
* refactor(cli/media-library): sync already uploaded by @IsaMic in https://github.com/ems-project/elasticms/pull/556
* refactor(cli/webToElasticms): add reports assetInError by @IsaMic in https://github.com/ems-project/elasticms/pull/557
### Chores
* chore: add makeFile actions for database by @Davidmattei in https://github.com/ems-project/elasticms/pull/563
* chore: cleanup bundles (composer scripts and .gitignore by @Davidmattei in https://github.com/ems-project/elasticms/pull/527

## 5.7.3 (2023-08-01)
### Bug Fixes
* fix(cli): malformed utf8 char by @theus77 in https://github.com/ems-project/elasticms/pull/551
* fix(cli): test if the tika extracted content has some malformed unicode chars by @theus77 in https://github.com/ems-project/elasticms/pull/548
* fix(cli/webToElasticms): migrate media file optimisation by @theus77 in https://github.com/ems-project/elasticms/pull/554
* fix(common/storage): image processor bad horizontal mirroring by @theus77 in https://github.com/ems-project/elasticms/pull/546
* fix(common/storage): limit a range request size to 8MB if not specified by the client by @theus77 in https://github.com/ems-project/elasticms/pull/547
* fix(core/job): schedule jobs not running by ems:job:run by @Davidmattei in https://github.com/ems-project/elasticms/pull/552
* fix(core/notifications): treat all updating contentType root field by @Davidmattei in https://github.com/ems-project/elasticms/pull/544
* fix(core/wysiwyg): correct height iframe by @Davidmattei in https://github.com/ems-project/elasticms/pull/566
* fix(standard/html): copy/paste relative links by @Davidmattei in https://github.com/ems-project/elasticms/pull/553
* fix(standard/html): sanitizer allow emsLinks by @Davidmattei in https://github.com/ems-project/elasticms/pull/545
* fix(standard/html): sanitizer max input length (default 500.000) by @Davidmattei in https://github.com/ems-project/elasticms/pull/549

## 5.7.2 (2023-07-10)
### Bug Fixes
* fix(core): customUserOptionsForm not null but empty string by @Davidmattei in https://github.com/ems-project/elasticms/pull/541
* fix(core): migration command options not correct defined by @Davidmattei in https://github.com/ems-project/elasticms/pull/542
* fix(demo): makeFile docker commands and pwd issue by @Davidmattei in https://github.com/ems-project/elasticms/pull/540

## 5.7.1 (2023-06-30)
### Features
* feat(demo): makeFile configs by @Davidmattei in https://github.com/ems-project/elasticms/pull/536
### Bug Fixes
* fix(common/storage): s3 upload core api not working (chunkSize) by @Davidmattei in https://github.com/ems-project/elasticms/pull/532
* fix(core/api): create managed alias set alias (instanceId) by @Davidmattei in https://github.com/ems-project/elasticms/pull/535
* fix(core/entities): priority entity services by @Davidmattei in https://github.com/ems-project/elasticms/pull/534
* fix(form): the index is always an integer by @theus77 in https://github.com/ems-project/elasticms/pull/530

## 5.7.0 (2023-06-26)
### Features
* feat(cli): hash uid by @theus77 in https://github.com/ems-project/elasticms/pull/488
* feat(client-helper): save assets hash in file by @theus77 in https://github.com/ems-project/elasticms/pull/504
* feat(clientHelper): redirect controller add headers support by @theus77 in https://github.com/ems-project/elasticms/pull/523
* feat(clientHelper/routing): error preview route by @theus77 in https://github.com/ems-project/elasticms/pull/519
* feat(common): new ems:admin:restore command by @theus77 in https://github.com/ems-project/elasticms/pull/515
* feat(common): s3 multipart upload by @theus77 in https://github.com/ems-project/elasticms/pull/490
* feat(common/twig): new function ems_template_exists by @theus77 in https://github.com/ems-project/elasticms/pull/520
* feat(core/mediaLib): ajax toolbar by @Davidmattei in https://github.com/ems-project/elasticms/pull/507
* feat(core/mediaLib): draggable upload by @Davidmattei in https://github.com/ems-project/elasticms/pull/499
* feat(core/mediaLib): infinity scrolling for files by @Davidmattei in https://github.com/ems-project/elasticms/pull/505
* feat(demo/elasticms): define EMSCO_ASSET_CONFIG for overwrite example by @theus77 in https://github.com/ems-project/elasticms/pull/513
* feat(elasticms): new AdminUIBundle by @Davidmattei in https://github.com/ems-project/elasticms/pull/526
* feat(elasticms-web/admin): common probe routes (_readiness, _liveness) by @theus77 in https://github.com/ems-project/elasticms/pull/522
* feat(emscli): add expression merge by @IsaMic in https://github.com/ems-project/elasticms/pull/509
### Bug Fixes
* fix(build): maennchen/zipstream-php fix to 2.4 by @Davidmattei in https://github.com/ems-project/elasticms/pull/529
* fix(cli): tika jar path (#485) by @theus77 in https://github.com/ems-project/elasticms/pull/486
* fix(common): storageManager initFinalize before finalize by @Davidmattei in https://github.com/ems-project/elasticms/pull/501
* fix(common/command): restore command messages by @theus77 in https://github.com/ems-project/elasticms/pull/516
* fix(common/storage): s3 invalid cache key by @Davidmattei in https://github.com/ems-project/elasticms/pull/528
* fix(core): dataTableFactory options by @Davidmattei in https://github.com/ems-project/elasticms/pull/500
* fix(core/mediaLib): max folders 5000 by @Davidmattei in https://github.com/ems-project/elasticms/pull/506
* fix(standards/json): the CLI's audit faces malformed utf8 string by @theus77 in https://github.com/ems-project/elasticms/pull/525
### Code Refactoring
* refactor(core): cant_be_finalized -> emsco_cant_be_finalized by @theus77 in https://github.com/ems-project/elasticms/pull/512
* refactor(core/dataTable): create dataTable types by @Davidmattei in https://github.com/ems-project/elasticms/pull/492
* refactor(core/dataTable): implement dataTable types by @Davidmattei in https://github.com/ems-project/elasticms/pull/493

## 5.6.3 (2023-06-22)
### Bug Fixes
* fix(clientHelper/asset): function emsch_asset assetConfig can be empty (return the file as is) by @theus77 in https://github.com/ems-project/elasticms/pull/521
* fix(common/propertyAccessor): a property can be of any type (bool,  array, int, string, ...) e… by @theus77 in https://github.com/ems-project/elasticms/pull/510
* fix(core/revision): file field mimeType not defined and image asset config for preview by @theus77 in https://github.com/ems-project/elasticms/pull/511
* fix(demo): update bundle.zip by @theus77 in https://github.com/ems-project/elasticms/pull/502
### Code Refactoring
* refactor(demo): elasticms/base-php:8.1-fpm-dev by @theus77 in https://github.com/ems-project/elasticms/pull/503

## 5.6.2 (2023-06-12)
### Bug Fixes
* fix(admin): security /admin routes by @Davidmattei in https://github.com/ems-project/elasticms/pull/495
* fix(cli): don't audit same urls with different parameters more than once by @theus77 in https://github.com/ems-project/elasticms/pull/491
* fix(cli): web-audit too big data by @theus77 in https://github.com/ems-project/elasticms/pull/496
* fix(demo): *.localhost no more internally available by @theus77 in https://github.com/ems-project/elasticms/pull/487
* fix(emsch form): isTokenValid by @theus77 in https://github.com/ems-project/elasticms/pull/498
* fix(emsch request): clean out empty file field by @theus77 in https://github.com/ems-project/elasticms/pull/497

## 5.6.1 (2023-06-05)
### Bug Fixes
* fix(emsch/search): add minimum_should_match only if defined  by @theus77 in https://github.com/ems-project/elasticms/pull/483
* fix: 2 commands with the same id by @theus77 in https://github.com/ems-project/elasticms/pull/474
* fix: job flag as started from created from schedule by @theus77 in https://github.com/ems-project/elasticms/pull/481
* fix: tag is not mandatory in schedule entity by @theus77 in https://github.com/ems-project/elasticms/pull/475
* fix: tika jar path by @theus77 in https://github.com/ems-project/elasticms/pull/485
### Code Refactoring
* refactor(demo): move language switch by @sylver4 in https://github.com/ems-project/elasticms/pull/470

## 5.6.0 (2023-05-30)
### Features
* feat(docker): tika.localhost route by @theus77 in https://github.com/ems-project/elasticms/pull/464
* feat(ems-client): datalink to a media_file (webToElasticms) by @IsaMic in https://github.com/ems-project/elasticms/pull/456
* feat(search): filter's clause config and search minimum_should_match config by @theus77 in https://github.com/ems-project/elasticms/pull/463
* feat(skeleton): SAML authentication by @Davidmattei in https://github.com/ems-project/elasticms/pull/469
* feat(twig): ems_asset_get_content by @theus77 in https://github.com/ems-project/elasticms/pull/458
* feat(xliff): xliff in json by @theus77 in https://github.com/ems-project/elasticms/pull/465
* feat: create new document on emscli:file-reader:import by @theus77 in https://github.com/ems-project/elasticms/pull/473
* feat: iterate on wild char property path by @theus77 in https://github.com/ems-project/elasticms/pull/466
* feat: remote schedule job by @theus77 in https://github.com/ems-project/elasticms/pull/467
* feat: tika on media sync command by @theus77 in https://github.com/ems-project/elasticms/pull/454
### Bug Fixes
* fix(cli): loading issue (serializer) by @theus77 in https://github.com/ems-project/elasticms/pull/455
* fix(wysiwyg): dashboard picker (double : ) by @Davidmattei in https://github.com/ems-project/elasticms/pull/472
* fix: if getNodeByName not found by @theus77 in https://github.com/ems-project/elasticms/pull/460
* fix: no base url on asset_file_path by @theus77 in https://github.com/ems-project/elasticms/pull/459
* fix: old revision might not have hash by @theus77 in https://github.com/ems-project/elasticms/pull/457
* fix: renew the csrf token on each submit by @theus77 in https://github.com/ems-project/elasticms/pull/471
* fix: update demo by @theus77 in https://github.com/ems-project/elasticms/pull/461
### Code Refactoring
* refactor(elasticm-cli): mediaFile() return by @IsaMic in https://github.com/ems-project/elasticms/pull/462

## 5.5.0 (2023-05-02)
### Features
* feat(common): storeData file system by @theus77 in https://github.com/ems-project/elasticms/pull/451
* feat(core): managed alias api and commands by @theus77 in https://github.com/ems-project/elasticms/pull/438
* feat(core/user): add the WYSIWYG profile in the datatable view by @theus77 in https://github.com/ems-project/elasticms/pull/435
* feat(emsco/env): rebuild all flag by @theus77 in https://github.com/ems-project/elasticms/pull/428
* feat(form): reorder form fields by @theus77 in https://github.com/ems-project/elasticms/pull/452
* feat(store): emsch store by @theus77 in https://github.com/ems-project/elasticms/pull/448

## 5.4.4 (2023-05-02)
### Bug Fixes
* fix(audit): issue in API sometimes by @theus77 in https://github.com/ems-project/elasticms/pull/449
* fix(core): render textare as non-html and replace nl by br (|nl2br) by @theus77 in https://github.com/ems-project/elasticms/pull/453
* fix(demo): manage alias not ready by @theus77 in https://github.com/ems-project/elasticms/pull/443
* fix(form): form children class by @theus77 in https://github.com/ems-project/elasticms/pull/447
* fix(tika): in case of corrupted document sent to tika by @theus77 in https://github.com/ems-project/elasticms/pull/450
* fix: asFilename by @theus77 in https://github.com/ems-project/elasticms/pull/445
* fix: image not found by dompdf by @theus77 in https://github.com/ems-project/elasticms/pull/442

## 5.4.3 (2023-04-24)
### Bug Fixes
* fix(core): choice field type's helptext & placeholder by @theus77 in https://github.com/ems-project/elasticms/pull/436
* fix(core/revision): align revision title and edit title by @theus77 in https://github.com/ems-project/elasticms/pull/433
* fix(docker): user in docker scripts by @theus77 in https://github.com/ems-project/elasticms/pull/437
* fix(form): add not lcfirst label (for translation in de) by @theus77 in https://github.com/ems-project/elasticms/pull/441
* fix(xliff): leading or ending spaces does not matter by @theus77 in https://github.com/ems-project/elasticms/pull/434
### Code Refactoring
* refactor: documentations -> documentation by @theus77 in https://github.com/ems-project/elasticms/pull/440
### Chores
* chore(demo): update from prod by @theus77 in https://github.com/ems-project/elasticms/pull/439

## 5.4.2 (2023-04-17)
### Bug Fixes
* fix(core): js console issue (counter) by @theus77 in https://github.com/ems-project/elasticms/pull/431
* fix(web-audit): audit clear cache by @theus77 in https://github.com/ems-project/elasticms/pull/429
* fix: ALTER TABLE revision CHANGE ouuid ouuid VARCHAR(255) DEFAULT NULL COLLATE `utf8_bin` by @theus77 in https://github.com/ems-project/elasticms/pull/430

## 5.4.1 (2023-04-11)
### Features
* feat(core): use query label as placeholder in object picker/data link field type by @theus77 in https://github.com/ems-project/elasticms/pull/421
### Bug Fixes
* fix(common/api): default env admin url and token by @theus77 in https://github.com/ems-project/elasticms/pull/425
* fix(core): textarea and input counter by @theus77 in https://github.com/ems-project/elasticms/pull/427
* fix(core): use the query search label by @theus77 in https://github.com/ems-project/elasticms/pull/420
* fix(emsco/emsLink): css width flex -> inline-flex by @Davidmattei in https://github.com/ems-project/elasticms/pull/419
### Code Refactoring
* refactor(cli): tika client use curl by @theus77 in https://github.com/ems-project/elasticms/pull/424
* refactor(core): new labels by @theus77 in https://github.com/ems-project/elasticms/pull/423
* refactor(twig): emsch_uuid -> ems_uuid by @theus77 in https://github.com/ems-project/elasticms/pull/426

## 5.4.0 (2023-03-31)
### Features
* feat(cli): file-reader importer command by @theus77 in https://github.com/ems-project/elasticms/pull/408
* feat(cli/media-lib-sync): import only missing document (refactor) by @theus77 in https://github.com/ems-project/elasticms/pull/406
* feat(cli/media-lib-sync): new command mediaFile Sync by @IsaMic in https://github.com/ems-project/elasticms/pull/373
* feat(cli/media-lib-sync): substr media synch command with expression by @theus77 in https://github.com/ems-project/elasticms/pull/412
* feat(cli/media-lib-sync): update media document with meta by @theus77 in https://github.com/ems-project/elasticms/pull/402
* feat(common/twig): ems_preg_match filter by @theus77 in https://github.com/ems-project/elasticms/pull/409
* feat(demo): component templates by @theus77 in https://github.com/ems-project/elasticms/pull/385
* feat(demo): styleset preview configured by @theus77 in https://github.com/ems-project/elasticms/pull/387
* feat(emsco): implementation emsco_display by @Davidmattei in https://github.com/ems-project/elasticms/pull/404
* feat(emsco/revision): display label (userLocale) by @Davidmattei in https://github.com/ems-project/elasticms/pull/401
* feat(emsco/user): command update option by @Davidmattei in https://github.com/ems-project/elasticms/pull/390
* feat(emsco/user): user update command allowed_configure_wysiwyg by @Davidmattei in https://github.com/ems-project/elasticms/pull/396
* feat(emsco/wysiwyg): ckeditor translations by @Davidmattei in https://github.com/ems-project/elasticms/pull/415
### Bug Fixes
* fix(cli): require symfony/http-client by @theus77 in https://github.com/ems-project/elasticms/pull/414
* fix(common/twig): dow crawler and preg match by @theus77 in https://github.com/ems-project/elasticms/pull/411
* fix(emsco): modal tab index and select2 by @Davidmattei in https://github.com/ems-project/elasticms/pull/399
* fix(emsco/emsLink): UI display emsLinks in modal or small screens by @Davidmattei in https://github.com/ems-project/elasticms/pull/405
* fix(emsco/revision):  add translation 'add-to-release' by @theus77 in https://github.com/ems-project/elasticms/pull/413
* fix(emsco/revision): finalization version broken by @Davidmattei in https://github.com/ems-project/elasticms/pull/410
* fix(emsco/user): profile remove update simplified UI by @Davidmattei in https://github.com/ems-project/elasticms/pull/407
* fix(emsco/wysiwyg): dashboard modal object/file select emsId by @Davidmattei in https://github.com/ems-project/elasticms/pull/416
* fix(xliff): continuous segments (#372) by @Davidmattei in https://github.com/ems-project/elasticms/pull/418
### Code Refactoring
* refactor(common): remove \EMS\CommonBundle\Common\Document by @Davidmattei in https://github.com/ems-project/elasticms/pull/403
* refactor(demo): promo website by @theus77 in https://github.com/ems-project/elasticms/pull/391
* refactor(emsco/tasks): only use "tasks" tab by @Davidmattei in https://github.com/ems-project/elasticms/pull/397
### Chores
* chore(build): prepare 5.4 by @Davidmattei in https://github.com/ems-project/elasticms/pull/417

## 5.3.5 (2023-03-27)
### Bug Fixes
* fix(emsch_asset): asset directory by @theus77 in https://github.com/ems-project/elasticms/pull/395
* fix(emsco/submission): controller spreadsheet generator not injected by @theus77 in https://github.com/ems-project/elasticms/pull/392
* fix(user profile): disallow user to edit wysiwyg profile by @theus77 in https://github.com/ems-project/elasticms/pull/394
* fix(xliff): continuous segments by @theus77 in https://github.com/ems-project/elasticms/pull/372
* fix: rawData type in postFinalizeTreatment by @theus77 in https://github.com/ems-project/elasticms/pull/393

## 5.3.4 (2023-03-20)
### Bug Fixes
* fix(ems): login page (js error)  by @Davidmattei in https://github.com/ems-project/elasticms/pull/381
* fix(emsch): local push empty contentType by @Davidmattei in https://github.com/ems-project/elasticms/pull/379
* fix(emsco): ckconfig json menu nested by @Davidmattei in https://github.com/ems-project/elasticms/pull/382
* fix(emsco): recompute deep (revert #366) by @Davidmattei in https://github.com/ems-project/elasticms/pull/383
* fix(emsf): allow domains as string[] in json format by @theus77 in https://github.com/ems-project/elasticms/pull/380
* fix(iframe preview): styleset preview height by @theus77 in https://github.com/ems-project/elasticms/pull/386
### Chores
* chore(deprecation): define third parameter for choiceList by @Davidmattei in https://github.com/ems-project/elasticms/pull/384

## 5.3.3 (2023-03-14)
### Bug Fixes
* fix(ems): spreadsheet csv separtor config by @Davidmattei in https://github.com/ems-project/elasticms/pull/378
* fix(emsf): align configs web/admin by @theus77 in https://github.com/ems-project/elasticms/pull/377
* fix(emsf): form configs by @theus77 in https://github.com/ems-project/elasticms/pull/375
### Code Refactoring
* refactor(emsco/recompute): migrate a notification to latest revision by @theus77 in https://github.com/ems-project/elasticms/pull/369

## 5.3.2 (2023-03-07)
### Features
* feat(emsch/routing): search one and http exceptions by @theus77 in https://github.com/ems-project/elasticms/pull/362
### Bug Fixes
* fix(emsch/templating): handling error EMSCH_TEMPLATES by @theus77 in https://github.com/ems-project/elasticms/pull/360
* fix(emsco/choice): multiple keyword index error by @Davidmattei in https://github.com/ems-project/elasticms/pull/365
* fix(emsco/export): solve a circular ref in form JSON export by @theus77 in https://github.com/ems-project/elasticms/pull/361
* fix(emsco/forms): default data in emco_form by @theus77 in https://github.com/ems-project/elasticms/pull/358
* fix(emsco/json-menu-nested): link unique array_diff error by @Davidmattei in https://github.com/ems-project/elasticms/pull/367
* fix(emsco/notifications): dateTimes not initialize by @theus77 in https://github.com/ems-project/elasticms/pull/359
* fix(emsco/recompute): set finalization fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/366
* fix(emsco/revision): search revision deleted by @Davidmattei in https://github.com/ems-project/elasticms/pull/364
### Code Refactoring
* refactor(demo): migrate promo website by @theus77 in https://github.com/ems-project/elasticms/pull/363

## 5.3.1 (2023-02-28)
### Bug Fixes
* fix(common/api): EMS_BACKEND_API_VERIFY by @Davidmattei in https://github.com/ems-project/elasticms/pull/351
* fix: recompute theme and useroptiontype specific id by @theus77 in https://github.com/ems-project/elasticms/pull/352

## 5.3.0 (2023-02-27)
### Features
* feat(admin): emsch local enabled in dev mode by @Davidmattei in https://github.com/ems-project/elasticms/pull/294
* feat(cli): asset2document migrate by @theus77 in https://github.com/ems-project/elasticms/pull/333
* feat(common): EMS_ELASTICSEARCH_CONNECTION_POOL by @theus77 in https://github.com/ems-project/elasticms/pull/311
* feat(common): ems:admin:command by @theus77 in https://github.com/ems-project/elasticms/pull/292
* feat(contentType): new setting hide sidebar by @theus77 in https://github.com/ems-project/elasticms/pull/338
* feat(contentType): post processing "rootObject" by @theus77 in https://github.com/ems-project/elasticms/pull/343
* feat(core): emsch env by @Davidmattei in https://github.com/ems-project/elasticms/pull/349
* feat(core): form entity and form field type by @theus77 in https://github.com/ems-project/elasticms/pull/323
* feat(core): form entity, reusing fields by @theus77 in https://github.com/ems-project/elasticms/pull/300
* feat(core/mediaLibrary): add templating by @Davidmattei in https://github.com/ems-project/elasticms/pull/348
* feat(core/user): custom user options by @theus77 in https://github.com/ems-project/elasticms/pull/327
* feat(dashboard): browsers by @Davidmattei in https://github.com/ems-project/elasticms/pull/310
* feat(datatable): group table item actions by @Davidmattei in https://github.com/ems-project/elasticms/pull/309
* feat(demo): update demo by @theus77 in https://github.com/ems-project/elasticms/pull/331
* feat(emsch/routing): response headers for asset action by @Davidmattei in https://github.com/ems-project/elasticms/pull/340
* feat(emsch/search): query_search option by @Davidmattei in https://github.com/ems-project/elasticms/pull/330
* feat(web): environment var EMSCH_LOCAL_PATH by @Davidmattei in https://github.com/ems-project/elasticms/pull/318
* feat(web): upgrade endroidQrCodeBundle from 3.x -> 4.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/319
* feat: forms in views by @theus77 in https://github.com/ems-project/elasticms/pull/350
### Bug Fixes
* fix(contentType): virtual field + propagate postprocessing+ isValid in FormType by @theus77 in https://github.com/ems-project/elasticms/pull/344
* fix(core): field-type manager is not an entity service by @Davidmattei in https://github.com/ems-project/elasticms/pull/329
* fix(core-api): default query search name by @theus77 in https://github.com/ems-project/elasticms/pull/304
* fix(core/circles): circles field out of sync by @theus77 in https://github.com/ems-project/elasticms/pull/337
* fix(core/jsonFieldType): validation and prettyPrint by @Davidmattei in https://github.com/ems-project/elasticms/pull/347
* fix(core/querySearch): paramConverter broken by @Davidmattei in https://github.com/ems-project/elasticms/pull/341
* fix(demo): UID is a shell read only variable, but UID is not defined as envronment variable by @theus77 in https://github.com/ems-project/elasticms/pull/336
* fix(demo): update assets / configs by @Davidmattei in https://github.com/ems-project/elasticms/pull/320
* fix(emsch/search): query_search replace request values by @Davidmattei in https://github.com/ems-project/elasticms/pull/345
* fix(emsch_search): query values are prior against request by @theus77 in https://github.com/ems-project/elasticms/pull/335
* fix(form): no entityService and missing migrations by @Davidmattei in https://github.com/ems-project/elasticms/pull/317
* fix(notifications): outbox table not showing results by @Davidmattei in https://github.com/ems-project/elasticms/pull/346
* fix(storage): header noindex for immutable responses by @theus77 in https://github.com/ems-project/elasticms/pull/339

## 5.2.7 (2023-03-31)
### Bug Fixes
* fix(xliff): continuous segments (#372) by @Davidmattei in https://github.com/ems-project/elasticms/pull/418

## 5.2.6 (2023-03-06)
### Bug Fixes
* fix(emsco/revision): search revision deleted by @Davidmattei in https://github.com/ems-project/elasticms/pull/364

## 5.2.5 (2023-02-25)
### Bug Fixes
* fix(admin): doctrine migrations in dev mode by @Davidmattei in https://github.com/ems-project/elasticms/pull/332
* fix(wysiwyg): paste-not-working by @Davidmattei in https://github.com/ems-project/elasticms/pull/326

## 5.2.4 (2023-02-13)
### Bug Fixes
* fix(common): ems_webalize accii folding german by @Davidmattei in https://github.com/ems-project/elasticms/pull/322
* fix(docker): expose cluster by @Davidmattei in https://github.com/ems-project/elasticms/pull/308
* fix(environment): call clearCache by @Davidmattei in https://github.com/ems-project/elasticms/pull/324
* fix(jsonMenuNested): label not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/316
* fix(revision): paste not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/315
### Chores
* chore: phpcs & phpstan by @Davidmattei in https://github.com/ems-project/elasticms/pull/325

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
