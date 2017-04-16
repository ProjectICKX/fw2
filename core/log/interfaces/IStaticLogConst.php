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
 * @package		date_time
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\log\interfaces;

/**
 * システム全体で利用するロガーインターフェース。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IStaticLogConst {
	const DEFAULT_PASSWORD			= 'zAl+j1/&3PjiB_?9uVFOUUX0Ji,p:ef=';
	const DEFAULT_SALT				= 'q|G7eoGJ.LY]kMov*5Z|x;%$Xrw4NCi(';
	const DEFAULT_HMAC_KEY			= '*1Xroo{px3wR0VI.zz@U\6`G|:N_2`B.';
	const DEFAULT_SECRET_KEY_LENGTH	= 8;
	const DEFAULT_HASH_ALGORITHM	= 'sha256';
}
