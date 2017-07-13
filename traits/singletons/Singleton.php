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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\singletons;

/**
 * Flywheel2 Singleton trait
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait Singleton {
	/**
	 * @var array	シングルトンインスタンス保持変数
	 */
	protected static $singletonInstance = [];

	/**
	 * シングルトンとして管理されているオブジェクトインスタンスを返します。
	 *
	 * @param	array	...$args	コンストラクタ引数
	 * @return	object	シングルトンとして管理されているオブジェクトインスタンス
	 */
	public static function getInstance(...$args) {
		return static::$singletonInstance[static::class] ?? static::$singletonInstance[static::class] = new static(...$args);
	}
}
