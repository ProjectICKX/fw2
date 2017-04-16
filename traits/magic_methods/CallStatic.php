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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\magic_methods;

use ickx\fw2\core\exception\CoreException;

/**
 * Flywheel2 Magic method supporter
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait CallStatic {
	/**
	 * 静的呼び出しメソッドの抽象化を支援します。
	 *
	 */
	public static function __callStatic ($name, $arguments) {
		static $method_cache;
		$class_name = get_called_class();

		if (!isset($method_cache[$class_name])) {
			foreach (array_filter((new \ReflectionClass($class_name))->getMethods(), function ($rm) {return preg_match("/^_(?:bulkA|a)ppendStaticCallMethod.*/", $rm->name) !== 0;}) as $rm) {
				$method_list = static::{$rm->name}();
				if (substr($rm->name, 0, 2) === '_a') {
					$method_list = [$method_list];
				}
				foreach ($method_list as $method) {
					$method_cache[$class_name][] = $method;
				}
			}
		}

		foreach ($method_cache[$class_name] as $method) {
			if (!(is_callable($method['matcher']) ? $matches = $method['matcher']() : preg_match($method['matcher'], $name, $matches) !== 0)) {
				continue;
			}

			$call_method = is_callable($method['method']) ? $method['method'] : [$class_name, $method['method']];
			$ret = $call_method($name, $arguments, $matches);
			if (isset($method['is_last']) && $method['is_last'] === false) {
				continue;
			}
			return $ret;
		}

		throw CoreException::RaiseSystemError('実行可能なメソッドを発見できませんでした。class:%s, method:%s。', [$class_name, $name]);
	}
}
