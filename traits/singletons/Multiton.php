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
 * Flywheel2 Multiton trait
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait Multiton {
	/**
	 * @var string	マルチトンインスタンスデフォルト名
	 * @static
	 */
	protected static $multitonDefaultName = ':default:';

	/**
	 * @var array	マルチトンインスタンス保持変数
	 */
	protected static $multitonInstances	= [];

	/**
	 * @var	string	現在のインスタンス名
	 */
	protected $multitonName	= null;

	/**
	 * マルチトンとしてインスタンスを作成し、保持します。
	 *
	 * @param	string	$name		インスタンス名
	 * @param	array	...$args	コンストラクタ引数
	 * @return	object	マルチトンとして管理されているオブジェクトインスタンス
	 */
	public static function init ($name = null, ...$args) {
		$name = $name ?? static::$multitonDefaultName;
		$instance = static::$multitonInstances[static::class][$name] = new static(...$args);
		$instance->multitonName = $name;
		return $instance;
	}

	/**
	 * マルチトンとして管理されているオブジェクトインスタンスを返します。
	 *
	 * @param	array	...$args	任意個数の引数
	 * @return	object	マルチトンとして管理されているオブジェクトインスタンス
	 */
	public static function getInstance ($name = null) {
		return static::$multitonInstances[static::class][$name ?? static::$multitonDefaultName];
	}
}
