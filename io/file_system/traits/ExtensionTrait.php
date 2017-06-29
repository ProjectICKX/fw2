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

namespace ickx\fw2\io\file_system\traits;

/**
 * 拡張子特性。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ExtensionTrait {
	/**
	 * 拡張子がCSVかどうか判定します。
	 *
	 * @param	string	$extension	拡張子
	 * @return	bool	拡張子がCSVの場合はtrue, そうでない場合はfalse
	 */
	public static function IsCsvExtension ($extension) {
		return in_array($extension, [
			static::EXTENSION_CSV,
		], true);
	}

	/**
	 * 拡張子がHTMLかどうか判定します。
	 *
	 * @param	string	$extension	拡張子
	 * @return	bool	拡張子がHTMLの場合はtrue, そうでない場合はfalse
	 */
	public static function IsHtmlExtension ($extension) {
		return in_array($extension, [
			static::EXTENSION_HTML,
			static::EXTENSION_HTM,
		], true);
	}
}
