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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\basic\log;

/**
 * Measure
 */
class Measure {
	//==============================================
	// クラス定数
	//==============================================
	/**
	 * @const   string  計測対象名のデフォルト値
	 */
	public const DEFAULT_NAME   = 'default';

	/**
	 * @const   string  レンダーのデフォルト値
	 */
	public const DEFAULT_RENDER_NAME   = 'html';

	/**
	 * @const   int     デフォルトの実行回数
	 */
	public const DEFAULT_BATCH = 1000;

	/**
	 * @const   int     デフォルトのラウンド数
	 */
	public const DEFAULT_ROUND = 3;

	/**
	 * @const   int     デフォルトの小数点以下丸め桁数
	 */
	public const DEFAULT_PRECISION = 4;

	/**
	 * @const   array   デフォルトの計測名エイリアス
	 *                  日本語化のために存在する
	 */
	public const DEFAULT_MEAS_TARGET_ALIAS = [
		'round'     => '回数',
		'batch'     => 'バッチ',
		'count'     => '件数',
		'sum'       => '総和',
		'average'   => '平均',
		'min'       => '最小',
		'median'    => '中央',
		'max'       => '最大',
		'mode'      => '頻出',
		'precision' => '丸め桁数',
	];

	protected const IGNORE_PRECISION_TARGET = [
		'round',
		'batch',
		'count',
		'precision',
	];

	public const DEFAULT_HTML_ENTITIES_FLAGS = \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_DISALLOWED | \ENT_HTML5;

	public const DEFAULT_ENCODING = 'UTF-8';

	public const CALC_TOGETHER_TARGET   = '1';
	public const CALC_TOGETHER_NAME     = '2';

	//==============================================
	// クラスプロパティ
	//==============================================
	/**
	 * @var    array   インスタンスキャッシュ
	 * @static
	 */
	protected static $instance  = [];

	/**
	 * @var callable    全体表示用レンダー処理
	 * @static
	 */
	protected static $renders   = [];

	/**
	 * @var array       リフレクションキャッシュ
	 * @static
	 */
	protected static $reflectionCache   = [];

	/**
	 * @var array       全体計算結果キャッシュ
	 * @static
	 */
	protected static $calcAllCache      = [];

	protected static $title = '';

	//==============================================
	// インスタンスプロパティ
	//==============================================
	/**
	 * @var string  計測対象名
	 */
	protected $name     = null;

	/**
	 * @var string  計測対象コメント
	 */
	protected $comment  = '';

	/**
	 * @var float   実行時間計測開始時間（マイクロ秒）
	 */
	protected $start    = null;

	/**
	 * @var array   実行時間計測ログ
	 */
	protected $logs     = [];

	/**
	 * @var array   計算結果キャッシュ
	 */
	protected $calcCache    = [];

	/**
	 * @var \Closure    実行時間計測対象クロージャー
	 */
	protected $closure  = null;

	/**
	 * @var callable    個別表示用レンダー処理
	 */
	protected $render   = null;

	/**
	 * @var array       メソッド名マップ
	 */
	protected $methodNameMap = null;

	//==============================================
	// コンストラクタ
	//==============================================
	/**
	 * コンストラクタ
	 *
	 * @param   string  $name       計測対象名
	 */
	protected function __construct ($name)
	{
		$this->name     = $name;
		$this->closure(function () {});
		$this->methodNameMap = static::$reflectionCache[static::class]['dynamic'];
	}

	//==============================================
	// マルチトンインスタンス生成
	//==============================================
	/**
	 * 計測器を初期化します。
	 *
	 * @param   string      $name       計測対象名
	 * @return  TimeMeas    計測対象名に紐づく計測器
	 * @static
	 */
	protected static function getInstance($name = null)
	{
		$name = $name ?? static::DEFAULT_NAME;
		$class_path = static::class;
		return static::$instance[$name] ?? static::$instance[$name] = new $class_path($name);
	}

	//==============================================
	// マジックメソッド
	//==============================================
	/**
	 * static method call interceptor
	 *
	 * @param   string  $method_name    メソッド名
	 * @param   array   $argments       呼び出し引数
	 * @return  mixed   実行したメソッドの返り値
	 */
	public static function __callStatic($method_name, $argments)
	{
		if (!isset(static::$reflectionCache[static::class])) {
			foreach ((new \ReflectionClass(static::class))->getMethods() as $method){
				static::$reflectionCache[static::class][$method->isStatic() ? 'static' : 'dynamic'][$method->name] = true;
				static::$reflectionCache[static::class]['length'][$method->name] = $method->getNumberOfParameters();
			}
		}

		if (isset(static::$reflectionCache[static::class]['static'][$method_name])) {
			return static::$method_name(...$argments);
		}

		if (isset(static::$reflectionCache[static::class]['dynamic'][$method_name])) {
			return static::get(static::$reflectionCache[static::class]['length'][$method_name] < count($argments) ? array_slice($argments, -1)[0] : null)->$method_name(...$argments);
		}

		return static::get($method_name);
	}

	/**
	 * method call interceptor
	 *
	 * @param   string  $method_name    メソッド名
	 * @param   array   $argments       呼び出し引数
	 * @return  mixed   実行したメソッドの返り値
	 */
	public function __call($method_name, $argments)
	{
		if (!isset($this->methodNameMap[$method_name])) {
			throw new \ErrorException(sprintf('Method \'%s\' not found', $method_name), 0, \E_ERROR, __FILE__, __LINE__);
		}
		return  $this->$method_name(...$argments);
	}

	//==============================================
	// クラスメソッド
	//==============================================
	/**
	 * 名前に紐づく計測器を返します。
	 *
	 * @param   string      $name       計測対象名
	 * @return  TimeMeas    計測対象名に紐づく計測器
	 */
	protected static function get($name = null)
	{
		return static::getInstance($name);
	}

	/**
	 * 保持する計測器を全て返します。
	 *
	 * @return  array   保持する全ての計測器
	 * @static
	 */
	protected static function instance()
	{
		return static::$instance;
	}

	/**
	 * 保持する計測器全てで計測を行います。
	 *
	 * @param   int         $batch      1回分の実行回数
	 * @param   int         $round      平均を取るためのラウンド数
	 * @param   \Closure    $closure    クロージャー この引数が指定されている場合、インスタンスに設定されているクロージャより優先して使用される
	 * @return  float       全ての計測器の総実行時間
	 */
	protected static function execAll($batch = null, $round = null, $closure = null, $argments = [])
	{
		$batch = $batch ?? static::DEFAULT_BATCH;
		$round = $round ?? static::DEFAULT_ROUND;

		$start = microtime(true);
		foreach (static::instance() as $instance) {
			$instance->exec($batch, $round, $closure, $argments);
		}
		return microtime(true) - $start;
	}

	/**
	 * 現時点までの実行時間計測記録を元に全ての計測器で各種値を算出し、返します。
	 *
	 * @param   int     $precision  丸め桁数
	 * @return  array   実行時間計測の計算結果
	 */
	protected static function calcAll($precision = null, $together = null)
	{
		$precision = $precision ?? static::DEFAULT_PRECISION;
		$result = [];

		if ($together === static::CALC_TOGETHER_TARGET) {
			foreach (static::instance() as $name => $instance) {
				foreach ($instance->calc($precision) as $key => $value) {
					$result[$key][$name] = $value;
				}
			}
		} else {
			foreach (static::instance() as $name => $instance) {
				$result[$name] = $instance->calc($precision);
			}
		}

		static::$calcAllCache = $result;

		return static::$calcAllCache;
	}

	protected static function e($string)
	{
		return htmlentities($string, static::DEFAULT_HTML_ENTITIES_FLAGS, static::DEFAULT_ENCODING);
	}

	protected static function c($key, $default = null)
	{
		return static::DEFAULT_MEAS_TARGET_ALIAS[$key] ?? ($default ?? $key);
	}

	protected static function ec($key, $default = null)
	{
		return static::e(static::c($key, $default));
	}

	/**
	 * 描画用のレンダー処理を登録します。
	 *
	 * @param   callable|string $render レンダー処理
	 * @return  callable|string
	 */
	protected static function renders()
	{
		$name = $name ?? static::DEFAULT_RENDER_NAME;
		if (empty(static::$renders)) {
			static::initDefaultRenders();
		}
		return static::$renders;
	}

	protected static function initDefaultRenders()
	{
		$number_format = function ($value, $row) {
			return number_format($value, $row['precision']);
		};

		$ignore_target  = [
			'round',
			'batch',
			'count',
			'precision',
		];

		$ignore_filter = function ($key) use ($ignore_target) {
			return in_array($key, $ignore_target, true);
		};

		static::$renders = [
			'html'  => function ($result = null) use ($number_format, $ignore_target, $ignore_filter) {
				$result = $result ?? static::$calcAllCache;

				$html = [];

				$html[] = '<table border="1">';
				$html[] = '  <thead>';

				$tmp = current($result);
				foreach ($ignore_target as $target) {
					$html[] = '    <tr>';
					$html[] = sprintf('      <th>%s</th>', static::ec($target));
					$html[] = sprintf('      <td align="right">%s</td>', $tmp[$target]);
					$html[] = '    </tr>';
				}

				$html[] = '  </thead>';
				$html[] = '</table>';


				$html[] = '<table border="1">';
				$html[] = '  <thead>';
				$html[] = '    <tr>';
				$html[] = '      <th></th>';

				foreach (array_keys(current($result)) as $target) {
					if (in_array($target, $ignore_target, true)) {
						continue;
					}
					$html[] = sprintf('      <th>%s</th>', static::ec($target));
				}

				$html[] = '    </tr>';

				$html[] = '  </thead>';

				$html[] = '  <tbody>';
				foreach ($result as $name => $row) {
					$html[] = '    <tr>';
					$html[] = sprintf('      <td>%s</td>', static::e($name));
					foreach ($row as $key => $value) {
						if (in_array($key, $ignore_target, true)) {
							continue;
						}
						$html[] = sprintf('      <td align="right">%s</td>', static::e(in_array($key, static::IGNORE_PRECISION_TARGET, true) ? $value : $number_format($value, $row)));
					}
					$html[] = '    </tr>';
				}
				$html[] = '  </tbody>';

				$html[] = '</table>';

				return implode("\n", $html);
			},
			'csv'  => function ($result = null) use ($number_format, $ignore_target, $ignore_filter) {
				$result = $result ?? static::$calcAllCache;

				$memory = 'php://memory';
				$mp = fopen($memory, 'bw+');
				fputcsv($mp, array_map([static::class, 'ec'], array_merge([''], array_keys(current($result)))));
				foreach ($result as $name => $row) {
					fputcsv($mp, array_merge([$name], array_map(function ($value, $key) use ($number_format, $row) {return in_array($key, static::IGNORE_PRECISION_TARGET, true) ? $value : $number_format($value, $row);}, $row, array_keys($row))));
				}
				rewind($mp);
				return stream_get_contents($mp);
			},
			'tsv'  => function ($result = null) use ($number_format, $ignore_target, $ignore_filter) {
				$result = $result ?? static::$calcAllCache;

				$memory = 'php://memory';
				$mp = fopen($memory, 'bw+');
				fputcsv($mp, array_map([static::class, 'ec'], array_merge([''], array_keys(current($result)))), "\t");
				foreach ($result as $name => $row) {
					fputcsv($mp, array_merge([$name], array_map(function ($value, $key) use ($number_format, $row) {return in_array($key, static::IGNORE_PRECISION_TARGET, true) ? $value : $number_format($value, $row);}, $row, array_keys($row))), "\t");
				}
				rewind($mp);
				return stream_get_contents($mp);
			},
			'qiita'  => function ($result = null) use ($number_format, $ignore_target, $ignore_filter) {
				$result = $result ?? static::$calcAllCache;

				$row_conv = function ($array, $e = null) {
					return sprintf('| %s |', implode(' | ', is_null($e) ? $array : array_map($e, $array)));
				};

				$filterd_current = array_filter($row, current($result), \ARRAY_FILTER_USE_KEY);

				$qiita = [];
				$qiita[] = $row_conv(array_merge([''], array_keys($filterd_current)), [static::class, 'ec']);
				$qiita[] = $row_conv(array_merge([':---'], array_fill(0, count($filterd_current), '---:')));
				foreach ($result as $name => $row) {
					$row = array_filter($row, $ignore_filter, \ARRAY_FILTER_USE_KEY);
					$qiita[] = $row_conv(array_merge([$name], array_map(function ($value, $key) use ($number_format, $row) {return in_array($key, static::IGNORE_PRECISION_TARGET, true) ? $value : $number_format($value, $row);}, $row, array_keys($row))));
				}

				return implode("\n", $qiita);
			}
		];
	}

	//==============================================
	// インスタンスメソッド
	//==============================================
	/**
	 * 登録されているクロージャを返します。
	 *
	 * 引数がある場合、クロージャを差し替え、このインスタンスを返します。
	 *
	 * @param   \Closure    $closure    実行時間計測対象
	 * @return  mixed       参照時：クロージャ、登録時：このインスタンス
	 */
	protected function closure($closure = null)
	{
		if (is_null($closure)) {
			return $this->closure;
		}

		$this->closure = $closure instanceof \Closure ? $closure : \Closure::fromCallable($closure);
		return $this;
	}

	/**
	 * 登録されているログを返します。
	 *
	 * 引数がある場合、ログを差し替え、このインスタンスを返します。
	 *
	 * @param   array       $logs   置き換え対象のログ
	 * @return  mixed       参照時：ログ、登録時：このインスタンス
	 */
	protected function logs($logs = null)
	{
		if (is_null($logs)) {
			return $this->logs;
		}

		$this->logs = $logs;
		return $this;
	}

	/**
	 * 実行時間計測を開始します。
	 *
	 * @return  float   実行計測開始時間（マイクロ秒）
	 * @see     http://php.net/manual/ja/function.microtime.php
	 */
	protected function start()
	{
		return $this->start = microtime(true);
	}

	/**
	 * 実行時間を記録します。
	 *
	 * 同時に実行時間を内部でスタックします。
	 *
	 * @param   string  $name   ログタイミング名
	 * @return  float   実行時間（マイクロ秒）
	 * @see     TimeMeas::start()
	 */
	protected function log($name = null)
	{
		if (is_null($this->start)) {
			throw new \Exception('startメソッドがコールされていません。');
		}
		$log = microtime(true) - $this->start;
		$this->calcCache = [];

		if (is_null($name)) {
			$this->logs[] = $log;
		} else {
			$this->logs[$name] = $log;
		}

		return $log;
	}

	/**
	 * 実行時間計測を終了します。
	 *
	 * @return  float   実行時間（マイクロ秒）
	 * @see     TimeMeas::start()
	 * @see     TimeMeas::log()
	 */
	protected function stop()
	{
		$log = $this->log();
		$this->start = null;
		return $log;
	}

	/**
	 * 現在の総実行時間を返します。
	 *
	 * @return  float   総実行時間（マイクロ秒）
	 */
	protected function sum()
	{
		return $this->calcCache['sum'] ?? $this->calcCache['sum'] = array_sum($this->logs);
	}

	/**
	 * 現在のログ件数を返します。
	 *
	 * @return  int 現在のログ件数
	 */
	protected function count()
	{
		return $this->calcCache['count'] ?? $this->calcCache['count'] = count($this->logs);
	}

	/**
	 * 現在の実行時間平均を返します。
	 *
	 * @return  float   実行時間平均（マイクロ秒）
	 * @see     TimeMeas::sum()
	 * @see     TimeMeas::count()
	 */
	protected function average()
	{
		return $this->calcCache['average'] ?? $this->calcCache['average'] = $this->sum() / $this->count();
	}

	/**
	 * 現在の最小実行時間を返します。
	 *
	 * @return  float   最小実行時間（マイクロ秒）
	 */
	protected function min()
	{
		return $this->calcCache['min'] ?? $this->calcCache['min'] = min($this->logs);
	}

	/**
	 * 現在の最大実行時間を返します。
	 *
	 * @return  float   最大実行時間（マイクロ秒）
	 */
	protected function max()
	{
		return $this->calcCache['max'] ?? $this->calcCache['max'] = min($this->logs);
	}

	/**
	 * 現在の実行時間の中央値を返します。
	 *
	 * @return  float   実行時間の中央値（マイクロ秒）
	 */
	protected function median()
	{
		if (!isset($this->calcCache['median'] )) {
			$logs = $this->logs;
			sort($logs);
			$count      = $this->count();
			$adjuster   = $count % 2 === 0 ? 2 : 1;
			$this->calcCache['median'] = array_sum(array_slice($logs, ceil($count / 2) - 1, $adjuster)) / $adjuster;
		}
		return $this->calcCache['median'] ;
	}

	/**
	 * 現在の実行時間の頻出値を返します。
	 *
	 * @param   int $precision  丸め桁数
	 * @return  float   実行時間の最頻値（マイクロ秒）
	 * @see     http://php.net/manual/ja/function.round.php
	 */
	protected function mode($precision = null)
	{
		$precision = $precision ?? static::DEFAULT_PRECISION;
		if (!isset($this->calcCache['mode'])) {
			$round_count = [];
			foreach ($this->logs as $value) {
				$round_value = (string) round($value, $precision, \PHP_ROUND_HALF_UP);
				$round_count[$round_value] = ($round_count[$round_value] ?? 0) + 1;
			}

			asort($round_count);
			end($round_count);

			$this->calcCache['mode']        = key($round_count);
			$this->calcCache['precision']   = $precision;
		}

		return $this->calcCache['mode'];
	}

	/**
	 * 現時点までの実行時間計測記録を元に各種値を算出し、返します。
	 *
	 * @param   int     $precision  丸め桁数
	 * @return  array   実行時間計測の計算結果
	 */
	protected function calc ($precision = null)
	{
		$precision = $precision ?? static::DEFAULT_PRECISION;

		return [
			'count'     => $this->count(),
			'batch'     => $this->batch,
			'round'     => $this->round,
			'precision' => $precision,
			'sum'       => $this->sum(),
			'average'   => $this->average(),
			'min'       => $this->min(),
			'median'    => $this->median(),
			'max'       => $this->max(),
			'mode'      => $this->mode($precision),
		];
	}

	/**
	 * 指定された回数クロージャーを実行し、実行時間計測を行います。
	 *
	 * @param   int         $batch      1回分の実行回数
	 * @param   int         $round      平均を取るためのラウンド数
	 * @param   \Closure    $closure    クロージャー この引数が指定されている場合、インスタンスに設定されているクロージャより優先して使用される
	 * @return  TimeMeas    このインスタンス
	 */
	protected function exec($batch = null, $round = null, $closure = null, $argments = [])
	{
		$this->batch = $batch ?? static::DEFAULT_BATCH;
		$this->round = $round ?? static::DEFAULT_ROUND;

		if (empty($argments)) {
			$this->start();
			$closure = $closure ?? $this->closure();

			for ($i = 0;$i < $this->round;++$i) {
				$this->start();
				for ($n = 0;$n < $this->batch;++$n) {
					$closure();
				}
				$this->stop();
			}

			return $this;
		} else {
			$this->start();
			$closure = $closure ?? $this->closure();

			for ($i = 0;$i < $this->round;++$i) {
				$this->start();
				for ($n = 0;$n < $this->batch;++$n) {
					$closure(...$argments);
				}
				$this->stop();
			}

			return $this;
		}
	}
}
