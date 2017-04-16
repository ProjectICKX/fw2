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
 * @package		commons
 * @author		wakaba
 * @copyright	Copyright 2012, Project ICKX. (http://www.ickx.jp/)
 * @license		require consultation
 * @varsion		0.0.1
 */

namespace ickx\fw2\extensions\twig\traits;

trait UtilityTrait {
	public function isEmpty ($value) {
		$value = (array) $value;
		return empty($value);
	}

	public function strTruncate ($value, $width, $trimmarker = '...', $offset = 0) {
		return mb_strimwidth($value, $offset, $width, $trimmarker, mb_detect_encoding($value));
	}

	public function shift ($value) {
		return array_shift($value);
	}
}
