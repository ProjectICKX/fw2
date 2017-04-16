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

namespace ickx\fw2\mvc\app\controllers\interfaces;

/**
 * Flywheel2 コントローラインターフェースです。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IController {
	const NEXT_RENDERING	= 'NEXT_RENDERING';
	const NEXT_REDIRECT		= 'NEXT_REDIRECT';
	const NEXT_FORWARD		= 'NEXT_FORWARD';
	const NEXT_CALLER		= 'NEXT_CALLER';

	const DEFAULT_CONTROLLER	= 'index';
	const DEFAULT_ACTION		= 'index';
	const DEFAULT_NEXT_NAME		= 0;

	const SAFIX_APPSETUP	= 'AppSetup';
	const SAFIX_SETUP		= 'Setup';
	const SAFIX_VALIDATE	= 'Validate';
	const SAFIX_ACTION		= 'Action';
	const SAFIX_Rule		= 'Rule';
	const SAFIX_RENDER		= 'Render';
}
