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

namespace ickx\fw2\mvc\defaults;

//==============================================
//初期化
//==============================================
call_user_func(function ($find_package_phar = false) {
//ベンダーディレクトリの割り出し
//併せて定数化
	$dir = str_replace("\\", '/', __DIR__);
	if (substr($dir, 0, 7) === 'phar://') {
		$dir = preg_replace("@(^|/)(.+)\.phar(?:\.[^/]+)*(/|$)@", '$1$2$3', substr($dir, 7));
	}
	define('FW2_VENDOR_ROOT_DIR', mb_substr($dir, 0, mb_strlen(str_replace("\\", '/', __NAMESPACE__)) * -1 - 1));

	//==============================================
	//オートローダーの登録
	//==============================================
	$auto_loader_configs = [
		//Flywheel用オートローダー登録
		['vendor' => 'ickx', 'package' => 'fw2', 'path' => ['core', 'loader', 'ClassLoader.php'], 'register' => ['\ickx\fw2\core\loader\ClassLoader', 'Register']],
		//Twig用オートローダー
		['vendor' => 'ickx', 'package' => 'fw2', 'path' => ['core', 'loader', 'twig', 'TwigLoader.php'], 'register' => ['\ickx\fw2\core\loader\twig\TwigLoader', 'Register']],
	];

	foreach ($auto_loader_configs as $auto_loader_config) {
		$vendor_name = $auto_loader_config['vendor'];
		$package_name = $auto_loader_config['package'];
		$register_function = $auto_loader_config['register'];

		$vendor_path = implode('/', [FW2_VENDOR_ROOT_DIR, $vendor_name]);
		if ($find_package_phar) {
			$package_list = [$package_name];
			foreach (glob(sprintf('%s/%s.phar*', $vendor_path, $package_name)) as $path) {
				$package_list[] = basename($path);
			}
			sort($package_list);
			$package_name = array_pop($package_list);
		}

		$loader_path = $auto_loader_config['path'];
		if (is_array($loader_path)) {
			$loader_path = implode('/', $loader_path);
		}

		$register_path = implode('/', [$vendor_path, $package_name, $loader_path]);
		$register_path = str_replace("\\", '/', $register_path);
		if (strpos($register_path, '.phar')) {
			$register_path = sprintf('phar://%s', $register_path);
		}

		require $register_path;
		$register_function();
	}

	//composer用オートローダー登録
	if (file_exists($composer_autoloade_path = implode('/', [FW2_VENDOR_ROOT_DIR, 'autoload.php']))) {
		$composer_loader = require $composer_autoloade_path;
		\ickx\fw2\core\loader\ClassLoader::SetComposerLoader($composer_loader);
		$composer_loader->unregister();
	}

}, isset($find_package_phar) ? $find_package_phar : false);
