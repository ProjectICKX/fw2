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

namespace ickx\fw2\io\cache\interfaces;

/**
 * キャッシュインターフェース
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface ICache {
	/**
	 * @var	int		デフォルトTTL：1日
	 */
	public const DEFAULT_TTL	= 86400;

	/**
	 * キャッシュグループ名を設定します。
	 *
	 * @param	string	$name	キャッシュグループ名
	 * @return	\ickx\fw2\io\cache\interfaces\ICache	ICacheを実装したインスタンス
	 */
	public function setGroupName ($name);

	/**
	 * 引数で与えた名前のキャッシュが存在するか確認します。
	 *
	 * @param	mixed	$name	キャッシュ名
	 * @return	bool	引数で与えた名前のキャッシュが存在する場合はtrue、そうでない場合はfalse
	 */
	public function has ($name);

	/**
	 * キャッシュした値を取得します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	mixed	キャッシュした値
	 */
	public function get ($name);

	/**
	 * キャッシュした値を全て取得します。
	 *
	 * @return	array	キャッシュした値の全て
	 */
	public function gets ();

	/**
	 * 値をキャッシュします。
	 *
	 * @param	string	$name	キャッシュ名
	 * @param	mixed	$value	キャッシュする値
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\interfaces\ICache	ICacheを実装したインスタンス
	 */
	public function set ($name, $value, $ttl = self::DEFAULT_TTL);

	/**
	 * 値を纏めてキャッシュします。
	 *
	 * @param	array	$sets	[['キャッシュ名' => キャッシュする値], ...]形式のキャッシュ名とキャッシュする値のペアを持つ配列
	 * @param	int		$ttl	キャッシュTTL
	 * @return	\ickx\fw2\io\cache\interfaces\ICache	ICacheを実装したインスタンス
	 */
	public function sets ($sets, $ttl = self::DEFAULT_TTL);

	/**
	 * キャッシュ名の値をキャッシュから破棄します。
	 *
	 * @param	string	$name	キャッシュ名
	 * @return	\ickx\fw2\io\cache\interfaces\ICache	ICacheを実装したインスタンス
	 */
	public function remove ($name);

	/**
	 * 現在キャッシュされている全ての値を破棄します。
	 *
	 * @return	\ickx\fw2\io\cache\interfaces\ICache	ICacheを実装したインスタンス
	 */
	public function clear ();

	/**
	 * 最後に実行した処理の状態を返します。
	 *
	 * @return	bool	最後の実行した処理の状態 成功している場合はtrue、そうでない場合はfalse
	 */
	public function state ();
}
