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
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\compression\interfaces;

/**
 * 圧縮を扱うクラス向けインターフェースです。
 *
 * @category	Flywheel2
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface ICompression {
	/**
	 * @var	int	デフォルトの圧縮率
	 * @static
	 */
	const COMPRESS_LEVEL	= 7;

	/**
	 * @var	string	エンコードモード用名称定数
	 * @static
	 */
	const ENCODE			= 'encode';

	/**
	 * @var	string	デコードモード用名称定数
	 * @static
	 */
	const DECODE			= 'decode';

	/**
	 * @var	string	エンコードモード名：BASE64
	 * @static
	 */
	const ENCODE_BASE64		= 'base64';
}
