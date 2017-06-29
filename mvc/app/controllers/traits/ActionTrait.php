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

namespace ickx\fw2\mvc\app\controllers\traits;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\mvc\app\builders\ActionBuilder;

/**
 * Flywheel2 Action特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ActionTrait {
	/** @var	bool	アクションをスキップするかどうかのフラグ */
	public $skipAction			= false;

	/** @var	bool	以降のアクションチェインをパージするかどうかのフラグ */
	public $purgeActionChain	= false;

	/** @var	array	途中乗換アクションリスト */
	public $changeAction		= [];

	/** @var	array	次のアクションindex */
	public $nextAction			= null;

	public static function ActionBuilder ($executer, $is_var = false) {
		return (new ActionBuilder())->executer($executer, $is_var);
	}

	/**
	 * 以降のアクションチェインをパージさせます。
	 */
	public function purgeActionChain () {
		$this->purgeActionChain  = true;
	}

	/**
	 * アクションチェインをパージしてよいかどうか判定します。
	 *
	 * @return	bool	アクションチェインをパージしても良い場合はtrue, そうでない場合はfalse
	 */
	public function isPurgeActionChain () {
		return $this->purgeActionChain;
	}

	/**
	 * アクションをスキップさせます。
	 */
	public function skipAction () {
		$this->skipAction	= true;
	}

	/**
	 * アクションをスキップしても良いかどうか判定します。
	 *
	 * @return	bool	アクションをスキップしても良い場合はtrue, そうでない場合はfalse
	 */
	public function isSkipAction () {
		return $this->skipAction;
	}

	/**
	 * ここより先の処理をエラー時の処理に乗り換えます。
	 */
	public function changeActionForError () {
		$this->changeAction = [$this->trigger, 'error'];
	}

	/**
	 * 次のアクションindexを設定します。
	 */
	public function setNextAction ($action_index) {
		$this->nextAction = $action_index;
	}

	/**
	 * アクションを実行します。
	 *
	 * @return	array	描画用データ
	 */
	public function action ($render = []) {
		if ($this->isError()) {
			if ($this->rule->error === false) {
				return $this->render;
			}
			$action_list = $this->rule->error ?: [];
		} else {
			if ($this->rule->action === false) {
				return $this->render;
			}
			$action_list = $this->rule->action ?: [];
		}

		$action_set = static::GetExecuteAction($this, $action_list);
		if (empty($action_set) || current($action_set) === null) {
			return [];
		}

		while (($action = current($action_set)) !== false) {
			$key	= key($action_set);
			$action = [
				1		=> $action,
				'value'	=> $action,
				0		=> $key,
				'key'	=> $key,
			];
			next($action_set);

			if ($this->isPurgeActionChain()) {
				return $this->render;
			}

			$key = $action['key'] ??  $action[0] ?? null;

			$action = current($action);

			$is_var = $action[4] ?? false;
			if ($is_var) {
				$action[1] = $action[1] ?? [];
				$result_alias = $action[2] ?? null;
				$post_action_filter = $action[3] ?? null;

				$result_part_list = $action[0];
			} else {
				if (!is_callable($action[0])) {
					if ($action[0] === null) {
						if ($this->isError()) {
							throw CoreException::RaiseSystemError('エラー時のメソッドが未定義です。%sActionを実装するか、\'error\'ルールに実行アクションリストまたはfalseを設定してください。', [$this->action]);
						}
						throw CoreException::RaiseSystemError('アクションメソッドが未定義です。%sActionを実装するか、\'action\'ルールに実行アクションリストまたはfalseを設定してください。', [$this->action]);
					}
					throw CoreException::RaiseSystemError('実行不能なメソッドを指定されました。method:%s, caller_class:%s, action:%s, trigger:%s', [$action[0] ? 'null' : serialize($action[0]), static::class, $this->action, $this->trigger]);
				}

				$action[1] = $action[1] ?? [];
				$result_alias = $action[2] ?? null;
				$post_action_filter = $action[3] ?? null;

				$inject_console = [];
				$trait_console = false;
				if (isset($this->rule->inject_console[$key])) {
					$inject_console = $this->rule->inject_console[$key];
					foreach (['start', 'end', 'header', 'prompt'] as $idx => $target_idx) {
						$inject_console[$target_idx] = $inject_console[$target_idx] ?? $inject_console[$idx] ?? null;
					}
					$trait_console = method_exists($this, 'ConsoleLog') && method_exists($this, 'ConsoleHeader') && method_exists($this, 'Prompt');
				}

				if (isset($inject_console['header']) && $trait_console) {
					static::ConsoleHeader($inject_console['header']);
				}
				if (isset($inject_console['start']) && $trait_console) {
					static::ConsoleLog($inject_console['start']);
				}
				if (isset($inject_console['prompt']) && is_array($inject_console['prompt']) && $trait_console) {
					$this->render['prompt_value'] = static::Prompt(
						$inject_console['prompt']['message'] ?? $inject_console['prompt'][0] ?? null,
						$inject_console['prompt']['validate_rule'] ?? $inject_console['prompt'][1] ?? null,
						$inject_console['prompt']['data_name'] ?? $inject_console['prompt'][2] ?? null
					);
				}

				$params = $action[1];
				foreach ($params as $idx => $value) {
					if (is_object($value) && is_callable($value)) {
						$params[$idx] = $value($this->render);
					}
				}

				//action本体の実行
				$result_part_list = $action[0](...$params);
			}

			if ($post_action_filter !== null && is_callable($post_action_filter)) {
				$result_part_list = $post_action_filter(...$result_part_list);
			}

			if (!is_null($result_alias)) {
				if (is_array($result_alias)) {
					$tmp = [];
					$end = false;
					reset($result_part_list);
					foreach ($result_alias as $alias) {
						if (!$end) {
							$tmp[$alias] = current($result_part_list);
							$end = next($result_part_list);
						} else {
							$tmp[$alias] = null;
						}
					}
					$result_part_list = $tmp;
				} else {
					$result_part_list = [$result_alias => $result_part_list];
				}
			}

			if (isset($inject_console['end']) && $trait_console) {
				static::ConsoleLog($inject_console['end']);
			}

			if (!is_array($result_part_list) && !$result_part_list instanceof \ickx\fw2\vartype\arrays\LazyArrayObject && !$result_part_list instanceof \ArrayObject) {
				$result_part_list = [];
			}

			$current_render = $this->render;
			foreach ($result_part_list as $name => $result_part) {
				$current_render[$name] = $result_part;
			}
			if (!empty($this->changeAction)) {
				$trigger = $this->changeAction[0];
				$name = $this->changeAction[1];
				$action_set = static::GetExecuteAction($this, $this->rule->$name ?: []);
				$this->changeAction = [];
			}

			if (!empty($this->nextAction) && isset($action_set[$this->nextAction])) {
				reset($action_set);
				while (($action = current($action_set)) !== false) {
					next($action_set);
					if (key($action_set) === $this->nextAction) {
						break;
					}
				}

				prev($action_set);
				$this->setNextAction(null);
			}
		}

		return $current_render;
	}

	/**
	 * 今回のリクエストで実行するアクションセットを返します。
	 *
	 * @param	Controller	$instance		コントローラインスタンス
	 * @param	array		$action_list	アクションリスト
	 * @return	array		アクションセット
	 */
	public static function GetExecuteAction ($instance, $action_list) {
		static $internal_functions;
		if (!isset($internal_functions)) {
			$internal_functions = get_defined_functions()['internal'];
		}

		$action_set = [];

		$key = null;

		$action_builder_class = ActionBuilder::class;

		//アクションそのものがコーラブルな場合、一度実行する
		if (is_object($action_list) && is_callable($action_list)) {
			$action_list = $action_list();
		}

		foreach ((array) $action_list as $key => $action) {
			//パラメータによる指示が無い場合はアクション名のメソッドを実行
			if ($action === static::MEAN_DEFAULT) {
				$action = Strings::ToLowerCamelCase($instance->action.'_action');
			}

			if (is_object($action) && $action instanceof $action_builder_class) {
				$action = $action->toArray();
			}

			$is_var = $action[4] ?? false;

			if ($is_var) {
				$result_alias = $action[2] ?? null;
				$action_set[$key] = [$action[0], null, $result_alias, null, $is_var];
				continue;
			} else {
				//アクション実行用のパラメータを設定
				$parameters = [];
				if (!is_object($action)) {
					if (is_array($action[0]) && is_callable($action[0])) {
						$parameters = $action['parameters'] ?? $action[1] ?? [];
					} else if (isset($action[1]) && is_string($action[1])) {
						$parameters = $action['parameters'] ?? $action[2] ?? [];
					}
				}

				//メソッドが現在のインスタンスに実行可能な状態で存在する場合の処理
				//例	'action'	=> 'indexAction',
				//		$this->indexAction($parameters);
				if (is_string($action) && method_exists($instance, $action)) {
					$action_set[$key] = [[$instance, $action], $parameters];
					continue;
				}

				//指定されたアクションそのものが実行可能な形式の場合の処理
				//ルールにてactionキーへ直接設定を書いた場合のみ該当する
				//例	ベタの関数の場合
				//		'action'	=> 'array_fliter',
				//		array_fliter($parameters)
				if (is_callable($action)) {
					$action_set[$key] = [$action, $parameters];
					continue;
				}
			}

			//複数アクションを指定していた場合の処理
			if (is_array($action)) {
				//結果に対して別名を付ける
				//[$result_alias => call_user_func_array($action[0], $action[1])];と同様の効果
				$result_alias = $action[2] ?? null;

				//action実行後に結果に対して実行するフィルタ
				$post_action_filter = isset($action[3]) ? (array) $action[3] : null;

				$is_var = $action[4] ?? false;
				if ($is_var) {
					$action_set[$key] = [$action[0], null, $result_alias, null, $is_var];
					continue;
				}

				if (is_string($action[0])) {
					//インスタンスメソッドを指定する場合
					$action[0] = [$instance, $action[0]];
				}

				//アクションセットの設定
				if (is_callable($action[0])) {
					$action_set[$key] = [$action[0], $action[1] ?? $parameters, $result_alias, $post_action_filter];
					continue;
				}

				//'action' => [$callback, $parameter],として設定した場合
				if (is_callable($action[0][0]) && !is_string($action[0][1])) {
					$action_set[$key] = [$action[0], $action[1] ?? $parameters, $result_alias, $post_action_filter];
					continue;
				}

				//'action' => [$instance, 'methodName'],として設定した場合、$instanceは任意のオブジェクトの指定が可能
				if (method_exists($action[0][0], $action[0][1])) {
					$action_set[$key] = [$action[0], $action[1] ?? $parameters, $result_alias, $post_action_filter];
					continue;
				}

				//'action' => ['function_name'],として設定した場合
				if ($action[0][0] === $instance && is_callable($action[0][1])) {
					$action_set[$key] = [$action[0][1], $action[1] ?? $parameters, $result_alias, $post_action_filter];
					continue;
				}
			}

			//
			throw CoreException::RaiseSystemError('未定義のメソッドを指定されました。');
		}

		if ($key === null || empty($action_set)) {
			$action_set[$key] = (method_exists($instance, Strings::ToLowerCamelCase($instance->action.'_action'))) ? [[$instance, Strings::ToLowerCamelCase($instance->action.'_action')], []] : null;
		}

		if (!empty($instance->rule->before_action)) {
			$action_set = array_merge($instance->rule->before_action, $action_set);
		}

		if (!empty($instance->rule->after_action)) {
			$action_set = array_merge($action_set, $instance->rule->after_action);
		}

		return $action_set;
	}
}
