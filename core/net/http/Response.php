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
 * @package		core
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\net\http;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\core\status\Status;
use ickx\fw2\international\encoding\Encoding;
use ickx\fw2\io\file_system\FileSystem;
use ickx\fw2\basic\outcontrol\OutputBuffer;
use ickx\fw2\vartype\strings\Strings;

/**
 * HTTPレスポンスを扱います。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Response extends \ickx\fw2\core\net\http\Http {
	/** @var	string	COOKIEのデフォルトパラメータ */
	const SET_COOKIE_DEFAULT	= '';

	/** @var	string	表示モード：付属物 */
	const CONTENT_DISPOSITION_ATTACHMENT	= 'attachment';

	/** @var	string	表示モード：インライン */
	const CONTENT_DISPOSITION_INLINE		= 'inline';

	/** @var	string	HTTPダウンロード時のデフォルトチャンクサイズ */
	const CHUNK_SIZE						= 4096;

	/**
	 * クッキーを設定します。
	 *
	 * @param	string		$name				クッキー名
	 * @param	string		$value				値
	 * @param	array		$options			クッキー名、値以外のオプション 有効期限とパスのみ設定できる
	 * @param	callable	$callback_filter	値に対してかけるフィルタ
	 * @return	bool		クッキーの設定に成功した場合はtreu 失敗した場合はfalse
	 * @throws	CoreException	値に配列やオブジェクトを設定した場合
	 */
	public static function SetCookie ($name, $value, array $options = [], $callback_filter = null) {
		$expire		= $options['expire']	?? 0;
		$path		= $options['path']		?? static::SET_COOKIE_DEFAULT;
		$domain		= $options['domain']	?? Request::GetDomainName();
		$secure		= $options['secure']	?? (Request::GetCurrnetProtocol() === Request::PROTOCOL_SECURE);

		if (is_array($value) || is_object($value)) {
			throw CoreException::RaiseSystemError('Cookieに配列やオブジェクトを指定することはできません。name:%s', [$name]);
		}

		if (is_callable($callback_filter)) {
			$value = call_user_func($callback_filter, $value);
		}

		return setcookie($name, $value, $expire, $path, $domain, $secure, true);
	}

	/**
	 * クッキーを削除します。optionsはSetCookie時のものと完全に同一である必要があります。
	 *
	 * @param	string		$name				クッキー名
	 * @param	array		$options			クッキー名以外のオプション
	 * @return	bool		クッキーの設定に成功した場合はtreu 失敗した場合はfalse
	 */
	public static function DeleteCookie ($name, array $options = []) {
		$path		= $options['path'] ?? static::SET_COOKIE_DEFAULT;

		if (isset($_COOKIE[$name])) {
			unset($_COOKIE[$name]);
		}
		return setcookie($name, static::SET_COOKIE_DEFAULT, time() - 3600, $path, Request::GetDomainName(), true, true);
	}

	/**
	 * クッキーを設定します。
	 *
	 * セキュリティオプションを無効にすることが可能です。
	 * 通常はSetCookieメソッドを利用します。
	 *
	 * @param	string		$name				クッキー名
	 * @param	string		$value				値 配列の場合はシリアライズされる オブジェクトの場合はクラス情報が失われる
	 * @param	array		$options			クッキー名、値以外のオプション
	 * @param	callable	$callback_filter	値に対してかけるフィルタ
	 * @return	bool		クッキーの設定に成功した場合はtreu 失敗した場合はfalse
	 * @throws	CoreException	値に配列やオブジェクトを設定した場合
	 */
	public static function SetNotSafeCookie ($name, $value = self::SET_COOKIE_DEFAULT, array $options = [], $filter = null) {
		$expire		= $options['expire']	?? static::SET_COOKIE_DEFAULT;
		$path		= $options['path']		?? static::SET_COOKIE_DEFAULT;
		$domain		= $options['domain']	?? static::SET_COOKIE_DEFAULT;
		$secure		= $options['secure']	?? static::SET_COOKIE_DEFAULT;
		$httponly	= $options['httponly']	?? static::SET_COOKIE_DEFAULT;

		if (is_array($value) || is_object($value)) {
			throw CoreException::RaiseSystemError('Cookieに配列やオブジェクトを指定することはできません。name:%s', [$name]);
		}

		if (is_callable($callback_filter)) {
			$value = call_user_func($callback_filter, $value);
		}

		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * クッキーを削除します。optionsはSetCookie時のものと完全に同一である必要があります。
	 *
	 * SetNotSafeCookieで作成したクッキーを削除する際に利用します。
	 *
	 * @param	string		$name				クッキー名
	 * @param	array		$options			クッキー名以外のオプション
	 * @return	bool		クッキーの設定に成功した場合はtreu 失敗した場合はfalse
	 */
	public static function DeleteNotSafeCookie ($name, array $options = []) {
		$path		= $options['path']		?? static::SET_COOKIE_DEFAULT;
		$domain		= $options['domain']	?? static::SET_COOKIE_DEFAULT;
		$secure		= $options['secure']	?? static::SET_COOKIE_DEFAULT;
		$httponly	= $options['httponly']	?? static::SET_COOKIE_DEFAULT;

		if (isset($_COOKIE[$name])) {
			unset($_COOKIE[$name]);
		}
		return setcookie($name, static::SET_COOKIE_DEFAULT, time() - 3600, $path, $domain, $secure, $httponly);
	}

	/**
	 * ファイルダウンロードに必要なHTTP Response Headerの内容を配列として返します。
	 *
	 * @param	string	$file_name				ファイル名
	 * @param	string	$file_size				ファイルサイズ
	 * @param	string	$mime_type				マイムタイプ
	 * @param	string	$filename_to_charset	ファイル名の文字エンコード
	 * @param	string	$file_charset			ファイルの文字エンコード
	 * @return	array	ファイルダウンロードに必要なHTTP Response Headerの内容配列
	 */
	public static function GetHttpHeaderContentDispositionAttachment ($file_name, $file_size = null, $mime_type = self::MIME_TYPE_HTML, $content_disposition = self::CONTENT_DISPOSITION_ATTACHMENT, $filename_to_charset = Encoding::SJIS, $filename_from_charset = Encoding::UTF_8, $file_charset = null) {
		if ($content_disposition === static::CONTENT_DISPOSITION_INLINE || self::isCurrentUserAgentIe5dot5()) {
			$content_disposition = 'Content-Disposition: inline; filename=%s';
		} else {
			$content_disposition = 'Content-Disposition: attachment; filename=%s';
		}

		//UAがFirefoxの場合のみ、ファイル名のキャラクタセットを必ずUTF-8にする必要がある
		$filename_to_charset = (self::isCurrentUserAgentFirefox()) ? Encoding::UTF_8 : $filename_to_charset;
		$file_name = mb_convert_encoding($file_name, $filename_to_charset, $filename_from_charset);

		$charset = $file_charset !== null ? sprintf('; charset=%s', $file_charset) : '';

		$header_list = [
			'Pragma: private',
			'Cache-Control: private ,must-revalidate, max-age=0',
			'Expires: Sat, 26 Jul 1997 05:00:00 GMT',
			sprintf('Content-Type: %s%s', $mime_type, $charset),
			sprintf($content_disposition, $file_name),
		];

		if ($file_size !== null) {
			$header_list[] = sprintf('Content-Length: %s', $file_size);
		}

		return $header_list;
	}

	/**
	 * Traversableインターフェースを実装したオブジェクトまたは配列をファイルとして送信します。
	 *
	 * @param	Traversable	$instance			ファイルとして送信するTraversableインターフェースを実装したオブジェクトまたは配列
	 * @param	string		$file_name			ファイル名
	 * @param	string		$mime_type			MIME type
	 * @param	callable	$callback_filter	コールバックフィルタ
	 * @param	array		$header_row			ヘッダ行
	 * @param	string		$options			オプション
	 */
	public static function SendFileFromTraversableInstance ($instance, $file_name, $mime_type = self::MIME_TYPE_HTML, $callback_filter = null, $header_row_list = [], $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$raise_exception		= $options['raise_exception']		?? true;
		$filename_from_charset	= $options['from_charset']			?? Encoding::UTF_8;
		$filename_to_charset	= $options['to_charset']			?? Encoding::SJIS;
		$lf_code				= $options['lf_code']				?? Strings::CRLF;
		$last_lf_trim			= $options['last_lf_trim']			?? true;
		$emulation_mode			= $options['emulation_mode']		?? false;
		$content_disposition	= $options['content_disposition']	?? static::CONTENT_DISPOSITION_ATTACHMENT;

		//==============================================
		//型確認
		//==============================================
		if (!is_array($instance) && !$instance instanceof \Traversable) {
			return CoreException::ScrubbedThrow(Status::NotFound('実行出来ない型の変数を渡されました。'), $raise_exception);
		}

		//==============================================
		//コールバックフィルタ展開
		//==============================================
		if ($callback_filter !== null && !is_callable($callback_filter)) {
			return CoreException::ScrubbedThrow(Status::NotFound('実行不能なコールバックメソッドを渡されました。'), $raise_exception);
		}

		//==============================================
		//コールバックフィルタ展開
		//==============================================
		$callback_filter = $callback_filter ?: function ($row) {
			return implode(', ', (array) $row);
		};

		//==============================================
		//MIME type別の強制設定
		//==============================================
		switch ($mime_type) {
			case self::MIME_TYPE_CSV:
				$lf_code = Strings::CRLF;
				break;
		}

		//==============================================
		//ファイル送信開始
		//==============================================
		//HTTP ResponseHeaderの出力：エミュレーションモードの場合はファイルDLとならないので注意
		if (!$emulation_mode) {
			foreach (static::GetHttpHeaderContentDispositionAttachment(
				$file_name,
				$file_size,
				$mime_type,
				$content_disposition,
				$filename_to_charset,
				$filename_from_charset
			) as $header) {
				header(str_replace(["\r", "\n"], '', $header));
			}
		}

		//----------------------------------------------
		//実処理の開始
		//----------------------------------------------
		//既存の出力を全て送信
		OutputBuffer::Clean();
		flush();

		//timeout引き延ばし処理：実行開始秒
		$split_time = time();

		//改行コード置き場
		$next_lf_code = '';

		//もしもheader_rowが指定されていたら先行で出力
		if (!empty($header_row_list)) {
			if (!is_array($header_row_list[0])) {
				$header_row_list = [$header_row_list];
			}

			foreach ($header_row_list as $header_row) {
				print $next_lf_code;
				print $callback_filter($header_row, true);
				$next_lf_code = $lf_code;
			}
			flush();
		}

		//出力
		foreach ($instance as $row) {
			print $next_lf_code;

			print $callback_filter($row, false);
			flush();

			$next_lf_code = $lf_code;

			//3秒以上経過ごとにタイムリミットを延長する
			if (time() - $split_time > 3) {
				set_time_limit(time() - $split_time);
			}
		}

		if (!$last_lf_trim) {
			print $next_lf_code;
		}

		//==============================================
		//処理の終了
		//==============================================
		flush();
	}

	/**
	 * ファイルを送信します。
	 *
	 * @param	string	$file_path	出力するファイルパス
	 * @param	string	$file_name	出力するファイル名 null時はファイルパスから取得できるファイル名
	 * @param	string	$mime_type	MIME type
	 * @param	string	$options	オプション
	 */
	public static function SendFile ($file_path, $file_name = null, $mime_type = null, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$filename_from_charset	= $options['from_charset']			?? Encoding::UTF_8;
		$filename_to_charset	= $options['to_charset']			?? Encoding::SJIS;
		$chunk_size				= $options['chunk_size']			?? static::CHUNK_SIZE;
		$content_disposition	= $options['content_disposition']	?? static::CONTENT_DISPOSITION_ATTACHMENT;

		//==============================================
		//ファイル存在確認
		//==============================================
		if (($status = FileSystem::IsReadableFile($file_path, $options)) !== true) {
			return $status;
		}

		//==============================================
		//ファイル情報取得
		//==============================================
		$file_size = filesize($file_path);

		//==============================================
		//ファイル名確定
		//==============================================
		$file_name = $file_name ?: basename($file_path);

		//==============================================
		//mime type確定
		//==============================================
		if ($mime_type === null) {
			$ext = pathinfo($file_name, \PATHINFO_EXTENSION);
			$mime_type = isset(self::GetMimeTypeMapForExt()[$ext]) ? self::GetMimeTypeMapForExt()[$ext] : self::MIME_TYPE_BINARY;
		}

		//==============================================
		//ファイル送信開始
		//==============================================
		//HTTP ResponseHeaderの出力
		foreach (static::GetHttpHeaderContentDispositionAttachment(
			$file_name,
			$file_size,
			$mime_type,
			$content_disposition,
			$filename_to_charset,
			$filename_from_charset
		) as $header) {
			header(str_replace(["\r", "\n"], '', $header));
		}

		//----------------------------------------------
		//実処理の開始
		//----------------------------------------------
		//既存の出力を全て送信
		OutputBuffer::Clean();
		flush();

		//file pointer open
		$fp = fopen($file_path, 'rb');

		//timeout引き延ばし処理：実行開始秒
		$split_time = time();

		//チャンクサイズごとに送信
		while (!feof($fp)) {
			print fread($fp, $chunk_size);
			flush();

			//3秒以上経過ごとにタイムリミットを延長する
			if (time() - $split_time > 3) {
				set_time_limit(time() - $split_time);
			}
		}

		//==============================================
		//処理の終了
		//==============================================
		fclose($fp);
	}

	/**
	 * 変数上のデータをファイルとして出力します。
	 *
	 * @param	mixed	$data		ファイルとして出力するデータ
	 * @param	string	$file_name	出力するファイル名
	 * @param	string	$mime_type	MIME type
	 * @param	string	$options	オプション
	 */
	public static function SendDataAsFile ($data, $file_name, $mime_type = null, $options = []) {
		//==============================================
		//オプション展開
		//==============================================
		$filename_from_charset	= $options['from_charset']			?? Encoding::UTF_8;
		$filename_to_charset	= $options['to_charset']			?? Encoding::SJIS;
		$chunk_size				= $options['chunk_size']			?? static::CHUNK_SIZE;
		$content_disposition	= $options['content_disposition']	?? static::CONTENT_DISPOSITION_ATTACHMENT;

		//==============================================
		//データサイズ取得
		//==============================================
		$file_size = strlen($data);

		//==============================================
		//mime type確定
		//==============================================
		if ($mime_type === null) {
			$ext = pathinfo($file_name, \PATHINFO_EXTENSION);
			$mime_type = self::GetMimeTypeMapForExt()[$ext] ?? self::MIME_TYPE_BINARY;
		}

		//==============================================
		//ファイル送信開始
		//==============================================
		//HTTP ResponseHeaderの出力
		foreach (static::GetHttpHeaderContentDispositionAttachment(
			$file_name,
			$file_size,
			$mime_type,
			$content_disposition,
			$filename_to_charset,
			$filename_from_charset
		) as $header) {
			header(str_replace(["\r", "\n"], '', $header));
		}

		//----------------------------------------------
		//実処理の開始
		//----------------------------------------------
		//既存の出力を全て送信
		OutputBuffer::Clean();
		flush();

		//timeout引き延ばし処理：実行開始秒
		$split_time = time();

		//チャンクサイズごとに送信
		for ($i = 0;$i < $file_size;$i++) {
			print $data[$i];
			flush();
					//3秒以上経過ごとにタイムリミットを延長する
			if (time() - $split_time > 3) {
				set_time_limit(time() - $split_time);
			}
		}

		//==============================================
		//処理の終了
		//==============================================
	}

	/**
	 * 拡張子からContent-Typeヘッダを構築し返します。
	 *
	 * 該当するContent-Typeヘッダが存在しない場合、application/octet-streamとして返します。
	 *
	 * @param	string	$ext	拡張子
	 * @return	string	Conent-Typeヘッダ
	 */
	public static function GetContentTypeHeaderByExt ($ext) {
		return sprintf('Content-Type: %s', static::GetMimeTypeMapForExt()[$ext] ?? self::MIME_TYPE_BINARY);
	}

	/**
	 * 拡張子からContent-Typeヘッダを構築し出力します。
	 *
	 * 該当するContent-Typeヘッダが存在しない場合、application/octet-streamとして出力します。
	 *
	 * @param	string	$ext	拡張子
	 */
	public static function SendContentTypeHeaderByExt ($ext) {
		header(sprint('Content-Type: %s', static::GetMimeTypeMapForExt()[$ext] ?? self::MIME_TYPE_BINARY));
	}
}
