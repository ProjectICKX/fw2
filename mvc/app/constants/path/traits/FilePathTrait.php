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

namespace ickx\fw2\mvc\app\constants\path\traits;

use ickx\fw2\io\php_ini\PhpIni;

/**
 * ファイルパス管理特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait FilePathTrait {
	/**
	 * パス情報上書き設定
	 *
	 * @return	array	上書き設定
	 */
	public static function PathConfig () {
		return static::LasyClassVarAccessCallback('path', [static::class, 'PathConfigList']);
	}

	/**
	 * パス情報上書き設定用データ
	 *
	 * @return	array	パス情報上書き設定用データ
	 */
	public static function PathConfigList () {
		return [
			//==============================================
			//一般設定
			//==============================================
			'PHP_BINDIR'		=> PHP_BINDIR,
			'BOOT_MODE'			=> Environment::IsCli() ? 'cli' : 'web',

			//==============================================
			//アプリ設定
			//==============================================
			'SYSTEM_ROOT_DIR'		=> Flywheel::GetSystemRootPath(),
			'SRC_DIR'				=> Flywheel::GetSrcPath(),
			'VENDOR_DIR'			=> Flywheel::GetVendorPath(),
			'PACKAGE_DIR'			=> Flywheel::GetPackageFullPath(),
			'APP_DIR'				=> Flywheel::GeAppFullPath(),

			'VENDOR_NAME'			=> Flywheel::GetVendorName(),
			'PACKAGE_NAME'			=> Flywheel::GetPackageName(),
			'CALL_TYPE'				=> Flywheel::GetCallType(),
			'APP_NAME'				=> Flywheel::GetAppName(),

			'PACKAGE_NS_PATH'		=> static::GetPackageNsPath(),
			'APP_NS_PATH'			=> static::GetAppNsPath(),

			'SRC_DIR'				=> static::GetSrcDir(),
			'CONF_DIR'				=> static::GetConfDir(),
			'PASSWORD_DIR'			=> static::GetPasswordDir(),

			'VAR_DIR'				=> static::GetVarDir(),
			'AUTH_DIR'				=> static::GetAuthDir(),
			'CACHE_DIR'				=> static::GetCacheDir(),
			'LOG_DIR'				=> static::GetLogDir(),
			'SESSION_DIR'			=> static::GetSessionDir(),
			'PHP_UPLOAD_TMP_DIR'	=> PhpIni::Get(PhpIni::UPLOAD_TMP_DIR),
			'TMP_DIR'				=> static::GetTmpDir(),

			'APP_CACHE_DIR'			=> static::GetAppCacheDir(),

			'FW2_DEFAULTS_DIR'		=> Flywheel::GetVendorPath() . '/ickx/fw2/mvc/defaults',
		];
	}

	public static function GetPackageNsPath () {
		return implode('/', [
			Flywheel::GetVendorName(),
			Flywheel::GetPackageName(),
		]);
	}

	public static function GetAppNsPath () {
		return implode('/', [
			Flywheel::GetVendorName(),
			Flywheel::GetPackageName(),
			Flywheel::GetCallType(),
			Flywheel::GetAppName(),
		]);
	}

	public static function GetSrcDir () {
		return Flywheel::GetSystemRootPath() . '/src/';
	}

	public static function GetConfDir () {
		return Flywheel::GetSystemRootPath() . '/config/';
	}

	public static function GetPasswordDir () {
		return Flywheel::GetSystemRootPath() . '/password/';
	}

	public static function GetVarDir () {
		return Flywheel::GetSystemRootPath() . '/var/';
	}

	public static function GetAuthDir () {
		return static::GetVarDir() . 'auth/';
	}

	public static function GetCacheDir () {
		return static::GetVarDir() . 'cache/';
	}

	public static function GetLogDir () {
		return static::GetVarDir() . 'log/';
	}

	public static function GetSessionDir () {
		return static::GetVarDir() . 'session/';
	}

	public static function GetTmpDir () {
		return static::GetVarDir() . 'tmp/';
	}

	public static function GetAppCacheDir () {
		return static::GetCacheDir() . static::GetAppNsPath();
	}
}
