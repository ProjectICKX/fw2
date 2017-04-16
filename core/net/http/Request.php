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

namespace ickx\fw2\core\net\http;

use ickx\fw2\vartype\arrays\Arrays;
use  \ickx\fw2\vartype\arrays\LazyArrayObject;

/**
 * HTTPリクエストを扱います。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Request extends \ickx\fw2\core\net\http\Http {
	/**
	 * HTTPリクエストとして送られてきたデータをLazyArrayObjectでラップして返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	HTTPリクエストとして送られてきたデータ
	 */
	public static function GetInputVars () {
		return LazyArrayObject::Create([
			'parameter'	=> static::GetParameters(),
			'data'		=> static::GetPostData(),
			'cookie'	=> static::GetCookies(),
		]);
	}

	/**
	 * 現在の接続プロトコルを返します。
	 *
	 * @return	string	現在の接続プロトコル名
	 */
	public static function GetProtocol () {
		return static::LasyClassVarAccessCallback('protocol', function () {return sprintf('%s/%s', static::GetCurrnetProtocol(), static::VERSION_1_1);});
	}

	/**
	 * 現在の接続メソッド名を返します。
	 *
	 * @return	string	現在の接続メソッド名
	 */
	public static function GetMethod () {
		return PHP_SAPI === 'cli' ? 'GET' : $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * 現在の接続プロトコルがPOSTかどうか判定します。
	 *
	 * @return	bool	現在の接続プロトコルがPOSTの場合はboolean true、そうでない場合はfalse
	 */
	public static function IsPostMethod () {
		return static::GetMethod() === 'POST';
	}

	/**
	 * 現在のHTTPリクエストヘッダを返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	HTTPリクエストヘッダ
	 */
	public static function GetHeaders () {
		return static::LasyClassVarAccessCallback('headers', function () {
			if (function_exists('apache_request_headers')) {
				$request_header_list = apache_request_headers();
			} else {
				foreach($_SERVER as $key=>$value) {
					if (substr($key, 0, 5) == 'HTTP_') {
						$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
						$request_header_list[$key] = $value;
					}
				}
			}
			return $request_header_list;
		});
	}

	/**
	 * 引数で指定した現在のHTTPリクエストヘッダを返します。
	 *
	 * @param	string	$name	HTTPリクエストヘッダ名
	 * @return	mixed	HTTPリクエストヘッダ
	 */
	public static function GetHeader ($name) {
		return isset(static::GetHeaders()[$name]) ? static::GetHeaders()[$name] : false;
	}

	/**
	 * 現在の接続URLを返します。
	 *
	 * @return	string	現在の接続URL
	 */
	public static function GetUrl () {
		return static::LasyClassVarAccessCallback('url', function () {return (preg_match(sprintf("@^%s(.+)$@u", str_replace('@', "\\@", dirname($_SERVER['SCRIPT_NAME']))), $_SERVER['REQUEST_URI'], $matches) === 1) ? '/' . explode('?', $matches[1], 2)[0] : '';});
	}

	/**
	 * 現在のリクエストパラメータを返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	現在のリクエストパラメータ
	 */
	public static function GetParameters () {
		return static::LasyClassVarAccessCallback('_get', function () {return LazyArrayObject::RecursiveCreate($_GET);});
	}

	/**
	 * 現在のクエリストリングを返します。
	 *
	 * @return	string	現在のクエリストリング
	 */
	public static function GetQueryString () {
		return static::LasyClassVarAccessCallback('_query_string', function () {return $_SERVER['QUERY_STRING'];});
	}

	/**
	 * 現在のクエリストリングをパーズして返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	パーズされた現在のクエリストリング
	 */
	public static function GetParsedQueryString () {
		return static::LasyClassVarAccessCallback('_parsed_query_string', function () {
			parse_str(static::GetQueryString(), $result);
			return LazyArrayObject::RecursiveCreate($result);
		});
	}

	/**
	 * 現在のPOSTを返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	HTTPポスト
	 */
	public static function GetPost () {
		return static::LasyClassVarAccessCallback('_post', function () {return LazyArrayObject::RecursiveCreate($_POST);});
	}

	/**
	 * 現在のPOSTデータを返します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	HTTPポストデータ
	 */
	public static function GetPostData () {
		return static::LasyClassVarAccessCallback('data', function () {return LazyArrayObject::RecursiveCreate(static::GetPost()->data ?: []);});
	}

	/**
	 * 全てのクッキーを取得します。
	 *
	 * @return	\ickx\fw2\vartype\arrays\LazyArrayObject	Cookie
	 */
	public static function GetCookies () {
		return static::LasyClassVarAccessCallback('_cookie', function () {return LazyArrayObject::RecursiveCreate($_COOKIE);});
	}

	/**
	 * クッキーを取得します。
	 *
	 * @param	string		$name				クッキー名
	 * @param	callable	$callback_filter	値に対してかけるフィルタ
	 */
	public static function GetCookie ($name, $callback_filter = null) {
		if (is_callable($callback_filter)) {
			return call_user_func($callback_filter, Arrays::AdjustValue($_COOKIE, $name));
		}
		return Arrays::AdjustValue($_COOKIE, $name);
	}

	/**
	 * 指定したパラメータをなかったことにします。
	 *
	 * @param	string	$name	パラメータ名
	 */
	public static function RemoveParameter ($name) {
		if (static::HasClassVar('_get', $name)) {
			static::RemoveClassVar('_get', $name);
		}
		if (Arrays::ExistsLowest($_GET, $name)) {
			Arrays::RemoveLowest($_GET, $name);
		}
	}

	/**
	 * 指定したポストをなかったことにします。
	 *
	 * @param	string	$name	ポスト名
	 */
	public static function RemovePost ($name) {
		if (static::HasClassVar('_post', $name)) {
			static::RemoveClassVar('_post', $name);
		}
		if (Arrays::ExistsLowest($_POST, $name)) {
			Arrays::RemoveLowest($_POST, $name);
		}
	}

	/**
	 * 指定したポストデータをなかったことにします。
	 *
	 * @param	string	$name	ポストデータ名
	 */
	public static function RemovePostData ($name) {
		if (static::HasClassVar('data', $name)) {
			static::RemoveClassVar('data', $name);
		}
		if (Arrays::ExistsLowest($_POST, array_merge(['data'], (array) $name))) {
			Arrays::RemoveLowest($_POST, array_merge(['data'], (array) $name));
		}
	}

	/**
	 * 指定したパラメータを別の値で上書きします。
	 *
	 * @param	string	$name	パラメータ名
	 * @param	mixed	$value	上書きするパラメータ
	 */
	public static function OverWriteParameters ($name, $value) {
		static::SetClassVar(array_merge(['_get'], (array) $name), $value);
		$_GET = Arrays::SetLowest($_GET, $name, $value);
	}

	/**
	 * 指定したポストデータを別の値で上書きします。
	 *
	 * @param	string	$name	ポストデータ名
	 * @param	mixed	$value	上書きするデータ
	 */
	public static function OverWritePostData ($name, $value) {
		$keys = array_merge(['data'], Arrays::AdjustArray($name));
		static::SetClassVar($keys, $value);
		static::SetClassVar(array_merge(['_post'], $keys), $value);
		if (!isset($_POST['data'])) {
			$_POST['data'] = [];
		}
		$_POST['data'] = Arrays::SetLowest($_POST['data'], $name, $value);
	}

	/**
	 * HTTPリクエストクエリを構築します。
	 *
	 * @param	array	$query_set	クエリの元になる配列
	 * @return	string	構築されたHTTPリクエストクエリ
	 */
	public static function BuildQuery ($query_set) {
		foreach (['url', 'controller', 'action'] as $param_name) {
			if (isset($query_set[$param_name])) {
				unset($query_set[$param_name]);
			}
		}
		if (empty($query_set)) {
			return '';
		}
		return '?' . http_build_query($query_set);
	}

	/**
	 * 現在の接続におけるプロトコルを返します。
	 *
	 * HTTP接続時であることを仮定した場合の処理のため、他のプロトコル(FTPなど)で接続されているかどうかの判定には使用しないでください。
	 *
	 * @return	string	HTTP接続時にはstatic::PROTOCOL、HTTPS接続時にはstatic::PROTOCOL_SECURE
	 */
	public static function GetCurrnetProtocol () {
		return static::LasyClassVarConstAccessCallback('current_protocol', function () {return static::EnableSSL() ? static::PROTOCOL_SECURE : static::PROTOCOL;});
	}

	/**
	 * 現在の接続においてSSLが有効かどうか調べます。
	 *
	 * CORE SERVER JPの共用SSLなど、リバースプロキシ環境下では期待通りの値を取れない場合があります。
	 *
	 * @return	bool	SSLが有効な場合はTRUE、そうでない場合はFALSE
	 */
	public static function EnableSSL () {
		return static::LasyClassVarConstAccessCallback('enable_ssl', function () {
				return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
		});
	}

	/**
	 * 現在の接続においてGZIPエンコーディングが有効かどうか調べます。
	 *
	 * @return	bool	GZIPエンコーディングが有効な場合はTRUE、そうでない場合はFALSE
	 */
	public static function EnableGZipEncoding () {
		return static::LasyClassVarConstAccessCallback('accept_encoding_gzip', function () {
			$accept_encodings = \ickx\fw2\core\net\http\Request::GetHeader('Accept-Encoding');
			if ($accept_encodings === false) {
				return false;
			}
			foreach (explode(',', $accept_encodings) as $accept_encoding) {
				if (trim($accept_encoding) === 'gzip') {
					return true;
				}
			}
			return false;
		});
	}
}
