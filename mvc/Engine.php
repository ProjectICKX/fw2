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

namespace ickx\fw2\mvc;

use ickx\fw2\core\exception\CoreException;

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
	 * @param	string	$call_type	フレームワークの呼び出され方
	 * @return	boolean	実行後ステータス 正常終了：true 異常終了：false callタイプの場合：アクションの実行結果
	 */
	public static function Ignition ($url, $namespace, $call_type = null) {
		//------------------------------------------------------
		//実行時間計測：開始
		//------------------------------------------------------
		Stopwatch::Start();

		//------------------------------------------------------
		//主処理の実行
		//------------------------------------------------------
		//var_dump対策のため、最上層でアウトプットバッファリングする
		//cliの場合はデフォルトで無効化とする
		Environment::IsCli() ?: OutputBuffer::Start(OutputBuffer::HANDLER_DEFAULT);

		//主処理の実行
		try {
			$ret = static::_Dispatch($url, $namespace, $call_type);
		} catch (CoreException $core_e) { // フレームワークで例外をキャッチできる最終地点
			StaticLog::WriteErrorLog($core_e->getStatusMessage() ."\n". CoreException::ConvertToStringMultiLine($core_e));
			throw $core_e;
		} catch (\Exception $e) {
			StaticLog::WriteErrorLog($e->getMessage() ."\n". CoreException::ConvertToStringMultiLine($e));
			throw $e;
		}

		//実行結果の出力
		Environment::IsCli() ?: OutputBuffer::EndFlush();

		//------------------------------------------------------
		//実行時間計測：終了
		//------------------------------------------------------
		Stopwatch::Stop();
		$diff = Stopwatch::Diff();

		//------------------------------------------------------
		//実行時間計測：ログ出力
		//------------------------------------------------------
		//ログ出力文字列の生成
		$log = sprintf("Execution time=%s, URL=%s", $diff, $url);

		//最大許容実行時間を越えていた場合はアラートを出す
		if ($diff > static::MAX_EXECUTE_TIME) {
			//ログ出力
			StaticLog::WriteErrorLog('SLOW DOWN!!:'. $log);
		}

		//実行時間ログの出力
		StaticLog::WriteLog('timer', $log);

		//------------------------------------------------------
		//処理の終了
		//------------------------------------------------------
		return $ret;
	}

	/**
	 * フレームワーク主処理
	 *
	 * @param	string	$url		フレームワーク起動URL
	 * @param	string	$namespace	アプリケーションネームスペース
	 * @return	boolean	実行後ステータス 正常終了：true 異常終了：false
	 */
	protected static function _Dispatch ($url, $namespace, $call_type = null) {
		//------------------------------------------------------
		//実行対象URLの解析
		//------------------------------------------------------
		//URLの解析
		$route = Router::Find($url);

		//------------------------------------------------------
		//実行キューの初期化
		//------------------------------------------------------
		//初期起動コントローラ、アクションの設定
		Queue::Init($route);

		//------------------------------------------------------
		//主処理開始
		//------------------------------------------------------
		foreach (Queue::GetIterator() as $route) {
			$controller_class = static::GetControllerClassPath($route, $namespace);
			$controller_instance = $controller_class::Execute($route);
			switch ($controller_instance->nextRule) {
				case IController::NEXT_REDIRECT:
					if (!$controller_instance->isRedirect()) {
						CoreException::RaiseSystemError('リダイレクトが設定されていません。');
					}

					$url = $controller_instance->nextUrl;
					if (preg_match("/^https?:\/\//", $url) !== 1) {
						$url = sprintf(
							'%s://%s/%s',
							strtolower(\ickx\fw2\core\net\http\Request::GetCurrnetProtocol()),
							\ickx\fw2\core\net\http\Request::GetHeader('Host'),
							$url
						);
					}
					$url = explode('://', $url, 2);
					$url = $url[0] .'://'. preg_replace("/\/+/", '/', $url[1]);
					header("Location: ". $url);
					exit;
				case IController::NEXT_FORWARD:
					Queue::Add(Router::Find($controller_instance->nextUrl));

					Flywheel::SetCurrnetUrl($controller_instance->nextUrl);
					continue;
				case IController::NEXT_RENDERING:
					if (!in_array($call_type, ['call', 'caller'])) {
						return $controller_instance->rendering();
					}
					break;
				case IController::NEXT_CALLER:
					return $controller_instance->render;
				default:
					throw CoreException::RaiseSystemError('適切な次処理が設定されていません。next:%s', [$controller_instance->next]);
					break;
			}
		}
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
