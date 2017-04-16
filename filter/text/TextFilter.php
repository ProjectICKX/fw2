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

namespace ickx\fw2\filter\text;

use ickx\fw2\international\encoding\Encoding;
use ickx\fw2\vartype\strings\Strings;

/**
 * テキストフィルタ
 *
 * @category	Flywheel2
 * @package		filter
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class TextFilter {
	/** @var	string	CSV文字列クォート方法：自動：-01や090など、MS EXCELでは数字に変更されてしまうパターンを"で括る */
	const CSV_QUOTE_AUTOMATIC	= 'CSV_QUOTE_AUTOMATIC';

	/** @var	string	CSV文字列クォート方法：強制：強制的にクォートする */
	const CSV_QUOTE_FORCE_QUOTE	= 'CSV_QUOTE_FORCE_QUOTE';

	/** @var	string	CSV文字列クォート方法：MS EXCEL準拠：MS EXCELで出力した場合と同様の出力にする */
	const CSV_QUOTE_MS_EXCEL	= 'CSV_QUOTE_MS_EXCEL';

	/**
	 * 文字列をCSVに適した形に変換します。
	 *
	 * @param	string	$text			変換する文字列
	 * @param	string	$encoding		エンコーディング
	 * @param	string	$quote_mode		文字列クォート方式
	 * @return	string	CSV化された文字列
	 */
	public static function TextToCsv ($text, $encoding = null, $quote_mode = self::CSV_QUOTE_AUTOMATIC) {
		$encoding	= $encoding ?: mb_internal_encoding();
		$length		= mb_strlen($text, $encoding);
		$escape		= false;
		$quote		= false;

		switch ($quote_mode) {
			case static::CSV_QUOTE_AUTOMATIC:
				if (1 < $length) {
					switch (substr($text, 0 , 1)) {
						case '0':
							$quote = true;
							break;
						case '-':
						case '+':
							if (substr($text, 1 , 1) == '0') {
								$quote = true;
							}
							break;
					}
				}
				break;
			case static::CSV_QUOTE_FORCE_QUOTE:
				$quote		= true;
				break;
		}

		for ($i = 0;$i < $length;$i++) {
			switch (mb_substr($text, $i, 1, $encoding)) {
				case '"':
					$escape = true;
				case Strings::CR:
				case Strings::LF:
				case ',':
					$quote = true;
					break 2;
			}
		}

		return ($escape) ? '"'. str_replace('"', '""', $text) .'"' : ($quote) ? '"'. $text .'"' : $text;
	}
}
