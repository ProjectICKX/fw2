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
 * @package		filter
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\filter\traits;

/**
 * フィルタイテレータ特性
 *
 * @category	Flywheel2
 * @package		filter
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait FilterIteratorTrait {
	/**
	 * フィルタイテレータを作成します。
	 *
	 * @param	\Iterator	$iterator	フィルタリングの対象となるイテレータ。
	 * @return	\Iterator	イテレータ
	 */
	public static function Create ($iterator) {
		return new static(is_array($iterator) ? new \ArrayIterator($iterator) : $iterator);
	}
}
