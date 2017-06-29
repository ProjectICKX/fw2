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
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\vartype\classes;

/**
 * クラスユーティリティクラスです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Classes {
	/** @var	string	継承先から実行 */
	const INVOKE_VECTOR_UP		= 1;

	/** @var	string	継承元から実行 */
	const INVOKE_VECTOR_DOWN	= 2;

	/**
	 * 指定したオーバーライドされているメソッドを全て実行します。
	 *
	 * @param	string	$class			対象となるクラス
	 * @param	string	$method_name	実行するメソッド名
	 * @param	int		$vector			処理する方向 Classes::INVOKE_VECTOR_UP：継承先から実行、Classes::INVOKE_VECTOR_DOWN：継承元から実行
	 * @return	array	メソッドの実行結果
	 */
	public static function InvokeOverrideMethod ($class, $method_name, $vector = self::INVOKE_VECTOR_UP) {
		$target_classes = [];

		$target_class = new \ReflectionClass($class);
		do {
			if (!$target_class->hasMethod($method_name)) {
				continue;
			}
			if ($target_class->getMethod($method_name)->getDeclaringClass()->getName() !== $target_class->getName()) {
				continue;
			}
			$target_classes[] = $target_class;
		} while ($target_class = $target_class->getParentClass());

		$ret_pool = [];
		$fetch_function = self::INVOKE_VECTOR_DOWN === $vector ? 'array_shift' : 'array_pop';
		while (($target_class = $fetch_function($target_classes)) !== null) {
			$ret_pool[] = $target_class->getMethod($method_name)->invoke(null);
		}

		return $ret_pool;
	}
}
