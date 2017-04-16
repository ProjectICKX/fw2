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
 * ハッシュを扱うクラスです。
 *
 * @category	Flywheel2
 * @package		Crypt
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Hash {
	/** @var	string	デフォルトとして使うハッシュアルゴリズム */
	const DEFAULT_HASH_ALGORITHM = 'sha256';

	/**
	 * @var	int	シークレットキーの長さ
	 * @static
	 */
	const SECRET_KEY_LENGTH	= 5;

	/**
	 * 文字列を元にハッシュ文字列を生成します。
	 *
	 * @param	string	$data	文字列
	 * @param	string	$algo	ハッシュアルゴリズム
	 * @return	string	ハッシュ文字列
	 */
	public static function String ($data, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash($algo, $data, false);
	}

	/**
	 * 文字列を元にハッシュバイナリを生成します。
	 *
	 * @param	string	$data	文字列
	 * @param	string	$algo	ハッシュアルゴリズム
	 * @return	string	ハッシュバイナリ
	 */
	public static function Binary ($data, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash($algo, $data, true);
	}

	/**
	 * キーと文字列を元にハッシュ文字列を生成します。
	 *
	 * @param	string	$data	文字列
	 * @param	string	$key	キー
	 * @param	string	$algo	ハッシュアルゴリズム
	 * @return	string	ハッシュ文字列
	 */
	public static function HmacString ($data, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash_hmac($algo, $data, $key, false);
	}

	/**
	 * キーと文字列を元にハッシュバイナリを生成します。
	 *
	 * @param	string	$data	文字列
	 * @param	string	$key	キー
	 * @param	string	$algo	ハッシュアルゴリズム
	 * @return	string	ハッシュバイナリ
	 */
	public static function HmacBinary ($data, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash_hmac($algo, $data, $key, true);
	}

	/**
	 * キーとファイルを元にハッシュ文字列を生成します。
	 *
	 * @param	string	$file_path	ファイルパス
	 * @param	string	$key		キー
	 * @param	string	$algo		ハッシュアルゴリズム
	 * @return	string	ハッシュ文字列
	 */
	public static function HmacStringFromFile ($file_path, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash_hmac_file($algo, $file_path, $key, false);
	}

	/**
	 * キーとファイルを元にハッシュバイナリを生成します。
	 *
	 * @param	string	$file_path	ファイルパス
	 * @param	string	$key		キー
	 * @param	string	$algo		ハッシュアルゴリズム
	 * @return	string	ハッシュバイナリ
	 */
	public static function HmacBinaryFromFile ($file_path, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		return hash_hmac_file($algo, $file_path, $key, true);
	}

	/**
	 * 指定したハッシュアルゴリズムが存在するか調べます。
	 *
	 * @param	string	$algorithm_name	ハッシュアルゴリズム名
	 * @return	bool	システム内にハッシュアルゴリズムがある場合は bool true、そうでない場合は false
	 */
	public static function AlgorithmExists ($algorithm_name) {
		return isset(array_flip(hash_algos())[$algorithm_name]);
	}

	/**
	 * ストレッチしたハッシュ文字列を生成します。
	 *
	 * @param	int		$count	ストレッチ回数
	 * @param	string	$data	文字列
	 * @param	string	$key	HMACキー
	 * @param	string	$algo	ハッシュアルゴリズム
	 */
	public static function HmacStringStretching ($count, $data, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		for ($i = 0;$i < $count;$i++) {
			$data = static::HmacString($data, $key, $algo);
		}
		return $data;
	}

	/**
	 * ストレッチしたハッシュバイナリを生成します。
	 *
	 * @param	int		$count	ストレッチ回数
	 * @param	string	$data	文字列
	 * @param	string	$key	HMACキー
	 * @param	string	$algo	ハッシュアルゴリズム
	 */
	public static function HmacBinaryStretching ($count, $data, $key, $algo = self::DEFAULT_HASH_ALGORITHM) {
		for ($i = 0;$i < $count;$i++) {
			$data = static::HmacBinary($data, $key, $algo);
		}
		return $data;
	}

	/**
	 * ランダムハッシュを構築します。
	 *
	 * @param	string	$string		キー
	 * @param	string	$salt		パスワード
	 * @param	string	$hmac_key	HMACキー
	 * @param	string	$secret_key	シークレットキーの長さ
	 * @param	string	$algo		ハッシュアルゴリズム
	 * @return	string	生成したランダムハッシュ
	 */
	public static function CreateRandomHash ($string, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		$secret_hmac_key = Hash::HmacStringStretching(rand(2, 5), microtime(true), mt_rand(), $algo);
		$secret_key = substr($secret_hmac_key, $secret_key_length, $secret_key_length);
		$secret_hash = Hash::HmacString("\\". $string .'_'. $salt .'/'.  $hmac_key, $secret_key, $algo);

		$secret_hash_length = strlen($secret_hash);

		$string_seed = hexdec(hash('crc32b', $string));
		$string_seed = str_pad($string_seed, $secret_key_length, $string_seed);

		$salt_seed = hexdec(hash('crc32b', $salt));
		$salt_seed = str_pad($salt_seed, $secret_key_length, $salt_seed);

		$index_list = [];
		for ($i = 0;$i < $secret_key_length;$i++) {
			$index = $string_seed[$i] * $salt_seed[$i] * $i + $string_seed[$i] + 1;
			$index = $index > $secret_hash_length  ? $secret_hash_length % $index : $index;
			if ($index < 1) {
				$index++;
			} else if ($index >= $secret_hash_length) {
				$index--;
			}
			$index_list[] = $index;
		}

		$random_hash = $secret_hash;
		for ($i = 0;$i < $secret_key_length;$i++) {
			$random_hash = substr($random_hash, 0, $index_list[$i]) . $secret_key[$i] . substr($random_hash, $index_list[$i]);
		}

		return $random_hash;
	}

	/**
	 * ランダムハッシュを検証します。
	 *
	 * @param	string	$random_hash	static::CreateRandomHashで構築されたランダムハッシュ
	 * @param	string	$string			キー
	 * @param	string	$salt			パスワード
	 * @param	string	$secret_key	シークレットキーの長さ
	 * @param	string	$algo		ハッシュアルゴリズム
	 * @return	bool	ランダムハッシュが正当なものならばtrue、そうでなければfalse
	 */
	public static function ValidRandomHash ($random_hash, $string, $salt, $hmac_key, $secret_key_length = self::SECRET_KEY_LENGTH, $algo = self::DEFAULT_HASH_ALGORITHM) {
		$secret_hash_length = strlen($random_hash) - $secret_key_length;

		$string_seed = hexdec(hash('crc32b', $string));
		$string_seed = str_pad($string_seed, $secret_key_length, $string_seed);

		$salt_seed = hexdec(hash('crc32b', $salt));
		$salt_seed = str_pad($salt_seed, $secret_key_length, $salt_seed);

		$index_list = [];
		for ($i = 0;$i < $secret_key_length;$i++) {
			$index = $string_seed[$i] * $salt_seed[$i] * $i + $string_seed[$i] + 1;
			$index = $index > $secret_hash_length  ? $secret_hash_length % $index : $index;
			if ($index < 1) {
				$index++;
			} else if ($index >= $secret_hash_length) {
				$index--;
			}
			$index_list[] = $index;
		}
		$kr_index_list = $index_list;

		krsort($kr_index_list);

		$secret_key = [];
		$secret_hash = $random_hash;
		foreach ($kr_index_list as $index) {
			$secret_key[] = substr($secret_hash, $index, 1);
			$secret_hash = substr($secret_hash, 0, $index) . substr($secret_hash, $index + 1);
		}
		krsort($secret_key);
		$secret_key = implode('', $secret_key);

		$secret_hash = Hash::HmacString("\\". $string .'_'. $salt .'/'. $hmac_key, $secret_key, $algo);
		for ($i = 0;$i < $secret_key_length;$i++) {
			$secret_hash = substr($secret_hash, 0, $index_list[$i]) . $secret_key[$i] . substr($secret_hash, $index_list[$i]);
		}

		return $random_hash === $secret_hash;
	}
}
