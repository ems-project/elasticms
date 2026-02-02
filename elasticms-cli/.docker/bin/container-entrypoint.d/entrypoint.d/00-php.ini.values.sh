#!/usr/bin/env bash

if [[ "${PHP_BYPASS_INI_DEFAULT_VALUES}" == "true" ]]; then
	return 0
fi

# SQLSRV Driver

PHP_SQLSRV_CLIENTBUFFERMAXKBSIZE_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("sqlsrv.ClientBufferMaxKBSize");')"
PHP_SQLSRV_LOGSEVERITY_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("sqlsrv.LogSeverity");')"
PHP_SQLSRV_LOGSUBSYSTEMS_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("sqlsrv.LogSubsystems");')"
PHP_SQLSRV_SETLOCALEINFO_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("sqlsrv.SetLocaleInfo");')"
PHP_SQLSRV_WARNINGSRETURNASERRORS_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("sqlsrv.WarningsReturnAsErrors");')"

# SQLSRV PDO Driver

PHP_PDO_SQLSRV_CLIENT_BUFFER_MAX_KB_SIZE_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("pdo_sqlsrv.client_buffer_max_kb_size");')"
PHP_PDO_SQLSRV_LOG_SEVERITY_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("pdo_sqlsrv.log_severity");')"
PHP_PDO_SQLSRV_REPORT_ADDITIONAL_ERRORS_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("pdo_sqlsrv.report_additional_errors");')"
PHP_PDO_SQLSRV_SET_LOCALE_INFO_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("pdo_sqlsrv.set_locale_info");')"

# ODBC PDO Driver

PHP_PDO_ODBC_CONNECTION_POOLING_INI_DEFAULT_VALUE="$(php -r 'echo ini_get("pdo_odbc.connection_pooling");')"

true