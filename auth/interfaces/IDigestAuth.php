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
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\interfaces;

/**
 * Flywheel2 ダイジェスト認証インターフェースです。
 *
 * @category	Flywheel2
 * @package		het
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IDigestAuth {
	/**
	 * @var	array	プロパティリスト
	 * @static
	 */
	public const PROPERTY_LIST	= [
		self::PROPERTY_REALM		=> self::PROPERTY_REALM,
		self::PROPERTY_USER_NAME	=> self::PROPERTY_USER_NAME,
		self::PROPERTY_METHOD		=> self::PROPERTY_METHOD,
		self::PROPERTY_URI			=> self::PROPERTY_URI,
		self::PROPERTY_NONCE		=> self::PROPERTY_NONCE,
		self::PROPERTY_CNONCE		=> self::PROPERTY_CNONCE,
		self::PROPERTY_QOP			=> self::PROPERTY_QOP,
		self::PROPERTY_ALGORITHM	=> self::PROPERTY_ALGORITHM,
		self::PROPERTY_NC			=> self::PROPERTY_NC,
		self::PROPERTY_RESPONSE		=> self::PROPERTY_RESPONSE,
	];

	/**
	 * @var	string	DIGEST認証用AUTHENTICATEヘッダ
	 * @static
	 */
	public const HTTP_HEADER_AUTHENTICATE		= 'WWW-Authenticate: Digest %s';

	/**
	 * @var	string	ダイジェストヘッダ解析用パターン
	 * @static
	 */
	public const DIGEST_HEADER_PURSE_PATTERN	= '@(%s)=(?:([\'"])([^\2]+?)\2|([^\s,]+))@';

	/**
	 * @var	string	プロパティ名：領域名
	 * @static
	 */
	public const PROPERTY_REALM		= 'realm';

	/**
	 * @var	string	プロパティ名：ユーザ名
	 * @static
	 */
	public const PROPERTY_USER_NAME	= 'username';

	/**
	 * @var	string	プロパティ名：メソッド
	 * @static
	 */
	public const PROPERTY_METHOD		= 'method';

	/**
	 * @var	string	プロパティ名：現在表示しているページのURL
	　* @static
	　*/
	public const PROPERTY_URI			= 'uri';

	/**
	 * @var	string	プロパティ名：サーバ側で生成した乱数
	 * @static
	 */
	public const PROPERTY_NONCE		= 'nonce';

	/**
	 * @var	string	プロパティ名：クライアント側で生成した乱数
	 * @static
	 */
	public const PROPERTY_CNONCE		= 'cnonce';

	/**
	 * @var	string	プロパティ名：保護レベル
	 * @static
	 */
	public const PROPERTY_QOP			= 'qop';

	/**
	 * @var	string	プロパティ名：暗号方式
	 * @static
	 */
	public const PROPERTY_ALGORITHM	= 'algorithm';

	/**
	 * @var	string	プロパティ名：クライアントからのリクエスト回数(16進数)
	 * @static
	 */
	public const PROPERTY_NC			= 'nc';

	/**
	 * @var	string	プロパティ名：ブラウザ側で生成したハッシュ値
	 * @static
	 */
	public const PROPERTY_RESPONSE		= 'response';

	/**
	 * @var	string	ハッシュアルゴリズム：md5 (デフォルト) 2017年7月現在でブラウザがサポートしているものはmd5のみ
	 * @static
	 */
	public const HASH_ALGORITHM_MD5			= 'md5';

	/**
	 * @var	string	ハッシュアルゴリズム：md5-sess
	 * @static
	 */
	public const HASH_ALGORITHM_MD5_SESS	= 'md5-sess';

	/**
	 * @var	string	ハッシュアルゴリズム：sha256
	 * @static
	 */
	public const HASH_ALGORITHM_SHA_256		= 'sha256';

	/**
	 * @var	string	QOP：auth
	 * @static
	 */
	public const QOP_AUTH		= 'auth';

	/**
	 * @var	string	QOP：auth-int
	 * @static
	 */
	public const QOP_AUTH_INT	= 'auth-int';

	/**
	 * @var	string	QOP：auth,auth-int
	 * @static
	 */
	public const QOP_AUTH_BOTH	= 'auth,auth-int';

	/**
	 * @var	string	デフォルトハッシュアルゴリズム
	 * @static
	 */
	public const DEFAULT_HASH_ALGORITHM	= self::HASH_ALGORITHM_MD5;

	/**
	 * @var	string	デフォルトQOP
	 * @static
	 */
	public const DEFAULT_QOP	= self::QOP_AUTH;

	/**
	 * @var	string	レスポンスセパレータ
	 * @static
	 */
	public const RESPONSE_SEPARATOR	= ':';
}
