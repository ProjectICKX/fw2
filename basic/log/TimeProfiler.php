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
 * @package		basic
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\basic\log;

/**
 * 名前付きで設定した区間の実行時間を計測します。
 */
class TimeProfiler {
	//==============================================
	// クラス定数
	//==============================================
	/**
	 * @var	array	計測時間用フォーマッタ：ナノ秒
	 * @const
	 */
	public const TIME_FORMAT_NANO	= [
		'%28s',
		'%0.22f'
	];

	/**
	 * @var	array	計測時間用フォーマッタ：マイクロ秒
	 * @const
	 */
	public const TIME_FORMAT_MICRO	= [
		'%12s',
		'%0.8f'
	];

	/**
	 * @var	array	計測時間用フォーマッタ：ミリ秒
	 * @const
	 */
	public const TIME_FORMAT_MILLI	= [
		'%8s',
		'%0.4f'
	];

	//==============================================
	// クラス変数
	//==============================================
	/**
	 * @var	array	マルチトンインスタンス保持配列
	 * @static
	 */
	protected static $instance		= [];

	/**
	 * @var	array	インスタンスごとの一括ログ
	 * @static
	 */
	protected static $instanceLog	= [];

	/**
	 * @var	array	インスタンスごとのリアルタイムコールバック（logメソッドが呼ばれる度にコールされる）
	 * @static
	 */
	protected static $logCallback	= [];

	//==============================================
	// プロパティ
	//==============================================
	/**
	 * @var	string	計測名
	 */
	protected $name			= null;

	/**
	 * @var	array	メソッド、関数の呼び出しツリー
	 */
	protected $stackTree	= [];

	/**
	 * @var	\ickx\fw2\basic\log\TimeProfiler	親要素となるプロファイラインスタンス
	 */
	protected $parentNode	= null;

	/**
	 * @var	float	ログ開始マイクロ秒
	 */
	protected $first		= null;

	/**
	 * @var	array	実行時間計測ログ
	 */
	protected $logs			= [];

	/**
	 * @var	float	最後に保存したマイクロ秒
	 */
	protected $last			= null;

	//==============================================
	// コンストラクタ
	//==============================================
	/**
	 * コンストラクタ
	 *
	 * @param	string	$name		計測名
	 * @param	array	$stack_tree	メソッド、関数の呼び出しツリー
	 */
	protected function __construct ($name, $stack_tree) {
		$this->name			= $name;
		$this->stackTree	= $stack_tree;
	}

	//==============================================
	// クラスメソッド
	//==============================================
	/**
	 * 静的メソッド名に計測名を指定して呼び出した場合のマジックメソッドです。
	 *
	 * @param	string	$name	計測名
	 * @return	\ickx\fw2\basic\log\TimeProfiler	プロファイラインスタンス
	 */
	public static function __callStatic ($name, $args) {
		//==============================================
		// スタックツリー構築
		//==============================================
		$befor_backtrace = null;
		foreach (debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT) as $backtrace) {
			if (!is_null($befor_backtrace)) {
				$stack_tree[]	= sprintf('%s(%s): %s', $befor_backtrace['file'], $befor_backtrace['line'] ?? 'NULL', isset($backtrace['class']) ? sprintf('%s%s%s', $backtrace['class'], $backtrace['type'], $backtrace['function']) : $backtrace['function']);
			}
			$befor_backtrace = $backtrace;
		}
		$stack_tree[]	= sprintf('%s(%s): main', $befor_backtrace['file'], $befor_backtrace['line'] ?? 'NULL');
		$node_name		= reset($stack_tree);
		$stack_tree		= array_reverse($stack_tree);

		$instance = static::$instance[$name] ?? static::$instance[$name] = new static($name, $stack_tree);

		$current_parent_tree	= array_slice($instance->stackTree, 0, -1);
		$parent_tree			= array_slice($stack_tree, 0, -1);

		//==============================================
		// スタックツリーの深さが変わらない場合はインスタンスを返して終わる
		//==============================================
		if ($current_parent_tree === $parent_tree) {
			return $instance;
		}

		//==============================================
		// スタックツリー変化時の処理
		//==============================================
		$same_parent		= count($instance->stackTree) === count($stack_tree);
		$stack_tree_node	= null;
		foreach ($current_parent_tree as $idx => $signature) {
			if ($signature !== ($stack_tree_node = $parent_tree[$idx] ?? false)) {
				$same_parent	= false;
				break;
			}
		}

		//----------------------------------------------
		// 単純に子要素として移動するだけの場合
		//----------------------------------------------
		if ($same_parent) {
			$instance							= new static($name, $stack_tree);
			static::$instance[$name]->logs[]	= ['child' => $instance];
			$instance->parentNode				= static::$instance[$name];
			static::$instance[$name]			= $instance;
			return $instance;
		}

		//----------------------------------------------
		// 複雑な遷移があった場合
		//----------------------------------------------
		$up_count = count($current_parent_tree) - $idx;
		for ($i = 0;$i < $up_count && !is_null($instance->parentNode);$i++) {
			$instance = $instance->parentNode;
		}
		$parentNode = $instance;

		//----------------------------------------------
		// 単純に親階層に移動しただけの場合
		//----------------------------------------------
		if (false === $stack_tree_node) {
			return static::$instance[$name] = $parentNode;
		}

		//----------------------------------------------
		// 複数段を飛ばした親要素への移動の場合
		//----------------------------------------------
		$instance					= new static($name, $stack_tree);
		$instance->parentNode		= $parentNode;
		$parentNode->logs[]			= ['child' => $instance];
		static::$instance[$name]	= $instance;

		return $instance;
	}

	/**
	 * 1行分のログデータを1行の文字列にして返します。
	 *
	 * @param	array	$log
	 * @param	\ickx\fw2\basic\log\TimeProfiler	プロファイラノード
	 * @return	string	ログデータ
	 */
	public static function format ($log, $instance, $point, $depth, $time_format = null, $format = null) {
		$time_format	= constant(static::class .'::'. ($time_format ?? 'TIME_FORMAT_MICRO'));
		$format			= $format ?? '[%s] log:%s.%-4s total:[split:'. $time_format[0] .', lap:'. $time_format[0] .'] part:[split:'. $time_format[0] .', rap:'. $time_format[0] .'] [%-5s:%s] comment:[%s]';

		return sprintf(
			$format,
			$instance->name(),
			date('Y-m-d H:i:s', (int) $log['log']),
			explode('.', $log['log'])[1] ?? 0,
			sprintf($time_format[1], $log['total_split']),
			sprintf($time_format[1], $log['total_lap']),
			sprintf($time_format[1], $log['split']),
			sprintf($time_format[1], $log['rap']),
			$point,
			implode("() => ", $instance->stackTree()) . '()',
			implode('::', (array) $log['comment'])
			);
	}

	public static function minimalFormat ($log, $instance, $point, $depth, $time_format = null, $format = null) {
		$time_format	= constant(static::class .'::'. ($time_format ?? 'TIME_FORMAT_MICRO'));
		$format			= $format ?? '[%s] [t_split:'. $time_format[0] .'] [split:'. $time_format[0] .', rap:'. $time_format[0] .'] %s [%-5s:%s] [%s]';

		$stack_trace = $instance->stackTree() ?? [];

		return sprintf(
			$format,
			$instance->name(),
			sprintf($time_format[1], $log['total_split']),
			sprintf($time_format[1], $log['split']),
			sprintf($time_format[1], $log['rap']),
			str_repeat(' ', $depth * 2),
			$point,
			end($stack_trace),
			implode('::', (array) $log['comment'])
		);
	}

	//==============================================
	// オブジェクトメソッド
	//==============================================
	/**
	 * 実行時間を計測します。
	 *
	 * @param	arary|string	$comment	コメント
	 */
	public function log ($comment = null) {
		$time	= microtime(true);

		if (!isset(static::$instanceLog[$this->name])) {
			static::$instanceLog[$this->name]	= [
				'first'	=> $time,
				'last'	=> $time,
			];
		}

		$this->first	?? $this->first = $time;
		$this->last		?? $this->last = $time;

		$log	= [
			'log'			=> $time,
			'rap'			=> $time - $this->last,
			'split' 		=> $time - $this->first,
			'total_lap'		=> $time - static::$instanceLog[$this->name]['last'],
			'total_split'	=> $time - static::$instanceLog[$this->name]['first'],
			'comment'		=> $comment,
		];

		$this->logs[]	= $log;
		$this->last		= $time;
		static::$instanceLog[$this->name]['last']	= $time;

		if (!is_null(static::$logCallback[$this->name] ?? null)) {
			static::$logCallback[$this->name]($log, $this);
		}

		return $this;
	}

	/**
	 * 取得した実行時間を配列としてダンプします。
	 *
	 * @param	array	$filter	指定したスタックツリーの箇所のみをダンプします。
	 * @return	array	取得した実行時間の配列
	 */
	public function dump ($filter = [], $depth = 0) {
		end($this->logs);
		$last_key = key($this->logs);
		reset($this->logs);

		$point = null;

		$result = [];
		foreach ($this->logs as $idx => $log) {
			if (isset($log['child'])) {
				$result = array_merge($result, $log['child']->dump($filter, $depth + 1));
			} else {
				if (empty($target) || $target === $this->stackTree) {
					$result[] = static::minimalFormat($log, $this, $last_key !== 0 && $last_key === $idx ? 'end' : (is_null($point) ? $point = 'start' : 'cp'), $depth);
				}
			}
		}
		return $result;
	}

	/**
	 * 計測スタックのルートノードを返します。
	 *
	 * @return	\ickx\fw2\basic\log\TimeProfiler	計測スタックのルートノード
	 */
	public function rootNode () {
		for ($instance = $this;!is_null($instance->parentNode);$instance = $instance->parentNode);
		return $instance;
	}

	/**
	 * ログ取得時に実行するコールバック処理を登録します。
	 *
	 * @param	callable	$callback	ログ取得時に実行するコールバック処理
	 */
	public function logCallback ($callback) {
		static::$logCallback[$this->name] = $callback;
	}

	/**
	 * 現在のインスタンスの計測名を返します。
	 *
	 * @return	string	計測名
	 */
	public function name () {
		return $this->name;
	}

	/**
	 * 現在のインスタンスのスタックツリーを返します。
	 *
	 * @return	array	スタックツリー
	 */
	public function stackTree () {
		return $this->stackTree;
	}

	/**
	 * 現在までに取得した内容をファイルに出力します。
	 *
	 * @param	string	$file_path	ファイルパス
	 * @return	bool	出力に成功した場合はtrue、そうでない場合はfalse
	 */
	public function outputDump ($file_path, $filter = []) {
		$group		= sprintf('[%s.%-4s] ', date('Y-m-d H:i:s', (int) $_SERVER['REQUEST_TIME_FLOAT']), explode('.', $_SERVER['REQUEST_TIME_FLOAT'])[1] ?? 0);
		$separator	= \PHP_EOL . $group;
		$instance	= $this->rootNode();
		return error_log($group . implode($separator, $instance->dump($filter)) . \PHP_EOL, 3, $file_path);
	}
}
