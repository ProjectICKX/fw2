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

namespace ickx\fw2\mvc\app\middleware\dispatch;

use ickx\fw2\other\middleware\abstracts\AbstractsMiddleware;
use ickx\fw2\mvc\Engine;

/**
 * アクションを実行するミドルウェアです。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DispatchMiddleware extends AbstractsMiddleware {
	/**
	 * invoke
	 *
	 * @param	MiddlewareRequest	$request
	 * @param	MiddlewareResponse	$response
	 * @param	Middleware			$next
	 * @return	mixed				ミドルウェアの実行結果
	 */
	public function __invoke ($request, $response, $next) {
		return $this->dispatch($request, $response);
	}

	/**
	 * フレームワーク主処理
	 *
	 * @param	string	$url		フレームワーク起動URL
	 * @param	string	$namespace	アプリケーションネームスペース
	 * @return	boolean	実行後ステータス 正常終了：true 異常終了：false
	 */
	protected function dispatch ($request, $response) {
		$url		= $request->forward ?? $request->url ?? null;
		$namespace	= $request->namespace ?? null;
		$call_type	= $request->call_type ?? null;

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		if (isset($request->redirect)) {
			return $this->redirect($request->redirect);
		}

		//------------------------------------------------------
		//実行対象URLの解析
		//------------------------------------------------------
		//URLの解析
		$route = $request->route ?? Router::Find($url);

		//------------------------------------------------------
		//実行キューの初期化
		//------------------------------------------------------
		//初期起動コントローラ、アクションの設定
		Queue::Init($route);

		//------------------------------------------------------
		//主処理開始
		//------------------------------------------------------
		foreach (Queue::GetIterator() as $route) {
			if (isset($route['path_through'])) {
				$real_path = realpath($route['path_through']);
				//@TODO SRC以下以外ははじくようにする
				if (substr($real_path, 0, 5) !== '/etc/' && file_exists($real_path)) {
					readfile($route['path_through']);
				}
				break;
			}

			$controller_class = Engine::GetControllerClassPath($route, $namespace);
			$controller_instance = $controller_class::Execute($route);
			switch ($controller_instance->nextRule) {
				case IController::NEXT_REDIRECT:
					if (!$controller_instance->isRedirect()) {
						CoreException::RaiseSystemError('リダイレクトが設定されていません。');
					}

					return $this->redirect($controller_instance->nextUrl);
				case IController::NEXT_FORWARD:
					Queue::Add(Router::Find($controller_instance->nextUrl));

					Flywheel::SetCurrnetUrl($controller_instance->nextUrl);
					continue;
				case IController::NEXT_RENDERING:
					if (!in_array($call_type, ['call', 'caller'], true)) {
						assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());
						$ret = $controller_instance->rendering();
						assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('rendering'));
					}
					break;
				case IController::NEXT_CALLER:
					$ret = $controller_instance->render;
				default:
					throw CoreException::RaiseSystemError('適切な次処理が設定されていません。next:%s', [$controller_instance->next]);
					break;
			}
		}
		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log(''));

		return $ret;
	}

	/**
	 * リダイレクト処理実体
	 *
	 * @param	string	$url	リダイレクト先URL
	 */
	protected function redirect ($url) {
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
	}
}

