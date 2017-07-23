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
	 * @param	string	$load_class_path	読み込むクラスパス
	 * @param	string	$class_file_ext		クラスファイル拡張子
	 * @throws	\Exception
	 */
	public static function AutoLoad ($load_class_path, $class_file_ext = self::CLASS_FILE_EXT) {
		//クラス名の割り出し
		$class_path = $load_class_path;
		$class_name = explode("\\", $class_path);
		$class_name = array_pop($class_name);

		//クラスパスの確定：パスオーバーライド対象のクラスのみ上書き
		$alias_class = false;

//@TODO path helper
//@TODO path array
		if (isset(static::$_ClassPathList[$class_path])) {
			$class_path = static::$_ClassPathList[$class_path];
			$alias_class = true;
		} else if (isset(static::$_ClassPathList[$class_name])) {
			$class_path = static::$_ClassPathList[$class_name];
			$alias_class = true;
		}

		$real_file_path = null;
		if (static::$_UseComposerLoader) {
			$real_file_path = ($real_file_path = static::$_ComposerLoader->findFile($load_class_path)) !== false ? realpath($real_file_path ) : $real_file_path;
			if (!$real_file_path) {
				$real_file_path = static::$_ComposerLoader->findFile($class_path);
			}
		}

		//クラスパスをリアルファイルパスに変換
		//パフォーマンスチューニング Ref) static::ClassPathToRealFilePath($class_path, $class_file_ext)
		if ($real_file_path === false) {
			$real_file_path  = static::$_VendorRootDir . str_replace(static::NAMESPACE_SEPARATOR, '/', ltrim($class_path, static::NAMESPACE_SEPARATOR)) . $class_file_ext;
		}

		//ファイルがロードされていない場合のみ実行
		if (isset(static::$_LoadedRealFilePathList[$real_file_path])) {
			//クラスエイリアスを設定する
			if ($alias_class) {
				class_alias($class_path, $load_class_path, true);
			}
			return true;
		}

		//クラスファイルが存在するか確認
		if (!static::$_UseComposerLoader && !file_exists($real_file_path)) {
			throw new \Exception(sprintf('class file not found:%s, file path:%s', $class_path, $real_file_path));
		}

		//クラスファイルのロード
		//チェックは全て完了しているので、もっとも高速なincludeを使用
		if ($class_path !== __CLASS__ && !class_exists($class_path, false) && !interface_exists($class_path, false) && !trait_exists($class_path, false)) {
			include $real_file_path;
		}

		//読み込み済みリアルファイルパスにフラグを立てる
		static::$_LoadedRealFilePathList[$real_file_path] = true;

		//クラスエイリアスを設定する
		if ($alias_class && $class_path !== $load_class_path) {
			if (!class_exists($load_class_path)) {
				class_alias($class_path, $load_class_path, true);
			}
		}

		//クラスが存在している事を確認
		//パフォーマンスチューニング Ref) !static::ExistsClass($class_path)
		if (!class_exists($class_path, true) && !interface_exists($class_path, true) && !trait_exists($class_path, true)) {
			if (!static::AutoLoad($class_path, $class_file_ext)) {
				throw new \Exception('class not found:'. $class_path .' file path:'. $real_file_path);
			}
		}

		return true;
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
		return static::$_SrcRootDir = dirname(static::$_VendorRootDir);
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
