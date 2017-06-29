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

namespace ickx\fw2\core\net\http\auth;

use ickx\fw2\core\net\http\Http;

/**
 * Http Digest Auth Class
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class BasicAuth {
	const AUTH_TYPE	= 'Basic';

	public static function Auth ($authenticate, $password_func) {
		if (static::IsInitial() || !$user_name = static::Authorize($password_func)) {
			http_response_code(401);
			header(sprintf('WWW-Authenticate: %s %s', static::AUTH_TYPE, static::ImplodeAuthenticate($authenticate, true)));
			return false;
		}
		return $user_name;
	}

	public static function ImplodeAuthenticate ($authenticate, $implode = false) {
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

		return $implode === true ? implode(',', $result) : $result;
	}

	public static function IsInitial () {
		return !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	}

	public static function Authorize ($password_func) {
		return password_verify($_SERVER['PHP_AUTH_PW'], $password_func($_SERVER['PHP_AUTH_USER']) ?? password_hash(openssl_random_pseudo_bytes(30), \PASSWORD_DEFAULT)) ? $_SERVER['PHP_AUTH_USER'] : false;
	}
}
