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

namespace ickx\fw2\core\net\http\traits;

/**
 * HTTP特性。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait HttpTrait {
	/**
	 * スクリプトが実行されているサーバのドメイン名を返します。
	 *
	 * ！！注意！！
	 * バーチャルホスト上で実行されている場合は、バーチャルホスト名が返ります。
	 *
	 * Tips
	 * HOST_NAMEではなくSERVER_NAMEとしているのは、Http RequestのHostヘッダを書き換える事でHOST_NAMEを変更できてしまうためです。
	 *
	 * @return	string	現在のサーバのドメイン名
	 */
	public static function GetDomainName () {
		if (mb_substr($_SERVER['SERVER_SOFTWARE'], 0, 6, 'UTF-8') === 'nginx/') {
			if (mb_substr($_SERVER['SERVER_NAME'], 0, 1, 'UTF-8') === '~') {
				if (preg_match(sprintf('@%s@', mb_substr($_SERVER['SERVER_NAME'], 1, null, 'UTF-8')), $_SERVER['HTTP_HOST'], $mat) === false || $mat[0] !== $_SERVER['HTTP_HOST']) {
					throw new \Exception(sprintf('HTTP HOSTインジェクションの疑いがあります。http host:%s', $_SERVER['HTTP_HOST']));
				}
				return $mat[0];
			}
		}

		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
	}

	/**
	 * 現在接続しているクライアントがIE5.5かどうかを返します。
	 *
	 * @return	boolean 現在接続しているクライアントがIE5.5の場合はboolean TRUE、そうでない場合はboolean FALSE
	 */
	public static function IsCurrentUserAgentIe5dot5 () {
		return (preg_match('/compatible; MSIE 5\.5/', $_SERVER['HTTP_USER_AGENT']) == 1);
	}

	/**
	 * 現在接続しているクライアントがIEかどうかを返します。
	 *
	 * @return	boolean	現在接続しているクライアントがIEの場合はboolean TRUE、そうでない場合はboolean FALSE
	 */
	public static function IsCurrentUserAgentIe () {
		return (preg_match('/compatible; MSIE [1-9][0-9]*\.(?:[1-9][0-9]|0)*/', $_SERVER['HTTP_USER_AGENT']) == 1);
	}

	/**
	 * 現在接続しているクライアントがFirefoxかどうかを返します。
	 *
	 * @return	boolean 現在接続しているクライアントがFirefoxの場合はboolean TRUE、そうでない場合はboolean FALSE
	 */
	public static function IsCurrentUserAgentFirefox () {
		return (preg_match('/Firefox/', $_SERVER['HTTP_USER_AGENT']) == 1);
	}
}
