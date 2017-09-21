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
	protected $callback		= null;

	/**
	 * @var	mixed			実行結果キャッシュ
	 */
	protected $result		= null;

	/**
	 * @var	array			オプション
	 */
	protected $options		= [];

	/**
	 * @var	array			デフォルト引数
	 */
	protected $defaultArgs	= [];

	/**
	 * @var	bool			値が既に存在しているかどうか
	 * 						null値に対応するために存在する
	 */
	protected $valueExist	= false;

	/**
	 * @var	bool			繰り返し最新値を取るかどうか
	 */
	protected $isRepeat		= false;

	/**
	 * @var	bool			次の一回のみ強制的に最新値を取る
	 */
	protected $flashRepeat	= false;

	/**
	 * @var	bool			永続化ストレージが有効かどうか
	 */
	protected $enableStrage	= false;

	/**
	 * コンストラクタ
	 *
	 * @param	string|array	遅延評価名
	 * @param	callable		遅延評価関数
	 * @param	array			オプション
	 *	[
	 *		'repeat'	=> bool		常に最新値を取るかどうか trueの場合は常に最新値を取る、falseまたは未定義の場合は値を取るのは最初の一回のみ
	 *		'storage'	=> callable	指定したコールバック関数から値を得られるかどうかを先に判断するようにする このオプションが有効な場合、storage > $this->result > $this->callbackの順に値を取得しにいく
	 *								指定できるコールバック関数は次の引数を受け付けられる必要がある
	 *								$name		マルチトンインスタンス名
	 *								$is_repeat	常に最新値を取るかどうかのフラグ
	 *								$callback	値取得用コールバック関数
	 *								...$args	値取得用コールバック関数用引数 $callback(...$args) として実行する
	 *								function (bool $is_repeat, callable $callback, ...$args);
	 *	]
	 */
	protected function __construct ($callback, $options = [], $default_args = []) {
		$this->callback		= $callback;
		$this->options		= $options;
		$this->defaultArgs	= $default_args;

		$this->isRepeat		= $this->options['repeat'] ?? false;
		$this->enableStrage	= isset($this->options['storage']) && is_callable($this->options['storage']);
	}

	/**
	 * 次の一回の実行時のみ、強制的に最新値を取るようにします。
	 */
	public function repeat () {
		$this->flashRepeat = true;
		return $this;
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
		if (!$this->valueExist || $this->isRepeat || $this->flashRepeat) {
			if (!empty($this->defaultArgs)) {
				$args = array_merge($this->defaultArgs, $args);
			}

			if ($this->enableStrage) {
				$this->result	= $this->options['storage']($this->multitonName, $this->isRepeat, $this->callback, ...$args);
			} else {
				$callback = $this->callback;
				$this->result	= $callback(...$args);
			}
			$this->flashRepeat = false;
			$this->valueExist = true;
		}
		return $this->result;
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
		if (!$this->valueExist || $this->isRepeat || $this->flashRepeat) {
			if (!empty($this->defaultArgs)) {
				$args = array_merge($this->defaultArgs, $args);
			}

			if ($this->enableStrage) {
				$this->result	= $this->options['storage']($this->multitonName, $this->isRepeat, $this->callback, ...$args);
			} else {
				$callback = $this->callback;
				$this->result	= $callback(...$args);
			}
			$this->flashRepeat = false;
			$this->valueExist = true;
		}
		return $this->result;
	}
}
