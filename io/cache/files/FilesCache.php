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

namespace ickx\fw2\io\cache\files;

use ickx\fw2\io\file_system\FileSystem;
use ickx\fw2\io\cache\Cache;

/**
 * キャッシュクラス：ファイルベース
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class FilesCache extends \ickx\fw2\io\cache\abstracts\AbstractCache {
	/**
	 * @var	string	ストレージタイプ
	 */
	public const STORAGE_TYPE	= Cache::STORAGE_TYPE_FILES;

	/**
	 * @var	string	デフォルトのキャッシュサブディレクトリ
	 */
	public const DEFAULT_CACHE_SUB_DIRECTORY	= 'fw2_cache';

	/**
	 * @var	string	キャッシュディレクトリ
	 */
	protected $_cacheDir	= null;

	/**
	 * コンストラクタ。
	 *
	 * @param	string	$name		キャッシュグループ名
	 * @param	array	...$args	初期化引数
	 */
	protected function __construct ($group_name, $cache_dir = null) {
		$this->_cacheDir = $cache_dir;
	}

	/**
	 * キャッシュした値を取得します。
	 *
	 * @param	string	$name	キャッシュ名：キャッシュファイルパス
	 * @return	mixed	キャッシュした値
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::get()
	 */
	public function get ($name) {
		if (substr($name, 0, 1) !== '/') {
			$name = sprintf('%s/%s', $this->_cacheDir ?? sprintf('%s/%s/%s', sys_get_temp_dir(), static::DEFAULT_CACHE_SUB_DIRECTORY, str_replace(["\\", '/', "\r\n", "\r", "\n"], '_', $this->groupName)), $name);
		}

		if (true !== FileSystem::IsReadableFile($name, ['raise_exception' => false])) {
			$this->state = false;
			return null;
		}

		$cache = include $name;
		if ($cache['expire'] < time()) {
			unlink($name);
			$this->state = false;
			return null;
		}

		$this->state = true;
		return $cache['value'];
	}

	/**
	 * キャッシュした値を全て取得します。
	 *
	 * 有効に使うには_cacheDirを明示的に指定する必要があります。
	 *
	 * @return	array	キャッシュした値の全て
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::gets()
	 */
	public function gets () {
		if (is_null($this->_cacheDir)) {
			return [];
		}

		$result = [];

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->_cacheDir, \FilesystemIterator::CURRENT_AS_SELF | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS), \RecursiveIteratorIterator::CHILD_FIRST) as $full_path => $fileInfo) {
			if ($fileInfo->isDir()) {
				$children = $fileInfo->getChildren();
				$children->setFlags(\FilesystemIterator::CURRENT_AS_FILEINFO);
				var_dump($children->next());
				if (is_null($children->next())) {
					rmdir($full_path);
				}
			} else {
				$cache = include $full_path;
				if (($cache['expire'] ?? 0) < time()) {
					unlink($full_path);
					$this->state = false;
					continue;
				}

				$this->state = true;
				$result[$fileInfo->getSubPathName()] = $cache['value'];
			}
		}

		return $result;
	}

	/**
	 * 値をキャッシュします。
	 *
	 * @param	string	$name	キャッシュ名：キャッシュファイルパス
	 * @param	mixed	$value	キャッシュする値
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::set()
	 */
	public function set ($name, $value, $ttl = self::DEFAULT_TTL) {
		if (substr($name, 0, 1) !== '/') {
			$cache_dir = $this->_cacheDir ?? sprintf('%s/%s/%s', sys_get_temp_dir(), static::DEFAULT_CACHE_SUB_DIRECTORY, str_replace(["\\", '/', "\r\n", "\r", "\n"], '_', $this->groupName));
			$name = sprintf('%s/%s', $cache_dir, $name);
		}

		if (true !== FileSystem::IsEnableDirectory(dirname($name), ['raise_exception' => false])) {
			FileSystem::CreateDirectory(dirname($name), ['raise_exception' => true, 'name' => dirname($name), 'parents' => true, 'skip' => true, 'mode' => 0775]);
		}

		$this->state = file_put_contents($name, '<?php return ' . var_export(['value' => $value, 'expire' => time() + $ttl, 'ttl' => $ttl], true) . ';');
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
		foreach ($sets as $name => $value) {
			if (substr($name, 0, 1) !== '/') {
				$cache_dir = $this->_cacheDir ?? sprintf('%s/%s/%s', sys_get_temp_dir(), static::DEFAULT_CACHE_SUB_DIRECTORY, str_replace(["\\", '/', "\r\n", "\r", "\n"], '_', $this->groupName));
				$name = sprintf('%s/%s', $cache_dir, $name);
			}

			if (true !== FileSystem::IsEnableDirectory(dirname($name), ['raise_exception' => false])) {
				FileSystem::CreateDirectory(dirname($name), ['raise_exception' => true, 'name' => dirname($name), 'parents' => true, 'skip' => true, 'mode' => 0775]);
			}

			$this->state = file_put_contents($name, '<?php return ' . var_export(['value' => $value, 'expire' => time() + $ttl, 'ttl' => $ttl], true) . ';');
		}
		return $this;
	}

	/**
	 * キャッシュ名の値をキャッシュから破棄します。
	 *
	 * @param	string	$name	キャッシュ名：キャッシュファイルパス
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::remove()
	 */
	public function remove ($name) {
		if (substr($name, 0, 1) !== '/') {
			$cache_dir = $this->_cacheDir ?? sprintf('%s/%s/%s', sys_get_temp_dir(), static::DEFAULT_CACHE_SUB_DIRECTORY, str_replace(["\\", '/', "\r\n", "\r", "\n"], '_', $this->groupName));
			if (true !== FileSystem::IsEnableDirectory($cache_dir, ['raise_exception' => false])) {
				FileSystem::CreateDirectory($cache_dir, ['raise_exception' => true, 'name' => $this->_cacheDir, 'parents' => true, 'skip' => true, 'mode' => 0775]);
			}
			$name = sprintf('%s/%s', $cache_dir, $name);
		}

		$this->state = unlink($name);
		return $this;
	}

	/**
	 * 現在キャッシュされている全ての値を破棄します。
	 *
	 * @return	\ickx\fw2\io\cache\apcu\ApcuCache	ApcuCacheインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::clear()
	 */
	public function clear () {
		if (is_null($this->_cacheDir)) {
			$This->state = false;
			return $this;
		}

		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->_cacheDir, \FilesystemIterator::CURRENT_AS_SELF | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS), \RecursiveIteratorIterator::CHILD_FIRST) as $full_path => $fileInfo) {
			if ($fileInfo->isDir()) {
				$children = $fileInfo->getChildren();
				$children->setFlags(\FilesystemIterator::CURRENT_AS_FILEINFO);
				var_dump($children->next());
				if (is_null($children->next())) {
					rmdir($full_path);
				}
			} else {
				$this->state = unlink($full_path);
			}
		}

		return $this;
	}
}
