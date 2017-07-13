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
 * Flywheel2 ベーシック認証インターフェースです。
 *
 * @category	Flywheel2
 * @package		het
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IBasicAuth {
	/**
	 * @var	array	プロパティリスト
	 * @static
	 */
	public const PROPERTY_LIST	= [
		self::PROPERTY_ALGORITHM	=> self::PROPERTY_ALGORITHM,
		self::PROPERTY_REALM		=> self::PROPERTY_REALM,
		self::PROPERTY_USER_NAME	=> self::PROPERTY_USER_NAME,
		self::PROPERTY_PASSWORD		=> self::PROPERTY_PASSWORD,
	];

	/**
	 * @var	string	BASIC認証用AUTHENTICATEヘッダ
	 * @static
	 */
	public const HTTP_HEADER_AUTHENTICATE	= 'WWW-Authenticate: Basic %s';

	/**
	 * @var	string	プロパティ名：暗号方式
	 * @static
	 */
	public const PROPERTY_ALGORITHM			= 'algorithm';

	/**
	 * @var	string	プロパティ名：領域名
	 * @static
	 */
	public const PROPERTY_REALM				= 'realm';

	/**
	 * @var	string	プロパティ名：ユーザ名
	 * @static
	 */
	public const PROPERTY_USER_NAME			= 'userName';

	/**
	 * @var	string	プロパティ名：パスワード
	 * @static
	 */
	public const PROPERTY_PASSWORD			= 'password';

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
	 * @var	string	デフォルトハッシュアルゴリズム
	 * @static
	 */
	public const DEFAULT_HASH_ALGORITHM		= self::HASH_ALGORITHM_MD5;
}
