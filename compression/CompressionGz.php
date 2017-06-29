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
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\compression;

/**
 * gzencode, gzdecodeを用いた文字列圧縮を扱うクラスです。
 *
 * @category	Flywheel2
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class CompressionGz implements interfaces\ICompression {
	use	traits\CompressionTrait;

	/**
	 * gzencodeを用いて文字列圧縮を行います。
	 * @param	string	$string	圧縮する文字列
	 * @return	binary	圧縮されたバイナリ
	 */
	public static function Compress ($string) {
		return gzencode($string, static::COMPRESS_LEVEL, \FORCE_GZIP);
	}

	/**
	 * gzdecodeを用いてgzバイナリを伸長します。
	 * @param	binary	$binary	GZバイナリ
	 * @return	mixed	伸長されたGZバイナリ
	 */
	public static function UnCompress ($binary) {
		return @gzdecode($binary);
	}
}
