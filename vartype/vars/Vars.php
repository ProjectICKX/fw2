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
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\vartype\vars;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;

/**
 * 変数ユーティリティクラスです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Vars {
	/**
	 * 値が空の場合にデフォルト値を返します。
	 *
	 * @param	mixed	$var			値
	 * @param	mixed	$default_value	デフォルト値
	 * @return	mixed	値が空ではない場合は値、そうでない場合はデフォルト値
	 */
	public static function Adjust ($var, $default_value) {
		return $var ?: $default_value;
	}

	//==============================================
	//Validations
	//==============================================
	/**
	 * 与えられた値がNULLかどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsNull ($value, $options = []) {
		if ($value === null) {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * 与えられた値が文字列として空かどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsStringEmpty ($value, $options = []) {
		if ($value === '') {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * 与えられた値が文字列として空かどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsNotStringEmpty ($value, $options = []) {
		if ($value !== '') {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * 与えられた値が空かどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsEmpty ($value, $options = []) {
		if (empty($value)) {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * 与えられた値が数値のみで構成されているかどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'decimal'         => bool   10進数表記(二桁目以上は1以上から始まる)かどうか視るかどうか。
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsNumber ($value, $options = []) {
		$prefix = (Arrays::AdjustValue($options, 'decimal')) ? "[1-9]" : '';
		if (preg_match("/^". $prefix ."[0-9]+$/", $value)) {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * 与えられた値が数値のみで構成されているかどうか検証します。
	 *
	 * @param 		mixed			$value		検証する値
	 * @param 		array			$options	オプション
	 * [
	 *     'decimal'         => bool   10進数表記(二桁目以上は1以上から始まる)かどうか視るかどうか。
	 *     'message'         => string 例外用メッセージフォーマット。sprintfに準拠。1個目の要素は自動的に第一引数の$valueとなる。
	 *     'message_options' => array  例外用メッセージに追加したいメッセージの配列。
	 * ]
	 * @return		bool			検証に成功した場合はtrue、そうでない場合はfalse
	 * @exception	CoreException	$optionsにmessageが指定されていて検証に失敗した場合、CoreExceptionをthrowする。
	 */
	public static function IsNotNumber ($value, $options = []) {
		$prefix = (Arrays::AdjustValue($options, 'decimal')) ? "[1-9]" : '';
		if (!preg_match("/^". $prefix ."[0-9]+$/", $value)) {
			return true;
		}
		if (isset($options['message'])) {
			throw CoreException::RaiseSystemError($options['message'], static::_MergeMessageVars($value, $options));
		}
		return false;
	}

	/**
	 * メッセージをマージします。
	 *
	 * @param	mixed	$value		マージ元となるメッセージ
	 * @param	array	$options	オプション
	 * @return	string	マージされた後のオプション
	 */
	protected static function _MergeMessageVars ($value, $options) {
		if (is_array($value)) {
			$value = implode(', ', $value);
		} else if (is_null($value)) {
			$value = '';
		}
		$value = [$value];
		if (isset($options['message_options'])) {
			return array_merge($value, $options['message_options']);
		}
		return $value;
	}
}
