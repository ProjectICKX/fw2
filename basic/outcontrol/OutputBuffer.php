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
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\basic\outcontrol;

/**
 * 出力バッファリングを行います。
 *
 * @category	Flywheel2
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class OutputBuffer {
	/**	@var	デフォルトの出力バッファハンドラ */
	const HANDLER_DEFAULT	= null;

	/** @var	gz利用時の出力バッファハンドラ */
	const HANDLER_GZIP		= 'ob_gzhandler';

	/**
	 * 出力バッファリングを開始します。
	 */
	public static function Start ($output_callback = self::HANDLER_DEFAULT, $chunk_size = 0, $erase = null) {
		return ($erase !== self::HANDLER_DEFAULT) ? ob_start($output_callback, $chunk_size, $erase) : ob_start($output_callback, $chunk_size);
	}

	/**
	 * gzip圧縮付の出力バッファリングを開始します。
	 */
	public static function StartGz ($chunk_size = 0, $erase = null) {
		return static::Start(static::HANDLER_GZIP, $chunk_size, $erase) ?: static::Start(self::HANDLER_DEFAULT, $chunk_size, $erase);
	}

	/**
	 * 現時点の出力バッファ長を返します。
	 */
	public static function Length () {
		return ob_get_length();
	}

	/**
	 * 現時点の出力バッファを文字列として返します。
	 */
	public static function Get () {
		return ob_get_contents();
	}

	/**
	 * 出力バッファをクリアし、出力バッファリングを終了します。
	 */
	public static function Clean () {
		return ob_end_clean();
	}

	/**
	 * 現時点の出力バッファを文字列として返し、出力バッファをクリアし、出力バッファリングを終了します。
	 */
	public static function GetClean () {
		return ob_get_clean();
	}

	/**
	 * 現時点の出力バッファを送信します。
	 */
	public static function Flush () {
		ob_flush();
	}

	/**
	 * 現時点の出力バッファを送信し、その内容を文字列として返し、出力バッファリングを終了します。
	 */
	public static function GetFlush () {
		return ob_get_flush();
	}

	/**
	 * 現時点の出力バッファを送信し、出力バッファリングを終了します。
	 */
	public static function EndFlush () {
		return ob_end_flush();
	}
}
