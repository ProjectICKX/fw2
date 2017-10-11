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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\data_store;

use ickx\fw2\io\cache\Cache;

/**
 * パス管理特性です。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait PathTrait {
	public static $_cache = null;

	/**
	 * パス設定を返します。
	 *
	 * @return	array	パス設定
	 */
	abstract public function PathConfig ();

	/**
	 * クラス定数名をスタティックメソッド呼び出しした際に実行される実メソッドです。
	 *
	 * @param	string	$name	クラス定数名
	 * @param	array	$args	引数
	 * @return	string	クラス定数値
	 */
	public static function __callStatic($name, $args) {
//		static::$_cache ?? static::$_cache = Cache::init(static::class);

		$cache_name = $name;
		if (!empty($args)) {
			$cache_name = $cache_name . '<>' . hash('sha256', json_encode($args, \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT));
		}

// 		if (static::$_cache->has($cache_name)) {
// 			return static::$_cache->get($cache_name);
// 		}

// 		static::$_cache->set($cache_name, $path = static::MakePath($name, $args[1] ?? [], $args[2] ?? null));
		$path = static::MakePath($name, $args[0] ?? [], $args[1] ?? null);
		return $path;
	}

	/**
	 * クラス定数名とパス設定から実際のパスを構築します。
	 *
	 * @param	string	$const_name		クラス定数名
	 * @param	array	$path_config	パス設定
	 * @return	string	パス
	 */
	public static function MakePath ($const_name, $node_list = [], $path_config = null) {
// 		static::$_cache ?? static::$_cache = Cache::init(static::class);

// 		if (static::$_cache->has($name)) {
// 			return static::$_cache->get($name);
// 		}

		$class_const = static::class.'::'.$const_name;
// 		if (!static::$_cache->has($class_const)) {
			if (!defined($class_const)) {
				throw new \Exception(sprintf('未定義のパス定数を設定されました。%s', $class_const));
			}
// 			static::$_cache->set($class_const, $path = constant($class_const));
			$path = constant($class_const);
// 		} else {
// 			$path = static::$_cache->get($class_const);
// 		}

// 		$path_config = static::$_cache->has('path_config') ? static::$_cache->get('path_config') :  static::PathConfig();
		$path_config = static::PathConfig();

		$add_node_flag = !empty($node_list);
		!$add_node_flag ?: $path .= '/'. implode('/', (array) $node_list);

		foreach ($path_config as $name => $value) {
			$path = str_replace('{:'.$name.'}', $value, $path);
		}
		$path = str_replace('//', '/', $path);

		for (;false !== ($start = mb_strpos($path, '{:')) && false !== ($end = mb_strpos($path, '}', $start));) {
			$part = mb_substr($path, $end + 1);
			$name = mb_substr($path, $start + 2, $end - 2);

			$class_const = static::class.'::'.$name;
			if (!defined($class_const)) {
				throw new \Exception(sprintf('未定義のパス定数を設定されました。%s', $class_const));
			}

			$path = [static::class, $name]() . $part;
		}

// 		$add_node_flag ?: static::$_cache->set($name, $path);

		return $path;
	}

	/**
	 * ディレクトリパスを構築します。
	 *
	 * @param	array	$node_list	ディレクトリパス構築用ノードリスト
	 * @return	string	ディレクトリパス
	 */
	public static function CreateDirPath ($node_list) {
		return static::CreateFilePath($node_list) . '/';
	}

	/**
	 * ファイルパスを構築します。
	 *
	 * @param	array	$node_list	ファイルパス構築用ノードリスト
	 * @return	string	ファイルパス
	 */
	public static function CreateFilePath ($node_list) {
		return str_replace('//', '/', implode('/', $node_list));
	}
}
