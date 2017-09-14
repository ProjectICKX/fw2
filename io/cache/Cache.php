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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\cache;

use ickx\fw2\io\cache\apcu\ApcuCache;
use ickx\fw2\io\cache\class_var\ClassVarCache;
use ickx\fw2\io\cache\files\FilesCache;
use ickx\fw2\io\cache\memcached\MemcachedCache;

/**
 * 共通インターフェースによるキャッシュ機能を提供します。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Cache {
	/**
	 * @var	string	キャッシュストレージタイプ：クラス変数
	 */
	public const STORAGE_TYPE_CLASS_VAR	= 'class_var';

	/**
	 * @var	string	キャッシュストレージタイプ：ファイル
	 */
	public const STORAGE_TYPE_FILES		= 'files';

	/**
	 * @var	string	キャッシュストレージタイプ：memcached
	 */
	public const STORAGE_TYPE_MEMCACHED	= 'memcached';

	/**
	 * @var	string	キャッシュストレージタイプ：APCu
	 */
	public const STORAGE_TYPE_APCU		= 'apcu';

	/**
	 * @var	array	キャッシュストレージクラスリスト
	 */
	public const STORAGE_CLASS_LIST	= [
		self::STORAGE_TYPE_CLASS_VAR	=> ClassVarCache::class,
		self::STORAGE_TYPE_FILES		=> FilesCache::class,
		self::STORAGE_TYPE_MEMCACHED	=> MemcachedCache::class,
		self::STORAGE_TYPE_APCU			=> ApcuCache::class,
	];

	/**
	 * @var	string	デフォルトのキャッシュ名
	 */
	public const DEFAULT_NAME			= 'default';

	/**
	 * @var	array	キャッシュリスト
	 */
	protected static $cacheList	= [];

	/**
	 * @var	string	デフォルトのキャッシュストレージタイプ
	 */
	public static $defaultStorageTypr	= null;

	/**
	 * キャッシュを初期化し、ストレージインスタンスを返します。
	 *
	 * @param	string	$name		キャッシュ名
	 * @param	string	$type		キャッシュストレージタイプ
	 * @param	array	...$args	キャッシュストレージコンストラクタ引数
	 * @return	\ickx\fw2\io\cache\abstracts\AbstractCache	AbstractCacheを継承したクラスのインスタンス
	 */
	public static function init ($name = self::DEFAULT_NAME, $type = null, ...$args) {
		!is_array($name) ?: $name = implode('<>', $name);
		$cacheClass = static::STORAGE_CLASS_LIST[$type ?? static::getLazyStorageType()];
		return static::$cacheList[$name] = $cacheClass::init($name, ...$args);
	}

	/**
	 * 静的メソッド名にキャッシュ名を指定して呼び出した場合のマジックメソッドです。
	 *
	 * @param	string	$name		キャッシュ名
	 * @param	array	$arguments	メソッド引数
	 * @return	NULL|\ickx\fw2\io\cache\abstracts\AbstractCache	AbstractCacheを継承したクラスのインスタンス
	 */
	public static function __callStatic ($name, $arguments) {
		return static::$cacheList[$name] ?? null;
	}

	/**
	 * キャッシュ名に紐づくストレージインスタンスを返します。
	 *
	 * @param	string	$name		キャッシュ名
	 * @param	array	$arguments	メソッド引数
	 * @return	NULL|\ickx\fw2\io\cache\abstracts\AbstractCache	AbstractCacheを継承したクラスのインスタンス
	 */
	public static function get ($name) {
		return static::$cacheList[$name] ?? null;
	}

	/**
	 * キャッシュストレージタイプを指定しなかった場合のキャッシュストレージタイプを返します。
	 *
	 * @return	string	キャッシュストレージタイプ
	 */
	public static function getLazyStorageType () {
		static $enable_apcu;
		return static::$defaultStorageTypr ?? ($enable_apcu ?? $enable_apcu = function_exists('apc_store')) ? static::STORAGE_TYPE_APCU : static::STORAGE_TYPE_FILES;
	}

	/**
	 * ストレージタイプのデフォルトを設定・取得します。
	 *
	 * @param	array	...$args	第一引数が指定されている場合は、デフォルトのストレージタイプを変更する そうでない場合は現在のデフォルトのストレージタイプを返す。
	 * @return	string	このクラス名
	 */
	public static function defaultStorageType (...$args) {
		if (empty($args)) {
			return static::$defaultStorageTypr;
		}

		static::$defaultStorageTypr = $args[0];

		return static::class;
	}
}
