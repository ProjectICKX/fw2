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
 * @package		crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\csprng;

/**
 * OpenSSLを扱うクラスです。
 *
 * @category	Flywheel2
 * @package		Crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Random {
	const DEFAULT_MIN		= 0;
	const DEFAULT_MAX		= 100;
	const DEFAULT_LENGTH	= 32;

	public static function Bytes ($length = null) {
		return random_bytes($length ?? static::DEFAULT_LENGTH);
	}

	public static function Int ($min = null, $max = null) {
		return random_int($min ?? static::DEFAULT_MIN, $max ?? static::DEFAULT_MAX);
	}
}
