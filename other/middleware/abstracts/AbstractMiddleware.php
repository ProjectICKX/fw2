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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\middleware\abstracts;

use ickx\fw2\other\middleware\interfaces\IMiddleware;

/**
 * ミドルウェアを実現するための基底クラスを定義します。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		1.0.0
 */
abstract class AbstractsMiddleware implements IMiddleware {
	/**
	 * ミドルウェアを初期化し返します。
	 *
	 * @return	Middleware	自身のインスタンス
	 */
	public static function init () {
		return new static;
	}

	/**
	 * invoke
	 *
	 * @param	MiddlewareRequest	$request
	 * @param	MiddlewareResponse	$response
	 * @param	Middleware			$next
	 * @return	mixed				ミドルウェアの実行結果
	 */
	public function __invoke($request, $response, $next) {
		return $next($request, $response);
	}
}
