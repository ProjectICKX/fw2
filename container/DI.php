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
 * @package		container
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\container;

/**
 * 簡易DIコンテナ
 *
 * @category	Flywheel2
 * @package		container
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DI {
	use \ickx\fw2\traits\data_store\ClassVariableTrait;

	/**
	 * @property	array	名前に紐付くインスタンスまたは名前空間付クラス名を保持します
	 * @static
	 */
	protected static $_instanceList = [];

	/** @property	string	クラス名を保持します */
	protected $_className = '';

	/**
	 * このクラスのインスタンスを返します。
	 *
	 * @static
	 * @param	string	$class_name	保持するクラス名
	 * @return	\ickx\fw2\container\DI	このクラスのインスタンス
	 */
	protected static function _GetInstance ($class_name) {
		return (isset(static::$_instanceList[$class_name])) ? static::$_instanceList[$class_name] : static::$_instanceList[$class_name] = new static($class_name);
	}

	/**
	 * クラス名と実体を紐付ます。
	 *
	 * @static
	 * @param	string	$class_name	クラス名
	 * @param	mixed	$class_path	実体 stringの場合：名前空間から始まるクラス名のフルパス、objectの場合：インスタンス
	 */
	public static function Connect ($class_name, $class_path) {
		static::SetClassVar($class_name, $class_path);
		return $class_path;
	}

	/**
	 * 名前に紐付くコンテナが持つ実体を返します。
	 *
	 * @static
	 * @param	string	$class_name	クラス名
	 */
	public static function GetClassPath ($class_name) {
		return static::GetClassVar($class_name);
	}

	/**
	 * 名前に紐付く値を返します。
	 *
	 * @param	string	$name	値名
	 * @return	mixed	値
	 */
	public static function GetValue ($name) {
		return static::GetClassVar($name);
	}

	/**
	 * クラスの実体を返します。
	 *
	 * @static
	 * @param	string	$class_name	クラス名
	 * @param	array	$args		引数
	 * [
	 *     0   => メソッド名
	 *     1...=> 引数
	 * ]
	 * @return	mixed	クラス名のみ指定している場合：ラッパーインスタンス、メソッド名が指定されている場合：メソッドの実行結果
	 */
	public static function __callStatic ($class_name, $args) {
		return (!isset(func_get_args()[1][0])) ? static::_GetInstance($class_name) : call_user_func_array([static::GetClassVar($class_name), array_shift($args)], $args);
	}

	/**
	 * コンストラクタ。
	 *
	 * @param	string	$class_name	クラス名
	 */
	protected function __construct($class_name) {
		$this->_className = $class_name;
	}

	/**
	 * メソッドを実行します。
	 *
	 * @param	string	$method_name	メソッド名
	 */
	public function __call ($method_name, $args) {
		return call_user_func_array([static::GetClassVar($this->_className), $method_name], $args);
	}
}
