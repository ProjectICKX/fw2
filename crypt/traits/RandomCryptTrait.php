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

namespace ickx\fw2\crypt\traits;

use ickx\fw2\compression\CompressionGz;
use ickx\fw2\crypt\Hash;

/**
 * ランダム暗号化を扱うトレイトです。
 *
 * @category	Flywheel2
 * @package		Crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait RandomCryptTrait {
	//==============================================
	//Encrypt
	//==============================================
	/**
	 * 偽装付きで暗号化します。
	 *
	 * @param	string	$message			暗号化する文字列
	 * @param	string	$password			パスワード
	 * @param	string	$salt				ソルト
	 * @param	string	$hmac_key			HMACキー
	 * @param	int		$secret_key_length	シークレットキーの長さ
	 * @param	string	$algo				ハッシュアルゴリズム
	 * @return	string	生成したランダム文字列
	 */
	public static function EncryptRandom ($message, $password, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		//randamizer
		$secret_hmac_key = Hash::HmacStringStretching(rand(2, 5), microtime(true), mt_rand(), $algo);

		//passwrod1
		$password1 = static::CreateForwardPassword($message, $password, $salt, $hmac_key, $secret_key_length, $algo);
		if (strlen($password1) > 56) {
			$password1 = substr($password1, -56);
		}

		//binary passwrod 1
		$binary_passwrod_1 = static::CreateForwardBinaryPassword($message, $password, $salt, $hmac_key, $secret_key_length, $algo);

		//マスタ
		$encoded_message = base64_encode(static::EncryptMessage($message, $password1, static::GetRandomEncryptOptions($binary_passwrod_1)));

		//secret_key
		$secret_key = substr(base64_encode($secret_hmac_key), $secret_key_length, $secret_key_length);

		//passwrod2
		$password2 = Hash::HmacStringStretching($secret_key_length, $password, $secret_key);
		if (strlen($password2) > 56) {
			$password2 = substr($password2, -56);
		}

		//binary passwrod 2
		$binary_passwrod_2 = Hash::HmacBinaryStretching($secret_key_length, $password, $secret_key);

		//ダミー
		$encoded_message = base64_encode(static::EncryptMessage(CompressionGz::Compress($encoded_message), $password2, static::GetRandomEncryptOptions($binary_passwrod_2)));
		$encoded_message = rtrim($encoded_message, '=');

		//アウトプット
		$encoded_message = substr($encoded_message, 0, $secret_key_length) . $secret_key . substr($encoded_message, $secret_key_length);

		//加工
		$encoded_message_length = strlen($encoded_message);

		$encoded_message = str_pad($encoded_message, $encoded_message_length + $encoded_message_length % 4, '=');

		//処理の終了
		return str_pad($encoded_message, $encoded_message_length + $encoded_message_length % 4,  '=');
	}

	//==============================================
	//Decrypt
	//==============================================
	/**
	 * 偽装した暗号を復号化します。
	 *
	 * @param	string	$encoded_message	暗号化された文字列
	 * @param	string	$password			パスワード
	 * @param	string	$salt				ソルト
	 * @param	string	$hmac_key			HMACキー
	 * @param	int		$secret_key_length	シークレットキーの長さ
	 * @param	string	$algo				ハッシュアルゴリズム
	 * @return	string	複合化した文字列
	 */
	public static function DecryptRandom ($encoded_message, $password, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		//passwrod1
		$password1 = static::CreateForwardPassword($encoded_message, $password, $salt, $hmac_key, $secret_key_length, $algo);
		if (strlen($password1) > 56) {
			$password1 = substr($password1, -56);
		}

		//binary passwrod 1
		$binary_passwrod_1 = static::CreateForwardBinaryPassword($encoded_message, $password, $salt, $hmac_key, $secret_key_length, $algo);

		//複合テスト
		//逆加工
		$encoded_message = trim($encoded_message);

		//secret key
		$secret_key = substr($encoded_message, $secret_key_length, $secret_key_length);

		//インプット
		$encoded_message = substr($encoded_message, 0, $secret_key_length) . substr($encoded_message, strlen($secret_key) * 2);

		//passwrod2
		$password2 = Hash::HmacStringStretching($secret_key_length, $password, $secret_key);
		if (strlen($password2) > 56) {
			$password2 = substr($password2, -56);
		}

		//binary passwrod 2
		$binary_passwrod_2 = Hash::HmacBinaryStretching($secret_key_length, $password, $secret_key);

		//ダミー
		$encoded_message_length = strlen($encoded_message);
		$encoded_message = str_pad($encoded_message, $encoded_message_length + $encoded_message_length % 4,  '=');
		$encoded_message = CompressionGz::UnCompress(static::DecryptMessage(base64_decode($encoded_message), $password2, static::GetRandomEncryptOptions($binary_passwrod_2)));

		//マスタ
		return static::DecryptMessage(base64_decode($encoded_message), $password1, static::GetRandomEncryptOptions($binary_passwrod_1));
	}

	//==============================================
	//Utility
	//==============================================
	/**
	 * フォワードパスワードを構築します。
	 *
	 * @param unknown_type $message
	 * @param unknown_type $password
	 * @param unknown_type $salt
	 * @param unknown_type $hmac_key
	 * @param unknown_type $secret_key_length
	 * @param unknown_type $algo
	 */
	protected static function CreateForwardPassword ($message, $password, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		$hash_password = sprintf('\\\s_\s_\s/', $message, $password, $salt);
		return Hash::HmacStringStretching($secret_key_length, $hash_password, $hmac_key,  $algo) . $hash_password;
	}

	/**
	 * フォワードバイナリパスワードを構築します。
	 *
	 * @param unknown_type $message
	 * @param unknown_type $password
	 * @param unknown_type $salt
	 * @param unknown_type $hmac_key
	 * @param unknown_type $secret_key_length
	 * @param unknown_type $algo
	 */
	protected static function CreateForwardBinaryPassword ($message, $password, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		$hash_password = sprintf('\\\s_\s_\s/', $message, $password, $salt);
		return Hash::HmacBinaryStretching($secret_key_length, $hash_password, $hmac_key,  $algo) . $hash_password;
	}

	/**
	 * ランダム暗号化用のデフォルトオプションを返します。
	 *
	 * OpenSSLでのIVなど、初期値を固定したい場合に使用します。
	 *
	 * @param	binary	$binary
	 * @throws	\Exception
	 * @return	array
	 */
	protected static function GetRandomEncryptOptions () {
		return [];
	}
}
