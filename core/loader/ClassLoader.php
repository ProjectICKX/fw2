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

namespace ickx\fw2\core\loader;

/**
 * クラスローダー
 *
 * もっとも最初に呼ばれる処理のため、他の処理を一切頼らない実装の必要がある。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class ClassLoader {
	/** @var	string	デフォルトのクラスファイル拡張子 */
	const CLASS_FILE_EXT = '.php';

	/** @var	string	名前空間のセパレータ */
	const NAMESPACE_SEPARATOR = "\\";

	/** @var	string	デフォルトの文字エンコーディング */
	const DEFAULT_ENCODING = 'UTF-8';

	/** @var	string	ベンダールートディレクトリ */
	protected static $_VendorRootDir = null;
	protected static $_SrcRootDir = null;

	/** @var	array	既に読み込んでいるファイルのリスト */
	protected static $_LoadedRealFilePathList = [];

	/** @var	array	デフォルトで読み込まれるクラスパスリスト */
	protected static $_ClassPathList = [
		'ClassLoader'	=> 'ickx\fw2\core\loader\ClassLoader',
	];

	protected static $_UseComposerLoader = false;

	protected static $_ComposerLoader = null;

	/**
	 * 自身をオートローダーとして登録します。
	 *
	 * このクラスローダ―を利用する場合には必ず呼びます。
	 * ex)
	 * \ickx\fw2\core\loader\ClassLoader::Register();
	 */
	public static function Register ($throw = true, $prepend = false) {
		ini_set('unserialize_callback_func', 'spl_autoload_call');

		static::InitRootDir();
		spl_autoload_register([static::class, 'AutoLoad'], $throw, $prepend);
	}

	/**
	 * オートローダー
	 *
	 * @param	string	$class_name		読み込むクラスパス
	 * @param	string	$class_file_ext	クラスファイル拡張子
	 * @throws	\Exception
	 */
	public static function AutoLoad ($class_name, $class_file_ext = self::CLASS_FILE_EXT) {
		$f = $class_name === 'App\common\constants\path\FilePath';

		// Connectからの変換
		if (isset(static::$_ClassPathList[$class_name])) {
			$load_class_path = static::$_ClassPathList[$class_name];

			// クラスパスをリアルファイルパスに変換
			if (static::$_UseComposerLoader) {
				if (false !== $real_file_path = static::$_ComposerLoader->findFile($load_class_path)) {
					// 相対パスが含まれているケースを潰す
					if ($real_file_path !== $real_file_path = realpath($real_file_path)) {
						static::$_ComposerLoader->addClassMap([$load_class_path => $real_file_path]);
					}
				}
			}

			if (false === $real_file_path) {
				//パフォーマンスチューニング Ref) static::ClassPathToRealFilePath($class_name, $class_file_ext)
				$real_file_path = static::$_VendorRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
				if (!file_exists($real_file_path)) {
					$real_file_path = static::$_SrcRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
					if (!file_exists($real_file_path)) {
						throw new \Exception('FW2 ClassLoader Exception: Class file not found:'. $class_name .' file path:'. $real_file_path);
					}
				}
			}

			// ファイルがロードされていない場合のみincludeする
			if (!isset(static::$_LoadedRealFilePathList[$real_file_path])) {
				if (!class_exists($load_class_path, true) && !interface_exists($load_class_path, true) && !trait_exists($load_class_path, true)) {
					include $real_file_path;
				}

				//読み込み済みリアルファイルパスにフラグを立てる
				static::$_LoadedRealFilePathList[$real_file_path] = true;
			}

			if ($load_class_path !== $class_name) {
				if (!class_exists($class_name, true) && !interface_exists($class_name, true) && !trait_exists($class_name, true)) {
					class_alias($load_class_path, $class_name, true);
				}
			}

			//クラスが存在している事を確認
			//パフォーマンスチューニング Ref) !static::ExistsClass($class_path)
			if (!class_exists($class_name, true) && !interface_exists($class_name, true) && !trait_exists($class_name, true)) {
				throw new \Exception('FW2 ClassLoader Exception: Class not found:'. $class_name .' file path:'. $real_file_path);
			}

			return true;
		}

		// 生パスとして処理
		$load_class_path = $class_name;

		// クラスパスをリアルファイルパスに変換
		if (static::$_UseComposerLoader) {
			if (false !== $real_file_path = static::$_ComposerLoader->findFile($load_class_path)) {
				// 相対パスが含まれているケースを潰す
				if ($real_file_path !== $real_file_path = realpath($real_file_path)) {
					static::$_ComposerLoader->addClassMap([$load_class_path => $real_file_path]);
				}
			}
		}

		if (false === $real_file_path) {
			//パフォーマンスチューニング Ref) static::ClassPathToRealFilePath($class_name, $class_file_ext)
			$real_file_path = static::$_VendorRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
			if (!file_exists($real_file_path)) {
				$real_file_path = static::$_SrcRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
			}
		}

		if (file_exists($real_file_path)) {
			// ファイルがロードされていない場合のみincludeする
			if (!isset(static::$_LoadedRealFilePathList[$real_file_path])) {
				if (!class_exists($load_class_path, true) && !interface_exists($load_class_path, true) && !trait_exists($load_class_path, true)) {
					include $real_file_path;
				}

				//読み込み済みリアルファイルパスにフラグを立てる
				static::$_LoadedRealFilePathList[$real_file_path] = true;

				if (class_exists($class_name, true) || interface_exists($class_name, true) || trait_exists($class_name, true)) {
					return true;
				}
			}
		}

		// 短縮表記検索
		$class_name_part = mb_substr($class_name, mb_strrpos($class_name, "\\") + 1);

		// Connectからの変換
		if (isset(static::$_ClassPathList[$class_name_part])) {
			$load_class_path = static::$_ClassPathList[$class_name_part];

			// クラスパスをリアルファイルパスに変換
			if (static::$_UseComposerLoader) {
				if (false !== $real_file_path = static::$_ComposerLoader->findFile($load_class_path)) {
					// 相対パスが含まれているケースを潰す
					if ($real_file_path !== $real_file_path = realpath($real_file_path)) {
						static::$_ComposerLoader->addClassMap([$load_class_path => $real_file_path]);
					}
				}
			}

			if (false === $real_file_path) {
				//パフォーマンスチューニング Ref) static::ClassPathToRealFilePath($class_name, $class_file_ext)
				$real_file_path = static::$_VendorRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
				if (!file_exists($real_file_path)) {
					$real_file_path = static::$_SrcRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($load_class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
					if (!file_exists($real_file_path)) {
						throw new \Exception('FW2 ClassLoader Exception: Class file not found:'. $class_name .' file path:'. $real_file_path);
					}
				}
			}

			// ファイルがロードされていない場合のみincludeする
			if (!isset(static::$_LoadedRealFilePathList[$real_file_path])) {
				if (!class_exists($load_class_path, true) && !interface_exists($load_class_path, true) && !trait_exists($load_class_path, true)) {
					include $real_file_path;
				}

				//読み込み済みリアルファイルパスにフラグを立てる
				static::$_LoadedRealFilePathList[$real_file_path] = true;
			}

			if ($load_class_path !== $class_name) {
				if (!class_exists($class_name, true) && !interface_exists($class_name, true) && !trait_exists($class_name, true)) {
					class_alias($load_class_path, $class_name, true);
				}
			}

			//クラスが存在している事を確認
			//パフォーマンスチューニング Ref) !static::ExistsClass($class_path)
			if (!class_exists($class_name, true) && !interface_exists($class_name, true) && !trait_exists($class_name, true)) {
				throw new \Exception('FW2 ClassLoader Exception: Class not found:'. $class_name .' file path:'. $real_file_path);
			}

			return true;
		}

		throw new \Exception('FW2 ClassLoader Exception: Class not found:'. $class_name .' file path:'. $real_file_path);
	}

	/**
	 *
	 */
	public static function Connect ($name, $class_path) {
		static::$_ClassPathList[$name] = $class_path;
	}

	public static function ReleaseConnect ($name) {
		unset(static::$_ClassPathList[$name]);
	}

	/**
	 * ベンダールートディレクトリを割り出します。
	 */
	public static function ExtractVendorRootDir ($class_name = null, $dir_path = __DIR__) {
		$dir = str_replace("\\", '/', __DIR__);
		if (substr($dir, 0, 7) === 'phar://') {
			$dir = preg_replace("@(^|/)(.+)\.phar(?:\.[^/]+)*(/|$)@", '$1$2$3', substr($dir, 7));
		}
		return sprintf('%s/', mb_substr($dir, 0, mb_strlen(str_replace("\\", '/', __NAMESPACE__)) * -1 - 1));
	}

	/**
	 * ファーパスを割り出します。
	 *
	 * 但し、拡張子が".phar"のものだけに限ります。
	 */
	public static function ExtractPharPath ($real_file_path) {
		$phar_file_path = $real_file_path;
		while (!file_exists($phar_file_path)) {
			if (file_exists($phar_file_path . '.phar')) {
				preg_match("@^". $phar_file_path ."(.+)@", $real_file_path, $matches);
				return 'phar://'. $phar_file_path . '.phar' . $matches[1];
			}
			$phar_file_path = dirname($phar_file_path);
		}
		return false;
	}

	/**
	 * クラス名からネームスペース名を割り出します。
	 *
	 * @param	string	$class_name	ネームスペースを割り出したいクラス名
	 * @param	string	$encoding	ファイルシステムのエンコーディング
	 * @return	string	ネームスペース名
	 */
	public static function ExtractNameSpace ($class_name, $encoding = self::DEFAULT_ENCODING) {
		return (mb_substr($class_name, mb_strlen($class_name, $encoding) - 1, 1, $encoding) === static::NAMESPACE_SEPARATOR)
		 ? $class_name
		 : mb_substr( $class_name, 0, mb_strrpos($class_name, static::NAMESPACE_SEPARATOR, 0, $encoding), $encoding);
	}

	/**
	 * 利用可能なクラス名か調べます。
	 *
	 * @param	string	$class_name	利用可能か調べるクラス名
	 * @return	bool	利用可能なクラス名の場合はtrue、そうでない場合はfalse
	 */
	public static function EnableName ($class_name) {
		return 1 !== preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $class_name);
	}

	/**
	 * 指定されたクラス名が、class, interface, traitのいずれかとして存在するか調べます。
	 *
	 * @param	string	$class_name	存在するか調べたいクラス名
	 * @return	bool	クラス名がclass, interface, traitのいずれかとして存在する場合はtrue、そうでない場合はfalse
	 */
	public static function ExistsClass ($class_path) {
		return class_exists($class_path, false) || interface_exists($class_path, false) || trait_exists($class_path, false);
	}

	/**
	 * クラスパスをリアルクラスファイルパスに変換します。
	 *
	 * @param	string	$class_path		変換したいクラスパス
	 * @param	string	$class_file_ext	クラスファイル拡張子
	 * @return	string	リアルクラスファイルパス
	 */
	public static function ClassPathToRealFilePath ($class_path, $class_file_ext = self::CLASS_FILE_EXT) {
		return static::$_VendorRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', $class_path) . $class_file_ext;
	}

	public static function UseOtherLoader () {
		static::$_UseOtherLoader = true;
	}

	/**
	 * ベンダールートディレクトリを割り出し、キャッシュします。
	 */
	protected static function InitRootDir () {
		static::$_VendorRootDir = static::ExtractVendorRootDir();
		static::$_SrcRootDir = dirname(static::$_VendorRootDir);
	}

	public static function GetVendorRootDir () {
		return static::$_VendorRootDir = static::ExtractVendorRootDir();
	}

	public static function GetSrcRootDir () {
		return static::$_SrcRootDir = dirname(static::$_VendorRootDir) . '/';
	}

	public static function GetClassPathList () {
		return static::$_ClassPathList;
	}

	public static function GetLoadedRealFilePathList () {
		return static::$_LoadedRealFilePathList;
	}

	public static function GetComposerLoader () {
		return static::$_ComposerLoader;
	}

	public static function SetComposerLoader ($composer_loader) {
		static::$_UseComposerLoader = true;
		static::$_ComposerLoader = $composer_loader;
	}
}
