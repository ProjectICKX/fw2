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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\cache\memcached;

use ickx\fw2\io\cache\Cache;

/**
 * キャッシュクラス：Memcached
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class MemcachedCache extends \ickx\fw2\io\cache\abstracts\AbstractCache {
	/**
	 * @var	string	ストレージタイプ
	 */
	public const STORAGE_TYPE	= Cache::STORAGE_TYPE_MEMCACHED;

	protected $memcached	= null;

	/**
	 * コンストラクタ。
	 *
	 * @param	string	$name		キャッシュグループ名
	 * @param	array	...$args	初期化引数
	 */
	protected function __construct ($name, ...$args) {
		$this->memcached = new \Memcached();
		$this->memcached->addServer($args[0], $args[1]);
		if ($this->memcached->getVersion() === false) {
			throw new \ErrorException(sprintf('Memcachedとの接続に失敗しました。host name:%s, port:%s', $args[0], $args[1]));
		}
	}

	/**
	 * 引数で与えた名前のキャッシュが存在するか確認します。
	 *
	 * @param	mixed	$name	キャッシュ名
	 * @return	bool	引数で与えた名前のキャッシュが存在する場合はtrue、そうでない場合はfalse
	 */
	public function has ($name) {
		$this->memcached->get($this->groupName .'<>'. $name);
		return $this->memcached->getResultCode() ===  \Memcached::RES_NOTFOUND;
	}

	/**
	 * キャッシュした値を取得します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	mixed	キャッシュした値
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::get()
	 */
	public function get ($name) {
		return $this->memcached->get($this->groupName .'<>'. $name);
	}

	/**
	 * キャッシュした値を全て取得します。
	 *
	 * @return	array	キャッシュした値の全て
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::gets()
	 */
	public function gets () {
		$all_keys	= $this->memcached->getAllKeys();
		$group_name	= $this->groupName .'<>';
		$group_name_length	= mb_strlen($group_name);
		foreach ($all_keys as $idx => $key) {
			if (mb_substr($key, 0, $group_name_length) !== $group_name) {
				unset($all_keys[$idx]);
			}
		}
		return $this->memcached->getMulti($all_keys);
	}

	/**
	 * 値をキャッシュします。
	 *
	 * @param	string	$name	キャッシュ名
	 * @param	mixed	$value	キャッシュする値
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\memcached\MemcachedCache	MemcachedCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::set()
	 */
	public function set ($name, $value, $ttl = self::DEFAULT_TTL) {
		$this->memcached->set($this->groupName .'<>'. $name, $value, $ttl);
		return $this;
	}

	/**
	 * 値を纏めてキャッシュします。
	 *
	 * @param	array	$sets	[['キャッシュ名' => キャッシュする値], ...]形式のキャッシュ名とキャッシュする値のペアを持つ配列
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\memcached\MemcachedCache	MemcachedCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::sets()
	 */
	public function sets ($sets, $ttl = self::DEFAULT_TTL) {
		$tmp = [];
		foreach ($sets as $name => $value) {
			$tmp[$this->groupName .'<>'. $name] = $value;
		}
		$this->memcached->setMulti($tmp, $ttl);
		return $this;
	}

	/**
	 * キャッシュ名の値をキャッシュから破棄します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	\ickx\fw2\io\cache\memcached\MemcachedCache	MemcachedCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::remove()
	 */
	public function remove ($name) {
		$this->memcached->delete($this->groupName .'<>'. $name, 0);
		return $this;
	}

	/**
	 * 現在キャッシュされている全ての値を破棄します。
	 *
	 * @return	\ickx\fw2\io\cache\memcached\MemcachedCache	MemcachedCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::clear()
	 */
	public function clear () {
		$all_keys	= $this->memcached->getAllKeys();
		$group_name	= $this->groupName .'<>';
		$group_name_length	= mb_strlen($group_name);
		foreach ($all_keys as $idx => $key) {
			if (mb_substr($key, 0, $group_name_length) !== $group_name) {
				unset($all_keys[$idx]);
			}
		}
		$this->memcached->deleteMulti($all_keys);
		return $this;
	}
}
