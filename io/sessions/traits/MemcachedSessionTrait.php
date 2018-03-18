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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\sessions\traits;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\auth\data_store\Memcached;

/**
 * FilesSessionTrait
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait MemcachedSessionTrait {
	//==============================================
	// Memcached
	//==============================================
	/**
	 * セッションセーブハンドラがmemcachedの場合の初期処理を行います。
	 */
	public static function MemcachedInit () {
		return static::MemcachedDetectiveSessionFixation();
	}

	/**
	 * セッションセーブハンドラがmemcachedの場合のセッションフィクセーション検知を行います。
	 *
	 * @return	bool	セッションフィクセーションを検知した場合はtrue そうでない場合はfalse
	 */
	public static function MemcachedDetectiveSessionFixation () {
		//==============================================
		//ファイル：セッションフィクセーション対策
		//==============================================
		//セッション取得元検証
		$session_name = session_name();
		if (isset($_POST[$session_name]) || isset($_GET[$session_name])) {
			throw CoreException::RaiseSystemError('クッキー以外の経路でセッションを指定されました。session_id:%s', [$session_id]);
		}

		//セッションIDがあった場合の検証
		$session_id = (isset($_COOKIE[$session_name])) ? $_COOKIE[$session_name] : session_id();
		if ($session_id !== '') {
			//不正な文字列がないか検証
			if (strpos($session_id, '.') !== false || strpos($session_id, '/') !== false || strpos($session_id, "\\") !== false) {
				throw CoreException::RaiseSystemError('セッションIDに不正な文字列が存在します。session_id:%s', [$session_id]);
			}
		}

		//==============================================
		//処理の終了
		//==============================================
		return true;
	}
}
