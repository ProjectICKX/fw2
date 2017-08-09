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

namespace ickx\fw2\mvc\app\traits;

/**
 * AppRouterTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppRouterTrait {
	/**
	 * utility：Routerに登録されている情報からURLを作成します。
	 *
	 * @param	string|ickx\fw2\mvc\app\AppController	$controller		コントローラ名またはコントローラ
	 * @param	string									$action_name	アクション名
	 * @param	array									$parameters		パラメータ
	 * @param	array									$var_parameters	遅延評価用パラメータ
	 * @param	string									$encoding		エンコーディング
	 * @return	string|bool								URL マッチするURLが無い場合はfalse
	 */
	public static function MakeUrl ($controller, $action_name = 'index', $parameters = [], $var_parameters = [], $encoding = null) {
		return Flywheel::MakeUrl ($controller, $action_name, $parameters, $var_parameters , $encoding);
	}
}
