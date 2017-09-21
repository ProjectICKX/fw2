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
		$name = implode('<>', (array) ($name ?? static::$multitonDefaultName));
		$instance = (static::$multitonInstances[static::class][$name] ?? static::$multitonInstances[static::class][$name] = new static(...$args));
		$instance->multitonName = $name;
		return $instance;
	}

	/**
	 * マルチトンとしてインスタンスを再作成し、保持します。
	 *
	 * @param	string	$name		インスタンス名
	 * @param	array	...$args	コンストラクタ引数
	 * @return	object	マルチトンとして管理されているオブジェクトインスタンス
	 */
	public static function rebuild ($name = null, ...$args) {
		$name = implode('<>', (array) ($name ?? static::$multitonDefaultName));
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

	/**
	 * 現在保持するマルチトンインスタンスを返します。
	 *
	 * @return	array	現在保持するマルチトンインスタンス
	 */
	public static function getInstances () {
		return static::$multitonInstances[static::class];
	}

	/**
	 * インスタンス単位での入眠処理
	 *
	 * @return	array	シリアライズ対象となるプロパティ名の配列
	 */
	public function __sleep () {
		$this->__seleep_temp	= static::$multitonInstances[static::class];
		return array_keys(get_object_vars($this));
	}

	/**
	 * インスタンス単位での起床処理
	 */
	public function __wakeup () {
		static::$multitonInstances[static::class]	= $this->__seleep_temp;
		unset($this->__seleep_temp);
	}

	/**
	 * マルチトン全体のシリアライザ
	 *
	 * @return	string	シリアル化されたマルチトン
	 */
	public static function serialize () {
		$data = [];
		foreach (static::$multitonInstances as $name => $instance) {
			$data[$name] = serialize($instance);
		}
		return serialize($data);
	}

	/**
	 * マルチトン全体のアンシリアライザ
	 *
	 * @param	string	$data	シリアル化されたマルチトン
	 */
	public static function unserialize ($data) {
		foreach (unserialize($data) ?? [] as $name => $datum) {
			static::$multitonInstances[$name] = unserialize($datum);
		}
	}
}
