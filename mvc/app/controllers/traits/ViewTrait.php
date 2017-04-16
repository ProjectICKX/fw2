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

namespace ickx\fw2\mvc\app\controllers\traits;

/**
 * Flywheel2 View特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ViewTrait {
	/**
	 * viewを実行します。
	 *
	 * @return	array	描画用データ
	 */
	public function view () {
		$view_method_name = Strings::ToLowerCamelCase($this->action.'_view');
		return (method_exists($this, $view_method_name)) ? $this->$view_method_name() : $this->render;
	}

	/**
	 * 指定された値をテンプレートにassignします。
	 *
	 * @param	string	変数名
	 * @param	mixed	assignする値
	 * @return	array	assignする変数のセット
	 */
	public function assignData ($key, $value) {
		return [$key => $value];
	}
}
