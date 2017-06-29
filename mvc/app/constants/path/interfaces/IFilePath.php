<?php
/**  ______ _                 _               _ ___
 *  |  ____| |               | |             | |__ \
 *  | |__  | |_   ___      __| |__   ___  ___| |  ) |
 *  |  __| | | | | \ \ /\ / /| '_ \ / _ \/ _ \ | / /
 *  | |    | | |_| |\ V  V / | | | |  __/  __/ |/ /_
 *  |_|    |_|\__, | \_/\_/  |_| |_|\___|\___|_|____|
 *             __/ |
 *            |___/
 *
 * Flywheel2: the inertia php framework
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mvc\app\constants\path\interfaces;

/**
 * ファイルパスインターフェース
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IFilePath {
	//==============================================
	//パス設定：定数名の末尾に()をつける事で関数として呼び出せる。
	//==============================================
	const VAR_DIR								= '{:VAR_DIR}';
	const CACHE_DIR								= '{:CACHE_DIR}';
	const SESSION_DIR							= '{:SESSION_DIR}';
	const LOG_DIR								= '{:LOG_DIR}';
	const PHP_UPLOAD_TMP_DIR					= '{:PHP_UPLOAD_TMP_DIR}';

	//==============================================
	//FW2参考設定
	//==============================================
	//パス
	const FW2_DEFAULT_CONF_PATH					= '{:FW2_DEFAULTS_DIR}/config';

	//iniパス
	const FW2_DEFAULTS_RESOURCE_INI_PATH		= '{:FW2_DEFAULTS_DIR}/config/php_ini_path/resource.ini';
	const FW2_DEFAULTS_ERROR_INI_PATH			= '{:FW2_DEFAULTS_DIR}/config/php_ini_path/error.ini';
	const FW2_DEFAULTS_SESSION_INI_PATH			= '{:FW2_DEFAULTS_DIR}/config/php_ini_path/session.ini';
	const FW2_DEFAULTS_FILE_UPLOAD_INI_PATH		= '{:FW2_DEFAULTS_DIR}/config/php_ini_path/file_upload.ini';

	//ini
	const FW2_DEFAULTS_RESOURCE_PHP_INI_PATH	= '{:FW2_DEFAULTS_DIR}/config/php_ini/resource_limits.php.ini';
	const FW2_DEFAULTS_ERROR_PHP_INI_PATH		= '{:FW2_DEFAULTS_DIR}/config/php_ini/error.php.ini';
	const FW2_DEFAULTS_SESSION_PHP_INI_PATH		= '{:FW2_DEFAULTS_DIR}/config/php_ini/not_secure_session.php.ini';
	const FW2_DEFAULTS_FILE_UPLOAD_PHP_INI_PATH	= '{:FW2_DEFAULTS_DIR}/config/php_ini/file_upload.php.ini';

	//==============================================
	//
	//==============================================
	const PACKAGE_CONF_PATH						= '{:CONF_DIR}/{:PACKAGE_NS_PATH}';

	const INI_CACHE_DIR							= '{:CACHE_DIR}/{:APP_NS_PATH}/ini';

	const VENDOR_DIR							= '{:VENDOR_DIR}';

	const SYSTEM_ROOT_DIR						= '{:SYSTEM_ROOT_DIR}';

	const SRC_DIR								= '{:SRC_DIR}';

	const APP_LOG_DIR							= '{:LOG_DIR}/{:APP_NS_PATH}';
	const APP_TIMER_LOG_PATH					= '{:LOG_DIR}/{:APP_NS_PATH}/{:APP_NAME}_timer.log';
	const SQL_TIMER_LOG_PATH					= '{:LOG_DIR}/{:APP_NS_PATH}/{:APP_NAME}_sql_timer.log';
	const SQL_ERROR_LOG_PATH					= '{:LOG_DIR}/{:APP_NS_PATH}/{:APP_NAME}_sql_error.log';
	const PHP_ERROR_LOG_PATH					= '{:LOG_DIR}/{:APP_NS_PATH}/{:APP_NAME}_php_error.log';

	const SESSION_INI_PATH						= '{:CONF_DIR}/{:PACKAGE_NS_PATH}/{:CALL_TYPE}/{:APP_NAME}/session/session.ini';
	const SESSION_PHP_INI_PATH					= '{:CONF_DIR}/{:PACKAGE_NS_PATH}/{:CALL_TYPE}/{:APP_NAME}/php_ini/session.php.ini';
	const PHP_SESSION_PATH						= '{:SESSION_DIR}/{:APP_NS_PATH}/{:CALL_TYPE}/{:APP_NAME}';
	const COOKIE_AUTH_SESSION_PATH				= '{:AUTH_DIR}/cookie_auth/{:APP_NS_PATH}/';

	const PHP_BIN_PATH							= \PHP_BINARY;

	const TWIG_INI_PATH							= '{:CONF_DIR}/{:PACKAGE_NS_PATH}/commons/twig/twig.ini';
	const TWIG_CACHE_DIR						= '{:CACHE_DIR}/{:APP_NS_PATH}/';

	const COMS_TWIG_EXTENSION_FUNCTION			= '\ickx\fw2\extensions\twig\Twig_Extension_Function';
	const COMS_TWIG_EXTENSION_FILTER			= '\ickx\fw2\extensions\twig\Twig_Extension_Filter';

	const APP_TWIG_EXTENSION_FUNCTION			= '\{:VENDOR_NAME}\{:PACKAGE_NAME}\{:CALL_TYPE}\{:APP_NAME}\app\extensions\twig\Twig_Extension_Function';
	const APP_TWIG_EXTENSION_FILTER				= '\{:VENDOR_NAME}\{:PACKAGE_NAME}\{:CALL_TYPE}\{:APP_NAME}\app\extensions\twig\Twig_Extension_Filter';

	const FW2_CONTROLLER_PATH					= '{:VENDOR_DIR}/ickx/fw2/mvc/app/controllers/';

	const FW2_EXT_TEMPLATES_PATH				= '{:VENDOR_DIR}/ickx/fw2/extensions/twig/templates/';
	const COMS_TEMPLATES_PATH					= '{:PACKAGE_DIR}/commons/app/templates/';

	const APP_PATH								= '{:APP_DIR}/app/';
	const TEMPLATES_PATH						= '{:APP_DIR}/app/templates/';
	const CONTROLLER_PATH						= '{:APP_DIR}/app/controllers/';

	const PHP_INI_PATH_RESOURCE_LIMITS			= '{:SYSTEM_ROOT_DIR}/configs/{:VENDOR_NAME}/{:PACKAGE_NAME}/{:CALL_TYPE}/{:APP_NAME}/php_ini/resource_limits.php.ini';

	const DSN_DIR								= '{:SYSTEM_ROOT_DIR}/configs/{:VENDOR_NAME}/{:PACKAGE_NAME}/{:CALL_TYPE}/{:APP_NAME}/dsn/';
}
