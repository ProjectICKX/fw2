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

use ickx\fw2\crypt\Hash;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\vartype\strings\Strings;

/**
 * ダイジェスト認証を管理します。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DigestAuth implements \ickx\fw2\auth\interfaces\IDigestAuth {
	use	\ickx\fw2\traits\singletons\Multiton,
		\ickx\fw2\traits\magic\Accessor;

	//==============================================
	// プロパティ
	//==============================================
	/**
	 * @var	Object	オーナーインスタンスへの参照
	 */
	protected $owner		= null;

	/**
	 * @var	array	設定のデフォルト値
	 * @static
	 */
	protected static $defaultConfig	= [
		self::PROPERTY_ALGORITHM	=> self::DEFAULT_HASH_ALGORITHM,
		self::PROPERTY_QOP			=> self::DEFAULT_QOP,
	];

	/**
	 * @var	string	認証領域
	 */
	protected $realm		= null;

	/**
	 * @var	string	現在接続中のHTTPリクエストメソッド
	 */
	protected $method		= null;

	/**
	 * @var	string	現在接続中のリクエストURI
	 */
	protected $uri			= null;

	/**
	 * @var	string	NONCE
	 */
	protected $nonce		= 1;

	/**
	 * @var	string	NC
	 */
	protected $nc			= 1;

	/**
	 * @var	string	CNONCE
	 */
	protected $cnonce		= 1;

	/**
	 * @var	string	ハッシュアルゴリズム：2017/7 現在において、ほぼすべてのブラウザはmd5のみ対応している
	 */
	protected $algorithm	= null;

	/**
	 * @var	string	qop
	 */
	protected $qop			= null;

	/**
	 * @var	string	A1レスポンスコード
	 */
	protected $responceA1	= null;

	/**
	 * @var	string	A2レスポンスコード
	 */
	protected $responceA2	= null;

	/**
	 * @var	string	レスポンスコード
	 */
	protected $response		= null;

	//==============================================
	// メソッド
	//==============================================
	/**
	 * ダイジェストAuth インスタンスを作成し、返します。
	 *
	 * @return \ickx\fw2\auth\http\DigestAuth
	 */
	public static function init () {
		return new static();
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
	 * @return	<array|\ickx\fw2\auth\http\DigestAuth>
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
	 * @return	\ickx\fw2\auth\http\DigestAuth
	 */
	public function adjust () {
		foreach (static::$defaultConfig as $name => $value) {
			if (is_null($this->$name)) {
				$this->$name = $value;
			}
		}
		return $this;
	}

	/**
	 * ダイジェスト認証のデフォルト値を調整します。
	 *
	 * @param	array	$digest_auth	ダイジェスト認証設定配列
	 * @return	array	調整済みダイジェスト認証設定配列
	 */
	public static function adjustConfig ($config) {
		return $config + static::$defaultConfig;
	}

	/**
	 * ダイジェスト認証用Authenticateを生成します。
	 *
	 * @return	string	ダイジェスト認証用Authenticate
	 */
	public function makeDigestAuthenticate () {
		return sprintf(
			static::HTTP_HEADER_AUTHENTICATE,
			$this->realm,
			openssl_random_pseudo_bytes(13),
			Hash::String($this->realm, $this->algorithm)
		);
	}

	/**
	 * A1レスポンスを生成します。
	 *
	 * @param	string	$user_name	ユーザ名
	 * @param	string	$password	パスワード
	 * @return	string	A1レスポンス
	 */
	public function createResponseA1 ($user_name, $password) {
		return $this->responceA1 = Hash::String(
			implode(static::RESPONSE_SEPARATOR, [
				$user_name,
				$this->realm,
				$password
			]),
			$this->algorithm
		);
	}

	/**
	 * A2レスポンスを生成します。
	 *
	 * @param	string	$uri			URI
	 * @param	string	$request_method	リクエストメソッド
	 * @return	string	A2レスポンス
	 */
	public function createResponseA2 ($uri = null, $method = null) {
		return $this->responceA2 = Hash::String(
			implode(static::RESPONSE_SEPARATOR, [
				$method ?? $this->method ?? Request::GetMethod(),
				$uri ?? $this->uri ?? $_SERVER['REQUEST_URI']
			]),
			$this->algorithm
		);
	}

	/**
	 * レスポンスを生成します。
	 *
	 * @param	string	$responce_a1	A1レスポンス
	 * @param	string	$responce_a2	A2レスポンス
	 * @return	string	レスポンス
	 */
	public function createResponse ($responce_a1 = null, $responce_a2 = null) {
		return $this->digestResponse = Hash::String(
			implode(static::RESPONSE_SEPARATOR, [
				$responce_a1 ?? $this->responceA1,
				$this->nonce,
				$this->nc,
				$this->cnonce,
				$this->qop,
				$responce_a2 ?? $this->responceA2
			]),
			$this->algorithm
		);
	}

	/**
	 * AUTH_DIGESTヘッダ上の値を反映します。
	 */
	public function reflectHttpAuthDigestHeader () {
		foreach ($_SERVER['PHP_AUTH_DIGEST'] ?? [] as $key => $value) {
			$property_name = Strings::ToLowerCamelCase($key);
			$this->$property_name = $value;
		}
	}
}
