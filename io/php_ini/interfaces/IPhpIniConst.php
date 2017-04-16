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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\php_ini\interfaces;

/**
 * php ini インターフェース
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IPhpIniConst {
	//==============================================
	//モジュール名
	//==============================================
	const CORE				= 'core';
	const DATE				= 'date';
	const EREG				= 'ereg';
	const LIBXML			= 'libxml';
	const OPENSSL			= 'openssl';
	const PCRE				= 'pcre';
	const SQLITE3			= 'sqlite3';
	const ZLIB				= 'zlib';
	const CTYPE 			= 'ctype';
	const DOM				= 'dom';
	const FILEINFO			= 'fileinfo';
	const FILTER			= 'filter';
	const GD				= 'gd';
	const GETTEXT			= 'gettext';
	const HASH				= 'hash';
	const ICONV 			= 'iconv';
	const JSON				= 'json';
	const MBSTRING			= 'mbstring';
	const MCRYPT			= 'mcrypt';
	const MYSQL 			= 'mysql';
	const SPL				= 'spl';
	const MYSQLI			= 'mysqli';
	const PDO				= 'pdo';
	const PDO_MYSQL 		= 'pdo_mysql';
	const PDO_SQLITE		= 'pdo_sqlite';
	const SESSION			= 'session';
	const POSIX 			= 'posix';
	const REFLECTION		= 'reflection';
	const STANDARD			= 'standard';
	const SIMPLEXML 		= 'simplexml';
	const PHAR				= 'phar';
	const TOKENIZER 		= 'tokenizer';
	const XML				= 'xml';
	const XMLREADER 		= 'xmlreader';
	const XMLWRITER 		= 'xmlwriter';
	const ZIP				= 'zip';
	const APACHE2HANDLER	= 'apache2handler';
	const XDEBUG			= 'xdebug';

	//==============================================
	//設定オプション名
	//==============================================
	const ALLOW_URL_FOPEN						= 'allow_url_fopen';
	const ALLOW_URL_INCLUDE						= 'allow_url_include';
	const ALWAYS_POPULATE_RAW_POST_DATA			= 'always_populate_raw_post_data';
	const ARG_SEPARATOR_INPUT 					= 'arg_separator.input';
	const ARG_SEPARATOR_OUTPUT					= 'arg_separator.output';
	const ASP_TAGS								= 'asp_tags';
	const ASSERT_ACTIVE							= 'assert.active';
	const ASSERT_BAIL 							= 'assert.bail';
	const ASSERT_CALLBACK 						= 'assert.callback';
	const ASSERT_QUIET_EVAL						= 'assert.quiet_eval';
	const ASSERT_WARNING						= 'assert.warning';
	const AUTO_APPEND_FILE						= 'auto_append_file';
	const AUTO_DETECT_LINE_ENDINGS				= 'auto_detect_line_endings';
	const AUTO_GLOBALS_JIT						= 'auto_globals_jit';
	const AUTO_PREPEND_FILE						= 'auto_prepend_file';
	const BROWSCAP								= 'browscap';
	const DATE_DEFAULT_LATITUDE					= 'date.default_latitude';
	const DATE_DEFAULT_LONGITUDE				= 'date.default_longitude';
	const DATE_SUNRISE_ZENITH 					= 'date.sunrise_zenith';
	const DATE_SUNSET_ZENITH					= 'date.sunset_zenith';
	const DATE_TIMEZONE							= 'date.timezone';
	const DEFAULT_CHARSET 						= 'default_charset';
	const DEFAULT_MIMETYPE						= 'default_mimetype';
	const DEFAULT_SOCKET_TIMEOUT				= 'default_socket_timeout';
	const DISABLE_CLASSES 						= 'disable_classes';
	const DISABLE_FUNCTIONS						= 'disable_functions';
	const DISPLAY_ERRORS						= 'display_errors';
	const DISPLAY_STARTUP_ERRORS				= 'display_startup_errors';
	const DOC_ROOT								= 'doc_root';
	const DOCREF_EXT							= 'docref_ext';
	const DOCREF_ROOT 							= 'docref_root';
	const ENABLE_DL								= 'enable_dl';
	const ENABLE_POST_DATA_READING				= 'enable_post_data_reading';
	const ENGINE								= 'engine';
	const ERROR_APPEND_STRING 					= 'error_append_string';
	const ERROR_LOG								= 'error_log';
	const ERROR_PREPEND_STRING					= 'error_prepend_string';
	const ERROR_REPORTING 						= 'error_reporting';
	const EXIT_ON_TIMEOUT 						= 'exit_on_timeout';
	const EXPOSE_PHP							= 'expose_php';
	const EXTENSION_DIR							= 'extension_dir';
	const FILE_UPLOADS							= 'file_uploads';
	const FILTER_DEFAULT						= 'filter.default';
	const FILTER_DEFAULT_FLAGS					= 'filter.default_flags';
	const FROM									= 'from';
	const GD_JPEG_IGNORE_WARNING				= 'gd.jpeg_ignore_warning';
	const HIGHLIGHT_COMMENT						= 'highlight.comment';
	const HIGHLIGHT_DEFAULT						= 'highlight.default';
	const HIGHLIGHT_HTML						= 'highlight.html';
	const HIGHLIGHT_KEYWORD						= 'highlight.keyword';
	const HIGHLIGHT_STRING						= 'highlight.string';
	const HTML_ERRORS 							= 'html_errors';
	const ICONV_INPUT_ENCODING					= 'iconv.input_encoding';
	const ICONV_INTERNAL_ENCODING 				= 'iconv.internal_encoding';
	const ICONV_OUTPUT_ENCODING					= 'iconv.output_encoding';
	const IGNORE_REPEATED_ERRORS				= 'ignore_repeated_errors';
	const IGNORE_REPEATED_SOURCE				= 'ignore_repeated_source';
	const IGNORE_USER_ABORT						= 'ignore_user_abort';
	const IMPLICIT_FLUSH						= 'implicit_flush';
	const INCLUDE_PATH							= 'include_path';
	const LAST_MODIFIED							= 'last_modified';
	const LOG_ERRORS							= 'log_errors';
	const LOG_ERRORS_MAX_LEN					= 'log_errors_max_len';
	const MAIL_ADD_X_HEADER						= 'mail.add_x_header';
	const MAIL_FORCE_EXTRA_PARAMETERS 			= 'mail.force_extra_parameters';
	const MAIL_LOG								= 'mail.log';
	const MAX_EXECUTION_TIME					= 'max_execution_time';
	const MAX_FILE_UPLOADS						= 'max_file_uploads';
	const MAX_INPUT_NESTING_LEVEL 				= 'max_input_nesting_level';
	const MAX_INPUT_TIME						= 'max_input_time';
	const MAX_INPUT_VARS						= 'max_input_vars';
	const MBSTRING_DETECT_ORDER					= 'mbstring.detect_order';
	const MBSTRING_ENCODING_TRANSLATION			= 'mbstring.encoding_translation';
	const MBSTRING_FUNC_OVERLOAD				= 'mbstring.func_overload';
	const MBSTRING_HTTP_INPUT 					= 'mbstring.http_input';
	const MBSTRING_HTTP_OUTPUT					= 'mbstring.http_output';
	const MBSTRING_HTTP_OUTPUT_CONV_MIMETYPES	= 'mbstring.http_output_conv_mimetypes';
	const MBSTRING_INTERNAL_ENCODING			= 'mbstring.internal_encoding';
	const MBSTRING_LANGUAGE						= 'mbstring.language';
	const MBSTRING_STRICT_DETECTION				= 'mbstring.strict_detection';
	const MBSTRING_SUBSTITUTE_CHARACTER			= 'mbstring.substitute_character';
	const MCRYPT_ALGORITHMS_DIR					= 'mcrypt.algorithms_dir';
	const MCRYPT_MODES_DIR						= 'mcrypt.modes_dir';
	const MEMORY_LIMIT							= 'memory_limit';
	const MYSQL_ALLOW_LOCAL_INFILE				= 'mysql.allow_local_infile';
	const MYSQL_ALLOW_PERSISTENT				= 'mysql.allow_persistent';
	const MYSQL_CONNECT_TIMEOUT					= 'mysql.connect_timeout';
	const MYSQL_DEFAULT_HOST					= 'mysql.default_host';
	const MYSQL_DEFAULT_PASSWORD				= 'mysql.default_password';
	const MYSQL_DEFAULT_PORT					= 'mysql.default_port';
	const MYSQL_DEFAULT_SOCKET					= 'mysql.default_socket';
	const MYSQL_DEFAULT_USER					= 'mysql.default_user';
	const MYSQL_MAX_LINKS 						= 'mysql.max_links';
	const MYSQL_MAX_PERSISTENT					= 'mysql.max_persistent';
	const MYSQL_TRACE_MODE						= 'mysql.trace_mode';
	const MYSQLI_ALLOW_LOCAL_INFILE				= 'mysqli.allow_local_infile';
	const MYSQLI_ALLOW_PERSISTENT 				= 'mysqli.allow_persistent';
	const MYSQLI_DEFAULT_HOST 					= 'mysqli.default_host';
	const MYSQLI_DEFAULT_PORT 					= 'mysqli.default_port';
	const MYSQLI_DEFAULT_PW						= 'mysqli.default_pw';
	const MYSQLI_DEFAULT_SOCKET					= 'mysqli.default_socket';
	const MYSQLI_DEFAULT_USER 					= 'mysqli.default_user';
	const MYSQLI_MAX_LINKS						= 'mysqli.max_links';
	const MYSQLI_MAX_PERSISTENT					= 'mysqli.max_persistent';
	const MYSQLI_RECONNECT						= 'mysqli.reconnect';
	const OPEN_BASEDIR							= 'open_basedir';
	const OUTPUT_BUFFERING						= 'output_buffering';
	const OUTPUT_HANDLER						= 'output_handler';
	const PCRE_BACKTRACK_LIMIT					= 'pcre.backtrack_limit';
	const PCRE_RECURSION_LIMIT					= 'pcre.recursion_limit';
	const PDO_MYSQL_DEFAULT_SOCKET				= 'pdo_mysql.default_socket';
	const PHAR_CACHE_LIST 						= 'phar.cache_list';
	const PHAR_READONLY							= 'phar.readonly';
	const PHAR_REQUIRE_HASH						= 'phar.require_hash';
	const POST_MAX_SIZE							= 'post_max_size';
	const PRECISION								= 'precision';
	const REALPATH_CACHE_SIZE 					= 'realpath_cache_size';
	const REALPATH_CACHE_TTL					= 'realpath_cache_ttl';
	const REGISTER_ARGC_ARGV					= 'register_argc_argv';
	const REPORT_MEMLEAKS 						= 'report_memleaks';
	const REPORT_ZEND_DEBUG						= 'report_zend_debug';
	const REQUEST_ORDER							= 'request_order';
	const SENDMAIL_FROM							= 'sendmail_from';
	const SENDMAIL_PATH							= 'sendmail_path';
	const SERIALIZE_PRECISION 					= 'serialize_precision';
	const SESSION_SAVE_PATH						= 'session.save_path';
	const SESSION_NAME							= 'session.name';
	const SESSION_SAVE_HANDLER					= 'session.save_handler';
	const SESSION_AUTO_START					= 'session.auto_start';
	const SESSION_GC_PROBABILITY				= 'session.gc_probability';
	const SESSION_GC_DIVISOR					= 'session.gc_divisor';
	const SESSION_GC_MAXLIFETIME				= 'session.gc_maxlifetime';
	const SESSION_SERIALIZE_HANDLER				= 'session.serialize_handler';
	const SESSION_COOKIE_LIFETIME				= 'session.cookie_lifetime';
	const SESSION_COOKIE_PATH					= 'session.cookie_path';
	const SESSION_COOKIE_DOMAIN					= 'session.cookie_domain';
	const SESSION_COOKIE_SECURE					= 'session.cookie_secure';
	const SESSION_COOKIE_HTTPONLY				= 'session.cookie_httponly';
	const SESSION_USE_COOKIES					= 'session.use_cookies';
	const SESSION_USE_ONLY_COOKIES				= 'session.use_only_cookies';
	const SESSION_REFERER_CHECK					= 'session.referer_check';
	// PHP 7.1で廃止 ここから
	const SESSION_ENTROPY_FILE					= 'session.entropy_file';				//PHP7.1で廃止
	const SESSION_ENTROPY_LENGTH				= 'session.entropy_length';				//PHP7.1で廃止
	const SESSION_HASH_FUNCTION					= 'session.hash_function';				//PHP7.1で廃止
	const SESSION_HASH_BITS_PER_CHARACTER		= 'session.hash_bits_per_character';	//PHP7.1で廃止
	// PHP 7.1で廃止 ここまで
	const SESSION_SID_LENGTH					= 'session.sid_length';
	const SESSION_SID_BITS_PER_CHARACTER		= 'session.sid_bits_per_character';
	const SESSION_CACHE_LIMITER					= 'session.cache_limiter';
	const SESSION_CACHE_EXPIRE					= 'session.cache_expire';
	const SESSION_USE_TRANS_SID					= 'session.use_trans_sid';
	const SESSION_BUG_COMPAT_42					= 'session.bug_compat_42';
	const SESSION_BUG_COMPAT_WARN				= 'session.bug_compat_warn';
	const SESSION_UPLOAD_PROGRESS_ENABLED		= 'session.upload_progress.enabled';
	const SESSION_UPLOAD_PROGRESS_CLEANUP		= 'session.upload_progress.cleanup';
	const SESSION_UPLOAD_PROGRESS_PREFIX		= 'session.upload_progress.prefix';
	const SESSION_UPLOAD_PROGRESS_NAME			= 'session.upload_progress.name';
	const SESSION_UPLOAD_PROGRESS_FREQ			= 'session.upload_progress.freq';
	const SESSION_UPLOAD_PROGRESS_MIN_FREQ		= 'session.upload_progress.min_freq';
	const SMTP									= 'SMTP';
	const SMTP_PORT								= 'smtp_port';
	const SQL_SAFE_MODE							= 'sql.safe_mode';
	const SQLITE3_EXTENSION_DIR					= 'sqlite3.extension_dir';
	const TRACK_ERRORS							= 'track_errors';
	const UNSERIALIZE_CALLBACK_FUNC				= 'unserialize_callback_func';
	const UPLOAD_MAX_FILESIZE 					= 'upload_max_filesize';
	const UPLOAD_TMP_DIR						= 'upload_tmp_dir';
	const URL_REWRITER_TAGS						= 'url_rewriter.tags';
	const USER_AGENT							= 'user_agent';
	const USER_DIR								= 'user_dir';
	const USER_INI_CACHE_TTL					= 'user_ini.cache_ttl';
	const USER_INI_FILENAME						= 'user_ini.filename';
	const VARIABLES_ORDER 						= 'variables_order';
	const XBITHACK								= 'xbithack';
	const XDEBUG_AUTO_TRACE						= 'xdebug.auto_trace';
	const XDEBUG_CLI_COLOR						= 'xdebug.cli_color';
	const XDEBUG_COLLECT_ASSIGNMENTS			= 'xdebug.collect_assignments';
	const XDEBUG_COLLECT_INCLUDES 				= 'xdebug.collect_includes';
	const XDEBUG_COLLECT_PARAMS					= 'xdebug.collect_params';
	const XDEBUG_COLLECT_RETURN					= 'xdebug.collect_return';
	const XDEBUG_COLLECT_VARS 					= 'xdebug.collect_vars';
	const XDEBUG_COVERAGE_ENABLE				= 'xdebug.coverage_enable';
	const XDEBUG_DEFAULT_ENABLE					= 'xdebug.default_enable';
	const XDEBUG_DUMP_COOKIE					= 'xdebug.dump.COOKIE';
	const XDEBUG_DUMP_ENV 						= 'xdebug.dump.ENV';
	const XDEBUG_DUMP_FILES						= 'xdebug.dump.FILES';
	const XDEBUG_DUMP_GET 						= 'xdebug.dump.GET';
	const XDEBUG_DUMP_POST						= 'xdebug.dump.POST';
	const XDEBUG_DUMP_REQUEST 					= 'xdebug.dump.REQUEST';
	const XDEBUG_DUMP_SERVER					= 'xdebug.dump.SERVER';
	const XDEBUG_DUMP_SESSION 					= 'xdebug.dump.SESSION';
	const XDEBUG_DUMP_GLOBALS 					= 'xdebug.dump_globals';
	const XDEBUG_DUMP_ONCE						= 'xdebug.dump_once';
	const XDEBUG_DUMP_UNDEFINED					= 'xdebug.dump_undefined';
	const XDEBUG_EXTENDED_INFO					= 'xdebug.extended_info';
	const XDEBUG_FILE_LINK_FORMAT 				= 'xdebug.file_link_format';
	const XDEBUG_IDEKEY							= 'xdebug.idekey';
	const XDEBUG_MANUAL_URL						= 'xdebug.manual_url';
	const XDEBUG_MAX_NESTING_LEVEL				= 'xdebug.max_nesting_level';
	const XDEBUG_OVERLOAD_VAR_DUMP				= 'xdebug.overload_var_dump';
	const XDEBUG_PROFILER_AGGREGATE				= 'xdebug.profiler_aggregate';
	const XDEBUG_PROFILER_APPEND				= 'xdebug.profiler_append';
	const XDEBUG_PROFILER_ENABLE				= 'xdebug.profiler_enable';
	const XDEBUG_PROFILER_ENABLE_TRIGGER		= 'xdebug.profiler_enable_trigger';
	const XDEBUG_PROFILER_OUTPUT_DIR			= 'xdebug.profiler_output_dir';
	const XDEBUG_PROFILER_OUTPUT_NAME 			= 'xdebug.profiler_output_name';
	const XDEBUG_REMOTE_AUTOSTART 				= 'xdebug.remote_autostart';
	const XDEBUG_REMOTE_CONNECT_BACK			= 'xdebug.remote_connect_back';
	const XDEBUG_REMOTE_COOKIE_EXPIRE_TIME		= 'xdebug.remote_cookie_expire_time';
	const XDEBUG_REMOTE_ENABLE					= 'xdebug.remote_enable';
	const XDEBUG_REMOTE_HANDLER					= 'xdebug.remote_handler';
	const XDEBUG_REMOTE_HOST					= 'xdebug.remote_host';
	const XDEBUG_REMOTE_LOG						= 'xdebug.remote_log';
	const XDEBUG_REMOTE_MODE					= 'xdebug.remote_mode';
	const XDEBUG_REMOTE_PORT					= 'xdebug.remote_port';
	const XDEBUG_SCREAM							= 'xdebug.scream';
	const XDEBUG_SHOW_EXCEPTION_TRACE 			= 'xdebug.show_exception_trace';
	const XDEBUG_SHOW_LOCAL_VARS				= 'xdebug.show_local_vars';
	const XDEBUG_SHOW_MEM_DELTA					= 'xdebug.show_mem_delta';
	const XDEBUG_TRACE_ENABLE_TRIGGER 			= 'xdebug.trace_enable_trigger';
	const XDEBUG_TRACE_FORMAT 					= 'xdebug.trace_format';
	const XDEBUG_TRACE_OPTIONS					= 'xdebug.trace_options';
	const XDEBUG_TRACE_OUTPUT_DIR 				= 'xdebug.trace_output_dir';
	const XDEBUG_TRACE_OUTPUT_NAME				= 'xdebug.trace_output_name';
	const XDEBUG_VAR_DISPLAY_MAX_CHILDREN 		= 'xdebug.var_display_max_children';
	const XDEBUG_VAR_DISPLAY_MAX_DATA 			= 'xdebug.var_display_max_data';
	const XDEBUG_VAR_DISPLAY_MAX_DEPTH			= 'xdebug.var_display_max_depth';
	const XMLRPC_ERROR_NUMBER 					= 'xmlrpc_error_number';
	const XMLRPC_ERRORS							= 'xmlrpc_errors';
	const ZEND_DETECT_UNICODE 					= 'zend.detect_unicode';
	const ZEND_ENABLE_GC						= 'zend.enable_gc';
	const ZEND_MULTIBYTE						= 'zend.multibyte';
	const ZEND_SCRIPT_ENCODING					= 'zend.script_encoding';
	const ZLIB_OUTPUT_COMPRESSION 				= 'zlib.output_compression';
	const ZLIB_OUTPUT_COMPRESSION_LEVEL			= 'zlib.output_compression_level';
	const ZLIB_OUTPUT_HANDLER 					= 'zlib.output_handler';
}
