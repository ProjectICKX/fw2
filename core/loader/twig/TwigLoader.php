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

namespace ickx\fw2\core\loader\twig;

/**
 * Twig用クラスローダー
 *
 * 通常はComposerのクラスマップを頼るべきだが、Composerが存在しない環境下でも利用するために実装
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class TwigLoader {
	const CLASS_FILE_EXT = '.php';

	protected static $_VendorRootDir = null;

	/**
	 * 自身をオートローダーとして登録します。
	 *
	 * このクラスローダ―を利用する場合には必ず呼びます。
	 * ex)
	 * \ickx\fw2\core\loader\custom\TwigLoader::Register();
	 */
	public static function Register ($throw = true, $prepend = true) {
		spl_autoload_register([static::class, 'AutoLoad'], $throw, $prepend);
	}

	/**
	 * オートローダー
	 *
	 * @param	string	$load_class_path	読み込むクラスパス
	 * @param	string	$class_file_ext		クラスファイル拡張子
	 * @throws	\Exception
	 */
	public static function AutoLoad ($load_class_path) {
		if (0 !== strpos($load_class_path, 'Twig')) {
			return false;
		}

		if (is_file($file = (static::$_VendorRootDir ?? static::$_VendorRootDir = \ickx\fw2\core\loader\ClassLoader::ExtractVendorRootDir()).'twig/twig/lib/'.str_replace(array('_', "\0"), array('/', ''), $load_class_path).'.php')) {
			require $file;
		}
	}
}
