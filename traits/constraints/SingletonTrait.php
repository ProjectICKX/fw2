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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\constraints;

/**
 * 強制的にシングルトンクラスにする特性です。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait SingletonTrait {
 	/**
 	 * コンストラクタ
 	 */
	protected function __construct () {}

	/**
	 * インスタンスを返します。
	 *
	 * @return	Object	インスタンス
	 */
	public static function GetInstance () {
		static $_this = null;
		return $_this ?: $_this = new static;
	}

	/**
	 * オブジェクトをクローニングします。
	 *
	 * @throws \RuntimeException
	 */
	protected function __clone  () {
		throw new \RuntimeException('Can not clone this instance.');
	}
}
