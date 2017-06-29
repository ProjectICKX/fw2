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
 * @package		date_time
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\date_time;

/**
 * 日付を管理するクラスです。
 *
 * @category	Flywheel2
 * @package		date_time
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DateTime extends \DateTime implements interfaces\IDateTimeConst {
	/**
	 * 文字列を適切なフォーマットに変換します。
	 *
	 * @param	string	$date_string	フォーマット変換したい日付文字列
	 * @param	string	$format			フォーマット
	 * @return	string	フォーマットを変換された日付文字列
	 */
	public static function StringConvert ($date_string, $format = self::YMD_HIS) {
		return (new static($date_string))->format($format);
	}
}
