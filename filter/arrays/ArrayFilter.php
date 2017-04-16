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

namespace ickx\fw2\filter\arrays;

/**
 * 配列フィルタ
 *
 * @category	Flywheel2
 * @package		filter
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ArrayFilter {
	/**
	 * 空の値をフィルタします。
	 *
	 * @param	array	$array	フィルタする配列
	 * @return	array	フィルタされた配列
	 */
	public static function SanitizeEmptyString ($array) {
		return array_filter(
			$array,
			['static', 'IsNotEmptyValue']
		);
	}

	/**
	 * 値が空かどうか判定します。
	 *
	 * @param	string	@value	判定する値
	 * @return	bool	値が空でない場合 bool true、そうでない場合はfalse
	 */
	public static function IsNotEmptyValue ($value) {
		if ($value === null) {
			return false;
		}
		if ($value === '') {
			return false;
		}
		return true;
	}
}
