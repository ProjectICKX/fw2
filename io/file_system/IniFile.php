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

namespace ickx\fw2\io\file_system;

use \ickx\fw2\core\exception\CoreException;
use \ickx\fw2\other\misc\ConstUtility;

/**
 * INI形式ファイルを扱います。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class IniFile {
	use traits\DirectoryTrait,
		traits\FileTrait;

	/** @var キャッシュファイルパス */
	protected static $_iniCacheFilePathList = [];

	/**
	 * 設定ファイルから設定を取得します。
	 *
	 * @param	string	$ini_path				設定ファイルのパス
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @param	array	$options				オプション
	 * @return	array	設定のリスト
	 */
	public static function GetConfig ($ini_path, $allow_parameter_list = [], $options = []) {
		$static_cache = isset($options['static_cache']) && $options['static_cache'];
		isset($options['cache_dir']) && static::$_iniCacheFilePathList[$ini_path] = static::GetCacheFilePath($ini_path, $options['cache_dir']);
		$cache_file_path = isset(static::$_iniCacheFilePathList[$ini_path]) ? static::$_iniCacheFilePathList[$ini_path] : null;

		if ($static_cache && $cache_file_path !== null) {
			$ini_set = static::LoadCache($ini_path, $cache_file_path);
			if ($ini_set !== null) {
				return $ini_set;
			}
		}

		$ini_list = static::ReflectDynamicConfig(static::LoadConfig($ini_path, $allow_parameter_list, $options), $allow_parameter_list, $options);

		//キャッシュが有効な場合はキャッシュファイルを構築する
		if ($static_cache && $cache_file_path !== null) {
			$parent_cache_dir = dirname($cache_file_path);
			!file_exists($parent_cache_dir) && mkdir($parent_cache_dir, 0755, true);
			file_put_contents($cache_file_path, "<?php\n\nreturn " . var_export($ini_list, true));
		}

		return $ini_list;
	}

	/**
	 * 設定ファイルから設定を取得します。
	 *
	 * @param	string	$ini_path				設定ファイルのパス
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @param	array	$options				オプション
	 * @return	array	設定のリスト
	 */
	public static function LoadConfig ($ini_path, $allow_parameter_list = [], $options = []) {
		static $enable_apcu;

		if (!isset($enable_apcu)) {
			$enable_apcu = function_exists('apc_store');
		}

		if ($enable_apcu) {
			//==============================================
			//キャッシュリターン
			//==============================================
			isset($options['cache_dir']) && static::$_iniCacheFilePathList[$ini_path] = static::GetCacheFilePath($ini_path, $options['cache_dir']);
			$cache_file_path = static::$_iniCacheFilePathList[$ini_path] ?? null;

			if ($options['clear'] ?? false) {
				\apcu_delete($cache_file_path);
			}

			if ($cache_file_path !== null) {
				$hit = false;
				$ini_set = apc_fetch($cache_file_path, $hit);
				if ($hit === true) {
					return $ini_set;
				}
			}

			//==============================================
			//設定値の調整
			//==============================================
			//設定値の取得
			$ini_set = parse_ini_file($ini_path, TRUE, INI_SCANNER_RAW);

			//設定名の確定
			$key = (isset($options['target']) && isset($ini_list[$options['target']])) ? $options['target'] : key($ini_set);
			if (empty($ini_set)) {
				return [];
			}

			//設定値配列の次元を下げる
			$ini_list = $ini_set[$key];

			//キャッシュが有効な場合はキャッシュファイルを構築する
			if ($cache_file_path !== null) {
				apcu_store($cache_file_path, $ini_list);
			}

			//処理の終了
			return $ini_list;
		} else {
			//==============================================
			//ファイルパスの検証
			//==============================================
			static::IsReadableFile($ini_path, ['raise_exception' => true, 'name' => $options['name'] ?? null]);

			//==============================================
			//キャッシュリターン
			//==============================================
			isset($options['cache_dir']) && static::$_iniCacheFilePathList[$ini_path] = static::GetCacheFilePath($ini_path, $options['cache_dir']);
			$cache_file_path = static::$_iniCacheFilePathList[$ini_path] ?? null;

			if ($cache_file_path !== null) {
				$ini_set = static::LoadCache($ini_path, $cache_file_path);
				if ($ini_set !== null) {
					return $ini_set;
				}
			}

			//==============================================
			//設定値の調整
			//==============================================
			//設定値の取得
			$ini_set = parse_ini_file($ini_path, TRUE, INI_SCANNER_RAW);

			//設定名の確定
			$key = (isset($options['target']) && isset($ini_list[$options['target']])) ? $options['target'] : key($ini_set);
			if (empty($ini_set)) {
				return [];
			}

			//設定値配列の次元を下げる
			$ini_list = $ini_set[$key];

			//キャッシュが有効な場合はキャッシュファイルを構築する
			if ($cache_file_path !== null) {
				$parent_cache_dir = dirname($cache_file_path);
				!file_exists($parent_cache_dir) && mkdir($parent_cache_dir, 0755, true);
				file_put_contents($cache_file_path, "<?php\n\nreturn " . var_export($ini_list, true));
			}

			//処理の終了
			return $ini_list;
		}
	}

	/**
	 * INIファイルキャッシュ用のファイルパスを構築します。
	 *
	 * @param	string	$ini_path	INIファイルパス
	 * @param	string	$cache_dir	キャッシュルートディレクトリ
	 * @return	string	INIキャッシュファイルパス
	 */
	public static function GetCacheFilePath ($ini_path, $cache_dir) {
		$round_dir_path	= md5(dirname($ini_path));

		return sprintf(
			'%s/%s/%s/%s_%s',
			$cache_dir,
			substr($round_dir_path, 0, 2),
			substr($round_dir_path, 2, 2),
			$round_dir_path,
			basename($ini_path)
		);
	}

	/**
	 * キャッシュファイルを読み込みます。
	 *
	 * @param	string	$ini_path			INIファイルパス
	 * @param	string	$cache_file_path	キャッシュファイルパス
	 * @return	array	キャッシュファイルから読み込んだINI
	 */
	public static function LoadCache ($ini_path, $cache_file_path) {
		clearstatcache(false, $cache_file_path);
		clearstatcache(false, $ini_path);
		return file_exists($cache_file_path) && filemtime($cache_file_path) < filemtime($ini_path) ? include $cache_file_path : null;
	}

	/**
	 * 設定配列内の動的設定を反映します。
	 *
	 * @param	array	$ini_list				設定配列
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @return	array	動的設定を反映した配列
	 */
	public static function ReflectDynamicConfig ($ini_list, $allow_parameter_list, $options = []) {
		//初期化
		$config_list = [];

		//許可する設定値のリスト
		$enable_ini_name_list = array_flip($allow_parameter_list);

		foreach ($ini_list as $name => $value) {
			if (!isset($enable_ini_name_list[$name])) {
				throw CoreException::RaiseSystemError('許可されていない設定名が設定されています。name:%s, value:%s', [$name, $value]);
			}
			if (is_array($value)) {
				foreach ($value as $option_name => $option_value) {
					$config_list[$name][] = [
						'name'	=> ConstUtility::ReplacePhpConstValue($option_name),
						'value'	=> static::ReflectOptions($name, ConstUtility::ReplacePhpConstValue($option_value), $options),
					];
				}
			} else {
				$config_list[$name] = static::ReflectOptions($name, ConstUtility::ReplacePhpConstValue($value), $options);
			}
		}

		return $config_list;
	}

	/**
	 * オプションを反映します。
	 *
	 * @param	string	$name		オプション名
	 * @param	mixed	$value		値
	 * @param	array	$options	オプション
	 * @return	mixed	反映されたオプション値
	 */
	public static function ReflectOptions ($name, $value, $options) {
		if (!isset($options['options'][$name])) {
			return $value;
		}
		if (isset($options['options'][$name]['prefix'])) {
			$value = $options['options'][$name]['prefix'] . $value;
		}
		if (isset($options['options'][$name]['safix'])) {
			$value .= $options['options'][$name]['safix'];
		}
		return $value;
	}
}
