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

namespace ickx\fw2\io\sessions\handlers;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\sessions\Session;

/**
 * FileSessionHandler
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
//class FileSessionHandler implements \SessionHandlerInterface {
class FileSessionHandler extends SessionHandler {
	/**
	 * セッションファイル名のプリフィックス
	 *
	 * @static
	 * @var		string
	 */
	const FILE_NAME_PREFIX	= 'sess_';

	/**
	 * セッション開始時に保存先の検証を行います。
	 *
	 * @param	string	$save_path		セッションファイル保存先ディレクトリパス
	 * @param	string	$session_name	セッション名 session_name() と同じ
	 * @return	bool	SessionHandler::open()の返り値 セッションを開けた場合はtrue そうでない場合はfalse
	 */
	public function open ($save_path, $session_name) {
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
		if (($session_id = session_id()) !== '') {
			//不正な文字列がないか検証
			if (strpos($session_id, '.') !== false || strpos($session_id, '/') !== false || strpos($session_id, "\\") !== false) {
				throw CoreException::RaiseSystemError('セッションIDに不正な文字列が存在します。session_id:%s', [$session_id]);
			}

			//セッションファイルパスの作成
			$session_file_path = sprintf('%s/%s%s', str_replace("\\", '/', $save_path), static::FILE_NAME_PREFIX, $session_id);

			//ファイル存在確認
			if (!file_exists($session_file_path)) {
				Session::DeleteSessionCookie();
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
		//セッション開始
		//==============================================
		return parent::open($save_path, $session_name);
	}
}
