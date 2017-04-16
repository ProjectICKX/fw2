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
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\status;

/**
 * Statusインターフェース
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IStatus {
	const ID_FLYWHEEL	= 2;

	const OK			= 0b00000000000000000000000000000001;
	const NG			= 0b00000000000000000000000000000010;

	const RESERVE		= 0b00000000000000000000000000000100;
	const CANCEL		= 0b00000000000000000000000000001000;

	const FOUND			= 0b00000000000000000000000000010000;
	const NOT_FOUND		= 0b00000000000000000000000000100000;

	const LEGAL			= 0b00000000000000000000000001000000;
	const ILLEGAL		= 0b00000000000000000000000010000000;

	const INFO			= 0b00000000000000000000000100000000;
	const NOTICE		= 0b00000000000000000000001000000000;
	const ERROR			= 0b00000000000000000000010000000000;
	const WARNING		= 0b00000000000000000000100000000000;
	const FATAL			= 0b00000000000000000001000000000000;
	const UNKOWN		= 0b00000000000000000010000000000000;

	const USER			= 0b00000000000000000100000000000000;
	const SYSTEM		= 0b00000000000000001000000000000000;

	public function getChildren ();
	public function getCode ();
	public function getSeverity ();
	public function getId ();
	public function getMessage ();
	public function getException ();
	public function isMultiStatus ();
	public function isOK ();
	public function matches ($severity_mask);
	public function __toString();
}
