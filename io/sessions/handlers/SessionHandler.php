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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\sessions\handlers;

/**
 * SessionHandler
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class SessionHandler extends \SessionHandler {
	/**
	 * 独自のセッションハンドラを設定します。
	 *
	 * @param	string	$handler_class_name	Sessionハンドラとして設定するクラス名、またはオブジェクト
	 * @param	bool	$register_shutdown	session_register_shutdownを登録するかどうか
	 * @return	bool	セッションハンドラの設定に成功した場合はTRUE 失敗した場合はFALSE
	 */
	public static function SetHandler ($handler_class = null, $register_shutdown = false) {
		$handler_class = $handler_class ?: static::class;
		if (is_string($handler_class)) {
			$handler_class = new $handler_class;
		}
		$ret = session_set_save_handler(new \SessionHandler, $register_shutdown);
		return $ret;
	}
}
