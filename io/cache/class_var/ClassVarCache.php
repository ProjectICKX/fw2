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

namespace ickx\fw2\io\cache\class_var;

use ickx\fw2\io\cache\Cache;

/**
 * キャッシュクラス：クラス変数
 *
 * ！！注意！！
 * 同一リクエスト内でのみ有効なキャッシュです。
 * 永続化を企図したキャッシュは他のキャッシュを利用してください。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ClassVarCache extends \ickx\fw2\io\cache\abstracts\AbstractCache {
	/**
	 * @var	string	ストレージタイプ
	 */
	public const STORAGE_TYPE	= Cache::STORAGE_TYPE_CLASS_VAR;

	protected $cacheList	= [];
	protected $cacheExpireList	= [];

	/**
	 * キャッシュした値を取得します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	mixed	キャッシュした値
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::get()
	 */
	public function get ($name) {
		if (!isset($this->cacheExpireList[$name])) {
			$this->state = false;
			return null;
		}

		if ($this->cacheExpireList[$name] < time()) {
			unset($this->cacheList[$name]);
			unset($this->cacheExpireList[$name]);

			$this->state = false;
			return null;
		}

		$this->state = true;
		return $this->cacheList[$name];
	}

	/**
	 * キャッシュした値を全て取得します。
	 *
	 * @return	array	キャッシュした値の全て
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::gets()
	 */
	public function gets () {
		foreach ($this->cacheExpireList as $name => $expire_time) {
			if ($this->cacheExpireList[$name] < time()) {
				unset($this->cacheList[$name]);
				unset($this->cacheExpireList[$name]);
				$this->state = false;
			} else {
				$this->state = true;
			}
		}

		return $this->cacheList;
	}

	/**
	 * 値をキャッシュします。
	 *
	 * @param	string	$name	キャッシュ名
	 * @param	mixed	$value	キャッシュする値
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\class_var	ClassVarCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::set()
	 */
	public function set ($name, $value, $ttl = self::DEFAULT_TTL) {
		$this->state = true;
		$this->cacheList[$name] = $value;
		$this->cacheExpireList[$name] = time() + $ttl;
		return $this;
	}

	/**
	 * 値を纏めてキャッシュします。
	 *
	 * @param	array	$sets	[['キャッシュ名' => キャッシュする値], ...]形式のキャッシュ名とキャッシュする値のペアを持つ配列
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\class_var	ClassVarCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::sets()
	 */
	public function sets ($sets, $ttl = self::DEFAULT_TTL) {
		$this->state = true;
		foreach ($sets as $name => $value) {
			$this->cacheList[$name] = $value;
			$this->cacheExpireList[$name] = time() + $ttl;
		}
		return $this;
	}

	/**
	 * キャッシュ名の値をキャッシュから破棄します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	\ickx\fw2\io\cache\class_var	ClassVarCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::remove()
	 */
	public function remove ($name) {
		if (isset($this->cacheExpireList[$name])) {
			$this->state = true;
			unset($this->cacheList[$name]);
			unset($this->cacheExpireList[$name]);
			return $this;
		}
		$this->state = false;
		return $this;
	}

	/**
	 * 現在キャッシュされている全ての値を破棄します。
	 *
	 * @return	\ickx\fw2\io\cache\class_var	ClassVarCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::clear()
	 */
	public function clear () {
		$this->cacheList = [];
		$this->cacheExpireList = [];
		$this->state = true;
		return $this;
	}
}
