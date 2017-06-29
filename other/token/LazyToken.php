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

namespace ickx\fw2\other\token;

use ickx\fw2\crypt\Hash;

/**
 * 簡易的なトークンを提供するクラスです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class LazyToken {
	use	\ickx\fw2\traits\magic\Accessor;

	/** @var	string	デフォルトとして使うハッシュアルゴリズム */
	const DEFAULT_HASH_ALGORITHM = 'crc32b';

	/**
	 * @var	int	デフォルトのシークレットキーの長さ
	 * @static
	 */
	const SECRET_KEY_LENGTH	= 4;

	/**
	 * @var	string	トークンの元になる文字列
	 */
	protected $seed			= null;

	/**
	 * @var	string	ソルト
	 */
	protected $salt			= null;

	/**
	 * @var	string	hmac key
	 */
	protected $hmacKey		= null;

	/**
	 * @var	string	シークレットキーの長さ
	 */
	protected $secretKeyLength	= self::SECRET_KEY_LENGTH;

	/**
	 * @var	string	hash化に使用するアルゴリズム
	 */
	protected $algo			= self::DEFAULT_HASH_ALGORITHM;

	/**
	 * @var	int		ストレッチコスト
	 */
	protected $cost			= self::SECRET_KEY_LENGTH;

	/**
	 * @var	callable	シード受け取り時のフィルタ
	 */
	protected $seedFilter	= null;

	/**
	 * @var	callable	トークン発行後の処理
	 */
	protected $postProcess	= null;

	/**
	 * 簡易的な一時トークンを発行します。
	 *
	 * @param	string	$seed	元になる文字列
	 * @return	string	トークン
	 */
	public function issue ($seed = null) {
		$token = Hash::CreateRandomHash(is_callable($this->seedFilter) ? $this->seedFilter()($seed ?? $this->seed) : $seed ?? $this->seed, $this->salt, $this->hmacKey, $this->secretKeyLength, $this->algo);
		if (is_callable($this->postProcess)) {
			$this->postProcess()($token);
		}
		return $token;
	}

	/**
	 * 簡易的な一時トークンを検証します。
	 *
	 * @param	string	$token	トークン
	 * @param	string	$seed	元になる文字列
	 * @return	bool	トークンが正当な場合はtrue、不正な場合はfalse
	 */
	public function verify ($token, $seed = null) {
		return Hash::ValidRandomHash($token, is_callable($this->seedFilter) ? $this->seedFilter()($seed ?? $this->seed) : $seed ?? $this->seed, $this->salt, $this->hmacKey, $this->secretKeyLength, $this->algo);
	}
}
