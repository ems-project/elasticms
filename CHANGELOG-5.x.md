# Changelog 5.x

## 5.24.3 (2024-12-17)
### Bug Fixes
* fix(common/pdf): dom pdf correct set $sysCacheDir by @theus77 in https://github.com/ems-project/elasticms/pull/1103
* fix(web/local): upload assets heads returns true by @Davidmattei in https://github.com/ems-project/elasticms/pull/1105

## 5.24.2 (2024-12-09)
### Bug Fixes
* fix(admin/asset): extractor test the filesize before downloading it by @theus77 in https://github.com/ems-project/elasticms/pull/1096
* fix(admin/asset): on upload correct set preview link by @theus77 in https://github.com/ems-project/elasticms/pull/1098
* fix(admin/field): deep recompute time field type by @theus77 in https://github.com/ems-project/elasticms/pull/1094
* fix(admin/job): avoid that JSON messages are hidden (overwrite) by @theus77 in https://github.com/ems-project/elasticms/pull/1100
* fix(admin/revision): restore trash locked exception by @Davidmattei in https://github.com/ems-project/elasticms/pull/1087
* fix(cli/file-reader): correct fallback with $fileIdentifier by @theus77 in https://github.com/ems-project/elasticms/pull/1093
* fix(cli/file-reader): get file from api by @theus77 in https://github.com/ems-project/elasticms/pull/1091
* fix(cli/file-reader): import miss formated excel by @theus77 in https://github.com/ems-project/elasticms/pull/1090
* fix(cli/file-reader): not found config fallback get config from api by @theus77 in https://github.com/ems-project/elasticms/pull/1092
* fix(common/mime-types): add XLIFF mimetype support by @theus77 in https://github.com/ems-project/elasticms/pull/1097
* fix(common/pdf): allow domPDF to use TempFile (in the system dir) by @theus77 in https://github.com/ems-project/elasticms/pull/1086
* fix(helper/file): csv remove utf8-bom by @Davidmattei in https://github.com/ems-project/elasticms/pull/1101
* fix(web/client): updateStyleSets set correct mimetype and filename by @theus77 in https://github.com/ems-project/elasticms/pull/1099

## 5.24.1 (2024-12-02)
### Features
* feat(common/file-reader): correct trimming white spaces and keep "0" values by @Davidmattei in https://github.com/ems-project/elasticms/pull/1082
### Bug Fixes
* fix(common): perf issues with etag behoind apache with deflate mod by @theus77 in https://github.com/ems-project/elasticms/pull/1084
* fix(common/helper): the mimetype guesser is not good at recognize text  file by @theus77 in https://github.com/ems-project/elasticms/pull/1083

## 5.24.0 (2024-11-26)
### Features
* feat(admin/cli): forward form submissions by @theus77 in https://github.com/ems-project/elasticms/pull/1070
* feat(cli): preload archive in cache by @theus77 in https://github.com/ems-project/elasticms/pull/1079
* feat(common/api): publish api (client/server)+ command by @theus77 in https://github.com/ems-project/elasticms/pull/1080
* feat(helper/uuid): generate uuid from value by @Davidmattei in https://github.com/ems-project/elasticms/pull/1064
### Bug Fixes
* fix(admin/cli): activate content type command fix configure by @Davidmattei in https://github.com/ems-project/elasticms/pull/1074
* fix(admin/cli): reindex command doctrine paginate with bulkSize by @Davidmattei in https://github.com/ems-project/elasticms/pull/1071
* fix(admin/revision): data not consumed error on publishing new version by @Davidmattei in https://github.com/ems-project/elasticms/pull/1066
* fix(admin/revision): finalize draft setMetaFields by @Davidmattei in https://github.com/ems-project/elasticms/pull/1065
* fix(admin/revision): no 'data_not_consumed' warning for private fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/1069
* fix(cli/command): file import config normal merge (for overwritting) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1063
* fix(cli/command): file reader import generate version uuid by @Davidmattei in https://github.com/ems-project/elasticms/pull/1067
* fix(cli/file-import): improvements and config(s) hashes or files by @Davidmattei in https://github.com/ems-project/elasticms/pull/1056
* fix(cli/file-structure): improve push command (save_hash_file, chunk size) by @theus77 in https://github.com/ems-project/elasticms/pull/1076
### Code Refactoring
* refactor(web): routing and templating match the content type name by @theus77 in https://github.com/ems-project/elasticms/pull/1073

## 5.23.5 (2024-12-17)
### Bug Fixes
* fix(common/pdf): dom pdf correct set $sysCacheDir by @theus77 in https://github.com/ems-project/elasticms/pull/1103

## 5.23.4 (2024-12-09)
### Bug Fixes
* fix(admin/asset): extractor test the filesize before downloading it by @theus77 in https://github.com/ems-project/elasticms/pull/1096
* fix(admin/asset): on upload correct set preview link by @theus77 in https://github.com/ems-project/elasticms/pull/1098
* fix(admin/field): deep recompute time field type by @theus77 in https://github.com/ems-project/elasticms/pull/1094
* fix(admin/job): avoid that JSON messages are hidden (overwrite) by @theus77 in https://github.com/ems-project/elasticms/pull/1100
* fix(admin/revision): restore trash locked exception by @Davidmattei in https://github.com/ems-project/elasticms/pull/1087
* fix(cli/file-reader): import miss formated excel by @theus77 in https://github.com/ems-project/elasticms/pull/1090
* fix(common/mime-types): add XLIFF mimetype support by @theus77 in https://github.com/ems-project/elasticms/pull/1097
* fix(common/pdf): allow domPDF to use TempFile (in the system dir) by @theus77 in https://github.com/ems-project/elasticms/pull/1086
* fix(web/client): updateStyleSets set correct mimetype and filename by @theus77 in https://github.com/ems-project/elasticms/pull/1099

## 5.23.3 (2024-11-26)
### Bug Fixes
* fix(admin/field): multiple asset must use the id of the children (not the parent) by @theus77 in https://github.com/ems-project/elasticms/pull/1075
* fix(common/storage): If 404 the file is missing (otherwise throws an error) in HTTP storage service by @theus77 in https://github.com/ems-project/elasticms/pull/1077

## 5.23.2 (2024-11-12)
### Bug Fixes
* fix(admin/form-submission): remove file fields (which are saved as empty array) by @theus77 in https://github.com/ems-project/elasticms/pull/1072
* fix(admin/revision): add ouuid_idx for better performance by @theus77 in https://github.com/ems-project/elasticms/pull/1068

## 5.23.1 (2024-11-04)
### Bug Fixes
* fix(admin/contentType): locale option array on dateRange by @Davidmattei in https://github.com/ems-project/elasticms/pull/1059
* fix(admin/dataTable): ajaxData is in export format (links are just text) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1058
* fix(admin/submission): non required in submisssion by @theus77 in https://github.com/ems-project/elasticms/pull/1061
* fix(common/log): add channel_ouuid_idx for performance by @theus77 in https://github.com/ems-project/elasticms/pull/1060

## 5.23.0 (2024-10-28)
### Features
* feat(admin-web/assets): new archive ems by @theus77 in https://github.com/ems-project/elasticms/pull/1041
* feat(admin/release): unpublish revision table use es query by @Davidmattei in https://github.com/ems-project/elasticms/pull/1052
* feat(common/cli): file structure api or storage manager by @Davidmattei in https://github.com/ems-project/elasticms/pull/1045
* feat(common/twig): new filter ems_valid_email by @Davidmattei in https://github.com/ems-project/elasticms/pull/1051
* feat(contentType/field): asset field type add label field by @theus77 in https://github.com/ems-project/elasticms/pull/1055
* feat(contentType/version): new not blank version option by @Davidmattei in https://github.com/ems-project/elasticms/pull/1054
### Bug Fixes
* fix(common/storage): log error on register services by @Davidmattei in https://github.com/ems-project/elasticms/pull/1046
### Code Refactoring
* refactor(admin): useless security access control since PR#1039 by @theus77 in https://github.com/ems-project/elasticms/pull/1057
* refactor(asset): deprecated emsch proxy routes by @theus77 in https://github.com/ems-project/elasticms/pull/1050
* refactor(common/cli): file structure use new ems archive by @theus77 in https://github.com/ems-project/elasticms/pull/1043
### Chores
* chore(doc): add example ems_dom_crawler twig filter by @Davidmattei in https://github.com/ems-project/elasticms/pull/1047

## 5.22.4 (2024-12-09)
### Bug Fixes
* fix(admin/asset): extractor test the filesize before downloading it by @theus77 in https://github.com/ems-project/elasticms/pull/1096
* fix(admin/asset): on upload correct set preview link by @theus77 in https://github.com/ems-project/elasticms/pull/1098
* fix(admin/field): deep recompute time field type by @theus77 in https://github.com/ems-project/elasticms/pull/1094
* fix(admin/job): avoid that JSON messages are hidden (overwrite) by @theus77 in https://github.com/ems-project/elasticms/pull/1100
* fix(admin/revision): restore trash locked exception by @Davidmattei in https://github.com/ems-project/elasticms/pull/1087
* fix(common/mime-types): add XLIFF mimetype support by @theus77 in https://github.com/ems-project/elasticms/pull/1097

## 5.22.3 (2024-11-04)
### Bug Fixes
* fix(admin/contentType): locale option array on dateRange by @Davidmattei in https://github.com/ems-project/elasticms/pull/1059

## 5.22.2 (2024-10-28)
### Bug Fixes
* fix(admin/contentType): locale default null not 'en' by @Davidmattei in https://github.com/ems-project/elasticms/pull/1049
* fix(admin/security): give public access to route prefixed with /bundle by @theus77 in https://github.com/ems-project/elasticms/pull/1053
* fix(common/storage): s3 upload key bucket aware by @Davidmattei in https://github.com/ems-project/elasticms/pull/1044

## 5.22.1 (2024-10-11)
### Bug Fixes
* fix(admin/cli): reindex skip lock and with_warnings false by @Davidmattei in https://github.com/ems-project/elasticms/pull/1035
* fix(admin/document): display external content by @Davidmattei in https://github.com/ems-project/elasticms/pull/1037
* fix(ems/asset): unzip assets in admin (both in edit and view revision) by @theus77 in https://github.com/ems-project/elasticms/pull/1039

## 5.22.0 (2024-10-08)
### Features
* feat(admin/contentType): dataLinks support display expression by @Davidmattei in https://github.com/ems-project/elasticms/pull/1032
* feat(admin/mapping): add synonym filter and implement into demo by @theus77 in https://github.com/ems-project/elasticms/pull/1016
* feat(admin/tasks): rename task manager to task admin by @Davidmattei in https://github.com/ems-project/elasticms/pull/1021
* feat(admin/twig): json menu nested copy/paste functionality by @Davidmattei in https://github.com/ems-project/elasticms/pull/1033
* feat(admin/user): new get user language and sort tabs by @Davidmattei in https://github.com/ems-project/elasticms/pull/1026
* feat(admin/wysiwyg): add data attributes in iframe preview by @Davidmattei in https://github.com/ems-project/elasticms/pull/1029
* feat(common/twig): add new filter ems_link by @Davidmattei in https://github.com/ems-project/elasticms/pull/1028
### Bug Fixes
* fix(admin/datatable): revert remove table-responsive by @Davidmattei in https://github.com/ems-project/elasticms/pull/1023
* fix(admin/filter): remove btn with a2lix_lib by @theus77 in https://github.com/ems-project/elasticms/pull/1017
* fix(admin/revision): trash view only working for super user (sorting) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1031
* fix(admin/twig-component):  update json menu nested template by @Davidmattei in https://github.com/ems-project/elasticms/pull/1025
* fix(xliff): drop ZWSP or SHY characters by @theus77 in https://github.com/ems-project/elasticms/pull/1014
### Documentation
* docs(admin): describe type of field (content types) by @theus77 in https://github.com/ems-project/elasticms/pull/1019
### Code Refactoring
* refactor(admin/cli): replace EmsCommand.php by AbstractCommand from c…   …ommon by @Davidmattei in https://github.com/ems-project/elasticms/pull/1022
### Chores
* chore(demo): remove double 'french_stop' from backup. by @theus77 in https://github.com/ems-project/elasticms/pull/1018

## 5.21.5 (2024-12-09)
### Bug Fixes
* fix(admin/asset): extractor test the filesize before downloading it by @theus77 in https://github.com/ems-project/elasticms/pull/1096
* fix(admin/asset): on upload correct set preview link by @theus77 in https://github.com/ems-project/elasticms/pull/1098
* fix(admin/field): deep recompute time field type by @theus77 in https://github.com/ems-project/elasticms/pull/1094
* fix(admin/job): avoid that JSON messages are hidden (overwrite) by @theus77 in https://github.com/ems-project/elasticms/pull/1100
* fix(admin/revision): restore trash locked exception by @Davidmattei in https://github.com/ems-project/elasticms/pull/1087

## 5.21.4 (2024-10-28)
### Bug Fixes
* fix(admin/security): give public access to route prefixed with /bundle by @theus77 in https://github.com/ems-project/elasticms/pull/1053

## 5.21.3 (2024-10-11)
### Bug Fixes
* fix(admin/datatable): revert remove table-responsive by @Davidmattei in https://github.com/ems-project/elasticms/pull/1023
* fix(admin/revision): trash view only working for super user (sorting) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1031
* fix(admin/twig-component):  update json menu nested template by @Davidmattei in https://github.com/ems-project/elasticms/pull/1025
* fix(ems/asset): unzip assets in admin (both in edit and view revision) by @theus77 in https://github.com/ems-project/elasticms/pull/1039

## 5.21.2 (2024-09-24)
### Bug Fixes
* fix(admin/environment): picker default name value by @Davidmattei in https://github.com/ems-project/elasticms/pull/1020
* fix(admin/wysiwyg): file links in fields (edit and revision view) by @theus77 in https://github.com/ems-project/elasticms/pull/1013

## 5.21.1 (2024-09-19)
### Bug Fixes
* fix(admin/asset-field): asset field remove 'sha1' and '_hash' field by @Davidmattei in https://github.com/ems-project/elasticms/pull/1011
* fix(admin/media-lib): fix invalid documents by @Davidmattei in https://github.com/ems-project/elasticms/pull/1008
* fix(admin/media-lib): media library keep selection and shift on select all by @Davidmattei in https://github.com/ems-project/elasticms/pull/1006
* fix(admin/media-lib): move file to home folder not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/1009
* fix(admin/release): layout issue, update labels and filter environments (default) by @Davidmattei in https://github.com/ems-project/elasticms/pull/1007

## 5.21.0 (2024-09-12)
### Features
* feat(admin/datatable): checkable option and actions with events by @Davidmattei in https://github.com/ems-project/elasticms/pull/1000
* feat(admin/media-lib): add sorting on file columns by @Davidmattei in https://github.com/ems-project/elasticms/pull/1002
* feat(admin/media-lib): select all, footer rendering and fix multiple upload by @Davidmattei in https://github.com/ems-project/elasticms/pull/1003
* feat(demo/media-lib): add date creation by @Davidmattei in https://github.com/ems-project/elasticms/pull/1004
### Bug Fixes
* fix(admin/release): improve labels and environment selects by @Davidmattei in https://github.com/ems-project/elasticms/pull/1001
* fix(common/api): api with large docs use EMSCO_LOCK_TIME by @theus77 in https://github.com/ems-project/elasticms/pull/996
### Code Refactoring
* refactor(common): use helper types and remove common types by @theus77 in https://github.com/ems-project/elasticms/pull/998

## 5.20.3 (2024-10-28)
### Bug Fixes
* fix(admin/security): give public access to route prefixed with /bundle by @theus77 in https://github.com/ems-project/elasticms/pull/1053

## 5.20.2 (2024-10-11)
### Bug Fixes
* fix(ems/asset): unzip assets in admin (both in edit and view revision) by @theus77 in https://github.com/ems-project/elasticms/pull/1039

## 5.20.1 (2024-09-03)
### Bug Fixes
* fix(common/api): emsch get via the admin API (EMS_ELASTICSEARCH_PROXY_API) by @theus77 in https://github.com/ems-project/elasticms/pull/997
* fix(common/storage): allow config or cache files to not be saved in any storage  by @theus77 in https://github.com/ems-project/elasticms/pull/995

## 5.20.0 (2024-08-23)
### Features
* feat(admin/contentType): implement dataTables by @Davidmattei in https://github.com/ems-project/elasticms/pull/978
* feat(admin/environment): implement dataTables (alias, managed, external and overview) by @Davidmattei in https://github.com/ems-project/elasticms/pull/981
* feat(admin/environments): environment orphan index datatable + translate Menu by @Davidmattei in https://github.com/ems-project/elasticms/pull/980
### Bug Fixes
* fix(admin/twig): align core and admin templates by @Davidmattei in https://github.com/ems-project/elasticms/pull/988
### Code Refactoring
* refactor(admin/datatable): i18n & wysiwyg datatables by @Davidmattei in https://github.com/ems-project/elasticms/pull/974

## 5.19.5 (2024-10-28)
### Bug Fixes
* fix(admin/security): give public access to route prefixed with /bundle by @theus77 in https://github.com/ems-project/elasticms/pull/1053

## 5.19.4 (2024-10-11)
### Bug Fixes
* fix(ems/asset): unzip assets in admin (both in edit and view revision) by @theus77 in https://github.com/ems-project/elasticms/pull/1039

## 5.19.3 (2024-09-03)
### Bug Fixes
* fix(common/api): emsch get via the admin API (EMS_ELASTICSEARCH_PROXY_API) by @theus77 in https://github.com/ems-project/elasticms/pull/997
* fix(common/storage): allow config or cache files to not be saved in any storage  by @theus77 in https://github.com/ems-project/elasticms/pull/995

## 5.19.2 (2024-08-23)
### Bug Fixes
* fix(common/storage): multipart upload (min 5mb) by @theus77 in https://github.com/ems-project/elasticms/pull/994

## 5.19.1 (2024-08-20)
### Bug Fixes
* fix(admin/asset): small images null string by @theus77 in https://github.com/ems-project/elasticms/pull/979
* fix(admin/command): recompute changed closing revisions by @Davidmattei in https://github.com/ems-project/elasticms/pull/977
* fix(admin/datatable): default sort job created desc by @Davidmattei in https://github.com/ems-project/elasticms/pull/975
* fix(admin/job): escape job commands trigger from UI by @Davidmattei in https://github.com/ems-project/elasticms/pull/976
* fix(admin/revision): is granted contentType edit role by @Davidmattei in https://github.com/ems-project/elasticms/pull/990
* fix(admin/revision): put back revision table action url by @Davidmattei in https://github.com/ems-project/elasticms/pull/982
* fix(admin/ui): modal blocking input fields by @Davidmattei in https://github.com/ems-project/elasticms/pull/989
* fix(cli/media-sync): add filesize limit and fix memory leak extraction by @Davidmattei in https://github.com/ems-project/elasticms/pull/983

## 5.19.0 (2024-07-29)
### Features
* feat(admin/command): add emsco:asset:refresh-file-fields (activate new fields) by @theus77 in https://github.com/ems-project/elasticms/pull/963
* feat(admin/command): contentType recompute new changed option by @Davidmattei in https://github.com/ems-project/elasticms/pull/941
* feat(admin/command): ems:job:run execute releases by @Davidmattei in https://github.com/ems-project/elasticms/pull/952
* feat(admin/image): add resize  with the client browser by @theus77 in https://github.com/ems-project/elasticms/pull/946
* feat(admin/image): resize on image from server by @theus77 in https://github.com/ems-project/elasticms/pull/969
* feat(admin/revision): improve datatable drafts and trash by @Davidmattei in https://github.com/ems-project/elasticms/pull/914
* feat(common/command): ems:admin:(backup|restore) export environment contentType by @theus77 in https://github.com/ems-project/elasticms/pull/968
* feat(common/twig): ems_file_from_archive twig function by @theus77 in https://github.com/ems-project/elasticms/pull/942
* feat(demo): add dashboards media library browsing by @Davidmattei in https://github.com/ems-project/elasticms/pull/944
* feat(web/azure): add service token functionality  by @Davidmattei in https://github.com/ems-project/elasticms/pull/973
* feat(web/sso): add support azure oauth2 by @Davidmattei in https://github.com/ems-project/elasticms/pull/960
### Bug Fixes
* fix(admin): environment not existing thows 500 error by @Davidmattei in https://github.com/ems-project/elasticms/pull/935
* fix(admin/command): emsco:contenttype:switch-default-env already switched return success by @Davidmattei in https://github.com/ems-project/elasticms/pull/972
* fix(admin/revision): collection enter key triggers delete first item by @Davidmattei in https://github.com/ems-project/elasticms/pull/954
* fix(admin/revision): duplicate ouuid put back deleted revision by @Davidmattei in https://github.com/ems-project/elasticms/pull/955
* fix(admin/user): export users switch last login and expiration date by @Davidmattei in https://github.com/ems-project/elasticms/pull/951
### Code Refactoring
* refactor(admin): archives in cache storage by @theus77 in https://github.com/ems-project/elasticms/pull/939
* refactor(admin): cache in storages by @theus77 in https://github.com/ems-project/elasticms/pull/930
* refactor(admin): coreControllerTrait instead of abstract class by @Davidmattei in https://github.com/ems-project/elasticms/pull/956
* refactor(admin): deprecate emsch_assets in favor of emsch_assets_version by @theus77 in https://github.com/ems-project/elasticms/pull/945
* refactor(admin): sys_get_temp_dir centralized in Helper classes (TempFile & TempDirectory) by @theus77 in https://github.com/ems-project/elasticms/pull/929
* refactor(admin/dataTable): filter & analyzer overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/966
* refactor(admin/datatable): jobs and extend dataTableTrait by @Davidmattei in https://github.com/ems-project/elasticms/pull/967
* refactor(admin/datatable): use correct translation keys and crud overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/962
* refactor(common/log): use translateMessage in LocalizedLogger by @Davidmattei in https://github.com/ems-project/elasticms/pull/958
* refactor(emsch/api): use common api by @Davidmattei in https://github.com/ems-project/elasticms/pull/940
### Chores
* chore(admin/web): improve dev logging (doctrine) by @Davidmattei in https://github.com/ems-project/elasticms/pull/947
* chore(build): .cache folder for ci tools by @Davidmattei in https://github.com/ems-project/elasticms/pull/948
* chore(build): disable http client verification by @Davidmattei in https://github.com/ems-project/elasticms/pull/949
* chore(build): dump translations sort by keys by @Davidmattei in https://github.com/ems-project/elasticms/pull/957
* chore(translations): support building multiline translations by @Davidmattei in https://github.com/ems-project/elasticms/pull/959

## 5.18.4 (2024-09-03)
### Bug Fixes
* fix(common/storage): allow config or cache files to not be saved in any storage  by @theus77 in https://github.com/ems-project/elasticms/pull/995

## 5.18.3 (2024-08-23)
### Bug Fixes
* fix(common/storage): multipart upload (min 5mb) by @theus77 in https://github.com/ems-project/elasticms/pull/994

## 5.18.2 (2024-07-29)
### Bug Fixes
* fix(admin/contentType): disable the choice field type if needed by @theus77 in https://github.com/ems-project/elasticms/pull/970
* fix(admin/revision): skip the FormFieldType's options in a container by @theus77 in https://github.com/ems-project/elasticms/pull/971

## 5.18.1 (2024-07-18)
### Bug Fixes
* fix(common/storage): return 404 response on not found by @Davidmattei in https://github.com/ems-project/elasticms/pull/938
* fix(demo): release search not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/943
* fix(emsco/media-lib): rename/delete command username with space by @Davidmattei in https://github.com/ems-project/elasticms/pull/961

## 5.18.0 (2024-06-26)
### Features
* feat(common/admin): update command dump file and onlyMissing option by @theus77 in https://github.com/ems-project/elasticms/pull/897
* feat(common/api): add EMS_BACKEND_API_TIMEOUT config by @theus77 in https://github.com/ems-project/elasticms/pull/900
* feat(core/twig): add emsco_save_contents twig function by @theus77 in https://github.com/ems-project/elasticms/pull/901
* feat(core/twig): add emsco_warning and emsco_notice functions by @theus77 in https://github.com/ems-project/elasticms/pull/905
* feat(core/view): define contentType default overview by @Davidmattei in https://github.com/ems-project/elasticms/pull/910
* feat(demo): add asset content type (documentation) by @Davidmattei in https://github.com/ems-project/elasticms/pull/907
* feat(demo): implement emsco_display for content types by @Davidmattei in https://github.com/ems-project/elasticms/pull/913
* feat(elasticms): add file structure commands for static websites by @theus77 in https://github.com/ems-project/elasticms/pull/917
* feat(elasticms): add force option to file structure publish (target out of sync) by @theus77 in https://github.com/ems-project/elasticms/pull/919
* feat(ems/command): add ems:batch command by @Davidmattei in https://github.com/ems-project/elasticms/pull/918
### Bug Fixes
* fix(cli): load twig bundle for (ems:batch) command by @Davidmattei in https://github.com/ems-project/elasticms/pull/927
* fix(cli/import): trim import headers by @Davidmattei in https://github.com/ems-project/elasticms/pull/908
* fix(core): translation key core.verion by @Davidmattei in https://github.com/ems-project/elasticms/pull/933
* fix(core/release): rework publish and unpublish by @Davidmattei in https://github.com/ems-project/elasticms/pull/912
### Code Refactoring
* refactor(core): extract translations by @Davidmattei in https://github.com/ems-project/elasticms/pull/916
* refactor: build translations with symfony 6 by @Davidmattei in https://github.com/ems-project/elasticms/pull/920

## 5.17.5 (2024-09-03)
### Bug Fixes
* fix(common/storage): allow config or cache files to not be saved in any storage  by @theus77 in https://github.com/ems-project/elasticms/pull/995

## 5.17.4 (2024-08-23)
### Bug Fixes
* fix(common/storage): multipart upload (min 5mb) by @theus77 in https://github.com/ems-project/elasticms/pull/994

## 5.17.3 (2024-07-18)
### Bug Fixes
* fix(emsco/media-lib): rename/delete command username with space by @Davidmattei in https://github.com/ems-project/elasticms/pull/961

## 5.17.2 (2024-06-26)
### Bug Fixes
* fix(common/storage): S3StreamPromise read by @theus77 in https://github.com/ems-project/elasticms/pull/924
* fix(core): environment modal(s) showing on page load by @Davidmattei in https://github.com/ems-project/elasticms/pull/934
* fix(core/api): json export remove proxy fields (__initializer__, ...) by @Davidmattei in https://github.com/ems-project/elasticms/pull/911
* fix(core/media-lib): preview modal not responsive to content by @Davidmattei in https://github.com/ems-project/elasticms/pull/925
* fix(core/media-lib): search bar query and ui by @Davidmattei in https://github.com/ems-project/elasticms/pull/926
* fix(core/mediaLib): wrong import inputOption by @Davidmattei in https://github.com/ems-project/elasticms/pull/922

## 5.17.1 (2024-06-11)
### Bug Fixes
* fix(core/mapping): fix json field type mapping by @theus77 in https://github.com/ems-project/elasticms/pull/899
* fix(core/media-lib): edit filename without extension by @Davidmattei in https://github.com/ems-project/elasticms/pull/904
* fix(core/twig): internal_links filter with null value by @Davidmattei in https://github.com/ems-project/elasticms/pull/909
* fix(core/twig): json menu nested component activate item by id by @Davidmattei in https://github.com/ems-project/elasticms/pull/906
* fix(demo): align with promo and last fixes by @Davidmattei in https://github.com/ems-project/elasticms/pull/898
### Chores
* chore(demo): revert back to ems_slug by @Davidmattei in https://github.com/ems-project/elasticms/pull/903

## 5.17.0 (2024-06-03)
### Features
* feat(core/revision): trash datatable by @Davidmattei in https://github.com/ems-project/elasticms/pull/888
* feat(demo): change requests by @Davidmattei in https://github.com/ems-project/elasticms/pull/893
* feat(ems): ip filtering with EMS_TRUSTED_IPS env variable by @Davidmattei in https://github.com/ems-project/elasticms/pull/883
### Bug Fixes
* fix(common/store_data): file system type not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/879
* fix(core/revision): json compare null as string if not defined by @theus77 in https://github.com/ems-project/elasticms/pull/878
* fix(core/revision): multiplex apply classes on children by @Davidmattei in https://github.com/ems-project/elasticms/pull/894
* fix(core/view): sorter view body empty by @Davidmattei in https://github.com/ems-project/elasticms/pull/886
* fix(submission/db): allow nullable expire_date by @Davidmattei in https://github.com/ems-project/elasticms/pull/880
### Chores
* chore(demo): remove publication route and upgrade note single colon controller by @Davidmattei in https://github.com/ems-project/elasticms/pull/887

## 5.16.1 (2024-06-24)
### Bug Fixes
* fix(common/storage): S3StreamPromise read by @theus77 in https://github.com/ems-project/elasticms/pull/924
* fix(common/store_data): file system type not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/879
* fix(core/media-lib): preview modal not responsive to content by @Davidmattei in https://github.com/ems-project/elasticms/pull/925
* fix(core/media-lib): search bar query and ui by @Davidmattei in https://github.com/ems-project/elasticms/pull/926
* fix(core/mediaLib): wrong import inputOption by @Davidmattei in https://github.com/ems-project/elasticms/pull/922
* fix(core/revision): json compare null as string if not defined by @theus77 in https://github.com/ems-project/elasticms/pull/878
* fix(submission/db): allow nullable expire_date by @Davidmattei in https://github.com/ems-project/elasticms/pull/880

## 5.16.0 (2024-04-29)
### Features
* feat(core/api): fileUrl for submission endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/876
* feat(ems/twig): added new ems_http twig function by @Davidmattei in https://github.com/ems-project/elasticms/pull/873
* feat(emsch/security): oAuth2 Open ID Connect authenticator (keycloak) by @Davidmattei in https://github.com/ems-project/elasticms/pull/874

## 5.15.3 (2024-06-24)
### Bug Fixes
* fix(core/media-lib): preview modal not responsive to content by @Davidmattei in https://github.com/ems-project/elasticms/pull/925
* fix(core/media-lib): search bar query and ui by @Davidmattei in https://github.com/ems-project/elasticms/pull/926
* fix(core/revision): json compare null as string if not defined by @theus77 in https://github.com/ems-project/elasticms/pull/878

## 5.15.2 (2024-04-12)
### Bug Fixes
* fix(core/api): getSubmissionFile correct headers and error handling by @Davidmattei in https://github.com/ems-project/elasticms/pull/871

## 5.15.1 (2024-04-10)
### Bug Fixes
* fix(core): reordered entity services by @theus77 in https://github.com/ems-project/elasticms/pull/865
* fix(core/environment): datatable only show managed environments by @Davidmattei in https://github.com/ems-project/elasticms/pull/869
* fix(core/environment): set order on create environment by @Davidmattei in https://github.com/ems-project/elasticms/pull/862
* fix(submission): change expire_date from date to date time by @Davidmattei in https://github.com/ems-project/elasticms/pull/867

## 5.15.0 (2024-04-02)
### Features
* feat(admin/media-lib): add preview file + ctrl click selection by @Davidmattei in https://github.com/ems-project/elasticms/pull/837
* feat(core/api): new submission file download endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/855
* feat(core/api): new submission view endpoint by @Davidmattei in https://github.com/ems-project/elasticms/pull/851
* feat(core/form): enable form routes with debug  by @Davidmattei in https://github.com/ems-project/elasticms/pull/848
* feat(core/search): add deprecrated search index view by @coppee in https://github.com/ems-project/elasticms/pull/859
* feat(core/user): custom reset email with i18n by @theus77 in https://github.com/ems-project/elasticms/pull/853
* feat(core/xliff): accept filename or hash in update command by @theus77 in https://github.com/ems-project/elasticms/pull/847
### Bug Fixes
* fix(core/revision): edit date range field in collection by @theus77 in https://github.com/ems-project/elasticms/pull/850
* fix(form/choice): choice labels not defined and no cache with debug controller by @Davidmattei in https://github.com/ems-project/elasticms/pull/861
### Documentation
* docs(demo): update from promo by @theus77 in https://github.com/ems-project/elasticms/pull/854
* docs: hashcash improved by @theus77 in https://github.com/ems-project/elasticms/pull/858
### Code Refactoring
* refactor(core): environment overview and wysiwyg  by @Davidmattei in https://github.com/ems-project/elasticms/pull/860
* refactor(core): migration environment table index by @OzkanO2 in https://github.com/ems-project/elasticms/pull/820
* refactor(core): migration wysiwyg profiles & stylesets by @OzkanO2 in https://github.com/ems-project/elasticms/pull/845

## 5.14.1 (2024-03-26)
### Bug Fixes
* fix(admin): publish all search results is not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/835
* fix(common/processor): image cast int for wartermark by @theus77 in https://github.com/ems-project/elasticms/pull/827
* fix(core/channel): entry path not defined should return null by @Davidmattei in https://github.com/ems-project/elasticms/pull/824
* fix(core/media-lib): unique file names by @Davidmattei in https://github.com/ems-project/elasticms/pull/821
* fix(core/release): revision ouuid does not exist anymore by @Davidmattei in https://github.com/ems-project/elasticms/pull/842
* fix(core/task): doctrine listener only on current revisions by @Davidmattei in https://github.com/ems-project/elasticms/pull/841
* fix(core/tasks): emsco:revision:task:create skip revisions with drafts by @Davidmattei in https://github.com/ems-project/elasticms/pull/817
* fix(core/tasks): on revision discard draft skip removing tasks by @Davidmattei in https://github.com/ems-project/elasticms/pull/816
* fix(core/twig): emsco_revisions_draft broken accessing revision as array by @Davidmattei in https://github.com/ems-project/elasticms/pull/818
* fix(core/xliff): reply-to option for emsco:xliff:extract command by @Davidmattei in https://github.com/ems-project/elasticms/pull/825

## 5.14.0 (2024-03-12)
### Features
* feat(client-helper/wysiwyg): update style sets during emsch:local:upload-assets by @theus77 in https://github.com/ems-project/elasticms/pull/799
* feat(common/elasticsearch): ems_analyze twig function by @theus77 in https://github.com/ems-project/elasticms/pull/807
* feat(core/media-lib): add search file functionality by @Davidmattei in https://github.com/ems-project/elasticms/pull/774
* feat(core/media-lib): delete multiple files by @Davidmattei in https://github.com/ems-project/elasticms/pull/756
* feat(core/media-lib): file rename/delete and folder rename by @Davidmattei in https://github.com/ems-project/elasticms/pull/627
* feat(core/media-lib): folder delete by @Davidmattei in https://github.com/ems-project/elasticms/pull/755
* feat(core/media-lib): move files by @Davidmattei in https://github.com/ems-project/elasticms/pull/771
* feat(core/release): add unpublish documents in release by @Davidmattei in https://github.com/ems-project/elasticms/pull/766
* feat(core/tasks): add task title suggestions by @Davidmattei in https://github.com/ems-project/elasticms/pull/797
* feat(core/tasks): notification mail command by @Davidmattei in https://github.com/ems-project/elasticms/pull/804
* feat(core/tasks): rework dashboard filters and workflow by @Davidmattei in https://github.com/ems-project/elasticms/pull/778
* feat(core/user): add expiration date property by @OzkanO2 in https://github.com/ems-project/elasticms/pull/798
* feat(demo): media file download with config by @Davidmattei in https://github.com/ems-project/elasticms/pull/719
* feat(elasticms): add s3 store data and storeDataSessionHandler by @theus77 in https://github.com/ems-project/elasticms/pull/785
### Bug Fixes
* fix(admin): still reference template @EMSCore by @Davidmattei in https://github.com/ems-project/elasticms/pull/769
* fix(core/field): jmn field environment is not required by @Davidmattei in https://github.com/ems-project/elasticms/pull/718
* fix(core/media-lib): fix folder rename not working by @Davidmattei in https://github.com/ems-project/elasticms/pull/812
* fix(core/media-lib): folder unique names by @Davidmattei in https://github.com/ems-project/elasticms/pull/810
* fix(core/tasks): remove tasks on delete revision by @Davidmattei in https://github.com/ems-project/elasticms/pull/815
* fix(core/tasks): task notification command start/end deadline by @Davidmattei in https://github.com/ems-project/elasticms/pull/814
* fix(core/user): throw expired exception inside providers by @Davidmattei in https://github.com/ems-project/elasticms/pull/813
* fix(core/view): log error in view report view templates by @Davidmattei in https://github.com/ems-project/elasticms/pull/739
* fix(helpers): HTML sanitizer by @coppee in https://github.com/ems-project/elasticms/pull/746
### Documentation
* docs(demo): update promo contents by @theus77 in https://github.com/ems-project/elasticms/pull/753
* docs(web): recipe to redirect a domain by @theus77 in https://github.com/ems-project/elasticms/pull/754
* docs: corresponding revision by @theus77 in https://github.com/ems-project/elasticms/pull/803
### Code Refactoring
* refactor(common/twig): ems_slug and flag ems_webalize as deprecated by @Davidmattei in https://github.com/ems-project/elasticms/pull/809
* refactor(core/media-lib): improve config injection by @Davidmattei in https://github.com/ems-project/elasticms/pull/808
### Tests
* test(helpers): support by AI by @coppee in https://github.com/ems-project/elasticms/pull/743

## 5.13.6 (2024-03-11)
### Bug Fixes
* fix(admin): hide sensitive info to non authenticated users by @theus77 in https://github.com/ems-project/elasticms/pull/757
* fix(api): indices by aliases only if exist by @theus77 in https://github.com/ems-project/elasticms/pull/783
* fix(common): ascii folding and webalize by @theus77 in https://github.com/ems-project/elasticms/pull/802
* fix(common): ascii folding use the UnicodeString->ascii method by @theus77 in https://github.com/ems-project/elasticms/pull/806
* fix(common/elastica): filter the indexes if a regex is defined by @theus77 in https://github.com/ems-project/elasticms/pull/781
* fix(core/ldap): ldap not configured should through error by @Davidmattei in https://github.com/ems-project/elasticms/pull/764
* fix(core/revision): linked revisions all default environments by @theus77 in https://github.com/ems-project/elasticms/pull/787
* fix(docker): docker was not starting any more by @theus77 in https://github.com/ems-project/elasticms/pull/761
* fix(emsch/api): refresh through core api by @Davidmattei in https://github.com/ems-project/elasticms/pull/805
* fix(make): demo's make under os x by @theus77 in https://github.com/ems-project/elasticms/pull/762

## 5.13.5 (2024-02-05)
### Bug Fixes
* fix(admin): db env is prod environment by @Davidmattei in https://github.com/ems-project/elasticms/pull/751
* fix(core/choice):  duplicate helptext in choice block by @theus77 in https://github.com/ems-project/elasticms/pull/745
* fix(core/choice): choice with linked collection by @theus77 in https://github.com/ems-project/elasticms/pull/742
* fix(core/ldap): config contains empty strings by @Davidmattei in https://github.com/ems-project/elasticms/pull/747
* fix(core/revision): delete command by query only for query and delete oldest by OUUID by @theus77 in https://github.com/ems-project/elasticms/pull/741
* fix(core/tika): better error management with file that are not supported by tika by @theus77 in https://github.com/ems-project/elasticms/pull/750

## 5.13.4 (2024-01-31)
### Bug Fixes
* fix(core/user): find users for role broken by @Davidmattei in https://github.com/ems-project/elasticms/pull/738

## 5.13.3 (2024-01-26)
### Features
* feat(admin): activate PdoSessionHandler (new db env) by @theus77 in https://github.com/ems-project/elasticms/pull/733

## 5.13.2 (2024-01-25)
### Bug Fixes
* fix(core/choice): multiple choice fields do not work by @Davidmattei in https://github.com/ems-project/elasticms/pull/731

## 5.13.1 (2024-01-19)
### Bug Fixes
* fix(core/search): legacy search not working on overviews by @Davidmattei in https://github.com/ems-project/elasticms/pull/729
* fix(core/twig): merge_recursive not working correctly by @Davidmattei in https://github.com/ems-project/elasticms/pull/728

## 5.13.0 (2024-01-03)
### Features
* feat(common/es): log error request/response  by @Davidmattei in https://github.com/ems-project/elasticms/pull/681
* feat(core/wysiwyg): default target _blank by @Davidmattei in https://github.com/ems-project/elasticms/pull/678
### Bug Fixes
* fix(clientHelper): http exception message and code can not be null by @theus77 in https://github.com/ems-project/elasticms/pull/686
* fix(core/doctrine): doctrine col type 'array' to 'json' by @Davidmattei in https://github.com/ems-project/elasticms/pull/696
### Documentation
* docs: migrate docs into monorepo (#671) by @Davidmattei in https://github.com/ems-project/elasticms/pull/672
### Code Refactoring
* refactor(admin): UrlEncodeEnvVarProcessor return type by @theus77 in https://github.com/ems-project/elasticms/pull/687
* refactor(core): getUsername -> getUserIdentifier deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/684
* refactor(form): missing types in form validators by @theus77 in https://github.com/ems-project/elasticms/pull/682
* refactor(submission): Using empty file as ZipArchive is deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/677
### Chores
* chore: improve makeFile setup by @Davidmattei in https://github.com/ems-project/elasticms/pull/697

## 5.12.3 (2023-12-22)
### Bug Fixes
* fix(core): deprecation migration event subscriber by @Davidmattei in https://github.com/ems-project/elasticms/pull/693
* fix(core/doctrine): invalid entities bigint, decimal returns string by @Davidmattei in https://github.com/ems-project/elasticms/pull/695

## 5.12.2 (2023-12-21)
### Bug Fixes
* fix(common): export/import documents by @theus77 in https://github.com/ems-project/elasticms/pull/675
* fix(core/choice-field): linked collection values are filtered out during reverse view transform by @theus77 in https://github.com/ems-project/elasticms/pull/674
* fix(core/extract): new tika content limit for admin by @Davidmattei in https://github.com/ems-project/elasticms/pull/692
* fix(core/jmn): jmn refresh after change by @Davidmattei in https://github.com/ems-project/elasticms/pull/679
* fix(demo): avoid to fetch media extracted data (_content, _author, ...) by @theus77 in https://github.com/ems-project/elasticms/pull/673
* fix(emsch/search): 'query_string' search escape query by @Davidmattei in https://github.com/ems-project/elasticms/pull/688
* fix(release): assets folders should excluded in packagist (to be tested) by @theus77 in https://github.com/ems-project/elasticms/pull/676

## 5.12.1 (2023-12-06)
### Bug Fixes
* fix(core/repository): fieldType repository is service entity by @Davidmattei in https://github.com/ems-project/elasticms/pull/670

## 5.12.0 (2023-12-05)
### Features
* feat(elasticsearc):  one host means SimpleConnectionPool strategy by @theus77 in https://github.com/ems-project/elasticms/pull/666
* feat(elk client): support advance host connection definition by @theus77 in https://github.com/ems-project/elasticms/pull/665
* feat(emsch): source in emsch_get by @theus77 in https://github.com/ems-project/elasticms/pull/663
### Bug Fixes
* fix(docker): minio setup by @Davidmattei in https://github.com/ems-project/elasticms/pull/668
* fix(emsch/search): query_search apply filters by @Davidmattei in https://github.com/ems-project/elasticms/pull/669
### Code Refactoring
* refactor(core): last getDoctrine in controller by @theus77 in https://github.com/ems-project/elasticms/pull/662
* refactor(core): remove deprecated Controller::getDoctrine  by @theus77 in https://github.com/ems-project/elasticms/pull/658
* refactor(deprecation): session service is deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/655
* refactor(user): getUsername() -> getUserIdentifier() by @theus77 in https://github.com/ems-project/elasticms/pull/656
* refactor: ROLE_PREVIOUS_ADMIN -> IS_IMPERSONATOR by @theus77 in https://github.com/ems-project/elasticms/pull/657
* refactor: _type is deprecated by @theus77 in https://github.com/ems-project/elasticms/pull/664
### Tests
* test(common): support by AI (2) by @coppee in https://github.com/ems-project/elasticms/pull/667

## 5.11.2 (2023-11-21)
### Bug Fixes
* fix(common): do not cache empty response by @theus77 in https://github.com/ems-project/elasticms/pull/654
* fix(emsch/search): add option fields_exclude by @Davidmattei in https://github.com/ems-project/elasticms/pull/659
* fix(emsch/security): lazy main firewall by @Davidmattei in https://github.com/ems-project/elasticms/pull/660

## 5.11.1 (2023-10-31)
### Bug Fixes
* fix(clientHelper): emsch_routing only prefix internal urls by @Davidmattei in https://github.com/ems-project/elasticms/pull/651
* fix(core/xliff): extract command email options optional by @theus77 in https://github.com/ems-project/elasticms/pull/652

## 5.11.0 (2023-10-30)
### Features
* feat(admin): colorized app icons by @theus77 in https://github.com/ems-project/elasticms/pull/634
* feat(admin/postprocessing): better post processing error message by @theus77 in https://github.com/ems-project/elasticms/pull/641
* feat(common/elasticSearch): skeleton working via the admin as proxy by @theus77 in https://github.com/ems-project/elasticms/pull/637
* feat(common/storage): api storage by @theus77 in https://github.com/ems-project/elasticms/pull/639
* feat(core/api): user profile return options by @Davidmattei in https://github.com/ems-project/elasticms/pull/633
* feat(demo): update demo by @theus77 in https://github.com/ems-project/elasticms/pull/638
* feat(emscli): add matches expr + keep attr style by @IsaMic in https://github.com/ems-project/elasticms/pull/628
* feat(emscli): audit file by @theus77 in https://github.com/ems-project/elasticms/pull/630
* feat(helper): smart crop + implementation by @theus77 in https://github.com/ems-project/elasticms/pull/648
* feat(http-submission): no redirect http handler by @theus77 in https://github.com/ems-project/elasticms/pull/629
* feat(submission): submission attachments route by @theus77 in https://github.com/ems-project/elasticms/pull/649
### Bug Fixes
* fix(demo): a 500 error is triggered in case of a 404 error by @theus77 in https://github.com/ems-project/elasticms/pull/646
### Tests
* test(common): ai generated tests by @coppee in https://github.com/ems-project/elasticms/pull/636
### Chores
* chore(scripts): init pg db without schema parameter  by @theus77 in https://github.com/ems-project/elasticms/pull/647

## 5.10.1 (2023-10-20)
### Features
* feat(admin): xliff export mail and currentRevisionOnly import by @theus77 in https://github.com/ems-project/elasticms/pull/643
* feat(admin/unpublish): db revisions not updated by @Davidmattei in https://github.com/ems-project/elasticms/pull/632
### Bug Fixes
* fix(admin/jmn): form locale and on jmn component by @Davidmattei in https://github.com/ems-project/elasticms/pull/642
* fix(admin/jmn): link fieldType needs environment by @Davidmattei in https://github.com/ems-project/elasticms/pull/640
* fix(admin/jmn): spaces in template by @Davidmattei in https://github.com/ems-project/elasticms/pull/645
* fix(admin/job): support ansi chars by @theus77 in https://github.com/ems-project/elasticms/pull/623
* fix(admin/mediaLib): template modal not found by @Davidmattei in https://github.com/ems-project/elasticms/pull/626
* fix(admin/revisions): draft counter and dataTable not the same by @Davidmattei in https://github.com/ems-project/elasticms/pull/620
* fix(admin/user): ignore empty string as valid Custom User Form by @theus77 in https://github.com/ems-project/elasticms/pull/644
* fix(cli): back to first character by @theus77 in https://github.com/ems-project/elasticms/pull/622

## 5.10.0 (2023-09-25)
### Features
* feat(cli): cache tika by @theus77 in https://github.com/ems-project/elasticms/pull/614
* feat(cli): load tika by @theus77 in https://github.com/ems-project/elasticms/pull/616
* feat(cli/media-sync): target folder command option by @theus77 in https://github.com/ems-project/elasticms/pull/593
* feat(common/ascii): includes utf8 punctuations in ascii folding by @theus77 in https://github.com/ems-project/elasticms/pull/615
* feat(core/api): fileInterface::uploadContents by @theus77 in https://github.com/ems-project/elasticms/pull/597
### Bug Fixes
* fix(core/criteria): correct response for criteria update by @Davidmattei in https://github.com/ems-project/elasticms/pull/618
* fix(core/datatable): revert horizontal scrollbar fix by @Davidmattei in https://github.com/ems-project/elasticms/pull/617

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

