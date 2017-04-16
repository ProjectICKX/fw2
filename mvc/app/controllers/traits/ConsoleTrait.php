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

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\international\encoding\Encoding;
use ickx\fw2\security\validators\Validator;

/**
 * Flywheel2 ConsoleLog特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ConsoleTrait {
	protected static $_consolePadLength = 80;

	public static function Prompt ($message, $validate_rule = [], $data_name = '', $options = []) {
		while (true) {
			static::ConsoleEcho($message);

			$data = trim(fgets(\STDIN));

			$result = Validator::Check($data, $validate_rule, $data_name);

			if (empty($result)) {
				break;
			}

			foreach ($result as $error_message) {
				static::ConsoleEcho($error_message);
			}
		}
		return $data;
	}

	public static function ConsoleTitle ($messages, $options = []) {
		static::ConsoleEcho(str_repeat('=', static::$_consolePadLength - 2), [0, static::$_consolePadLength, '+', '+']);
		static::ConsoleEcho($messages, [0, static::$_consolePadLength, '| ', ' |']);
		static::ConsoleEcho(str_repeat('=', static::$_consolePadLength - 2), [0, static::$_consolePadLength, '+', '+']);
	}

	public static function ConsoleHeader ($messages, $options = []) {
		echo \PHP_EOL;

		static::ConsoleEcho(str_repeat('-', static::$_consolePadLength - 2), [0, static::$_consolePadLength, '+', '+']);
		static::ConsoleEcho($messages, [0, static::$_consolePadLength, '| ', ' |']);
		static::ConsoleEcho(str_repeat('-', static::$_consolePadLength - 2), [0, static::$_consolePadLength, '+', '+']);
	}

	public static function ConsoleLog ($messages, $options = []) {
		$options['timestamp'] = Arrays::AdjustValue($options, 'timestamp', true);
		static::ConsoleEcho($messages, $options);
	}

	public static function ConsoleLogTable ($values, $options = []) {
		$encoding = isset($options['encoding']) ? $options['encoding'] : 'UTF-8';
		$header = isset($options['header']) ? $options['header'] : false;

		$width_list = [];

		if ($header === false) {
			$keys = [];
		} else if ($header !== true && !empty($header)) {
			$keys = $header;
		} else {
		$keys = current($values);
		$keys = array_keys($keys);
		}
		foreach ($keys as $idx) {
			$mb_width = mb_strwidth($idx, $encoding);
			$width_list[$idx] = $mb_width;
		}

		foreach ($values as $row) {
			foreach ($row as $idx => $value) {
				$mb_width = mb_strwidth($value, $encoding);
				if (!isset($width_list[$idx])) {
					$width_list[$idx] = 0;
				}
				if ($mb_width > $width_list[$idx]) {
					$width_list[$idx] = $mb_width;
				}
			}
		}

		$total_width = array_sum($width_list) + 4 + (count($width_list) - 1) * 3;
		static::ConsoleEcho(str_repeat('=', $total_width - 2), [0, 0, '+', '+']);

		$tmp_row = [];
		foreach ($keys as $idx) {
			$pad_vector = is_int($idx) ? \STR_PAD_LEFT : \STR_PAD_RIGHT;
			$pad_length = $width_list[$idx] - mb_strwidth($idx, $encoding);
			$pad_report = str_repeat(' ', $pad_length);
			$tmp_row[] = $pad_vector === \STR_PAD_LEFT ? $pad_report . $idx : $idx . $pad_report;
		}
		if (!empty($tmp_row)) {
		static::ConsoleEcho(implode(' | ', $tmp_row), [0, 0, '| ', ' |']);
		static::ConsoleEcho(str_repeat('=', $total_width - 2), [0, 0, '+', '+']);
		}

		foreach ($values as $row) {
			$tmp_row = [];
			foreach ($row as $idx => $value) {
				$pad_vector = is_int($value) ? \STR_PAD_LEFT : \STR_PAD_RIGHT;
				$pad_length = $width_list[$idx] - mb_strwidth($value, $encoding);
				$pad_report = str_repeat(' ', $pad_length);
				$tmp_row[] = $pad_vector === \STR_PAD_LEFT ? $pad_report . $value : $value . $pad_report;
			}
			static::ConsoleEcho(implode(' | ', $tmp_row), [0, 0, '| ', ' |']);
		}

		static::ConsoleEcho(str_repeat('=', $total_width - 2), [0, 0, '+', '+']);
	}

	public static function ConsoleEcho ($messages, $options = []) {
		if (\PHP_SAPI === 'cli') {
			$left_pad = Arrays::AdjustValue($options, [0, 'left'], 0);
			$right_pad = Arrays::AdjustValue($options, [1, 'right'], 0);

			$os_view_encoding = Encoding::GetOsViewEncoding();

			$prefix = Encoding::Adjust(Arrays::AdjustValue($options, [2, 'prefix'], ''), $os_view_encoding);
			$safix = Encoding::Adjust(Arrays::AdjustValue($options, [3, 'safix'], ''), $os_view_encoding);

			$timesampe = Arrays::AdjustValue($options, ['timestamp'], false);

			$encoding = isset($options['encoding']) ? $options['encoding'] : 'UTF-8';

			foreach ((array) $messages as $message) {
								$message = Encoding::Adjust($message, $os_view_encoding);

				if ($timesampe) {
					$mts = microtime(true);
					$mtime = explode('.', $mts);
					$message = sprintf('[%s.%s] %s (%s)', date('Y-m-d H:i:s', $mtime[0]), str_pad($mtime[1], 4, ' ',\ STR_PAD_RIGHT), $message, $mts - $_SERVER['REQUEST_TIME_FLOAT']);
				}

				$view_message = $prefix . $message;
				echo str_pad($view_message, mb_strwidth($view_message, $encoding) - strlen($view_message), ' ', \STR_PAD_LEFT);
				if ($right_pad > 0) {
					echo str_repeat(' ', $right_pad - mb_strwidth($view_message, $encoding) - mb_strwidth($prefix, $encoding));
				}
				echo $safix;
				echo \PHP_EOL;
			}
		}
	}
}

/*
assert
count
debug
dir
dirxml
error
group
groupCollapsed
groupEnd
info
log
markTimeline
profile
profileEnd
time
timeEnd
timeStamp
trace
warn
*/

