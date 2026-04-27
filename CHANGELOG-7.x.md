# Changelog 7.x

## 7.2.0 (2026-04-27)
### Features
* feat (media-library): emscli media library update file links by @YanisGroffier in https://github.com/ems-project/elasticms/pull/1572
* feat(admin): add tags data-testid for test by @IsaMic in https://github.com/ems-project/elasticms/pull/1637
* feat(admin/asset): get asset info from hash (api+cli) by @theus77 in https://github.com/ems-project/elasticms/pull/1681
* feat(admin/commands): standardize username option by @theus77 in https://github.com/ems-project/elasticms/pull/1670
* feat(admin/contenttype): add html unwrap transformer by @Davidmattei in https://github.com/ems-project/elasticms/pull/1656
* feat(admin/contenttype): add unwrap for html attribute transformer by @Davidmattei in https://github.com/ems-project/elasticms/pull/1652
* feat(admin/job): relaunch job by @theus77 in https://github.com/ems-project/elasticms/pull/1666
* feat(admin/twig): add filter ems_get_file_object and emsco_get_file_object by @theus77 in https://github.com/ems-project/elasticms/pull/1665
* feat(cli/command): add new emscli:dev:fake-project-build by @theus77 in https://github.com/ems-project/elasticms/pull/1673
* feat(common/image): webp xmp writer by @theus77 in https://github.com/ems-project/elasticms/pull/1644
* feat(common/property-accessor): recursive get/set and iterate by @Davidmattei in https://github.com/ems-project/elasticms/pull/1672
* feat(docker): add sandbox dev container (useful for AI agents) by @theus77 in https://github.com/ems-project/elasticms/pull/1671
* feat(docker/sandbox): add pbc alias and npm i -g by @theus77 in https://github.com/ems-project/elasticms/pull/1688
* feat(web/asset):  create symlink for relative or absolute EMSCH_ASSET_LOCAL_FOLDER by @theus77 in https://github.com/ems-project/elasticms/pull/1667
* feat(web/asset): add new EMSCH_ASSET_SRC_IMAGE_CONFIG by @theus77 in https://github.com/ems-project/elasticms/pull/1645
* feat(web/search): save emsch search response in cache by @theus77 in https://github.com/ems-project/elasticms/pull/1658
### Bug Fixes
* fix(admin): audit table full width by @Davidmattei in https://github.com/ems-project/elasticms/pull/1638
* fix(admin): data link for archived documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1649
* fix(admin/cli): username option not defined and rebuild failing by @Davidmattei in https://github.com/ems-project/elasticms/pull/1679
* fix(admin/dashboard): add data-skip-click-event attribute by @Davidmattei in https://github.com/ems-project/elasticms/pull/1653
* fix(admin/form-submissions): add batch size on delete (avoid out of memory) by @theus77 in https://github.com/ems-project/elasticms/pull/1647
* fix(admin/menu): invalid test id on dashboard menu by @Davidmattei in https://github.com/ems-project/elasticms/pull/1660
* fix(admin/menu): invalid test id on dashboard menu by @Davidmattei in https://github.com/ems-project/elasticms/pull/1661
* fix(admin/post-processing): add revision environments in context by @Davidmattei in https://github.com/ems-project/elasticms/pull/1650
* fix(admin/security): API authorization bypass via access_control rule ordering by @theus77 in https://github.com/ems-project/elasticms/pull/1686
* fix(admin/security): open redirect via unvalidated redirect by @theus77 in https://github.com/ems-project/elasticms/pull/1687
* fix(admin/storage): add getFileObject on storageManager and fileService by @theus77 in https://github.com/ems-project/elasticms/pull/1648
* fix(admin/tiptap): remove format only style and indent correct by @Davidmattei in https://github.com/ems-project/elasticms/pull/1676
* fix(admin/user): canonical fields must be updated on change by @theus77 in https://github.com/ems-project/elasticms/pull/1684
* fix(common/asset): do not try to get the ems archive if it is missing by @theus77 in https://github.com/ems-project/elasticms/pull/1675
* fix(common/json-menu-nested): context may define testId by @theus77 in https://github.com/ems-project/elasticms/pull/1669
* fix(common/search): correct serialization suggest by @Davidmattei in https://github.com/ems-project/elasticms/pull/1639
* fix(common/spreadsheets): generate normalized xlsx files by @theus77 in https://github.com/ems-project/elasticms/pull/1646
* fix(docker): allow to overwrite the default sandbox environment variables by @theus77 in https://github.com/ems-project/elasticms/pull/1678
* fix(web/asset): add $skipUnzip flag to use the $saveDir as $publishPath by @theus77 in https://github.com/ems-project/elasticms/pull/1662
* fix(web/assets): emsch_assets_version's publishPath parameter by @theus77 in https://github.com/ems-project/elasticms/pull/1663
* fix(web/routing): avoid to break everything if one route is broken by @theus77 in https://github.com/ems-project/elasticms/pull/1654
* fix(web/search): empty suggest in search by @Davidmattei in https://github.com/ems-project/elasticms/pull/1664
* fix: publiccode.yml validation warnings by @bfabio in https://github.com/ems-project/elasticms/pull/1631
### Documentation
* docs(dev): install php wih brew on os x by @theus77 in https://github.com/ems-project/elasticms/pull/1682
* docs: install local dev on os x by @theus77 in https://github.com/ems-project/elasticms/pull/1689
* docs: new roadmap by @theus77 in https://github.com/ems-project/elasticms/pull/1655
### Code Refactoring
* refactor(admin/user): deprecated UserService::update by @theus77 in https://github.com/ems-project/elasticms/pull/1685
### Chores
* chore(make): add new target 'make pull' for running docker compose pull by @theus77 in https://github.com/ems-project/elasticms/pull/1683
* chore: rector, phpstan, phpcs, linting and format docs by @Davidmattei in https://github.com/ems-project/elasticms/pull/1659

## 7.1.0 (2026-04-07)
### Features
* feat(admin): tiptap basic implementation by @Davidmattei in https://github.com/ems-project/elasticms/pull/1632
* feat(adminUI): vite8 and eslint 10 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1624
* feat(common/archive): new functions add/replace and remove archive by @IsaMic in https://github.com/ems-project/elasticms/pull/1627
* feat(common/spreadsheet): cell type option 's' for string by @IsaMic in https://github.com/ems-project/elasticms/pull/1626
* feat(demo): json error template (+add content if 400 bad request) by @theus77 in https://github.com/ems-project/elasticms/pull/1617
* feat(web/inline-edit): implement a inline editor by @Davidmattei in https://github.com/ems-project/elasticms/pull/1599
### Bug Fixes
* fix(web): local push route without query by @theus77 in https://github.com/ems-project/elasticms/pull/1622
* fix(web): not found page should return a 404 code by @theus77 in https://github.com/ems-project/elasticms/pull/1633
* fix(wysiwyg): correct load in core & adminUI bundle by @Davidmattei in https://github.com/ems-project/elasticms/pull/1630
### Code Refactoring
* refactor(admin): remove ckeditor5 beta implementation by @Davidmattei in https://github.com/ems-project/elasticms/pull/1629
### Chores
* chore: redis multi archi image by @theus77 in https://github.com/ems-project/elasticms/pull/1623
* chore: replace mailhog by mailpit by @theus77 in https://github.com/ems-project/elasticms/pull/1618

## 7.0.0 (2026-03-04)
### Features
* feat(admin): manage webhooks by @theus77 in https://github.com/ems-project/elasticms/pull/1512
* feat(admin/api): index data from asset by @theus77 in https://github.com/ems-project/elasticms/pull/1472
* feat(admin/dashboard): add permission check to overdue filter in task dashboard by @coppee in https://github.com/ems-project/elasticms/pull/1468
* feat(admin/webhook): newIndexEvent and revisionDeleteEvent by @theus77 in https://github.com/ems-project/elasticms/pull/1474
* feat(build): docker build for admin/cli/web by @zebby76 in https://github.com/ems-project/elasticms/pull/1562
* feat(cache): varnish with web hooks and tag collectors by @theus77 in https://github.com/ems-project/elasticms/pull/1460
* feat(cli/command): add multifile file field to archive command by @theus77 in https://github.com/ems-project/elasticms/pull/1571
* feat(cli/command): dead links report by @theus77 in https://github.com/ems-project/elasticms/pull/1591
* feat(common): JSON menu nested add getPosition method by @theus77 in https://github.com/ems-project/elasticms/pull/1540
* feat(common/cli): abstractCommand get EMSLink helpers by @theus77 in https://github.com/ems-project/elasticms/pull/1511
* feat(common/storage): new _path_in_archive for processing file in archive by @theus77 in https://github.com/ems-project/elasticms/pull/1448
* feat(demo): add emsch_archive route by @theus77 in https://github.com/ems-project/elasticms/pull/1451
* feat(doc): add prettier and linter by @Davidmattei in https://github.com/ems-project/elasticms/pull/1461
* feat(doc): use vitepress instead of docsify by @Davidmattei in https://github.com/ems-project/elasticms/pull/1452
* feat(docker): enable opcache preload by @Davidmattei in https://github.com/ems-project/elasticms/pull/1614
* feat(docker): replaces Apache with Nginx for Admin Docker image by @zebby76 in https://github.com/ems-project/elasticms/pull/1587
* feat(docker): replaces Apache with Nginx for Website Docker image by @zebby76 in https://github.com/ems-project/elasticms/pull/1601
* feat(elasticSearch): upgrade support eleasticSearch 8 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1557
* feat(elasticms): php8.5 and symfony7.4 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1522
* feat(phpunit): upgrade 11.5 to 12.5 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1549
* feat(rector): codeQuality by @Davidmattei in https://github.com/ems-project/elasticms/pull/1590
* feat(symfony): convert xml routes to php by @Davidmattei in https://github.com/ems-project/elasticms/pull/1541
* feat(webhook): new alias update event by @theus77 in https://github.com/ems-project/elasticms/pull/1507
* feat(webhooks): add emsco_webhook, emsch_webhook_event, ems_clear_http_caches twig functions by @theus77 in https://github.com/ems-project/elasticms/pull/1477
* feat: open telemetry by @theus77 in https://github.com/ems-project/elasticms/pull/1413
### Bug Fixes
* fix(admin): small regression bugs by @Davidmattei in https://github.com/ems-project/elasticms/pull/1604
* fix(admin/cache): doctrine meta cache warmup with lazy repositories by @Davidmattei in https://github.com/ems-project/elasticms/pull/1560
* fix(admin/fields): remove deprecated JsonFieldFype by @Davidmattei in https://github.com/ems-project/elasticms/pull/1582
* fix(admin/form): form label empty calling deprecated __toString by @Davidmattei in https://github.com/ems-project/elasticms/pull/1576
* fix(admin/web): remove default values for EMS_STORAGES by @Davidmattei in https://github.com/ems-project/elasticms/pull/1583
* fix(admin/web): remove deprecation twig and routes by @Davidmattei in https://github.com/ems-project/elasticms/pull/1573
* fix(build): fork stevenmaguire/oauth2-keycloak for jwt 7 support by @Davidmattei in https://github.com/ems-project/elasticms/pull/1602
* fix(build): setup 7 series release by @Davidmattei in https://github.com/ems-project/elasticms/pull/1597
* fix(cli): remove deprecated TextHelper by @Davidmattei in https://github.com/ems-project/elasticms/pull/1595
* fix(common/api): /upload-chunk/{sha1} replaced by /chunk/{hash} by @theus77 in https://github.com/ems-project/elasticms/pull/1580
* fix(common/coreApi): runCommand use new core JobApiController by @Davidmattei in https://github.com/ems-project/elasticms/pull/1566
* fix(docker): NGINX / PHP-FPM configuration  by @zebby76 in https://github.com/ems-project/elasticms/pull/1611
* fix(docker): add OTEL_ENABLED variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1564
* fix(docker): correct script execution order by @zebby76 in https://github.com/ems-project/elasticms/pull/1616
* fix(docker): fix and improve NGINX / PHP configuration by @zebby76 in https://github.com/ems-project/elasticms/pull/1606
* fix(docker): optional include docker .env file by @Davidmattei in https://github.com/ems-project/elasticms/pull/1577
* fix(docker): upgrade traefik v2.2 to v3.6 (issue with docker API version >= 1.52) by @theus77 in https://github.com/ems-project/elasticms/pull/1480
* fix(elasticms): remove bundles deprecations by @Davidmattei in https://github.com/ems-project/elasticms/pull/1574
* fix(env): correct EMS_ELASTICSEARCH_HOSTS by @Davidmattei in https://github.com/ems-project/elasticms/pull/1605
* fix(nginx): Fix incorrect SERVER_NAME passed to PHP by @zebby76 in https://github.com/ems-project/elasticms/pull/1598
* fix(phpstan): deprecations and baseline issues by @Davidmattei in https://github.com/ems-project/elasticms/pull/1538
* fix(phpstan): request get method and array offsetAccess by @Davidmattei in https://github.com/ems-project/elasticms/pull/1536
* fix(rector): coding style by @Davidmattei in https://github.com/ems-project/elasticms/pull/1593
* fix(rector): deadCode rules by @Davidmattei in https://github.com/ems-project/elasticms/pull/1592
* fix(security): admin & web trusted hosts & proxies by @Davidmattei in https://github.com/ems-project/elasticms/pull/1613
* fix(symfony): xmlFileLoader deprecated by @Davidmattei in https://github.com/ems-project/elasticms/pull/1537
* fix(twig): clientHelper and common bundle attribute extensions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1586
* fix(twig): core, form and  submission bundle attribute extensions by @Davidmattei in https://github.com/ems-project/elasticms/pull/1588
* fix(twig): rollback twig attributes by @Davidmattei in https://github.com/ems-project/elasticms/pull/1568
* fix(twig): symfony request::get() is deprecated by @Davidmattei in https://github.com/ems-project/elasticms/pull/1544
* fix(twig): translatableMessage __toString deprecated by @Davidmattei in https://github.com/ems-project/elasticms/pull/1546
* fix(web): SearchController is deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/1510
* fix(web/admin): regression bugs form and jsonMenuNested by @Davidmattei in https://github.com/ems-project/elasticms/pull/1610
* fix(web/form): browser plugin false warning and webpack upgrade by @theus77 in https://github.com/ems-project/elasticms/pull/1499
* fix(xliff): replace commonBundle dependency with ems helpers by @theus77 in https://github.com/ems-project/elasticms/pull/1550
* fix: remove fork stevenmaguire/oauth2-keycloak by @Davidmattei in https://github.com/ems-project/elasticms/pull/1615
* fix: resolve symfony 7.4 deprections by @Davidmattei in https://github.com/ems-project/elasticms/pull/1542
### Documentation
* docs(dev): install OpenTelemetry (linux,macOS,source) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1561
* docs: php 8.4 -> php 8.5 by @theus77 in https://github.com/ems-project/elasticms/pull/1556
* docs: publiccode.yml by @theus77 in https://github.com/ems-project/elasticms/pull/1551
* docs: roadmap by @theus77 in https://github.com/ems-project/elasticms/pull/1555
### Styles
* style(twigcs): enable twigcs standard rules by @michaeldk in https://github.com/ems-project/elasticms/pull/1516
### Code Refactoring
* refactor(docker): migrate app config to PHP-FPM environment and decouple metrics by @zebby76 in https://github.com/ems-project/elasticms/pull/1594
* refactor(styling): improve bs5 styling by @michaeldk in https://github.com/ems-project/elasticms/pull/1528
* refactor: xliff library by @theus77 in https://github.com/ems-project/elasticms/pull/1527
### Tests
* test: cypress first tests by @theus77 in https://github.com/ems-project/elasticms/pull/1579
### Chores
* chore(demo): update docker and elasticms configuration for 7.x by @zebby76 in https://github.com/ems-project/elasticms/pull/1603
* chore(docker): drop elk7 and new single node setup by @Davidmattei in https://github.com/ems-project/elasticms/pull/1525
* chore(lincense): use LGPL-3.0-or-later by @Davidmattei in https://github.com/ems-project/elasticms/pull/1543
* chore(rector): rector symfony7.4 php8.5 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1565
* chore(updrade): upgrade dependencies by @Davidmattei in https://github.com/ems-project/elasticms/pull/1548
* chore: symfony 7.4.5 and phpcs 3.93.1 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1547
* chore: update elasticMS logos by @theus77 in https://github.com/ems-project/elasticms/pull/1515

