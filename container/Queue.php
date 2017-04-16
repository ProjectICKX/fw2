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
 * 簡易キューコンテナ
 *
 * @category	Flywheel2
 * @package		container
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Queue {
	/**
	 * @property	\ArrayObject	キュー保持用配列
	 * @static
	 */
	private static $_queueList = null;

	/**
	 * キューを初期化します。
	 */
	public static function Init ($queue = null, $name = null) {
		static::$_queueList = null;
		if ($queue !== null) {
			$name = $name ?: \ickx\fw2\core\log\Caller::GetClassMethod();
			static::Add($queue, $name);
		}
	}

	/**
	 * キューを追加します。
	 *
	 * @static
	 * @param	mixed	$queue	追加するキュー
	 * @param	string	$name	キュー名 省略した場合は呼び出し元クラス名+メソッド名
	 */
	public static function Add  ($queue, $name = null) {
		$name = $name ?: \ickx\fw2\core\log\Caller::GetClassMethod();
		if (!isset(static::$_queueList[$name])) {
			static::$_queueList[$name] = new \ArrayObject();
		}
		static::$_queueList[$name][] = $queue;
	}

	/**
	 * イテレータを返します。
	 *
	 * @static
	 * @param	string			$name	キュー名 省略した場合は呼び出し元クラス名+メソッド名
	 * @return	\ArrayIterator	イテレータ
	 */
	public static function GetIterator ($name = null) {
		$name = $name ?: \ickx\fw2\core\log\Caller::GetClassMethod();
		if (!isset(static::$_queueList[$name])) {
			static::$_queueList[$name] = new \ArrayObject();
		}
		return static::$_queueList[$name]->getIterator();
	}
}
