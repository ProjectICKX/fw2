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

namespace ickx\fw2\traits\magic;

/**
 * Flywheel2 Magic method supporter
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait CallStaticFilterTrait {
	/**
	 * 静的呼び出しメソッドの抽象化を支援します。
	 *
	 */
	public static function __callStatic ($name, $arguments) {
		$trait_seed = explode('__', $name);
		if (!isset($trait_seed[1])) {
			$backtrace = debug_backtrace()[1];
			set_error_handler(function ($errno, $errstr, $errfile, $errline) {echo $errstr;exit(1);});
			trigger_error(sprintf('<br /><b>Fatal error</b>: Call to undefined method %s::%s() in <b>%s</b> on line <b>%d</b>', $backtrace['class'], $backtrace['function'], $backtrace['file'], $backtrace['line']), \E_USER_ERROR);
			exit;
		}

		$current_class = static::class;

		$method_name = explode('__', $trait_seed[0])[0];
		$trait_name = $method_name . 'Trait';
		$target_class = new \ReflectionClass($current_class);
		foreach ($target_class->getTraits() as $trait) {
			$invoke = [$current_class, $method_name];
			if ($trait->getShortName() === $trait_name && is_callable($invoke)) {
				return call_user_func($invoke, $name, $arguments);
			}
		}

		$backtrace = debug_backtrace()[1];
		set_error_handler(function ($errno, $errstr, $errfile, $errline) {echo $errstr;exit(1);});
		trigger_error(sprintf('<br /><b>Fatal error</b>: Call to undefined method %s::%s() in <b>%s</b> on line <b>%d</b>', $backtrace['class'], $backtrace['function'], $backtrace['file'], $backtrace['line']), \E_USER_ERROR);
		exit;
	}
}
