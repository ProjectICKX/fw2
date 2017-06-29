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
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\basic\log;

use ickx\fw2\core\environment\Environment;
use ickx\fw2\core\cli\Cli;

/**
 * 詳細なロギングを提供します。
 *
 * @category	Flywheel2
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Log {
	const DEFAULT_PASSWORD			= 'zAl+j1/&3PjiB_?9uVFOUUX0Ji,p:ef=';
	const DEFAULT_SALT				= 'q|G7eoGJ.LY]kMov*5Z|x;%$Xrw4NCi(';
	const DEFAULT_HMAC_KEY			= '*1Xroo{px3wR0VI.zz@U\6`G|:N_2`B.';
	const DEFAULT_SECRET_KEY_LENGTH	= 8;
	const DEFAULT_HASH_ALGORITHM	= 'sha256';

	public static function Capture ($log_dir, $options = []) {
		//取得対象外URIリスト
		$exclusion_uri_list = isset($options['exclusion_uri']) ? (array) $options['exclusion_uri'] : [];

		//取得対象外URIの場合、処理終了
		$request_uri = Environment::IsCli() ? Cli::GetFirstParameter() : $_SERVER['REQUEST_URI'];
		if (in_array($request_uri, $exclusion_uri_list, true)) {
			return null;
		}

		//取得対象外IPリスト
		$exclusion_remote_addr_list = isset($options['exclusion_remote_addr']) ? (array) $options['exclusion_remote_addr'] : [];

		//取得対象外URIの場合、処理終了
		$remote_addr = Environment::IsCli() ? null : $_SERVER['REMOTE_ADDR'];
		if (in_array($remote_addr, $exclusion_remote_addr_list, true)) {
			return null;
		}

		//ログの稼働切り分け
		$disable_curl_log = isset($options['disable_curl_log']) && $options['disable_curl_log'];
		$disable_json_log = isset($options['disable_json_log']) && $options['disable_json_log'];

		//ロギング
		$disable_curl_log ?: static::CaptureStyleCurl($log_dir, $options);
		$disable_json_log ?: static::CaptureStyleJson($log_dir, $options);
	}

	protected static function _AdjustOutputOptions ($options) {
		//出力結果の暗号化設定
		$encrypt_setting = [
			'encrypt_disable'	=> false,
			'encrypt_function'	=> '\ickx\fw2\crypt\OpenSSL::EncryptRandom',
			'encrypt_argument'	=> [':replace_point:value', static::DEFAULT_PASSWORD, static::DEFAULT_SALT, static::DEFAULT_HMAC_KEY, static::DEFAULT_SECRET_KEY_LENGTH, static::DEFAULT_HASH_ALGORITHM],
		];

		//ログフォーマット
		$log_format = [
			'log_format'	=> "[%s] %s\n",
			'log_values'	=> [static::GetRequestTime(), ':replace_point:value'],
		];

		//一括設定
		$options_settings = [
			$encrypt_setting,
			$log_format,
		];
		foreach ($options_settings as $options_setting) {
			foreach ($options_setting as $name => $setting) {
				isset($options[$name]) ?: $options[$name] = $setting;
			}
		}

		return $options;
	}

	public static function CaptureStyleCurl ($log_dir, $options = []) {
		//ログパスの構築
		$curl_log_dir = isset($options['curl_log_name']) ? $options['curl_log_name'] : $log_dir;
		$curl_log_name = isset($options['curl_log_name']) ? $options['curl_log_name'] : 'curl.log';
		$log_file_path = implode(DIRECTORY_SEPARATOR, [$curl_log_dir, $curl_log_name]);

		//$ex_field
		$ex_field = [];

		//protocol:ez impl
		$https_flag = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
		$protocol = $https_flag ? 'https' : 'http';

		//basic auth
		$basic_auth_user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
		$basic_auth_password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;

		$use_basic_auth = $basic_auth_user || $basic_auth_password;

		$auth_info = $use_basic_auth ? sprintf('%s:%s@', $basic_auth_user, $basic_auth_password) : null;

		//host name
		$server_name_target = $_SERVER['SERVER_NAME'] ?? null;
		if (isset($_SERVER['HTTP_HOST'])) {
			$server_name_target = $_SERVER['SERVER_NAME'] !== $_SERVER['HTTP_HOST'] ? 'HTTP_HOST' : 'SERVER_NAME';
		}
		$host_name = $_SERVER[$server_name_target] ?? null;

		//server port
		$server_port = $_SERVER['SERVER_PORT'] ?? null;
		$server_port = $server_port === '80' || ($https_flag && $server_port === '443') ? null : $server_port;

		//request url
		$request_uri = Environment::IsCli() ? Cli::GetFirstParameter() : $_SERVER['REQUEST_URI'];

		//url
		$url = sprintf('%s://%s%s%s%s', $protocol, $auth_info, $host_name, $server_port, $request_uri);

		//header
		$request_header_list = Environment::IsCli() ? [] : Request::GetHeaders();
		$header_list = [];
		foreach ($request_header_list as $name => $value) {
			$header_list[$name] = sprintf("-H %s", escapeshellarg(sprintf('%s: %s', $name, $value)));
		}

		$data = '';

		if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
			if (isset($request_header_list['Content-Type']) && substr($request_header_list['Content-Type'], 0, 20) === 'multipart/form-data;' && preg_match("@^multipart/form-data; boundary=\-+([^\-]+)$@", $request_header_list['Content-Type'], $ret)) {
				unset($header_list['Content-Type']);

				$data = [];
				foreach ($_POST as $name => $value) {
					$data[] = sprintf('-F "%s"', http_build_query([$name => $value]));
				}
				$data = implode(' ', $data);

				if (!empty($_FILES)) {
					$ex_field[] = sprintf('### file upload. dump $_FILES : "%s"', rawurlencode(var_export($_FILES, true)));
				}
			} else {
				$data = sprintf("--data %s", file_get_contents('php://input'));
			}
		}

		//curl
		//curl '%s -g -k -s -S %s %s %s', escapeshellarg($url), $header, $data, $ex_field
		$header = implode(' ', $header_list);

		$ex_field = implode(' ', $ex_field);

		$curl = sprintf('curl "%s" -g -k -s -S %s %s %s', $url, $header, $data, $ex_field);

		static::Write($curl, $log_file_path, $options);
	}

	public static function CaptureStyleJson ($log_dir, $options = []) {
		//ログパスの構築
		$json_log_dir = isset($options['json_log_name']) ? $options['json_log_name'] : $log_dir;
		$json_log_name = isset($options['json_log_name']) ? $options['json_log_name'] : 'json.log';
		$log_file_path = implode(DIRECTORY_SEPARATOR, [$json_log_dir, $json_log_name]);

		$request_uri = Environment::IsCli() ? Cli::GetFirstParameter() : $_SERVER['REQUEST_URI'];

		$json_data = [
			'REQUEST_URI'	=> $request_uri,
			'GET'			=> $_GET,
			'POST'			=> $_POST,
			'COOKIE'		=> $_COOKIE,
			'SESSION'		=> isset($_SESSION) ? $_SESSION : null,
			'FILES'			=> $_FILES,
		];

		static::Write(json_encode($json_data, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE), $log_file_path, $options);
	}

	public static function Write ($message, $log_file_path, $options = []) {
		$options = static::_AdjustOutputOptions($options);

		if (!$options['encrypt_disable']) {
			foreach ($options['encrypt_argument'] as $idx => $value) {
				if ($value === ':replace_point:value') {
					$options['encrypt_argument'][$idx] = $message;
				}
			}
			$message = call_user_func_array($options['encrypt_function'], $options['encrypt_argument']);
		}

		foreach ($options['log_values'] as $idx => $value) {
			if ($value === ':replace_point:value') {
				$options['log_values'][$idx] = static::ReplaceNullByte($message);
			}
		}

		error_log(vsprintf($options['log_format'], $options['log_values']), 3, $log_file_path);
	}

	public static function ReplaceNullByte ($string) {
		return str_replace(["\0", "\x00"], "<<Null Byte Char>>", $string);
	}

	public static function GetRequestTime () {
		return sprintf('%s.%-06s', date('Y-m-d H:i:s', floor($_SERVER['REQUEST_TIME_FLOAT'])), explode('.', $_SERVER['REQUEST_TIME_FLOAT'])[1]);
	}
}
