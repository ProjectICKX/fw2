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

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\core\status\Status;

/**
 * HTTPを扱います。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Http implements interfaces\IMimeType {
	use	\ickx\fw2\traits\data_store\ClassVariableTrait,
		traits\HttpTrait,
		traits\MimeTypeTrait;

	//==============================================
	//プロトコル名
	//==============================================
	/** @var	string	プロトコル名：HTTP */
	const PROTOCOL			= 'HTTP';

	/** @var	string	プロトコル名：HTTPS */
	const PROTOCOL_SECURE	= 'HTTPS';

	//==============================================
	//プロトコルバージョン
	//==============================================
	/** @var	string	プロトコルバージョン：1.0 */
	const VERSION_1_0		= '1.0';

	/** @var	string	プロトコルバージョン：1.1 */
	const VERSION_1_1		= '1.1';

	/** @var	string	プロトコルバージョン：1.2 */
	const VERSION_1_2		= '1.2';

	//==============================================
	//ステータスコード
	//==============================================
	//----------------------------------------------
	//1xx Informational 情報
	//----------------------------------------------
	/** @var	int	ステータスコード：継続 */
	const STATUS_CONTINUE							= 100;

	/** @var	int	ステータスコード：プロトコルの切り替え */
	const STATUS_SWITCHING_PROTOCOLS				= 101;

	//----------------------------------------------
	//2xx Success 成功
	//----------------------------------------------
	/** @var	int	ステータスコード：成功 */
	const STATUS_OK									= 200;

	/** @var	int	ステータスコード：作成 */
	const STATUS_CREATED							= 201;

	/** @var	int	ステータスコード：受理 */
	const STATUS_ACCEPTED							= 202;

	/** @var	int	ステータスコード：信頼できない情報 */
	const STATUS_NON_AUTHORITATIVE_INFORMATION		= 203;

	/** @var	int	ステータスコード：内容なし */
	const STATUS_NO_CONTENT							= 204;

	/** @var	int	ステータスコード：内容のリセット */
	const STATUS_RESET_CONTENT						= 205;

	/** @var	int	ステータスコード：部分的内容 */
	const STATUS_PARTIAL_CONTENT					= 206;

	/** @var	int	ステータスコード：複数のステータス WebDAVの拡張ステータスコード */
	const STATUS_MULTI_STATUS						= 207;

	/** @var	int	ステータスコード：IM使用 Delta encoding in HTTPの拡張ステータスコード */
	const STATUS_IM_USED							= 208;

	//----------------------------------------------
	//3xx Redirection リダイレクション
	//----------------------------------------------
	/** @var	int	ステータスコード：複数の選択 */
	const STATUS_MULTIPLE_CHOICES					= 300;

	/** @var	int	ステータスコード：恒久的に移動 */
	const STATUS_MOVED_PERMANENTLY					= 301;

	/** @var	int	ステータスコード：発見した */
	const STATUS_FOUND								= 302;

	/** @var	int	ステータスコード：他を参照せよ */
	const STATUS_SEE_OTHER							= 303;

	/** @var	int	ステータスコード：未更新 */
	const STATUS_NOT_MODIFIED						= 304;

	/** @var	int	ステータスコード：プロキシを使用せよ */
	const STATUS_USE_PROXY							= 305;

	/** @var	int	ステータスコード：将来のために予約されている */
	const STATUS_UNUSED								= 306;

	/** @var	int	ステータスコード：一時的リダイレクト */
	const STATUS_TEMPORARY_REDIRECT					= 307;

	//----------------------------------------------
	//4xx Client Error クライアントエラー
	//----------------------------------------------
	/** @var	int	ステータスコード：リクエストが不正 */
	const STATUS_BAD_REQUEST						= 400;

	/** @var	int	ステータスコード：認証が必要 */
	const STATUS_UNAUTHORIZED						= 401;

	/** @var	int	ステータスコード：支払いが必要 */
	const STATUS_PAYMENT_REQUIRED					= 402;

	/** @var	int	ステータスコード：アクセス拒否 */
	const STATUS_FORBIDDEN							= 403;

	/** @var	int	ステータスコード：未検出 */
	const STATUS_NOT_FOUND							= 404;

	/** @var	int	ステータスコード：許可されていないメソッド */
	const STATUS_METHOD_NOT_ALLOWED					= 405;

	/** @var	int	ステータスコード：受理できない */
	const STATUS_NOT_ACCEPTABLE						= 406;

	/** @var	int	ステータスコード：プロキシ認証が必要 */
	const STATUS_PROXY_AUTHENTICATION_REQUIRED		= 407;

	/** @var	int	ステータスコード：リクエストタイムアウト */
	const STATUS_REQUEST_TIME_OUT					= 408;

	/** @var	int	ステータスコード：要求が現在のリソースと矛盾している */
	const STATUS_CONFLICT							= 409;

	/** @var	int	ステータスコード：消滅した */
	const STATUS_GONE								= 410;

	/** @var	int	ステータスコード：Content-Length ヘッダがない */
	const STATUS_LENGTH_REQUIRED					= 411;

	/** @var	int	ステータスコード：前提条件で失敗した */
	const STATUS_PRECONDITION_FAILED				= 412;

	/** @var	int	ステータスコード：リクエストエンティティが大きすぎる */
	const STATUS_REQUEST_ENTITY_TOO_LARGE			= 413;

	/** @var	int	ステータスコード：リクエストURIが大きすぎる */
	const STATUS_REQUEST_URI_TOO_LARGE				= 414;

	/** @var	int	ステータスコード：サポートしていないメディアタイプ */
	const STATUS_UNSUPPORTED_MEDIA_TYPE				= 415;

	/** @var	int	ステータスコード：リクエストしたレンジは範囲外にある */
	const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE	= 416;

	/** @var	int	ステータスコード：Expectヘッダによる拡張が失敗 */
	const STATUS_EXPECTATION_FAILED					= 417;

	/** @var	int	ステータスコード：私はティーポット ティーポットに珈琲を煎れさせようとした場合に発生 */
	const STATUS_I_AM_A_TEAPOT						= 418;

	/** @var	int	ステータスコード：処理できないエンティティ */
	const STATUS_UNPROCESSABLE_ENTITY				= 422;

	/** @var	int	ステータスコード：ロックされている */
	const STATUS_LOCKED								= 423;

	/** @var	int	ステータスコード：依存関係で失敗 */
	const STATUS_FAILED_DEPENDENCY					= 424;

	/** @var	int	ステータスコード：アップグレード要求 */
	const STATUS_UPGRADE_REQUIRED					= 426;

	//----------------------------------------------
	//5xx Server Error サーバエラー
	//----------------------------------------------
	/** @var	int	ステータスコード：サーバ内部エラー */
	const STATUS_INTERNAL_SERVER_ERROR				= 500;

	/** @var	int	ステータスコード：実装されていない */
	const STATUS_NOT_IMPLEMENTED					= 501;

	/** @var	int	ステータスコード：不正なゲートウェイ */
	const STATUS_BAD_GATEWAY						= 502;

	/** @var	int	ステータスコード：サービス利用不可 */
	const STATUS_SERVICE_UNAVAILABLE				= 503;

	/** @var	int	ステータスコード：ゲートウェイタイムアウト */
	const STATUS_GATEWAY_TIME_OUT					= 504;

	/** @var	int	ステータスコード：サポートしていないHTTPバージョン */
	const STATUS_HTTP_VERSION_NOT_SUPPORTED			= 505;

	/** @var	int	ステータスコード：Variant型でも交渉する */
	const STATUS_VARIANT_ALSO_NEGOTIATES			= 506;

	/** @var	int	ステータスコード：容量不足 */
	const STATUS_INSUFFICIENT_STORAGE				= 507;

	/** @var	int	ステータスコード：帯域幅制限超過 */
	const STATUS_BANDWIDTH_LIMIT_EXCEEDED			= 509;

	/** @var	int	ステータスコード：拡張できない */
	const STATUS_NOT_EXTENDED						= 510;

	//==============================================
	//static property
	//==============================================
	/** @var	string	現在のプロトコル */
	public $protocol		= 'HTTP';

	/** @var	string	現在のプロトコルバージョン */
	public $version			= '1.1';

	/** @var	string	現在のプロトコルとバージョン */
	public $full_protocol	= 'HTTP/1.1';

	/** @var	string	現在のホスト */
	public $host			= 'localhost';

	/** @var	int		現在のポート */
	public $port			= 80;

	/** @var	string	現在のパス */
	public $path			= null;

	/** @var	string	現在のユーザ名 */
	public $user_name		= null;

	/** @var	string	現在のパスワード */
	public $password		= null;

	/** @var	string	現在のファイルタイプ */
	public $type			= 'html';

	/** @var	string	現在のリクエストヘッダ */
	public $headers			= [];

	/** @var	string	現在のリクエストボディ */
	public $body			= [];

	/**
	 * 上書きを許可するプロパティか判定します。
	 *
	 * @param	string	$property_name	上書きできるか確認するプロパティ名
	 * @return	bool	上書きを許可できるプロパティの場合はTRUE、そうでない場合はFALSE
	 */
	protected static function _OverwritePropertyFilter ($property) {
		$class_name = static::class;
		return isset(static::LasyClassVarConstAccessCallback('overwritable_property', function () use ($class_name) {
			$temp = $class_name::_OverwritablePropertyList();
			return array_combine($temp, $temp);
		})[key($property)]);
	}

	/**
	 * 上書きを許可するプロパティを返します。
	 *
	 */
	protected static function _OverwritablePropertyList () {
		return ['protocol', 'version', 'full_protocol', 'host', 'port', 'path', 'user_name', 'password', 'type'];
	}

	/**
	 * 引数からURLを生成します。
	 *
	 * @param	array	$paths				URLを生成するための配列
	 * @param	bool	$add_end_separator	末尾に/を付けるかどうか boolean trueの場合はつける、そうでない場合はつけない
	 * @return	string	生成されたURL
	 */
	public static function MakeUrl ($paths, $add_end_separator = true) {
		return sprintf('/%s%s',
			implode('/', $paths),
			($add_end_separator) ? '/' : ''
		);
	}

	/**
	 * 簡易的にURLの実在を調べます。
	 *
	 * @param	$url				string	実在を調べるURL
	 * @param	$timeout			int		リクエストタイムアウト
	 * @param	$protocol_version	string	HTTPプロトコルバージョン
	 * @return	bool		接続先へのタイムアウトまたは404だった場合はfalse、そうでなければtrue
	 */
	public static function UrlExsist ($url, $timeout = 30, $protocol_version = self::VERSION_1_1) {
		$result = static::HeadRequest($url, $timeout, $protocol_version);
		return isset($result['http']['status']['code']) && $result['http']['status']['code'] !== '404';
	}

	/**
	 * URLへheadメソッドで接続し、結果を返します。
	 *
	 * @param	$url				string	接続先URL
	 * @param	$timeout			int		リクエストタイムアウト
	 * @param	$protocol_version	string	HTTPプロトコルバージョン
	 * @return	bool		接続先へのタイムアウトまたは404だった場合はfalse、そうでなければtrue
	 */
	public static function HeadRequest ($url, $timeout = 30, $protocol_version = self::VERSION_1_1) {
		$parsed_url = parse_url($url);

		$host = $parsed_url['host'];
		if (!isset($parsed_url['port'])) {
			$port = $parsed_url['scheme'] === 'https' ? 443 : 80;
		}
		$path = $parsed_url['path'];

		$request_headers = [
			sprintf('HEAD %s HTTP/%s', $path, $protocol_version),
			'User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)',
			sprintf('Host: %s', $host),
			'Connection: Close',
		];
		$request_header = implode("\r\n", $request_headers) . "\r\n\r\n";

		$schema = $parsed_url['scheme'] === 'https' ? 'ssl://' : '';

		$fp = fsockopen($schema . $host, $port, $errno, $errstr, $timeout);
		if (!$fp) {
			CoreException::ScrubbedThrow(Status::SystemError(sprintf('%s (%s)', $errstr, $errno)));
		}

		fwrite($fp, $request_header);

		$return_data = [];
		while (!feof($fp)) {
			$return_data[] = trim(fgets($fp, 4000));
		}
		fclose($fp);

		$result = [];
		foreach ($return_data as $row) {
			if (preg_match("/^HTTP\/(\d+.\d+) (\d+) (.+)$/", $row, $matches) === 1) {
				$result['http'] = [
					'version'	=> $matches[1],
					'status'	=> [
						'code'		=> $matches[2],
						'message'	=> $matches[3],
					],
				];
				continue;
			}
			if (preg_match("/^([^:]+): (.+)/", $row, $matches) === 1) {
				$result[$matches[1]] = $matches[2];
				continue;
			}
		}
		return $result;
	}

	/**
	 * postメソッドで接続し、結果を返します。
	 *
	 * @param	$url				string	接続先URL
	 * @param	$timeout			int		リクエストタイムアウト
	 * @param	$protocol_version	string	HTTPプロトコルバージョン
	 * @return	bool		接続先へのタイムアウトまたは404だった場合はfalse、そうでなければtrue
	 */
	public static function PostRequest ($url, $post_data = [], $timeout = 30, $protocol_version = self::VERSION_1_1, $options = []) {
		$parsed_url = parse_url($url);

		$host = $parsed_url['host'];
		if (!isset($parsed_url['port'])) {
			$port = $parsed_url['scheme'] === 'https' ? 443 : 80;
		}
		$path = $parsed_url['path'];

		$request_headers = [
			sprintf('POST %s HTTP/%s', $path, $protocol_version),
			sprintf('Host: %s', $host),
			'User-Agent: Mozilla/4.0 (compatible; MSIE5.01; Windows NT)',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: ja,en-us;q=0.7,en;q=0.3',
			'Accept-Encoding: gzip, deflate',
			'Connection: keep-alive',
			'Content-Type: application/x-www-form-urlencoded',
		];

		$enable_send_data = !empty($post_data);
		if ($enable_send_data) {
			$raw_post_data = http_build_query($post_data);
			$raw_post_data_length = strlen($raw_post_data);
			$request_headers[] = sprintf('Content-Length: %d', $raw_post_data_length);
		}
		if (isset($options['basic']) && is_array($options['basic'])) {
			$request_headers[] = sprintf('Authorization: Basic %s', base64_encode(implode(':', $options['basic'])));
		}

		$request_header = implode("\r\n", $request_headers) . "\r\n\r\n";

		$request = $enable_send_data ? sprintf("%s%s", $request_header, $raw_post_data) : $request_header;
		$request .= "\r\n\r\n";

		$schema = $parsed_url['scheme'] === 'https' ? 'ssl://' : '';

$data = http_build_query($post_data);

$header = array(
	'Content-Type: application/x-www-form-urlencoded',
	'Content-Length: '.strlen($data),
	'Authorization: Basic '.base64_encode('a:a'),
);

$context = array(
	'http' => array(
		'method'	=> 'POST',
		'header'	=> implode("\r\n", $header),
		'content'	=> $data,
	),
);

echo file_get_contents($url, false, stream_context_create($context));

		/*
		$fp = fsockopen($schema . $host, $port, $errno, $errstr, $timeout);
		if (!$fp) {
			CoreException::ScrubbedThrow(Status::SystemError(sprintf('%s (%s)', $errstr, $errno)));
		}

		fwrite($fp, $request_header);

		$return_data = [];
		$start_mts = microtime(true);
		while (!feof($fp) && (microtime(true) - $start_mts) < $timeout) {
			$return_data[] = trim(fgets($fp, 4000));
		}
		fclose($fp);

		$response_header = [];
		$response_body = [];
		$in_header = true;
		foreach ($return_data as $row) {
			if ($in_header && trim($row) === "") {
				$in_header = false;
				continue;
			}
			if ($in_header) {
				if (preg_match("/^HTTP\/(\d+.\d+) (\d+) (.+)$/", $row, $matches) === 1) {
					$response_header['http'] = [
						'version'	=> $matches[1],
						'status'	=> [
							'code'		=> $matches[2],
							'message'	=> $matches[3],
						],
					];
					continue;
				}
				if (preg_match("/^([^:]+): (.+)/", $row, $matches) === 1) {
					$response_header[$matches[1]] = $matches[2];
					continue;
				}
			} else {
				$response_body[] = $row;
			}
		}
		return [
			'header'	=> $response_header,
			'body'		=> $response_body,
		];
		*/
	}
}
