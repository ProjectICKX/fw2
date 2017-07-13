<?php
/**  ______ _				 _		       _ ___
 *  |  ____| |		       | |		     | |__ \
 *  | |__  | |_   ___      __| |__   ___  ___| |  ) |
 *  |  __| | | | | \ \ /\ / /| '_ \ / _ \/ _ \ | / /
 *  | |    | | |_| |\ V  V / | | | |  __/  __/ |/ /_
 *  |_|    |_|\__, | \_/\_/  |_| |_|\___|\___|_|____|
 *		     __/ |
 *		    |___/
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

/**
 * ベーシック認証を扱います。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class BasicAuth implements \ickx\fw2\auth\interfaces\IBasicAuth {
	use	\ickx\fw2\traits\singletons\Multiton,
		\ickx\fw2\traits\magic\Accessor;

	/**
	 * @var	string	ハッシュアルゴリズム：2017/7 現在において、ほぼすべてのブラウザはmd5のみ対応している
	 */
	protected $algorithm	= null;

	/**
	 * @var	string	認証領域
	 */
	protected $realm		= null;

	/**
	 * @var	string	ユーザ名：認証に成功した際に、このオブジェクトによって設定される。
	 */
	protected $userName		= null;

	/**
	 * @var	<array|callable>	パスワード
	 */
	protected $password		= null;

	/**
	 * 認証を行います。
	 *
	 * @return	bool	認証に成功している場合はtrue、そうでない場合はfalse
	 */
	public function auth () {
		$authenticate;
		$password_func;
		if ($this->isInitial() || !$this->authorize()) {
			http_response_code(401);
			header(sprintf(static::HTTP_HEADER_AUTHENTICATE, $this->implodeAuthenticate()));
			return false;
		}

		return true;
	}

	/**
	 * 認証状態を剥奪します。
	 */
	public function deprive () {
		throw new \ErrorException('Basic認証ではサーバサイドのみでの認証停止は行えません。次のように実在しないダミーのユーザを指定したHTTPアクセスを行わせてください。https://dummy@localhost/');
	}

	/**
	 * Authenticate　Header拡張領域を返します。
	 *
	 * @return	string	Authenticate　Header拡張領域
	 */
	public function implodeAuthenticate () {
		$authenticate = [];

		if (!is_null($this->realm)) {
			$authenticate[] = sprintf('realm="%s"', htmlspecialchars($this->realm, \ENT_QUOTES, 'UTF-8'));
		}

		if (!is_null($this->algorithm)) {
			$authenticate[] = sprintf('algorithm=%s', htmlspecialchars($this->algorithm, \ENT_QUOTES, 'UTF-8'));
		}

		return implode(',', $authenticate);
	}

	/**
	 * 初回アクセスかどうか判定します。
	 *
	 * @return	bool	初回アクセスの場合はtrue、そうでない場合はfalse
	 */
	public function isInitial () {
		return !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	}

	/**
	 * 認証処理を行います。
	 *
	 * @return	bool	認証に成功した場合はtrue、失敗した場合はfalse
	 */
	public function authorize () {
		if ($ret = password_verify($_SERVER['PHP_AUTH_PW'], (is_callable($this->password) ? $this->password()($_SERVER['PHP_AUTH_USER']) : $this->password[$_SERVER['PHP_AUTH_USER']] ?? null) ?? password_hash(openssl_random_pseudo_bytes(30), \PASSWORD_DEFAULT))) {
			$this->userName = $_SERVER['PHP_AUTH_USER'];
		}
		return $ret;
	}

	/**
	 * コンフィグを纏めて取得・設定します。
	 *
	 * @param	array	...$config	コンフィグ
	 * @return	array	配列化した設定または、設定済みのこのインスタンス
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

	//==============================================
	// プロテクテッドメソッド
	//==============================================
	/**
	 * コンストラクタ
	 */
	protected function __construct () {}
}
