# Changelog 6.x

## 6.0.0 (2025-02-03)
### Features
* feat(admin): media library and datatable improvements by @Davidmattei in https://github.com/ems-project/elasticms/pull/1005
* feat(admin): wywiwyg editor field for ckeditor 4 or 5 by @theus77 in https://github.com/ems-project/elasticms/pull/1129
* feat(admin-ui): migrate bootstrap 3 to 5 by @sylver4 in https://github.com/ems-project/elasticms/pull/596
* feat(admin/jmn): implement json menu nested in 6.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/984
* feat(admin/media-lib):  support for media library 6.x by @Davidmattei in https://github.com/ems-project/elasticms/pull/992
* feat(bridge): improve bridge data delete and info bridge for documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1154
* feat(cli/command): add ems:document:merge  by @theus77 in https://github.com/ems-project/elasticms/pull/1113
* feat(common/api): use sf client this enables profiler by @Davidmattei in https://github.com/ems-project/elasticms/pull/1144
* feat(core/field-type): new FieldTypeService for building tree by @Davidmattei in https://github.com/ems-project/elasticms/pull/788
* feat(core/users): permissions overview (r-n-r-summary) by @OzkanO2 in https://github.com/ems-project/elasticms/pull/780
* feat(demo): use vite instead of webpack by @Davidmattei in https://github.com/ems-project/elasticms/pull/1178
* feat(docker): Add a NPM_EXTRA_CMD to customize the npm config by @theus77 in https://github.com/ems-project/elasticms/pull/1175
* feat(elasticsearch/mapping): new EMSCO_DYNAMIC_MAPPING  configuration by @theus77 in https://github.com/ems-project/elasticms/pull/711
* feat(ems): add core bridge (api & service) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1143
* feat(web/emsch): add FormController with FormType by @Davidmattei in https://github.com/ems-project/elasticms/pull/1138
* feat(wysiwyg/preview): prefixed styleset css, remove iframe by @theus77 in https://github.com/ems-project/elasticms/pull/790
* feat: edit image in CKE5 by @theus77 in https://github.com/ems-project/elasticms/pull/872
* feat: require php 8.4 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1123
* feat: upgrade doctrine orm 3.0 by @Davidmattei in https://github.com/ems-project/elasticms/pull/1161
### Bug Fixes
* fix(admin): hide sensitive info to non authenticated users by @theus77 in https://github.com/ems-project/elasticms/pull/758
* fix(admin): migration issues by @Davidmattei in https://github.com/ems-project/elasticms/pull/1122
* fix(admin): slug config names use separator '_' by @Davidmattei in https://github.com/ems-project/elasticms/pull/1173
* fix(admin/field): add help text support for VersionTagFieldType by @theus77 in https://github.com/ems-project/elasticms/pull/1170
* fix(admin/submission): export command use ems property accessor  by @theus77 in https://github.com/ems-project/elasticms/pull/1142
* fix(admin/twig): escape label in data links by @theus77 in https://github.com/ems-project/elasticms/pull/1132
* fix(autosave): empty warnings by @theus77 in https://github.com/ems-project/elasticms/pull/792
* fix(bootstrap 5): need a clearfix when text counter and no help by @theus77 in https://github.com/ems-project/elasticms/pull/839
* fix(common/twig): add ems_asset_head(s) and implement for wysiwyg field by @theus77 in https://github.com/ems-project/elasticms/pull/1171
* fix(core/bootstrap5): tabs in edit revision by @theus77 in https://github.com/ems-project/elasticms/pull/734
* fix(core/flash-messages): obvious duplicate alerts by @theus77 in https://github.com/ems-project/elasticms/pull/794
* fix(core/rector): entities $id is not readonly by @theus77 in https://github.com/ems-project/elasticms/pull/735
* fix(core/revision): empty environment actions, no dropdown by @Davidmattei in https://github.com/ems-project/elasticms/pull/885
* fix(core/twig): html escape special chars in display label by @Davidmattei in https://github.com/ems-project/elasticms/pull/1124
* fix(core/ui): select2 icon picker by @theus77 in https://github.com/ems-project/elasticms/pull/1158
* fix(core/wywiwyg): delegate label to RevisionService::display by @theus77 in https://github.com/ems-project/elasticms/pull/840
* fix(demo): double css import by @theus77 in https://github.com/ems-project/elasticms/pull/791
* fix(demo): using session in stateless api calls by @Davidmattei in https://github.com/ems-project/elasticms/pull/776
* fix(demo/structure): get default environment for section by @theus77 in https://github.com/ems-project/elasticms/pull/709
* fix(docker): introduce POSTGRES_VERSION env variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/1137
* fix(file-structure::push): avoid silent error when it's not possible to update the .hash file by @theus77 in https://github.com/ems-project/elasticms/pull/1146
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
* fix(twig): deprecate ems function and filters with deprecation_info by @Davidmattei in https://github.com/ems-project/elasticms/pull/1125
* fix(web): error templates based on request format (json, xml, html, ...) by @theus77 in https://github.com/ems-project/elasticms/pull/1152
* fix(web): performance issue with getHierarchy function by @theus77 in https://github.com/ems-project/elasticms/pull/1155
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
* chore: add POSTGRES_VERSION environment variable for dev docker by @theus77 in https://github.com/ems-project/elasticms/pull/1148
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

