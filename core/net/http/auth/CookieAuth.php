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
use ickx\fw2\core\exception\CoreException;
use ickx\fw2\crypt\OpenSSL;
use ickx\fw2\compression\CompressionGz;

/**
 * Http Cookie Auth Class
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class CookieAuth implements interfaces\IAuth, interfaces\IDigestResponse {
	use	traits\DigestResponseTrait;

	const DEFAULT_CONNECTION_NAME	= ':default:';
	const VALID_ALTER_LV_STRICT		= 'strict';
	const VALID_ALTER_LV_LAZY		= 'lazy';

	/**
	 * @property	string	現在設定されているアルゴリズム
	 * @static
	 */
	protected static $_algorithm	= null;

	/**
	 * @property	string	クッキーのデフォルト値
	 * @static
	 */
	protected static $_defaultCookieOptions	= [];

	/**
	 * @property	array	認証領域ごとのログイン済みユーザ名
	 * @static
	 */
	protected static $_loginUserName	= [];

	/**
	 * クッキー認証用の初期設定を行います。
	 *
	 * @param	array	$options
	 */
	public static function Connect (array $options, $name = self::DEFAULT_CONNECTION_NAME) {
		foreach (['server_key', 'server_salt', 'client_key', 'client_salt'] as $key) {
			if (!isset($options[$key])) {
				throw CoreException::RaiseSystemError('クッキー認証の%sが設定されていません。CookieAuth::Connect([\'%s\' => 任意のパスキー])として設定を行ってください。', [$key, $key]);
			}
		}
		if (!isset($options['stretcher'])) {
			throw CoreException::RaiseSystemError('クッキー認証のstretcherが設定されていません。CookieAuth::Connect([\'stretcher\' => [一つ目のstretch, 二つ目のstretch, 三つ目のstretch]])として設定を行ってください。');
		}

		return static::OverwriteAuthCookieOptions ($options, $name);
	}

	/**
	 * 認証済みかどうか判定します。
	 *
	 * @param	string	$name	認証名
	 * @return	bool	認証済みの場合はtrue、そうでない場合はfalse
	 */
	public static function IsAuthenticated ($name = self::DEFAULT_CONNECTION_NAME) {
		return isset(static::$_loginUserName[$name]) && static::$_loginUserName[$name] !== null;
	}

	/**
	 * 認証を行います。
	 *
	 * @param	string	$realm
	 * @param	string	$response_a1
	 * @param	string	$key
	 * @param	string	$auth_callback
	 * @param	string	$options
	 */
	public static function Auth ($user_name = null, $password = null, $name = self::DEFAULT_CONNECTION_NAME) {
		if (static::IsAuthenticated($name)) {
			return static::$_loginUserName[$name];
		}

		//==============================================
		//設定確認
		//==============================================
		$options = static::GetAuthCookieOptions($name);
		foreach (['server_key', 'server_salt', 'client_key', 'client_salt'] as $key) {
			if (empty($options[$key])) {
				throw CoreException::RaiseSystemError('クッキー認証の%sが設定されていません。CookieAuth::Connect([\'%s\' => 任意のパスキー])として設定を行ってください。', [$key, $key]);
			}
		}
		if (empty($options['stretcher'])) {
			throw CoreException::RaiseSystemError('クッキー認証のstretcherが設定されていません。CookieAuth::Connect([\'stretcher\' => [一つ目のstretch, 二つ目のstretch, 三つ目のstretch]])として設定を行ってください。');
		}
		if (empty($options['password_callback'])) {
			throw CoreException::RaiseSystemError('クッキー認証のpassword_callbackが設定されていません。CookieAuth::Connect([\'password_callback\' => call back function]])として設定を行ってください。');
		}

		//==============================================
		//ガベージコレクタ
		//==============================================
		static::AuthCookieSessionGarbageCollection($options['divisor'], $options['probability'], $options, $name);

		//==============================================
		//認証クッキー取得
		//==============================================
		$auth_cookie	= static::GetAuthCookieValue($name);

		//ユーザー名とパスワードがある場合は強制再認証
		if ($auth_cookie !== null && $user_name !== null && $password !== null) {
			list($cookie_name, $cookie_value) = $auth_cookie;
			$auth_cookie = null;
			static::AuthDelete($cookie_name, $name);
		}

		//==============================================
		//認証
		//==============================================
		$password_callback	 = $options['password_callback'];

		//未認証の疑い
		if ($auth_cookie === null) {
			//認証クッキーがなく、user_nameの指定がない場合は未認証とする。
			if ($user_name === null) {
				static::$_loginUserName[$name] = null;
				return static::STATUS_UNAUTHORIZED;
			}

			//認証待ち
			//実在するユーザーかどうか確認する
			$on_storage_password = $password_callback($user_name);
			if ($on_storage_password === null) {
				//認証失敗
				static::$_loginUserName[$name] = null;
				return static::STATUS_FAILURE;
			}

			//正しいパスワードかどうか
			$auth_impact_callback	 = $options['auth_impact_callback'];
			if (!$auth_impact_callback($on_storage_password, $user_name, $password)) {
				//認証失敗
				static::$_loginUserName[$name] = null;
				return static::STATUS_FAILURE;
			}
			$password = $on_storage_password;

			//認証展開
			$cookie_name = static::CreateCookieName($name);
			if ($cookie_name === null) {
				throw CoreException::RaiseSystemError('クッキー認証ファイルの配置ができませんでした。');
			}
			static::UpdateAuthStatus($cookie_name, $user_name, $password, 1, $name);

			//認証成功
			static::$_loginUserName[$name] = $user_name;
			return ['user_name' => static::$_loginUserName[$name], 'cookie_auth' => false];
		}

		//==============================================
		//認証クッキーの正当性検証
		//==============================================
		//クッキー名と値の取得
		list($cookie_name, $cookie_value) = $auth_cookie;

		//認証データが残っているかどうか検証
		if (!is_file(static::CreateCookieSavePath($cookie_name, $name))) {
			static::AuthDelete($cookie_name, $name);
			throw CoreException::RaiseSystemError('存在しない認証クッキーを渡されました。cookie_name:%s, cookie_save_dir:%s', [$cookie_name, $options['cookie_save_dir']]);
		}

		//ユーザ名の取得
		$user_name = static::GetUserNameFromSessionData($cookie_name, $name);

		//パスワードを取得する
		$password = $password_callback($user_name);

		//実在するユーザーかどうか確認する
		if ($password === null || $password === false) {
			//認証失敗
			static::AuthDelete($cookie_name, $name);
			static::$_loginUserName[$name] = null;
			return static::STATUS_FAILURE;
		}

		//クッキー認証セッションデータの取得
		$server_data = static::GetAuthSessionData($cookie_name, $user_name, $password, $name);

		//クッキー値の展開
		$client_data = static::PurseAuthCookieData($cookie_value, $user_name, $password, $name);
		if ($client_data === false) {
			static::AuthDelete($cookie_name, $name);
			throw CoreException::RaiseSystemError('認証クッキーそのものが改ざんされています。');
		}

		//不動キーの突合検証
		if (!Hash::ValidRandomHash($client_data['shadow'], $options['client_key'], $options['server_salt'], $options['server_key'])) {
			static::AuthDelete($cookie_name, $name);
			throw CoreException::RaiseSystemError('認証クッキーの内容が改ざんされています。key:%s', [$client_data['shadow']]);
		}

		//認証値の突合確認
		foreach ($options['valid_alter_lv'] === static::VALID_ALTER_LV_LAZY ? [] : ['username', 'realm', 'uri', 'nc', 'nonce', 'cnonce', 'response'] as $key) {
			if ($server_data[$key] !== $client_data[$key]) {
				static::AuthDelete($cookie_name, $name);
				throw CoreException::RaiseSystemError('認証クッキーの内容が改ざんされています。key:%s', [$key]);
			}
		}

		//認証情報更新
		static::UpdateAuthStatus($cookie_name, $user_name, $password, $server_data['nc'] + 1, $name);

		//認証成功
		static::$_loginUserName[$name] = $user_name;
		return ['user_name' => static::$_loginUserName[$name], 'cookie_auth' => true];
	}

	/**
	 * 認証を破棄します。
	 *
	 */
	public static function AuthDelete ($cookie_name = null, $name = self::DEFAULT_CONNECTION_NAME) {
		$cookie_name = $cookie_name ?: ((null === $auth_cookie = static::GetAuthCookieValue($name)) ? $auth_cookie : $auth_cookie[0]);

		if ($cookie_name === null) {
			return false;
		}

		$options = static::GetAuthCookieOptions($name);

		//クライアント側クッキーの削除
		$path				= $options['cookie_path'];
		$domain				= $options['cookie_domain'];
		$secure				= $options['cookie_secure'];
		$httponly			= $options['cookie_httponly'];
		$expire				= time() - 1800;
		setcookie($cookie_name, null, $expire, $path, $domain, $secure, $httponly);

		//クッキー認証セッションファイルの削除
		$cookie_save_path	= static::CreateCookieSavePath($cookie_name, $name);
		if (!is_file($cookie_save_path)) {
			return false;
		}
		unlink($cookie_save_path);

		//認証を破棄
		return true;
	}

	/**
	 * 認証維持用のデータを構築します。
	 */
	public static function CreateAuthData ($user_name, $password, $nonce, $cnonce, $dummy_user_name, $dummy_realm, $nc, $uri, $name = self::DEFAULT_CONNECTION_NAME) {
		$options = static::GetAuthCookieOptions($name);
		$qop				= $options['qop'];
		$client_key			= $options['client_key'];
		$client_salt		= $options['client_salt'];
		$server_key			= $options['server_key'];
		$server_salt		= $options['server_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];
		$realm				= $options['realm'];

		$response_a1 = static::CreateResponseA1($realm, $user_name, $password, $client_key);
		$response_a2 = static::CreateResponseA2($uri, $client_key, 'GET');

		$response	= static::CreateResponse($response_a1, $nonce, $nc, $cnonce, $qop, $response_a2, $client_key);

		return [
			static::PARAM_USER_NAME	=> $dummy_user_name,
			static::PARAM_REALM		=> $dummy_realm,
			static::PARAM_NC		=> $nc,
			static::PARAM_NONCE		=> $nonce,
			static::PARAM_CNONCE	=> $cnonce,
			static::PARAM_URI		=> $uri,
			static::PARAM_RESPONSE	=> $response,
		];
	}

	/**
	 * 認証を更新します。
	 *
	 * @param unknown_type $user_name
	 * @param unknown_type $password
	 * @param unknown_type $name
	 */
	public static function UpdateAuthStatus ($cookie_name, $user_name, $password, $nc, $name = self::DEFAULT_CONNECTION_NAME) {
		$options = static::GetAuthCookieOptions($name);
		$client_key			= $options['client_key'];
		$client_salt		= $options['client_salt'];
		$server_key			= $options['server_key'];
		$server_salt		= $options['server_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];

		$uri				= $_SERVER['REQUEST_URI'];
		$dummy_user_name	= Hash::CreateRandomHash(microtime(true).rand(0,1000), $client_key, $client_salt, $separator_length);
		$dummy_realm		= Hash::CreateRandomHash($options['realm'].microtime(true).rand(0,1000), $client_key, $client_salt, $separator_length);

		$nonce				= Hash::CreateRandomHash($dummy_user_name.$dummy_realm, $server_salt, $server_key, $separator_length);
		$cnonce				= Hash::CreateRandomHash($nonce, $client_key, $client_salt, $separator_length);

		$server_data = static::CreateAuthData($user_name, $password, $nonce, $cnonce, $dummy_user_name, $dummy_realm, $nc, $uri, $name);
		$server_data['shadow']	= $user_name;

		$client_data = static::CreateAuthData($user_name, $password, $nonce, $cnonce, $dummy_user_name, $dummy_realm, $nc, $uri, $name);
		$client_data['shadow']	= Hash::CreateRandomHash($client_key, $server_salt, $server_key);

		static::UpdateAuthCookieSession($cookie_name, $user_name, $password, $server_data, $name);
		static::UpdateAuthCookie($cookie_name, $user_name, $password, $client_data, $name);
	}

	/**
	 * 認証セッションを更新します。
	 */
	public static function UpdateAuthCookieSession ($cookie_name, $user_name, $password, $data, $name = self::DEFAULT_CONNECTION_NAME) {
		$options	= static::GetAuthCookieOptions($name);

		$server_key			= $options['server_key'];
		$server_salt		= $options['server_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];

		$server_salt	= Hash::HmacStringStretching($stretcher[1], $server_salt, $server_key);
		$password		= Hash::HmacStringStretching($stretcher[2], $password, $user_name);
		$hmac_key		= Hash::HmacStringStretching($stretcher[0], $server_salt, $password);

		$data	= [
			'shadow'	=> $user_name,
			'raw_data'	=> OpenSSL::EncryptRandom(CompressionGz::CompressVariable($data), $password, $server_salt, $hmac_key, $separator_length),
		];
		$data = OpenSSL::EncryptRandom(CompressionGz::CompressVariable($data), $cookie_name, $server_salt, $server_key, $separator_length);

		return file_put_contents(static::CreateCookieSavePath($cookie_name, $name), $data);
	}

	/**
	 * 認証クッキーを更新します。
	 */
	public static function UpdateAuthCookie ($cookie_name, $user_name, $password, $value, $name = self::DEFAULT_CONNECTION_NAME) {
		$options	= static::GetAuthCookieOptions($name);

		$cookie_expire		= $options['cookie_expire'];
		$client_key			= $options['client_key'];
		$client_salt		= $options['client_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];

		$client_salt	= Hash::HmacStringStretching($stretcher[1], $client_salt, $client_key);
		$password		= Hash::HmacStringStretching($stretcher[2], $password, $user_name);
		$hmac_key		= Hash::HmacStringStretching($stretcher[0], $client_salt, $password);

		$value			= OpenSSL::EncryptRandom(CompressionGz::CompressVariable($value), $password, $client_salt, $hmac_key, $separator_length);

		$expire			= $cookie_expire > 0 ? time() + $cookie_expire : $cookie_expire;
		$path			= $options['cookie_path'];
		$domain			= $options['cookie_domain'];
		$secure			= $options['cookie_secure'];
		$httponly		= $options['cookie_httponly'];

		return setcookie($cookie_name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * 認証セッションからユーザ名だけ取得します。
	 */
	public static function GetUserNameFromSessionData ($cookie_name, $name = self::DEFAULT_CONNECTION_NAME) {
		$options	= static::GetAuthCookieOptions($name);

		$server_key			= $options['server_key'];
		$server_salt		= $options['server_salt'];
		$separator_length	= $options['separator_length'];

		$raw_data = file_get_contents(static::CreateCookieSavePath($cookie_name, $name));
		$data = CompressionGz::UnCompressVariable(OpenSSL::DecryptRandom($raw_data, $cookie_name, $server_salt, $server_key, $separator_length));

		return $data['shadow'];
	}

	/**
	 * 認証セッションの値を取得します。
	 */
	public static function GetAuthSessionData ($cookie_name, $user_name, $password, $name = self::DEFAULT_CONNECTION_NAME) {
		$options	= static::GetAuthCookieOptions($name);

		$server_key			= $options['server_key'];
		$server_salt		= $options['server_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];

		$raw_data	= file_get_contents(static::CreateCookieSavePath($cookie_name, $name));
		$raw_data	= CompressionGz::UnCompressVariable(OpenSSL::DecryptRandom($raw_data, $cookie_name, $server_salt, $server_key, $separator_length));
		$raw_data	= $raw_data['raw_data'];

		$server_salt	= Hash::HmacStringStretching($stretcher[1], $server_salt, $server_key);
		$password		= Hash::HmacStringStretching($stretcher[2], $password, $user_name);
		$hmac_key		= Hash::HmacStringStretching($stretcher[0], $server_salt, $password);

		return CompressionGz::UnCompressVariable(OpenSSL::DecryptRandom($raw_data, $password, $server_salt, $hmac_key, $separator_length));
	}

	/**
	 * 認証クッキーの値を解読します。
	 */
	public static function PurseAuthCookieData ($cookie_value, $user_name, $password, $name = self::DEFAULT_CONNECTION_NAME) {
		$options	= static::GetAuthCookieOptions($name);

		$client_key			= $options['client_key'];
		$client_salt		= $options['client_salt'];
		$stretcher			= $options['stretcher'];
		$separator_length	= $options['separator_length'];

		$client_salt	= Hash::HmacStringStretching($stretcher[1], $client_salt, $client_key);
		$password		= Hash::HmacStringStretching($stretcher[2], $password, $user_name);
		$hmac_key		= Hash::HmacStringStretching($stretcher[0], $client_salt, $password);

		return CompressionGz::UnCompressVariable(OpenSSL::DecryptRandom($cookie_value, $password, $client_salt, $hmac_key, $separator_length));
	}

	/**
	 * クッキー認証用オプションを返します。
	 *
	 * @return	array	クッキー認証用オプション
	 */
	public static function GetAuthCookieOptions ($name = self::DEFAULT_CONNECTION_NAME) {
		if (!isset(static::$_defaultCookieOptions[$name])) {
			static::SetAuthCookieOptions([
				'algorithm'				=> static::DEFAULT_HASH_ALGORITHM,
				'realm'					=> $_SERVER['SERVER_NAME'],
				'qop'					=> 'auth',
				'client_key'			=> null,
				'client_salt'			=> null,
				'server_key'			=> null,
				'server_salt'			=> null,
				'stretcher'				=> null,	//ex) [3, 1, 4],
				'separator_length'		=> 8,
				'valid_alter_lv'		=> static::VALID_ALTER_LV_LAZY,
				'cookie_save_dir'		=> sys_get_temp_dir(),
				'cookie_name_prefix'	=> 'cookie_auth_',
				'cookie_name_safix'		=> '',
				'cookie_expire'			=> 86400,
				'cookie_path'			=> '/',
				'cookie_domain'			=> null,
				'cookie_secure'			=> true,
				'cookie_httponly'		=> true,
				'divisor'				=> 1,
				'probability'			=> 512,
				'password_callback'		=> null,	// IF function ($user_name);
				'auth_impact_callback'	=> function ($on_storage_password, $user_name, $password) {
					return $on_storage_password === $password;
				},
			], $name);
		}
		return static::$_defaultCookieOptions[$name];
	}

	/**
	 * クッキー認証用オプションのデフォルト値を設定します。
	 *
	 * @return	array	クッキー認証用オプションのデフォルト値
	 */
	public static function OverwriteAuthCookieOptions (array $options, $name = self::DEFAULT_CONNECTION_NAME) {
		static::$_defaultCookieOptions[$name] = $options + static::GetAuthCookieOptions($name);
	}

	/**
	 * クッキー認証用オプションのデフォルト値を設定します。
	 *
	 * @return	array	クッキー認証用オプションのデフォルト値
	 */
	public static function SetAuthCookieOptions ($options, $name = self::DEFAULT_CONNECTION_NAME) {
		static::$_defaultCookieOptions[$name] = $options;
	}

	/**
	 * クッキー認証用セッションファイルの保存先を返します。
	 *
	 * @return	string	クッキー認証用セッションファイルの保存先
	 */
	public static function GetAuthCookieSessionSavePath ($name = self::DEFAULT_CONNECTION_NAME) {
		return static::GetAuthCookieOptions($name)['cookie_save_dir'] ?? sys_get_temp_dir();
	}

	/**
	 * 認証クッキー用セッションファイルに対してガベージコレクションを実行します。
	 *
	 * @param	int		$divisor			ガベージコレクタの起動確率 デフォルトは1 数値を増やすと起動確率が高くなる
	 * @param	int		$probability		ガベージコレクタの起動確率 母数 デフォルトは512
	 * @return	bool	ガベージコレクタが起動した場合はtrue、そうでない場合はfalse
	 */
	public static function AuthCookieSessionGarbageCollection ($divisor = 1, $probability = 512, $options = [], $name = self::DEFAULT_CONNECTION_NAME) {
		if (rand(0, $probability) < $divisor) {
			return false;
		}

		$options = static::GetAuthCookieOptions($name);
		$cookie_expire		= $options['cookie_expire'];
		$cookie_save_dir	= $options['cookie_save_dir'];
		$cookie_name_prefix	= $options['cookie_name_prefix'];
		$cookie_name_safix	= $options['cookie_name_safix'];

		$time_limit = $cookie_expire !== 0 ? time() - $cookie_expire : -1;
		$cookie_pattern	= sprintf("/^%s.+%s$/", $cookie_name_prefix, $cookie_name_safix);

		if (!file_exists($cookie_save_dir)) {
			if (!mkdir($cookie_save_dir, 0755, true)) {
				throw CoreException::RaiseSystemError('クッキー認証用ディレクトリを構築できませんでした。dir_path:%s', [$cookie_save_dir]);
			}
		}

		foreach (new \DirectoryIterator($cookie_save_dir) as $entry) {
			if ($entry->isFile() && preg_match($cookie_pattern, $entry->getFilename()) === 1 && $entry->getMTime() < $time_limit) {
				unlink($entry->getPathname());
			}
		}
		return true;
	}

	/**
	 * 認証クッキーのサーバ側のファイルを保持するパスを返します。
	 *
	 * @param	string	$cookie_name		認証クッキー名
	 * @param	string	$cookie_save_dir	認証クッキー保存パス
	 */
	public static function CreateCookieSavePath ($cookie_name, $name = self::DEFAULT_CONNECTION_NAME) {
		return sprintf('%s/%s', rtrim(str_replace("\\", '/', static::GetAuthCookieSessionSavePath($name)), '/') , $cookie_name);
	}

	/**
	 * 認証クッキー名を構築します。
	 *
	 * @return	mixed	認証クッキーとして利用できる値がある場合はその値、ない場合はnull
	 */
	public static function CreateCookieName ($name = self::DEFAULT_CONNECTION_NAME) {
		$options = static::GetAuthCookieOptions($name);
		$cookie_save_dir	= $options['cookie_save_dir'];
		$cookie_name_prefix	= $options['cookie_name_prefix'];
		$cookie_name_safix	= $options['cookie_name_safix'];

		clearstatcache();
		for ($i = 0;$i < 100;$i++) {
			$cookie_name = sprintf('%s%s%s', $cookie_name_prefix, Hash::HmacString(rand(1, $i.microtime(true)).$cookie_name_prefix, rand(1, microtime(true)).$cookie_name_safix), $cookie_name_safix);
			if (!is_file(static::CreateCookieSavePath($cookie_name))) {
				return $cookie_name;
			}
		}

		return null;
	}

	/**
	 * 認証クッキーとして利用できる値を返します。
	 *
	 * @return	mixed	認証クッキーとして利用できる値がある場合はその値、ない場合はnull
	 */
	public static function GetAuthCookieValue ($name = self::DEFAULT_CONNECTION_NAME) {
		$options = static::GetAuthCookieOptions($name);

		$cookie_name_prefix	= $options['cookie_name_prefix'];
		$cookie_name_safix	= $options['cookie_name_safix'];

		$cookie_pattern	= sprintf("/^%s.+%s$/", $cookie_name_prefix, $cookie_name_safix);
		foreach (Request::GetCookies() as $name => $value) {
			if (preg_match($cookie_pattern, $name, $matches) !== 0) {
				return [$matches[0], $value];
			}
		}
		return null;
	}
}
