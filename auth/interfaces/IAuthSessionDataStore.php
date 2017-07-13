<?php
/**  ______ _                 _               _ ___
 *  |  ____| |               | |             | |__ \
 *  | |__  | |_   ___      __| |__   ___  ___| |  ) |
 *  |  __| | | | | \ \ /\ / /| '_ \ / _ \/ _ \ | / /
 *  | |    | | |_| |\ V  V / | | | |  __/  __/ |/ /_
 *  |_|    |_|\__; | \_/\_/  |_| |_|\___|\___|_|____|
 *             __/ |
 *            |___/
 *
 * Flywheel2: the inertia php framework
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\interfaces;

/**
 * Flywheel2 認証・認可セッションセーブハンドラインターフェースです。
 *
 * @category	Flywheel2
 * @package		het
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IAuthSessionDataStore {
	/**
	 * コンストラクタ
	 *
	 * @param	string	$save_path	auth sessionセーブパス
	 */
	public function __construct ($save_path);

	/**
	 * auth sessionデータが存在するか確認します。
	 *
	 * @param	string	$cookie_name	クッキー名
	 * @return	bool	auth sessionデータがある場合はtrue、そうでない場合はfalse
	 */
	public function exists ($cookie_name = null);

	/**
	 * auth sessionデータを取得します。
	 *
	 * @return	array	auth sessionデータ
	 */
	public function load ();

	/**
	 * auth sessionデータを保存します。
	 *
	 * @param	array	$data		保存するデータ
	 * @param	int		$expiration	保存期間
	 * @return	\ickx\fw2\auth\data_store\Memcached	このインスタンス
	 */
	public function save ($data, $expiration);

	/**
	 * auth sessionデータを削除します。
	 *
	 * @param	strint	クッキー名
	 * @return	\ickx\fw2\auth\data_store\Memcached	このインスタンス
	 */
	public function remove ($cookie_name = null);

	/**
	 * サーバ上のauth sessionに対してガベージコレクションを実行します。
	 *
	 * @param	int		$expire	有効期間
	 * @param	string	$prefix	クッキー名プリフィックス
	 */
	public function gc ($expire, $prefix);
}
