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

namespace ickx\fw2\io\php_ini;

use ickx\fw2\io\cache\file\ArrayCache;
use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\FileSystem;
use ickx\fw2\io\file_system\IniFile;
use ickx\fw2\vartype\arrays\Arrays;

/**
 * php.iniの設定値を扱います。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class PhpIni implements interfaces\IPhpIniConst {
	use	traits\PhpIniFileUploadTrait,
		traits\PhpIniSessionTrait;

	//==============================================
	//設定モード
	//==============================================
	const PHP_INI_USER		= 1;
	const PHP_INI_PERDIR	= 2;
	const PHP_INI_SYSTEM	= 4;
	const PHP_INI_ALL		= 7;

	//==============================================
	//コア
	//==============================================
	/**
	 * php ini設定を設定します。
	 *
	 * @param	string	$name	設定名
	 * @param	mixed	$value	設定値
	 * @return	array	設定の変更結果
	 */
	public static function Set ($name, $value = null) {
		if (!is_array($name)) {
			$ini_set = [$name => $value];
		} else {
			$ini_set = $name;
		}

		$ret = [];
		foreach ($ini_set as $name => $value) {
			if (!static::Updatable($name)) {
				throw CoreException::RaiseSystemError('設定を変更できない設定値を渡されました。name:%s, value:%s', [$name, $value]);
			}
			$ret[$name] = ini_set($name, $value);
		}

		if (count($ret) === 1) {
			return current($ret);
		}

		return $ret;
	}

	/**
	 * php ini設定を取得します。
	 *
	 * @param	string	$name	php ini設定名
	 * @return	mixed	php iniの設定値
	 */
	public static function Get ($name) {
		switch ($name) {
			case static::UPLOAD_TMP_DIR:
				return ini_get($name) ?: sys_get_temp_dir();
		}
		return ini_get($name);
	}

	/**
	 * バイトの単位を変換します。
	 *
	 * @param	int		$byte	単位
	 * @return	string	単位変換したバイト
	 */
	public static function ToByteAbbreviations ($byte) {
		if ($byte % 1073741824 === 0) {
			return $byte / 1073741824 . 'G';
		}
		if ($byte % 1048576 === 0) {
			return $byte / 1048576 . 'M';
		}
		if ($byte % 1024 === 0) {
			return $byte / 1024 . 'K';
		}
		return $byte;
	}

	/**
	 * 単位付きのバイトを数値に変換します。
	 *
	 * @param	string	$size_string	単位付きのバイト
	 * @return	int		数値化されたバイト
	 */
	public static function ToByte ($size_string) {
		$size_string = trim($size_string);
		switch(strtolower($size_string[strlen($size_string)-1])) {
			case 'g':
				$size_string *= 1024;
			case 'm':
				$size_string *= 1024;
			case 'k':
				$size_string *= 1024;
		}
		return $size_string;
	}

	/**
	 * php ini設定をデフォルトに戻します。
	 *
	 * @param	string	$name	php ini設定名
	 */
	public static function Restore ($name) {
		ini_restore($name);
	}

	/**
	 * 指定されたphp ini設定が変更できるか検証します。
	 *
	 * @param	string	$name		検証するphp ini設定名
	 * @param	string	$extension	検証時に絞り込む拡張名
	 * @return	bool	変更できる設定の場合はtrue, そうでない場合はfalse
	 */
	public static function Updatable ($name, $extension = null, $forced_obtain = false) {
		$access_lv = static::GetDetail($name, $extension, $forced_obtain)['access'];
		if ($access_lv === static::PHP_INI_USER) {
			return true;
		}
		if ($access_lv === static::PHP_INI_ALL) {
			return true;
		}
		return false;
	}

	/**
	 * 指定されたphp ini設定の詳細を取得します。
	 *
	 * @param	string	$name		php iniの設定名
	 * @param	string	$extension	検証時に絞り込む拡張名
	 * @return	array	php ini設定の詳細
	 */
	public static function GetDetail ($name, $extension = null, $forced_obtain = false) {
		static $detail_list;

		$extension_name = $extension ?: 0;
		if (!isset($detail_list[$extension_name]) || $forced_obtain) {
			$detail_list[$extension_name] = ini_get_all($extension, true);
		}

		$detail = $detail_list[$extension_name];
		$detail = isset($detail[$name]) ? $detail[$name] : null;

		if ($detail === null){
			if ($extension) {
				throw CoreException::RaiseSystemError('未定義の設定名を渡されました。name:%s, extension:%s', [$name, $extension]);
			} else {
				throw CoreException::RaiseSystemError('未定義の設定名を渡されました。name:%s', [$name]);
			}
		}

		return $detail;
	}

	/**
	 * 現在のphp ini設定を全て取得します。
	 *
	 * @param	string	$extension_name	絞り込む拡張名 null指定時は全ての結果を得られる
	 * @return	array	現在のphp ini設定の全て
	 */
	public static function GetCurrent ($extension_name = null) {
		switch ($extension_name) {
			case 'file_uploads':
				return static::GetFileUploadIniNameList();
			default:
				return ini_get_all($extension_name, false);
		}
	}

	public static function GetNestedCurrent ($extension_name = null) {
		$ret = [];
		foreach (static::GetCurrentVar($extension_name) as $ini_name => $value) {
			$ret = array_merge_recursive($ret, static::PurseIniName($ini_name, $value));
		}
		return $ret;
	}

	/**
	 * php iniの設定名のリストをもとに設定名の配列を構築します。
	 *
	 * @param	array	$ini_name_list	php iniの設定名のリスト
	 * @return	array	設定名の配列
	 */
	public static function PurseIniNameList (array $ini_name_list) {
		$ret = [];
		foreach ($ini_name_list as $ini_name) {
			$ret = array_merge_recursive($ret, static::PurseIniName($ini_name));
		}
		return $ret;
	}

	/**
	 * php iniの設定名を解析します。
	 *
	 * .が含まれる名称の場合、.で分割し、多次元化されます。
	 *
	 * @param	string	$ini_name	php iniの設定名
	 * @return	mixed	解析したphp iniの設定名
	 */
	public static function PurseIniName ($ini_name) {
		return Arrays::SetLowest([], explode('.', $ini_name), (func_num_args() == 2) ? func_get_arg(1) : $ini_name);
	}

	/**
	 * iniファイルを元にphp ini設定を変更します。
	 *
	 * @param	string	$ini_file_path	iniファイルのパス
	 * @param 	bool	$extension		絞り込み用拡張名
	 * @param	array	$options		オプション
	 * @return	array	設定後配列
	 */
	public static function ReflectFromIniFile ($ini_file_path, $extension = null, $options = []) {
		$cache_dir		= isset($options['cache']) ? $options['cache'] : null;

		if ($cache_dir) {
			$ini = ArrayCache::GetCache($ini_file_path, $cache_dir, $options);
			if ($ini) {
				static::Set($ini);
				return $ini;
			}
		}

		//ファイル生存系確認：本当はvalidationでやりたい
		FileSystem::IsReadableFile($ini_file_path);

		$allow_parameter_list = array_keys((($extension === null) ? static::GetCurrent() : static::GetCurrent($extension)));
		switch ($extension) {
			case static::SESSION:
				$allow_parameter_list[] = static::URL_REWRITER_TAGS;
				break;
		}

		$ini = IniFile::GetConfig($ini_file_path, $allow_parameter_list, $options);
		if ($cache_dir) {
			ArrayCache::SetCache($ini_file_path, $cache_dir, $ini, $options);
		}

		return static::Set($ini);
	}
}
