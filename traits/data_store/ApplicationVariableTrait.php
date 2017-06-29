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
 * アプリケーション全体で共有する変数特性です。
 *
 * 2012年現在のPHPの特性上、1リクエスト中の処理全体での変数の共有となります。
 * リクエストを跨ぐ永続化された変数の共有は行われません。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ApplicationVariableTrait {
	/** @staticvar	array	共有変数リスト */
	protected static $_ApplicationVariableTrait_Data = [];

	/** @staticvar	array	定数化設定リスト */
	protected static $_ApplicationVariableTrait_ConstList = [];

	/**
	 * コールバック関数を用いて上書き禁止設定のアプリケーション変数を設定します。
	 *
	 * 特性上、アプリケーション変数が削除されない限り、コールバック関数は一度しか呼ばれません。
	 *
	 * @param	string		$name		アプリケーション変数名
	 * @param	callable	$call_back	値を出力するコールバック関数
	 * @return	mixed		設定した値
	 */
	public static function LasyClassVarConstAccessCallback ($name, $call_back) {
		if (!static::HasClassVar($name)) {
			static::$_ApplicationVariableTrait_Data[$name] = $call_back();
			static::$_ApplicationVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))] = true;
		}
		return static::$_ApplicationVariableTrait_Data[$name];
	}

	/**
	 * コールバック関数を用いてアプリケーション変数を設定します。
	 *
	 * 特性上、アプリケーション変数が削除されない限り、コールバック関数は一度しか呼ばれません。
	 *
	 * @param	string		$name		アプリケーション変数名
	 * @param	callable	$call_back	値を出力するコールバック関数
	 * @return	mixed		設定した値
	 */
	public static function LasyClassVarAccessCallback ($name, $call_back) {
		if (!static::HasClassVar($name)) {
			static::$_ApplicationVariableTrait_Data[$name] = $call_back();
			static::$_ApplicationVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))] = false;
		}
		return static::$_ApplicationVariableTrait_Data[$name];
	}

	/**
	 * 上書き禁止設定のアプリケーション変数を設定します。
	 *
	 * @param	string	$name		アプリケーション変数名
	 * @param	mixed	$value		アプリケーション変数値
	 */
	public static function SetClassVarConst ($name, $value) {
		static::Set($name, $value, true);
	}

	/**
	 * アプリケーション変数を設定します。
	 *
	 * @param	string	$name		アプリケーション変数名
	 * @param	mixed	$value		アプリケーション変数値
	 * @param	bool	$const_flag	上書き禁止設定
	 */
	public static function SetClassVar ($name, $value, $const_flag = false) {
		$name = Arrays::AdjustArray($name);
		if ($const_flag && Arrays::AdjustValue(static::$_ApplicationVariableTrait_ConstList, implode('=>', Arrays::AdjustArray($name)))) {
			throw CoreException::RaiseSystemError('%s is constant', [implode(' => ', $name)]);
		}
		static::$_ApplicationVariableTrait_Data = Arrays::SetLowest(static::$_ApplicationVariableTrait_Data, $name, $value);
		static::$_ApplicationVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))] = $const_flag;
	}

	/**
	 * アプリケーション変数を取得します。
	 *
	 * @param	string	$name		アプリケーション変数名
	 * @return	mixed	アプリケーション変数値
	 */
	public static function GetClassVar ($name, $default_value = null) {
		return (static::HasClassVar($name)) ? Arrays::GetLowest(static::$_ApplicationVariableTrait_Data, $name) : $default_value;
	}

	/**
	 * 全てのアプリケーション変数を取得します。
	 *
	 * @return	array	全てのアプリケーション変数
	 */
	public static function GetClassVarAll () {
		return static::$_ApplicationVariableTrait_Data;
	}

	/**
	 * アプリケーション変数が存在するかどうか判定します。
	 *
	 * @param	string	$name		アプリケーション変数名
	 * @return	bool	アプリケーション変数が存在する場合はbool true, そうでない場合はfalse
	 */
	public static function HasClassVar ($name) {
		return Arrays::ExistsLowest(static::$_ApplicationVariableTrait_Data, $name);
	}

	/**
	 * アプリケーション変数を削除します。
	 *
	 * @param	string	$name	アプリケーション変数名
	 */
	public static function RemoveClassVar ($name) {
		$name = Arrays::AdjustArray($name);
		if (Arrays::AdjustValue(static::$_ApplicationVariableTrait_ConstList, implode('=>', Arrays::AdjustArray($name)))) {
			throw CoreException::RaiseSystemError('%s is constant', [implode(' => ', $name)]);
		}
		static::$_ApplicationVariableTrait_Data = Arrays::RemoveLowest(static::$_ApplicationVariableTrait_Data, $name);
		unset(static::$_ApplicationVariableTrait_ConstList[implode('=>', Arrays::AdjustArray($name))]);
	}
}
