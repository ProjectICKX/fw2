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

namespace ickx\fw2\basic\urls;

/**
 * URLエンコードを扱います。
 *
 * @category	Flywheel2
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Url {
	public static function Encode ($type, $value, $options = []) {
		$method_name = ucfirst($type) . 'Encode';
		if (!method_exists(static::class, $method_name)) {
			CoreException::ScrubbedThrow(Status::SystemError('存在しないメソッドを指定されました。'));
		}
		return static::$method_name($value, $options);
	}

	public static function Decode ($type, $value, $options = []) {
		$method_name = ucfirst($type) . 'Decode';
		if (!method_exists(static::class, $method_name)) {
			CoreException::ScrubbedThrow(Status::SystemError('存在しないメソッドを指定されました。'));
		}
		return static::$method_name($value, $options);
	}

	/**
	 * Base64 Encodeを行います。
	 *
	 * @param unknown $value
	 * @param unknown $options
	 * @return string
	 */
	public static function Base64Encode ($value, $options = []) {
		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			$value = mb_convert_encoding($value, $to_encoding, $from_encoding);
		}
		return base64_encode($value);
	}

	/**
	 * Base64 Decodeを行います。
	 *
	 * @param unknown $value
	 * @param unknown $options
	 * @return string
	 */
	public static function Base64Decode ($value, $options = []) {
		$value = base64_decode($value);

		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			return mb_convert_encoding($value, $to_encoding, $from_encoding);
		}

		return $value;
	}

	public static function PercentEncode ($value, $options = []) {
		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			$value = mb_convert_encoding($value, $to_encoding, $from_encoding);
		}
		return rawurlencode($value);
	}

	public static function PercentDecode ($value, $options = []) {
		$value = rawurldecode($value);

		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			return mb_convert_encoding($value, $to_encoding, $from_encoding);
		}

		return $value;
	}

	public static function UrlEncode ($value, $options = []) {
		return static::PercentEncode($value, $options);
	}

	public static function UrlDecode ($value, $options = []) {
		return static::PercentDecode($value, $options);
	}

	public static function UnicodeEncode ($value, $options = []) {
		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			$value = mb_convert_encoding($value, $to_encoding, $from_encoding);
		}
		return trim(json_encode($value), '"');
	}

	public static function UnicodeDecode ($value, $options = []) {
		$value = json_decode(sprintf('"%s"', $value));

		$to_encoding = isset($options['to_encoding']) ? $options['to_encoding'] : false;
		if ($to_encoding !== false) {
			$from_encoding = isset($options['from_encoding']) ? $options['from_encoding'] : static::DEFAULT_ENCODING;
			return mb_convert_encoding($value, $to_encoding, $from_encoding);
		}

		return $value;
	}
}
