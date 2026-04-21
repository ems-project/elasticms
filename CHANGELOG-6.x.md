# Changelog 6.x

## 6.9.11 (2026-04-16)
### Features
* feat(admin): add tags data-testid for test by @IsaMic in https://github.com/ems-project/elasticms/pull/1637
* feat(admin/contenttype): add html unwrap transformer by @Davidmattei in https://github.com/ems-project/elasticms/pull/1656
* feat(admin/contenttype): add unwrap for html attribute transformer by @Davidmattei in https://github.com/ems-project/elasticms/pull/1652
* feat(web/search): save emsch search response in cache by @theus77 in https://github.com/ems-project/elasticms/pull/1658
### Bug Fixes
* fix(admin): audit table full width by @Davidmattei in https://github.com/ems-project/elasticms/pull/1638
* fix(admin): data link for archived documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1649
* fix(admin/dashboard): add data-skip-click-event attribute by @Davidmattei in https://github.com/ems-project/elasticms/pull/1653
* fix(admin/form-submissions): add batch size on delete (avoid out of memory) by @theus77 in https://github.com/ems-project/elasticms/pull/1647
* fix(admin/menu): invalid test id on dashboard menu by @Davidmattei in https://github.com/ems-project/elasticms/pull/1661
* fix(admin/post-processing): add revision environments in context by @Davidmattei in https://github.com/ems-project/elasticms/pull/1650
* fix(admin/storage): add getFileObject on storageManager and fileService by @theus77 in https://github.com/ems-project/elasticms/pull/1648
* fix(common/search): correct serialization suggest by @Davidmattei in https://github.com/ems-project/elasticms/pull/1639
* fix(common/spreadsheets): generate normalized xlsx files by @theus77 in https://github.com/ems-project/elasticms/pull/1646
* fix(web/asset): add $skipUnzip flag to use the $saveDir as $publishPath by @theus77 in https://github.com/ems-project/elasticms/pull/1662
* fix(web/routing): avoid to break everything if one route is broken by @theus77 in https://github.com/ems-project/elasticms/pull/1654
* fix(web/search): empty suggest in search by @Davidmattei in https://github.com/ems-project/elasticms/pull/1664

## 6.9.10 (2026-04-07)
### Bug Fixes
* fix(web): not found page should return a 404 code by @theus77 in https://github.com/ems-project/elasticms/pull/1633

## 6.9.9 (2026-03-23)
### Features
* feat(common/archive): new functions add/replace and remove archive by @IsaMic in https://github.com/ems-project/elasticms/pull/1627
* feat(common/spreadsheet): cell type option 's' for string by @IsaMic in https://github.com/ems-project/elasticms/pull/1626
* feat(demo): json error template (+add content if 400 bad request) by @theus77 in https://github.com/ems-project/elasticms/pull/1617
### Bug Fixes
* fix(web): local push route without query by @theus77 in https://github.com/ems-project/elasticms/pull/1622

## 6.9.8 (2026-02-18)
### Features
* feat(web/security): sso oauth2 ping identity by @Davidmattei in https://github.com/ems-project/elasticms/pull/1569
### Bug Fixes
* fix(admin/environment): color argument can be null by @Davidmattei in https://github.com/ems-project/elasticms/pull/1545
* fix(admin/user):  authToken user provider not using group defined roles by @theus77 in https://github.com/ems-project/elasticms/pull/1575
* fix(common/es): include ems internal fields in _source by @theus77 in https://github.com/ems-project/elasticms/pull/1567
* fix(common/image): the resolution might be set in the EXIF meta (but not only) by @theus77 in https://github.com/ems-project/elasticms/pull/1553
* fix(demo): traefik v3 update hostRegex admin and web by @Davidmattei in https://github.com/ems-project/elasticms/pull/1563
* fix(docker): minio do not use semver by @theus77 in https://github.com/ems-project/elasticms/pull/1578
* fix(web/routing): routing include only required field by @Davidmattei in https://github.com/ems-project/elasticms/pull/1600
* fix(web/security): ping identity http basic auth by @Davidmattei in https://github.com/ems-project/elasticms/pull/1585
* fix(web/security): saml redirect with RelayState by @Davidmattei in https://github.com/ems-project/elasticms/pull/1589
* fix(web/submission): email handler support priority by @theus77 in https://github.com/ems-project/elasticms/pull/1539
### Code Refactoring
* refactor: data-test to data-testid by @theus77 in https://github.com/ems-project/elasticms/pull/1584
### Tests
* test(e2e-tests): new data-test attributes by @theus77 in https://github.com/ems-project/elasticms/pull/1581

## 6.9.7 (2026-01-26)
### Features
* feat(demo): resolve deprecations and update by @Davidmattei in https://github.com/ems-project/elasticms/pull/1529
### Bug Fixes
* fix(common/encoding): ems_anti_spam handle multiple phone numbers by @theus77 in https://github.com/ems-project/elasticms/pull/1523
* fix(core/form-submission): avoid doctrine out of memory exception by @theus77 in https://github.com/ems-project/elasticms/pull/1518
* fix(xliff): useless namespace + trans-unit without id by @theus77 in https://github.com/ems-project/elasticms/pull/1526
* fix(xliff/extract): ctype in bx ex by @theus77 in https://github.com/ems-project/elasticms/pull/1524
* fix(xliff/extract): format error by @theus77 in https://github.com/ems-project/elasticms/pull/1521
* fix(xliff/extract): wrong final segment by @theus77 in https://github.com/ems-project/elasticms/pull/1520

## 6.9.6 (2026-01-13)
### Bug Fixes
* fix(admin/aliases): allow to remove all indexes from a managed aliases by @theus77 in https://github.com/ems-project/elasticms/pull/1509
* fix(admin/js): nested modal datatable search not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/1513
* fix(admin/view): title: '{name} of {plural}' by @theus77 in https://github.com/ems-project/elasticms/pull/1502
* fix(admin/webalize): align regex with web by @Davidmattei in https://github.com/ems-project/elasticms/pull/1501
* fix(common/encoding): ems_anti_spam wrong encoding on tel: href by @theus77 in https://github.com/ems-project/elasticms/pull/1505
* fix(web/security): security listener was starting session by getting token by @theus77 in https://github.com/ems-project/elasticms/pull/1514
* fix(web/twig):  cache key add count and last published for index updates by @theus77 in https://github.com/ems-project/elasticms/pull/1508

## 6.9.5 (2025-12-23)
### Features
* feat(core/api): add data environments endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1496
* feat(core/api): add meta findAll drafts endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1488
* feat(core/api): add meta get environments endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1491
* feat(core/api): new meta attach alias to environment endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1493
* feat(core/cli): (un)lock all commands by @Davidmattei in https://github.com/ems-project/elasticms/pull/1492
* feat(core/cli): create environment new position option by @Davidmattei in https://github.com/ems-project/elasticms/pull/1494
* feat(core/cli): create environment new role and color option by @Davidmattei in https://github.com/ems-project/elasticms/pull/1497
* feat(core/job): show created user in job overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/1490
### Bug Fixes
* fix(admin/view): calendar,criteria gallery findChildByName by @Davidmattei in https://github.com/ems-project/elasticms/pull/1500
* fix(common): reference to CoreBundle namespace are not allowed by @Davidmattei in https://github.com/ems-project/elasticms/pull/1487
* fix(core/api): job status return status value by @Davidmattei in https://github.com/ems-project/elasticms/pull/1489
* fix(core/api): run command output can be null by @Davidmattei in https://github.com/ems-project/elasticms/pull/1495
* fix(web): rollback asset path must take the baseUrl by @theus77 in https://github.com/ems-project/elasticms/pull/1486

## 6.9.4 (2025-12-10)
### Bug Fixes
* fix(admin/api): finalize draft without rawData by @Davidmattei in https://github.com/ems-project/elasticms/pull/1481
* fix(admin/publish): excluded deleted environment revisions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1485
* fix(admin/publish): load all version environment revisions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1484
* fix(admin/twig): alternative twig filter emco_src_path by @Davidmattei in https://github.com/ems-project/elasticms/pull/1483
* fix(admin/wysiwyg): style set check hash exists by @Davidmattei in https://github.com/ems-project/elasticms/pull/1482

## 6.9.3 (2025-11-25)
### Features
* feat(admin/status): jobs & bus status by @theus77 in https://github.com/ems-project/elasticms/pull/1463
### Bug Fixes
* fix(admin): user profile add translation key by @theus77 in https://github.com/ems-project/elasticms/pull/1462
* fix(admin/contentType): remove cascade delete contentType environment by @Davidmattei in https://github.com/ems-project/elasticms/pull/1466
* fix(admin/core): revert npm audit fix by @Davidmattei in https://github.com/ems-project/elasticms/pull/1476
* fix(admin/field): JSONFieldType allow null value by @Davidmattei in https://github.com/ems-project/elasticms/pull/1469
* fix(admin/form-submissions): remove confirm key for download and export by @Davidmattei in https://github.com/ems-project/elasticms/pull/1475
* fix(admin/icons): load only legacy icon sets by @Davidmattei in https://github.com/ems-project/elasticms/pull/1467
* fix(admin/notification): treat accept button stays disabled by @Davidmattei in https://github.com/ems-project/elasticms/pull/1473
* fix(admin/revision): locking skip granted checks when a username is pass by @Davidmattei in https://github.com/ems-project/elasticms/pull/1471
* fix(admin/view): hierarchical correct print label from labelField by @Davidmattei in https://github.com/ems-project/elasticms/pull/1464
* fix(admin/view): hierarchical reorder clears children by @Davidmattei in https://github.com/ems-project/elasticms/pull/1465
* fix(web): the asset path must take the baseUrl into account by @theus77 in https://github.com/ems-project/elasticms/pull/1459
* fix(web/search): boost must be disabled in order to avoid impact on the _score by @theus77 in https://github.com/ems-project/elasticms/pull/1470

## 6.9.2 (2025-11-07)
### Bug Fixes
* fix(admin/contentType): getSortOrder -> getOrderKey by @Davidmattei in https://github.com/ems-project/elasticms/pull/1457
* fix(admin/media-lib): deprecated nested_path for parent_field by @Davidmattei in https://github.com/ems-project/elasticms/pull/1455
* fix(admin/search): nested sort and filter remove nested_path by @Davidmattei in https://github.com/ems-project/elasticms/pull/1456
* fix(web/search): deprecated nested_path for parent_field by @Davidmattei in https://github.com/ems-project/elasticms/pull/1458

## 6.9.1 (2025-11-04)
### Bug Fixes
* fix(admin/channel): channel routes when a base url exists by @theus77 in https://github.com/ems-project/elasticms/pull/1454
* fix(admin/contentType): edit structure $id is not an int anymore by @theus77 in https://github.com/ems-project/elasticms/pull/1449
* fix(admin/contentType): expose content type to Twig template by @theus77 in https://github.com/ems-project/elasticms/pull/1453

## 6.9.0 (2025-10-27)
### Features
* feat(admin/cli): environment rebuild new option ignore-referrers by @theus77 in https://github.com/ems-project/elasticms/pull/1422
* feat(admin/job): env placeholder in job command by @theus77 in https://github.com/ems-project/elasticms/pull/1447
* feat(admin/metrics): add failed job metrics by @Davidmattei in https://github.com/ems-project/elasticms/pull/1442
* feat(admin/wysiwyg): hide/show file fields instead of remove/add by @coppee in https://github.com/ems-project/elasticms/pull/1425
* feat(demo): add acronym keyword filter by @theus77 in https://github.com/ems-project/elasticms/pull/1437
* feat(web/assets): preload upload asset in cache by @theus77 in https://github.com/ems-project/elasticms/pull/1440
### Bug Fixes
* fix(admin/job): scheduled jobs set tag by @Davidmattei in https://github.com/ems-project/elasticms/pull/1443
* fix(admin/revision): sort revision environment by order key by @Davidmattei in https://github.com/ems-project/elasticms/pull/1444
* fix(admin/web/cli): fallback env is prod not dev by @Davidmattei in https://github.com/ems-project/elasticms/pull/1445
### Documentation
* docs(web/pdf): example with files  by @Davidmattei in https://github.com/ems-project/elasticms/pull/1430
### Code Refactoring
* refactor(admin): use content type service in controller by @theus77 in https://github.com/ems-project/elasticms/pull/1439
* refactor(admin/cli): (un)lock commands by @theus77 in https://github.com/ems-project/elasticms/pull/1428
* refactor(admin/contentType): remove unused lockBy and lockUntil by @theus77 in https://github.com/ems-project/elasticms/pull/1429
### Chores
* chore(admin/cli): better description for lock command by @theus77 in https://github.com/ems-project/elasticms/pull/1433
* chore: npm audit fix by @theus77 in https://github.com/ems-project/elasticms/pull/1423
* chore: prepare 6.9 release with rector by @Davidmattei in https://github.com/ems-project/elasticms/pull/1446

## 6.8.1 (2025-10-21)
### Bug Fixes
* fix(admin/field): keep null attribute for filter option by @theus77 in https://github.com/ems-project/elasticms/pull/1435
* fix(admin/job): check return code for success by @Davidmattei in https://github.com/ems-project/elasticms/pull/1438
* fix(cli/sync): new aggs size option for ems:index:synch by @theus77 in https://github.com/ems-project/elasticms/pull/1426
* fix(common/cli): index synch close scroll by @theus77 in https://github.com/ems-project/elasticms/pull/1431
* fix(demo): title mapping, docker image and version 6.8.0 by @theus77 in https://github.com/ems-project/elasticms/pull/1436

## 6.8.0 (2025-09-29)
### Features
* feat(admin/metric): add jobs pending gauge by @Davidmattei in https://github.com/ems-project/elasticms/pull/1418
* feat(cli/sync): support sync ems index over skeleton by @Davidmattei in https://github.com/ems-project/elasticms/pull/1417
* feat(common/command): ems:elasticsearch:clean-orphan-indices by @theus77 in https://github.com/ems-project/elasticms/pull/1404
* feat(common/command): ems:indexes:synchronize, sync 2 indexes by @theus77 in https://github.com/ems-project/elasticms/pull/1395
* feat(common/twig): ems_files_in_archive by @theus77 in https://github.com/ems-project/elasticms/pull/1409
* feat(common/twig): new ems_check_ip function by @theus77 in https://github.com/ems-project/elasticms/pull/1399
* feat(web): core authentication with sso by @Davidmattei in https://github.com/ems-project/elasticms/pull/1414
### Bug Fixes
* fix(admin/backup): environment export ignore alias by @theus77 in https://github.com/ems-project/elasticms/pull/1406
* fix(cli/sync): use symfony http client service by @Davidmattei in https://github.com/ems-project/elasticms/pull/1416
* fix(web): support EMSCH_OAUTH2_REDIRECT_URI as path by @Davidmattei in https://github.com/ems-project/elasticms/pull/1419
### Chores
* chore(cli): enable web profile in dev env by @Davidmattei in https://github.com/ems-project/elasticms/pull/1415
* chore(docker): add prometheus & grafana by @Davidmattei in https://github.com/ems-project/elasticms/pull/1412

## 6.7.4 (2026-01-13)
### Bug Fixes
* fix(web/security): security listener was starting session by getting token by @theus77 in https://github.com/ems-project/elasticms/pull/1514

## 6.7.3 (2025-09-18)
### Bug Fixes
* fix(admin/submission): memory issues on remove and export by @theus77 in https://github.com/ems-project/elasticms/pull/1408
* fix(common/zip): disable defaultEnableZeroHeader for zipStream by @theus77 in https://github.com/ems-project/elasticms/pull/1410
* fix(web/api): treatFiles improve error message by @theus77 in https://github.com/ems-project/elasticms/pull/1411

## 6.7.2 (2025-09-11)
### Bug Fixes
* fix(admin/channel): new option prefix_instance_id by @Davidmattei in https://github.com/ems-project/elasticms/pull/1403
* fix(admin/session): cookie_samesite lax by @Davidmattei in https://github.com/ems-project/elasticms/pull/1402
* fix(admin/twig): json menu query default check by @Davidmattei in https://github.com/ems-project/elasticms/pull/1401
* fix(demo): invalid replacing placeholder ~view.id~ by @Davidmattei in https://github.com/ems-project/elasticms/pull/1400
* fix(web/sso): env variable SESSION_COOKIE_SAMESITE by @Davidmattei in https://github.com/ems-project/elasticms/pull/1398

## 6.7.1 (2025-08-28)
### Bug Fixes
* fix(admin/contentType): display json menu link with query by @Davidmattei in https://github.com/ems-project/elasticms/pull/1394
* fix(admin/migration): replace choiceType update type by @Davidmattei in https://github.com/ems-project/elasticms/pull/1393
* fix(common/spreadsheet): allow mixed as cell value by @Davidmattei in https://github.com/ems-project/elasticms/pull/1392

## 6.7.0 (2025-08-25)
### Features
* feat(admin/content-type): recompute on publication by @theus77 in https://github.com/ems-project/elasticms/pull/1381
* feat(admin/twig): new twig function emsco_job by @Davidmattei in https://github.com/ems-project/elasticms/pull/1387
* feat(common/asset): new canonical header config by @theus77 in https://github.com/ems-project/elasticms/pull/1384
* feat(common/spreadsheet): support excel date fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/1391
* feat(web/brigde): support file upload (chunk) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1385
* feat(web/form): support percent type by @Davidmattei in https://github.com/ems-project/elasticms/pull/1383
### Code Refactoring
* refactor(admin/action): execute job action in service by @Davidmattei in https://github.com/ems-project/elasticms/pull/1386

## 6.6.1 (2025-08-08)
### Bug Fixes
* fix(cli/import): add new mime type configuration by @Davidmattei in https://github.com/ems-project/elasticms/pull/1380
* fix(common/spreadsheet): config value_binder by @Davidmattei in https://github.com/ems-project/elasticms/pull/1382

## 6.6.0 (2025-07-29)
### Features
* feat(demo): using webp in demo's cards by @theus77 in https://github.com/ems-project/elasticms/pull/1374
### Bug Fixes
* fix(admin): ct picker background color in multiple mode by @theus77 in https://github.com/ems-project/elasticms/pull/1377
### Documentation
* docs: licences mit to LGPL-3.0 by @theus77 in https://github.com/ems-project/elasticms/pull/1376
### Code Refactoring
* refactor(admin): crud overview template to page object by @Davidmattei in https://github.com/ems-project/elasticms/pull/1371
* refactor(admin): move admin controllers by @Davidmattei in https://github.com/ems-project/elasticms/pull/1373
* refactor(admin): remove add/edit form template for admin config by @Davidmattei in https://github.com/ems-project/elasticms/pull/1372

## 6.5.1 (2025-07-25)
### Bug Fixes
* fix(admin/revision): inactive actions by @theus77 in https://github.com/ems-project/elasticms/pull/1375

## 6.5.0 (2025-07-10)
### Features
* feat(admin/cli): new submission export command by @coppee in https://github.com/ems-project/elasticms/pull/1352
* feat(admin/media-lib): add behavior for view file on click by @theus77 in https://github.com/ems-project/elasticms/pull/1342
* feat(admin/media-lib): add searchType option by @theus77 in https://github.com/ems-project/elasticms/pull/1340
* feat(admin/media-lib): folder move and revision task helpText by @coppee in https://github.com/ems-project/elasticms/pull/1351
* feat(admin/media-lib): separate navbar and breadcrumb by @theus77 in https://github.com/ems-project/elasticms/pull/1341
* feat(cli/import): add new option lowercase_headers by @Davidmattei in https://github.com/ems-project/elasticms/pull/1369
* feat(demo/media-lib): apply end user remarks (edit button and author) by @theus77 in https://github.com/ems-project/elasticms/pull/1348
### Bug Fixes
* fix(admin/bridge): meta info environments not correct by @Davidmattei in https://github.com/ems-project/elasticms/pull/1370
* fix(admin/user): enable groups with EMSCO_GROUP_FEATURE (default false) by @theus77 in https://github.com/ems-project/elasticms/pull/1343
* fix(web/api): treat an simple array of files by @IsaMic in https://github.com/ems-project/elasticms/pull/1366

## 6.4.6 (2025-07-09)
### Bug Fixes
* fix(admin/environment): revert align command use dbal queries by @Davidmattei in https://github.com/ems-project/elasticms/pull/1367
* fix(admin/environment): revert unpublish command use dbal queries by @Davidmattei in https://github.com/ems-project/elasticms/pull/1368

## 6.4.5 (2025-06-24)
### Bug Fixes
* fix(admin/environment): correct remove/add with version revisions  by @Davidmattei in https://github.com/ems-project/elasticms/pull/1362
* fix(admin/revision): lock exception on silent publication by @Davidmattei in https://github.com/ems-project/elasticms/pull/1364
### Chores
* chore(docker): overwrite postgres volume name by @Davidmattei in https://github.com/ems-project/elasticms/pull/1363

## 6.4.4 (2025-06-17)
### Features
* feat(cli/import): support align environments by @Davidmattei in https://github.com/ems-project/elasticms/pull/1358
* feat(cli/import): support query for search documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1356
### Bug Fixes
* fix(admin/datatable): use post for ajax request core data tables by @Davidmattei in https://github.com/ems-project/elasticms/pull/1347
* fix(admin/json-menu-nested): display template not rendering choices by @Davidmattei in https://github.com/ems-project/elasticms/pull/1357
* fix(admin/media-lib): delete folder not granted by @Davidmattei in https://github.com/ems-project/elasticms/pull/1353
* fix(admin/user): profile group only print if defined by @Davidmattei in https://github.com/ems-project/elasticms/pull/1359
* fix(common/backup): exclude _published by from export by @Davidmattei in https://github.com/ems-project/elasticms/pull/1360
* fix(common/job): correct set status for core jobs by @Davidmattei in https://github.com/ems-project/elasticms/pull/1361
* fix(common/storage): asset processor overwrite default mimetype by @theus77 in https://github.com/ems-project/elasticms/pull/1349
* fix(core/repository): typo in findLatestVersion by @coppee in https://github.com/ems-project/elasticms/pull/1355

## 6.4.3 (2025-06-10)
### Bug Fixes
* fix(admin/content-type): mandatory if, not working as expected by @theus77 in https://github.com/ems-project/elasticms/pull/1344
* fix(admin/datatable): use post instead of get for ajax requests by @theus77 in https://github.com/ems-project/elasticms/pull/1339
* fix(admin/revision): bulk unpublish flag environment revision as deleted by @Davidmattei in https://github.com/ems-project/elasticms/pull/1345
* fix(admin/security): on all logins set last login (api,bridge) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1346

## 6.4.2 (2025-06-04)
### Bug Fixes
* fix(admin/contentType): versioning fields empty string by @Davidmattei in https://github.com/ems-project/elasticms/pull/1337
* fix(admin/doctrine): fieldType options can be null by @Davidmattei in https://github.com/ems-project/elasticms/pull/1336
* fix(admin/environment): correct counter on overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/1338
* fix(admin/environment): rebuild correct compare current env by @Davidmattei in https://github.com/ems-project/elasticms/pull/1335

## 6.4.1 (2025-06-03)
### Features
* feat(twig): add global emschLocales variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1328
### Bug Fixes
* fix(admin/cke): browser server of images by @IsaMic in https://github.com/ems-project/elasticms/pull/1330
* fix(admin/field): multiplex correct use form locale by @Davidmattei in https://github.com/ems-project/elasticms/pull/1329
* fix(admin/group): remove not working + improve by @IsaMic in https://github.com/ems-project/elasticms/pull/1333
* fix(admin/mercure): change default dev env MERCURE_URL by @Davidmattei in https://github.com/ems-project/elasticms/pull/1327
* fix(admin/trash): role Edit can "Put back" by @IsaMic in https://github.com/ems-project/elasticms/pull/1331
* fix(common/elasticSearch): double slash in request (getVersion) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1334
* fix(common/runner): allow to use a self-signed ca for openshift by @theus77 in https://github.com/ems-project/elasticms/pull/1332

## 6.4.0 (2025-05-26)
### Features
* feat(admin/api): add openapi documentation by @Zki49 in https://github.com/ems-project/elasticms/pull/1309
* feat(admin/job): auto launch runner by @theus77 in https://github.com/ems-project/elasticms/pull/1326
* feat(admin/jobs): add metrics job collector by @Davidmattei in https://github.com/ems-project/elasticms/pull/1324
* feat(admin/revision): implement actions and AI support by @Davidmattei in https://github.com/ems-project/elasticms/pull/1303
* feat(admin/revision): implement publish event action by @theus77 in https://github.com/ems-project/elasticms/pull/1320
* feat(admin/revision): set published meta fields in raw by @theus77 in https://github.com/ems-project/elasticms/pull/1317
* feat(admin/symfony): implement mercure bundle (real-time push) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1310
* feat(cli/import): support exclude expression by @Davidmattei in https://github.com/ems-project/elasticms/pull/1323
* feat(common/pdf): printer support remote hosts option by @theus77 in https://github.com/ems-project/elasticms/pull/1322
### Bug Fixes
* fix(admin/contentType): invalid mapping for multiplex with locales variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1321
* fix(admin/doctrine): invalid entity mapping for new EnvironmentRevision by @Davidmattei in https://github.com/ems-project/elasticms/pull/1312
* fix(admin/environment): restore revision from trash (EnvironmentRevision) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1313
* fix(admin/mercure): one JWT_TOKEN and better token generation by @Davidmattei in https://github.com/ems-project/elasticms/pull/1311
* fix(admin/revision): cascade delete environments by @Davidmattei in https://github.com/ems-project/elasticms/pull/1314
* fix(admin/revision): new document without ouuid by @theus77 in https://github.com/ems-project/elasticms/pull/1319
* fix(common/admin): already connected supres error on existing valid authkey by @theus77 in https://github.com/ems-project/elasticms/pull/1316
* fix(common/core-api): json decode on forward stream for form file by @Davidmattei in https://github.com/ems-project/elasticms/pull/1325
### Code Refactoring
* refactor(admin/doctrine): add EnvironmentRevision associative entity by @theus77 in https://github.com/ems-project/elasticms/pull/1306

## 6.3.1 (2025-05-14)
### Bug Fixes
* fix(admin/user): create groups from json by @theus77 in https://github.com/ems-project/elasticms/pull/1315
### Documentation
* docs(upgrade/6.0.0): emsco_get.source for accessing data by @theus77 in https://github.com/ems-project/elasticms/pull/1318

## 6.3.0 (2025-04-29)
### Features
* feat(admin/cli): add and remove user from group commands by @Zki49 in https://github.com/ems-project/elasticms/pull/1304
* feat(admin/cli): add report option for emsco:asset:extract by @Davidmattei in https://github.com/ems-project/elasticms/pull/1302
* feat(admin/messenger): add messenger component for queuing by @coppee in https://github.com/ems-project/elasticms/pull/1262
* feat(admin/user): implement user groups by @Zki49 in https://github.com/ems-project/elasticms/pull/1199
* feat(admin/user): support user group in adminUI by @theus77 in https://github.com/ems-project/elasticms/pull/1294
* feat(common/runner): implement job runners by @theus77 in https://github.com/ems-project/elasticms/pull/1288
* feat(demo/wysiwyg): add wysiwyg crawler macro by @michaeldk in https://github.com/ems-project/elasticms/pull/1296
### Bug Fixes
* fix(admin/database): add missing migrations by @Davidmattei in https://github.com/ems-project/elasticms/pull/1308
* fix(admin/revision): detail current version is closed by @Davidmattei in https://github.com/ems-project/elasticms/pull/1305
* fix(admin/view): correct view templates for bootstrap5 by @theus77 in https://github.com/ems-project/elasticms/pull/1286

## 6.2.2 (2025-04-17)
### Bug Fixes
* fix(admin/api): ldap authenticator use provider by @Davidmattei in https://github.com/ems-project/elasticms/pull/1301
* fix(admin/revision): lock exception on restore from trash by @Davidmattei in https://github.com/ems-project/elasticms/pull/1298
* fix(admin/revision): use revision display column in trash overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/1300
* fix(admin/security): allows public access to /emsf/ baseurls (channels) by @theus77 in https://github.com/ems-project/elasticms/pull/1292
* fix(web/form): pass submitted data to template by @Davidmattei in https://github.com/ems-project/elasticms/pull/1297

## 6.2.1 (2025-04-04)
### Features
* feat(client/emschForm): support validate block by @Davidmattei in https://github.com/ems-project/elasticms/pull/1287
* feat(emsch/form): add "greaterThan" and "lessThan" constraints by @Davidmattei in https://github.com/ems-project/elasticms/pull/1283
* feat(web/twig): add 'ems_search_config_execute' twig function by @Davidmattei in https://github.com/ems-project/elasticms/pull/1289
### Bug Fixes
* fix(admin/revision): set version revision on publish by @coppee in https://github.com/ems-project/elasticms/pull/1278
* fix(admin/view): view type selects not showing labels by @theus77 in https://github.com/ems-project/elasticms/pull/1284
* fix(common/bridge): add publish versions endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1285
* fix(common/storage): s3 upload bug small files by @theus77 in https://github.com/ems-project/elasticms/pull/1280
* fix(core/revision): api finalize autoSave check for _version_uuid by @Davidmattei in https://github.com/ems-project/elasticms/pull/1282
* fix(core/revision): autoSave without merge by @Davidmattei in https://github.com/ems-project/elasticms/pull/1281

## 6.2.0 (2025-03-31)
### Features
* feat(admin): add `forgot_password_url` configuration by @IsaMic in https://github.com/ems-project/elasticms/pull/1251
* feat(cli/user): collect users info from multiple admins by @theus77 in https://github.com/ems-project/elasticms/pull/1234
* feat(common/twig): add file reader functions (csv & excel) by @theus77 in https://github.com/ems-project/elasticms/pull/1264
* feat(demo): allow access to file if in indexes by @theus77 in https://github.com/ems-project/elasticms/pull/1256
* feat(demo): demo-FilesUpload + fix(submission) by @IsaMic in https://github.com/ems-project/elasticms/pull/1255
* feat(demo): js translations from twig/skeleton by @theus77 in https://github.com/ems-project/elasticms/pull/1267
* feat(demo/file-upload): styling & UX by @michaeldk in https://github.com/ems-project/elasticms/pull/1274
* feat(demo/js-translations): add support for pattern replacement by @michaeldk in https://github.com/ems-project/elasticms/pull/1275
### Bug Fixes
* fix(admin-ui): fix ems font import by @michaeldk in https://github.com/ems-project/elasticms/pull/1277
* fix(admin/versioning): ref search latest version by @Davidmattei in https://github.com/ems-project/elasticms/pull/1273
### Code Refactoring
* refactor(admin-ui-bundle/css): refactor CSS imports by @michaeldk in https://github.com/ems-project/elasticms/pull/1265
* refactor(admin/ui): admin wysiwyg entities by @theus77 in https://github.com/ems-project/elasticms/pull/1235
* refactor(admin/ui): documentaiton page and add alias  by @theus77 in https://github.com/ems-project/elasticms/pull/1236
* refactor(admin/ui): draft in progress by @theus77 in https://github.com/ems-project/elasticms/pull/1246
* refactor(admin/ui): elasticsearch and avoid json.twig templates by @theus77 in https://github.com/ems-project/elasticms/pull/1237
* refactor(admin/ui): filter by @theus77 in https://github.com/ems-project/elasticms/pull/1242
* refactor(admin/ui): i18n by @theus77 in https://github.com/ems-project/elasticms/pull/1245
* refactor(admin/ui): navigation breadcrumbs in uploaded files by @theus77 in https://github.com/ems-project/elasticms/pull/1244
* refactor(admin/ui): pagination.html.twig by @theus77 in https://github.com/ems-project/elasticms/pull/1238
* refactor(admin/ui): query_search by @theus77 in https://github.com/ems-project/elasticms/pull/1241
* refactor(admin/ui): schedule by @theus77 in https://github.com/ems-project/elasticms/pull/1240
* refactor(admin/ui): search templates by @theus77 in https://github.com/ems-project/elasticms/pull/1239
* refactor(admin/ui): trash and logs by @theus77 in https://github.com/ems-project/elasticms/pull/1243
### Chores
* chore(admin-ui/vite): add source map in dev mode for easier debug by @michaeldk in https://github.com/ems-project/elasticms/pull/1276
* chore(dev): switch default EMS_CACHE redis to file_system by @theus77 in https://github.com/ems-project/elasticms/pull/1261
* chore(docker): update deprecated minio env variables by @michaeldk in https://github.com/ems-project/elasticms/pull/1254
* chore: add dev scripts for AdminUIBundle in makeFile by @theus77 in https://github.com/ems-project/elasticms/pull/1257

## 6.1.2 (2025-03-24)
### Features
* feat(admin/api): add support for lazy indexing by @Davidmattei in https://github.com/ems-project/elasticms/pull/1268
* feat(cli/import): add digest feature by @Davidmattei in https://github.com/ems-project/elasticms/pull/1271
### Bug Fixes
* fix(admin/contentType): add suport helptext for choiceFieldType by @theus77 in https://github.com/ems-project/elasticms/pull/1258
* fix(admin/contentType): datetime field type correct default display format by @theus77 in https://github.com/ems-project/elasticms/pull/1270
* fix(admin/contentType): support helptext for choiceFieldType by @theus77 in https://github.com/ems-project/elasticms/pull/1259
* fix(admin/contentType): wrong default display date format for DataTimeFieldType by @theus77 in https://github.com/ems-project/elasticms/pull/1260
* fix(admin/extract): avoid creating ghost documents by @theus77 in https://github.com/ems-project/elasticms/pull/1263
* fix(admin/js): disable button on file upload by @theus77 in https://github.com/ems-project/elasticms/pull/1272
* fix(admin/mapping): not analyzed index by @theus77 in https://github.com/ems-project/elasticms/pull/1269

## 6.1.1 (2025-03-12)
### Features
* feat(admin): allow change link forgot password by @IsaMic in https://github.com/ems-project/elasticms/pull/1250
* feat(cli/import): add command for database import by @Davidmattei in https://github.com/ems-project/elasticms/pull/1253
### Bug Fixes
* fix(admin/publish): only create new version if tags are defined by @Davidmattei in https://github.com/ems-project/elasticms/pull/1247
* fix(cli/file-import): use correct mimetype of import file by @Davidmattei in https://github.com/ems-project/elasticms/pull/1252
* fix(web/form): add range and regex assert by @Davidmattei in https://github.com/ems-project/elasticms/pull/1248

## 6.1.0 (2025-02-26)
### Features
* feat(admin/contentType): optional tags and date support by @Davidmattei in https://github.com/ems-project/elasticms/pull/1224
* feat(admin/field): multiplexed tab with emsch.locales parameter by @theus77 in https://github.com/ems-project/elasticms/pull/1201
* feat(admin/post-processing): add document revision in context by @Davidmattei in https://github.com/ems-project/elasticms/pull/1214
* feat(admin/ui): replace select2 by choices.js by @theus77 in https://github.com/ems-project/elasticms/pull/1210
* feat(common/bridge): add data publish endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/1196
* feat(common/bridge): add new initDraft method and CoreBridgeResponse by @Davidmattei in https://github.com/ems-project/elasticms/pull/1209
* feat(common/twig): add ems_flash twig function by @Davidmattei in https://github.com/ems-project/elasticms/pull/1215
* feat(mono-repo): add make 'check' target by @Davidmattei in https://github.com/ems-project/elasticms/pull/1220
* feat(spreadsheet): add data validation by @IsaMic in https://github.com/ems-project/elasticms/pull/1190
* feat(web/form): add dynamic choice element for emsch forms  by @Davidmattei in https://github.com/ems-project/elasticms/pull/1232
* feat(web/form): add emsch_form_view view variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1233
* feat(web/form): support constraints and extra elements by @Davidmattei in https://github.com/ems-project/elasticms/pull/1221
### Bug Fixes
* fix(admin): color picker form field by @theus77 in https://github.com/ems-project/elasticms/pull/1195
* fix(admin): optional use of deprecated EMSCH_BACKEND_URL env by @Davidmattei in https://github.com/ems-project/elasticms/pull/1219
* fix(admin/action): filter on the environment even if the template.role == "not-defined" by @theus77 in https://github.com/ems-project/elasticms/pull/1205
* fix(admin/cli): backup fieldType ignore displayExtraOptions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1212
* fix(admin/color-picker): use translation function for choices by @theus77 in https://github.com/ems-project/elasticms/pull/1216
* fix(admin/dashboard): use data-icon for type just like other pickers by @theus77 in https://github.com/ems-project/elasticms/pull/1230
* fix(admin/field): avoid to have extra display options saved in the FileType entity by @theus77 in https://github.com/ems-project/elasticms/pull/1203
* fix(admin/field): reverse entity name modal transformer nullable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1211
* fix(admin/revision): unlock after discard draft by @theus77 in https://github.com/ems-project/elasticms/pull/1223
* fix(admin/ui): action are not filtered on envs is role is not defined by @theus77 in https://github.com/ems-project/elasticms/pull/1206
* fix(admin/ui): edit content type form bootstrap 5 by @theus77 in https://github.com/ems-project/elasticms/pull/1204
* fix(demo): correct demo-backup-configs by @theus77 in https://github.com/ems-project/elasticms/pull/1194
### Code Refactoring
* refactor(admin/ui): actions admin and forms admin (bs5 template) by @theus77 in https://github.com/ems-project/elasticms/pull/1226
* refactor(admin/ui): admin analyzer by @theus77 in https://github.com/ems-project/elasticms/pull/1227
* refactor(admin/ui): admin channels by @theus77 in https://github.com/ems-project/elasticms/pull/1228
* refactor(admin/ui): admin content types by @theus77 in https://github.com/ems-project/elasticms/pull/1229
* refactor(admin/ui): admin dashboard by @theus77 in https://github.com/ems-project/elasticms/pull/1231
* refactor(admin/ui): crud add template by @theus77 in https://github.com/ems-project/elasticms/pull/1217
* refactor(admin/ui): job templates by @theus77 in https://github.com/ems-project/elasticms/pull/1225
* refactor(admin/ui): select2 to choices.js by @theus77 in https://github.com/ems-project/elasticms/pull/1207
* refactor(admin/ui): style admin job with generic card template by @theus77 in https://github.com/ems-project/elasticms/pull/1213
* refactor(common/slugger): preserve file extension option by @theus77 in https://github.com/ems-project/elasticms/pull/1187
* refactor(demo): simplify content types and add a link content type by @theus77 in https://github.com/ems-project/elasticms/pull/1191
* refactor(helper/date): convert java/js format to php by @Davidmattei in https://github.com/ems-project/elasticms/pull/1222
### Chores
* chore(admin/ui): npm run format by @theus77 in https://github.com/ems-project/elasticms/pull/1208
* chore(docker): setup_minio like demo by @theus77 in https://github.com/ems-project/elasticms/pull/1198
* chore(make): upgrade to base-php:8.4-cli-dev for npm by @theus77 in https://github.com/ems-project/elasticms/pull/1202
* chore: build translations correct paths and only yaml by @Davidmattei in https://github.com/ems-project/elasticms/pull/1218

## 6.0.1 (2025-02-12)
### Bug Fixes
* fix(admin/ui): select2 in bootstrap's modal by @theus77 in https://github.com/ems-project/elasticms/pull/1192
* fix(common/storage): weird s3 issue on completeMultipartUpload by @theus77 in https://github.com/ems-project/elasticms/pull/1197
* fix(demo): Font Awesome 5 Free -> Font Awesome 6 Free by @theus77 in https://github.com/ems-project/elasticms/pull/1193
* fix(emsch/template): rollback exits use template builder by @Davidmattei in https://github.com/ems-project/elasticms/pull/1200

## 6.0.0 (2025-02-05)
### Features
* feat(admin): cke4 features in bootstrap5 by @theus77 in https://github.com/ems-project/elasticms/pull/1179
* feat(admin): media library and datatable improvements by @Davidmattei in https://github.com/ems-project/elasticms/pull/1005
* feat(admin): wywiwyg editor field for ckeditor 4 or 5 by @theus77 in https://github.com/ems-project/elasticms/pull/1129
* feat(admin-ui): migrate bootstrap 3 to 5 by @sylver4 in https://github.com/ems-project/elasticms/pull/596
* feat(admin/jmn): implement json menu nested in 6.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/984
* feat(admin/media-lib):  support for media library 6.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/992
* feat(bridge): improve bridge data delete and info bridge for documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1154
* feat(cli/command): add ems:document:merge  by @theus77 in https://github.com/ems-project/elasticms/pull/1113
* feat(common/api): use sf client this enables profiler by @Davidmattei in https://github.com/ems-project/elasticms/pull/1144
* feat(common/asset): vite php dev server by @Davidmattei in https://github.com/ems-project/elasticms/pull/1182
* feat(core/field-type): new FieldTypeService for building tree by @Davidmattei in https://github.com/ems-project/elasticms/pull/788
* feat(core/users): permissions overview (r-n-r-summary) by @OzkanO2 in https://github.com/ems-project/elasticms/pull/780
* feat(demo): use vite instead of webpack by @Davidmattei in https://github.com/ems-project/elasticms/pull/1178
* feat(elasticsearch/mapping): new EMSCO_DYNAMIC_MAPPING  configuration by @theus77 in https://github.com/ems-project/elasticms/pull/711
* feat(ems): add core bridge (api & service) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1143
* feat(web/emsch): add FormController with FormType by @Davidmattei in https://github.com/ems-project/elasticms/pull/1138
* feat(web/twig): add new emsch_asset_redirect by @theus77 in https://github.com/ems-project/elasticms/pull/1184
* feat(wysiwyg/preview): prefixed styleset css, remove iframe by @theus77 in https://github.com/ems-project/elasticms/pull/790
* feat: edit image in CKE5 by @theus77 in https://github.com/ems-project/elasticms/pull/872
* feat: require php 8.4 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1123
* feat: upgrade doctrine orm 3.0 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1161
### Bug Fixes
* fix(admin): hide sensitive info to non authenticated users by @theus77 in https://github.com/ems-project/elasticms/pull/758
* fix(admin): migration issues by @Davidmattei in https://github.com/ems-project/elasticms/pull/1122
* fix(admin): slug config names use separator '_' by @Davidmattei in https://github.com/ems-project/elasticms/pull/1173
* fix(autosave): empty warnings by @theus77 in https://github.com/ems-project/elasticms/pull/792
* fix(bootstrap 5): need a clearfix when text counter and no help by @theus77 in https://github.com/ems-project/elasticms/pull/839
* fix(common): (new Encoder())->slug replaces '.' by '-'.  by @theus77 in https://github.com/ems-project/elasticms/pull/1186
* fix(common/vite): make vite optional  by @Davidmattei in https://github.com/ems-project/elasticms/pull/1183
* fix(core/bootstrap5): tabs in edit revision by @theus77 in https://github.com/ems-project/elasticms/pull/734
* fix(core/flash-messages): obvious duplicate alerts by @theus77 in https://github.com/ems-project/elasticms/pull/794
* fix(core/rector): entities $id is not readonly by @theus77 in https://github.com/ems-project/elasticms/pull/735
* fix(core/revision): empty environment actions, no dropdown by @Davidmattei in https://github.com/ems-project/elasticms/pull/885
* fix(core/ui): select2 icon picker by @theus77 in https://github.com/ems-project/elasticms/pull/1158
* fix(core/wywiwyg): delegate label to RevisionService::display by @theus77 in https://github.com/ems-project/elasticms/pull/840
* fix(demo): double css import by @theus77 in https://github.com/ems-project/elasticms/pull/791
* fix(demo): fix favicon redirect to avoid _get_file_path: true by @theus77 in https://github.com/ems-project/elasticms/pull/1185
* fix(demo): using session in stateless api calls by @Davidmattei in https://github.com/ems-project/elasticms/pull/776
* fix(demo/structure): get default environment for section by @theus77 in https://github.com/ems-project/elasticms/pull/709
* fix(docker): introduce POSTGRES_VERSION env variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1137
* fix(emsch/asset): still need to update the styleset on admin side by @theus77 in https://github.com/ems-project/elasticms/pull/1181
* fix(helper): prevent tempFile destructor from being called too early by @Davidmattei in https://github.com/ems-project/elasticms/pull/1160
* fix(helper/color): gd alpha's range is [0,127] while HLML alpha range is [0,255] by @theus77 in https://github.com/ems-project/elasticms/pull/1168
* fix(phpstan): resolve baseline issues by @Davidmattei in https://github.com/ems-project/elasticms/pull/1128
* fix(symfony/6.4): AbstractProcessingHandler handle LogRecord by @theus77 in https://github.com/ems-project/elasticms/pull/685
* fix(symfony/6.4): cli doctrine mapping from annotation to xml by @theus77 in https://github.com/ems-project/elasticms/pull/698
* fix(symfony/6.4): client routing and common elasticsearch by @theus77 in https://github.com/ems-project/elasticms/pull/699
* fix(symfony/6.4): commands $defaultName is deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/701
* fix(symfony/6.4): composer requirement aligned by @Davidmattei in https://github.com/ems-project/elasticms/pull/705
* fix(symfony/6.4): deprecations web/admin & cli by @Davidmattei in https://github.com/ems-project/elasticms/pull/707
* fix(symfony/6.4): fix admin deprecations framework, security and twig by @theus77 in https://github.com/ems-project/elasticms/pull/704
* fix(symfony/6.4): flashbag service deprecated  by @theus77 in https://github.com/ems-project/elasticms/pull/683
* fix(symfony/6.4): log_message formatted column can not be null by @Davidmattei in https://github.com/ems-project/elasticms/pull/703
* fix(symfony/6.4): remove sensio/framework-extra-bundle & annotations  by @Davidmattei in https://github.com/ems-project/elasticms/pull/708
* fix(symfony/6.4): throw UserNotFoundException if ldap server (dn == '') is not defined by @theus77 in https://github.com/ems-project/elasticms/pull/700
* fix(twig): apply deprecation_info for twig functions and filters by @Davidmattei in https://github.com/ems-project/elasticms/pull/1126
* fix: closuse in elastica exception by @theus77 in https://github.com/ems-project/elasticms/pull/1150
* fix: deprecations and upgrade packages (domPdf, phpOffice, guzzle) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1153
* fix: index are deprecated (an cause issues) by @theus77 in https://github.com/ems-project/elasticms/pull/1151
* fix: migration issues by @Davidmattei in https://github.com/ems-project/elasticms/pull/1165
* fix: orderKey has been renamed by @theus77 in https://github.com/ems-project/elasticms/pull/986
* fix: resolve last remaining deprecations by @Davidmattei in https://github.com/ems-project/elasticms/pull/1162
* fix: temp file and directory use new destruct collector by @Davidmattei in https://github.com/ems-project/elasticms/pull/1163
### Documentation
* docs: php8.4 by @theus77 in https://github.com/ems-project/elasticms/pull/1172
* docs: small adjustments in upgrade note by @theus77 in https://github.com/ems-project/elasticms/pull/1145
### Code Refactoring
* refactor(admin): remove radio and select fields (converted to choice) by @theus77 in https://github.com/ems-project/elasticms/pull/1134
* refactor(admin-ui): Frontend JS, CSS, .. that still to be refactored by @theus77 in https://github.com/ems-project/elasticms/pull/737
* refactor(admin-ui): JS work in progress by @theus77 in https://github.com/ems-project/elasticms/pull/727
* refactor(admin-ui): ace code editor by @theus77 in https://github.com/ems-project/elasticms/pull/724
* refactor(admin-ui): action.js by @theus77 in https://github.com/ems-project/elasticms/pull/749
* refactor(admin-ui): add styleint and apply (+ems icon) by @theus77 in https://github.com/ems-project/elasticms/pull/722
* refactor(admin-ui): ajax safe + toast notifications + out of sync by @theus77 in https://github.com/ems-project/elasticms/pull/748
* refactor(admin-ui): asset extract by @theus77 in https://github.com/ems-project/elasticms/pull/752
* refactor(admin-ui): checkbox by @theus77 in https://github.com/ems-project/elasticms/pull/725
* refactor(admin-ui): file input into bootstrap 5 by @theus77 in https://github.com/ems-project/elasticms/pull/721
* refactor(admin-ui): js in edit revision (DateTime, Reorder, Sortable, ..) by @theus77 in https://github.com/ems-project/elasticms/pull/770
* refactor(admin-ui): jsonMenu by @theus77 in https://github.com/ems-project/elasticms/pull/768
* refactor(admin-ui): media library and json menu nested by @theus77 in https://github.com/ems-project/elasticms/pull/720
* refactor(admin-ui): migrate ems listeners by @theus77 in https://github.com/ems-project/elasticms/pull/726
* refactor(admin-ui): migrate js apps by @theus77 in https://github.com/ems-project/elasticms/pull/716
* refactor(admin-ui): sidebar control by @theus77 in https://github.com/ems-project/elasticms/pull/736
* refactor(admin-ui): stylelint issues by @theus77 in https://github.com/ems-project/elasticms/pull/723
* refactor(admin-ui): twig extensions by @theus77 in https://github.com/ems-project/elasticms/pull/715
* refactor(admin/ui): align tabs by @theus77 in https://github.com/ems-project/elasticms/pull/1180
* refactor(admin/wysiwyg): cke5 apply profile and docs by @theus77 in https://github.com/ems-project/elasticms/pull/819
* refactor(bootstrap 5): date field types, textareas and some fixes by @theus77 in https://github.com/ems-project/elasticms/pull/772
* refactor(bootstrap 5): edit revision by @theus77 in https://github.com/ems-project/elasticms/pull/767
* refactor(bootstrap 5): float-end by @theus77 in https://github.com/ems-project/elasticms/pull/759
* refactor(bootstrap 5): remove selectpicker for select2 by @theus77 in https://github.com/ems-project/elasticms/pull/775
* refactor(bootstrap 5): search form by @theus77 in https://github.com/ems-project/elasticms/pull/773
* refactor(bootstrap 5): sidebar active menu + postButton by @theus77 in https://github.com/ems-project/elasticms/pull/763
* refactor(cke5): autosave on keyup input an cke5 fields by @theus77 in https://github.com/ems-project/elasticms/pull/795
* refactor(cke5): ckeditor in rev by @theus77 in https://github.com/ems-project/elasticms/pull/786
* refactor(cke5): clean on paste by @theus77 in https://github.com/ems-project/elasticms/pull/800
* refactor(cke5): continue on cke5 refactoring by @theus77 in https://github.com/ems-project/elasticms/pull/796
* refactor(cke5): link plugin by @theus77 in https://github.com/ems-project/elasticms/pull/822
* refactor(common): flag ems_webalize as deprecated alternative slug by @theus77 in https://github.com/ems-project/elasticms/pull/801
* refactor(common/core-api): use single instance everywhere (emsch, adminHelper) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1141
* refactor(common/twig): add common twig template by @Davidmattei in https://github.com/ems-project/elasticms/pull/1140
* refactor(core/api): remove json.twig templates for JsonResponse by @theus77 in https://github.com/ems-project/elasticms/pull/782
* refactor(core/icon-picker): font awesome 6 icons by @theus77 in https://github.com/ems-project/elasticms/pull/744
* refactor(doctrine): doctrine annotations to xml by @Davidmattei in https://github.com/ems-project/elasticms/pull/625
* refactor(generated assets): delete generated assets by @theus77 in https://github.com/ems-project/elasticms/pull/760
* refactor(helper/json): deprecated classes and \json_ use helper by @theus77 in https://github.com/ems-project/elasticms/pull/714
* refactor(rector): rector on full code base by @theus77 in https://github.com/ems-project/elasticms/pull/691
* refactor(rector): rector readonly properties and final consts by @theus77 in https://github.com/ems-project/elasticms/pull/710
* refactor(twig): twig lint by @theus77 in https://github.com/ems-project/elasticms/pull/793
* refactor: \file_put_contents to File::putContents by @theus77 in https://github.com/ems-project/elasticms/pull/1149
* refactor: avoid to pollute the root with unnamed resources by @theus77 in https://github.com/ems-project/elasticms/pull/985
* refactor: emsco diffs by @theus77 in https://github.com/ems-project/elasticms/pull/1159
* refactor: entity datatables by @theus77 in https://github.com/ems-project/elasticms/pull/866
* refactor: finalize the link modal by @theus77 in https://github.com/ems-project/elasticms/pull/864
* refactor: nested sortables by @theus77 in https://github.com/ems-project/elasticms/pull/1112
* refactor: no more node or java dependencies in CLI by @theus77 in https://github.com/ems-project/elasticms/pull/1115
* refactor: php83 by @theus77 in https://github.com/ems-project/elasticms/pull/877
* refactor: rm elk 6 and before by @theus77 in https://github.com/ems-project/elasticms/pull/1131
* refactor: stateless logger by @theus77 in https://github.com/ems-project/elasticms/pull/777
* refactor: ts+vite by @theus77 in https://github.com/ems-project/elasticms/pull/1034
* refactor: upgrade postgres to version 17 by @theus77 in https://github.com/ems-project/elasticms/pull/1130
* refactor: validate link form + anchor links by @theus77 in https://github.com/ems-project/elasticms/pull/863
### Builds
* build(6.x): enable monorepo splitter for 6.x branch by @Davidmattei in https://github.com/ems-project/elasticms/pull/706
* build(symfony/6.4): phpcs update by @theus77 in https://github.com/ems-project/elasticms/pull/689
* build(symfony/6.4): update composer dependencies  by @theus77 in https://github.com/ems-project/elasticms/pull/702
### Chores
* chore(phpcs): enable modernize_types_casting by @Davidmattei in https://github.com/ems-project/elasticms/pull/1166
* chore(phpcs): enable no alias functions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1167
* chore: composer lint script by @Davidmattei in https://github.com/ems-project/elasticms/pull/1164
* chore: enable strict types by @Davidmattei in https://github.com/ems-project/elasticms/pull/1133
* chore: fix last phpUnit deprecations (doctrine) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1157
* chore: implement new bundle structure by @Davidmattei in https://github.com/ems-project/elasticms/pull/1174
* chore: improve app tests and fix cli tests by @Davidmattei in https://github.com/ems-project/elasticms/pull/1156
* chore: improve local setup by @Davidmattei in https://github.com/ems-project/elasticms/pull/1110
* chore: improve vite dev server by @Davidmattei in https://github.com/ems-project/elasticms/pull/1111
* chore: new config dir by @Davidmattei in https://github.com/ems-project/elasticms/pull/1177
* chore: php-cs-fixer add @PHP84Migration by @Davidmattei in https://github.com/ems-project/elasticms/pull/1136
* chore: phpcs by @theus77 in https://github.com/ems-project/elasticms/pull/1114
* chore: phpstan deprecations by @Davidmattei in https://github.com/ems-project/elasticms/pull/1135
* chore: rector 2 and php 2 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1127
* chore: update composer dependencies  by @Davidmattei in https://github.com/ems-project/elasticms/pull/1139
* chore: upgrade jsonMenuNested component by @Davidmattei in https://github.com/ems-project/elasticms/pull/1116
* chore: upgrade phpUnit 9 -> 11 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1147

