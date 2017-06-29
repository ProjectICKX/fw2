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
 * @version		2.0.0
 */

namespace ickx\fw2\core\cli;

/**
 * CLI操作を支援するクラスです。。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */
class Cli {
	/**
	 * コマンドラインパラメータを取得します。
	 *
	 */
	public static function GetRequestParameterList () {
		if ($_SERVER['argc'] < 1) {
			return [];
		}
		$current_key = 0;
		$parameter_list = [];

		$args = array_slice($_SERVER['argv'], 1);
		$argc = count($args);

		for ($i = 0;$i < $argc;$i++) {
			$argv = $args[$i];
			if (substr($argv, 0, 1) === '-') {
				$current_key = substr($argv, 1);
				$parameter_list[$current_key] = '';
				if (isset($args[$i + 1]) && substr($args[$i + 1], 0, 1) !== '-') {
					$parameter_list[$current_key] = $args[$i + 1];
					$i++;
				}
				continue;
			}
			if (isset($parameter_list[$current_key])) {
				if (!is_array($parameter_list[$current_key])) {
					$parameter_list[$current_key] = (array) $parameter_list[$current_key];
				}
				$parameter_list[$current_key][] = $argv;
			} else {
				$parameter_list[$current_key] = $argv;
			}
		}

		return $parameter_list;
	}

	/**
	 * 最初のコマンドラインパラメータを取得します。
	 *
	 * @return	mixed	最初のコマンドラインパラメータ
	 */
	public static function GetFirstParameter () {
		$parameter_list = static::GetRequestParameterList();
		while (is_array($parameter_list)) {
			$parameter_list = array_shift($parameter_list);
		}
		return $parameter_list;
	}

	public static function Header () {
		if (\PHP_SAPI !== 'cli') {
			return ;
		}
		echo str_repeat('=', 80), \PHP_EOL;
		foreach (func_get_args() as $arg) {
			static::Echo($arg);
		}
		echo str_repeat('=', 80), \PHP_EOL;
	}

	public static function SubHeader () {
		if (\PHP_SAPI !== 'cli') {
			return ;
		}
		echo str_repeat('-', 80), \PHP_EOL;
		foreach (func_get_args() as $arg) {
			static::Echo($arg);
		}
		echo str_repeat('-', 80), \PHP_EOL;
	}

	public static function Echo () {
		if (\PHP_SAPI !== 'cli') {
			return ;
		}
		$time = explode('.', microtime(true));
		echo sprintf('[%s.%-04s] %s%s', date('Y-m-d H:i:s', $time[0]), substr($time[1], 0, 4), implode(', ', func_get_args()), \PHP_EOL);
	}
}
