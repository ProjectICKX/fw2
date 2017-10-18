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
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\http;

use ickx\fw2\other\curl\Curl;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\security\validators\Validator;

/**
 * OAuth2認証を扱います。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class OAuth2 {
	use	\ickx\fw2\traits\singletons\Multiton,
		\ickx\fw2\traits\magic\Accessor;

	//==============================================
	// クラスコンスト
	//==============================================
	/**
	 * @var	string	スコープセパレータ
	 * @static
	 */
	public const SCOPE_SEPARATOR	= ' ';

	//----------------------------------------------
	// プロパティ名
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：認証前URL
	 * @static
	 */
	public const PROPERTY_ORIGIN_REQUEST_URL	= 'originRequestUrl';

	/**
	 * @var	string	プロパティ名：コードベリファイア
	 * @static
	 */
	public const PROPERTY_CODE_VERIFIER			= 'codeVerifier';

	/**
	 * @var	string	プロパティ名：コードチャレンジ
	 * @static
	 */
	public const PROPERTY_CODE_CHALLENGE		= 'codeChallenge';

	//----------------------------------------------
	// コードチャレンジメソッド
	//----------------------------------------------
	/**
	 * @var	string	コードチャレンジメソッド：平文
	 * @static
	 */
	public const CODE_CHALLENGE_METHOD_PLAIN	= 'plain';

	/**
	 * @var	string	コードチャレンジメソッド：SHA256ハッシュ
	 * @static
	 */
	public const CODE_CHALLENGE_METHOD_SHA256	= 'S256';

	//----------------------------------------------
	// グラントタイプ
	//----------------------------------------------
	/**
	 * @var	string	グラントタイプ：インプリシット
	 * @static
	 */
	public const GRANT_TYPE_IMPLICIT	= 'implicit';

	/**
	 * @var	string	グラントタイプ：オーサライゼーションコード
	 * @static
	 */
	public const GRANT_TYPE_AUTHORIZATION_CODE	= 'authorization_code';

	/**
	 * @var	string	グラントタイプ：リソースオーナークレデンシャル
	 * @static
	 */
	public const GRANT_TYPE_RESOURCE_OWNER_PASSWORD_CREDENTIALS	= 'resource_owner_password_credentials';

	/**
	 * @var	string	グラントタイプ：クライアントクレデンシャル
	 * @static
	 */
	public const GRANT_TYPE_CLIENT_CREDENTIALS	= 'client_credentials';

	/**
	 * @var	string	グラントタイプ：リフレッシュトークン
	 * @static
	 */
	public const GRANT_TYPE_REFRESH_TOKEN	= 'refresh_token';

	//----------------------------------------------
	// レスポンスタイプ
	//----------------------------------------------
	/**
	 * @var	string	レスポンスタイプ：コード
	 * @static
	 */
	public const RESPONSE_TYPE_CODE	= 'code';

	/**
	 * @var	string	レスポンスタイプ：トークン
	 * @static
	 */
	public const RESPONSE_TYPE_TOKEN	= 'token';

	//----------------------------------------------
	// オーサライゼーションヘッダ
	//----------------------------------------------
	/**
	 * @var	string	オーサライゼーションヘッダ：ベーシック
	 * @static
	 */
	public const AUTHORIZATION_HADER_BASIC	= 'Basic';

	/**
	 * @var	string	オーサライゼーションヘッダ：ベアラー
	 * @static
	 */
	public const AUTHORIZATION_HADER_BEARER	= 'Bearer';

	//==============================================
	// オブジェクトプロパティ
	//==============================================
	/**
	 * @var	\ickx\fw2\auth\http\AuthSession	認証セッション管理用インスタンス
	 */
	protected $authSession		= null;

	/**
	 * @var	bool		認証済み状態管理フラグ
	 */
	protected $isAuthed			= false;

	/**
	 * @var	array		現在のトークン情報
	 */
	protected $token			= [];

	/**
	 * @var	mixed		現在の拡張データ情報
	 */
	protected $expandData		= [];

	/**
	 * @var	mixed		拡張データフィルタ
	 */
	protected $expandDataFilter	= [];

	//----------------------------------------------
	// エンドポイントパス
	//----------------------------------------------
	/**
	 * @var	string	認可エンドポイントパス
	 */
	protected $authorizePath		= null;

	/**
	 * @var	string	トークンエンドポイントパス
	 */
	protected $accessTokenPath		= null;

	//----------------------------------------------
	// 設定値
	//----------------------------------------------
	/**
	 * @var	string	接続先ホスト名：スキーマの指定も必要
	 */
	protected $authHost				= null;

	/**
	 * @var	string	API接続先ホスト名：スキーマの指定も必要
	 */
	protected $authApiHost			= null;

	/**
	 * @var	string	API接続先SSL証明書検証
	 */
	protected $authApiHostSslVerify	= true;

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
	 * @var	string	コード受け取りURL
	 */
	protected $codetUri				= null;

	/**
	 * @var	string	コード受け取りパラメータ名
	 */
	protected $codeParamName		= null;

	/**
	 * @var	array	コード受け取りパラメータに対する検証ルールリスト
	 */
	protected $codeValidateRule		= [];

	/**
	 * @var	string	ステート受け取りパラメータ名
	 */
	protected $stateParamName		= null;

	/**
	 * @var	array	ステート受け取りパラメータに対する検証ルールリスト
	 */
	protected $stateValidateRule	= [];

	/**
	 * @var	string	バリデーションエラーレベル
	 */
	protected $validateErrorLevel	= 'raise_exception';

	/**
	 * @var	callable	アクセストークン受け取り後フィルタ
	 */
	protected $acceccTokenFilter	= null;

	/**
	 * @var	string		リダイレクトURL
	 */
	protected $redirectUrl			= null;

	/**
	 * @var	string		デフォルトの認証後URL
	 */
	protected $defaultAuthedUrl		= null;

	/**
	 * @var	array	挟み込み認証を行わないURL
	 */
	protected $disableAuthPath		= [];

	/**
	 * @var	string	挟み込み認証用オリジンURL
	 */
	protected $originRequestUrl		= null;

	/**
	 * @var	bool	GET以外のメソッドで挟み込み認証が発生した場合、強制的にデフォルトの認証後URLへ飛ばすかどうか
	 */
	protected $isForciblyDefaultAuthedPath	= true;

	//==============================================
	// メソッド
	//==============================================
	/**
	 * 認可が維持されているか確認します。
	 *
	 * @throws	\ErrorException	認可フローが未実装なグラントタイプを渡された場合
	 * @return	bool	認可が維持されている場合はtrue、そうでない場合はfalse
	 */
	public function auth () {
		if ($this->isAuthed()) {
			return true;
		}

		switch ($this->grantType) {
			//==============================================
			// オーサライゼーションコード時の処理
			//==============================================
			case static::GRANT_TYPE_AUTHORIZATION_CODE:
				// 認証迂回パス判定
				$request_path	= ($pos = strpos($_SERVER['REQUEST_URI'], '?')) ? substr($_SERVER['REQUEST_URI'], 0, $pos - 1) : $_SERVER['REQUEST_URI'];
				foreach ($this->disableAuthPath ?? [] as $disable_auth_path) {
					if ($request_path === $disable_auth_path) {
						return true;
					}
				}

				// コード受け取り時の処理
				if ($this->isCodeUrl()) {
					if (!empty($tmpSession = $this->authSession->tmpGet())) {
						try {
							$tmp_session_extra_data	= $tmpSession['extra_data'];
							$state					= $tmp_session_extra_data[$this->stateParamName];
							$origin_url				= $tmp_session_extra_data[static::PROPERTY_ORIGIN_REQUEST_URL];
							$code_verifier			= $tmp_session_extra_data[static::PROPERTY_CODE_VERIFIER];
							$code_challenge			= $tmp_session_extra_data[static::PROPERTY_CODE_CHALLENGE];
							$access_token			= $this->token		= $this->getAccessToken($state, $code_verifier, $code_challenge);
							$expand_data			= $this->expandData	= is_callable($this->expandDataFilter) ? $this->expandDataFilter()($access_token, $tmpSession) : $this->expandData;

							$this->authSession->update($access_token['access_token'], $access_token['refresh_token'], ['access_token' => $access_token, 'expand_data' => $expand_data]);
						} catch (\Throwable $e) {
							throw $e;
						} finally {
							$this->authSession->tmpClose();
						}

						$this->redirectUrl($origin_url);

						return false;
					}

					return false;
				}

				// 認可クッキーが無い場合は認可ページへリダイレクト
				if (empty($auth_session = $this->authSession->get())) {
					// 認証前仮クッキーの発行
					$code_verifier	= $this->generateCodeVerifier();
					$code_challenge	= $this->generateCodeChallenge($code_verifier);
					$state			= $this->generateState();

					$this->authSession->close();

					$this->authSession->tmpUpdate([
						static::PROPERTY_CODE_VERIFIER		=> $code_verifier,
						static::PROPERTY_CODE_CHALLENGE		=> $code_challenge,
						static::PROPERTY_ORIGIN_REQUEST_URL	=> ((bool) $this->isForciblyDefaultAuthedPath && Request::GetMethod() !== 'GET') ? $this->defaultAuthedUrl : is_callable($this->originRequestUrl) ? $this->originRequestUrl()() : $this->originRequestUrl,
						$this->stateParamName				=> $state,
					]);

					// 認可ページへリダイレクト
					$this->redirectUrl($this->getAuthorizeUrl($state, $code_challenge));
					return false;
				}

				// 認可クッキーが存在するので、有効期限を確認する
				$extra_data		= $auth_session['raw_data']['extra_data'];
				$access_token	= $extra_data['access_token'];
				$expand_data	= $extra_data['expand_data'];

				if (time() - $access_token['start_time'] > $access_token['expires_in']) {
					//認可期限切れ
					//トークンリフレッシュを試行する
					try {
						$access_token	= $this->tokenRefresh();
					} catch (\ErrorException $ee) {
						//トークンリフレッシュにも失敗した場合は認可ページへリダイレクト
						// 認証前仮クッキーの発行
						$code_verifier	= $this->generateCodeVerifier();
						$code_challenge	= $this->generateCodeChallenge($code_verifier);
						$state			= $this->generateState();

						$this->authSession->close();

						$this->authSession->tmpUpdate([
							static::PROPERTY_CODE_VERIFIER		=> $code_verifier,
							static::PROPERTY_CODE_CHALLENGE		=> $code_challenge,
							static::PROPERTY_ORIGIN_REQUEST_URL	=> ((bool) $this->isForciblyDefaultAuthedPath && Request::GetMethod() !== 'GET') ? $this->defaultAuthedUrl : is_callable($this->originRequestUrl) ? $this->originRequestUrl()() : $this->originRequestUrl,
							$this->stateParamName				=> $state,
						]);

						// 認可ページへリダイレクト
						$this->redirectUrl($this->getAuthorizeUrl($state, $code_challenge));
						return false;
					}
				}

				// ここまで到達できている場合、有効な認可があると判断する
				// forwordで遷移する場合でも一回のみupdateされるようにする
				$this->authSession->update($access_token['access_token'], $access_token['refresh_token'], ['access_token' => $this->token = $access_token, 'expand_data' => $this->expandData = $expand_data]);

				$this->isAuthed(true);

				return true;
		}

		throw new \ErrorException(sprintf('指定されたgrant typeに対応する認可フローが実装されていません。grant type:%s', $this->grantType));
	}

	/**
	 * 現在の認可状態をリフレッシュします。
	 *
	 * @throws	\ErrorException
	 * @return	bool	認可状態のrefreshに成功した場合はtrue、そうでない場合はfalse
	 */
	public function refresh () {
		if (!$this->isAuthed()) {
			return false;
		}

		switch ($this->grantType) {
			//==============================================
			// オーサライゼーションコード時の処理
			//==============================================
			case static::GRANT_TYPE_AUTHORIZATION_CODE:
				//トークンリフレッシュを試行する
				try {
					$access_token	= $this->tokenRefresh();
				} catch (\Throwable $e) {
					throw $e;
				}

				$this->authSession->update($access_token['access_token'], $access_token['refresh_token'], ['access_token' => $this->token = $access_token, 'expand_data' => $this->expandData = $expand_data]);

				// ここまで到達できている場合、有効な認可があると判断する
				return true;
		}

		throw new \ErrorException(sprintf('指定されたgrant typeに対応する認可フローが実装されていません。grant type:%s', $this->grantType));
	}

	/**
	 * 認可状態を剥奪します。
	 */
	public function deprive () {
		$this->authSession->close();
		$this->authSession->tmpClose();
		$this->isAuthed(false);
	}

	/**
	 * 認可URLを返します。
	 *
	 * @param	string	$state			ステート
	 * @param	string	$code_challenge	コードチャレンジ
	 * @return	string	認可URL
	 */
	public function getAuthorizeUrl ($state, $code_challenge) {
		return sprintf('%s%s?%s', is_callable($this->authHost) ? $this->authHost()() : $this->authHost, $this->authorizePath, http_build_query([
			'response_type'			=> $this->code ?? static::RESPONSE_TYPE_CODE,
			'client_id'				=> $this->clientId,
			'redirect_uri'			=> is_callable($this->codeUri) ? $this->codeUri()() : $this->codeUri,
			'scope'					=> implode(static::SCOPE_SEPARATOR, (array) $this->scope),
			'state'					=> $state,
			'code_challenge_method'	=> $this->codeChallengeMethod ?? ($this->codeChallengeMethod = static::CODE_CHALLENGE_METHOD_SHA256),
			'code_challenge'		=> $code_challenge,
		]));
	}

	/**
	 * 現在のURLがコード受け取りURLかどうか判定します。
	 *
	 * @return	bool	現在のURLがコード受け取りURLの場合true、そうでない場合はfalse
	 */
	public function isCodeUrl () {
		$parameters = Request::GetParameters();
		$code_uri = parse_url(is_callable($this->codeUri) ? $this->codeUri()() : $this->codeUri);
		return isset($parameters[$this->codeParamName]) && isset($parameters[$this->stateParamName])
		&& $code_uri['scheme'] === Request::GetCurrnetProtocol()
		&& $code_uri['host'] === $_SERVER['HTTP_HOST']
		&& $code_uri['path'] === substr($_SERVER['REQUEST_URI'], 0, -1 + -1 * strlen($_SERVER['QUERY_STRING']));
	}

	/**
	 * Code受け取りページの検証を行います。
	 *
	 * @param	string	$state	static::getAuthorizeUrlで使用したステート
	 * @return	array	Code受け取りページの検証に成功した場合は空配列、失敗した場合はエラーメッセージの配列
	 */
	public function validateCode ($state) {
		$parameters = Request::GetParameters();

		$code_validator		= [
			['require',				$this->validateErrorLevel],
			['not_string_empty',	$this->validateErrorLevel],
		];
		foreach ($this->codeValidateRule as $rule_name) {
			$code_validator[] = [$rule_name, $this->validateErrorLevel];
		}

		$state_validator	= [
			['require',				$this->validateErrorLevel],
			['not_string_empty',	$this->validateErrorLevel],
		];
		foreach ($this->stateValidateRule as $rule_name) {
			$state_validator[] = [$rule_name, $this->validateErrorLevel];
		}
		$state_validator[]	= ['===', $state, $this->validateErrorLevel];

		$validate_rule_list = [
			$this->codeParamName	=> $code_validator,
			$this->stateParamName	=> $state_validator,
		];

		return Validator::BulkCheck($parameters, $validate_rule_list);
	}

	/**
	 * アクセストークンを取得します。
	 *
	 * @param	string	$state			static::getAuthorizeUrlで使用したステート
	 * @param	string	$code_verifier	static::getAuthorizeUrlで使用したコードチャレンジと同時に作ったコードベリファイ
	 * @param	string	$code_challenge	static::getAuthorizeUrlで使用したコードチャレンジ
	 * @throws	\ErrorException	アクセストークンの取得に失敗した場合
	 * @return	array	アクセストークン
	 */
	public function getAccessToken ($state, $code_verifier, $code_challenge) {
		if (!empty($errors = $this->validateCode($state))) {
			return $errors;
		}

		$parameters = Request::GetParameters();

		$start_time	= time();

		$result = Curl::url($curl_url = sprintf('%s%s', is_callable($this->authApiHost) ? $this->authApiHost()() : $this->authApiHost, $this->accessTokenPath))->headers($curl_headers = [
			'Authorization'	=> sprintf('%s %s', static::AUTHORIZATION_HADER_BASIC, base64_encode(sprintf('%s:%s', $this->clientId, $this->secret))),
		])->parameters($curl_parameters = [
			'response_type'			=> static::RESPONSE_TYPE_CODE,
			'client_id'				=> $this->clientId,
			'redirect_uri'			=> is_callable($this->codeUri) ? $this->codeUri()() : $this->codeUri,
			'scope'					=> implode(static::SCOPE_SEPARATOR, (array) $this->scope),
			'state'					=> $state,
			'code_challenge_method'	=> $this->codeChallengeMethod,
			'code_challenge'		=> $code_challenge,
		])->bodies($curl_bodies = [
			'grant_type'		=> $this->grantType ?? static::GRANT_TYPE_AUTHORIZATION_CODE,
			'code'				=> $parameters[$this->codeParamName],
			'redirect_uri'		=> is_callable($this->codeUri) ? $this->codeUri()() : $this->codeUri,
			'client_id'			=> $this->clientId,
			'code_verifier'		=> $code_verifier,
		])->sslVerify($this->authApiHostSslVerify)->exec();

		$header = $result['header'];
		switch ($header['http_code']) {
			case 200:
				$access_token = (array) (is_callable($this->acceccTokenFilter) ? $this->acceccTokenFilter()($result) : $result['body'] ?? '');
				$access_token['start_time']	= $start_time;
				return $access_token;
			case 403:
				break;
			case 0:
				throw new \ErrorException(sprintf('access token取得時にエラーが発生しました。http status code:%s, url:%s', $header['http_code'], $header['url']));
				break;
		}

		throw new \ErrorException(sprintf('access token取得時にエラーが発生しました。http status code:%s, message:%s', $header['http_code'], $result['body']));
	}

	/**
	 * トークンをリフレッシュします。
	 *
	 * @throws	\ErrorException	トークンのリフレッシュに失敗した場合
	 * @return	array	リフレッシュ済みのアクセストークン
	 */
	public function tokenRefresh () {
		$start_time	= time();

		$result = Curl::url(sprintf('%s%s', is_callable($this->authApiHost) ? $this->authApiHost()() : $this->authApiHost, $this->accessTokenPath))->headers([
			'Authorization'	=> sprintf('%s %s', static::AUTHORIZATION_HADER_BASIC, base64_encode(sprintf('%s:%s', $this->clientId, $this->secret))),
		])->bodies([
			'grant_type'		=> static::GRANT_TYPE_REFRESH_TOKEN,
			'refresh_token'		=> $this->token['refresh_token'],
		])->sslVerify($this->authApiHostSslVerify)->exec();

		$header = $result['header'];
		switch ($header['http_code']) {
			case 200:
				$access_token = (array) (is_callable($this->acceccTokenFilter) ? $this->acceccTokenFilter()($result) : $result['body'] ?? '');
				$access_token['start_time']	= $start_time;
				return $access_token;
			case 403:
				break;
		}

		throw new \ErrorException(sprintf('token refresh時にエラーが発生しました。http status code:%s, message:%s', $header['http_code'], $result['body']));
	}

	/**
	 * Auth Sessionの設定を一括で取得・設定します。
	 *
	 * @param	array	...$config	設定
	 * @return	\ickx\fw2\auth\http\AuthSession	設定済みのAuthSession
	 */
	public function authSession (...$config) {
		if (empty($config)) {
			return $this->authSession;
		}
		$this->authSession->config($config[0])->adjust();
		return $this->authSession;
	}

	/**
	 * コンフィグを纏めて取得・設定します。
	 *
	 * @param	array	...$config	コンフィグ
	 * @return	<array|\ickx\fw2\auth\http\OAuth2>	配列化した設定または、設定済みのこのインスタンス
	 */
	public function config (...$config) {
		if (empty($config)) {
			$ret = [];
			foreach (static::PROPERTY_LIST as $property_name) {
				$ret[$property_name] = $this->$property_name ?? null;
			}
			return $ret;
		}

		foreach ($config[0] as $property_name => $value) {
			$this->$property_name = $value;
		}

		return $this;
	}

	/**
	 * 現在のインスタンスを参照渡しで引き渡します。
	 *
	 * @param	null	$bind
	 * @return	\ickx\fw2\auth\http\OAuth2	自分自身のインスタンス
	 */
	public function capture (&$bind) {
		$bind = $this;
		return $this;
	}

	//==============================================
	// プロテクテッドメソッド
	//==============================================
	/**
	 * コンストラクタ
	 */
	protected function __construct () {
		$this->authSession		= AuthSession::init()->owner($this);
	}

	/**
	 * code verifier用のランダム文字列を生成します。
	 *
	 * @throws	\ErrorException	code verifierの生成に失敗した場合
	 * @return	string	code verifier
	 */
	protected function generateCodeVerifier () {
		$sp		= ['-', '/', '_',  '~'];
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

		return implode('', $stack);
	}

	/**
	 * コードチャレンジを生成します。
	 *
	 * @throws	\ErrorException	不明なコードチャレンジメソッドを指定された場合。
	 * @return	string	コードチャレンジ
	 */
	protected function generateCodeChallenge ($code_verifier) {
		switch ($this->codeChallengeMethod) {
			case static::CODE_CHALLENGE_METHOD_SHA256:
				return base64_encode(hash('SHA256', $code_verifier, true));
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
