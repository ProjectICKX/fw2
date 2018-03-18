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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mvc;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\other\middleware\Middleware;

/**
 * フレームワークをまわすクラス
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Engine {
	use \ickx\fw2\traits\data_store\ClassVariableTrait;

	/** @var	int	最大実行時間：秒 */
	const MAX_EXECUTE_TIME = 29;

	/**
	 * フレームワーク実行フロントエンド
	 *
	 * @param	string	$url		フレームワーク起動URL
	 * @param	string	$namespace	アプリケーションネームスペース
	 * @param	string	$fw_class	起動元フレームワーククラス
	 * @param	string	$call_type	フレームワークの呼び出され方
	 * @return	boolean	実行後ステータス 正常終了：true 異常終了：false callタイプの場合：アクションの実行結果
	 */
	public static function Ignition ($url, $namespace, $fw_class, $call_type = null) {
		//------------------------------------------------------
		//実行時間計測：開始
		//------------------------------------------------------
		Stopwatch::Start();

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		//------------------------------------------------------
		//主処理の実行
		//------------------------------------------------------
		//var_dump対策のため、最上層でアウトプットバッファリングする
		//cliの場合はデフォルトで無効化とする
		Environment::IsCli() ?: OutputBuffer::Start(OutputBuffer::HANDLER_DEFAULT);

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		//主処理の実行
		try {
			$ret = static::_Dispatch($url, $namespace, $fw_class, $call_type);
		} catch (CoreException $core_e) { // フレームワークで例外をキャッチできる最終地点
			StaticLog::WriteErrorLog($core_e->getStatusMessage() ."\n". CoreException::ConvertToStringMultiLine($core_e));
			throw $core_e;
		} catch (\Exception $e) {
			StaticLog::WriteErrorLog($e->getMessage() ."\n". CoreException::ConvertToStringMultiLine($e));
			throw $e;
		}

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		//実行結果の出力
		Environment::IsCli() ?: OutputBuffer::EndFlush();

		//------------------------------------------------------
		//実行時間計測：終了
		//------------------------------------------------------
		Stopwatch::Stop();

		//------------------------------------------------------
		//処理の終了
		//------------------------------------------------------
		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		return $ret;
	}

	/**
	 * ミドルウェアを起動し、処理を遂行します。
	 */
	protected static function _Dispatch ($url, $namespace, $fw_class , $call_type) {
		return Middleware::init($fw_class::getMiddlewareList())->run([
			'url'		=> $url,
			'namespace'	=> $namespace,
			'call_type'	=> $call_type,
			'fw_class'	=> $fw_class,
		]);
	}

	/**
	 * Controller Classのクラスパスを取得します。
	 *
	 * @param	array	$route		router
	 * @param	string	$namespace	namespace
	 */
	public static function GetControllerClassPath ($route, $namespace) {
		$class_path = "\\". implode("\\", [$namespace, 'app', 'controllers', $route['controller'], Strings::ToUpperCamelCase($route['controller']) . 'Controller']);
		if (!class_exists($class_path)) {
			$class_path = "\\". implode("\\", [__NAMESPACE__, 'app', 'controllers', 'UnkownController']);
		}
		return $class_path;
	}
}
