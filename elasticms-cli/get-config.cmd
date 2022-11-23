#/bin/bash
echo "Update admin configs Filters"
call php bin\console ems:admin:get filter --export

echo "Update admin configs Analyzers"
call php bin\console ems:admin:get analyzer --export

echo "Update admin configs Schedules"
call php bin\console ems:admin:get schedule --export

echo "Update admin configs WYSIWYG Style Sets"
call php bin\console ems:admin:get wysiwyg-style-set --export

echo "Update admin configs WYSIWYG Profiles"
call php bin\console ems:admin:get wysiwyg-profile --export

echo "Update admin configs i18n"
call php bin\console ems:admin:get i18n --export

echo "Update admin configs Environments"
call php bin\console ems:admin:get environment --export

echo "Update admin configs ContentTypes"
call php bin\console ems:admin:get content-type --export

echo "Update admin configs QuerySearches"
call php bin\console ems:admin:get query-search --export

echo "Update admin configs Dashboards"
call php bin\console ems:admin:get dashboard --export

echo "Update admin configs Channels"
call php bin\console ems:admin:get channel --export
