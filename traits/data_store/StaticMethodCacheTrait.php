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

namespace ickx\fw2\traits\data_store;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\IniFile;
use ickx\fw2\mvc\app\constants\path\FilePath;

/**
 * 静的呼び出しメソッドの実効結果をキャッシュする特性です。。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait StaticMethodCacheTrait {
	/** @staticvar	array	キャッシュリスト */
	protected static $_StaticMethodCacheTrait_CacheList = [];

	/**
	 * キャッシュをクリアします。
	 *
	 * @param unknown $method_name
	 * @param unknown $options
	 * @param unknown $arguments
	 */
	public static function ClearStaticMethodCache ($method_name, $options = [], $arguments = []) {
		//もしもoptionsが文字列の場合、iniファイルによる設定と推定する
		!is_string($options) ?: $options = ['ini' => $options];

		//実装時設定と併用するため、optionでの指定も可能にする
		if (isset($options['ini'])) {
			$ini_path = $options['ini'];
			clearstatcache(true, $ini_path);
			if (!file_exists($ini_path)) {
				throw CoreException::RaiseSystemError('設定ファイルが見つかりません。ini path:%s', [$ini_path]);
			}
			$optinos = IniFile::GetConfig($ini_path, ['type', 'limit', 'cache_dir', 'cache_dir_permission']);
		}

		$options['clear'] = true;
		static::StaticMethodCache($method_name, $arguments, $options);
	}

	/**
	 * 静的メソッド単位での実行結果キャッシュを行います。
	 *
	 * @param unknown $method_name
	 * @param unknown $arguments
	 * @param unknown $options
	 */
	public static function StaticMethodCache ($method_name, $arguments = [], $options = []) {
		//======================================================
		//初期化
		//======================================================
		//実行中のクラス名の特定
		$current_class = static::class;

		//引数の正規化
		$arguments === null ? $arguments = [] : $arguments = (array) $arguments;

		//クラス名＋メソッド名によるキャッシュキー
		$static_cache_key = hash('sha256', serialize([$current_class, $method_name]));

		//引数によるキャッシュキー
		$arg_cache_key = hash('sha256', serialize($arguments));

		//もしもoptionsが文字列の場合、iniファイルによる設定と推定する
		!is_string($options) ?: $options = ['ini' => $options];

		//実装時設定と併用するため、optionでの指定も可能にする
		if (isset($options['ini'])) {
			$ini_path = $options['ini'];
			clearstatcache(true, $ini_path);
			if (!file_exists($ini_path)) {
				throw CoreException::RaiseSystemError('設定ファイルが見つかりません。ini path:%s', [$ini_path]);
			}
			$optinos = IniFile::GetConfig($ini_path, ['type', 'limit', 'cache_dir', 'cache_dir_permission']);
		}

		//キャッシュタイプの特定
		isset($options['type']) ?: $options['type'] = 'on_request';
		$type_key = $options['type'];

		//キャッシュライフタイムの確定
		$limit = isset($options['limit']) ? $options['limit'] : 30;
		$limit = (int) ($limit * 1000000);

		//キャッシュクリアモードの確定
		$clear = isset($options['clear']) && $options['clear'];

		//強制再取得モードの確定
		$obtain = isset($options['obtain']) && $options['obtain'];

		//======================================================
		//実処理
		//======================================================
		//キャッシュクリアが有効な場合、または強制再取得モードの場合、オンリクエストキャッシュを破棄する。（全タイプ共通）
		if (($clear || $obtain) && isset(static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key])) {
			unset(static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key]);
		}

		//強制再取得モードではなく、キャッシュが既に存在していて、キャッシュライフタイムが制限を超えていない場合、キャッシュを返す
		if ($obtain === false && isset(static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['create_microtime']) && static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['create_microtime'] + $limit > microtime(true)) {
			return static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['cache'];
		}

		//タイプ別の処理
		$cache_method_name = sprintf('StaticMethodCacheBy%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $type_key))));
		return static::$cache_method_name($current_class, $method_name, $arguments, $static_cache_key, $arg_cache_key, $type_key, $limit, $options);
	}


	public static function StaticMethodCacheByFile ($current_class, $method_name, $arguments, $static_cache_key, $arg_cache_key, $type_key, $limit, $options = []) {
		//キャッシュクリアモードの確定
		$clear = isset($options['clear']) && $options['clear'];

		//強制再取得モードの確定
		$obtain = isset($options['obtain']) && $options['obtain'];

		//キャッシュディレクトリの特定
		$cache_dir = isset($options['cache_dir']) ? $options['cache_dir'] : FilePath::STATIC_METHOD_CACHE_DIR();

		//キャッシュディレクトリ権限の特定
		$cache_dir_permission = isset($options['cache_dir_permission']) ? $options['cache_dir_permission'] : 0775;
		!is_string($cache_dir_permission) ?: $cache_dir_permission = octdec($cache_dir_permission);

		//キャッシュディレクトリ自体の実在、可用性検証
		clearstatcache(true, $cache_dir);
		if (!file_exists($cache_dir)) {
			$parent_dir = dirname($cache_dir);
			if (!is_executable($parent_dir)) {
				throw CoreException::RaiseSystemError('static methodキャッシュディレクトリの親ディレクトリを開けません。dir:%s', [$parent_dir]);
			}
			if (!is_writable($parent_dir)) {
				throw CoreException::RaiseSystemError('static methodキャッシュディレクトリの親ディレクトリに書き込めません。dir:%s', [$parent_dir]);
			}

			//キャッシュクリアモード時は何もしない。
			if ($clear) {
				return null;
			}

			//キャッシュディレクトリ自体が無い場合はここで作成する。
			mkdir($cache_dir, $cache_dir_permission);
		} else {
			if (!is_executable($cache_dir)) {
				throw CoreException::RaiseSystemError('static methodキャッシュディレクトリを開けません。dir:%s', [$cache_dir]);
			}
			if (!is_writable($cache_dir)) {
				throw CoreException::RaiseSystemError('static methodキャッシュディレクトリに書き込めません。dir:%s', [$cache_dir]);
			}
		}

		//クラス名＋メソッド名単位でのキャッシュディレクトリの実在、可用性検証
		$static_cache_dir = implode('/', [$cache_dir, $static_cache_key]);
		clearstatcache(true, $static_cache_dir);

		//キャッシュクリアフラグが有効な場合ここで終了
		if ($clear) {
			//存在する場合、クラス名＋メソッド名キャッシュディレクトリ以下を全て削除
			if (file_exists($static_cache_dir)) {
				foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($static_cache_dir, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $node) {
					if ($node->isDir()) {
						rmdir($node->getPathname());
					} else {
						unlink($node->getPathname());
					}
				}
				rmdir($static_cache_dir);
			}
			return null;
		}

		//引数キャッシュサブディレクトリ：そのまま使うとディレクトリを逼迫するため、4桁/4桁のサブディレクトリを作成する
		$arg_chunk_1 = substr($arg_cache_key, 0, 4);
		$arg_chunk_2 = substr($arg_cache_key, 4, 4);

		//引数キャッシュサブディレクトリの実在、可用性検証
		$arg_cache_dir = implode('/', [$static_cache_dir, $arg_chunk_1, $arg_chunk_2]);
		clearstatcache(true, $arg_cache_dir);

		//サブディレクトリがない場合は作成する。
		if (!file_exists($arg_cache_dir)) {
			mkdir($arg_cache_dir, $cache_dir_permission, true);
		}

		//キャッシュファイルパスの作成
		$cache_file_path = implode('/', [$arg_cache_dir, $arg_cache_key . '.cache']);
		clearstatcache(true, $cache_file_path);

		//キャッシュファイルからのキャッシュ取得
		if (file_exists($cache_file_path)) {
			if ($obtain) {
				//強制再取得モード時はキャッシュファイルをクリアする
				unlink($cache_file_path);
			} else {
				static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key] = unserialize(file_get_contents($cache_file_path));
				//キャッシュライフタイムが制限を超えていない場合、キャッシュを返す
				if (static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['create_microtime'] + $limit > microtime(true)) {
					return static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['cache'];
				}
			}
		}

		//キャッシュデータ構築
		static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key] = [
			'cache'				=> forward_static_call_array([$current_class, $method_name], $arguments),
			'arg'	 			=> $arguments,
			'create_microtime'	=> microtime(true),
		];

		//ファイルへのキャッシュ保存
		file_put_contents($cache_file_path, serialize(static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]));

		//======================================================
		//処理の終了
		//======================================================
		return static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['cache'];
	}

	public static function StaticMetdhoCacheByCallback ($current_class, $method_name, $arguments, $static_cache_key, $arg_cache_key, $type_key, $limit, $options = []) {
		$ret = $options['callback']($current_class, $method_name, $arguments, $static_cache_key, $arg_cache_key, $type_key, $limit, $options);
		if ($ret === null) {
			return null;
		}
		static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key] = $ret;
		return static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['cache'];
	}

	public static function StaticMethodCacheByOnRequest ($current_class, $method_name, $arguments, $static_cache_key, $arg_cache_key, $type_key, $limit, $options = []) {
		//キャッシュクリア時は何もせず終了する。
		if (isset($options['clear']) && $options['clear']) {
			return null;
		}

		//キャッシュデータ構築
		static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key] = [
			'cache'				=> forward_static_call_array([$current_class, $method_name], $arguments),
			'arg'	 			=> $arguments,
			'create_microtime'	=> microtime(true),
		];

		//======================================================
		//処理の終了
		//======================================================
		return static::$_StaticMethodCacheTrait_CacheList[$static_cache_key][$arg_cache_key][$type_key]['cache'];
	}
}
