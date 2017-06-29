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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\curl;

/**
 * cURLを用いた接続のラッパークラスです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Curl {
	use	\ickx\fw2\traits\magic\Accessor;

	public const CURL_INFO_LIST	= [
		\CURLINFO_EFFECTIVE_URL				=> 'CURLINFO_EFFECTIVE_URL',			// 直近の有効な URL
		\CURLINFO_HTTP_CODE					=> 'CURLINFO_HTTP_CODE',				// 最後に受け取った HTTP コード
		\CURLINFO_FILETIME					=> 'CURLINFO_FILETIME',					// ドキュメントを取得するのにかかった時間。 CURLOPT_FILETIME が有効な状態で用いる。 取得できなかった場合は -1
		\CURLINFO_TOTAL_TIME				=> 'CURLINFO_TOTAL_TIME',				// 直近の伝送にかかった秒数
		\CURLINFO_NAMELOOKUP_TIME			=> 'CURLINFO_NAMELOOKUP_TIME',			// 名前解決が完了するまでにかかった秒数
		\CURLINFO_CONNECT_TIME				=> 'CURLINFO_CONNECT_TIME',				// 接続を確立するまでにかかった秒数
		\CURLINFO_PRETRANSFER_TIME			=> 'CURLINFO_PRETRANSFER_TIME',			// 開始からファイル伝送がはじまるまでにかかった秒数
		\CURLINFO_STARTTRANSFER_TIME		=> 'CURLINFO_STARTTRANSFER_TIME',		// 最初のバイトの伝送がはじまるまでの秒数
		\CURLINFO_REDIRECT_COUNT			=> 'CURLINFO_REDIRECT_COUNT',			// リダイレクト処理の回数 (CURLOPT_FOLLOWLOCATION オプションが有効な場合)
		\CURLINFO_REDIRECT_TIME				=> 'CURLINFO_REDIRECT_TIME',			// 伝送が始まるまでのリダイレクト処理の秒数 (CURLOPT_FOLLOWLOCATION オプションが有効な場合)
		\CURLINFO_REDIRECT_URL				=> 'CURLINFO_REDIRECT_URL',				// CURLOPT_FOLLOWLOCATION オプションが無効な場合: 直近のトランザクションで見つかったリダイレクト先 URL。これを、次に手動でリクエストしなければいけません。 CURLOPT_FOLLOWLOCATION オプションが有効な場合: これは空になります。 この場合のリダイレクト先 URL は、CURLINFO_EFFECTIVE_URL となります。
		\CURLINFO_PRIMARY_IP				=> 'CURLINFO_PRIMARY_IP',				// 直近の接続の IP アドレス
		\CURLINFO_PRIMARY_PORT				=> 'CURLINFO_PRIMARY_PORT',				// 直近の接続の接続先ポート
		\CURLINFO_LOCAL_IP					=> 'CURLINFO_LOCAL_IP',					// 直近の接続の接続元 IP アドレス
		\CURLINFO_LOCAL_PORT				=> 'CURLINFO_LOCAL_PORT',				// 直近の接続の接続元ポート
		\CURLINFO_SIZE_UPLOAD				=> 'CURLINFO_SIZE_UPLOAD',				// アップロードされたバイト数
		\CURLINFO_SIZE_DOWNLOAD				=> 'CURLINFO_SIZE_DOWNLOAD',			// ダウンロードされたバイト数
		\CURLINFO_SPEED_DOWNLOAD			=> 'CURLINFO_SPEED_DOWNLOAD',			// 平均のダウンロード速度
		\CURLINFO_SPEED_UPLOAD				=> 'CURLINFO_SPEED_UPLOAD',				// 平均のアップロード速度
		\CURLINFO_HEADER_SIZE				=> 'CURLINFO_HEADER_SIZE',				// 受信したヘッダのサイズ
		\CURLINFO_HEADER_OUT				=> 'CURLINFO_HEADER_OUT',				// 送信したリクエスト文字列。 これを動作させるには、curl_setopt() をコールする際に CURLINFO_HEADER_OUT オプションを使うようにしておく必要があります。
		\CURLINFO_REQUEST_SIZE				=> 'CURLINFO_REQUEST_SIZE',				// 発行されたリクエストのサイズ。現在は HTTP リクエストの場合のみ
		\CURLINFO_SSL_VERIFYRESULT			=> 'CURLINFO_SSL_VERIFYRESULT',			// CURLOPT_SSL_VERIFYPEER を設定した際に要求される SSL 証明書の認証結果
		\CURLINFO_CONTENT_LENGTH_DOWNLOAD	=> 'CURLINFO_CONTENT_LENGTH_DOWNLOAD',	// ダウンロードされるサイズ。 Content-Length: フィールドの内容を取得する
		\CURLINFO_CONTENT_LENGTH_UPLOAD		=> 'CURLINFO_CONTENT_LENGTH_UPLOAD',	// アップロードされるサイズ。
		\CURLINFO_CONTENT_TYPE				=> 'CURLINFO_CONTENT_TYPE',				// 要求されたドキュメントの Content-Type:。 NULL は、サーバーが適切な Content-Type: ヘッダを返さなかったことを示す
		\CURLINFO_PRIVATE					=> 'CURLINFO_PRIVATE',					// この cURL ハンドルに関連づけられたプライベートデータ。 事前に curl_setopt() の CURLOPT_PRIVATE オプションで設定したもの。
		\CURLINFO_RESPONSE_CODE				=> 'CURLINFO_RESPONSE_CODE',			// 直近のレスポンスコード。
		\CURLINFO_HTTP_CONNECTCODE			=> 'CURLINFO_HTTP_CONNECTCODE',			// CONNECT のレスポンスコード。
		\CURLINFO_HTTPAUTH_AVAIL			=> 'CURLINFO_HTTPAUTH_AVAIL',			// 直前のレスポンスから判断する、利用可能な認証方式のビットマスク。
		\CURLINFO_PROXYAUTH_AVAIL			=> 'CURLINFO_PROXYAUTH_AVAIL',			// 直前のレスポンスから判断する、プロキシ認証方式のビットマスク。
		\CURLINFO_OS_ERRNO					=> 'CURLINFO_OS_ERRNO',					// 接続に失敗したときのエラー番号。OS やシステムによって異なります。
		\CURLINFO_NUM_CONNECTS				=> 'CURLINFO_NUM_CONNECTS',				// curl が直前の転送を実行するために要した接続数。
		\CURLINFO_SSL_ENGINES				=> 'CURLINFO_SSL_ENGINES',				// サポートする OpenSSL 暗号エンジン。
		\CURLINFO_COOKIELIST				=> 'CURLINFO_COOKIELIST',				// すべての既知のクッキー。
		\CURLINFO_FTP_ENTRY_PATH			=> 'CURLINFO_FTP_ENTRY_PATH',			// FTP サーバーのエントリパス。
		\CURLINFO_APPCONNECT_TIME			=> 'CURLINFO_APPCONNECT_TIME',			// リモートホストとの SSL/SSH 接続／ハンドシェイク が完了するまでに要した秒数。
		\CURLINFO_CERTINFO					=> 'CURLINFO_CERTINFO',					// TLS 証明書チェイン。
		\CURLINFO_CONDITION_UNMET			=> 'CURLINFO_CONDITION_UNMET',			// 時間の条件が満たされなかったことに関する情報。
		\CURLINFO_RTSP_CLIENT_CSEQ			=> 'CURLINFO_RTSP_CLIENT_CSEQ',			// 次の RTSP クライアントの CSeq。
		\CURLINFO_RTSP_CSEQ_RECV			=> 'CURLINFO_RTSP_CSEQ_RECV',			// 直前に受け取った CSeq。
		\CURLINFO_RTSP_SERVER_CSEQ			=> 'CURLINFO_RTSP_SERVER_CSEQ',			// 次の RTSP サーバーの CSeq。
		\CURLINFO_RTSP_SESSION_ID			=> 'CURLINFO_RTSP_SESSION_ID',			// RTSP セッション ID。
	];

	protected $url			= null;
	protected $curlOptions	= [
		\CURLOPT_RETURNTRANSFER	=> true,
		\CURLINFO_HEADER_OUT	=> true,
		\CURLOPT_HEADER			=> true,
	];
	protected $sslVerify	= true;
	protected $headers		= null;
	protected $parameters	= null;
	protected $bodies		= null;

	public static function init ($url = null) {
		return new static($url);
	}

	protected function __construct ($url = null) {
		$this->url	= $url;
	}

	public function appendCurlOptions ($options) {
		$this->curlOptions = $options + $this->curlOptions;
		return $this;
	}

	public function removeCurlOptions ($options) {
		foreach ((array) $options as $option) {
			unset($this->curlOptions[$option]);
		}
		return $this;
	}

	public function appendHttpHeaders ($headers) {
		$this->headers = $headers + $this->headers;
		return $this;
	}

	public function removeHttpOptions ($keys) {
		foreach ((array) $keys as $key) {
			unset($this->headers[$key]);
		}
		return $this;
	}

	public function exec () {
		$curl_options	= [];

		$url	= $this->url;
		if (is_array($this->parameters)) {
			$url	= sprintf('%s?%s', $url, http_build_query($this->parameters));
		}

		$curl_options[\CURLOPT_URL]	= $url;

		if (is_array($this->headers)) {
			foreach ($this->headers ?? [] as $key => $value) {
				$curl_options[\CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $key, $value);
			}
		}

		if (is_array($this->bodies)) {
			$curl_options[\CURLOPT_POST]		= true;
			$curl_options[\CURLOPT_POSTFIELDS]	= http_build_query($this->bodies);
		}

		$curl_options[\CURLOPT_SSL_VERIFYPEER]	= $this->sslVerify;
		$curl_options[\CURLOPT_SSL_VERIFYHOST]	= $this->sslVerify;

		$this->appendCurlOptions($curl_options);

		$curl	= curl_init();
		curl_setopt_array($curl, $this->curlOptions);


		$response	= curl_exec($curl);
		$header		= curl_getinfo($curl);

		foreach (static::CURL_INFO_LIST as $key => $opt) {
			$header[$key] = curl_getinfo($curl, $key);
		}

		curl_close($curl);

		$header_size	= $header[\CURLINFO_HEADER_SIZE];

		$raw_header	= substr($response, 0, $header_size);
		$body		= substr($response, $header_size);

		return [
			'response'			=> $response,
			'header'			=> $header,
			'header_size'		=> $header_size,
			'raw_header'		=> $raw_header,
			'body'				=> $body,
		];
	}
}
