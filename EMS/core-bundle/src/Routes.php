<?php

declare(strict_types=1);

namespace EMS\CoreBundle;

class Routes
{
    final public const ADMIN_CONTENT_TYPE_ACTION_ADD = 'emsco_admin_content_type_action_add';
    final public const ADMIN_CONTENT_TYPE_ACTION_DELETE = 'emsco_admin_content_type_action_delete';
    final public const ADMIN_CONTENT_TYPE_ACTION_EDIT = 'emsco_admin_content_type_action_edit';
    final public const ADMIN_CONTENT_TYPE_ACTION_INDEX = 'emsco_admin_content_type_action_index';
    final public const ADMIN_CONTENT_TYPE_ACTIVATE = 'emsco_admin_content_type_activate';
    final public const ADMIN_CONTENT_TYPE_ADD = 'emsco_admin_content_type_add';
    final public const ADMIN_CONTENT_TYPE_ADD_REFERENCED = 'emsco_admin_content_type_add_referenced';
    final public const ADMIN_CONTENT_TYPE_ADD_REFERENCED_INDEX = 'emsco_admin_content_type_add_referenced_index';
    final public const ADMIN_CONTENT_TYPE_DEACTIVATE = 'emsco_admin_content_type_deactivate';
    final public const ADMIN_CONTENT_TYPE_EDIT = 'emsco_admin_content_type_edit';
    final public const ADMIN_CONTENT_TYPE_EDIT_FIELD_EDIT = 'emsco_admin_content_type_edit_field';
    final public const ADMIN_CONTENT_TYPE_EXPORT = 'emsco_admin_content_type_export';
    final public const ADMIN_CONTENT_TYPE_INDEX = 'emsco_admin_content_type_index';
    final public const ADMIN_CONTENT_TYPE_REFRESH_MAPPING = 'emsco_admin_content_type_refresh_mapping';
    final public const ADMIN_CONTENT_TYPE_REMOVE = 'emsco_admin_content_type_remove';
    final public const ADMIN_CONTENT_TYPE_REORDER = 'emsco_admin_content_type_reorder';
    final public const ADMIN_CONTENT_TYPE_STRUCTURE = 'emsco_admin_content_type_structure';
    final public const ADMIN_CONTENT_TYPE_UPDATE_FROM_JSON = 'emsco_admin_content_type_update_from_json';
    final public const ADMIN_CONTENT_TYPE_VIEW_ADD = 'emsco_admin_content_type_view_add';
    final public const ADMIN_CONTENT_TYPE_VIEW_DEFINE = 'emsco_admin_content_type_view_define';
    final public const ADMIN_CONTENT_TYPE_VIEW_DELETE = 'emsco_admin_content_type_view_delete';
    final public const ADMIN_CONTENT_TYPE_VIEW_DUPLICATE = 'emsco_admin_content_type_view_duplicate';
    final public const ADMIN_CONTENT_TYPE_VIEW_EDIT = 'emsco_admin_content_type_view_edit';
    final public const ADMIN_CONTENT_TYPE_VIEW_INDEX = 'emsco_admin_content_type_view_index';
    final public const ADMIN_CONTENT_TYPE_VIEW_UNDEFINE = 'emsco_admin_content_type_view_undefine';
    final public const ADMIN_ENVIRONMENT_ADD = 'emsco_admin_environment_add';
    final public const ADMIN_ENVIRONMENT_ALIAS_ATTACH = 'emsco_admin_environment_alias_attach';
    final public const ADMIN_ENVIRONMENT_ALIAS_REMOVE = 'emsco_admin_environment_alias_remove';
    final public const ADMIN_ENVIRONMENT_EDIT = 'emsco_admin_environment_edit';
    final public const ADMIN_ENVIRONMENT_INDEX = 'emsco_admin_environment_index';
    final public const ADMIN_ENVIRONMENT_REBUILD = 'emsco_admin_environment_rebuild';
    final public const ADMIN_ENVIRONMENT_REMOVE = 'emsco_admin_environment_remove';
    final public const ADMIN_ENVIRONMENT_VIEW = 'emsco_admin_environment_view';
    final public const ADMIN_ELASTIC_ORPHAN = 'emsco_admin_elastic_orphan';
    final public const ADMIN_ELASTIC_ORPHAN_DELETE = 'emsco_admin_elastic_orphan_delete';

    final public const AUTH_TOKEN_LOGIN = 'emsco_auth_token_login';
    final public const EDIT_REVISION = 'emsco_edit_revision';
    final public const VIEW_REVISIONS = 'emsco_view_revisions';
    final public const VIEW_REVISIONS_AUDIT = 'emsco_view_revisions_table_audit';
    final public const DISCARD_DRAFT = 'emsco_discard_draft';
    final public const DRAFT_IN_PROGRESS = 'emsco_draft_in_progress';
    final public const DATA_TABLE_AJAX_TABLE = 'emsco_datatable_ajax_table';
    final public const DATA_TABLE_AJAX_TABLE_EXPORT = 'emsco_datatable_ajax_table_export';
    final public const DASHBOARD_ADMIN_INDEX = 'emsco_dashboard_admin_index';
    final public const DASHBOARD_ADMIN_ADD = 'emsco_dashboard_admin_add';
    final public const DASHBOARD_ADMIN_EDIT = 'emsco_dashboard_admin_edit';
    final public const DASHBOARD_ADMIN_DELETE = 'emsco_dashboard_admin_delete';
    final public const DASHBOARD_ADMIN_DEFINE = 'emsco_dashboard_admin_define';
    final public const DASHBOARD_ADMIN_UNDEFINE = 'emsco_dashboard_admin_undefine';
    final public const DASHBOARD = 'emsco_dashboard';
    final public const DASHBOARD_HOME = 'emsco_dashboard_home';
    final public const ANALYZER_INDEX = 'emsco_analyzer_index';
    final public const ANALYZER_EDIT = 'emsco_analyzer_edit';
    final public const ANALYZER_DELETE = 'emsco_analyzer_delete';
    final public const ANALYZER_ADD = 'emsco_analyzer_add';
    final public const ANALYZER_EXPORT = 'emsco_analyzer_export';
    final public const FILTER_INDEX = 'emsco_filter_index';
    final public const FILTER_EDIT = 'emsco_filter_edit';
    final public const FILTER_DELETE = 'emsco_filter_delete';
    final public const FILTER_ADD = 'emsco_filter_add';
    final public const FILTER_EXPORT = 'emsco_filter_export';
    final public const FORM_ADMIN_INDEX = 'emsco_form_admin_index';
    final public const FORM_ADMIN_ADD = 'emsco_form_admin_add';
    final public const FORM_ADMIN_EDIT = 'emsco_form_admin_edit';
    final public const FORM_ADMIN_REORDER = 'emsco_form_admin_reorder';
    final public const FORM_ADMIN_DELETE = 'emsco_form_admin_delete';
    final public const I18N_INDEX = 'emsco_i18n_index';
    final public const I18N_ADD = 'emsco_i18n_add';
    final public const I18N_EDIT = 'emsco_i18n_edit';
    final public const I18N_DELETE = 'emsco_i18n_delete';
    final public const RELEASE_INDEX = 'emsco_release_index';
    final public const RELEASE_VIEW = 'emsco_release_view';
    final public const RELEASE_ADD = 'emsco_release_add';
    final public const RELEASE_EDIT = 'emsco_release_edit';
    final public const RELEASE_PUBLISH = 'emsco_release_publish';
    final public const RELEASE_DELETE = 'emsco_release_delete';
    final public const RELEASE_SET_STATUS = 'emsco_release_set_status';
    final public const RELEASE_ADD_REVISION = 'emsco_release_add_revision';
    final public const RELEASE_ADD_REVISIONS = 'emsco_release_add_revisions';
    final public const RELEASE_NON_MEMBER_REVISION_AJAX = 'emsco_release_ajax_data_table_non_member_revision';
    final public const DATA_DEFAULT_VIEW = 'emsco_data_default_view';
    final public const DATA_LINK = 'emsco_data_link';
    final public const DATA_IN_MY_CIRCLE_VIEW = 'emsco_data_in_my_circle_view';
    final public const DATA_PUBLIC_VIEW = 'emsco_data_public_view';
    final public const DATA_PRIVATE_VIEW = 'emsco_data_private_view';
    final public const DATA_ADD = 'emsco_data_add';
    final public const DATA_TRASH = 'emsco_data_trash';
    final public const DATA_TRASH_PUT_BACK = 'emsco_data_put_back';
    final public const DATA_TRASH_EMPTY = 'emsco_data_empty_trash';
    final public const DATA_ADD_REVISION_TO_RELEASE = 'emsco_data_add_revision_to_release';
    final public const SCHEDULE_INDEX = 'emsco_schedule_index';
    final public const SCHEDULE_ADD = 'emsco_schedule_add';
    final public const SCHEDULE_EDIT = 'emsco_schedule_edit';
    final public const SCHEDULE_DUPLICATE = 'emsco_schedule_duplicate';
    final public const SCHEDULE_DELETE = 'emsco_schedule_delete';
    final public const UPLOAD_ASSET_PUBLISHER_OVERVIEW = 'emsco_uploaded_asset_publisher_overview';
    final public const UPLOAD_ASSET_PUBLISHER_HIDE = 'emsco_uploaded_asset_publisher_hide';
    final public const UPLOAD_ASSET_ADMIN_OVERVIEW = 'emsco_uploaded_asset_admin_overview';
    final public const UPLOAD_ASSET_ADMIN_TOGGLE_VISIBILITY = 'emsco_uploaded_asset_admin_toggle_visibility';
    final public const UPLOAD_ASSET_ADMIN_DELETE = 'emsco_uploaded_asset_admin_delete';
    final public const USER_INDEX = 'emsco_user_index';
    final public const USER_ADD = 'emsco_user_add';
    final public const USER_EDIT = 'emsco_user_edit';
    final public const USER_ENABLING = 'emsco_user_enabling';
    final public const USER_API_KEY = 'emsco_user_api_key';
    final public const USER_DELETE = 'emsco_user_delete';
    final public const USER_PROFILE = 'emsco_user_profile';
    final public const USER_PROFILE_EDIT = 'emsco_user_profile_edit';
    final public const USER_CHANGE_PASSWORD = 'emsco_user_change_password';
    final public const USER_LOGOUT = 'emsco_user_logout';
    final public const USER_LOGIN = 'emsco_user_login';
    final public const LOG_INDEX = 'emsco_log_index';
    final public const LOG_DELETE = 'emsco_log_delete';
    final public const LOG_VIEW = 'emsco_log_view';
    final public const WYSIWYG_INDEX = 'emsco_wysiwyg_index';
    final public const WYSIWYG_PROFILE_ADD = 'emsco_wysiwyg_profile_add';
    final public const WYSIWYG_PROFILE_DELETE = 'emsco_wysiwyg_profile_delete';
    final public const WYSIWYG_PROFILE_EDIT = 'emsco_wysiwyg_profile_edit';
    final public const WYSIWYG_STYLE_SET_ADD = 'emsco_wysiwyg_style_set_new';
    final public const WYSIWYG_STYLE_SET_EDIT = 'emsco_wysiwyg_style_set_edit';
    final public const WYSIWYG_STYLE_SET_DELETE = 'emsco_wysiwyg_style_set_delete';
}
