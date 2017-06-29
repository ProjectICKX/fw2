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

namespace ickx\fw2\io\file_system\interfaces;

/**
 * ファイル拡張子管理インターフェース
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IExtension {
	const EXTENSION_TXT		= 'txt';
	const EXTENSION_TEXT	= 'text';
	const EXTENSION_HTML	= 'html';
	const EXTENSION_HTM		= 'htm';
	const EXTENSION_XML		= 'xml';
	const EXTENSION_JS		= 'js';
	const EXTENSION_JSON	= 'json';
	const EXTENSION_VBS		= 'vbs';
	const EXTENSION_CSS		= 'css';
	const EXTENSION_CSV		= 'csv';
	const EXTENSION_XLS		= 'xls';
	const EXTENSION_GIF		= 'gif';
	const EXTENSION_JPG		= 'jpg';
	const EXTENSION_JPEG	= 'jpeg';
	const EXTENSION_PNG		= 'png';
	const EXTENSION_CGI		= 'cgi';
	const EXTENSION_DOC		= 'doc';
	const EXTENSION_PDF		= 'pdf';
	const EXTENSION_MP3		= 'mp3';
	const EXTENSION_MP4		= 'mp4';
	const EXTENSION_MPG		= 'mpg';
	const EXTENSION_MPEG	= 'mpeg';
	const EXTENSION_WAV		= 'wav';
	const EXTENSION_WAVE	= 'wave';
}
