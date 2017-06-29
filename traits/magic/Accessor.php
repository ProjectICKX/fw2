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
 * Flywheel2 Magic accessor
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait Accessor {
	/**
	 *
	 * @param unknown $name
	 * @param unknown $arguments
	 * @return unknown
	 */
	public static function __callStatic ($name, $arguments) {
		if (method_exists(static::class, 'init')) {
			return static::init()->$name(...$arguments);
		}
		return (new static)->$name(...$arguments);
	}

	/**
	 *
	 * @param unknown $name
	 * @param unknown $arguments
	 * @throws \RuntimeException
	 * @return unknown|\ickx\fw2\traits\magic\Accessor
	 */
	public function __call ($name, $arguments) {
		if (method_exists($this, $name)) {
			return $this->$name(...$arguments);
		}

		if (!property_exists($this, $name)) {
			throw new \RuntimeException('Property fot found.');
		}

		if (empty($arguments)) {
			return $this->$name;
		}

		$this->$name = $arguments[0];
		return $this;
	}
}
