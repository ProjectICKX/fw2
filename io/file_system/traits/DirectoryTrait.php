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

namespace ickx\fw2\io\file_system\traits;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\status\DirectoryStatus;

/**
 * ディレクトリ特性。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait DirectoryTrait {
	/**
	 * ディレクトリを生成します。
	 *
	 * @param	string	$dir_path	生成するディレクトリ
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 *     'parents'           => (bool) 有効時に存在しない親ディレクトリも生成させる場合はtrue
	 *     'p'                 => 'parents'の省略表記
	 *     'skip'              => (bool) 既にディレクトリが存在している場合はスキップ
	 *     'mode'              => (oct) ディレクトリのモード
	 *     'owner'             => (string) ディレクトリのオーナー
	 *     'group'             => (string) ディレクトリのグループ
	 * ]
	 * @return	mixed	ディレクトリが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ディレクトリが利用可能ではない場合
	 */
	public static function CreateDirectory ($dir_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= $options['raise_exception']	?? true;
		$name				= $options['name']				?? '';
		$parents			= $options['parents'] ?? $options['p']	?? false;
		$skip				= $options['skip']				??  false;

		//==============================================
		//ディレクトリ検証
		//==============================================
		//ディレクトリ存在確認
		if (file_exists($dir_path)) {
			if ($skip) {
				return true;
			}
			return CoreException::ScrubbedThrow(DirectoryStatus::Found('既に%sディレクトリが存在します。dir_path:%s', [$name, $dir_path]), $raise_exception);
		}

		//親ディレクトリ権限確認
		$tmp_optioins = $options;
		$tmp_optioins['name']			= ($name) ? '親' : $name . 'の親';
		$tmp_optioins['auto_create']	= false;
		if (($status = static::IsEnableDirectory(dirname($dir_path), $tmp_optioins)) !== true && $parents === false) {
			return $status;
		}

		//==============================================
		//ディレクトリ作成
		//==============================================
		$mode			= $options['mode'] ?? static::DEFAULT_DIR_MODE;
		$owner			= $options['owner'] ?? null;
		$group			= $options['group'] ?? null;

		//ディレクトリの作成
		if (!mkdir($dir_path, $mode, $parents)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ディレクトリの作成に失敗しました。dir_path:%s, mode:%s, parents:%s'. [$dir_path, $mode ,$parents]), $raise_exception);
		}

		//持ち主の変更
		if ($owner !== null && !chown($dir_path, $owner)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ディレクトリのオーナー変更に失敗しました。dir_path:%s, owner:%s'. [$dir_path, $owner]), $raise_exception);
		}

		//グループの変更
		if ($group !== null && !chgrp($dir_path, $group)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ディレクトリのグループ変更に失敗しました。dir_path:%s, group:%s'. [$dir_path, $group]), $raise_exception);
		}

		//==============================================
		//処理の終了
		//==============================================
		return true;
	}

	/**
	 * ディレクトリを削除します。
	 *
	 * 再帰的に要素を削除するため、中身が含まれていても削除を行えます。
	 *
	 * @param	string	$dir_path	削除するディレクトリ
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ディレクトリが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ディレクトリが利用可能ではない場合
	 */
	public static function RemoveDirectory ($dir_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= $options['raise_exception'] ?? true;
		$name				= $options['name'] ?? '';

		//==============================================
		//ディレクトリ検証
		//==============================================
		//ディレクトリ存在確認
		if (!file_exists($dir_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::Found('%sディレクトリが存在しません。dir_path:%s', [$name, $dir_path]), $raise_exception);
		}

		//親ディレクトリ権限確認
		$tmp_optioins = $options;
		$tmp_optioins['name']			= ($name) ? '親' : $name . 'の親';
		$tmp_optioins['auto_create']	= false;
		if (($status = static::IsEnableDirectory(dirname($dir_path), $tmp_optioins)) !== true) {
			return $status;
		}

		//==============================================
		//ディレクトリ削除
		//==============================================
		foreach (new \DirectoryIterator($dir_path) as $element) {
			if ($element->isDot()) {
				continue;
			}
			if ($element->isDir()) {
				self::RemoveDirectory($element->getPathname(), $options);
			} else {
				unlink($element->getPathname());
			}
		}
		rmdir($dir_path);

		//==============================================
		//処理の終了
		//==============================================
		return true;
	}

	/**
	 * 利用可能なディレクトリか検証します。
	 *
	 * @param	string	$dir_path	検証するディレクトリ
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 *     'auto_create'       => (bool) ディレクトリが存在しない場合に自動生成させる場合はtrue
	 *     'parents'           => (bool) 'auto_create'有効時に存在しない親ディレクトリも生成させる場合はtrue
	 *     'p'                 => 'parents'の省略表記
	 *     'mode'              => (oct) 'auto_create'有効時に作成するディレクトリのモード
	 *     'owner'             => (string) 'auto_create'有効時に作成するディレクトリのオーナー
	 *     'group'             => (string) 'auto_create'有効時に作成するディレクトリのグループ
	 * ]
	 * @return	mixed	ディレクトリが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ディレクトリが利用可能ではない場合
	 */
	public static function IsEnableDirectory ($dir_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= $options['raise_exception'] ?? true;
		$name				= $options['name'] ?? '';
		$auto_create		= $options['auto_create'] ?? false;
		$parents			= $options['parents'] ?? $options['p'] ?? false;

		//==============================================
		//ディレクトリ検証
		//==============================================
		//ディレクトリ存在確認
		if (!file_exists($dir_path)) {
			if (!(($auto_create || $parents) ? static::CreateDirectory($dir_path, $options) : false)) {
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sディレクトリがありません。dir:%s', [$name, $dir_path]), $raise_exception);
			}
		}

		//対象確認
		if (!is_dir($dir_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sディレクトリではありません。dir:%s', [$name, $dir_path]), $raise_exception);
		}

		//書き込み権確認
		if (!is_writable($dir_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sディレクトリに書き込めません。dir:%s', [$name, $dir_path]), $raise_exception);
		}

		//読み込み権確認
		if (!is_readable($dir_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sディレクトリから読み込みできません。dir:%s', [$name, $dir_path]), $raise_exception);
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}
}
