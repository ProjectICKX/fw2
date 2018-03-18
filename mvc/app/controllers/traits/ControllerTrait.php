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

namespace ickx\fw2\mvc\app\controllers\traits;

use ickx\fw2\vartype\objects\Objects;
use ickx\fw2\vartype\arrays\LazyArrayObject;

/**
 * Flywheel2 Controller特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ControllerTrait {
	use
		RuleTrait,
		ValidateTrait,
		StatusTrait,
		EventManageTrait,
		ActionTrait,
		ViewTrait,
		ForwarderTrait,
		RedirecterTrait,
		RenderTrait;

	/** @var	array	リクエストパラメータ */
	public $params			= null;

	/** @var	\ickx\fw2\core\net\http\Request 			Requestインスタンス */
	public $request			= null;

	/** @var	\ickx\fw2\core\net\http\Response			Responseインスタンス */
	public $response		= null;

	/** @var	string	現在のアプリケーション名 */
	public $appName			= null;

	/** @var	string	現在のコントローラ名 */
	public $controller		= null;

	/** @var	string	現在のアクション名 */
	public $action			= null;

	/** @var	string	次に行う処理の名称 */
	public $next			= null;

	/** @var	string	次に行う処理のURL */
	public $nextUrl			= null;

	/** @var	mixed	次に行う処理セット名 */
	public $nextName		= self::DEFAULT_NEXT_NAME;

	/**	@var	array	起動オプション */
	public $options			= [];

	/** @var	bool	トリガー開放フラグ */
	public $keepTrigger		= false;

	public $currentRule		= null;

	/**
	 * エグゼキュータ
	 *
	 * @param	array		$params		パラメータ
	 * @param	Controller	$instance	インスタンスを再利用する場合のインスタンス
	 * @return	Controller	インスタンス
	 */
	public static function Execute ($params, $instance = null) {
		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		$instance = new static;
		if ($instance !== null) {
			$class_name = static::class;
			if (!($instance instanceof $class_name)) {
				throw CoreException::RaiseSystemError('再利用するインスタンスのクラスが合いません。class_name:%s, instance_class_name:%s', [$class_name, (new \ReflectionObject($instance))->getName()]);
			}
		} else {
			$instance = new static;
		}

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('create_instance'));

		$params = LazyArrayObject::Create($params);
		$instance->options		= LazyArrayObject::RecursiveCreate($params->options ?: []);
		$instance->render		= LazyArrayObject::RecursiveCreate($params->render ?: []);
		$instance->error		= LazyArrayObject::RecursiveCreate($params->error ?: []);

		Environment::IsCli() && $instance->adjustCliParameters();

		$instance->request		= Request::GetInputVars([]);

		if (isset($instance->options->default_parameters)) {
			foreach ($instance->options->default_parameters as $parameter_name => $parameter_value) {
				if (!isset($params->parameter[$parameter_name]) || $params->parameter[$parameter_name] == '') {
					$params->parameter[$parameter_name] = $parameter_value;
				}
			}
		}

		if (isset($instance->options['redirect'])) {
			$instance->nextRule = IController::NEXT_REDIRECT;
			$instance->nextUrl = $instance->options['redirect'];
			if (method_exists($instance, 'clearFlashClassSession')) {
				$instance->clearFlashClassSession();
			}
			return $instance;
		}

		$instance->params		= $params;

//@TODO 様子見
//		$instance->overWriteParameterSet($params->parameter ?: []);
		$instance->route		= LazyArrayObject::Create($params->parameter ?? []);

		$instance->controller	= str_replace('/', '_', $params->controller ?? 'index');
		$instance->action		= str_replace('/', '_', $params->action ?? 'index');

		$instance->rawController	= $params->controller;
		$instance->rawAction		= $params->action;

		$route		= $instance->route;
		$render		= $instance->render;
		$options	= $instance->options;

		$replace_values	= LazyArrayObject::Create();
		$replace_values->merge($route, $render);

		foreach ($instance->options->getArrayCopy() as $key => $value) {
			if (!is_object($value) && mb_strpos($value, '{:') !== false) {
				$instance->options->$key = preg_replace_callback(
					"/\{:(.+?)\}/",
					function ($matches) use ($replace_values, $options) {
						$replace = isset($replace_values[$matches[1]]) ? $replace_values[$matches[1]] : (isset($options[$matches[1]]) ? $options[$matches[1]] : $matches[0]);
						return is_callable($replace) ? $replace($replace_values, $options) : $replace;
					},
					is_callable($value) ? $value($render, $options) : $value
				);
			}
		}

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('route_and_options_setup'));

		$instance->templateDir	= $instance->options->template_dir ?: $instance->controller;
		$instance->templateExtDirList	= $instance->options->template_ext_dir_list ? $instance->options->template_ext_dir_list->getArrayCopy() : [];

		$instance->template		= $instance->options->template ?: $instance->action;
		if (is_array($instance->template) || $instance->template instanceof \ickx\fw2\vartype\arrays\LazyArrayObject) {
			switch (count($instance->template)) {
				case 2:
					$instance->templateDir	= $instance->template['controller'] ?? $instance->template[0];
					$instance->template		= $instance->template['action'] ?? $instance->template[1];
					break;
				default:
					$instance->template		= $instance->template['action'] ?? $instance->template[0];
					break;
			}
		}

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('template_setup'));

		if (!$instance->canKeepTrigger() && $instance->trigger === null) {
			$instance->trigger = $instance->searchTrigger($instance->currentRule = $instance->getActionRule());
		}

		$instance->rule			= $instance->purseActionRule($instance->trigger);

		$instance->setUpDefaultEvents();

		if (method_exists($instance, 'setupEvents')) {
			Objects::InvokeOverrideMethod($instance, 'setupEvents');
		}

		$setup_action_events = [$instance, Strings::ToLowerCamelCase($instance->action.'_setup_events')];
		!is_callable($setup_action_events) ?: $setup_action_events();

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('setup_action_events'));

		$instance->dispatchEvents();

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log('dispatchEvents'));

		if (!$instance->canKeepTrigger()) {
			$instance->releaseTrigger($instance->trigger);
		}
		$instance->keepTrigger = false;

		assert((Flywheel::$reportingLevel & Flywheel::REPORTING_LEVEL_PROFILE) === 0 ?: TimeProfiler::debug()->log());

		if (method_exists($instance, 'clearFlashClassSession')) {
			$instance->clearFlashClassSession();
		}
		return $instance;
	}
	/**
	 * Render用に変数をassignします。
	 *
	 * @param	$name	変数名
	 * @param	$value	値
	 */
	public function assign ($name, $value) {
		$this->render[$name] = $value;
	}

	/**
	 * 配列で定義されたassign情報を元にRender用に変数をassignします。
	 *
	 * @param	$list	assign情報
	 */
	public function assigns ($list) {
		foreach ($list as $name => $value) {
			$this->render[$name] = $value;
		}
	}

	/**
	 * POST Dataから値を取得しアサインします。
	 *
	 * @param	string	$name
	 */
	public function assignPostData ($name) {
		$this->render[$name] = $this->request->data->$name;
	}

	/**
	 * トリガーを保持します。
	 */
	public function keepTrigger () {
		$this->keepTrigger = true;
	}

	/**
	 * トリガーを開放するかどうかの判定を行います。
	 *
	 * @return	bool	トリガーを開放しない場合はtrue、そうでない場合はfalse
	 */
	public function canKeepTrigger () {
		return $this->keepTrigger;
	}

	/**
	 * トリガーを解放します。
	 *
	 * @param	string	$trigger	トリガー
	 */
	public function releaseTrigger ($trigger) {
		if ($this->request->data) {
			Request::RemovePostData($trigger);
			if (Arrays::KeyExists($this->request->data, $trigger)) {
				unset($this->request->data->$trigger);
			}
		}

		if ($this->request->parameter) {
			Request::RemoveParameter($trigger);
			if (Arrays::KeyExists($this->request->parameter, $trigger)) {
				unset($this->request->parameter->$trigger);
			}
		}

		$this->trigger = null;
	}

	/**
	 * 次処理を強制的に設定します。
	 *
	 * @param	string	$url	次URL
	 * @param	string	$rule	次ルール
	 */
	public function forceNext ($url, $rule = self::NEXT_FORWARD) {
		$this->rule->next[0][0] = $url;
		$this->rule->next[0][1] = $rule;
	}

	/**
	 * エラー処理を強制的に設定します。
	 *
	 * @param	string	$url	次URL
	 * @param	string	$rule	次ルール
	 */
	public function forceErrorNext ($url, $rule = self::NEXT_FORWARD) {
		$this->rule->next['error'][0] = $url;
		$this->rule->next['error'][1] = $rule;
	}

	/**
	 * 次処理セット名を設定します。
	 *
	 * @param	string	$name	次処理セット名
	 */
	public function setNextName ($name) {
		if (!isset($this->rule->next[$name])) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('実在しないnext nameを指定されました。name:%s', [$name]);
		}
		$this->nextName = $name;
	}

	/**
	 * 次処理URLを設定します。
	 *
	 * @param	string	$url	次URL
	 */
	public function setUrl ($url) {
		if (!is_string($url) && is_callable($url)) {
			$this->nextUrl = $url($this->render);
		} else {
			$this->nextUrl = is_callable($url) ? $url() : $url;
		}
	}

	/**
	 * デフォルトの次URLを取得します。
	 *
	 * @return	mixed	デフォルトの次URL
	 */
	public function getDefaultNextUrl () {
		$next_name = $this->nextName;
		if (!isset($this->rule->next[$this->nextName]) && !empty($this->rule->next)) {
			$next_name = $this->rule->next;
			reset($next_name);
			$next_name = key($next_name);
		}

		if (isset($this->rule->next[$next_name]) && is_object($this->rule->next[$next_name]) && is_callable($this->rule->next[$next_name])) {
			$this->rule->next[$next_name] = [
				$this->rule->next[$next_name]()
			];
		}

		return isset($this->rule->next[$next_name][0]) ? $this->rule->next[$next_name][0] : null;
	}

	/**
	 * デフォルトの次ルールを取得します。
	 *
	 * @return	array	デフォルトの次ルール
	 */
	public function getDefaultNextRule () {
		return isset($this->rule->next[$this->nextName][1]) ? $this->rule->next[$this->nextName][1] : (($this->getDefaultNextUrl() === null) ? static::NEXT_RENDERING : static::NEXT_FORWARD);
	}

	/**
	 * エラー時の次URLを取得します。
	 *
	 * @return	mixed	エラー時の次URL
	 */
	public function getErrorNextUrl () {
		return isset($this->rule->next['error'][0]) ? $this->rule->next['error'][0] : null;
	}

	/**
	 * エラー時の次ルールを取得します。
	 *
	 * @return	array	エラー時の次ルール
	 */
	public function getErrorNextRule () {
		return isset($this->rule->next['error'][1]) ? $this->rule->next['error'][1] : (($this->getErrorNextUrl() === null) ? static::NEXT_RENDERING : static::NEXT_FORWARD);
	}

	/**
	 * 指定した配列要素にマッチする名称のポストデータを削除します。
	 *
	 * @param	array	$array	削除対象のキーリスト
	 * @return	bool	削除に成功した場合はtrue, そうでない場合はfalse
	 */
	public function removePostDataSet ($array) {
		if (!Arrays::IsTraversable($array)) {
			return false;
		}
		foreach ($array as $name) {
			if (isset($this->request->data->$name)) {
				Request::RemovePostData($name);
				unset($this->request->data->$name);
			}
		}
		return true;
	}

	/**
	 * ポストデータを上書きします。
	 *
	 * @param	string	$name	名前
	 * @param	mixed	$data	値
	 * @return	bool	常にtrue
	 */
	public function overWritePostData ($name, $data) {
		Request::OverWritePostData($name, $data);
		$this->request->data->$name = $data;
		return true;
	}

	/**
	 * 配列を使用して、ポストデータを一括して上書きします。
	 *
	 * @param	array	$array	上書きするデータ
	 * @return	bool	常にtrue
	 */
	public function overWritePostDataSet ($array) {
		if (!Arrays::IsTraversable($array)) {
			return false;
		}
		foreach ($array as $name => $data) {
			Request::OverWritePostData($name, $data);
			$this->request->data->$name = $data;
		}
		return true;
	}

	/**
	 * パラメータを上書きします。
	 *
	 * @param	string	$name	名前
	 * @param	mixed	$data	値
	 * @return	bool	常にtrue
	 */
	public function overWriteParameter ($name, $data) {
		Request::OverWriteParameters($name, $data);
		$this->request->parameter->$name = $data;
		return true;
	}

	/**
	 * 配列を使用して、パラメータを一括して上書きします。
	 *
	 * @param	array	$array	上書きするデータ
	 * @return	bool	常にtrue
	 */
	public function overWriteParameterSet ($array) {
		if (!Arrays::IsTraversable($array)) {
			return false;
		}
		foreach ($array as $name => $data) {
			Request::OverWriteParameters($name, $data);
			$this->request->parameter[$name] = $data;
		}
		if (is_array($this->request->parameter)) {
			$this->request->parameter = LazyArrayObject::Create($this->request->parameter);
		}
		return true;
	}

	/**
	 * CLIモード時にコマンドラインパラメータをリクエストパラメータに変換します。
	 *
	 */
	public function adjustCliParameters () {
		!isset($_GET) && $_GET = [];

		foreach ($this->getCliRequestParameterList() as $name => $value) {
			Request::OverWriteParameters($name, $value);
		}
	}

	/**
	 * GET パラメータからフィルタ済みの配列を返します。
	 *
	 * @param	array	$parameter_name_list	許可するパラメータ名リスト
	 * @return	array	フィルタ済みの配列
	 */
	public function getFilteredParameters ($parameter_name_list) {
		return array_filter($this->request->parameter->getRecursiveArrayCopy(), function ($key) use ($parameter_name_list) {return in_array($key, $parameter_name_list);}, \ARRAY_FILTER_USE_KEY);
	}

	public function stringEmptyFilter ($array, $key_list = []) {
		return array_filter($array, function ($value, $key) use ($key_list) {return !empty($key_list) && !in_array($key, $key_list, true) ? true : $value !== '';}, \ARRAY_FILTER_USE_BOTH);
	}

	public function arrayEmptyFilter ($array, $key_list = []) {
		return array_filter($array, function ($value, $key) use ($key_list) {return !empty($key_list) && !in_array($key, $key_list, true) ? true : !empty($value);}, \ARRAY_FILTER_USE_BOTH);
	}

	public function toArrayFilter ($array, $keys) {
		foreach ($keys as $key) {
			$array[$key] = isset($array[$key]) ? (array) $array[$key] : [];
		}
		return $array;
	}

	public function multiFilter ($parameter_name_list, $filter_set) {
		$param_list = $this->getFilteredParameters($parameter_name_list);
		foreach ($filter_set as $method => $list) {
			$param_list = $this->$method($param_list, $list);
		}
		return $param_list;
	}

	/**
	 * CLIモード時のコマンドラインパラメータを取得します。
	 *
	 */
	public static function getCliRequestParameterList () {
		if ($_SERVER['argc'] < 3) {
			return [];
		}
		$current_key = null;
		$parameter_list = [];

		$args = array_slice($_SERVER['argv'], 2);
		$argc = count($args);

		for ($i = 0;$i < $argc;$i++) {
			$argv = $args[$i];
			if (substr($argv, 0, 1) === '-') {
				$current_key = substr($argv, 1);
				$parameter_list[$current_key] = '';
				if (isset($args[$i + 1]) && substr($args[$i + 1], 0, 1) !== '-') {
					$parameter_list[$current_key] = $args[$i + 1];
					$i++;
				}
				continue;
			}
			if (isset($parameter_list[$current_key])) {
				if (!is_array($parameter_list[$current_key])) {
					$parameter_list[$current_key] = (array) $parameter_list[$current_key];
				}
				$parameter_list[$current_key][] = $argv;
			} else {
				$parameter_list[$current_key] = $argv;
			}
		}

		return $parameter_list;
	}

	/**
	 * 文字列を内部URL形式に変換します。
	 *
	 * @param	string	$url	変換するURL文字列
	 * @return	string	内部URL形式に変換された文字列
	 */
	public static function ConvertUrlString ($url) {
		return '/' . ltrim($url, '/');
	}
}
