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

namespace ickx\fw2\io\cache\apcu;

use ickx\fw2\io\cache\Cache;

/**
 * キャッシュクラス：APCu
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ApcuCache extends \ickx\fw2\io\cache\abstracts\AbstractCache {
	/**
	 * @var	string	ストレージタイプ
	 */
	public const STORAGE_TYPE	= Cache::STORAGE_TYPE_APCU;

	/**
	 * 引数で与えた名前のキャッシュが存在するか確認します。
	 *
	 * @param	mixed	$name	キャッシュ名
	 * @return	bool	引数で与えた名前のキャッシュが存在する場合はtrue、そうでない場合はfalse
	 */
	public function has ($name) {
		if (!is_array($name)) {
			return apcu_exists($this->groupName .'<>'. $name);
		}

		$ret = [];
		foreach ($name as $key) {
			$ret[$key] = apcu_exists($this->groupName .'<>'. $key);
		}
		return $ret;
	}

	/**
	 * キャッシュした値を取得します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	mixed	キャッシュした値
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::get()
	 */
	public function get ($name) {
		return apcu_fetch($this->groupName .'<>'. $name, $this->state);
	}

	/**
	 * キャッシュした値を全て取得します。
	 *
	 * @return	array	キャッシュした値の全て
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::gets()
	 */
	public function gets () {
		$result = [];
		$group_name_length = mb_strlen($this->groupName . '<>');
		foreach (apcu_cache_info()['cache_list'] ?? [] as $cache_info) {
			if (mb_substr($cache_info['info'], 0, $group_name_length) === $this->groupName. '<>') {
				$result[mb_substr($cache_info['info'], $group_name_length)] = apcu_fetch($cache_info['info'], $this->state);
			}
		}
		return $result;
	}

	/**
	 * 値をキャッシュします。
	 *
	 * @param	string	$name	キャッシュ名
	 * @param	mixed	$value	キャッシュする値
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::set()
	 */
	public function set ($name, $value, $ttl = self::DEFAULT_TTL) {
		$this->state = apcu_store($this->groupName .'<>'. $name, $value, $ttl);
		return $this;
	}

	/**
	 * 値を纏めてキャッシュします。
	 *
	 * @param	array	$sets	[['キャッシュ名' => キャッシュする値], ...]形式のキャッシュ名とキャッシュする値のペアを持つ配列
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::sets()
	 */
	public function sets ($sets, $ttl = self::DEFAULT_TTL) {
		$tmp = [];
		foreach ($sets as $key => $value) {
			$tmp[$this->groupName .'<>'. $key] = $value;
		}
		$this->state = apcu_store($tmp, null, $ttl);
		return $this;
	}

	/**
	 * キャッシュ名の値をキャッシュから破棄します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::remove()
	 */
	public function remove ($name) {
		$this->state = apcu_delete($this->groupName .'<>'. $name);
		return $this;
	}

	/**
	 * 現在キャッシュされている全ての値を破棄します。
	 *
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::clear()
	 */
	public function clear () {
		$this->state = apcu_clear_cache();
		return $this;
	}
}
