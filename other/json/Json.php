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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\json;

/**
 * JSONを扱うクラスです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Json {
	/**
	 * @var	string	JSON解析時の再帰の深さ
	 * @static
	 */
	public const DEFAULT_DEPTH		= 512;

	/**
	 * @var	string	JSONエンコードオプション
	 * @static
	 */
	public const DEFAULT_OPTIONS	= \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT;

	/**
	 * @var	array	JSONエンコード・デコードエラーメッセージ
	 * @static
	 */
	public const ERROR_MESSAGES		= [
		\JSON_ERROR_NONE					=> 'エラーは発生しませんでした。',
		\JSON_ERROR_DEPTH					=> 'スタックの深さの最大値を超えました。',
		\JSON_ERROR_STATE_MISMATCH			=> 'JSON の形式が無効、あるいは壊れています。',
		\JSON_ERROR_CTRL_CHAR				=> '制御文字エラー。おそらくエンコーディングが違います。',
		\JSON_ERROR_SYNTAX					=> '構文エラー。',
		\JSON_ERROR_UTF8					=> '正しくエンコードされていないなど、不正な形式の UTF-8 文字を渡されました。',
		\JSON_ERROR_RECURSION				=> 'エンコード対象の値に再帰参照が含まれています',
		\JSON_ERROR_INF_OR_NAN				=> 'エンコード対象の値に NAN あるいは INF が含まれています。',
		\JSON_ERROR_UNSUPPORTED_TYPE		=> 'エンコード不可能な型の値が渡されました。',
		\JSON_ERROR_INVALID_PROPERTY_NAME	=> 'エンコードできないプロパティ名が指定されました。',
		\JSON_ERROR_UTF16					=> '誤ってエンコードされた不正なUTF-16文字を渡されました。',
	];

	/**
	 * PHPの変数受け取り、それをJSON エンコードされた文字列に変換します。
	 *
	 * @param	mixed	$value		エンコード対象となるPHP変数。
	 * @param	int		$options	JSONデコードオプションビットマスク
	 * @param	int		$depth		再帰の深さ
	 * @return	mixed|null			適切な型のPHP変数 デコードに失敗した場合はnull
	 */
	public static function encode ($value, $options = null, $depth = null) {
		$result = json_encode($value, $options ?? static::DEFAULT_OPTIONS, $depth ?? static::DEFAULT_DEPTH);
		if (\JSON_ERROR_NONE !== $error_code = json_last_error()) {
			throw new \ErrorException(sprintf('[%d] %s (%s)', $error_code, static::ERROR_MESSAGES[$error_code] ?? 'UNKOWN', json_last_error_msg()), 0, \E_ERROR, __FILE__, __LINE__);
		}
		return $result;
	}

	/**
	 * JSON エンコードされた文字列を受け取り、それをPHPの変数に変換します。
	 *
	 * @param	string	$value		デコード対象となるjson文字列。
	 * @param	bool	$assoc		返り値のオブジェクトを連想配列に変換するかどうか。
	 * @param	int		$depth		再帰の深さ
	 * @param	int		$options	JSONデコードオプションビットマスク
	 * @return	mixed|null			適切な型のPHP変数 デコードに失敗した場合はnull
	 */
	public static function decode ($value, $assoc = null, $depth = null, $options = null) {
		$result = json_decode($value, $assoc ?? false, $depth ?? static::DEFAULT_DEPTH, $options ?? static::DEFAULT_OPTIONS);
		if (\JSON_ERROR_NONE !== $error_code = json_last_error()) {
			throw new \ErrorException(sprintf('[%d] %s : %s', $error_code, static::ERROR_MESSAGES[$error_code] ?? 'UNKOWN', json_last_error_msg()), 0, \E_ERROR, __FILE__, __LINE__);
		}
		return $result;
	}
}
