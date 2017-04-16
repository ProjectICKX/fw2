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

namespace ickx\fw2\core\environment;

/**
 * 実行環境情報クラス
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Environment {
	//==============================================
	//アプリケーションの実行モード設定
	//==============================================
	/**
	 * @var	string	DEBUG		：デバッグモード
	 * @static
	 */
	const DEBUG			= 'DEBUG';

	/**
	 * @var	string	DEVELOPMENT	：開発環境モード
	 * @static
	 */
	const DEVELOPMENT	= 'DEVELOPMENT';

	/**
	 * @var	string	STAGING		：ステージング環境モード
	 * @static
	 */
	const STAGING		= 'STAGING';

	/**
	 * @var	string	PRODUCTION	：本番環境モード
	 * @static
	 */
	const PRODUCTION	= 'PRODUCTION';

	/**
	 * 実行環境がCLIかどうか返します。
	 *
	 * @return	bool	実行環境がcliの場合はtrue、そうでない場合はfalse
	 */
	public static function IsCli () {
		return (PHP_SAPI === 'cli');
	}

	/**
	 * 実行環境がCLI-Serverかどうか返します。
	 *
	 * @return	bool	実行環境がCLI-Serverの場合はtrue、そうでない場合はfalse
	 */
	public static function IsCliServer () {
		return (PHP_SAPI === 'cli-server');
	}
}
