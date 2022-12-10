#/bin/bash
./demo-preview.sh ems:admin:login

echo "Update admin configs Filters"
./demo-preview.sh ems:admin:get filter --export

echo "Update admin configs Analyzers"
./demo-preview.sh ems:admin:get analyzer --export

echo "Update admin configs Schedules"
./demo-preview.sh ems:admin:get schedule --export

echo "Update admin configs WYSIWYG Style Sets"
./demo-preview.sh ems:admin:get wysiwyg-style-set --export

echo "Update admin configs WYSIWYG Profiles"
./demo-preview.sh ems:admin:get wysiwyg-profile --export

echo "Update admin configs i18n"
./demo-preview.sh ems:admin:get i18n --export

echo "Update admin configs Environments"
./demo-preview.sh ems:admin:get environment --export

echo "Update admin configs ContentTypes"
./demo-preview.sh ems:admin:get content-type --export

echo "Update admin configs QuerySearches"
./demo-preview.sh ems:admin:get query-search --export

echo "Update admin configs Dashboards"
./demo-preview.sh ems:admin:get dashboard --export

echo "Update admin configs Channels"
./demo-preview.sh ems:admin:get channel --export

echo "Download documents"
./demo-preview.sh ems:document:download page
./demo-preview.sh ems:document:download publication
./demo-preview.sh ems:document:download slideshow
./demo-preview.sh ems:document:download structure
./demo-preview.sh ems:document:download form_instance
./demo-preview.sh ems:document:download asset

