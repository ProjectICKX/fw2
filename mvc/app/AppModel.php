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

namespace ickx\fw2\mvc\app;

/**
 * Index
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class AppModel {
	use	\ickx\fw2\mvc\app\models\traits\ModelTrait,
		\ickx\fw2\traits\magic_methods\CallStatic,
		\ickx\fw2\io\rdbms\accessors\traits\MethodAccessorTrait;

	protected static $priority	= DBI::PRIORITY_MASTER;
	protected static $stack_no	= DBI::DEFAULT_STACK_NO;

	/**
	 * テーブルアクセス時のデフォルトオプションを返します。
	 *
	 */
	public static function GetDefaultOptions () {
		return [
			'connection_name'				=> static::GetConnectionName(),
			'priority'						=> static::$priority,
			'stack_no'						=> static::$stack_no,
			\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
		];
	}
}
