# Changelog 7.x

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

