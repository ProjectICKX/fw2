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
 * @category	Flywheel2 demo
 * @package		commons
 * @author		wakaba
 * @copyright	Copyright 2012, Project ICKX. (http://www.ickx.jp/)
 * @license		require consultation
 * @varsion		0.0.1
 */

namespace ickx\fw2\extensions\twig;

class Twig_Extension_Store {
	protected static $instance = null;

	protected $data = [];

	protected function __construct () {
	}

	public static function set ($name, $key, $value) {
		static::$instance ?? static::$instance = new static;
		static::$instance->data[$name][$key] = $value;
	}

	public static function get ($name, $key, $default_value = null) {
		static::$instance ?? static::$instance = new static;
		return static::$instance->data[$name][$key] ?? $default_value;
	}
}
