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
	use \ickx\fw2\traits\data_store\ClassVariableTrait;

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
	public static function __callStatic($name, $args = []) {
		$path = static::GetClassVar($name);
		if (!$path || (isset($args[0]) && !empty($args[0]))) {
			return call_user_func_array(get_called_class().'::MakePath', array_merge([$name], $args));
		}
		return $path;
	}

	/**
	 * クラス定数名とパス設定から実際のパスを構築します。
	 *
	 * @param	string	$name		クラス定数名
	 * @param	array	$path_config	パス設定
	 * @return	string	パス
	 */
	public static function MakePath ($name, $node_list = [], $path_config = null) {
		$class_const = get_called_class().'::'.$name;
		if (!defined($class_const)) {
			throw new \Exception(sprintf('未定義のパス定数を設定されました。%s', $class_const));
		}
		if (static::HasClassVar($name)) {
			return static::GetClassVar($name);
		}

		$path = constant($class_const);
		$add_node_flag = !empty($node_list);
		!$add_node_flag ?: $path .= '/'. implode('/', $node_list);
		foreach ($path_config ?: static::PathConfig() as $name => $value) {
			$path = str_replace('{:'.$name.'}', $value, $path);
		}
		$path = str_replace('//', '/', $path);
		$add_node_flag ?: static::SetClassVar($name, $path);
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
