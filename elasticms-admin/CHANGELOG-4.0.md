CHANGELOG for 4.x
===================

## [4.3.0-RC2](https://github.com/ems-project/elasticms/releases/tag/4.3.0-RC2) (17-11-2022)
  * fix: redis is prod env by @Davidmattei in #260
  * CoreBundle [4.3.0-RC2](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.3.0-RC2)
    * fix(phpstan): phpstan 1.9.1 by @Davidmattei in #1245
    * fix(views/search): not specify the value (0) of the env, use first by @coppee in #1242
    * fix(views/revision-data): add hasVersionTags check by @coppee in #1243
    * feat(environment): template publication by @Davidmattei in #1246
    * feat(environment): add publish role field by @Davidmattei in #1247
    * feat(revision): delete revision command by @Davidmattei in #1250
  * CommonBundle [4.3.0-RC2](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.3.0-RC2)
    * feat(command): abstract command multiple choices by @Davidmattei in #458
    * fix(command): user select 'all' for choice argument array by @Davidmattei in #459
  * FormBundle [4.2.1](https://github.com/ems-project/EMSFormBundle/releases/tag/4.2.1)
    * fix(phpstan): return type FormDataFile.php by @Davidmattei in #302

## [4.3.0-RC1](https://github.com/ems-project/elasticms/releases/tag/4.3.0-RC1) (01-11-2022)
  * CoreBundle [4.3.0-RC1](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.3.0-RC1)
    * feat(revision-task): assignee, owner, version filter dashboard by @Davidmattei in #1237
    * feat(revision-version): make version on publication by @Davidmattei in #1240
  * CommonBundle [4.3.0-RC1](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.3.0-RC1)
    * feat(twig): ems_hash filter by @theus77 in #456

## [4.2.2](https://github.com/ems-project/elasticms/releases/tag/4.2.2) (17-11-2022)
  * fix: redis is prod env by @Davidmattei in #260
  * CoreBundle [4.2.2](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.2.2)
    * fix(phpstan): phpstan 1.9.1 by @Davidmattei in #1245
    * fix(views/search): not specify the value (0) of the env, use first by @coppee in #1242
  * FormBundle [4.2.1](https://github.com/ems-project/EMSFormBundle/releases/tag/4.2.1)
    * fix(phpstan): return type FormDataFile.php by @Davidmattei in #302

## [4.2.1](https://github.com/ems-project/elasticms/releases/tag/4.2.1) (01-11-2022)
  * CoreBundle [4.2.1](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.2.1)
    * fix(revision-task): export task dashboard by @Davidmattei in #1230
    * fix(revision-task): dashboard print modified column correctly by @Davidmattei in #1231
    * fix(revision-view): custom data link view no results > returns all by @Davidmattei in #1233
    * fix(environment): align view broken by @Davidmattei in #1234
    * fix(environment): recompute command broken by @Davidmattei in #1235
    * fix(route): change upload file api endpoint by @coppee in #1239   
    * feat(revision-task): dashboard filter on status by @Davidmattei in #1232
    * chore(php81): compile error __set needs to return void by @Davidmattei in #1236
    * chore(phpcs): v3.13 by @Davidmattei in #1241
  * CommonBundle [4.2.1](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.2.1)
    * chore(phpcs): v3.13 by @Davidmattei in #457
  * ClientHelperBundle [4.2.1](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/4.2.1)
    * fix(api): change upload file core api endpoint by @coppee in #365

## [4.2.0](https://github.com/ems-project/elasticms/releases/tag/4.2.0) (24-10-2022)
  * CoreBundle [4.2.0](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.2.0)
    * fix: upgrade webpack (#1207) by @Davidmattei in [#1208](https://github.com/ems-project/EMSCoreBundle/pull/1208)
    * fix: revision task current nullable by @Davidmattei in [#1211](https://github.com/ems-project/EMSCoreBundle/pull/1211)
    * fix: search query by @Davidmattei in [#1214](https://github.com/ems-project/EMSCoreBundle/pull/1214)
    * fix(500): edit the main container by @Davidmattei in [#1212](https://github.com/ems-project/EMSCoreBundle/pull/1212)
    * fix(contentType): search link display (external env) by @Davidmattei in [#1215](https://github.com/ems-project/EMSCoreBundle/pull/1215)
    * fix(security): ldap handle exceptions by @Davidmattei in [#1216](https://github.com/ems-project/EMSCoreBundle/pull/1216)
    * fix(view): role not required by @Davidmattei in [#1217](https://github.com/ems-project/EMSCoreBundle/pull/1217)
    * fix(js): treat not defined (build prod no source map) by @Davidmattei in [#1222](https://github.com/ems-project/EMSCoreBundle/pull/1222)
    * fix(json-nested): custom blocks not working in 4.0 by @Davidmattei in [#1223](https://github.com/ems-project/EMSCoreBundle/pull/1223)
    * fix: change _self target links to _parent in iframe preview by @coppee in [#1227](https://github.com/ems-project/EMSCoreBundle/pull/1227)
    * feat(task): add created modified (default sort) by @Davidmattei in [#1221](https://github.com/ems-project/EMSCoreBundle/pull/1221)
    * feat(wysiwyg): profile ems settings by @Davidmattei in [#1219](https://github.com/ems-project/EMSCoreBundle/pull/1219)
    * feat: treat accept reject disabled by @IsaMic in [#1220](https://github.com/ems-project/EMSCoreBundle/pull/1220)
    * feat(user): options and simplied UI by @Davidmattei in [#1224](https://github.com/ems-project/EMSCoreBundle/pull/1224)
    * feat(dataLink): check ct view role, no link if not granted by @Davidmattei in [#1226](https://github.com/ems-project/EMSCoreBundle/pull/1226)
    * feat: contenttype fields by @Davidmattei in [#1228](https://github.com/ems-project/EMSCoreBundle/pull/1228)
    * refactor(contentType): create roles json plus migration by @Davidmattei in [#1225](https://github.com/ems-project/EMSCoreBundle/pull/1225)
    * chore: deprecations on cache:clear dev by @Davidmattei in [#1213](https://github.com/ems-project/EMSCoreBundle/pull/1213)
    * chore: build assets for #1227 by @Davidmattei in [#1229](https://github.com/ems-project/EMSCoreBundle/pull/1229)
  * CommonBundle [4.2.0](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.2.0)
    * fix: dont throw excection if the temp upload file is missing by @theus77 in [#455](https://github.com/ems-project/EMSCommonBundle/pull/455)
  * ClientHelperBundle [4.2.0](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/4.2.0)
  * FormBundle [4.2.0](https://github.com/ems-project/EMSFormBundle/releases/tag/4.2.0)
  * SubmissionBundle [4.2.0](https://github.com/ems-project/SubmissionBundle/releases/tag/4.2.0)

## [4.1.1](https://github.com/ems-project/elasticms/releases/tag/4.1.1) (04-10-2022)
  * CoreBundle [4.1.1](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.1.1)
  * FormBundle [4.1.1](https://github.com/ems-project/EMSFormBundle/releases/tag/4.1.1)

## [4.1.0](https://github.com/ems-project/elasticms/releases/tag/4.1.0) (09-09-2022)
  * CoreBundle [4.1.0](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.1.0)
  * CommonBundle [4.1.0](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.1.0)
  * ClientHelperBundle [4.1.0](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/4.1.0)
  * FormBundle [4.1.0](https://github.com/ems-project/EMSFormBundle/releases/tag/4.1.0)
  * SubmissionBundle [4.1.0](https://github.com/ems-project/SubmissionBundle/releases/tag/4.1.0)

## [4.0.1](https://github.com/ems-project/elasticms/releases/tag/4.0.1) (09-09-2022)
  * CoreBundle [4.0.1](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.0.1)
  * CommonBundle [4.0.1](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.0.1)

## [4.0.0](https://github.com/ems-project/elasticms/releases/tag/4.0.0) (06-09-2022)
  * CoreBundle [4.0.0](https://github.com/ems-project/EMSCoreBundle/releases/tag/4.0.0)
  * CommonBundle [4.0.0](https://github.com/ems-project/EMSCommonBundle/releases/tag/4.0.0)
  * ClientHelperBundle [4.0.0](https://github.com/ems-project/EMSClientHelperBundle/releases/tag/4.0.0)
  * FormBundle [4.0.0](https://github.com/ems-project/EMSFormBundle/releases/tag/4.0.0)
  * SubmissionBundle [4.0.0](https://github.com/ems-project/SubmissionBundle/releases/tag/4.0.0)