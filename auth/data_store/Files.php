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
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\data_store;

/**
 * Auto Sessionデータストア：Files
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Files implements \ickx\fw2\auth\interfaces\IAuthSessionDataStore {
	use	\ickx\fw2\traits\magic\Accessor;

	/**
	 * @var	string	auth sessionセーブパス
	 */
	protected $savePath		= null;

	/**
	 * @var	string	auth sessionクッキー名
	 */
	protected $cookieName	= null;

	/**
	 * @var	int		auth session有効期限
	 */
	protected $expiration	= null;

	/**
	 * コンストラクタ
	 *
	 * @param	string	$save_path	auth sessionセーブパス
	 */
	public function __construct ($save_path) {
		$this->savePath	= $save_path ?? sys_get_temp_dir();
	}

	/**
	 * auth sessionデータが存在するか確認します。
	 *
	 * @param	string	$cookie_name	クッキー名
	 * @return	bool	auth sessionデータがある場合はtrue、そうでない場合はfalse
	 */
	public function exists ($cookie_name = null) {
		clearstatcache();
		return file_exists($this->savePath .'/'. ($cookie_name ?? $this->cookieName));
	}

	/**
	 * auth sessionデータを取得します。
	 *
	 * @return	array	auth sessionデータ
	 */
	public function load () {
		return file_get_contents($this->savePath .'/'. $this->cookieName);
	}

	/**
	 * auth sessionデータを保存します。
	 *
	 * @param	array	$data		保存するデータ
	 * @param	int		$expiration	保存期間
	 * @return	\ickx\fw2\auth\data_store\Files	このインスタンス
	 */
	public function save ($data, $expiration) {
		$this->expiration	= $expiration;
		file_put_contents($this->savePath .'/'. $this->cookieName, $data);
		return $this;
	}

	/**
	 * auth sessionデータを削除します。
	 *
	 * @return	\ickx\fw2\auth\data_store\Files	このインスタンス
	 */
	public function remove ($cookie_name = null) {
		if ($this->exists($cookie_name ?? $this->cookieName)) {
			unlink($this->savePath .'/'. ($cookie_name ?? $this->cookieName));
		}
		return $this;
	}

	/**
	 * サーバ上のauth sessionに対してガベージコレクションを実行します。
	 *
	 * @param	int		$expire	有効期間
	 * @param	string	$prefix	クッキー名プリフィックス
	 */
	public function gc ($expire, $prefix) {
		$prefix_length = mb_strlen($prefix);
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->savePath), \RecursiveIteratorIterator::CHILD_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD) as $file) {
			if ($file->isFile() && ($prefix_length === 0 || mb_substr($file->getFilename(), 0, $prefix_length) === $prefix) && $file->getATime() < time() + $expire) {
				unlink($file->getRealPath());
			}
		}
	}
}
