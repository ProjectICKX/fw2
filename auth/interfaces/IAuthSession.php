<?php
/**  ______ _                 _               _ ___
 *  |  ____| |               | |             | |__ \
 *  | |__  | |_   ___      __| |__   ___  ___| |  ) |
 *  |  __| | | | | \ \ /\ / /| '_ \ / _ \/ _ \ | / /
 *  | |    | | |_| |\ V  V / | | | |  __/  __/ |/ /_
 *  |_|    |_|\__; | \_/\_/  |_| |_|\___|\___|_|____|
 *             __/ |
 *            |___/
 *
 * Flywheel2: the inertia php framework
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\interfaces;

/**
 * Flywheel2 認証・認可セッションインターフェースです。
 *
 * @category	Flywheel2
 * @package		het
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IAuthSession {
	/**
	 * @var	array	プロパティリスト
	 * @static
	 */
	public const PROPERTY_LIST	= [
		//----------------------------------------------
		// 認証セッション暗号化設定
		//----------------------------------------------
		self::PROPERTY_CLIENT_KEY				=> self::PROPERTY_CLIENT_KEY,
		self::PROPERTY_CLIENT_SALT				=> self::PROPERTY_CLIENT_SALT,
		self::PROPERTY_SERVER_KEY				=> self::PROPERTY_SERVER_KEY,
		self::PROPERTY_SERVER_SALT				=> self::PROPERTY_SERVER_SALT,

		self::PROPERTY_STRETCHER				=> self::PROPERTY_STRETCHER,
		self::PROPERTY_SEPARATOR_LENGTH			=> self::PROPERTY_SEPARATOR_LENGTH,
		self::PROPERTY_VALID_ALTER_LV			=> self::PROPERTY_VALID_ALTER_LV,

		//----------------------------------------------
		// 認証セッション設定
		//----------------------------------------------
		self::PROPERTY_COOKIE_NAME_PREFIX		=> self::PROPERTY_COOKIE_NAME_PREFIX,
		self::PROPERTY_SESSION_EXPIRE			=> self::PROPERTY_SESSION_EXPIRE,
		self::PROPERTY_COOKIE_LIFETIME			=> self::PROPERTY_COOKIE_LIFETIME,
		self::PROPERTY_COOKIE_PATH				=> self::PROPERTY_COOKIE_PATH,
		self::PROPERTY_COOKIE_DOMAIN			=> self::PROPERTY_COOKIE_DOMAIN,
		self::PROPERTY_COOKIE_SECURE			=> self::PROPERTY_COOKIE_SECURE,

		self::PROPERTY_SAVE_HANDLER				=> self::PROPERTY_SAVE_HANDLER,
		self::PROPERTY_SAVE_PATH				=> self::PROPERTY_SAVE_PATH,

		//----------------------------------------------
		// ガベージコレクタ発動条件
		//----------------------------------------------
		self::PROPERTY_PROBABILITY				=> self::PROPERTY_PROBABILITY,
		self::PROPERTY_DIVISOR					=> self::PROPERTY_DIVISOR,

		//----------------------------------------------
		// 認証前仮セッション暗号化設定
		//----------------------------------------------
		self::PROPERTY_TMP_CLIENT_KEY			=> self::PROPERTY_TMP_CLIENT_KEY,
		self::PROPERTY_TMP_CLIENT_SALT			=> self::PROPERTY_TMP_CLIENT_SALT,
		self::PROPERTY_TMP_SERVER_KEY			=> self::PROPERTY_TMP_SERVER_KEY,
		self::PROPERTY_TMP_SERVER_SALT			=> self::PROPERTY_TMP_SERVER_SALT,

		self::PROPERTY_TMP_STRETCHER			=> self::PROPERTY_TMP_STRETCHER,
		self::PROPERTY_TMP_SEPARATOR_LENGTH		=> self::PROPERTY_TMP_SEPARATOR_LENGTH,
		self::PROPERTY_TMP_VALID_ALTER_LV		=> self::PROPERTY_TMP_VALID_ALTER_LV,

		//----------------------------------------------
		// 認証前仮セッション設定
		//----------------------------------------------
		self::PROPERTY_TMP_COOKIE_NAME_PREFIX	=> self::PROPERTY_TMP_COOKIE_NAME_PREFIX,
		self::PROPERTY_TMP_SESSION_EXPIRE		=> self::PROPERTY_TMP_SESSION_EXPIRE,
		self::PROPERTY_TMP_COOKIE_LIFETIME		=> self::PROPERTY_TMP_COOKIE_LIFETIME,
		self::PROPERTY_TMP_COOKIE_PATH			=> self::PROPERTY_TMP_COOKIE_PATH,

		self::PROPERTY_TMP_SAVE_HANDLER			=> self::PROPERTY_TMP_SAVE_HANDLER,
		self::PROPERTY_TMP_SAVE_PATH			=> self::PROPERTY_TMP_SAVE_PATH,

		//----------------------------------------------
		// セッションセーブハンドラ
		//----------------------------------------------
		self::PROPERTY_SESSION_SAVE_HANDLER		=> self::PROPERTY_SESSION_SAVE_HANDLER,
	];

	//----------------------------------------------
	// 認証セッション暗号化設定
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：認証セッション：クライアントキー
	 * @static
	 */
	public const PROPERTY_CLIENT_KEY				= 'clientKey';

	/**
	 * @var	string	プロパティ名：認証セッション：クライアントソルト
	 * @static
	 */
	public const PROPERTY_CLIENT_SALT				= 'clientSalt';

	/**
	 * @var	string	プロパティ名：認証セッション：サーバキー
	 * @static
	 */
	public const PROPERTY_SERVER_KEY				= 'serverKey';

	/**
	 * @var	string	プロパティ名：認証セッション：サーバソルト
	 * @static
	 */
	public const PROPERTY_SERVER_SALT				= 'serverSalt';

	/**
	 * @var	string	プロパティ名：認証セッション：ストレッチャー
	 * @static
	 */
	public const PROPERTY_STRETCHER					= 'stretcher';

	/**
	 * @var	string	プロパティ名：認証セッション：ランダムセパレータ長
	 * @static
	 */
	public const PROPERTY_SEPARATOR_LENGTH			= 'separatorLength';

	/**
	 * @var	string	プロパティ名：認証セッション：検証レベル
	 * @static
	 */
	public const PROPERTY_VALID_ALTER_LV			= 'validAlterLv';

	//----------------------------------------------
	// 認証セッション設定
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：認証セッション：クッキー名プリフィックス
	 * @static
	 */
	public const PROPERTY_COOKIE_NAME_PREFIX		= 'cookieNamePrefix';

	/**
	 * @var	string	プロパティ名：認証セッション：セッション有効期限
	 * @static
	 */
	public const PROPERTY_SESSION_EXPIRE			= 'sessionExpire';

	/**
	 * @var	string	プロパティ名：認証セッション：クッキーライフタイム
	 * @static
	 */
	public const PROPERTY_COOKIE_LIFETIME			= 'cookieLifetime';

	/**
	 * @var	string	プロパティ名：認証セッション：クッキー有効パス
	 * @static
	 */
	public const PROPERTY_COOKIE_PATH				= 'cookiePath';

	/**
	 * @var	string	プロパティ名：認証セッション：クッキー有効ドメイン
	 * @static
	 */
	public const PROPERTY_COOKIE_DOMAIN				= 'cookieDomain';

	/**
	 * @var	string	プロパティ名：認証セッション：クッキーセキュア属性
	 * @static
	 */
	public const PROPERTY_COOKIE_SECURE				= 'cookieSecure';

	/**
	 * @var	string	プロパティ名：認証セッション：セーブハンドラ
	 * @static
	 */
	public const PROPERTY_SAVE_HANDLER				= 'saveHandler';

	/**
	 * @var	string	プロパティ名：認証セッション：セーブパス
	 * @static
	 */
	public const PROPERTY_SAVE_PATH					= 'savePath';

	//----------------------------------------------
	// ガベージコレクタ発動条件
	// probability / divisor の確率で実施
	//----------------------------------------------
	public const PROPERTY_PROBABILITY				= 'probability';
	public const PROPERTY_DIVISOR					= 'divisor';

	//----------------------------------------------
	// 認証前セッション暗号化設定
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：認証前セッション：クライアントキー
	 * @static
	 */
	public const PROPERTY_TMP_CLIENT_KEY			= 'tmpClientKey';

	/**
	 * @var	string	プロパティ名：認証前セッション：クライアントソルト
	 * @static
	 */
	public const PROPERTY_TMP_CLIENT_SALT			= 'tmpClientSalt';

	/**
	 * @var	string	プロパティ名：認証前セッション：サーバキー
	 * @static
	 */
	public const PROPERTY_TMP_SERVER_KEY			= 'tmpServerKey';

	/**
	 * @var	string	プロパティ名：認証前セッション：サーバソルト
	 * @static
	 */
	public const PROPERTY_TMP_SERVER_SALT			= 'tmpServerSalt';

	/**
	 * @var	string	プロパティ名：認証前セッション：ストレッチャー
	 * @static
	 */
	public const PROPERTY_TMP_STRETCHER				= 'tmpStretcher';

	/**
	 * @var	string	プロパティ名：認証前セッション：ランダムセパレータ長
	 * @static
	 */
	public const PROPERTY_TMP_SEPARATOR_LENGTH		= 'tmpSeparatorLength';

	/**
	 * @var	string	プロパティ名：認証前セッション：認証検証レベル
	 * @static
	 */
	public const PROPERTY_TMP_VALID_ALTER_LV		= 'tmpValidAlterLv';

	//----------------------------------------------
	// 認証前セッション設定
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：認証前セッション：クッキー名プリフィックス
	 * @static
	 */
	public const PROPERTY_TMP_COOKIE_NAME_PREFIX	= 'tmpCookieNamePrefix';

	/**
	 * @var	string	プロパティ名：認証前セッション：セッション有効期限
	 * @static
	 */
	public const PROPERTY_TMP_SESSION_EXPIRE		= 'tmpSessionExpire';

	/**
	 * @var	string	プロパティ名：認証前セッション：クッキーライフタイム
	 * @static
	 */
	public const PROPERTY_TMP_COOKIE_LIFETIME		= 'tmpCookieLifetime';

	/**
	 * @var	string	プロパティ名：認証前セッション：クッキー有効パス
	 * @static
	 */
	public const PROPERTY_TMP_COOKIE_PATH			= 'tmpCookiePath';

	/**
	 * @var	string	プロパティ名：認証前セッション：セーブハンドラ
	 * @static
	 */
	public const PROPERTY_TMP_SAVE_HANDLER			= 'tmpSaveHandler';

	/**
	 * @var	string	プロパティ名：認証前セッション：セーブパス
	 * @static
	 */
	public const PROPERTY_TMP_SAVE_PATH				= 'tmpSavePath';

	//----------------------------------------------
	// セッションセーブハンドラ
	//----------------------------------------------
	/**
	 * @var	string	プロパティ名：セッションセーブハンドラ
	 * @static
	 */
	public const PROPERTY_SESSION_SAVE_HANDLER		= 'sessionSaveHandler';

	//----------------------------------------------
	// デフォルト値
	//----------------------------------------------
	/**
	 * @var	string	セーブハンドラ：ファイルベース
	 * @static
	 */
	public const SAVE_HANDLER_TYPE_FILES			= 'files';

	/**
	 * @var	string	セーブハンドラ：Memcached
	 * @static
	 */
	public const SAVE_HANDLER_TYPE_MEMCACHED		= 'memcached';

	/**
	 * @var	string	認証検証レベル：厳密（ブラウザバックも許さないレベル）
	 * @static
	 */
	public const VALID_ALTER_LV_STRICT				= 'strict';

	/**
	 * @var	string	認証検証レベル：簡易
	 * @static
	 */
	public const VALID_ALTER_LV_LAZY				= 'lazy';

	//----------------------------------------------
	// デフォルト値
	//----------------------------------------------
	/**
	 * @var	string	デフォルト認証名
	 * @static
	 */
	public const DEFAULT_NAME						= ':default:';

	/**
	 * @var	bool	デフォルトクッキーセキュア属性：false
	 * @static
	 */
	public const COOKIE_SECURE_IN_SECURE			= false;

	/**
	 * @var	string	デフォルトセーブハンドラー
	 * @static
	 */
	public const DEFAULT_SAVE_HANDLER_TYPE			= self::SAVE_HANDLER_TYPE_FILES;

	/**
	 * @var	string	デフォルト認証検証レベル
	 * @static
	 */
	public const DEFAULT_VALID_ALTER_LV				= self::VALID_ALTER_LV_LAZY;

	/**
	 * @var	string	認証セッション：デフォルトクッキー名プリフィックス：Auth Session
	 * @static
	 */
	public const DEFAULT_COOKIE_NAME_PREFIX			= 'as_';

	/**
	 * @var	int		認証セッション：デフォルトサーバーデータ有効期限：8時間
	 * @static
	 */
	public const DEFAULT_SESSION_EXPIRE				= 28800;

	/**
	 * @var	int		認証セッション：デフォルトクッキーライフタイム
	 * @static
	 */
	public const DEFAULT_COOKIE_LIFETIME			= 0;

	/**
	 * @var	string	認証セッション：デフォルトクッキーパス
	 * @static
	 */
	public const DEFAULT_COOKIE_PATH				= '/';

	/**
	 * @var	bool	デフォルトクッキーセキュア属性
	 * @static
	 */
	public const DEFAULT_COOKIE_DOMAIN				= null;

	/**
	 * @var	bool	デフォルトクッキーセキュア属性
	 * @static
	 */
	public const DEFAULT_COOKIE_SECURE				= true;

	/**
	 * @var	string	デフォルトセーブパス
	 * @static
	 */
	public const DEFAULT_SAVE_PATH					= null;

	/**
	 * @var	int		デフォルトガベージコレクタ発動確率
	 * @static
	 */
	public const DEFAULT_PROBABILITY				= 2;

	/**
	 * @var	int		デフォルトガベージコレクタ発動確率：除数部
	 * @static
	 */
	public const DEFAULT_DIVISOR					= 100;

	/**
	 * @var	bool	クッキーに対するHTTP Only制約：常にTRUEであること
	 * @static
	 */
	public const COOKIE_HTTP_ONLY					= true;	// DO NOT CHANGE. EVERYTIME true.

	/**
	 * @var	string	認証前セッション：デフォルトクッキー名プリフィックス：Auth Session Tmp
	 * @static
	 */
	public const DEFAULT_TMP_COOKIE_NAME_PREFIX		= 'ast_';

	/**
	 * @var	int		認証前セッション：デフォルトサーバーデータ有効期限：10分
	 * @static
	 */
	public const DEFAULT_TMP_SESSION_EXPIRE			= 600;

	/**
	 * @var	int		認証前セッション：デフォルトクッキーライフタイム：0（ブラウザを閉じるまで）
	 * @static
	 */
	public const DEFAULT_TMP_COOKIE_LIFETIME		= 0;
}
