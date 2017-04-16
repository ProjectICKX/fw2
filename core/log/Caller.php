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
 * @package		core
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */

namespace ickx\fw2\core\log;

use ickx\fw2\vartype\arrays\Arrays;

/**
 * 呼び出し元を取得するためのクラスです。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */
class Caller {
	const CALL_TYPE_METHOD = '->';
	const CALL_TYPE_STATIC = '::';

	/**
	 * 呼び出し元の全情報を取得します。
	 *
	 * @return	array	呼び出し元の全情報
	 */
	public static function GetInfo () {
		return static::_GetInfo();
	}

	/**
	 * 呼び出し元のファイル名を取得します。
	 *
	 * @return	string	呼び出し元のファイル名
	 */
	public static function GetFileName () {
		return static::_GetInfo()['file'];
	}

	/**
	 * 呼び出し元の行を取得します。
	 *
	 * @return	int		呼び出し元の行
	 */
	public static function GetLineNumber () {
		return static::_GetInfo()['line'];
	}

	/**
	 * 呼び出し元のオブジェクト情報を取得します。
	 *
	 * @return	string	呼び出し元のオブジェクト情報
	 */
	public static function GetObject () {
		return static::_GetInfo()['object'];
	}

	/**
	 * 呼び出し元のクラス名を取得します。
	 *
	 * @return	string	呼び出し元のクラス名
	 */
	public static function GetClassName () {
		return static::_GetInfo()['class'];
	}

	/**
	 * 呼び出し元のメソッド名を取得します。
	 *
	 * @return	string	呼び出し元のメソッド名
	 */
	public static function GetMethodName () {
		return static::_GetInfo()['function'];
	}

	/**
	 * 呼び出し元のファイル名と行番号のセットを取得します。
	 *
	 * @return	string	呼び出し元のファイル名と行番号のセット
	 */
	public static function GetFileLine () {
		return Arrays::SelectImplode('', static::_GetInfo(), ['file', 'line']);
	}

	/**
	 * 呼び出し元のクラス名とメソッド名のセットを取得します。
	 *
	 * @return	string	呼び出し元のクラス名とメソッド名のセット
	 */
	public static function GetClassMethod () {
		return Arrays::SelectImplode('', static::_GetInfo(), ['class', 'type', 'function']);
	}

	/**
	 * 呼び出し元のクラス名、メソッド名、および呼び出し方を取得します。
	 *
	 * @return	string	呼び出し元のクラス名、メソッド名、および呼び出し方
	 */
	public static function GetFileLineClassMethod () {
		$caller_info = static::_GetInfo();
		return sprintf(
			'%s(%d) %s',
			$caller_info['file'],
			$caller_info['line'],
			Arrays::SelectImplode('', $caller_info, ['class', 'type', 'function'])
		);
	}

	/**
	 * 呼び出し元情報を取得します。
	 *
	 * @return	array	呼び出し元情報
	 */
	protected static function _GetInfo () {
		return debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4)[3];
	}
}
