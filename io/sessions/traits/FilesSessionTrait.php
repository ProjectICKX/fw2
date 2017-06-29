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

namespace ickx\fw2\io\sessions\traits;

use ickx\fw2\core\exception\CoreException;

/**
 * FilesSessionTrait
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait FilesSessionTrait {
	/**
	 * セッションセーブパスを取得します。
	 *
	 * @return	string	セッションセーブパス
	 */
	public static function SavePath () {
		return session_save_path();
	}

	/**
	 * セッションセーブパスを変更します。
	 *
	 * @param	string	$save_path	セッションセーブパス
	 * @return	string	変更前のセッションセーブパス
	 */
	public static function SetSavePath ($save_path) {
		if (static::IsSessionActive()) {
			throw CoreException::RaiseSystemError('既にセッションが開始されているため、session_save_pathを変更できません。session_save_path:%s', [$save_path]);
		}
		return session_save_path($save_path);
	}

	/**
	 * セッションセーブハンドラがfilesの場合の初期処理を行います。
	 */
	public static function FilesInit () {
		return static::FilesDetectiveSessionFixation();
	}

	/**
	 * セッションセーブハンドラがfilesの場合のセッションフィクセーション検知を行います。
	 *
	 * @return	bool	セッションフィクセーションを検知した場合はtrue そうでない場合はfalse
	 */
	public static function FilesDetectiveSessionFixation () {
		//==============================================
		//変数初期化
		//==============================================
		$save_path = static::SavePath();

		//==============================================
		//ディレクトリ
		//==============================================
		//ディレクトリ存在確認
		if (!file_exists($save_path)) {
			if (!mkdir($save_path, 0700, true)) {
				throw CoreException::RaiseSystemError('セッションセーブディレクトリがありません。dir:%s', [$save_path]);
			}
		}

		//対象確認
		if (!is_dir($save_path)) {
			throw CoreException::RaiseSystemError('セッションセーブディレクトリがファイルです。dir:%s', [$save_path]);
		}

		//書き込み権確認
		if (!is_writable($save_path)) {
			throw CoreException::RaiseSystemError('セッションセーブディレクトリに書き込めません。dir:%s', [$save_path]);
		}

		//読み込み権確認
		if (!is_readable($save_path)) {
			throw CoreException::RaiseSystemError('セッションセーブディレクトリから読み込みできません。dir:%s', [$save_path]);
		}

		//==============================================
		//ファイル：セッションフィクセーション対策
		//==============================================
		//セッション取得元検証
		$session_name = session_name();
		if (isset($_POST[$session_name]) || isset($_GET[$session_name])) {
			throw CoreException::RaiseSystemError('クッキー以外の経路でセッションを指定されました。session_id:%s', [$session_id]);
		}

		//セッションIDがあった場合の検証
		$session_id = (isset($_COOKIE[$session_name])) ? $_COOKIE[$session_name] : session_id();
		if ($session_id !== '') {
			//不正な文字列がないか検証
			if (strpos($session_id, '.') !== false || strpos($session_id, '/') !== false || strpos($session_id, "\\") !== false) {
				throw CoreException::RaiseSystemError('セッションIDに不正な文字列が存在します。session_id:%s', [$session_id]);
			}

			//セッションファイルパスの作成
			$session_file_path = sprintf('%s/%s%s', str_replace("\\", '/', $save_path), static::SESSION_FILE_NAME_PREFIX, $session_id);

			//ファイル存在確認
			if (!file_exists($session_file_path)) {
				static::DeleteSessionCookie();
				throw CoreException::RaiseSystemError('セッションファイルがありません。セッションフィクセーションの疑いがあります。file:%s', [$session_file_path]);
			}

			//対象確認
			if (!is_file($session_file_path)) {
				throw CoreException::RaiseSystemError('セッションファイルがファイルではありません。file:%s', [$session_file_path]);
			}

			//書き込み権確認
			if (!is_writable($session_file_path)) {
				throw CoreException::RaiseSystemError('セッションファイルに書き込めません。file:%s', [$session_file_path]);
			}

			//読み込み権確認
			if (!is_readable($session_file_path)) {
				throw CoreException::RaiseSystemError('セッションファイルから読み込みできません。file:%s', [$session_file_path]);
			}
		}

		//==============================================
		//処理の終了
		//==============================================
		return true;
	}

	/**
	 * 現在のセッションファイルパスを取得します。
	 *
	 * @return	string	現在のセッションファイルパス
	 */
	public static function GetSessionFilePath () {
		return sprintf('%s/%s%s', session_save_path(), static::SESSION_FILE_NAME_PREFIX, session_id());
	}
}
