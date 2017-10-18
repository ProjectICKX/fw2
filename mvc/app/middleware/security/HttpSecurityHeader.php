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
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mvc\app\middleware\exception;

use ickx\fw2\other\middleware\abstracts\AbstractsMiddleware;

/**
 * HTTP用セキュアヘッダ出力を強制するミドルウェアです。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class HttpSecurityHeader extends AbstractsMiddleware {
	public function __invoke ($request, $response, $next) {
		$secure_header_list = [
			'X-Content-Type-Options: nosniff',
			'X-XSS-Protection: 1; mode=block',
			'X-Download-Options: noopen',
			'X-Frame-Options: SAMEORIGIN',
			'X-Permitted-Cross-Domain-Policies: master-only',
//			'Content-Security-Policy: default-src \'self\'',
			'Strict-Transport-Security: max-age=31536000; includeSubDomains',
			'Referrer-Policy: origin',
		];

		foreach ($secure_header_list as $secure_header) {
			header($secure_header);
		}

		return $next($request, $response);
	}
}
