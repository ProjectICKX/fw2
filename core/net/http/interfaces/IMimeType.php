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

namespace ickx\fw2\core\net\http\interfaces;

/**
 * MIME TYPEインターフェース
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IMimeType {
	//==============================================
	//バイナリ
	//==============================================
	/** @var	string	MIME TYPE：バイナリ */
	const MIME_TYPE_BINARY					= 'application/octet-stream';

	//==============================================
	//テキスト
	//==============================================
	/** @var	string	MIME TYPE：テキスト */
	const MIME_TYPE_TEXT					= 'text/plain';

	/** @var	string	MIME TYPE：HTML */
	const MIME_TYPE_HTML					= 'text/html';

	/** @var	string	MIME TYPE：XML */
	const MIME_TYPE_XML						= 'text/xml';

	/** @var	string	MIME TYPE：JavaScript */
	const MIME_TYPE_JS						= 'text/javascript';

	/** @var	string	MIME TYPE：JSON */
	const MIME_TYPE_JSON					= 'application/json';

	/** @var	string	MIME TYPE：VBScript */
	const MIME_TYPE_VBS						= 'text/vbscript';

	/** @var	string	MIME TYPE：CSS */
	const MIME_TYPE_CSS						= 'text/css';

	/** @var	string	MIME TYPE：CSV */
	const MIME_TYPE_CSV						= 'text/csv';

	/** @var	string	MIME TYPE：CSV */
	const MIME_TYPE_COMMA_SEPARATED_VALUES	= 'text/comma-separated-values';

	//==============================================
	//イメージ
	//==============================================
	/** @var	string	MIME TYPE：gif */
	const MIME_TYPE_GIF						= 'image/gif';

	/** @var	string	MIME TYPE：jpg */
	const MIME_TYPE_JPG						= 'image/jpeg';

	/** @var	string	MIME TYPE：jpg(ms) */
	const MIME_TYPE_MS_JPG					= 'image/pjpeg';

	/** @var	string	MIME TYPE：png */
	const MIME_TYPE_PNG						= 'image/png';

	/** @var	string	MIME TYPE：png(ms) */
	const MIME_TYPE_MS_PNG					= 'image/x-png';

	//==============================================
	//音声
	//==============================================
	/** @var	string	MIME TYPE：mp3 */
	const MIME_TYPE_MP3						= 'audio/mpeg';

	/** @var	string	MIME TYPE：wav */
	const MIME_TYPE_WAV						= 'audio/wav';

	/** @var	string	MIME TYPE：wave */
	const MIME_TYPE_WAVE					= 'audio/wav';

	//==============================================
	//動画
	//==============================================
	/** @var	string	MIME TYPE：mp4 */
	const MIME_TYPE_MP4						= 'video/mp4';

	/** @var	string	MIME TYPE：mpg */
	const MIME_TYPE_MPG						= 'video/mpeg';

	/** @var	string	MIME TYPE：mpeg */
	const MIME_TYPE_MPEG					= 'video/mpeg';

	//==============================================
	//その他
	//==============================================
	/** @var	string	MIME TYPE：CGI */
	const MIME_TYPE_CGI						= 'application/x-httpd-cgi';

	/** @var	string	MIME TYPE：PDF */
	const MIME_TYPE_PDF						= 'application/pdf';

	/** @var	string	MIME TYPE：Microsoft WORD */
	const MIME_TYPE_DOC						= 'application/msword';

	/** @var	string	MIME TYPE：Microsoft EXCEL */
	const MIME_TYPE_MS_EXCEL				= 'application/vnd.ms-excel';

	//==============================================
	//キャラクターセット
	//==============================================
	/** @var	string	charset：UTF-8 */
	const CHARSET_UTF_8						= 'UTF-8';

	/** @var	string	charset：EUC-JP */
	const CHARSET_EUC_JP					= 'EUC-JP';

	/** @var	string	charset：SJIS */
	const CHARSET_SJIS						= 'Shift_JIS';

	/** @var	string	charset：Shift_JIS */
	const CHARSET_SHIFT_JIS					= 'Shift_JIS';
}
