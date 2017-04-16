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
 * @package		crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\crypt;

/**
 * OpenSSLを扱うクラスです。
 *
 * @category	Flywheel2
 * @package		Crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class OpenSSL {
	use traits\RandomCryptTrait;

	/** @var	string	デフォルトとして使うハッシュアルゴリズム */
	const DEFAULT_HASH_ALGORITHM	= 'sha256';

	/** @var	string	デフォルトとして使う暗号化メソッド */
	const DEFAULT_CIPHER_METHODS	= 'AES-256-CBC';

	/** @var	string	バイトストリーム用にデフォルトとして使う暗号化メソッド */
	const DEFAULT_BYTE_STREAM_CIPHER_METHODS	= 'AES-256-CFB';

	const DEFAULT_OPENSSL_OPTION = \OPENSSL_RAW_DATA & \OPENSSL_ZERO_PADDING;

	/**
	 * @var	int	シークレットキーの長さ
	 * @static
	 */
	const SECRET_KEY_LENGTH	= 5;

	//==============================================
	//Encrypt
	//==============================================
	/**
	 * ファイルを暗号化して文字列として返します。
	 *
	 * @param	string	$file_path	暗号化するファイルのパス
	 * @param	string	$key		暗号化キー
	 * @param	array	$options	暗号化オプション
	 * @return	string	暗号化されたファイルの実体
	 */
	public static function EncryptFile ($file_path, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_encrypt(file_get_contents($file_path), $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * 文字列を暗号化します。
	 *
	 * @param	string	$message	暗号化する文字列
	 * @param	string	$key		暗号化キー
	 * @param	array	$options	暗号化オプション
	 * @return	string	暗号化された文字列
	 */
	public static function EncryptMessage ($message, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_encrypt($message, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * バイト列を暗号化します。
	 *
	 * @param	binary	$byte		バイナリ
	 * @param	string	$key		暗号化キー
	 * @param	array	$options	暗号化オプション
	 * @return	string	暗号化されたバイナリ
	 */
	public static function EncryptByteStream ($byte, $key, $options = []) {
		$options['module'] = static::DEFAULT_BYTE_STREAM_CIPHER_METHODS;
		$options = static::_AdjustOptions($options);
		return openssl_encrypt($byte, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * 暗号化します。
	 *
	 * @param	mixed	$data		暗号化するデータ
	 * @param	string	$key		暗号化キー
	 * @param	array	$options	暗号化オプション
	 * @return	string	暗号化されたデータ
	 */
	public static function Encrypt ($data, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_encrypt($data, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	//==============================================
	//Decrypt
	//==============================================
	/**
	 * ファイルを復号化して文字列として返します。
	 *
	 * @param	string	$file_path	復号化するファイルのパス
	 * @param	string	$key		復号化キー
	 * @param	array	$options	復号化オプション
	 * @return	string	復号化されたファイルの実体
	 */
	public static function DecryptFile ($file_path, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_decrypt(file_get_contents($file_path), $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * 文字列を復号化します。
	 *
	 * @param	string	$file_path	復号化するファイルのパス
	 * @param	string	$key		復号化キー
	 * @param	array	$options	復号化オプション
	 * @return	string	復号化された文字列
	 */
	public static function DecryptMessage ($message, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_decrypt($message, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * バイト列を復号化します。
	 *
	 * @param	binary	$byte		バイナリ
	 * @param	string	$key		復号化キー
	 * @param	array	$options	復号化オプション
	 * @return	string	復号化されたバイナリ
	 */
	public static function DecryptByteStream ($byte, $key, $options = []) {
		$options['module'] = static::DEFAULT_BYTE_STREAM_CIPHER_METHODS;
		$options = static::_AdjustOptions($options);
		return openssl_decrypt($byte, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	/**
	 * 復号化します。
	 *
	 * @param	mixed	$data		復号化するデータ
	 * @param	string	$key		復号化キー
	 * @param	array	$options	復号化オプション
	 * @return	string	復号化されたデータ
	 */
	public static function Decrypt ($data, $key, $options = []) {
		$options = static::_AdjustOptions($options);
		return openssl_decrypt($data, $options['method'], $key, $options['openssl_options'], $options['iv']);
	}

	//==============================================
	//Utility
	//==============================================
	/**
	 * オプションを調整します。
	 *
	 * @param	array	$options	調整するオプション
	 * @return	array	調整後のオプション
	 */
	private static function _AdjustOptions ($options) {
		$method	= isset($options['method']) ? $options['method'] : static::DEFAULT_CIPHER_METHODS;
		$iv		= isset($options['iv']) ? $options['iv'] : openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

		return [
			'method'			=> $method,
			'iv'				=> $iv,
			'openssl_options'	=> isset($options['options']) ? $options['options'] : static::DEFAULT_OPENSSL_OPTION ,
		];
	}

	/**
	 * ランダム暗号化用のデフォルトオプションを返します。
	 *
	 * OpenSSLではIVを固定する必要があるため、このメソッドの実装は必須となります。
	 *
	 * @param	binary	$binary
	 * @throws	\Exception
	 * @return	array
	 */
	protected static function GetRandomEncryptOptions ($binary) {
		$options = static::_AdjustOptions([]);
		$length = openssl_cipher_iv_length($options['method']);

		if (strlen($binary) >= $length) {
			$options['iv'] = substr($binary, 0, $length);
			return $options;
		}

		throw new \Exception('IVバイナリの長さがメソッドに対して足りていません。');
	}

	//==============================================
	// SSL証明書
	//==============================================
	/**
	 * 指定されたドメインのSSL証明書パラメータを取得し、返します。
	 *
	 * @param	string		$domain_name	SSL証明書のパラメータを取得するドメイン
	 * @param	int			$port			SSLで接続するポート デフォルトは443
	 * @param	int			$timeout		コネクションタイムアウトまでの秒数
	 * @return	array|bool	SSL証明書のパラメータを取得出来た場合はSSL証明書のパラメータの配列、そうでない場合はfalse
	 */
	public static function GetSSLCertificateParameters ($domain_name, $port = 443, $timeout = 30) {
		$sp = stream_socket_client(
			sprintf('ssl://%s:%d', $domain_name, $port),
			$errno,
			$errstr,
			$timeout,
			\STREAM_CLIENT_CONNECT,
			stream_context_create(['ssl' => [
				'verify_peer'				=> false,
				'verify_peer_name'			=> false,
				'allow_self_signed'			=> true,
				'capture_peer_cert'			=> true,
				'capture_peer_cert_chain'	=> true,
				'SNI_enabled'				=> true
			]])
		);

		if (!is_resource($sp)) {
			return false;
		}

		$parameters = stream_context_get_params($sp);
		fclose($sp);

		if (!isset($parameters['options']['ssl']['peer_certificate'])) {
			return false;
		}
		$peer_certificate = openssl_x509_parse($parameters['options']['ssl']['peer_certificate']);

		$peer_certificate_chain_list = [];
		foreach ($parameters['options']['ssl']['peer_certificate_chain'] as $peer_certificate_chain) {
			$peer_certificate_chain_list[] = openssl_x509_parse($peer_certificate_chain);
		}

		return [
			'peer_certificate'				=> $peer_certificate,
			'peer_certificate_chain_list'	=> $peer_certificate_chain_list,
			'self_signed_certificate'		=> $peer_certificate['issuer']['CN'] === $domain_name,
		];
	}

	/**
	 * 指定されたドメインのSSL証明書の有効期限を取得し、返します。
	 *
	 * @param	string		$domain_name	SSL証明書の有効期限を取得するドメイン
	 * @param	int			$port			SSLで接続するポート デフォルトは443
	 * @param	int			$timeout		コネクションタイムアウトまでの秒数
	 * @return	int|bool	SSL証明書の有効期限を取得出来た場合はtrue、そうでない場合はfalse
	 */
	public static function GetSSLCertificateValidTime ($domain_name, $port = 443, $timeout = 30) {
		$certification_parameters = static::GetSSLCertificateParameters($domain_name, $port, $timeout);
		return $certification_parameters !== false && strpos($certification_parameters['peer_certificate']['subject']['CN'], $domain_name) !== false ? $certification_parameters['peer_certificate']['validTo_time_t'] : null;
	}
}

