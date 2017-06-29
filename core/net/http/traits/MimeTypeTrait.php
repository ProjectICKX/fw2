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

namespace ickx\fw2\core\net\http\traits;

use ickx\fw2\io\file_system\interfaces\IExtension;
use ickx\fw2\international\encoding\Encoding;

/**
 * MIME TYPE特性。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait MimeTypeTrait {
	/**
	 * 拡張子とMIME TYPEの対応表を返します。
	 *
	 * @return	array	拡張子とMIME TYPEの対応表
	 */
	public static function GetMimeTypeMapForExt () {
		return [
			IExtension::EXTENSION_TXT	=> static::MIME_TYPE_TEXT,
			IExtension::EXTENSION_TEXT	=> static::MIME_TYPE_TEXT,
			IExtension::EXTENSION_HTML	=> static::MIME_TYPE_HTML,
			IExtension::EXTENSION_HTM	=> static::MIME_TYPE_HTML,
			IExtension::EXTENSION_XML	=> static::MIME_TYPE_XML,
			IExtension::EXTENSION_JS	=> static::MIME_TYPE_JS,
			IExtension::EXTENSION_JSON	=> static::MIME_TYPE_JSON,
			IExtension::EXTENSION_VBS	=> static::MIME_TYPE_VBS,
			IExtension::EXTENSION_CSS	=> static::MIME_TYPE_CSS,
			IExtension::EXTENSION_CSV	=> static::MIME_TYPE_COMMA_SEPARATED_VALUES,
			IExtension::EXTENSION_XLS	=> static::MIME_TYPE_MS_EXCEL,
			IExtension::EXTENSION_GIF	=> static::MIME_TYPE_GIF,
			IExtension::EXTENSION_JPG	=> static::MIME_TYPE_JPG,
			IExtension::EXTENSION_JPEG	=> static::MIME_TYPE_JPG,
			IExtension::EXTENSION_PNG	=> static::MIME_TYPE_PNG,
			IExtension::EXTENSION_CGI	=> static::MIME_TYPE_CGI,
			IExtension::EXTENSION_DOC	=> static::MIME_TYPE_DOC,
			IExtension::EXTENSION_PDF	=> static::MIME_TYPE_PDF,
			IExtension::EXTENSION_MP3	=> static::MIME_TYPE_MP3,
			IExtension::EXTENSION_MP4	=> static::MIME_TYPE_MPG,
			IExtension::EXTENSION_MPG	=> static::MIME_TYPE_MPG,
			IExtension::EXTENSION_MPEG	=> static::MIME_TYPE_MPEG,
			IExtension::EXTENSION_WAV	=> static::MIME_TYPE_WAV,
			IExtension::EXTENSION_WAVE	=> static::MIME_TYPE_WAVE,
		];
	}

	/**
	 * アップロードされたPNGファイルのMIME TYPEが正しいかどうか判定します。
	 *
	 * ※IE系ブラウザの場合、PNGファイルのMIME TYPEが独自仕様になるため。
	 *
	 * @param	string	$mime_type	判定するMIME TYPE
	 * @return	bool	PNGファイルのMIME TYPEの場合 bool true、そうでない場合 false
	 */
	public static function IsUploadPngImageMimeType ($mime_type) {
		return in_array($mime_type, static::GetUploadPngImageMimeType(), true);
	}

	/**
	 * アップロードされたJPEGファイルのMIME TYPEが正しいかどうか判定します。
	 *
	 * ※IE系ブラウザの場合、JPEGファイルのMIME TYPEが独自仕様になるため。
	 *
	 * @param	string	$mime_type	判定するMIME TYPE
	 * @return	bool	JPEGファイルのMIME TYPEの場合 bool true、そうでない場合 false
	 */
	public static function IsUploadJpegImageMimeType ($mime_type) {
		return in_array($mime_type, static::GetUploadJpegImageMimeType(), true);
	}

	/**
	 * アップロードされたファイル用のPNGファイル向けMIME TYPEセットを返します。
	 *
	 * ※IE系ブラウザの場合、PNGファイルのMIME TYPEが独自仕様になるため。
	 *
	 * @return	array	PNGファイルのMIME TYPEセット
	 */
	public static function GetUploadPngImageMimeType () {
		$mime_type_list = [
			static::MIME_TYPE_PNG,
		];
		if (static::IsCurrentUserAgentIe()) {
			$mime_type_list[] = static::MIME_TYPE_MS_PNG;
		}
		return $mime_type_list;
	}

	/**
	 * アップロードされたファイル用のJPEGファイル向けMIME TYPEセットを返します。
	 *
	 * ※IE系ブラウザの場合、JPEGファイルのMIME TYPEが独自仕様になるため。
	 *
	 * @return	array	JPEGファイルのMIME TYPEセット
	 */
	public static function GetUploadJpegImageMimeType () {
		$mime_type_list = [
			static::MIME_TYPE_JPG,
		];
		if (static::IsCurrentUserAgentIe()) {
			$mime_type_list[] = static::MIME_TYPE_MS_JPG;
		}
		return $mime_type_list;
	}

	/**
	 * CSVファイルのMIME TYPEが正しいかどうか判定します。
	 *
	 * @param	string	$mime_type	判定するMIME TYPE
	 * @return	bool	CSVファイルのMIME TYPEの場合 bool true、そうでない場合 false
	 */
	public static function IsCsvMimeType ($mime_type) {
		$mime_type_list = [
			static::MIME_TYPE_CSV,
			static::MIME_TYPE_COMMA_SEPARATED_VALUES
		];
		if (static::IsCurrentUserAgentIe()) {
			$mime_type_list[] = static::MIME_TYPE_MS_EXCEL;
		}
		return in_array($mime_type, $mime_type_list, true);
	}

	/**
	 * MIME TYPEセットを返します。
	 *
	 * @return	array	MIME TYPEセット
	 */
	public static function GetMimeTypeByExt ($ext) {
		$ext = strtolower($ext);
		$mime_type_map = static::GetMimeTypeMapForExt();
		return isset($mime_type_map[$ext]) ? $mime_type_map[$ext] : static::MIME_TYPE_BINARY;
	}

	/**
	 * MIME TYPEに使用できるキャラクタセットの対応表を返します。
	 *
	 * @return	array	キャラクタセットとMIME TYPEの対応表
	 */
	public static function GetCharsetMapForMimeType () {
		return [
			Encoding::UTF_8		=> static::CHARSET_UTF_8,
			Encoding::EUC_JP	=> static::CHARSET_EUC_JP,
			Encoding::SJIS		=> static::CHARSET_SHIFT_JIS,
			Encoding::SJIS_WIN	=> static::CHARSET_SHIFT_JIS,
		];
	}

	/**
	 * MIME TYPEに使用できるキャラクタセットを返します。
	 *
	 * @return	array	MIME TYPEに使用できるキャラクタセット、マッチするキャラクタセットが無い場合はnull
	 */
	public static function GetMimeTypeCharsetByCharset ($charset) {
		$charset_map = static::GetCharsetMapForMimeType();
		return isset($charset_map[$charset]) ? $charset_map[$charset] : null;
	}
}
