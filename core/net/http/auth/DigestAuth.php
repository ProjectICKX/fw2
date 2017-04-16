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

namespace ickx\fw2\core\net\http\auth;

use ickx\fw2\crypt\Hash;
use ickx\fw2\core\net\http\Http;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\core\exception\CoreException;

/**
 * Http Digest Auth Class
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DigestAuth implements interfaces\IAuth, interfaces\IDigestResponse {
	use	traits\DigestResponseTrait;

	/**
	 * @property	bool	ログイン中かどうか
	 * @static
	 */
	private static $_isLogin	= false;

	/**
	 * digest認証を行います。
	 *
	 * @param	string	$realm			領域名：ダイアログで表示される名称 アスキーのみ使用可能
	 * @param	string	$response_a1	A1パート
	 * @return	bool	認証に成功した場合はtrue 失敗した場合はfalse
	 */
	public static function Auth ($realm, $response_a1, $key) {
		//==============================================
		//既に認証に成功している場合は無条件でtrue
		//==============================================
		if (static::$_isLogin) {
			return true;
		}

		//==============================================
		//未認証検証
		//==============================================
		//ダイジェストヘッダが送られてきていない場合のみ、認証ヘッダを出力する
		if (!static::GetDigestAuthHeader()) {
			//認証ヘッダの出力
			static::SendDigestAuthHeader($realm);

			//この段階では未認証なのでfalse
			return false;
		}

		//==============================================
		//認証確認処理
		//==============================================
		//digestヘッダから認証情報を取得
		$digest_data = static::ParseDigestAuthHeader();

		//繰り返し使う値の切り出し
		$username	= $digest_data[static::PARAM_USER_NAME];
		$nonce		= $digest_data[static::PARAM_NONCE];
		$nc			= $digest_data[static::PARAM_NC];
		$cnonce		= $digest_data[static::PARAM_CNONCE];
		$qop		= $digest_data[static::PARAM_QOP];
		$uri		= $digest_data[static::PARAM_URI];
		$response	= $digest_data[static::PARAM_RESPONSE];

		//==============================================
        //認証
        //==============================================
        if ($response !== static::CreateResponse($response_a1, $nonce, $nc, $cnonce, $qop, static::CreateResponseA2($uri, $key))) {
        	//認証に失敗
        	return false;
        }

        //認証済みフラグを立てる
        static::$_isLogin = true;

		//認証済みのためtrue
		return true;
	}

	/**
	 * httpヘッダーからdigestヘッダを取得します。
	 *
	 * @return	string	ダイジェストヘッダ
	 */
	public static function GetDigestAuthHeader () {
		return isset($_SERVER['PHP_AUTH_DIGEST']) ? $_SERVER['PHP_AUTH_DIGEST'] : false;
	}

	/**
	 * ダイジェスト認証用httpレスポンスヘッダを出力します。
	 *
	 * @param	string	$realm	領域名：ダイアログで表示される名称 アスキーのみ使用可能
	 */
	public static function SendDigestAuthHeader ($realm) {
		http_response_code(Http::STATUS_UNAUTHORIZED);
		header(static::MakeAuthenticate($realm));
		exit;
	}

	/**
	 * digest認証ヘッダを解析します。
	 *
	 * @return	mixed	解析済みdigest認証ヘッダ 解析に失敗した場合はfalse
	 */
	public static function ParseDigestAuthHeader () {
		//ダイジェストヘッダの取得
		$http_digest_text = static::GetDigestAuthHeader();

		// データが失われている場合への対応
		$needed_parts = [
			static::PARAM_NONCE		=> 1,
			static::PARAM_NC		=> 1,
			static::PARAM_CNONCE	=> 1,
			static::PARAM_QOP		=> 1,
			static::PARAM_USER_NAME	=> 1,
			static::PARAM_URI		=> 1,
			static::PARAM_RESPONSE	=> 1,
		];

		//DIGESTヘッダの解析
		$pattern = sprintf(static::DIGEST_HEADER_PURSE_PATTERN, implode('|', array_keys($needed_parts)));
		preg_match_all($pattern, $http_digest_text, $matches, \PREG_SET_ORDER);

		$data = [];
		foreach ($matches as $m) {
			$data[$m[1]] = $m[3] ?: $m[4];
			unset($needed_parts[$m[1]]);
		}

		//処理の終了
		return !empty($needed_parts) ? false : $data;
	}

	/**
	 * ハッシュアルゴリズムを設定します。
	 *
	 * @param	string	$algorithm_name	ハッシュアルゴリズム名
	 * @throws	CoreException	未対応のハッシュアルゴリズムを指定された場合
	 */
	public static function SetAlgorithm ($algorithm_name) {
		if (!Hash::Exists($algorithm_name)) {
			throw CoreException::RaiseSystemError('未対応のハッシュアルゴリズムを指定されました。algorithm:%s', [$algorithm_name]);
		}
		static::$_algorithm = $algorithm_name;
	}
}
