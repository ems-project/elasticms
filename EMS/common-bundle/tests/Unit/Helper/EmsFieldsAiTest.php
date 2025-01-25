<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmsFieldsAiTest extends TestCase
{
    #[DataProvider('constantsProvider')]
    public function testConstants($constantName, $expectedValue): void
    {
        $this->assertEquals($expectedValue, \constant("EMS\CommonBundle\Helper\EmsFields::$constantName"));
    }

    public static function constantsProvider(): array
    {
        return [
            ['CONTENT_MIME_TYPE_FIELD', 'mimetype'],
            ['CONTENT_FILE_HASH_FIELD', 'sha1'],
            ['CONTENT_FILE_SIZE_FIELD', 'filesize'],
            ['CONTENT_FILE_NAME_FIELD', 'filename'],
            ['CONTENT_MIME_TYPE_FIELD_', '_type'],
            ['CONTENT_FILE_HASH_FIELD_', '_hash'],
            ['CONTENT_FILE_ALGO_FIELD_', '_algo'],
            ['CONTENT_FILE_SIZE_FIELD_', '_size'],
            ['CONTENT_FILE_NAME_FIELD_', '_name'],
            ['CONTENT_FILE_ALGO_FIELD_', '_algo'],
            ['CONTENT_FILE_CONTENT', '_content'],
            ['CONTENT_FILE_LANGUAGE', '_language'],
            ['CONTENT_FILE_DATE', '_date'],
            ['CONTENT_FILE_AUTHOR', '_author'],
            ['CONTENT_FILE_TITLE', '_title'],
            ['CONTENT_HASH_ALGO_FIELD', '_hash_algo'],
            ['CONTENT_PUBLISHED_DATETIME_FIELD', '_published_datetime'],
            ['CONTENT_FILES', '_files'],
            ['ASSET_CONFIG_DISPOSITION', '_disposition'],
            ['ASSET_CONFIG_BACKGROUND', '_background'],
            ['ASSET_CONFIG_TYPE', '_config_type'],
            ['ASSET_CONFIG_URL_TYPE', '_url_type'],
            ['ASSET_CONFIG_TYPE_IMAGE', 'image'],
            ['ASSET_CONFIG_TYPE_ZIP', 'zip'],
            ['ASSET_CONFIG_GRAVITY', '_gravity'],
            ['ASSET_CONFIG_MIME_TYPE', '_mime_type'],
            ['ASSET_CONFIG_FILE_NAMES', '_file_names'],
            ['ASSET_CONFIG_HEIGHT', '_height'],
            ['ASSET_CONFIG_QUALITY', '_quality'],
            ['ASSET_CONFIG_IMAGE_FORMAT', '_image_format'],
            ['ASSET_CONFIG_WEBP_IMAGE_FORMAT', 'webp'],
            ['ASSET_CONFIG_GIF_IMAGE_FORMAT', 'gif'],
            ['ASSET_CONFIG_BMP_IMAGE_FORMAT', 'bmp'],
            ['ASSET_CONFIG_JPEG_IMAGE_FORMAT', 'jpeg'],
            ['ASSET_CONFIG_PNG_IMAGE_FORMAT', 'png'],
            ['ASSET_CONFIG_RESIZE', '_resize'],
            ['ASSET_CONFIG_WIDTH', '_width'],
            ['ASSET_CONFIG_RADIUS', '_radius'],
            ['ASSET_CONFIG_RADIUS_GEOMETRY', '_radius_geometry'],
            ['ASSET_CONFIG_BORDER_COLOR', '_border_color'],
            ['ASSET_CONFIG_COLOR', '_color'],
            ['ASSET_CONFIG_WATERMARK_HASH', '_watermark_hash'],
            ['ASSET_CONFIG_GET_FILE_PATH', '_get_file_path'],
            ['ASSET_CONFIG_ROTATE', '_rotate'],
            ['ASSET_CONFIG_AUTO_ROTATE', '_auto_rotate'],
            ['ASSET_CONFIG_FLIP_HORIZONTAL', '_flip_horizontal'],
            ['ASSET_CONFIG_FLIP_VERTICAL', '_flip_vertical'],
            ['ASSET_CONFIG_USERNAME', '_username'],
            ['ASSET_CONFIG_PASSWORD', '_password'],
            ['ASSET_CONFIG_AFTER', '_after'],
            ['ASSET_CONFIG_BEFORE', '_before'],
            ['ASSET_SEED', '_seed'],
            ['LOG_ALIAS', 'ems_internal_logger_alias'],
            ['LOG_TYPE', 'doc'],
            ['LOG_ENVIRONMENT_FIELD', 'environment'],
            ['LOG_CONTENTTYPE_FIELD', 'contenttype'],
            ['LOG_OPERATION_FIELD', 'operation'],
            ['LOG_USERNAME_FIELD', 'username'],
            ['LOG_IMPERSONATOR_FIELD', 'impersonator'],
            ['LOG_OUUID_FIELD', 'ouuid'],
            ['LOG_REVISION_ID_FIELD', 'revision_id'],
            ['LOG_KEY_FIELD', 'key'],
            ['LOG_VALUE_FIELD', 'value'],
            ['LOG_HOST_FIELD', 'host'],
            ['LOG_URL_FIELD', 'url'],
            ['LOG_ROUTE_FIELD', 'route'],
            ['LOG_STATUS_CODE_FIELD', 'status_code'],
            ['LOG_SIZE_FIELD', 'size'],
            ['LOG_MICROTIME_FIELD', 'microtime'],
            ['LOG_ERROR_MESSAGE_FIELD', 'error_message'],
            ['LOG_EXCEPTION_FIELD', 'exception'],
            ['LOG_SESSION_ID_FIELD', 'session_id'],
            ['LOG_INSTANCE_ID_FIELD', 'instance_id'],
            ['LOG_VERSION_FIELD', 'version'],
            ['LOG_COMPONENT_FIELD', 'component'],
            ['LOG_CONTEXT_FIELD', 'context'],
            ['LOG_LEVEL_FIELD', 'level'],
            ['LOG_MESSAGE_FIELD', 'message'],
            ['LOG_LEVEL_NAME_FIELD', 'level_name'],
            ['LOG_CHANNEL_FIELD', 'channel'],
            ['LOG_DATETIME_FIELD', 'datetime'],
            ['LOG_FIELD_IN_ERROR_FIELD', 'field'],
            ['LOG_PATH_IN_ERROR_FIELD', 'path'],
            ['LOG_EXIT_CODE', 'exit_code'],
            ['LOG_COMMAND_NAME', 'command_name'],
            ['LOG_COMMAND_LINE', 'command_line'],
            ['LOG_OPERATION_CREATE', 'CREATE'],
            ['LOG_OPERATION_UPDATE', 'UPDATE'],
            ['LOG_OPERATION_READ', 'READ'],
            ['LOG_OPERATION_DELETE', 'DELETE'],
        ];
    }
}
