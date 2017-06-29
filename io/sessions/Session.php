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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\sessions;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\vartype\strings\Strings;

/**
 * Sessionクラスです。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Session {
	use traits\FilesSessionTrait;

	/**
	 * @var	string	キャッシュリミッタ：クライアント及びプロキシにキャッシュを許可
	 * @static
	 */
	const CACHE_LIMITER_PUBLIC				= 'public';

	/**
	 * @var	string	キャッシュリミッタ：クライアントのキャッシュを許可、プロキシのキャッシュは不可
	 * @static
	 */
	const CACHE_LIMITER_PRIVATE				= 'private';

	/**
	 * @var	string	キャッシュリミッタ：Expireヘッダを送らないprivateリミッタ
	 * @static
	 */
	const CACHE_LIMITER_PRIVATE_NO_EXPIRE	= 'private_no_expire';

	/**
	 * @var	string	キャッシュリミッタ：キャッシュを許可しない
	 * @static
	 */
	const CACHE_LIMITER_NOCACHE				= 'nocache';

	/**
	 * @var	string	キャッシュリミッタ：キャッシュリミッタを送信しない
	 * @static
	 */
	const CACHE_LIMITER_NOT_SEND_HEADER		= '';

	/**
	 * @var	string	セッションファイル名のプリフィックス
	 * @static
	 */
	const SESSION_FILE_NAME_PREFIX			= 'sess_';

	/**
	 * @var	string	セッションファイルハンドラ：ファイルベース
	 * @static
	 */
	const SESSION_SAVE_HANDLER_FILES		= 'files';

	/**
	 * @var	string	セッションファイルハンドラ：ユーザ定義
	 * @static
	 */
	const SESSION_SAVE_HANDLER_USER			= 'user';

	//==============================================
	//Core
	//==============================================
	/**
	 * セッション起動時の初期化処理を行います。
	 *
	 * Module名をアッパーキャメルケース化した文字列とInitを組み合わせたメソッドが実行されます。
	 * ex) "files"の場合
	 * FilesInitメソッドが実行される
	 */
	public static function Init () {
		return static::{Strings::ToUpperCamelCase(session_module_name()) . 'Init'}();
	}

	/**
	 * セッションを開始します。
	 *
	 * @return	mixed	セッションを開始した場合はbool true、既に開かれている場合はPHP_SESSION_ACTIVE、開始できなかった場合はPHP_SESSION_DISABLED
	 */
	public static function Start () {
		if (static::IsSessionNone()) {
			static::Init();
			if (session_start() !== false) {
				return true;
			}

			switch (session_module_name()) {
				case 'files':
					$save_dir= session_save_path();
					if (!file_exists($save_dir)) {
						throw CoreException::RaiseSystemError(sprintf('セッションセーブディレクトリがありません。dir path:%s', $save_dir));
					}

					var_dump(getmyuid(), getmygid(), fileowner($save_dir), get_current_user (),filegroup($save_dir), fileperms($save_dir));
					break;
			}

			return false;
		}
		return static::Status();
	}

	/**
	 * セッションデータを書き込んでセッションを終了します。
	 *
	 * 通常は使用しません。
	 */
	public static function Close () {
		return session_write_close();
	}

	/**
	 * セッションに書き込みます。
	 *
	 * @param	mixed	$name	名前
	 * @param	mixed	$value	値
	 */
	public static function Write ($name, $value) {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}

		if ($value instanceof \ArrayObject) {
			$value = $value->getArrayCopy();
		}

		if (is_array($name)) {
			$_SESSION = Arrays::SetLowest($_SESSION, $name, $value);
		} else {
			$_SESSION[$name] = $value;
		}
	}

	/**
	 * セッションから読み込みます。
	 *
	 * @param	mixed	$name	名前
	 * @return	mixed	値 セッションに値が存在しない場合はnull
	 */
	public static function Read ($name) {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}
		if (is_array($name)) {
			return Arrays::GetLowest($_SESSION, $name);
		} else {
			return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
		}
	}

	/**
	 * セッションから要素を削除します。
	 *
	 * @param	mixed	$name	削除する要素の名前
	 */
	public static function Delete ($name) {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}
		if (is_array($name)) {
			$_SESSION = Arrays::RemoveLowest($_SESSION, $name);
		} else {
			unset($_SESSION[$name]);
		}
	}

	/**
	 * セッションに値が存在するか調べます。
	 *
	 * @param	mixed	$name	検索する名前
	 * @return	bool	値が存在する場合はtrue 存在しない場合はfalse
	 */
	public static function Exists ($name) {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}
		if (is_array($name)) {
			return Arrays::ExistsLowest($_SESSION, $name);
		} else {
			return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
		}
	}

	/**
	 * セッションの値を全てクリアします。
	 */
	public static function Clear () {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}
		$_SESSION = [];
	}

	/**
	 * 現在のサーバ上のセッション情報を破棄します。
	 *
	 * @return	bool	成功した場合はtrue 失敗した場合はfalse
	 */
	public static function Destroy () {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを利用できません。');
		}
		return session_destroy();
	}

	/**
	 * 現在のセッションを完全に消滅させます。
	 *
	 * 同時に$_SESSION上のデータも消滅するため、このメソッドの実行後に値を利用したい場合は先に他の変数へ代入しておく必要があります。
	 *
	 * @return	bool	成功した場合はtrue 失敗した場合はfalse
	 */
	public static function Extinguish () {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションを消滅させられません。');
		}
		static::Clear();
		static::DeleteSessionCookie();
		return static::Destroy();
	}

	/**
	 * セッションクッキーを破棄します。
	 */
	public static function DeleteSessionCookie () {
		if (static::UseCookie()) {
			$cookie_params = static::CookieParams();
			setcookie(
				static::Name(),
				'',
				time() - 42000,
				$cookie_params['path'],
				$cookie_params['domain'],
				$cookie_params['secure'],
				$cookie_params['httponly']
			);
		}
	}

	/**
	 * セッションIDを付け替えます。
	 * 付け替え前のセッション情報は破棄されます。
	 *
	 * @return	int	付け替え後のセッションID
	 */
	public static function RegenerateId () {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションIDを付け替えられません。');
		}
		return session_regenerate_id(true);
	}

	/**
	 * セッションIDを付け替えます。
	 * 付け替え前のセッション情報は維持されます。
	 *
	 * @return	int	付け替え後のセッションID
	 */
	public static function RegenerateIdLeaveOldSession () {
		static::Start();
		if (!static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('セッションが開始されていないため、セッションIDを付け替えられません。');
		}
		return session_regenerate_id(false);
	}

	//==============================================
	//Accessor
	//==============================================
	//----------------------------------------------
	//Getter
	//----------------------------------------------
	/**
	 * セッションステータスを返します。
	 *
	 * @return	int		セッションステータス
	 */
	public static function Status () {
		return session_status();
	}

	/**
	 * 現在のセッションIDを返します。
	 *
	 * @return	string	現在のセッションID
	 */
	public static function Id () {
		return session_id();
	}

	/**
	 * 現在のセッション名を返します。
	 *
	 * @return	string	現在のセッション名
	 */
	public static function Name () {
		return session_name();
	}

	/**
	 * キャッシュ期間を返します。
	 *
	 * @return	int	キャッシュ期間
	 */
	public static function CacheExpire () {
		return session_cache_expire();
	}

	/**
	 * クッキーパラメータを全て取得します。
	 *
	 * @return	array	クッキーパラメータ
	 */
	public static function CookieParams () {
		return session_get_cookie_params();
	}

	/**
	 * 現在のセッションモジュール名を取得します。
	 *
	 * @return	string	現在のセッションモジュール名
	 */
	public static function Module () {
		return session_module_name();
	}

	//----------------------------------------------
	//Setter
	//----------------------------------------------
	/**
	 * セッションIDを変更し、別のセッションを利用できるようにします。
	 *
	 * @param	string	$session_id	別のセッションID
	 * @return	string	変更前のセッションID
	 */
	public static function SetId ($session_id) {
		if (static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('既にセッションが開始されているため、session_idを変更できません。session_id:%s', [$session_id]);
		}
		return session_id($session_id);
	}

	/**
	 * セッション名を変更します。
	 *
	 * セッション名とはクッキーやURLで使用されるセッションのパラメータ名です。
	 * 例）PHPSESSID
	 *
	 * @param	string	$session_name	別のセッション名
	 * @return	string	変更前のセッション名
	 */
	public static function SetName ($session_name) {
		if (static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('既にセッションが開始されているため、session_nameを変更できません。session_name:%s', [$session_name]);
		}
		return session_name($session_name);
	}

	/**
	 * キャッシュの有効期限を変更します。
	 *
	 * @param	string	$cache_expire	キャッシュの有効期限
	 * @return	int		変更前のキャッシュ有効期限 分単位
	 */
	public static function SetCacheExpire ($cache_expire) {
		if (static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('既にセッションが開始されているため、session_cache_expireを変更できません。session_cache_expire:%s', [$cache_expire]);
		}
		if (static::IsCacheLimiterNoCache()) {
			throw CoreException::RaiseSystemError('キャッシュリミッタがノーキャッシュとなっているため、session_cache_expireを変更できません。session_cache_expire:%s', [$cache_expire]);
		}
		return session_cache_expire($cache_expire);
	}

	/**
	 * キャッシュリミッタを変更します。
	 *
	 * @param	string	$cache_limiter	キャシュリミッタ 利用できる値は次の4種類
	 *     Session::CACHE_LIMITER_PUBLIC
	 *     Session::CACHE_LIMITER_PRIVATE_NO_EXPIRE
	 *     Session::CACHE_LIMITER_PRIVATE
	 *     Session::CACHE_LIMITER_NOCACHE
	 * @return	string	変更前のキャッシュリミッタ
	 * @see	http://www.php.net/manual/ja/function.session-cache-limiter.php
	 */
	public static function SetCacheLimiter ($cache_limiter) {
		if (!in_array($cache_limiter, static::GetCacheLimiterList(), true)) {
			throw CoreException::RaiseSystemError('存在しないlimiterを設定されました。session_cache_limiter:%s', [$cache_limiter]);
		}
		if (static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('既にセッションが開始されているため、session_cache_limiterを変更できません。session_cache_limiter:%s', [$cache_limiter]);
		}
		return session_cache_limiter($cache_limiter);
	}

	/**
	 * セッションモジュールを変更します。
	 *
	 * @param	$module_name	string	設定するセッションモジュール名
	 */
	public static function SetModule ($module_name) {
		return session_module_name($module_name);
	}

	//==============================================
	//Session Status
	//==============================================
	/**
	 * セッションが無効かどうか調べます。
	 *
	 * @return	bool	セッションが無効な場合はtrue, そうでない場合はfalse
	 */
	public static function IsSessionDisabled () {
		return session_status() === PHP_SESSION_DISABLED;
	}

	/**
	 * セッションが存在するかどうか調べます。
	 *
	 * @return	bool	セッションが存在しない場合はtrue, そうでない場合はfalse
	 */
	public static function IsSessionNone () {
		return session_status() === PHP_SESSION_NONE;
	}

	/**
	 * セッションが有効かどうか調べます。
	 *
	 * @return	bool	セッションが有効な場合はtrue, そうでない場合はfalse
	 */
	public static function IsSessionActive () {
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * セッションのキャッシュリミッタがno cacheになっているかを調べます。
	 *
	 * @return	bool	セッションのキャッシュリミッタがno cacheの場合はtrue, そうでない場合はfalse
	 */
	public static function IsCacheLimiterNoCache () {
		return ini_get('session.cache_limiter') === static::CACHE_LIMITER_PUBLIC;
	}

	/**
	 * セッションがクッキーを使う設定になっているかどうかを調べます。
	 *
	 * @return	セッションがクッキーを使う設定になっている場合はtrue, そうでない場合はfalse
	 */
	public static function UseCookie () {
		return (bool) ini_get("session.use_cookies");
	}

	//==============================================
	//Const list
	//==============================================
	/**
	 * 利用できるキャッシュリミッタのリストを返します。
	 *
	 * @return	array	利用できるキャッシュリミッタのリスト
	 */
	public static function GetCacheLimiterList () {
		return [
			static::CACHE_LIMITER_PUBLIC				=> static::CACHE_LIMITER_PUBLIC,
			static::CACHE_LIMITER_PRIVATE_NO_EXPIRE		=> static::CACHE_LIMITER_PRIVATE_NO_EXPIRE,
			static::CACHE_LIMITER_PRIVATE				=> static::CACHE_LIMITER_PRIVATE,
			static::CACHE_LIMITER_NOCACHE				=> static::CACHE_LIMITER_NOCACHE,
			static::CACHE_LIMITER_NOT_SEND_HEADER		=> static::CACHE_LIMITER_NOT_SEND_HEADER,
		];
	}
}
