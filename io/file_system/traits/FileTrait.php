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
use ickx\fw2\core\status\MultiStatus;
use ickx\fw2\io\file_system\status\DirectoryStatus;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\vartype\strings\Strings;

/**
 * ファイル特性。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait FileTrait {
	use ExtensionTrait;

	/**
	 * エンコーディングを変換した後のファイルからファイルポインタを生成して返します。
	 *
	 * ！！注意！！cli版PHPコマンドを直接叩いて実行します。ファイルサイズによっては予期しない問題が発生します。
	 * ！！注意！！cli版PHPコマンドを直接叩いて実行します。呼び出し元にてファイルパス制約を厳密に行ってください。
	 */
	public static function DirtyFopenConvertEncoding ($arg_file_path, $to_encoding, $from_encoding, $php_path = null) {
		//==============================================
		//何があっても/etcにはアクセスさせない
		//==============================================
		$file_path = realpath($arg_file_path);
		if (!$file_path) {
			throw CoreException::RaiseSystemError('対象ファイルが存在しません。path:%s', [$arg_file_path]);
		}

		if (substr($file_path, 0, 5) === '/etc/') {
			throw CoreException::RaiseSystemError('/etc以下を狙ったアクセスが行われました！path:%s', [$file_path]);
		}

		$php_path = $php_path ?: \PHP_BINARY;
		if (!realpath($php_path)) {
			throw CoreException::RaiseSystemError('対象コマンドが存在しません。path:%s', [$php_path]);
		}

		if ($php_path !== 'php' && substr(realpath($php_path), 0, 5) === '/etc/') {
			throw CoreException::RaiseSystemError('/etc以下を狙ったアクセスが行われました！path:%s', [$php_path]);
		}

		//
		$to_encoding = $to_encoding ?: mb_internal_encoding();

		if ($from_encoding) {
			$command = sprintf(
				"echo mb_convert_encoding(file_get_contents('%s'), '%s', '%s');",
				$file_path,
				$to_encoding,
				$from_encoding
			);
		} else {
			$command = sprintf(
				"echo mb_convert_encoding(file_get_contents('%s'), '%s');",
				$file_path,
				$to_encoding
			);
		}
//@TODO rbを定数化
		return popen(sprintf('%s -d memory_limit=-1 -r %s', escapeshellarg($php_path), escapeshellarg($command)), 'rb');
	}

	/**
	 * ファイルポインタを生成して返します。
	 *
	 * PHP組込関数のfopenと異なる点はファイルに確実に書き込みが出来るかどうかを検証するようになっている事です。
	 *
	 * @param	string	$file_path
	 * @param unknown $mode
	 * @param string $use_include_path
	 * @param string $context
	 */
	public static function FileOpen ($file_path, $mode, $use_include_path = false, $context = null, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';

		if (preg_match("/[rwaxc]\+?[tb]?/", $mode) === false) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルへのストリームアクセス形式が誤っています。mode:%s, file:%s', [$name, $mode, $file_path]), $raise_exception);
		}

		$mode_set = str_split($mode);

		$valid_readable	= false;
		$valid_writable	= false;
		$valid_exists	= false;

		switch ($mode_set[0]) {
			case 'r':
				$valid_readable	= true;
				break;
			case 'x':
				$valid_exists	= true;
			case 'w':
			case 'a':
			case 'c':
				$valid_writable	= true;
				break;
			default:
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルへのストリームアクセス形式が誤っています。mode:%s, file:%s', [$name, $mode, $file_path]), $raise_exception);
				break;
		}

		if (isset($mode_set[1]) && $mode_set[1] === '+') {
			$valid_readable	= true;
			$valid_writable	= true;
		}

		$parent_dir = dirname($file_path);
		clearstatcache(true, $parent_dir);
		if (!file_exists($parent_dir)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルの親ディレクトリが存在しません。file:%s', [$name, $file_path]), $raise_exception);
		}

		if (!is_readable($parent_dir)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルの親ディレクトリを開けません。file:%s', [$name, $file_path]), $raise_exception);
		}

		clearstatcache(true, $file_path);
		$file_exists = file_exists($file_path);

		if ($valid_writable) {
			if (!is_writable($parent_dir)) {
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルの親ディレクトリに書き込み権がありません。。file:%s', [$name, $file_path]), $raise_exception);
			}

			if ($valid_exists) {
				if ($file_exists) {
					return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルが既に存在しています。file:%s', [$name, $file_path]), $raise_exception);
				}
			}
			if ($file_exists && !is_writable($file_path)) {
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルに対する書き込み権がありません。file:%s', [$name, $file_path]), $raise_exception);
			}
		}

		if ($valid_readable) {
			if (!$file_exists) {
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルが存在しません。file:%s', [$name, $file_path]), $raise_exception);
			}

			if (!is_readable($file_path)) {
				return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルに対する読み込み権がありません。file:%s', [$name, $file_path]), $raise_exception);
			}
		}

		return $context === null ? fopen($file_path, $mode, $use_include_path) : fopen($file_path, $mode, $use_include_path, $context);
	}

	/**
	 * ファイルを生成します。
	 *
	 * @param	string	$file_path	生成するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 *     'auto_create'       => (bool) ディレクトリが存在しない場合に自動生成させる場合はtrue
	 *     'parents'           => (bool) 'auto_create'有効時に存在しない親ディレクトリも生成させる場合はtrue
	 *     'p'                 => 'parents'の省略表記
	 *     'mode'              => (oct) ディレクトリのモード
	 *     'owner'             => (string) ディレクトリのオーナー
	 *     'group'             => (string) ディレクトリのグループ
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function CreateFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';
		$parents			= isset($options['parents']) ? $options['parents'] : isset($options['p']) ? $options['p'] : false;

		//==============================================
		//インスタンス構築
		//==============================================
		$spi = new \SplFileInfo($file_path);

		//==============================================
		//ファイル検証
		//==============================================
		//ファイル存在確認
		if (file_exists($file_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::Found('既にファイルが存在します。dir_path:%s'. [$file_path]), $raise_exception);
		}

		//親ディレクトリ権限確認
		$tmp_optioins = $options;
		$tmp_optioins['name']			= ($name) ? '親' : $name . 'の親';
		$tmp_optioins['auto_create']	= false;
		if (($status = static::IsEnableDirectory(dirname($file_path), $tmp_optioins)) !== true && $parents === false) {
			return $status;
		}

		//==============================================
		//ファイル作成
		//==============================================
		$mode	= isset($options['mode']) ? $options['mode'] : static::DEFAULT_DIR_MODE;
		$owner	= isset($options['owner']) ? $options['owner'] : null;
		$group	= isset($options['group']) ? $options['group'] : null;

		//ファイルの作成
		if (!mkdir($file_path, $mode, $parents)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ファイルの作成に失敗しました。dir_path:%s, mode:%s, parents:%s'. [$file_path, $mode ,$parents]), $raise_exception);
		}

		//持ち主の変更
		if ($owner !== null && !chown($file_path, $owner)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ファイルのオーナー変更に失敗しました。dir_path:%s, owner:%s'. [$file_path, $owner]), $raise_exception);
		}

		//グループの変更
		if ($group !== null && !chgrp($file_path, $group)) {
			return CoreException::ScrubbedThrow(Status::SystemError('ファイルのグループ変更に失敗しました。dir_path:%s, group:%s'. [$file_path, $group]), $raise_exception);
		}

		//==============================================
		//処理の終了
		//==============================================
		return true;
	}

	/**
	 * 指定されたファイルパスが有効かどうか返します。
	 *
	 * @param	string	$file_path	判定するファイルパス
	 * @param	array	$options	オプション
	 * @return	bool	ファイルパスが有効な場合はtrue, そうでない場合はfalse
	 */
	public static function EnableFilePath  ($file_path, $options = []) {
		return static::EnableFile ($file_path, $options);
	}

	/**
	 * 利用可能なファイルか検証します。
	 *
	 * @param	string	$file_path	検証するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 *     'mode'              => (oct) 'auto_create'有効時のモード
	 *     'owner'             => (string) 'auto_create'有効時のオーナー
	 *     'group'             => (string) 'auto_create'有効時のグループ
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function EnableFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';
		$auto_create		= isset($options['auto_create']) ? $options['auto_create'] : false;

		//==============================================
		//ファイル検証
		//==============================================
		//書き込み権確認
		if (($status = static::IsReadableFile($file_path, $options)) !== true) {
			return $status;
		}

		//読み込み権確認
		if (($status = static::IsWritableFile($file_path, $options)) !== true) {
			return $status;
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}

	/**
	 * ファイルが存在するか検証します。
	 *
	 * @param	string	$file_path	検証するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function ExistsFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';

		//==============================================
		//ファイル検証
		//==============================================
		//ファイル存在確認
		clearstatcache($file_path, true);
		if (!file_exists($file_path)) {
			empty($file_path) && $file_path = sprintf('ファイルパスが指定されていません。値：%s', var_export($file_path, true));
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルがありません。file_path:%s', [$name, $file_path]), $raise_exception);
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}

	/**
	 * 対象がファイルどうか検証します。
	 *
	 * @param	string	$file_path	検証するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function IsFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';

		//==============================================
		//ファイル検証
		//==============================================
		//ファイル存在確認
		if (($status = static::ExistsFile($file_path, $options)) !== true) {
			return $status;
		}

		//対象確認
		if (!is_file($file_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sがファイルではありません。file:%s', [$name, $file_path]), $raise_exception);
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}

	/**
	 * 読み込み可能なファイルか検証します。
	 *
	 * @param	string	$file_path	検証するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function IsReadableFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';

		//==============================================
		//ファイル検証
		//==============================================
		//ファイル存在確認
		if (($status = static::ExistsFile($file_path, $options)) !== true) {
			return $status;
		}

		//対象確認
		if (($status = static::IsFile($file_path, $options)) !== true) {
			return $status;
		}

		//読み込み権確認
		if (!is_readable($file_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルの読み込み権がありません。file:%s', [$name, $file_path]), $raise_exception);
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}

	/**
	 * 書き込み可能なファイルか検証します。
	 *
	 * @param	string	$file_path	検証するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function IsWritableFile ($file_path, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception	= isset($options['raise_exception']) ? $options['raise_exception'] : true;
		$name				= isset($options['name']) ? $options['name'] : '';
		$is_overwrite		= isset($options['is_overwrite']) ? $options['is_overwrite'] : true;

		//==============================================
		//ファイル検証
		//==============================================
		//ファイル存在確認
		if (($status = static::ExistsFile($file_path, $options)) !== true) {
			return $status;
		}

		//対象確認
		if (($status = static::IsFile($file_path, $options)) !== true) {
			return $status;
		}

		//書き込み権確認
		if (!is_writable($file_path)) {
			return CoreException::ScrubbedThrow(DirectoryStatus::NotFound('%sファイルの書き込み権がありません。file:%s', [$name, $file_path]), $raise_exception);
		}

		//==============================================
		//検証の終了
		//==============================================
		return true;
	}

	/**
	 * 指定されたファイルの拡張子を返します。
	 *
	 * @param	string	$file_path	拡張子を調べるファイルパス
	 * @return	string	ファイルの拡張子
	 */
	public static function GetExtension ($file_path) {
		return (new \SplFileInfo($file_path))->getExtension();
	}

	/**
	 * ファイルを削除します。
	 *
	 * @param	string	$file_path	削除するファイル
	 * @param	array	$options	オプション
	 * [
	 *     'raise_exception'   => (bool) エラー発生時に例外を起こさせる場合はtrue
	 *     'name'	           => (string) エラー表示用の名称
	 * ]
	 * @return	mixed	ファイルが利用可能な場合はtrue そうでない場合はStatusインスタンス
	 * @throws	raise_exceptionオプションがtrueとして設定され、ファイルが利用可能ではない場合
	 */
	public static function RemoveFile ($file_path, $options = []) {
		//==============================================
		//ファイルを削除できるか検証する
		//==============================================
		if (($status = static::IsWritableFile($file_path, $options) !== true)) {
			return $status;
		}

		//==============================================
		//ファイルを削除する
		//==============================================
		return unlink($file_path);
	}

	/**
	 * 指定されたファイルの権限を取得します。
	 *
	 * @param	string	$path	ファイルパス
	 * @return	array	ファイルの権限
	 */
	public static function GetFilePermission ($path) {
		$file_perms = fileperms($path);

		//タイプ
		$type_list = [
			's'	=> 0xC000,	//ソケット
			'l'	=> 0xA000,	//シンボリックリンク
			'-'	=> 0x8000,	//ファイル
			'b'	=> 0x6000,	//ブロックスペシャルファイル
			'd'	=> 0x4000,	//ディレクトリ
			'c'	=> 0x2000,	//キャラクタスペシャルファイル
			'p'	=> 0x1000,	//FIFO パイプ
			'u'	=> 0x0000,	//不明
		];
		foreach ($type_list as $type => $perms) {
			if (($file_perms & $perms) == $perms) {
				break;
			}
		}

		// 所有者
		$owner = [
			'r'	=> (($file_perms & 0x0100) ? 'r' : '-'),
			'w'	=> (($file_perms & 0x0080) ? 'w' : '-'),
			'x'	=> (($file_perms & 0x0040) ? (($file_perms & 0x0800) ? 's' : 'x' ) : (($file_perms & 0x0800) ? 'S' : '-')),
		];

		// グループ
		$group = [
			'r'	=> (($file_perms & 0x0020) ? 'r' : '-'),
			'w'	=> (($file_perms & 0x0010) ? 'w' : '-'),
			'x'	=> (($file_perms & 0x0008) ? (($file_perms & 0x0400) ? 's' : 'x' ) : (($file_perms & 0x0400) ? 'S' : '-')),
		];

		// 全体
		$other = [
			'r'	=> (($file_perms & 0x0004) ? 'r' : '-'),
			'w'	=> (($file_perms & 0x0002) ? 'w' : '-'),
			'x'	=> (($file_perms & 0x0001) ? (($file_perms & 0x0200) ? 's' : 'x' ) : (($file_perms & 0x0200) ? 'S' : '-')),
		];

		return [
			'oct'		=> substr(sprintf('%o', $file_perms), -4),
			'string'	=> $type . implode('', $owner) . implode('', $group) . implode('', $other),
			'type'		=> $type,
			'owner'		=> $owner,
			'group'		=> $group,
			'other'		=> $other,
		];
	}

	/**
	 * 対象ファイルが現在の実行ユーザのものかを判定します。
	 *
	 * @param	string	$path	判定するファイルパス
	 * @return	bool	対象のファイルが実行ユーザのものの場合はtrue、そうでない場合はfalse
	 */
	public static function IsOwnFile ($path) {
		return get_current_user() === static::GetOwnerName($path);
	}

	/**
	 * 対象ファイルのオーナー情報を取得します。
	 *
	 * @param	string	$path	オーナー情報を取得するファイルパス
	 * @return	array	ユーザ情報
	 */
	public static function GetOwnerInfo ($path) {
		return posix_getpwuid(fileowner($path));
	}

	/**
	 * 対象ファイルのオーナー名を取得します。
	 *
	 * @param	string	$path	オーナー名を取得するファイルパス
	 * @return	string	オーナー名
	 */
	public static function GetOwnerName ($path) {
		return posix_getpwuid(fileowner($path))['name'];
	}
}
