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

namespace ickx\fw2\mvc\app\controllers\traits;

/**
 * Flywheel2 Controller向けRedirect特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait RedirecterTrait {
	/**
	 * リダイレクトを設定します。
	 *
	 * @param	string	$url		リダイレクト先URL
	 * @param	array	$parameter	オプションパラメータ
	 */
	public function redirect ($url, $parameter = []) {
		$this->forceNext($url, static::NEXT_REDIRECT);
	}

	/**
	 * リダイレクトが設定されているかどうかを返します。
	 *
	 * @return	bool	リダイレクトが設定されている場合はtrue, そうでない場合はfalse
	 */
	public function isRedirect () {
		return ($this->nextRule === static::NEXT_REDIRECT && $this->nextUrl !== null);
	}
}
