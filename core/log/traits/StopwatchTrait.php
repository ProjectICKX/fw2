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
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\log\traits;

/**
 * ストップウォッチ特性です。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait StopwatchTrait {
	/**
	 * @staticvar	array	ストップウォッチログ
	 */
	protected static $_stopwatchLog = null;

	/**
	 * ストップウォッチログの取得を開始します。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	float	ストップウォッチログ取得開始時のマイクロタイム
	 */
	public static function Start ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		return static::$_stopwatchLog[$name]['start'] = microtime(true);
	}

	/**
	 * Splitを取ります。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	float	Split時のマイクロタイム
	 */
	public static function Split ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		return static::$_stopwatchLog[$name]['split'][] = microtime(true);
	}

	/**
	 * ストップウォッチを停止します。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	float	停止時のマイクロタイム
	 */
	public static function Stop ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		return static::$_stopwatchLog[$name]['stop'] = microtime(true);
	}

	/**
	 * ストップウォッチログを取得します。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	array	開始時間、split、終了時間の全ログ
	 */
	public static function GetLog ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		return static::_ParseTime($name);
	}

	/**
	 * 開始時間とSplitとの差分を取ります。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	float	開始時間とSplitとの差分
	 */
	public static function SplitDiff ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		return static::Split($name) - static::$_stopwatchLog[$name]['start'];
	}

	/**
	 * 開始時間と終了時間との差分をとります。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	float	開始時間と終了時間との差分
	 */
	public static function Diff ($name = null) {
		$name = (is_null($name)) ? \ickx\fw2\core\log\Caller::GetClassMethod() : $name;
		$log = static::GetLog($name);
		if (is_null($log) || !isset($log['start']) || !isset($log['stop'])) {
			return null;
		}
		return $log['stop'] - $log['start'];
	}

	/**
	 * 取得したログを整形して返します。
	 *
	 * @param	string	$name	ストップウォッチ名 デフォルトでは呼び出し元クラス名
	 * @return	array	取得したログ
	 */
	protected static function _ParseTime ($name) {
		if (!isset(static::$_stopwatchLog[$name])) {
			return null;
		}
		$log = static::$_stopwatchLog[$name];
		$ret = array();
		if (isset($log['start'])) {
			$ret['start'] = sprintf('%F', $log['start']);
		}
		if (isset($log['split']) && is_array($log['split'])) {
			foreach ($log['split'] as $split) {
				$ret['split'][] = sprintf('%F', $split);
			}
		}
		if (isset($log['stop'])) {
			$ret['stop'] = sprintf('%F', $log['stop']);
		}
		return $ret;
	}
}
