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
use ickx\fw2\io\cache\Cache;

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

	protected static $_cachePathList	= [];
	protected static $_cache			= null;

	/**
	 * 動的展開を行い設定ファイルから設定を取得します。
	 *
	 * @param	string	$ini_path				設定ファイルのパス
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @param	array	$options				オプション
	 * @return	array	設定のリスト
	 */
	public static function GetConfig ($ini_path, $allow_parameter_list = [], $options = []) {
		//==============================================
		// キャッシュせず展開ずみの値を返す場合
		//==============================================
		$static = $options['static'] ?? false;
		if ($options['disable_cache'] ?? false) {
			return static::ReflectDynamicConfig(static::LoadConfig($ini_path, $allow_parameter_list, $options), $allow_parameter_list, $options, $static ? ConstUtility::REPLACE_MODE_STATIC : ConstUtility::REPLACE_MODE_ALL);
		}

		//==============================================
		// キャッシュ設定
		//==============================================
		$cache_path = static::$_cachePathList[$ini_path] ?? static::$_cachePathList[$ini_path] = static::GetCachePath($ini_path, $options['cache_dir'] ?? '');
		$cache = static::$_cache ?? static::$_cache = Cache::init($ini_path, $options['cache_storage_type'] ?? null, ...($options['cache_args'] ?? []));

		//==============================================
		// キャッシュリフレッシュ時の処理
		//==============================================
		if ($options['cache_refresh'] ?? false) {
			$cache->remove($cache_path);
		}

		$static_cache = $options['static_cache'] ?? false;
		if ($cache_path !== null) {
			$cache_data = $cache->get($cache_path);
			if ($cache->state()) {
				clearstatcache(true, $cache_data['path']);
				if ($cache_data['mtime'] === filemtime($cache_data['path'])) {
					return static::ReflectDynamicConfig($cache_data['data'], $allow_parameter_list, $options, $static_cache ? ConstUtility::REPLACE_MODE_ONLY_CALLBACK : ConstUtility::REPLACE_MODE_ALL, true);
				}
			}
		}

		$ini_set = static::ReflectDynamicConfig(static::LoadConfig($ini_path, $allow_parameter_list, $options), $allow_parameter_list, $options, $static_cache ? ConstUtility::REPLACE_MODE_STATIC : ConstUtility::REPLACE_MODE_ALL);

		//キャッシュが有効な場合はキャッシュファイルを構築する
		if ($cache_path !== null) {
			clearstatcache(true, $ini_path);
			$cache->set($cache_path, [
				'data'	=> $ini_set,
				'path'	=> $ini_path,
				'mtime'	=> filemtime($ini_path),
			]);
		}

		return $static_cache ? static::ReflectDynamicConfig($ini_set, $allow_parameter_list, $options, ConstUtility::REPLACE_MODE_ONLY_CALLBACK, true) : $ini_set;
	}

	/**
	 * 動的展開を行わず設定ファイルから設定を取得します。
	 *
	 * @param	string	$ini_path				設定ファイルのパス
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @param	array	$options				オプション
	 * @return	array	設定のリスト
	 */
	public static function LoadConfig ($ini_path, $allow_parameter_list = [], $options = []) {
		//==============================================
		// 設定値の取得と展開
		//==============================================
		// 設定値の取得
		$ini_set = parse_ini_file($ini_path, TRUE, INI_SCANNER_RAW);

		// 有意な値を取れない場合は空配列を返して終了
		if (empty($ini_set)) {
			return [];
		}

		// 設定名の確定
		$key = $options['target'] ?? key($ini_set);

		// 設定値配列の次元を下げる
		$ini_set = $ini_set[$key];

		//処理の終了
		return $ini_set;
	}

	/**
	 * INIファイルキャッシュ用のファイルパスを構築します。
	 *
	 * @param	string	$ini_path	INIファイルパス
	 * @param	string	$cache_dir	キャッシュルートディレクトリ
	 * @return	string	INIキャッシュファイルパス
	 */
	public static function GetCachePath ($ini_path, $cache_dir) {
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
	 * 設定配列内の動的設定を反映します。
	 *
	 * @param	array	$ini_list				設定配列
	 * @param	array	$allow_parameter_list	許可する設定のリスト
	 * @param	string	$replace_mode			定数展開器の動作モード
	 * 											ConstUtility::REPLACE_MODE_ALL：全ての定数を展開する
	 * 											ConstUtility::REPLACE_MODE_STATIC：STATIC：PHP_CALLBACK以外を展開する
	 * 											ConstUtility::REPLACE_MODE_ONLY_CALLBACK：PHP_CALLBACKのみ展開する
	 * @param	bool	$redeploy				キャッシュから展開された場合など、再展開時フラグ
	 * @return	array	動的設定を反映した配列
	 */
	public static function ReflectDynamicConfig ($ini_list, $allow_parameter_list, $options = [], $replace_mode = ConstUtility::REPLACE_MODE_DEFAULT, $redeploy = false) {
		//初期化
		$config_list = [];

		//許可する設定値のリスト
		if (is_null($allow_parameter_list)) {
			$enable_ini_name_list = null;
		} else {
			$enable_ini_name_list = array_flip($allow_parameter_list);
		}

		$replace_mode_only_callback = $replace_mode === ConstUtility::REPLACE_MODE_ONLY_CALLBACK;

		foreach ($ini_list as $name => $value) {
			if (!is_null($enable_ini_name_list) && !isset($enable_ini_name_list[$name])) {
				throw CoreException::RaiseSystemError('許可されていない設定名が設定されています。name:%s, value:%s', [$name, $value]);
			}
			if (!$replace_mode_only_callback && !$redeploy && is_array($value)) {
				foreach ($value as $option_name => $option_value) {
					if ($options['use_option_name_key'] ?? false) {
						$config_list[$name][ConstUtility::ReplacePhpConstValue($option_name)] = static::ReflectOptions($name, ConstUtility::ReplacePhpConstValue($option_value, $replace_mode), $options);
					} else {
						$config_list[$name][] = [
							'name'	=> ConstUtility::ReplacePhpConstValue($option_name),
							'value'	=> static::ReflectOptions($name, ConstUtility::ReplacePhpConstValue($option_value, $replace_mode), $options),
						];
					}
				}
			} else {
				$config_list[$name] = $replace_mode_only_callback ? ConstUtility::ReplacePhpConstValue($value, $replace_mode) : static::ReflectOptions($name, ConstUtility::ReplacePhpConstValue($value, $replace_mode), $options);
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

	public static function ConvByte ($value) {
		if (!in_array($last = (string) substr($value, -1), ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], true)) {
			$value = substr($value, 0, -1);
			switch(strtolower($last)) {
				case 'p':
					$value *= 1024;
				case 't':
					$value *= 1024;
				case 'g':
					$value *= 1024;
				case 'm':
					$value *= 1024;
				case 'k':
					$value *= 1024;
			}
		}
		return $value;
	}

	public static function ConvUnitByte ($value) {
		$unit = ['', 'K', 'M', 'G', 'T', 'P'];
		$factor = floor((strlen($value) - 1) / 3);
		return $value / pow(1024, $factor) . $unit[$factor] ?? 'P';
	}
}
