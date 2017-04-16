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
 * ＜非推奨＞暗号を扱うクラスです。
 *
 * !!注意!!
 * PHP7.2より使用できなくなります。
 *
 * @category	Flywheel2
 * @package		Crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Mcrypt {
	use traits\RandomCryptTrait;

	/** @var	string	デフォルトとして使うハッシュアルゴリズム */
	const DEFAULT_HASH_ALGORITHM = 'sha256';

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
		$options['module'] = \MCRYPT_MODE_CBC;
		return static::Encrypt(file_get_contents($file_path), $key, $options);
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
		$options['module'] = \MCRYPT_MODE_ECB;
		return static::Encrypt($message, $key, $options);
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
		$options['module'] = \MCRYPT_MODE_CFB;
		return static::Encrypt($byte, $key, $options);
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

		$td = static::_OpenMcrypt($key, $options);
		$block_size = mcrypt_enc_get_block_size($td);

		$pad = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($pad), $pad);

		$data = mcrypt_generic($td, $data);
		static::_CloseMcrypt($td);

		return $data;
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
		$options['module'] = \MCRYPT_MODE_CBC;
		return static::Decrypt(file_get_contents($file_path), $key, $options);
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
		$options['module'] = \MCRYPT_MODE_ECB;
		return static::Decrypt($message, $key, $options);
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
		$options['module'] = \MCRYPT_MODE_CFB;
		return static::Decrypt($byte, $key, $options);
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
		$td = static::_OpenMcrypt($key, $options);
		$data = @mdecrypt_generic($td, $data);
		static::_CloseMcrypt($td);

		$pad = ord($data{strlen($data) - 1});
		if ($pad > strlen($data)) {
			return false;
		}

		if (strspn($data, chr($pad), strlen($data) - $pad) != $pad) {
			return false;
		}

		return substr($data, 0, -1 * $pad);
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
		return [
			'cipher'				=> isset($options['cipher']) ? $options['cipher'] : \MCRYPT_BLOWFISH,
			'module'				=> isset($options['module']) ? $options['module'] : \MCRYPT_MODE_ECB,
			'algorithm_directory'	=> isset($options['algorithm_directory']) ? $options['algorithm_directory'] : '',
			'mode_directory'		=> isset($options['mode_directory']) ? $options['mode_directory'] : '',
			'random_source'			=> isset($options['random_source']) ? $options['random_source'] : \MCRYPT_DEV_URANDOM,
		];
	}

	/**
	 * ブロックサイズを取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	int		ブロックサイズ
	 */
	private static function _GetBlockSize ($options) {
		return mcrypt_get_block_size($options['cipher'], $options['module']);
	}

	/**
	 * Mcryptモジュールを開きます。
	 *
	 * @param	string		$key		暗号化キー
	 * @param	array		$options	オプション
	 * @return	resource	暗号化記述子
	 */
	private static function _OpenMcrypt ($key, $options) {
		$td = mcrypt_module_open($options['cipher'], $options['algorithm_directory'],  $options['module'], $options['mode_directory']);
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv = mcrypt_create_iv($iv_size, $options['random_source']);
		mcrypt_generic_init($td, $key, $iv);
		return $td;
	}

	/**
	 * Mcryptモジュールを閉じます。
	 *
	 * @param	resource	暗号化記述子
	 */
	private static function _CloseMcrypt ($td) {
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
	}
}
