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
 * @package		core
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\net\http\auth\traits;

use ickx\fw2\crypt\Hash;
use ickx\fw2\core\net\http\Http;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\core\exception\CoreException;

/**
 * Http Digest Auth Trait
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait DigestResponseTrait {
	/**
	 * ダイジェスト認証用Authenticateを生成します。
	 *
	 * @param	string	$realm	領域名：ダイアログで表示される名称 アスキーのみ使用可能
	 * @return	string	ダイジェスト認証用Authenticate
	 */
	public static function MakeAuthenticate ($realm) {
		return sprintf(
			self::HTTP_HEADER_AUTHENTICATE,
			$realm,
			uniqid(),
			md5($realm)
		);
	}

	/**
	 * A1レスポンスを生成します。
	 *
	 * @param	string	$realm		領域名
	 * @param	string	$user_name	ユーザ名
	 * @param	string	$password	パスワード
	 * @param	string	$key		共有秘密鍵
	 * @return	string	A1レスポンス
	 */
	public static function CreateResponseA1 ($realm, $user_name, $password, $key) {
		return Hash::HmacString(implode(static::RESPONSE_SEPARATOR,[$user_name, $realm, $password]), $key, static::GetAlgorithm());
	}

	/**
	 * A2レスポンスを生成します。
	 *
	 * @param	string	$uri			URI
	 * @param	string	$request_method	リクエストメソッド
	 * @param	string	$key			共有秘密鍵
	 * @return	string	A2レスポンス
	 */
	public static function CreateResponseA2 ($uri, $key, $request_method = null) {
		return Hash::HmacString(implode(static::RESPONSE_SEPARATOR, [$request_method ?: Request::GetMethod(), $uri]), $key, static::GetAlgorithm());
	}

	/**
	 * レスポンスを生成します。
	 *
	 * @param	string	$response_a1	A1レスポンス
	 * @param	string	$nonce			サーバ側で生成した乱数
	 * @param	string	$nc				クライアントからのリクエスト回数(16進数)
	 * @param	string	$cnonce			クライアント側で生成した乱数
	 * @param	string	$qop			保護レベル
	 * @param	string	$response_a2	A2レスポンス
	 * @param	string	$key			共有秘密鍵
	 * @return	string	レスポンス
	 */
	public static function CreateResponse ($response_a1, $nonce, $nc, $cnonce, $qop, $response_a2, $key) {
		return Hash::HmacString(implode(static::RESPONSE_SEPARATOR, [$response_a1, $nonce, $nc, $cnonce, $qop, $response_a2]), $key, static::GetAlgorithm());
	}

	/**
	 * 現在設定されているハッシュアルゴリズムを返します。
	 *
	 * @return	string	現在設定されているハッシュアルゴリズム
	 */
	public static function GetAlgorithm () {
		return static::$_algorithm ?: static::DEFAULT_HASH_ALGORITHM;
	}
}
