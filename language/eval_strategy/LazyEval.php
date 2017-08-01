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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\language\eval_strategy;

/**
 * 遅延評価を提供します。
 *
 * @category	Flywheel2
 * @package		language
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class LazyEval {
	use \ickx\fw2\traits\singletons\Multiton;

	/**
	 * @var	callable		遅延評価関数
	 */
	protected $callback	= null;

	/**
	 * @var	mixed			実行結果キャッシュ
	 */
	protected $result	= null;

	/**
	 * @var	array			オプション
	 */
	protected $options	= [];

	/**
	 * コンストラクタ
	 *
	 * @param	string|array	遅延評価名
	 * @param	callable		遅延評価関数
	 * @param	array			オプション
	 */
	protected function __construct ($callback, $options = []) {
		$this->callback	= $callback;
		$this->options	= $options;
	}

	/**
	 * 中間状態を返します。
	 *
	 * @return	\ickx\fw2\language\eval_strategy\LazyEval	現在のインスタンス
	 */
	public function promise () {
		return $this;
	}

	/**
	 * 計算実体を返します。
	 *
	 * @return	callable	遅延評価関数
	 */
	public function thunk () {
		return $this->callback;
	}

	/**
	 * 評価を強制します。
	 *
	 * コールバック関数を実行し、その結果を返します。
	 *
	 * @param	array	...$args	callbackへ渡す引数
	 * @return	mixed	評価結果。
	 */
	public function force (...$args) {
		$callback = $this->callback;
		return $this->options['repeat'] ?? false ? ($this->result ?? $this->result = $callback(...$args)) : $callback(...$args);
	}

	/**
	 * 評価を強制します。
	 *
	 * コールバック関数を実行し、その結果を返します。
	 *
	 * @param	array	...$args	callbackへ渡す引数
	 * @return	mixed	評価結果。
	 */
	public function __invoke (...$args) {
		$callback = $this->callback;
		return $this->options['repeat'] ?? false ? ($this->result ?? $this->result = $callback(...$args)) : $callback(...$args);
	}
}
