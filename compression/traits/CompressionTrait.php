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

namespace ickx\fw2\compression\traits;

/**
 * 圧縮クラス特性
 *
 * @category	Flywheel2
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait CompressionTrait {
	/**
	 * 文字列を圧縮した後に、指定されたエンコードに変換してから返します。
	 *
	 * @param	string			$string	圧縮する文字列
	 * @param	string/callable	$encode	圧縮後に掛けるエンコード名 コールバック関数を指定した場合はそちらがコールバック関数でエンコードする
	 * @return	mixed			圧縮され指定されたエンコード変換後の文字列
	 */
	public static function CompressEncode ($string, $encode = null) {
		if ($encode === null) {
			return static::Compress($string);
		}
		if (is_callable($encode)) {
			return $encode(static::Compress($string));
		}
		$encode_func_list = static::GetEncodeFunction();
		if (!isset($encode_func_list[$encode][static::ENCODE])) {
			return static::Compress($string);
		}
		return $encode_func_list[$encode][static::ENCODE](static::Compress($string));
	}

	/**
	 * PHPの変数をシリアライズして圧縮します。
	 *
	 * @param	mixed			$var	PHPの変数
	 * @param	string/callable	$encode	圧縮後に掛けるエンコード名 コールバック関数を指定した場合はコールバック関数でエンコードする
	 * @return	mixed			圧縮され指定されたエンコード変換後の変数
	 */
	public static function CompressVariable ($var, $encode = null) {
		return static::CompressEncode(serialize($var), $encode);
	}

	/**
	 * バイナリを伸長した後に、指定されたエンコードで変換してから返します。
	 *
	 * @param	binary			$binary	圧縮されたバイナリ
	 * @param	string/callable	$decode	伸長後に掛けるデコーダ用エンコード名 コールバック関数を指定した場合はコールバック関数でデコードする
	 * @return	mixed			伸長されたバイナリ
	 */
	public static function UnCompressDecode ($binary, $decode = null) {
		if ($decode === null) {
			return static::UnCompress($binary);
		}
		if (is_callable($decode)) {
			return static::UnCompress($decode($binary));
		}
		$encode_func_list = static::GetEncodeFunction();
		if (!isset($encode_func_list[$decode][static::DECODE])) {
			return static::UnCompress($binary);
		}
		return static::UnCompress($encode_func_list[$decode][static::DECODE]($binary));
	}

	/**
	 * 指定されたエンコードでデコードしたのち、バイナリを伸長し、アンシリアライズしてPHPの変数として返します。
	 *
	 * @param	binary			$binary	圧縮されたバイナリ
	 * @param	string/callable	$decode	伸長後に掛けるデコーダ用エンコード名 コールバック関数を指定した場合はコールバック関数でデコードする
	 * @return	mixed			アンシリアライズされたPHP変数
	 */
	public static function UnCompressVariable ($string, $encode = null) {
		return unserialize(static::UnCompressDecode($string, $encode));
	}

	/**
	 * 指定されたエンコード名に紐付く、エンコーダー、デコーダーを返します。
	 *
	 * @return	array	指定されたエンコード名に紐付くエンコーダー、デコーダー
	 */
	public static function GetEncodeFunction () {
		return [
			static::ENCODE_BASE64	=> [
				static::ENCODE	=> 'base64_encode',
				static::DECODE	=> 'base64_decode',
			],
		];
	}
}
