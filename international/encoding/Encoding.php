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
 * @package		international
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */

namespace ickx\fw2\international\encoding;

/**
 * 文字エンコーディングに係る処理を扱います。
 *
 * @see http://php.net/manual/ja/mbstring.encodings.php
 * @see http://php.net/manual/ja/mbstring.supported-encodings.php
 */
class Encoding {
	const UCS_4						= 'UCS-4';
	const UCS_4BE					= 'UCS-4BE';
	const UCS_4LE					= 'UCS-4LE';
	const UCS_2						= 'UCS-2';
	const UCS_2BE					= 'UCS-2BE';
	const UCS_2LE					= 'UCS-2LE';
	const UTF_32					= 'UTF-32';
	const UTF_32BE					= 'UTF-32BE';
	const UTF_32LE					= 'UTF-32LE';
	const UTF_16					= 'UTF-16';
	const UTF_16BE					= 'UTF-16BE';
	const UTF_16LE					= 'UTF-16LE';
	const UTF_7						= 'UTF-7';
	const UTF_7_IMAP				= 'UTF7-IMAP';
	const UTF_8						= 'UTF-8';
	const ASCII						= 'ASCII';
	const EUC_JP					= 'EUC-JP';
	const SJIS						= 'SJIS';
	const EUCJP_WIN					= 'eucJP-win';
	const SJIS_WIN					= 'SJIS-win';
	const ISO_2022_JP				= 'ISO-2022-JP';
	const ISO_2022_JP_MS			= 'ISO-2022-JP-MS';
	const CP932						= 'CP932';
	const CP51932					= 'CP51932';
	const SJIS_MAC					= 'SJIS-mac';
	const SJIS_MOBILE_DOCOMO		= 'SJIS-Mobile#DOCOMO';
	const SJIS_MOBILE_KDDI			= 'SJIS-Mobile#KDDI';
	const SJIS_MOBILE_SOFTBANK		= 'SJIS-Mobile#SOFTBANK';
	const UTF_8_MOBILE_DOCOMO		= 'UTF-8-Mobile#DOCOMO';
	const UTF_8_MOBILE_KDDI_A		= 'UTF-8-Mobile#KDDI-A';
	const UTF_8_MOBILE_KDDI_B		= 'UTF-8-Mobile#KDDI-B';
	const UTF_8_MOBILE_SOFTBANK		= 'UTF-8-Mobile#SOFTBANK';
	const ISO_2022_JP_MOBILE_KDDI	= 'ISO-2022-JP-MOBILE#KDDI';
	const JIS						= 'JIS';
	const JIS_MS					= 'JIS-ms';
	const CP50220					= 'CP50220';
	const CP50220RAW				= 'CP50220raw';
	const CP50221					= 'CP50221';
	const CP50222					= 'CP50222';
	const ISO_8859_1				= 'ISO-8859-1';
	const ISO_8859_2				= 'ISO-8859-2';
	const ISO_8859_3				= 'ISO-8859-3';
	const ISO_8859_4				= 'ISO-8859-4';
	const ISO_8859_5				= 'ISO-8859-5';
	const ISO_8859_6				= 'ISO-8859-6';
	const ISO_8859_7				= 'ISO-8859-7';
	const ISO_8859_8				= 'ISO-8859-8';
	const ISO_8859_9				= 'ISO-8859-9';
	const ISO_8859_10				= 'ISO-8859-10';
	const ISO_8859_13				= 'ISO-8859-13';
	const ISO_8859_14				= 'ISO-8859-14';
	const ISO_8859_15				= 'ISO-8859-15';
	const BYTE2BE					= 'byte2be';
	const BYTE2LE					= 'byte2le';
	const BYTE4BE					= 'byte4be';
	const BYTE4LE					= 'byte4le';
	const BASE64					= 'BASE64';
	const HTML_ENTITIES				= 'HTML-ENTITIES';
	const IANA_7BIT					= '7bit';
	const IANA_8BIT					= '8bit';
	const EUC_CN					= 'EUC-CN';
	const CP936						= 'CP936';
	const GB18030					= 'GB18030';
	const HZ						= 'HZ';
	const EUC_TW					= 'EUC-TW';
	const CP950						= 'CP950';
	const BIG_5						= 'BIG-5';
	const EUC_KR					= 'EUC-KR';
	const UHC						= 'UHC';
	const ISO_2022_KR				= 'ISO-2022-KR';
	const WINDOWS_1251				= 'Windows-1251';
	const WINDOWS_1252				= 'Windows-1252';
	const CP866						= 'CP866';
	const KOI8_R					= 'KOI8-R';
	const DEFAULT_ENCODING			= self::UTF_8;

	const ENCOFING_LIST				= [self::ASCII, self::JIS, self::JIS_MS, self::UTF_8, self::EUCJP_WIN, self::EUC_JP, self::SJIS_WIN, self::SJIS];

	/**
	 * 文字列のエンコーディングが指定されたものかどうかを判定します。
	 *
	 * @param	string	$string				エンコーディングを調べたい文字列
	 * @param	string	$target_encoding	マッチさせるエンコーディング
	 * @return	mixed	エンコーディングがマッチした場合はTRUE、そうでない場合はFALSE
	 * 					文字列のエンコーディング検出に失敗している場合はNULL
	 */
	public static function Compare ($string, $target_encoding, $encoding_list = self::ENCOFING_LIST) {
		$encoding = static::Detect($string, $encoding_list);
		return ($encoding === FALSE) ? FALSE : ($encoding === $target_encoding);
	}

	/**
	 * 文字列のエンコーディングを検出します。
	 *
	 * @param	string	$string			エンコーディングを検出したい文字列。
	 * @param	string	$encoding_list	エンコーディング検出順リスト カンマ区切りの文字列で記述 省略可能
	 * @return	mixed	検出したエンコーディング名 検出に失敗した場合はFALSE
	 */
	public static function Detect ($string, $encoding_list = self::ENCOFING_LIST) {
		return mb_detect_encoding($string, $encoding_list, TRUE);
	}

	/**
	 * 文字列のエンコーディングを合わせます。
	 *
	 * @param	string	$string			エンコードを合わせる文字列
	 * @param	string	$to_encoding	変換後のエンコード nullが指定された場合は内部エンコーディングを使用
	 * @param	string	$encoding_list	エンコード検出順リスト カンマ区切りの文字列で記述 省略可能
	 * @return	string	$to_encodingにエンコードを合わせた文字列
	 */
	public static function Adjust ($string, $to_encoding = null, $encoding_list = self::ENCOFING_LIST) {
		$to_encoding !== null ?: $to_encoding = mb_internal_encoding();
		if (static::Compare($string, $to_encoding, $encoding_list)) {
			return $string;
		}
		return static::Convert($string, $to_encoding, static::Detect($string, $encoding_list));
	}

	/**
	 *
	 */
	public static function GetOsViewEncoding ($os_name = \PHP_OS) {
		switch ($os_name) {
			case 'WINNT':
			case 'WIN32':
				return static::SJIS_WIN;
			default:
				return static::UTF_8;
		}
	}

	/**
	 * 文字列の文字エンコーディングを変換します。
	 *
	 * @param	string	$string			文字エンコーディング変換を行う文字列
	 * @param	string	$from_encoding	変換先の文字エンコーディング名
	 * @param	string	$from_encoding	変換元の文字エンコーディング名
	 * @return	string	文字エンコーディングを変換された文字列
	 */
	public static function Convert ($string, $to_encoding, $from_encoding = self::DEFAULT_ENCODING) {
		return mb_convert_encoding($string, $to_encoding, $from_encoding);
	}

	/**
	 * 文字列をシステムがデフォルトで使用している文字エンコーディングに変換します。
	 *
	 * @param	string	$string			文字エンコーディング変換を行う文字列
	 * @param	string	$from_encoding	変換先の文字エンコーディング名
	 * @return	string	文字エンコーディングを変換された文字列
	 */
	public static function ConvertToDefaultEncoding ($string, $from_encoding = null) {
		return mb_convert_encoding($string, static::DEFAULT_ENCODING, $from_encoding ?: static::Detect($string));
	}

	/**
	 * 配列全ての要素に文字エンコーディング変換を適用します。
	 *
	 * @param	array	$input			文字エンコーディング変換対象の配列
	 * @param	string	$to_encoding	変換先の文字エンコーディング名
	 * @param	string	$from_encoding	変換元の文字エンコーディング名
	 * @return	mixed	文字エンコーディングを変換された配列 変換に失敗している場合はboolean FALSE
	 */
	public static function ConvertForArray (array $input, $to_encoding, $from_encoding = self::DEFAULT_ENCODING) {
		$parameter_list = array(
			'to'	=> $to_encoding,
			'from'	=> $from_encoding,
		);
		return (array_walk($input, array('static', '_ConvertRapperForArrayWalk'), $parameter_list) === FALSE) ? FALSE : $input;
	}

	/**
	 * 配列全ての要素に文字エンコーディング変換を再帰的に適用します。
	 *
	 * @param	array	$input			文字エンコーディング変換対象の配列
	 * @param	string	$to_encoding	変換先の文字エンコーディング名
	 * @param	string	$from_encoding	変換元の文字エンコーディング名
	 * @return	mixed	再帰的に文字エンコーディングを変換された配列 変換に失敗している場合はboolean FALSE
	 */
	public static function ConvertArrayRecursive (array $input, $to_encoding, $from_encoding = self::DEFAULT_ENCODING) {
		$parameter_list = array(
			'to'	=> $to_encoding,
			'from'	=> $from_encoding,
		);
		return (array_walk_recursive($input, array('static', '_ConvertRapperForArrayWalk'), $parameter_list) === FALSE) ? FALSE : $input;
	}

	/**
	 * array_walk*関数用の文字エンコーディング変換ラッパーメソッドです。
	 *
	 * @param	mixed	&$value			文字エンコーディング変換を行う配列の値
	 * @param	mixed	$key			配列のキー
	 * @param	array	$parameter_list	変換用の設定値。次の構造となる
	 * array(
	 * 		'to'	=> 変換先エンコーディング名。必須
	 * 		'from'	=> 変換元エンコーディング名。必須
	 * )
	 */
	private static function _ConvertRapperForArrayWalk (&$value, $key, array $parameter_list) {
		if (!is_array($value)) {
			$value = static::convert($value, $parameter_list['to'], $parameter_list['from']);
		}
	}
}
