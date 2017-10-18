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

namespace ickx\fw2\mvc;

use ickx\fw2\core\environment\Environment;
use ickx\fw2\core\cli\Cli;
use ickx\fw2\io\cache\Cache;
use ickx\fw2\mvc\app\middleware\exception\ExceptionMiddleware;
use ickx\fw2\mvc\app\middleware\dispatch\DispatchMiddleware;

/**
 * Flywheel Freamwork spin-up class
 *
 * \(-_-)/ /(_-_)\ \(-_-)/ /(_-_)\ GronGron!!
 */
class Flywheel {
	use \ickx\fw2\traits\data_store\ClassVariableTrait;

	/**
	 * @var	string	routing uriを保持するパラメータ名
	 */
	public const URL_PARAM_NAME	= 'routing_url';

	/**
	 * @var	int	レポーティングレベル：とりうるすべてのレポートの取得
	 */
	public const REPORTING_LEVEL_ALL		= 32767;

	/**
	 * @var	int	レポーティングレベル：プロファイルレポートを取得する
	 */
	public const REPORTING_LEVEL_PROFILE	= 8;

	/**
	 * @var	int	レポーティングレベル：デバッグレポートを取得する
	 */
	public const REPORTING_LEVEL_DEBUG		= 4;

	/**
	 * @var	int	レポーティングレベル：インフォレポートを取得する
	 */
	public const REPORTING_LEVEL_INFO		= 2;

	/**
	 * @var	int	レポーティングレベル：本番用レポートを取得する
	 */
	public const REPORTING_LEVEL_PROD		= 1;

	/**
	 * @var	int	現在のレポーティングレベル
	 * @static
	 */
	public static $reportingLevel			= self::REPORTING_LEVEL_PROD | self::REPORTING_LEVEL_INFO;

	/**
	 * defaultのミドルウェア設定
	 *
	 * @return string[]
	 */
	public static function getMiddlewareList () {
		return [
			ExceptionMiddleware::class,
			DispatchMiddleware::class,
		];
	}

	/**
	 * phase0:Built-in server support
	 */
	public static function SupportBuiltInServer () {
		$backtrace = debug_backtrace();
		$htdocs = dirname(str_replace(\DIRECTORY_SEPARATOR, '/', array_pop($backtrace)['file']));
		$_SERVER['DOCUMENT_ROOT'] = $htdocs;

		$static_path = sprintf('%s/%s', $htdocs, ltrim($_SERVER['SCRIPT_NAME'], '/'));

		clearstatcache($static_path, true);
		if (substr($_SERVER['SCRIPT_NAME'], -1. -3) !== '.php' && file_exists($static_path)) {
			return false;
		}

		return true;
	}

	/**
	 * phase1:Checking
	 */
	public static function Checking () {
		ClassLoader::Connect('TimeProfiler',	'ickx\fw2\basic\log\TimeProfiler');
		ClassLoader::Connect('Flywheel',		static::class);

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		ClassLoader::Connect('DI',				'ickx\fw2\container\DI');
		ClassLoader::Connect('Queue',			'ickx\fw2\container\Queue');
		ClassLoader::Connect('Environment',		'ickx\fw2\core\environment\Environment');
		ClassLoader::Connect('CoreException',	'ickx\fw2\core\exception\CoreException');
		ClassLoader::Connect('StaticLog',		'ickx\fw2\core\log\StaticLog');
		ClassLoader::Connect('Stopwatch',		'ickx\fw2\core\log\Stopwatch');
		ClassLoader::Connect('CookieAuth',		'ickx\fw2\core\net\http\auth\CookieAuth');
		ClassLoader::Connect('Http',			'ickx\fw2\core\net\http\Http');
		ClassLoader::Connect('Request',			'ickx\fw2\core\net\http\Request');
		ClassLoader::Connect('Response',		'ickx\fw2\core\net\http\Response');
		ClassLoader::Connect('FileUpload',		'ickx\fw2\features\file_upload\FileUpload');
		ClassLoader::Connect('FileSystem',		'ickx\fw2\io\file_system\FileSystem');
		ClassLoader::Connect('IniFile',			'ickx\fw2\io\file_system\IniFile');
		ClassLoader::Connect('OutputBuffer',	'ickx\fw2\basic\outcontrol\OutputBuffer');
		ClassLoader::Connect('Url',				'ickx\fw2\basic\urls\url');
		ClassLoader::Connect('PhpIni',			'ickx\fw2\io\php_ini\PhpIni');
		ClassLoader::Connect('SDFI',			'ickx\fw2\io\sdf\SDFI');
		ClassLoader::Connect('DBI',				'ickx\fw2\io\rdbms\DBI');
		ClassLoader::Connect('Session',			'ickx\fw2\io\sessions\Session');
		ClassLoader::Connect('IController',		'ickx\fw2\mvc\app\controllers\interfaces\IController');
		ClassLoader::Connect('PagerUtility',	'ickx\fw2\other\pager\PagerUtility');
		ClassLoader::Connect('Router',			'ickx\fw2\router\Router');
		ClassLoader::Connect('Validator',		'ickx\fw2\security\validators\Validator');
		ClassLoader::Connect('PathTrait',		'ickx\fw2\traits\data_store\PathTrait');
		ClassLoader::Connect('Arrays',			'ickx\fw2\vartype\arrays\Arrays');
		ClassLoader::Connect('LazyArrayObject',	'ickx\fw2\vartype\arrays\LazyArrayObject');
		ClassLoader::Connect('Objects',			'ickx\fw2\vartype\objects\Objects');
		ClassLoader::Connect('Strings',			'ickx\fw2\vartype\strings\Strings');

		ClassLoader::Connect('ErrorController',	'ickx\fw2\mvc\app\controllers\error\ErrorController');

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());
	}

	/**
	 * phase2:Startup
	 */
	public static function Startup () {
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		$cache = Cache::init([static::class, __FUNCTION__]);
		if (false === $path = $cache->get('path')) {
			$cache->set('path', $path = [
				'var_dir'				=> FilePath::VAR_DIR(),
				'cache_dir'				=> FilePath::CACHE_DIR(),
				'log_dir'				=> FilePath::LOG_DIR(),
				'session_dir'			=> FilePath::SESSION_DIR(),
			]);
		}

		if (false !== $cache->get('created')) {
			FileSystem::CreateDirectory($path['var_dir'],		['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'ファイル展開', 'state_cache' => true]);
			FileSystem::CreateDirectory($path['cache_dir'],		['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'キャッシュ', 'state_cache' => true]);
			FileSystem::CreateDirectory($path['log_dir'],		['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'ログ', 'state_cache' => true]);
			FileSystem::CreateDirectory($path['session_dir'],	['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'セッション', 'state_cache' => true]);
		}

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('CreateDirectorys'));

		static::_IniSetup();

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('IniSetup'));

		static::_LogSetup();

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('LogSetup'));
	}

	/**
	 * phase3:Connection
	 */
	public static function Connection () {
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		\ickx\fw2\router\Router::Connect('/{:controller}/{:action}/{:args}');
		\ickx\fw2\router\Router::Connect('/{:args}');

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());
	}

	/**
	 * phase4:Clutch
	 */
	public static function Clutch () {
//@TODO Clutch enable switch
//@TODO FW2 Swich's
//@TODO Free dir layout (target dir adding)
		$vendor_dir_byte_length = strlen(FilePath::VENDOR_DIR());

		foreach (new \DirectoryIterator(FilePath::CONTROLLER_PATH()) as $app_dir) {
			if ($app_dir->isFile()) {
				continue;
			}
			if ($app_dir->isDot()) {
				continue;
			}

			$controller_name = ucfirst(preg_replace_callback("/(?:^|_)([a-z])/", function ($matches) {return strtoupper($matches[1]);}, $app_dir->getBasename()));
			$class_base = $app_dir->getPath() .'/'. $app_dir->getBasename() .'/'. $controller_name .'Clutch';
			$clutch_file_path = $class_base .'.php';
			$clutch_class_name = str_replace('/', "\\", substr($class_base, $vendor_dir_byte_length));

			clearstatcache(true, $clutch_file_path);
			if (file_exists($clutch_file_path) && class_exists($clutch_class_name, true)) {
				if (method_exists($clutch_class_name, 'Checking')) {
					$clutch_class_name::Checking();
				}
				if (method_exists($clutch_class_name, 'Startup')) {
					$clutch_class_name::Startup();
				}
				if (method_exists($clutch_class_name, 'Connection')) {
					$clutch_class_name::Connection();
				}
			}
		}
	}

	/**
	 * phase5:Ignition
	 */
	public static function Ignition ($url = null, $app_namespace = null) {
		$called_class = static::class;

		static::SetClassVar(static::URL_PARAM_NAME, $url ?: static::GetRequestUrl());
		static::SetClassVar('app_namespace', $app_namespace ?: substr($called_class, 0, strrpos($called_class, "\\")));

		if (Environment::IsCliServer()) {
			if (false === static::SupportBuiltInServer()) {
				return false;
			}
		}

		static::Checking();

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());
		static::Startup();
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('start up'));
		static::Connection();
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('connection'));
		static::Clutch();
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('clutch'));

		$ret = \ickx\fw2\mvc\Engine::Ignition(static::GetClassVar(static::URL_PARAM_NAME), static::GetClassVar('app_namespace'), static::class, static::GetCallType());
		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('ignition'));

		assert((static::$reportingLevel & static::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->outputDump(FilePath::APP_PROFILE_LOG_PATH()));

		return $ret;
	}

	/**
	 * DirectIgnition
	 */
	public static function DirectIgnition ($url = null, $app_namespace = null) {
		$ret = null;

		try {
			$ret = static::Ignition($url, $app_namespace);
		} catch (\ickx\fw2\core\exception\CoreException $e) {
			$message = $e->getStatusMessage();
			$trace = $e->getTraceAsString();
		} catch (\Exception $e) {
			$message = \ickx\fw2\core\exception\CoreException::ConvertinternalEncoding($e->getMessage());
			$trace = \ickx\fw2\core\exception\CoreException::ConvertToStringMultiLine($e);
		}

		//システムエラー画面探索と表示
		if (isset($e)) {
			foreach ([
				sprintf('%s/%s/app/system_error/system_error.php', static::GetVendorPath(), static::GetAppPath()),
				sprintf('%s/%s/system_error/system_error.php', static::GetVendorPath(), static::GetAppPath()),
				sprintf('%s/app/system_error/system_error.php', __DIR__),
			] as $path) {
				if (file_exists($path)) {
					require $path;
					break;
				}
			}
		}

		Environment::IsCli() ?: \ickx\fw2\basic\outcontrol\OutputBuffer::EndFlush();

		//------------------------------------------------------
		//実行時間計測：ログ出力
		//------------------------------------------------------
		//ログ出力文字列の生成
		$log = sprintf("Execution time=%s, URL=%s", $diff = Stopwatch::RequestTimeDiff(), static::GetClassVar(static::URL_PARAM_NAME));

		//最大許容実行時間を越えていた場合はアラートを出す
		if ($diff > Engine::MAX_EXECUTE_TIME) {
			//ログ出力
			StaticLog::WriteErrorLog('SLOW DOWN!!:'. $log);
		}

		//実行時間ログの出力
		StaticLog::WriteLog('timer', $log);

		return $ret;
	}

	/**
	 * リクエストログ設定
	 */
	protected static function _RequestLogOptions () {
		return [
			'exclusion_uri'			=> [],
			'exclusion_remote_addr'	=> [],
			'disable_curl_log'		=> false,
			'disable_json_log'		=> false,
			'encrypt_disable'		=> true,
			'encrypt_function'		=> '\ickx\fw2\crypt\OpenSSL::EncryptRandom',
			'encrypt_argument'		=> [':replace_point:value', StaticLog::DEFAULT_PASSWORD, StaticLog::DEFAULT_SALT, StaticLog::DEFAULT_HMAC_KEY, StaticLog::DEFAULT_SECRET_KEY_LENGTH, StaticLog::DEFAULT_HASH_ALGORITHM],
			'log_format'			=> "[%s] %s\n",
			'log_values'			=> [StaticLog::GetRequestTime(), ':replace_point:value'],
		];
	}

	/**
	 * utility:get request url
	 *
	 * @return	request url
	 */
	public static function GetRequestUrl () {
		$action_url = '/';
		if (Environment::IsCli()) {
			$argv = $_SERVER['argv'];
			array_shift($argv);
			if (!isset($argv[0])) {
				throw new \Exception('URLが設定されていません。');
			}
			$action_url = $argv[0];
		} else {
			$request_uri = Environment::IsCli() ? (Cli::GetRequestParameterList()[0] ?? '') : $_SERVER['REQUEST_URI'];
			$path_info = parse_url(sprintf('http://localhost%s', $request_uri), \PHP_URL_PATH);
			if (preg_match("@\?([^\?&=]+)(?:&|$)@", $path_info, $mat)) {
				$action_url = $mat[1];
			} else {
				$action_url = substr($path_info, strlen(dirname($_SERVER['SCRIPT_NAME'])));
				if (substr($action_url, 0, 1) !== '/') {
					$action_url = '/' . $action_url;
				}
			}
		}

		return $action_url;
	}

	/**
	 * utility:SystemRootへのフルパスを返します。
	 *
	 * @return	string	SystemRootへのフルパス
	 */
	public static function GetSystemRootPath () {
		return str_replace("\\", '/', dirname(dirname(static::GetVendorPath())));
	}

	/**
	 * utility:SRCへのフルパスを返します。
	 *
	 * @return	string	SystemRootへのフルパス
	 */
	public static function GetSrcPath () {
		return static::GetSystemRootPath() . '/src';
	}

	/**
	 * utility:vendorへのフルパスを返します。
	 *
	 * @return	string	vendorへのフルパス
	 */
	public static function GetVendorPath () {
		return rtrim(strstr(str_replace(DIRECTORY_SEPARATOR, '/', __FILE__), str_replace("\\", '/', __CLASS__), true), '/');
	}

	/**
	 * utility:get vendor name
	 *
	 * @return	string	vendor name
	 */
	public static function GetVendorName () {
		$called_class = static::class;
		return substr($called_class, 0, strpos($called_class, "\\"));
	}

	/**
	 * utility:get package path
	 *
	 * @return	string	package path
	 */
	public static function GetPackagePath () {
		$called_class = static::class;
		$offset = strpos($called_class, "\\") + 1;
		return str_replace("\\", '/', substr($called_class, 0, strpos($called_class, "\\", $offset)));
	}

	/**
	 * utility:get package full path
	 *
	 * @return	string	package full path
	 */
	public static function GetPackageFullPath () {
		return static::GetSrcPath() .'/'. static::GetPackagePath();
	}

	/**
	 * utility:get package name
	 *
	 * @return	string	package name
	 */
	public static function GetPackageName () {
		$package_path = static::GetPackagePath();
		return substr($package_path, strrpos($package_path, '/') + 1);
	}

	public static function GetCallTypePath () {
		$called_class = static::class;
		$offset = strpos($called_class, "\\") + 1;
		$offset = strpos($called_class, "\\", $offset) + 1;
		return str_replace("\\", '/', substr($called_class, 0, strpos($called_class, "\\", $offset)));
	}

	/**
	 * utility:get package full path
	 *
	 * @return	string	package full path
	 */
	public static function GeCallTypeFullPath () {
		return static::GetVendorPath() .'/'. static::GetCallTypePath();
	}

	/**
	 * utility:get app name
	 *
	 * @return	string	package name
	 */
	public static function GetCallType () {
		$call_type = static::GetCallTypePath();
		return substr($call_type, strrpos($call_type, '/') + 1);
	}

	/**
	 * utility:get app path
	 *
	 * @return	string	package path
	 */
	public static function GetAppPath () {
		$called_class = static::class;
		$offset = strpos($called_class, "\\") + 1;
		$offset = strpos($called_class, "\\", $offset) + 1;
		$offset = strpos($called_class, "\\", $offset) + 1;
		return str_replace("\\", '/', substr($called_class, 0, strpos($called_class, "\\", $offset)));
	}

	/**
	 * utility:get package full path
	 *
	 * @return	string	package full path
	 */
	public static function GeAppFullPath () {
		static $app_full_path;
		if (!isset($app_full_path)) {
			if (!is_null($composer_loader = ClassLoader::GetComposerLoader())) {
				$psr4_map = $composer_loader->getPrefixesPsr4();
				for ($base_app_path = $app_path = str_replace("/", "\\", rtrim(static::GetAppPath(), '/')) . "\\"; !isset($psr4_map[$app_path]) && $app_path !== "\\"; $app_path = mb_substr($app_path, 0, mb_strrpos($app_path, "\\", -2)) . "\\");
			}

			if (isset($psr4_map[$app_path])) {
				$app_full_path = str_replace("\\", '/', str_replace($app_path, realpath($psr4_map[$app_path][0]) . '/', $base_app_path));
			}

			$vendor_dir = static::GetVendorPath();

			isset($app_full_path) && file_exists($app_full_path)
			 ?: file_exists($app_full_path = dirname($vendor_dir) . '/' . static::GetAppPath())
			 ?: file_exists($app_full_path = $vendor_dir .'/'. static::GetAppPath())
			 ?: false;
		}

		return $app_full_path;
	}

	/**
	 * utility:get app name
	 *
	 * @return	string	package name
	 */
	public static function GetAppName () {
		$app_path = static::GetAppPath();
		return substr($app_path, strrpos($app_path, '/') + 1);
	}

	/**
	 * utility:指定されたパスから指定された回数分上の階層に移動したパスを返します。
	 *
	 * @param	string	$path	パス
	 * @param	int		$count	繰り上がる回数
	 * @return	string	移動したパス
	 */
	protected static function _MovedUpPath ($path, $count) {
		for ($i = 0;$i < $count;$i++) {
			$path = dirname($path);
		}
		return str_replace("\\", '/', $path);
	}

	/**
	 * utility:パスから最下層の名称を取得します。
	 *
	 * @param	string	$path	パス
	 * @return	string	パスの最下層名
	 */
	protected static function _CutoutBottomPath ($path) {
		return trim(strrchr($path, '/'), '/');
	}

	/**
	 * utility:パッケージのパスを返します。
	 *
	 * @return	string	パッケージのパス
	 */
	public static function GetCalledFlywheelPath () {
		return str_replace("\\", '/', static::class);
	}

	/**
	 * utility:URLのパスパートにアプリケーションルートからのパスを追加して返します。
	 *
	 * @param	string	$part_url	URLのパスパート
	 * @return	string	アプリケーションルートからのパスパート
	 */
	public static function AssetUrl ($part_url) {
		if (Environment::IsCliServer()) {
			return $part_url;
		}
		return '/'. ltrim(str_replace("\\", '/', dirname(Environment::IsCli() ? getcwd() : $_SERVER['SCRIPT_NAME']) . '/' . ltrim($part_url, '/')), '/');
	}

	/**
	 * utility:URLのパスパートにアプリケーションルートからのパスを追加して返します。
	 *
	 * @param	string	$part_url	URLのパスパート
	 * @return	string	アプリケーションルートからのパスパート
	 */
	public static function AssetFullUrl ($part_url) {
		$current_protocol = static::_IsHttps() ? 'https' : 'http';
		$port = $_SERVER['SERVER_PORT'];
		$port_str = $port !== 80 && $port !== 443 ? ':'. $port : '';
		return sprintf('%s://%s%s%s', $current_protocol, $_SERVER['SERVER_NAME'], $port_str, static::AssetUrl($part_url));
	}

	/**
	 * utility:現在のURLを更新。
	 *
	 * @param	string	現在のURL
	 */
	public static function SetCurrnetUrl ($url) {
		return static::SetClassVar(static::URL_PARAM_NAME, $url);
	}

	/**
	 * utility:現在のURLを返します。
	 *
	 * @return	string	現在のURL
	 */
	public static function GetCurrnetUrl () {
		return static::GetClassVar(static::URL_PARAM_NAME);
	}

	/**
	 * utility：Routerに登録されている情報からURLを作成します。
	 *
	 * @param	string|ickx\fw2\mvc\app\AppController	$controller		コントローラ名またはコントローラ
	 * @param	string									$action_name	アクション名
	 * @param	array									$parameters		パラメータ
	 * @param	array									$var_parameters	遅延評価用パラメータ
	 * @param	string									$encoding		エンコーディング
	 * @return	string|bool								URL マッチするURLが無い場合はfalse
	 */
	public static function MakeUrl ($controller, $action_name = 'index', $parameters = [], $var_parameters = [], $encoding = null) {
		return \ickx\fw2\router\Router::GetUrl((is_string($controller) ? $controller : $controller->controller), $action_name, $parameters, $var_parameters, $encoding);
	}

	/**
	 *
	 */
	protected static function _IniSetup () {
		$target_ini_list = [
			FilePath::FW2_DEFAULTS_RESOURCE_INI_PATH(),
			FilePath::FW2_DEFAULTS_ERROR_INI_PATH(),
			FilePath::FW2_DEFAULTS_FILE_UPLOAD_INI_PATH(),
		];

		$ini_cache_dir = FilePath::INI_CACHE_DIR();
		$options = ['cache_dir' => $ini_cache_dir, 'static_cache' => true];
		$allow_parameter_list = ['ini_path'];

		foreach ($target_ini_list as $target_ini) {
			$app_ini = IniFile::GetConfig($target_ini, $allow_parameter_list, $options);
			if (!isset($app_ini['ini_path'])) {
				throw CoreException::RaiseSystemError('iniファイルパスが設定されていません。');
			}
			PhpIni::ReflectFromIniFile($app_ini['ini_path'], null, $options);
		}

		if (!Environment::IsCli()) {
			$session_ini_path = FilePath::SESSION_INI_PATH();
			if ($session_ini_path === null || $session_ini_path === '' || FileSystem::IsReadableFile($session_ini_path) !== true) {
				$session_ini_path =FilePath::FW2_DEFAULTS_SESSION_INI_PATH();
			}
			$app_session_ini = IniFile::GetConfig($session_ini_path, $allow_parameter_list, $options);
			if (!isset($app_session_ini['ini_path'])) {
				throw CoreException::RaiseSystemError('セッション用iniファイルパスが設定されていません。');
			}
			PhpIni::ReflectFromIniFile($app_session_ini['ini_path'], PhpIni::SESSION, $options);
		}
	}

	protected static function _LogSetup () {
		$cache = Cache::init([static::class, __FUNCTION__]);
		if ($cache->has('path')) {
			$path = $cache->get('path');
		} else {
			$cache->set('path', $path = [
				'app_timer_log_path'	=> FilePath::APP_TIMER_LOG_PATH(),
				'sql_error_log_path'	=> FilePath::SQL_ERROR_LOG_PATH(),
				'php_error_log_path'	=> FilePath::PHP_ERROR_LOG_PATH(),
				'app_log_dir'			=> FilePath::APP_LOG_DIR(),
			]);
		}

		if (!$cache->has('created') || !$cache->get('created')) {
			FileSystem::CreateDirectory($path['app_log_dir'],	['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'アプリログディレクトリ', 'state_cache' => true]);

			FileSystem::CreateFile($path['app_timer_log_path'],	['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'タイマーログ', 'state_cache' => true]);
			FileSystem::CreateFile($path['sql_error_log_path'],	['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'SQLエラー', 'state_cache' => true]);
			FileSystem::CreateFile($path['php_error_log_path'],	['auto_create' => true, 'parents' => true, 'skip' => true, 'name' => 'PHPエラー', 'state_cache' => true]);

			$cache->set('created', true);
		}

		//タイマーログファイルディレクトリの設定
		StaticLog::SetLogFilePath('timer', $path['app_timer_log_path'], Strings::LF, true);

		//SQLエラーログディレクトリ
		StaticLog::SetLogFilePath('sql_error', $path['sql_error_log_path'], Strings::LF, true);

		//PHPエラーログディレクトリ
		StaticLog::SetErrorLogFilePath($path['php_error_log_path'], Strings::LF, true);

		//リクエストログ（この段階でログを取得する）
		if (!Environment::IsCli()) {
			StaticLog::SetRequestLog($path['app_log_dir'], static::_RequestLogOptions(), Strings::LF, true);
		}
	}

	/**
	 * utility:現在の接続がHTTPSかどうか返します。
	 *
	 * @return	bool	現在の接続がHTTPSの場合はtrue, そうでない場合はfalse。
	 */
	protected static function _IsHttps () {
		return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
	}
}
