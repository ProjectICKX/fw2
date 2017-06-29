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

use ickx\fw2\other\curl\Curl;

/**
 * OAuth2 Class
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class OAuth2 {
	use	\ickx\fw2\traits\magic\Accessor;

	//==============================================
	// クラスコンスト
	//==============================================
	// コードチャレンジメソッド
	//----------------------------------------------
	/**
	 * @var	string	コードチャレンジメソッド：平文
	 * @static
	 */
	protected const CODE_CHALLENGE_METHOD_PLAIN		= 'plain';

	/**
	 * @var	string	コードチャレンジメソッド：SHA256ハッシュ
	 * @static
	 */
	protected const CODE_CHALLENGE_METHOD_SHA256	= 'S256';

	//----------------------------------------------
	// グラントタイプ
	//----------------------------------------------
	/**
	 * @var	string	グラントタイプ：インプリシット
	 * @static
	 */
	protected const GRANT_TYPE_IMPLICIT	= 'implicit';

	/**
	 * @var	string	グラントタイプ：オーサライゼーションコード
	 * @static
	 */
	protected const GRANT_TYPE_AUTHORIZATION_CODE	= 'authorization_code';

	/**
	 * @var	string	グラントタイプ：リソースオーナークレデンシャル
	 * @static
	 */
	protected const GRANT_TYPE_RESOURCE_OWNER_PASSWORD_CREDENTIALS	= 'resource_owner_password_credentials';

	/**
	 * @var	string	グラントタイプ：クライアントクレデンシャル
	 * @static
	 */
	protected const GRANT_TYPE_CLIENT_CREDENTIALS	= 'client_credentials';

	//----------------------------------------------
	// レスポンスタイプ
	//----------------------------------------------
	/**
	 * @var	string	レスポンスタイプ：コード
	 * @static
	 */
	protected const RESPONSE_TYPE_CODE	= 'code';

	/**
	 * @var	string	レスポンスタイプ：トークン
	 * @static
	 */
	protected const RESPONSE_TYPE_TOKEN	= 'token';

	//==============================================
	// オブジェクトプロパティ
	//==============================================
	/**
	 * @var	string	接続先ホスト名：スキーマの指定も必要
	 */
	protected $host					= null;

	/**
	 * @var	string	認可エンドポイントパス
	 */
	protected $authorizePath		= null;

	/**
	 * @var	string	トークンエンドポイントパス
	 */
	protected $accessTokenPath		= null;

	/**
	 * @var	string	クライアントID
	 */
	protected $clientId				= null;

	/**
	 * @var	string	シークレット
	 */
	protected $secret				= null;

	/**
	 * @var	string|array	スコープ
	 */
	protected $scope				= null;

	/**
	 * @var	string	コード受け取り用のリダイレクトURI
	 */
	protected $redirectUri			= null;

	/**
	 * @var	string	コードベリファイア
	 */
	protected $codeVerifier			= null;

	/**
	 * @var	string	コードチャレンジメソッド
	 */
	protected $codeChallengeMethod	= null;

	/**
	 * @var	string	グラントタイプ
	 */
	protected $grantType			= null;

	/**
	 * @var	string	コードチャレンジ
	 */
	protected $codeChallenge		= null;

	/**
	 * @var	string	ステート
	 */
	protected $state				= null;

	/**
	 * @var	Object	アクセストークンなどのトークンインフォ
	 */
	protected $tokenInfo			= null;

	//==============================================
	// スタティックメソッド
	//==============================================
	/**
	 * OAuth2認可処理を初期化します。
	 *
	 * @return	\ickx\fw2\core\net\http\auth\OAuth2	OAuth2インスタンス
	 */
	public static function init () {
		return new static();
	}

	//==============================================
	// パブリックメソッド
	//==============================================
	/**
	 * 認可URLを返します。
	 *
	 * @return	string	認可URL
	 */
	public function getAuthorizeUrl () {
		return sprintf('%s%s?%s', $this->host, $this->authorizePath, http_build_query([
			'response_type'			=> $this->code ?? static::RESPONSE_TYPE_CODE,
			'client_id'				=> $this->clientId,
			'redirect_uri'			=> $this->redirectUri,
			'scope'					=> implode(' ', (array) $this->scope),
			'state'					=> $this->state,
			'code_challenge'		=> $this->codeChallenge,
			'code_challenge_method'	=> $this->codeChallengeMethod ?? static::CODE_CHALLENGE_METHOD_SHA256,
		]));
	}

	/**
	 * 引数で渡されたsteteを検証します。
	 *
	 * @param	string	$state	OAuthサーバから返されたstate
	 * @return	bool	正当なstateの場合はtrue、そうでない場合はfalse
	 */
	public function validState ($state) {
		return $this->state === $state;
	}

	/**
	 * アクセストークンを取得します。
	 *
	 * @param	string	$code	認可コード
	 * @param	string	$state	ステート
	 * @throws	\ErrorException	ステートが不正だった場合
	 * @return	\ickx\fw2\core\net\http\auth\OAuth2	OAuth2インスタンス
	 */
	public function accessToken ($code, $state) {
		if ($this->validState($state)) {
			throw new \ErrorException('stateが不正です。');
		}

		$result = Curl::url(sprintf('%s%s', $this->host, $this->accessTokenPath))->headers([
			'Authorization'	=> sprintf('Basic %s', base64_encode(sprintf('%s:%s', $this->clientId, $this->secret))),
		])->parameters([
			'response_type'			=> $this->code ?? static::RESPONSE_TYPE_CODE,
			'client_id'				=> $this->clientId,
			'redirect_uri'			=> $this->redirectUri,
			'scope'					=> implode(' ', (array) $this->scope),
			'state'					=> $this->state,
			'code_challenge'		=> $this->codeChallenge,
			'code_challenge_method'	=> $this->codeChallengeMethod ?? static::CODE_CHALLENGE_METHOD_SHA256,
		])->bodies([
			'grant_type'		=> $this->grantType ?? static::GRANT_TYPE_AUTHORIZATION_CODE,
			'code'				=> $code,
			'redirect_uri'		=> $this->redirectUri,
			'client_id'			=> $this->clientId,
			'code_verifier'		=> $this->codeVerifier,
		])->exec();

		$this->tokenInfo	= json_decode($result['body']);

		return $this;
	}

	/**
	 * 認証・認可状態を返します。
	 *
	 * @return	bool	認証・認可が有効な場合はtrue、そうでない場合はfalse
	 */
	public function isAuth () {
		return $this->tokenInfo->access_token ?? false;
	}

	//==============================================
	// プロテクテッドメソッド
	//==============================================
	/**
	 * コンストラクタ
	 */
	protected function __construct () {
		$this->codeChallengeMethod	= static::CODE_CHALLENGE_METHOD_SHA256;
		$this->grantType			= static::GRANT_TYPE_AUTHORIZATION_CODE;

		$this->codeChallenge	= $this->generateCodeChallenge();
		$this->state			= $this->generateState();
	}

	/**
	 * code verifier用のランダム文字列を生成します。
	 *
	 * @throws	\ErrorException	code verifierの生成に失敗した場合
	 * @return	string	code verifier
	 */
	protected function generateCodeVerifier () {
		$sp		= ['-'. '/', '_',  '~'];
		$length	= random_int(43, 128);
		$stack	= [];

		for ($i = 0;$i < $length;$i++) {
			switch (random_int(1, 4)) {
				case 1:
					$stack[] = chr(random_int(0x30, 0x39));
					break;
				case 2:
					$stack[] = chr(random_int(0x41, 0x5A));
					break;
				case 3:
					$stack[] = chr(random_int(0x61, 0x7A));
					break;
				case 4:
					$stack[] = $sp[random_int(0, 3)];
					break;
			}
		}

		if (count($stack) !== $length) {
			throw new \ErrorException('code verifierの作成に失敗しました。');
		}

		return $this->codeVerifier = implode('', $stack);
	}

	/**
	 * コードチャレンジを生成します。
	 *
	 * @throws	\ErrorException	不明なコードチャレンジメソッドを指定された場合。
	 * @return	string	コードチャレンジ
	 */
	protected function generateCodeChallenge () {
		switch ($this->codeChallengeMethod) {
			case static::CODE_CHALLENGE_METHOD_SHA256:
				return base64_encode(hash('SHA256', $this->generateCodeVerifier(), true));
			case static::CODE_CHALLENGE_METHOD_PLAIN:
				return $this->generateCodeVerifier();
			default:
				throw new \ErrorException('未定義の code challenge method を指定されています。');
		}
	}

	/**
	 * ステート用ランダム文字列を生成します。
	 *
	 * @return	string	ステート用ランダム文字列。
	 */
	protected function generateState () {
		return session_create_id();
	}
}
