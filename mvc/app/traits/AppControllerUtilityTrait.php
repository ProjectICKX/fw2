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
 * AppControllerUtilityTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppControllerUtilityTrait {
	/**
	 * 現在登録されているrender要素をdumpして終了します。
	 *
	 * @param	array	...$names
	 */
	public function dumpExit (...$names) {
		$renders = $this->render->getArrayCopy();

		if (empty($renders)) {
			echo '現在のレンダーの中身は空です。';
			exit;
		}

		if (empty($names)) {
			$names = array_keys($renders);
		}

		foreach ($names as $name) {
			echo '==============================================', \PHP_EOL, $name, \PHP_EOL, '==============================================', \PHP_EOL;
			var_dump(array_key_exists($name, $renders) ? $renders[$name] : '未定義');
			echo \PHP_EOL;
		}

		exit;
	}
}
