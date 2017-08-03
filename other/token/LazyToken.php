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
	 * @var	string		トークン名
	 */
	protected $tokenName	= 'token';

	/**
	 * @var	string		シード値のプリフィックス
	 */
	protected $seedPrefix	= null;

	/**
	 * @var	callable	トークンの永続化処理
	 */
	protected $saveToken	= null;

	/**
	 * @var	callable	トークン除去処理
	 */
	protected $destroyToken	= null;

	/**
	 * @var	callable	トークンの存在確認処理
	 */
	protected $existsToken	= null;

	/**
	 * 簡易的な一時トークンを発行します。
	 *
	 * @param	string	$seed	元になる文字列
	 * @return	string	トークン
	 */
	public function issue ($seed = null) {
		$seed	= array_merge((array) $this->seedPrefix, (array) ($seed ?? $this->seed));
		$token_code = Hash::CreateRandomHash(is_callable($this->seedFilter) ? $this->seedFilter()($seed) : $seed, $this->salt, $this->hmacKey, $this->secretKeyLength, $this->algo);
		if (is_callable($this->saveToken)) {
			$this->saveToken()($this->tokenName, $token_code);
		}
		return $token_code;
	}

	/**
	 * 簡易的な一時トークンを検証します。
	 *
	 * @param	string	$token_code	トークン
	 * @param	string	$seed		元になる文字列
	 * @return	bool	トークンが正当な場合はtrue、不正な場合はfalse
	 */
	public function verify ($token_code, $seed = null) {
		$seed	= array_merge((array) $this->seedPrefix, (array) ($seed ?? $this->seed));
		return Hash::ValidRandomHash($token_code, is_callable($this->seedFilter) ? $this->seedFilter()($seed) : $seed, $this->salt, $this->hmacKey, $this->secretKeyLength, $this->algo);
	}

	/**
	 * 簡易的な一時トークンがあるかどうか調べます。
	 *
	 * @param	string	$token_code	トークン
	 * @return	bool	トークンがある場合はtrue, そうでない場合はfalse
	 */
	public function exists ($token_code) {
		return $this->existsToken()($this->tokenName, $token_code);
	}

	/**
	 * トークン名に紐づく簡易的な一時トークンを破棄します。
	 */
	public function destroy () {
		$this->destroyToken()($this->tokenName);
	}
}
