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
 * Auto Sessionデータストア：Memcached。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Memcached implements \ickx\fw2\auth\interfaces\IAuthSessionDataStore {
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
		if (mb_substr($save_path, 0, 7) === 'unix://') {
			$host_name	= $save_path;
			$port		= 0;
		} else if (false !== preg_match("/\A([^:]+):(\d+)\z/", $save_path, $mat)) {
			$host_name	= $mat[1];
			$port		= $mat[2];
		} else {
			throw new \ErrorException(sprintf('不正なMemcached用パスを渡されました。save path:%s', $save_path));
		}

		$this->handler = new \Memcached();
		$this->handler->addServer($host_name, $port);
		if ($this->handler->getVersion() === false) {
			throw new \ErrorException(sprintf('Memcachedとの接続に失敗しました。host name:%s, port:%s', $host_name, $port));
		}
	}

	/**
	 * auth sessionデータが存在するか確認します。
	 *
	 * @param	string	$cookie_name	クッキー名
	 * @return	bool	auth sessionデータがある場合はtrue、そうでない場合はfalse
	 */
	public function exists ($cookie_name = null) {
		$this->handler->get($cookie_name ?? $this->cookieName);
		return $this->handler->getResultCode() !== \Memcached::RES_NOTFOUND;
	}

	/**
	 * auth sessionデータを取得します。
	 *
	 * @return	array	auth sessionデータ
	 */
	public function load () {
		return $this->handler->get($this->cookieName);
	}

	/**
	 * auth sessionデータを保存します。
	 *
	 * @param	array	$data		保存するデータ
	 * @param	int		$expiration	保存期間
	 * @return	\ickx\fw2\auth\data_store\Memcached	このインスタンス
	 */
	public function save ($data, $expiration) {
		$this->expiration	= $expiration;
		$this->handler->set($this->cookieName, $data);
		return $this;
	}

	/**
	 * auth sessionデータを削除します。
	 *
	 * @return	\ickx\fw2\auth\data_store\Memcached	このインスタンス
	 */
	public function remove ($cookie_name = null) {
		$this->handler->delete($cookie_name ?? $this->cookieName);
	}

	/**
	 * サーバ上のauth sessionに対してガベージコレクションを実行します。
	 *
	 * @param	int		$expire	有効期間
	 * @param	string	$prefix	クッキー名プリフィックス
	 */
	public function gc ($expire, $prefix) {
	}
}
