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

namespace ickx\fw2\mvc\app\controllers\traits\file_upload;

/**
 * Flywheel2 File Upload特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait FileUploadTrait {
	/**	@property	ファイルアップロードを利用可能かどうか */
	public $fileUpload = false;

	/**
	 * ファイルアップロードの利用を開始します。
	 *
	 * アップロードフォームを表示するアクションと、アップロードファイルを受け取るアクションの両方でコールする必要があります。
	 */
	public function useFileUpload () {
		$this->fileUpload = FileUpload::PreCheck();
	}

	/**
	 * アップロードファイルのエラーを取得します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	int		エラーコード
	 */
	public function getUploadFileError ($name) {
		return Arrays::GetLowest($_FILES['data']['error'], $name);
	}

	/**
	 * アップロードファイルの一時展開ファイルパスを取得します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	string	アップロードファイルの一時展開ファイルパス
	 */
	public function getTmpUploadFilePath ($name) {
		return Arrays::GetLowest($_FILES['data']['tmp_name'], $name);
	}

	/**
	 * アップロードファイルのサイズを取得します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	int		アップロードファイルのサイズ
	 */
	public function getUploadFileSize ($name) {
		return Arrays::GetLowest($_FILES['data']['size'], $name);
	}

	/**
	 * アップロードファイルのマイムタイプを取得します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	string アップロードファイルのマイムタイプ
	 */
	public function getUploadFileMimeType ($name) {
		return Arrays::GetLowest($_FILES['data']['type'], $name);
	}

	/**
	 * アップロードファイルのアップロード時点でのファイル名を取得します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	string アップロード時点でのファイル名
	 */
	public function getUploadFileName ($name) {
		return Arrays::GetLowest($_FILES['data']['name'], $name);
	}

	/**
	 * アップロードしたファイル名から拡張子を取得します。
	 *
	 * 拡張子を取得できなかった場合は空文字を返します。
	 *
	 * @param	mixed	$name	アップロードフォーム名
	 * @return	string	ファイル名の拡張子 取得できな場合は空文字
	 */
	public function getUploadFileExtension ($name) {
		return FileSystem::GetExtension($this->getUploadFileName($name));
	}

	/**
	 * アップロードしたファイルを移動します。
	 *
	 * @param	mixed	$name		アップロードフォーム名
	 * @param	string	$file_path	移動先ファイルパス
	 */
	public function moveUploadFile ($name, $file_path) {
		return FileUpload::MoveUploadedFile($this->getTmpUploadFilePath($name), $file_path);
	}

	/**
	 * エンコーディング変換済みテンプファイルへのプロセスポインタを返します。
	 *
	 * ！！注意！！内部でPHPコマンドを実行します。
	 * ！！注意！！PHPコマンドのパスは指定していません。
	 * ！！注意！！現在のプロセス管理外のPHPコマンドが実行されるため、システム全体のメモリ使用量が極大化する可能性があります。
	 *
	 * @param	string		$tmp_path		エンコーディング変換して取得したいテンプファイルのパス
	 * @param	string		$to_encoding	変換後エンコーディング
	 * @param	string		$form_encoding	変換前エンコーディング
	 * @return	resource	プロセスポインタ
	 */
	public function openTmpFileDirtyPPointerConvertEncoding ($tmp_path, $to_encoding, $form_encoding, $php_path = null) {
		return FileUpload::TmpFileDirtyPOpenConvertEncoding($tmp_path, $to_encoding, $form_encoding, $php_path);
	}

	/**
	 * プロセスポインタを閉じます。
	 *
	 * @param	resource	$pp	プロセスポインタ
	 * @return	bool		プロセスポインタを閉じられた場合はtrue それ以外はfalse
	 */
	public function closeTmpFilePPointer ($pp) {
		return FileUpload::TmpFileDirtyPClose($pp);
	}
}
