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
		// キャッシュ設定
		//==============================================
		$cache = static::$_cache ?? static::$_cache = Cache::init(static::class, $options['cache_storage_type'] ?? null, ...($options['cache_args'] ?? []));
		$cache_path = static::$_cachePathList[$ini_path] ?? static::$_cachePathList[$ini_path] = static::GetCachePath($ini_path, $options['cache_dir'] ?? '');

		//==============================================
		// キャッシュリフレッシュ時の処理
		//==============================================
		if ($options['cache_refresh'] ?? false) {
			$cache->remove($cache_path);
		}

		$static_cache = $options['static_cache'] ?? false;
		if ($cache_path !== null) {
			$ini_set = $cache->get($cache_path);
			if ($cache->state()) {
				return static::ReflectDynamicConfig($ini_set, $allow_parameter_list, $options, $static_cache ? ConstUtility::REPLACE_MODE_ONLY_CALLBACK : ConstUtility::REPLACE_MODE_ALL);
			}
		}

		$ini_set = static::ReflectDynamicConfig(static::LoadConfig($ini_path, $allow_parameter_list, $options), $allow_parameter_list, $options, $static_cache ? ConstUtility::REPLACE_MODE_STATIC : ConstUtility::REPLACE_MODE_ALL);

		//キャッシュが有効な場合はキャッシュファイルを構築する
		if ($cache_path !== null) {
			$cache->set($cache_path, $ini_set);
		}

		return $static_cache ? static::ReflectDynamicConfig($ini_set, $allow_parameter_list, $options, ConstUtility::REPLACE_MODE_ONLY_CALLBACK) : $ini_set;
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
	 * @return	array	動的設定を反映した配列
	 */
	public static function ReflectDynamicConfig ($ini_list, $allow_parameter_list, $options = [], $replace_mode = ConstUtility::REPLACE_MODE_DEFAULT) {
		//初期化
		$config_list = [];

		//許可する設定値のリスト
		$enable_ini_name_list = array_flip($allow_parameter_list);

		$replace_mode_only_callback = $replace_mode === ConstUtility::REPLACE_MODE_ONLY_CALLBACK;

		foreach ($ini_list as $name => $value) {
			if (!is_null($enable_ini_name_list) && !isset($enable_ini_name_list[$name])) {
				throw CoreException::RaiseSystemError('許可されていない設定名が設定されています。name:%s, value:%s', [$name, $value]);
			}
			if (!$replace_mode_only_callback && is_array($value)) {
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
}
