<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper;

final class EmsFields
{
    public const string CONTENT_MIME_TYPE_FIELD = 'mimetype';
    public const string CONTENT_FILE_HASH_FIELD = 'sha1';
    public const string CONTENT_FILE_SIZE_FIELD = 'filesize';
    public const string CONTENT_FILE_NAME_FIELD = 'filename';
    public const string CONTENT_IMAGE_RESIZED_HASH_FIELD = '_image_resized_hash';
    public const string CONTENT_MIME_TYPE_FIELD_ = '_type';
    public const string CONTENT_FILE_HASH_FIELD_ = '_hash';
    public const string CONTENT_FILE_ALGO_FIELD_ = '_algo';
    public const string CONTENT_FILE_SIZE_FIELD_ = '_size';
    public const string CONTENT_FILE_NAME_FIELD_ = '_name';
    public const string CONTENT_FILE_CONTENT = '_content';
    public const string CONTENT_FILE_LANGUAGE = '_language';
    public const string CONTENT_FILE_DATE = '_date';
    public const string CONTENT_FILE_AUTHOR = '_author';
    public const string CONTENT_FILE_TITLE = '_title';
    public const string CONTENT_HASH_ALGO_FIELD = '_hash_algo';
    public const string CONTENT_PUBLISHED_DATETIME_FIELD = '_published_datetime';
    public const string CONTENT_FILES = '_files';

    public const string ASSET_CONFIG_DISPOSITION = '_disposition';
    public const string ASSET_CONFIG_BACKGROUND = '_background';
    public const string ASSET_CONFIG_TYPE = '_config_type';
    public const string ASSET_CONFIG_URL_TYPE = '_url_type';
    public const string ASSET_CONFIG_TYPE_IMAGE = 'image';
    public const string ASSET_CONFIG_TYPE_ZIP = 'zip';
    public const string ASSET_CONFIG_GRAVITY = '_gravity';
    public const string ASSET_CONFIG_MIME_TYPE = '_mime_type';
    public const string ASSET_CONFIG_FILE_NAMES = '_file_names';
    public const string ASSET_CONFIG_HEIGHT = '_height';
    public const string ASSET_CONFIG_QUALITY = '_quality';
    public const string ASSET_CONFIG_IMAGE_FORMAT = '_image_format';
    public const string ASSET_CONFIG_WEBP_IMAGE_FORMAT = 'webp';
    public const string ASSET_CONFIG_GIF_IMAGE_FORMAT = 'gif';
    public const string ASSET_CONFIG_BMP_IMAGE_FORMAT = 'bmp';
    public const string ASSET_CONFIG_JPEG_IMAGE_FORMAT = 'jpeg';
    public const string ASSET_CONFIG_PNG_IMAGE_FORMAT = 'png';
    public const string ASSET_CONFIG_RESIZE = '_resize';
    public const string ASSET_CONFIG_WIDTH = '_width';
    public const string ASSET_CONFIG_RADIUS = '_radius';
    public const string ASSET_CONFIG_RADIUS_GEOMETRY = '_radius_geometry';
    public const string ASSET_CONFIG_BORDER_COLOR = '_border_color';
    public const string ASSET_CONFIG_COLOR = '_color';
    public const string ASSET_CONFIG_WATERMARK_HASH = '_watermark_hash';
    public const string ASSET_CONFIG_GET_FILE_PATH = '_get_file_path';
    public const string ASSET_CONFIG_ROTATE = '_rotate';
    public const string ASSET_CONFIG_AUTO_ROTATE = '_auto_rotate';
    public const string ASSET_CONFIG_FLIP_HORIZONTAL = '_flip_horizontal';
    public const string ASSET_CONFIG_FLIP_VERTICAL = '_flip_vertical';
    public const string ASSET_CONFIG_USERNAME = '_username';
    public const string ASSET_CONFIG_PASSWORD = '_password';
    public const string ASSET_CONFIG_AFTER = '_after';
    public const string ASSET_CONFIG_BEFORE = '_before';
    public const string ASSET_SEED = '_seed';
    public const string ASSET_CONFIG_X = '_x';
    public const string ASSET_CONFIG_Y = '_y';

    public const string LOG_ALIAS = 'ems_internal_logger_alias';
    public const string LOG_TYPE = 'doc';
    public const string LOG_ENVIRONMENT_FIELD = 'environment';
    public const string LOG_CONTENTTYPE_FIELD = 'contenttype';
    public const string LOG_OPERATION_FIELD = 'operation';
    public const string LOG_USERNAME_FIELD = 'username';
    public const string LOG_IMPERSONATOR_FIELD = 'impersonator';
    public const string LOG_OUUID_FIELD = 'ouuid';
    public const string LOG_REVISION_ID_FIELD = 'revision_id';
    public const string LOG_KEY_FIELD = 'key';
    public const string LOG_VALUE_FIELD = 'value';
    public const string LOG_HOST_FIELD = 'host';
    public const string LOG_URL_FIELD = 'url';
    public const string LOG_ROUTE_FIELD = 'route';
    public const string LOG_STATUS_CODE_FIELD = 'status_code';
    public const string LOG_SIZE_FIELD = 'size';
    public const string LOG_MICROTIME_FIELD = 'microtime';
    public const string LOG_ERROR_MESSAGE_FIELD = 'error_message';
    public const string LOG_EXCEPTION_FIELD = 'exception';
    public const string LOG_SESSION_ID_FIELD = 'session_id';
    public const string LOG_INSTANCE_ID_FIELD = 'instance_id';
    public const string LOG_VERSION_FIELD = 'version';
    public const string LOG_COMPONENT_FIELD = 'component';
    public const string LOG_CONTEXT_FIELD = 'context';
    public const string LOG_LEVEL_FIELD = 'level';
    public const string LOG_MESSAGE_FIELD = 'message';
    public const string LOG_LEVEL_NAME_FIELD = 'level_name';
    public const string LOG_CHANNEL_FIELD = 'channel';
    public const string LOG_DATETIME_FIELD = 'datetime';
    public const string LOG_FIELD_IN_ERROR_FIELD = 'field';
    public const string LOG_PATH_IN_ERROR_FIELD = 'path';
    public const string LOG_EXIT_CODE = 'exit_code';
    public const string LOG_COMMAND_NAME = 'command_name';
    public const string LOG_COMMAND_LINE = 'command_line';

    public const string LOG_OPERATION_CREATE = 'CREATE';
    public const string LOG_OPERATION_UPDATE = 'UPDATE';
    public const string LOG_OPERATION_READ = 'READ';
    public const string LOG_OPERATION_DELETE = 'DELETE';
}
