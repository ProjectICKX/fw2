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
 * @package		features
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\features\file_upload;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\FileSystem;
use ickx\fw2\io\php_ini\PhpIni;

/**
 * ファイルアップロードを扱います。
 *
 * @category	Flywheel2
 * @package		features
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class FileUpload {
	/**
	 * サーバ側がファイルアップロード可能な状態かどうか検証します。
	 */
	public static function PreCheck () {
		//==============================================
		//検証開始
		//==============================================
		//そもそもuploade機能が有効か検証
		if (!PhpIni::Get(PhpIni::FILE_UPLOADS)) {
			throw CoreException::RaiseSystemError('file_uploadsが設定されていません。');
		}

		//Upload max sizeがPost max sizeを超えていないかどうか
		$upload_max_filesize = PhpIni::ToByte(PhpIni::Get(PhpIni::UPLOAD_MAX_FILESIZE));
		$post_max_ise = PhpIni::ToByte(PhpIni::Get(PhpIni::POST_MAX_SIZE));
		if ($upload_max_filesize > $post_max_ise) {
			throw CoreException::RaiseSystemError('upload_max_filesize(%d)がpost_max_size(%d)を超えています。', [$upload_max_filesize, $post_max_ise]);
		}

		//アップロード先一時ディレクトリが利用できるかどうか
		$upload_tmp_dir = PhpIni::Get(PhpIni::UPLOAD_TMP_DIR);
		if (!$upload_tmp_dir) {
			throw CoreException::RaiseSystemError('アップロード先一時ディレクトリが設定されていません。ini_name:%s', [PhpIni::UPLOAD_TMP_DIR]);
		}
		if (FileSystem::IsEnableDirectory($upload_tmp_dir, ['name' => 'アップロード先一時', 'p' => true, 'raise_exception' => false]) !== true) {
			FileSystem::CreateDirectory($upload_tmp_dir, ['name' => 'アップロード先一時']);
		}

		//$ini_list, 'file_upload'

		return [
			'max_file_size'	=> $upload_max_filesize,
		];
	}

	/**
	 * 現在のファイルアップロード関連のphp.ini設定を取得します。
	 *
	 * @return	array	現在のファイルアップロード関連のphp.ini設定
	 */
	public static function GetCurrentIni () {
		return PhpIni::GetFileUploadIniAll(false);
	}

	/**
	 * 指定されたアップロード一時ファイルをエンコーディング変換してからプロセスポインタとして返します。
	 *
	 * ！！注意！！
	 * 開いたプロセスポインタは必ずstatic::TmpFileDirtyPCloseで閉じる必要があります。
	 *
	 * @param	string		$tmp_path		アップロード一時ファイルパス
	 * @param	string		$to_encoding	コンバート後の文字エンコーディング
	 * @param	string		$form_encoding	コンバート前の文字エンコーディング
	 * @param	string		$php_path		PHPコマンドのパス
	 * @return	resource	プロセスポインタ
	 */
	public static function TmpFileDirtyPOpenConvertEncoding ($tmp_path, $to_encoding, $form_encoding, $php_path = null) {
		//バックエンドでコマンドを叩くというかなり危険な実装のため、検証
		if (PhpIni::Get(PhpIni::UPLOAD_TMP_DIR) !== dirname(realpath($tmp_path)) . '/') {
			throw CoreException::RaiseSystemError('指定外領域を狙ったアクセスが行われました！path:%s', [$tmp_path]);
		}

		if (!is_uploaded_file($tmp_path)) {
			throw CoreException::RaiseSystemError('アップロードされたファイル以外へのアクセスが行われました！path:%s', [$tmp_path]);
		}

		return FileSystem::DirtyFopenConvertEncoding($tmp_path, $to_encoding, $form_encoding, $php_path);
	}

	/**
	 * static::TmpFileDirtyPOpenConvertEncodingで開いたプロセスポインタを閉じます。
	 *
	 * @param	resource	$pp	プロセスポインタ
	 */
	public static function TmpFileDirtyPClose ($pp) {
		pclose($pp);
	}

	/**
	 * アップロード一時ファイルを保存します。
	 *
	 * @param	string	$tmp_paht	アップロード一時ファイル
	 * @param	string	$file_path	保存際ファイルパス
	 * @return	boolean	保存に成功した場合はbool true、そうでない場合はfalse
	 */
	public static function MoveUploadedFile ($tmp_paht, $file_path) {
		return move_uploaded_file($tmp_paht, $file_path);
	}
}
