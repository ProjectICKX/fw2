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

namespace ickx\fw2\io\cache\abstracts;

/**
 * 抽象化キャッシュクラス
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class AbstractCache implements \ickx\fw2\io\cache\interfaces\ICache {
	/**
	 * @var	bool		最後に実行した処理の状態を保持します。
	 */
	protected $state		= false;

	/**
	 * @var	string		キャッシュグループ名
	 */
	protected $groupName	= null;

	/**
	 * 初期化処理を行います。
	 *
	 * @param	string	$name		キャッシュグループ名
	 * @param	array	...$args	初期化引数
	 * @return	\ickx\fw2\io\cache\abstracts\AbstractCache	AbstractCacheを継承したクラスのインスタンス
	 */
	public static function init ($name, ...$args) {
		return (new static($name, ...$args))->setGroupName($name);
	}

	/**
	 * コンストラクタ。
	 *
	 * @param	string	$name		キャッシュグループ名
	 * @param	array	...$args	初期化引数
	 */
	protected function __construct ($name, ...$args) {
	}

	/**
	 * キャッシュグループ名を設定します。
	 *
	 * @param	string	$name	キャッシュグループ名
	 * @return	\ickx\fw2\io\cache\abstracts\AbstractCache	AbstractCacheを継承したクラスのインスタンス
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::setGroupName()
	 */
	public function setGroupName ($name) {
		$this->groupName = $name;
		return $this;
	}

	/**
	 * 最後に実行した処理の状態を返します。
	 *
	 * @return	bool	最後の実行した処理の状態 成功している場合はtrue、そうでない場合はfalse
	 * @see		\ickx\fw2\io\cache\interfaces\ICache::state()
	 */
	public function state () {
		return $this->state;
	}
}
