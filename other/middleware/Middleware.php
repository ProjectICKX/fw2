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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\middleware;

/**
 * ミドルウェアを実現するための基礎的な機能を提供します。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		1.0.0
 */
class Middleware {
	/**
	 * @var	array	実行対象のミドルウェアのリスト
	 */
	protected $middlewareList	= [];

	/**
	 * @var	integer	現在保持しているミドルウェアリスト内のポインタ位置
	 */
	protected $index			= 0;

	/**
	 * コンストラクタ
	 *
	 * @param	array		$middleware_list	実行対象のミドルウェアのリスト
	 * @return	Middleware	自身のインスタンス
	 */
	protected function __construct ($middleware_list) {
		$this->middlewareList	= $middleware_list;
		$this->index			= 0;
	}

	/**
	 * ミドルウェアを初期化し返します。
	 *
	 * @param	array		$middleware_list	実行対象のミドルウェアのリスト
	 * @return	Middleware	自身のインスタンス
	 */
	public static function init ($middleware_list) {
		return new static($middleware_list);
	}

	/**
	 * ミドルウェアを実行します。
	 *
	 * @param	array	$options	実行時オプション
	 * @return	array	ミドルウェアの実行結果
	 */
	public function run ($request = [], $response = []) {
		$request	= MiddlewareRequest::init($request);
		$response	= MiddlewareResponse::init($response);
		$result		= $this($request, $response);
		return [
			'request'	=> $request,
			'response'	=> $response,
			'result'	=> $result,
		];
	}

	/**
	 * invoke
	 *
	 * @param	MiddlewareRequest	$request
	 * @param	MiddlewareResponse	$response
	 * @param	Middleware			$next
	 * @return	mixed				ミドルウェアの実行結果
	 */
	public function __invoke($request, $response, $next = null) {
		if (isset($this->middlewareList[$this->index])) {
			$skip_middleware_class = $response->skipMiddlewareClass() ?? [];
			for (
				$middleware_class = $this->middlewareList[$this->index++];
				in_array($middleware_class, $skip_middleware_class, true);
				$middleware_class = $this->middlewareList[$this->index++]
			);
			return $middleware_class::init()($request, $response, $next ?? $this);
		}
		return $response;
	}
}
