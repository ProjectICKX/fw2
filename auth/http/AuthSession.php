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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\http;

use ickx\fw2\auth\data_store\{Files, Memcached};
use ickx\fw2\compression\CompressionGz;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\crypt\{Hash, OpenSSL};
use ickx\fw2\other\json\Json;

/**
 * 認証セッションを管理します。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class AuthSession implements \ickx\fw2\auth\interfaces\IAuthSession {
	use	\ickx\fw2\traits\magic\Accessor;

	//==============================================
	// プロパティ
	//==============================================
	/**
	 * @var	\ickx\fw2\auth\http\DigestAuth	ダイジェスト認証計算処理用インスタンス
	 */
	protected $digestAuth	= null;

	/**
	 * @var	Object	オーナーインスタンスへの参照
	 */
	protected $owner		= null;

	//==============================================
	// 共通設定
	//==============================================
	/**
	 * @var	array	設定のデフォルト値
	 * @static
	 */
	protected static $defaultConfig	= [
		// 認証前セッション
		self::PROPERTY_TMP_VALID_ALTER_LV		=> self::DEFAULT_VALID_ALTER_LV,
		self::PROPERTY_TMP_COOKIE_NAME_PREFIX	=> self::DEFAULT_TMP_COOKIE_NAME_PREFIX,
		self::PROPERTY_TMP_SESSION_EXPIRE		=> self::DEFAULT_TMP_SESSION_EXPIRE,
		self::PROPERTY_TMP_COOKIE_LIFETIME		=> self::DEFAULT_TMP_COOKIE_LIFETIME,
		self::PROPERTY_TMP_COOKIE_PATH			=> self::DEFAULT_COOKIE_PATH,
		self::PROPERTY_TMP_SAVE_HANDLER			=> self::DEFAULT_SAVE_HANDLER_TYPE,
		self::PROPERTY_TMP_SAVE_PATH			=> self::DEFAULT_SAVE_PATH,

		// 認証セッション
		self::PROPERTY_COOKIE_SECURE			=> self::COOKIE_SECURE_IN_SECURE,
		self::PROPERTY_VALID_ALTER_LV			=> self::DEFAULT_VALID_ALTER_LV,
		self::PROPERTY_COOKIE_NAME_PREFIX		=> self::DEFAULT_COOKIE_NAME_PREFIX,
		self::PROPERTY_SESSION_EXPIRE			=> self::DEFAULT_SESSION_EXPIRE,
		self::PROPERTY_COOKIE_LIFETIME			=> self::DEFAULT_COOKIE_LIFETIME,
		self::PROPERTY_COOKIE_PATH				=> self::DEFAULT_COOKIE_PATH,
		self::PROPERTY_COOKIE_DOMAIN			=> self::DEFAULT_COOKIE_DOMAIN,
		self::PROPERTY_COOKIE_SECURE			=> self::DEFAULT_COOKIE_SECURE,
		self::PROPERTY_SAVE_HANDLER				=> self::DEFAULT_SAVE_HANDLER_TYPE,
		self::PROPERTY_SAVE_PATH				=> self::DEFAULT_SAVE_PATH,
		self::PROPERTY_PROBABILITY				=> self::DEFAULT_PROBABILITY,
		self::PROPERTY_DIVISOR					=> self::DEFAULT_DIVISOR,
	];

	/**
	 * @var	string	認証設定名
	 */
	protected $name				= null;

	/**
	 * @var	string	クッキー有効ドメイン名
	 */
	protected $cookieDomain		= null;

	/**
	 * @var	string	クッキーセキュアモード
	 */
	protected $cookieSecure		= true;

	//----------------------------------------------
	// ガベージコレクタ発動条件
	// probability / divisor の確率で実施
	//----------------------------------------------
	/**
	 * @var	int		ガベージコレクタ発動確率
	 */
	protected $probability		= null;

	/**
	 * @var	int		ガベージコレクタ発動確率：除数部
	 */
	protected $divisor			= null;

	//==============================================
	// 認証前セッション
	//==============================================
	/**
	 * @var	string	認証前セッション：クライアントクッキー暗号化キー
	 */
	protected $tmpClientKey				= null;

	/**
	 * @var	string	認証前セッション：クライアントクッキー暗号化ソルト
	 */
	protected $tmpClientSalt			= null;

	/**
	 * @var	string	認証前セッション：サーバデータ暗号化キー
	 */
	protected $tmpServerKey				= null;

	/**
	 * @var	string	認証前セッション：サーバデータ暗号化ソルト
	 */
	protected $tmpServerSalt			= null;

	/**
	 * @var	array	認証前セッション：ハッシュストレッチ設定 整数の要素を3つ指定する 例） [3, 1, 2]
	 */
	protected $tmpStretcher				= null;

	/**
	 * @var	int		認証前セッション：攪乱文字列長
	 */
	protected $tmpSeparatorLength		= null;

	/**
	 * @var	string	認証前セッション：検証厳密度レベル
	 */
	protected $tmpValidAlterLv			= null;

	/**
	 * @var	string	認証前セッション：クッキー名プリフィックス
	 */
	protected $tmpCookieNamePrefix		= null;

	/**
	 * @var	string	認証前セッション：サーバデータ生存時間
	 */
	protected $tmpSessionExpire			= null;

	/**
	 * @var	string	認証前セッション：クッキーライフタイム
	 */
	protected $tmpCookieLifetime		= null;

	/**
	 * @var	string	認証前セッション：クッキー名
	 */
	protected $tmpCookieName			= null;

	/**
	 * @var	string	認証前セッション：クッキー有効パス
	 */
	protected $tmpCookiePath			= null;

	/**
	 * @var	string	認証前セッション：サーバデータセーブハンドラ
	 */
	protected $tmpSaveHandler			= null;

	/**
	 * @var	string	認証前セッション：サーバデータ保存パス
	 */
	protected $tmpSavePath				= null;

	/**
	 * @var	object	認証前セッション：サーバハンドラインスタンス
	 * @see \ickx\fw2\auth\data_store\Files
	 * @see \ickx\fw2\auth\data_store\Memcached
	 */
	protected $tmpSessionSaveHandler	= null;

	//==============================================
	// 認証セッション
	//==============================================
	/**
	 * @var	bool	認証セッション：セッション自体の検証を厳密に行うかどうか
	 */
	protected $strict				= false;

	/**
	 * @var	string	認証セッション：クライアントクッキー暗号化キー
	 */
	protected $clientKey			= null;

	/**
	 * @var	string	認証セッション：クライアントクッキー暗号化ソルト
	 */
	protected $clientSalt			= null;

	/**
	 * @var	string	認証セッション：サーバデータ暗号化キー
	 */
	protected $serverKey			= null;

	/**
	 * @var	string	認証セッション：サーバデータ暗号化ソルト
	 */
	protected $serverSalt			= null;

	/**
	 * @var	array	認証セッション：ハッシュストレッチ設定 整数の要素を3つ指定する 例） [3, 1, 2]
	 */
	protected $stretcher			= null;

	/**
	 * @var	int		認証セッション：攪乱文字列長
	 */
	protected $separatorLength		= null;

	/**
	 * @var	string	認証セッション：検証厳密度レベル
	 */
	protected $validAlterLv			= null;

	/**
	 * @var	string	認証セッション：クッキー名プリフィックス
	 */
	protected $cookieNamePrefix		= null;

	/**
	 * @var	string	認証セッション：サーバデータ生存時間
	 */
	protected $sessionExpire		= null;

	/**
	 * @var	string	認証セッション：クッキーライフタイム
	 */
	protected $cookieLifetime		= null;

	/**
	 * @var	string	認証セッション：クッキー名
	 */
	protected $cookieName			= null;

	/**
	 * @var	string	認証セッション：クッキー有効パス
	 */
	protected $cookiePath			= null;

	/**
	 * @var	string	認証セッション：サーバデータセーブハンドラ
	 */
	protected $saveHandler			= null;

	/**
	 * @var	string	認証セッション：サーバデータ保存パス
	 */
	protected $savePath				= null;

	/**
	 * @var	object	認証セッション：サーバハンドラインスタンス
	 * @see \ickx\fw2\auth\data_store\Files
	 * @see \ickx\fw2\auth\data_store\Memcached
	 */
	protected $sessionSaveHandler	= null;

	//==============================================
	// メソッド
	//==============================================
	/**
	 * AuthSession インスタンスを作成し、返します。
	 *
	 * @param	string	$name	認証名
	 * @return \ickx\fw2\auth\http\AuthSession
	 */
	public static function init ($name = null) {
		$instance = new static();
		$instance->name($name ?? static::DEFAULT_NAME);
		return $instance;
	}

	/**
	 * コンストラクタ
	 *
	 * @see	\ickx\fw2\auth\http\DigestAuth
	 */
	protected function __construct () {
		$this->digestAuth = DigestAuth::init()->owner($this);
	}

	/**
	 * ダイジェスト認証インスタンスへの設定を行います。
	 *
	 * @param	array	...$config	設定値
	 * @return	<\ickx\fw2\auth\http\DigestAuth|\ickx\fw2\auth\http\AuthSession>	設定が行われた場合はDigestAuth、そうでない場合は自分自身のインスタンス
	 */
	public function digestAuth (...$config) {
		if (empty($config)) {
			return $this->digestAuth;
		}
		$this->digestAuth->config($config[0])->adjust();
		return $this;
	}

	/**
	 * 自分から見て最上位のowner インスタンスを返します。
	 *
	 * @return	object	最上位のownerインスタンス
	 */
	public function rootOwner () {
		$instance = $this;
		while (property_exists($instance, 'owner')) {
			$instance = $instance->owner();
		};
		return $instance;
	}

	/**
	 * コンフィグを纏めて取得・設定します。
	 *
	 * @param	array	...$config	コンフィグ
	 * @return	<array|\ickx\fw2\auth\http\AuthSession>
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
	 * 現在設定済みのコンフィグをデフォルト値を用いて調整します。
	 *
	 * @return	\ickx\fw2\auth\http\AuthSession
	 */
	public function adjust () {
		foreach (static::$defaultConfig as $name => $value) {
			if (is_null($this->$name)) {
				$this->$name = $value;
			}
		}
		return $this;
	}

	//==============================================
	// 認証前セッション
	//==============================================
	/**
	 * 認証前セッションの値を更新します。
	 *
	 * @param	array	$data	セッションに登録したい値
	 */
	public function tmpUpdate ($data) {
		$user_name	= Hash::String(Json::encode($data));
		$password	= '';

		$uri		= $this->digestAuth->uri() ?? static::getDefaultUri();
		$method		= $this->digestAuth->method() ?? Request::GetMethod();
		$realm		= $this->digestAuth->realm() ?? static::getDefaultRealm();

		if (random_int(0, $this->divisor) < $this->probability) {
			$this->tmpGc();
		}

		$dummy_user_name	= Hash::CreateRandomHash(microtime(true).random_int(0, 1000), $this->tmpClientKey, $this->tmpClientSalt, $this->tmpSeparatorLength);
		$dummy_realm		= Hash::CreateRandomHash($realm.microtime(true).random_int(0, 1000), $this->tmpClientKey, $this->tmpClientSalt, $this->tmpSeparatorLength);

		$cnonce_seed		= Hash::CreateRandomHash($dummy_user_name . random_int(0, 1000) . $dummy_realm, $this->tmpClientSalt, $this->tmpClientKey, $this->tmpSeparatorLength);

		if ($this->getTmpCookieName() && !empty($old_server_data = $this->tmpGet())) {
			if ($this->strict) {
				++$old_server_data[$this->digestAuth::PROPERTY_NC];
				$this->digestAuth->nonce(Hash::CreateRandomHash(random_int(0, 1000) . $old_server_data[$this->digestAuth::PROPERTY_NC], $this->tmpClientSalt, $this->tmpClientKey, $this->tmpSeparatorLength))
				->nc($old_server_data[$this->digestAuth::PROPERTY_NC])
				->cnonce(Hash::CreateRandomHash($cnonce_seed . $old_server_data[$this->digestAuth::PROPERTY_NC], $this->tmpClientKey, $this->tmpClientSalt, $this->tmpSeparatorLength));
			} else {
				$this->digestAuth->nonce($old_server_data[$this->digestAuth::PROPERTY_NONCE])
				->nc(1)
				->cnonce($old_server_data[$this->digestAuth::PROPERTY_CNONCE]);
			}
		} else {
			$this->digestAuth->nonce(Hash::CreateRandomHash(random_int(0, 1000) . 1, $this->tmpClientSalt, $this->tmpClientKey, $this->tmpSeparatorLength))
			->nc(1)
			->cnonce(Hash::CreateRandomHash($cnonce_seed . 1, $this->tmpClientKey, $this->tmpClientSalt, $this->tmpSeparatorLength));
		}

		//クライアントクッキー準備
		$client_data = $this->createAuthData($dummy_user_name, $password, $uri);
		$client_data['shadow']	= Hash::CreateRandomHash($this->tmpClientKey, $this->tmpServerSalt, $dummy_user_name);

		//サーバデータ準備
		$server_data = $this->createAuthData($dummy_user_name, $password, $uri);
		$server_data['shadow']		= $dummy_user_name;
		$server_data['cnonce_seed']	= $cnonce_seed;
		$server_data['extra_data']	= $data;

		$dummy_password	= Hash::HmacStringStretching($this->tmpStretcher[2], $password, $user_name);

		//更新
		$this->tmpUpdateData($user_name, $dummy_password, $server_data);
		$this->tmpUpdateCookie($dummy_user_name, $dummy_password, $client_data);
	}

	/**
	 * 認証前セッションのサーバデータを更新します。
	 *
	 * @param	string	$user_name		ユーザ名
	 * @param	string	$dummy_password	ダミーパスワード
	 * @param	array	$server_data	サーバ保存データ
	 */
	public function tmpUpdateData ($user_name, $dummy_password, $server_data) {
		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($this->tmpCookieName ?? $this->tmpCookieName = $this->currentTmpCookieName());
		}

		$hmac_key				= Hash::HmacStringStretching($this->tmpStretcher[0], $this->tmpServerSalt, $dummy_password);

		$data	= [
			'name'				=> $this->name,
			'user_name'			=> $user_name,
			'dummy_password'	=> $dummy_password,
			'raw_data'			=> OpenSSL::EncryptRandom(CompressionGz::CompressSerializedVariable($server_data), $dummy_password, $this->tmpServerSalt, $hmac_key, $this->tmpSeparatorLength),
		];

		$data	= OpenSSL::EncryptRandom(CompressionGz::CompressSerializedVariable($data), $this->tmpCookieName, $this->tmpServerSalt, $this->tmpServerKey, $this->tmpSeparatorLength);

		$this->tmpSessionSaveHandler->save($data, $this->tmpSessionExpire);
	}

	/**
	 * 認証前セッションクッキーを更新します。
	 *
	 * @param	string	$dummy_user_name	ダミーユーザ名
	 * @param	string	$dummy_password		ダミーパスワード
	 * @param	array	$client_data	クライアント側保存データ
	 * @return	bool	クッキーを送出できている場合はtrue、そうでない場合はfalse
	 */
	public function tmpUpdateCookie ($dummy_user_name, $dummy_password, $client_data) {
		$dummy_client_salt	= Hash::HmacStringStretching($this->tmpStretcher[1], $this->tmpClientSalt, $this->tmpClientKey);
		$dummy_password		= Hash::HmacStringStretching($this->tmpStretcher[2], $dummy_password, $dummy_user_name);
		$hmac_key			= Hash::HmacStringStretching($this->tmpStretcher[0], $dummy_client_salt, $dummy_password);

		$value				= OpenSSL::EncryptRandom(CompressionGz::CompressVariable($client_data), $dummy_password, $dummy_client_salt, $hmac_key, $this->tmpSeparatorLength);

		$tmp_cookie_name	= $this->tmpCookieName ?? $this->tmpCookieName = $this->currentTmpCookieName();

		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($tmp_cookie_name);
		}

		$cookie_path		= $this->tmpCookiePath;;
		$cookie_domain		= (is_callable($this->cookieDomain) ? $this->cookieDomain()() : $this->cookieDomain) ?? $_SERVER['SERVER_NAME'];
		$cookie_secure		= $this->cookieSecure;
		$expire				= $this->tmpCookieLifetime > 0 ? time() + $this->tmpCookieLifetime : $this->tmpCookieLifetime;

		$set_cookie_data = [$tmp_cookie_name, $value, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY];
		if (!setcookie(...$set_cookie_data)) {
			if (headers_sent($file, $line)) {
				throw new \ErrorException(sprintf('クッキーの発行に失敗しました。%s (%s) にて既にHTTP Response Headerの出力が行われています。%s', $file, $line, implode(', ', $set_cookie_data)));
			} else {
				throw new \ErrorException(sprintf('クッキーの発行に失敗しました。%s', implode(', ', $set_cookie_data)));
			}
		}
	}

	/**
	 * 認証前セッションのGCを行います。
	 */
	public function tmpGc () {
		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($this->tmpCookieName ?? ($this->tmpCookieName = $this->currentTmpCookieName()));
		}
		$this->tmpSessionSaveHandler->gc($this->tmpSessionExpire, $this->tmpCookieNamePrefix);
	}

	/**
	 * 認証前セッションを取得します。
	 *
	 * @return	array	現在の認証前セッション情報、そもそも認証前セッションが始まっていない場合は空配列が返る。
	 */
	public function tmpGet () {
		if (random_int(0, $this->divisor) < $this->probability) {
			$this->tmpGc();
		}

		$server_data = $this->tmpGetOnServer();
		if (empty($server_data) || $server_data === ['raw_data' => false]) {
			return [];
		}

		$client_data = $this->tmpGetOnCookie($server_data['raw_data']['shadow'], $server_data['dummy_password']);
		if ($client_data === false) {
			return [];
		}

		return $this->tmpValid($server_data, $client_data) ? ($server_data['raw_data'] ?? []) : [];
	}

	/**
	 * サーバ上の認証前セッションデータを取得します。
	 *
	 * @return	array	認証前セッションデータ
	 */
	public function tmpGetOnServer () {
		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($this->tmpCookieName ?? ($this->tmpCookieName = $this->getTmpCookieName()));
		}

		if ($this->tmpCookieName === null) {
			return [];
		}

		if (false === $value = $this->tmpSessionSaveHandler->load()) {
			$this->tmpClose();
			throw new \ErrorException('検証前セッションデータの取得に失敗しました。');
		}

		$data				= OpenSSL::DecryptRandom($value, $this->tmpCookieName, $this->tmpServerSalt, $this->tmpServerKey, $this->tmpSeparatorLength);
		if ($data === false) {
			throw new \ErrorException('複合に失敗しました。');
		}

		$data				= CompressionGz::UnCompressSerializedVariable($data);

		$dummy_password		= $data['dummy_password'];
		$hmac_key			= Hash::HmacStringStretching($this->tmpStretcher[0], $this->tmpServerSalt, $dummy_password);
		$data['raw_data']	= CompressionGz::UnCompressSerializedVariable(OpenSSL::DecryptRandom($data['raw_data'], $dummy_password, $this->tmpServerSalt, $hmac_key, $this->tmpSeparatorLength));

		return $data;
	}

	/**
	 * クッキー上の認証前セッションデータを取得します。
	 *
	 * @param	string	$dummy_user_name	ダミーユーザ名
	 * @param	string	$dummy_password		ダミーパスワード
	 * @return	array	認証前セッションデータ
	 */
	public function tmpGetOnCookie ($dummy_user_name, $dummy_password) {
		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($this->tmpCookieName ?? ($this->tmpCookieName = $this->getTmpCookieName()));
		}

		$cookie_value		= $_COOKIE[$this->tmpCookieName];

		$dummy_client_salt	= Hash::HmacStringStretching($this->tmpStretcher[1], $this->tmpClientSalt, $this->tmpClientKey);
		$dummy_password		= Hash::HmacStringStretching($this->tmpStretcher[2], $dummy_password, $dummy_user_name);
		$hmac_key			= Hash::HmacStringStretching($this->tmpStretcher[0], $dummy_client_salt, $dummy_password);

		return CompressionGz::UnCompressVariable(OpenSSL::DecryptRandom($cookie_value, $dummy_password, $dummy_client_salt, $hmac_key, $this->tmpSeparatorLength));
	}

	/**
	 * 認証前セッションを閉じます。
	 */
	public function tmpClose () {
		if (!$this->enableTmpSessionSaveHandler()) {
			$this->setupTmpSessionSaveHandler()->tmpSessionSaveHandler->cookieName($this->tmpCookieName ?? ($this->tmpCookieName = $this->getTmpCookieName()));
		}

		if (is_null($this->tmpCookieName)) {
			return null;
		}

		//認証前セッション情報の削除
		$this->tmpSessionSaveHandler->remove($this->tmpCookieName);

		//クライアント側クッキーの削除
		$cookie_path		= $this->tmpCookiePath;
		$cookie_domain		= (is_callable($this->cookieDomain) ? $this->cookieDomain()() : $this->cookieDomain) ?? $_SERVER['SERVER_NAME'];
		$cookie_secure		= $this->cookieSecure;

		$expire	= time() - 1800;
		$set_cookie_data = [$this->tmpCookieName, null, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY];
		setcookie(...$set_cookie_data);
	}

	/**
	 * 認証前セッションを検証します。
	 *
	 * @param	array	$server_data	サーバ側データ
	 * @param	array	$client_data	クライアント側データ
	 * @throws	\ErrorException	認証に異常が認められた場合
	 * @return	bool	認証が正常な場合はtrue、セッションが始まっていない場合はfalse
	 */
	public function tmpValid ($server_data, $client_data) {
		if ($server_data === ['raw_data' => false] && $client_data === false) {
			return false;
		}

		$server_data	= $server_data['raw_data'];
		$server_data[$this->digestAuth::PROPERTY_USER_NAME]	= $server_data['shadow'];

		if (!Hash::ValidRandomHash($client_data[$this->digestAuth::PROPERTY_CNONCE], $server_data['cnonce_seed'] . $server_data[$this->digestAuth::PROPERTY_NC], $this->tmpClientKey, $this->tmpClientSalt, $this->tmpSeparatorLength)) {
			return false;
		}

		$check_target_list = [
			$this->digestAuth::PROPERTY_USER_NAME,
			$this->digestAuth::PROPERTY_REALM,
			$this->digestAuth::PROPERTY_NC,
			$this->digestAuth::PROPERTY_NONCE,
			$this->digestAuth::PROPERTY_CNONCE,
			$this->digestAuth::PROPERTY_URI,
			$this->digestAuth::PROPERTY_RESPONSE,
		];

		foreach ($check_target_list as $name) {
			if (($server_data[$name] ?? null) !== ($client_data[$name] ?? null)) {
				throw new \ErrorException(sprintf('認証前セッションに齟齬があります。name:%s, server:%s, client:%s', $name, $server_data[$name] ?? '未設定', $client_data[$name] ?? '未設定'));
			}
		}

		return true;
	}

	/**
	 * Cookieから認証前クッキー名を取得します。
	 *
	 * @return	string	認証前クッキー名
	 */
	public function getTmpCookieName () {
		$prefix_length	= mb_strlen($this->tmpCookieNamePrefix);

		foreach ($_COOKIE as $cookie_name => $value) {
			if (($prefix_length === 0 || mb_substr($cookie_name, 0, $prefix_length) === $this->tmpCookieNamePrefix) && strlen(mb_substr($cookie_name, $prefix_length)) === 64) {
				return $cookie_name;
			}
		}

		return null;
	}

	/**
	 * 現在の認証前クッキー名を返します。
	 *
	 * 認証前クッキー名が存在しない場合は作成して返します。
	 *
	 * @param	bool	$regenerate	強制再発行フラグ
	 * @throws	\ErrorException	新規発行認証前セッションクッキー名が既存と重複した場合
	 * @return	string	現在の認証前クッキー名
	 */
	public function currentTmpCookieName ($regenerate = false) {
		if (!$regenerate) {
			$cookie_name = $this->getTmpCookieName();
			if (!is_null($cookie_name)) {
				return $cookie_name;
			}
		}

		for ($i = 0;$i < 100;$i++) {
			if (!$this->tmpSessionSaveHandler->exists($cookie_name = sprintf('%s%s', $this->tmpCookieNamePrefix, bin2hex(random_bytes(32)))) && !is_null($cookie_name)) {
				return $cookie_name;
			}
		}

		throw new \ErrorException(sprintf('新規発行認証前セッションクッキー名が重複しました。最後の一つは%sです。', $cookie_name));
	}

	/**
	 * 認証前セッションハンドラが有効になっているか確認します。
	 *
	 * @return	bool	認証前セッションハンドラが有効な場合はtrue、そうでない場合はfalse
	 */
	public function enableTmpSessionSaveHandler () {
		return $this->tmpSessionSaveHandler !== null;
	}

	/**
	 * 認証前セッションハンドラを準備します。
	 *
	 * @return	\ickx\fw2\auth\traits\AuthSession	認証前セッションハンドラが準備済みのこのインスタンス
	 */
	public function setupTmpSessionSaveHandler () {
		switch ($this->tmpSaveHandler) {
			case static::SAVE_HANDLER_TYPE_FILES:
				$this->tmpSessionSaveHandler	= new Files($this->tmpSavePath ?? sys_get_temp_dir());
				break;
			case static::SAVE_HANDLER_TYPE_MEMCACHED:
				$this->tmpSessionSaveHandler	= new Memcached($this->tmpSavePath);
				break;
			default:
				throw new \ErrorException('認証前セッションハンドラのタイプに有効な値が指定されていません。', 0, \E_USER_ERROR);
		}

		return $this;
	}

	//==============================================
	// 認証済みセッション
	//==============================================
	/**
	 * 認証セッションを更新します。
	 *
	 * 併せて認証セッションクッキーの発行及び、認証セッションデータのサーバ保存を行います。
	 *
	 * @param	string	$user_name	ユーザ名
	 * @param	string	$password	パスワード
	 * @param	bool	$replace	認証セッション名を新しいセッション名に付け替える
	 */
	public function update ($user_name, $password, $extra_data = [], $replace = false) {
		$uri		= $this->digestAuth->uri() ?? static::getDefaultUri();
		$method		= $this->digestAuth->method() ?? Request::GetMethod();
		$realm		= $this->digestAuth->realm() ?? static::getDefaultRealm();

		if (random_int(0, $this->divisor) < $this->probability) {
			$this->gc();
		}

		$dummy_realm		= Hash::CreateRandomHash($realm.microtime(true).random_int(0, 1000), $this->clientKey, $this->clientSalt, $this->separatorLength);

		if ($this->getCookieName() && !empty($old_server_data = $this->get())) {
			$dummy_user_name	= $old_server_data['raw_data']['maru'];
			$cnonce_seed		= Hash::CreateRandomHash($dummy_user_name . random_int(0, 1000) . $dummy_realm, $this->clientSalt, $this->clientKey, $this->separatorLength);

			if ($this->strict) {
				++$old_server_data['raw_data'][$this->digestAuth::PROPERTY_NC];
				$this->digestAuth->nonce(Hash::CreateRandomHash(random_int(0, 1000) . $old_server_data['raw_data'][$this->digestAuth::PROPERTY_NC], $this->clientSalt, $this->clientKey, $this->separatorLength))
				->nc($old_server_data['raw_data'][$this->digestAuth::PROPERTY_NC])
				->cnonce(Hash::CreateRandomHash($cnonce_seed . $old_server_data['raw_data'][$this->digestAuth::PROPERTY_NC], $this->clientKey, $this->clientSalt, $this->separatorLength));
			} else {
				$this->digestAuth->nonce($old_server_data['raw_data'][$this->digestAuth::PROPERTY_NONCE])
				->nc(1)
				->cnonce($old_server_data['raw_data'][$this->digestAuth::PROPERTY_CNONCE]);
			}
		} else {
			$dummy_user_name	= Hash::CreateRandomHash(microtime(true).random_int(0, 1000), $this->clientKey, $this->clientSalt, $this->separatorLength);
			$cnonce_seed		= Hash::CreateRandomHash($dummy_user_name . random_int(0, 1000) . $dummy_realm, $this->clientSalt, $this->clientKey, $this->separatorLength);

			$this->digestAuth->nonce(Hash::CreateRandomHash(random_int(0, 1000) . 1, $this->clientSalt, $this->clientKey, $this->separatorLength))
			->nc(1)
			->cnonce(Hash::CreateRandomHash($cnonce_seed . 1, $this->clientKey, $this->clientSalt, $this->separatorLength));
		}

		if ($replace) {
			$this->replace();
		}

		//クライアントクッキー準備
		$client_data = $this->createAuthData($dummy_user_name, $password, $uri);
		$client_data['shadow']	= Hash::CreateRandomHash($this->clientKey, $this->serverSalt, $dummy_user_name);

		//サーバデータ準備
		$server_data = $this->createAuthData($dummy_user_name, $password, $uri);
		$server_data['shadow']		= $dummy_user_name;
		$server_data['cnonce_seed']	= $cnonce_seed;
		$server_data['extra_data']	= $extra_data;
		$server_data['maru']		= $dummy_user_name;

		$dummy_password	= Hash::HmacStringStretching($this->stretcher[2], $password, $user_name);

		//更新
		$this->updateData($user_name, $dummy_password, $server_data);
		$this->updateCookie($dummy_user_name, $dummy_password, $client_data);
	}

	/**
	 * 認証セッションのサーバデータを更新します。
	 *
	 * @param	string	$user_name		ユーザ名
	 * @param	string	$dummy_password	ダミーパスワード
	 * @param	array	$server_data	サーバ保存データ
	 */
	public function updateData ($user_name, $dummy_password, $server_data) {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->currentCookieName());
		}

		$hmac_key				= Hash::HmacStringStretching($this->stretcher[0], $this->serverSalt, $dummy_password);

		$data	= [
			'name'				=> $this->name,
			'user_name'			=> $user_name,
			'dummy_password'	=> $dummy_password,
			'raw_data'			=> OpenSSL::EncryptRandom(CompressionGz::CompressSerializedVariable($server_data), $dummy_password, $this->serverSalt, $hmac_key, $this->separatorLength),
		];

		$data	= OpenSSL::EncryptRandom(CompressionGz::CompressSerializedVariable($data), $this->cookieName, $this->serverSalt, $this->serverKey, $this->separatorLength);

		$this->sessionSaveHandler->save($data, $this->sessionExpire);
	}

	/**
	 * 認証セッションクッキーを更新します。
	 *
	 * @param	string	$dummy_user_name	ダミーユーザ名
	 * @param	string	$dummy_password		ダミーパスワード
	 * @param	array	$client_data		クライアント側保存データ
	 * @return	bool	クッキーを送出できている場合はtrue、そうでない場合はfalse
	 */
	public function updateCookie ($dummy_user_name, $dummy_password, $client_data) {
		$dummy_client_salt	= Hash::HmacStringStretching($this->stretcher[1], $this->clientSalt, $this->clientKey);
		$dummy_password		= Hash::HmacStringStretching($this->stretcher[2], $dummy_password, $dummy_user_name);
		$hmac_key			= Hash::HmacStringStretching($this->stretcher[0], $dummy_client_salt, $dummy_password);

		$value				= OpenSSL::EncryptRandom(CompressionGz::CompressVariable($client_data), $dummy_password, $dummy_client_salt, $hmac_key, $this->separatorLength);

		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->currentCookieName());
		}

		$cookie_path		= $this->cookiePath;
		$cookie_domain		= (is_callable($this->cookieDomain) ? $this->cookieDomain()() : $this->cookieDomain) ?? $_SERVER['SERVER_NAME'];
		$cookie_secure		= $this->cookieSecure;
		$expire				= $this->cookieLifetime > 0 ? time() + $this->cookieLifetime : $this->cookieLifetime;

		$set_cookie_data = [$this->cookieName, $value, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY];
		if (!setcookie(...$set_cookie_data)) {
			if (headers_sent($file, $line)) {
				throw new \ErrorException(sprintf('クッキーの発行に失敗しました。%s (%s) にて既にHTTP Response Headerの出力が行われています。%s', $file, $line, implode(', ', $set_cookie_data)));
			} else {
				throw new \ErrorException(sprintf('クッキーの発行に失敗しました。%s', implode(', ', $set_cookie_data)));
			}
		}
	}

	/**
	 * 認証セッションのGCを行います。
	 */
	public function gc () {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->currentCookieName());
		}

		$this->sessionSaveHandler->gc($this->sessionExpire, $this->cookieNamePrefix);
	}

	/**
	 * 認証セッションを取得します。
	 *
	 * @return	array	現在の認証セッション情報、そもそも認証が始まっていない場合は空配列が返る。
	 */
	public function get () {
		if (random_int(0, $this->divisor) < $this->probability) {
			$this->gc();
		}

		$server_data = $this->getOnServer();
		if (!isset($server_data['dummy_password'])) {
			return [];
		}
		$client_data = $this->getOnCookie($server_data['raw_data']['shadow'], $server_data['dummy_password']);

		if ($this->valid($server_data, $client_data)) {
			$this->digestAuth->nc($this->strict ? $server_data['raw_data']['nc'] : 1);
			return $server_data;
		} else {
			$this->digestAuth->nc(null);
			return [];
		}
	}

	/**
	 * サーバ上の認証セッションデータを取得します。
	 *
	 * @return	array	認証セッションデータ
	 */
	public function getOnServer () {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->getCookieName());
		}

		if (false === $encoded_message = $this->sessionSaveHandler->load()) {
			return [];
		}

		$data				= CompressionGz::UnCompressSerializedVariable(OpenSSL::DecryptRandom($encoded_message, $this->cookieName, $this->serverSalt, $this->serverKey, $this->separatorLength));

		$dummy_password		= $data['dummy_password'];
		$hmac_key			= Hash::HmacStringStretching($this->stretcher[0], $this->serverSalt, $dummy_password);
		$data['raw_data']	= CompressionGz::UnCompressSerializedVariable(OpenSSL::DecryptRandom($data['raw_data'], $dummy_password, $this->serverSalt, $hmac_key, $this->separatorLength));

		return $data;
	}

	/**
	 * クッキー上の認証セッションデータを取得します。
	 *
	 * @param	string	$dummy_user_name	ダミーユーザ名
	 * @param	string	$dummy_password		ダミーパスワード
	 * @return	array	認証セッションデータ
	 */
	public function getOnCookie ($dummy_user_name, $dummy_password) {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->getCookieName());
		}

		$cookie_value		= $_COOKIE[$this->cookieName];

		$dummy_client_salt	= Hash::HmacStringStretching($this->stretcher[1], $this->clientSalt, $this->clientKey);
		$dummy_password		= Hash::HmacStringStretching($this->stretcher[2], $dummy_password, $dummy_user_name);
		$hmac_key			= Hash::HmacStringStretching($this->stretcher[0], $dummy_client_salt, $dummy_password);

		$cookie_value		= OpenSSL::DecryptRandom($cookie_value, $dummy_password, $dummy_client_salt, $hmac_key, $this->separatorLength);
		if ($cookie_value === false) {
			return [];
		}

		return CompressionGz::UnCompressVariable($cookie_value);
	}

	/**
	 * 認証セッションを閉じます。
	 */
	public function close () {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->getCookieName());
		}

		if (is_null($this->cookieName)) {
			return false;
		}

		//認証セッション情報の削除
		$this->sessionSaveHandler->remove($this->cookieName);

		//クライアント側クッキーの削除
		$cookie_path		= $this->cookiePath;
		$cookie_domain		= (is_callable($this->cookieDomain) ? $this->cookieDomain()() : $this->cookieDomain) ?? $_SERVER['SERVER_NAME'];
		$cookie_secure		= $this->cookieSecure;

		$expire	= time() - 1800;
		$set_cookie_data = [$this->cookieName, null, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY];
		setcookie(...$set_cookie_data);
	}

	/**
	 * 認証セッションを付け替えます。
	 */
	public function replace () {
		if (!$this->enableSessionSaveHandler()) {
			$this->setupSessionSaveHandler()->sessionSaveHandler->cookieName($this->cookieName ?? $this->cookieName = $this->currentCookieName());
		}

		$new_auth_session_name	= $this->currentCookieName(true);

		$cookie_path		= $this->cookiePath;
		$cookie_domain		= (is_callable($this->cookieDomain) ? $this->cookieDomain()() : $this->cookieDomain) ?? $_SERVER['SERVER_NAME'];
		$cookie_secure		= $this->cookieSecure;

		//認証セッション情報の付け替え
		$server_data		= $this->sessionSaveHandler->load();
		$this->sessionSaveHandler->remove($new_auth_session_name);
		$this->sessionSaveHandler->save($server_data, $this->sessionExpire);

		//クライアント側クッキーの付け替え
		$expire	= time() - 1800;
		setcookie($this->cookieName, null, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY);

		$expire				= $this->cookieLifetime > 0 ? time() + $this->cookieLifetime : $this->cookieLifetime;
		$client_data		= $_COOKIE[$this->cookieName] ?? null;
		setcookie($new_auth_session_name, $client_data, $expire, $cookie_path, $cookie_domain, $cookie_secure, static::COOKIE_HTTP_ONLY);

		// 新しいセッション名を設定
		$this->cookieName = $new_auth_session_name;
	}

	/**
	 * 認証セッションを検証します。
	 *
	 * @param	array	$server_data	サーバ側データ
	 * @param	array	$client_data	クライアント側データ
	 * @throws	\ErrorException	認証に異常が認められた場合
	 * @return	bool	認証が正常な場合はtrue、セッションが始まっていない場合はfalse
	 */
	public function valid ($server_data, $client_data) {
		if ($server_data === ['raw_data' => false] || $client_data === false) {
			return false;
		}

		$server_data	= $server_data['raw_data'];
		$server_data[$this->digestAuth::PROPERTY_USER_NAME]	= $server_data['shadow'];

		$check_target_list = [
			$this->digestAuth::PROPERTY_USER_NAME,
			$this->digestAuth::PROPERTY_REALM,
			$this->digestAuth::PROPERTY_NC,
			$this->digestAuth::PROPERTY_NONCE,
			$this->digestAuth::PROPERTY_CNONCE,
			$this->digestAuth::PROPERTY_URI,
			$this->digestAuth::PROPERTY_RESPONSE,
		];

		foreach ($check_target_list as $name) {
			if (($server_data[$name] ?? null) !== ($client_data[$name] ?? null)) {
				//throw new \ErrorException(sprintf('認証セッションに齟齬があります。name:%s, server:%s, client:%s', $name, $server_data[$name] ?? '未設定', $client_data[$name] ?? '未設定'));
				return false;
			}
		}

		if ($this->strict) {
			if (!Hash::ValidRandomHash($client_data[$this->digestAuth::PROPERTY_CNONCE], $server_cnonce = $server_data['cnonce_seed'] . $server_data[$this->digestAuth::PROPERTY_NC], $this->clientKey, $this->clientSalt, $this->separatorLength)) {
				throw new \ErrorException(sprintf('認証セッションに齟齬があります。ランダムハッシュの検証に失敗しました。'));
			}
		} else {
			if (!hash_equals($client_data[$this->digestAuth::PROPERTY_CNONCE], $server_cnonce = $server_data[$this->digestAuth::PROPERTY_CNONCE])) {
				throw new \ErrorException(sprintf('認証セッションに齟齬があります。ランダムハッシュの検証に失敗しました。'));
			}
		}

		return true;
	}

	/**
	 * Cookieから認証クッキー名を取得します。
	 *
	 * @return	string	認証クッキー名
	 */
	public function getCookieName () {
		$prefix_length	= mb_strlen($this->cookieNamePrefix);

		foreach ($_COOKIE as $cookie_name => $value) {
			if (($prefix_length === 0 || mb_substr($cookie_name, 0, $prefix_length) === $this->cookieNamePrefix) && strlen(mb_substr($cookie_name, $prefix_length)) === 64) {
				return $cookie_name;
			}
		}

		return null;
	}

	/**
	 * 現在の認証クッキー名を返します。
	 *
	 * 認証クッキー名が存在しない場合は作成して返します。
	 *
	 * @param	bool	$regenerate	強制再発行フラグ
	 * @throws	\ErrorException	新規発行認証セッションクッキー名が既存と重複した場合
	 * @return	string	現在の認証クッキー名
	 */
	public function currentCookieName ($regenerate = false) {
		if (!$regenerate) {
			$cookie_name = $this->getCookieName();
			if (!is_null($cookie_name)) {
				return $cookie_name;
			}
		}

		for ($i = 0;$i < 100;$i++) {
			if (!$this->sessionSaveHandler->exists($cookie_name = sprintf('%s%s', $this->cookieNamePrefix, bin2hex(random_bytes(32))))) {
				return $cookie_name;
			}
		}

		throw new \ErrorException(sprintf('新規発行認証セッションクッキー名が重複しました。最後の一つは%sです。', $cookie_name));
	}

	/**
	 * 認証セッションハンドラが有効になっているか確認します。
	 *
	 * @return	bool	認証セッションハンドラが有効な場合はtrue、そうでない場合はfalse
	 */
	public function enableSessionSaveHandler () {
		return $this->sessionSaveHandler !== null;
	}

	/**
	 * 認証セッションハンドラを準備します。
	 *
	 * @return	\ickx\fw2\auth\traits\AuthSession	認証セッションハンドラが準備済みのこのインスタンス
	 */
	public function setupSessionSaveHandler () {
		switch ($this->saveHandler) {
			case static::SAVE_HANDLER_TYPE_FILES:
				$this->sessionSaveHandler	= new Files($this->savePath ?? sys_get_temp_dir());
				break;
			case static::SAVE_HANDLER_TYPE_MEMCACHED:
				$this->sessionSaveHandler	= new Memcached($this->savePath);
				break;
			default:
				throw new \ErrorException('認証セッションハンドラのタイプに有効な値が指定されていません。', 0, \E_USER_ERROR);
		}

		return $this;
	}

	//==============================================
	// ユーティリティ
	//==============================================
	/**
	 * デフォルトの認証領域を返します。
	 *
	 * @return	string	認証領域
	 */
	public static function getDefaultRealm () {
		return sprintf('%s://%s', Request::EnableSSL() ? 'https' : 'http', $_SERVER['SERVER_NAME']);
	}

	/**
	 * デフォルトのURIを返します。
	 *
	 * @return	string	URI
	 */
	public static function getDefaultUri () {
		return parse_url(sprintf('http://%s%s', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']), \ PHP_URL_PATH);
	}

	/**
	 * 認証データを作成します。
	 *
	 * @param	string	$dummy_user_name	ダミーユーザ名：クッキーに出力するのでダミー化すること
	 * @param	string	$password			パスワード
	 * @param	string	$uri				現在のリクエストURI
	 * @param	string	$method				現在のリクエストメソッド
	 * @return	array	認証データ
	 */
	public function createAuthData ($dummy_user_name, $password, $uri = null, $method = null) {
		$uri			= $uri ?? $this->digestAuth->uri() ?? static::getDefaultUri();
		$method			= $method ?? $this->digestAuth->method() ?? Request::GetMethod();

		$response_a1	= $this->digestAuth->createResponseA1($dummy_user_name, $password);
		$response_a2	= $this->digestAuth->createResponseA2($uri, $method);
		$response		= $this->digestAuth->createResponse($response_a1, $response_a2);

		return [
			$this->digestAuth::PROPERTY_USER_NAME	=> $dummy_user_name,
			$this->digestAuth::PROPERTY_REALM		=> $this->digestAuth->realm(),
			$this->digestAuth::PROPERTY_NC			=> $this->digestAuth->nc(),
			$this->digestAuth::PROPERTY_NONCE		=> $this->digestAuth->nonce(),
			$this->digestAuth::PROPERTY_CNONCE		=> $this->digestAuth->cnonce(),
			$this->digestAuth::PROPERTY_URI			=> $uri,
			$this->digestAuth::PROPERTY_RESPONSE	=> $response,
		];
	}
}
