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

namespace ickx\fw2\core\net\http\auth\interfaces;

/**
 * Flywheel2 認証インターフェースです。
 *
 * @category	Flywheel2
 * @package		het
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IDigestResponse {
	/** @var	string	DIGEST認証用AUTHENTICATEヘッダ */
	const HTTP_HEADER_AUTHENTICATE = 'WWW-Authenticate: Digest realm="%s",qop="auth",nonce="%s",opaque="%s"';

	/** @var	string	ダイジェストヘッダ解析用パターン */
	const DIGEST_HEADER_PURSE_PATTERN	= '@(%s)=(?:([\'"])([^\2]+?)\2|([^\s,]+))@';

	/** @var	string	領域名：ダイアログで表示される アスキーのみ使用可能 */
	const PARAM_REALM		= 'realm';

	/** @var	string	ユーザ名 */
	const PARAM_USER_NAME	= 'username';

	/** @var	string	現在表示しているページのURL */
	const PARAM_URI			= 'uri';

	/** @var	string	サーバ側で生成した乱数 */
	const PARAM_NONCE		= 'nonce';

	/** @var	string	クライアント側で生成した乱数 */
	const PARAM_CNONCE		= 'cnonce';

	/** @var	string	保護レベル */
	const PARAM_QOP			= 'qop';

	/** @var	string	暗号方式 */
	const PARAM_ALGORITHM	= 'algorithm';

	/** @var	string	クライアントからのリクエスト回数(16進数) */
	const PARAM_NC			= 'nc';

	/** @var	string	ブラウザ側で生成したハッシュ値 */
	const PARAM_RESPONSE	= 'response';

	/** @var	string	デフォルトハッシュアルゴリズム */
	const DEFAULT_HASH_ALGORITHM	= 'sha256';

	/** @var	string	レスポンスセパレータ */
	const RESPONSE_SEPARATOR	= ':';
}

