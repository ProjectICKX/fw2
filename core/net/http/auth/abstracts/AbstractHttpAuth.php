<?php
/**  ______ _				 _		       _ ___
 *  |  ____| |		       | |		     | |__ \
 *  | |__  | |_   ___      __| |__   ___  ___| |  ) |
 *  |  __| | | | | \ \ /\ / /| '_ \ / _ \/ _ \ | / /
 *  | |    | | |_| |\ V  V / | | | |  __/  __/ |/ /_
 *  |_|    |_|\__, | \_/\_/  |_| |_|\___|\___|_|____|
 *		     __/ |
 *		    |___/
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

namespace ickx\fw2\core\net\http\auth\abstracts;

/**
 * Http Digest Auth Class
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class AbstractHttpAuth {
	/**
	 * 認証処理を一括して行います。
	 *
	 * @param	array		$authenticate
	 * @param	callback	$authorize
	 * @return boolean
	 */
	public static function Auth () {
		if (static::IsInitial() || !static::Authorize($authorize, $authenticate)) {
			http_response_code(401);
			header(sprintf('WWW-Authenticate: %s %s', static::AUTH_TYPE, static::ImplodeAuthenticate($authenticate, true)));
			return false;
		}
		return true;
	}

	/**
	 * Authenticateパラメータを構築しかえします。
	 *
	 * @param	array	$authenticate
	 * @return	string	Authenticateパラメータ
	 */
	public static function ImplodeAuthenticate ($authenticate) {
		$algorithm = $authenticate['algorithm'] ?? null;
		if (isset($authenticate['algorithm'])) {
			unset($authenticate['algorithm']);
		}

		$result = [];
		foreach ($authenticate as $key => $value) {
			$result[] = sprintf('%s="%s"', htmlspecialchars($key, \ENT_QUOTES, 'UTF-8'), htmlspecialchars($value, \ENT_QUOTES, 'UTF-8'));
		}

		if (!is_null($algorithm)) {
			$result[] = sprintf('algorithm=%s', htmlspecialchars($algorithm, \ENT_QUOTES, 'UTF-8'));
		}

		return implode(',', $result);
	}
}
