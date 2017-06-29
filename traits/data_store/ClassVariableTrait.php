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

use \ickx\fw2\core\exception\CoreException;
use \ickx\fw2\vartype\arrays\Arrays;

/**
 * クラス全体で共有する変数特性です。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ClassVariableTrait {
	/** @staticvar	array	共有変数リスト */
	protected static $_ClassVariableTrait_Data = [];

	/** @staticvar	array	定数化設定リスト */
	protected static $_ClassVariableTrait_ConstList = [];

	/**
	 * コールバック関数を用いて上書き禁止設定のクラス変数を設定します。
	 *
	 * 特性上、クラス変数が削除されない限り、コールバック関数は一度しか呼ばれません。
	 *
	 * @param	string		$name		クラス変数名
	 * @param	callable	$call_back	値を出力するコールバック関数
	 * @return	mixed		設定した値
	 */
	public static function LasyClassVarConstAccessCallback ($name, $call_back) {
		if (!isset(static::$_ClassVariableTrait_Data[$name])) {
			static::$_ClassVariableTrait_Data[$name] = $call_back();
			static::$_ClassVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))] = true;
		}
		return static::$_ClassVariableTrait_Data[$name];
	}

	/**
	 * コールバック関数を用いてクラス変数を設定します。
	 *
	 * 特性上、クラス変数が削除されない限り、コールバック関数は一度しか呼ばれません。
	 *
	 * @param	string		$name		クラス変数名
	 * @param	callable	$call_back	値を出力するコールバック関数
	 * @return	mixed		設定した値
	 */
	public static function LasyClassVarAccessCallback ($name, $call_back) {
		if (!isset(static::$_ClassVariableTrait_Data[$name])) {
			static::$_ClassVariableTrait_Data[$name] = $call_back();
			static::$_ClassVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))] = false;
		}
		return static::$_ClassVariableTrait_Data[$name];
	}

	/**
	 * 上書き禁止設定のクラス変数を設定します。
	 *
	 * @param	string	$name		クラス変数名
	 * @param	mixed	$value		クラス変数値
	 */
	public static function SetClassVarConst ($name, $value) {
		static::SetClassVar($name, $value, true);
	}

	/**
	 * クラス変数を設定します。
	 *
	 * @param	string	$name		クラス変数名
	 * @param	mixed	$value		クラス変数値
	 * @param	bool	$const_flag	上書き禁止設定
	 */
	public static function SetClassVar ($name, $value, $const_flag = false) {
		$name_key = implode('=>', (array) $name);
		if ($const_flag && isset(static::$_ClassVariableTrait_ConstList[$name_key])) {
			throw CoreException::RaiseSystemError('%s is constant', [$name_key]);
		}
		if (is_array($name)) {
			static::$_ClassVariableTrait_Data = Arrays::SetLowest(static::$_ClassVariableTrait_Data, $name, $value);
		} else {
			static::$_ClassVariableTrait_Data[$name] = $value;
		}

		static::$_ClassVariableTrait_ConstList[$name_key] = $const_flag;
	}

	/**
	 * クラス変数を取得します。
	 *
	 * @param	string	$name		クラス変数名
	 * @return	mixed	クラス変数値
	 */
	public static function GetClassVar ($name, $default_value = null) {
		if (is_array($name)) {
			return Arrays::GetLowest(static::$_ClassVariableTrait_Data, $name) ?: $default_value;
		}
		return isset(static::$_ClassVariableTrait_Data[$name]) ? static::$_ClassVariableTrait_Data[$name] : $default_value;
	}

	/**
	 * 全てのクラス変数を取得します。
	 *
	 * @return	array	全てのクラス変数
	 */
	public static function GetClassVarAll () {
		return static::$_ClassVariableTrait_Data;
	}

	/**
	 * クラス変数が存在するかどうか判定します。
	 *
	 * @param	string	$name		クラス変数名
	 * @return	bool	クラス変数が存在する場合はbool true, そうでない場合はfalse
	 */
	public static function HasClassVar ($name) {
		return Arrays::ExistsLowest(static::$_ClassVariableTrait_Data, $name);
	}

	/**
	 * クラス変数を削除します。
	 *
	 * @param	string	$name	クラス変数名
	 */
	public static function RemoveClassVar ($name) {
		$name = Arrays::AdjustArray($name);
		if (Arrays::AdjustValue(static::$_ClassVariableTrait_ConstList, implode('=>', Arrays::AdjustArray($name)))) {
			throw CoreException::RaiseSystemError('%s is constant', [implode(' => ', $name)]);
		}
		static::$_ClassVariableTrait_Data = Arrays::RemoveLowest(static::$_ClassVariableTrait_Data, $name);
		unset(static::$_ClassVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))]);
	}
}
