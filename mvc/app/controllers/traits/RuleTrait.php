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

/**
 * Flywheel2 Controller向けRule特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait RuleTrait {
	/**	@property	array	現在のアクションルール */
	public $rule	= [];

	/** @property	string	アクションルールトリガ */
	public $trigger	= null;

	/**
	 * 現在のリクエストにマッチするアクションルールトリガを検索します。
	 *
	 * @param	array	$action_rules	アクションルール
	 * @return	string	アクションルールトリガ
	 */
	public function searchTrigger ($action_rule_list = null) {
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			foreach (array_keys($action_rule_list ?: $this->getActionRule()) as $trigger) {
				if (substr($trigger, 0, 8) === ':router:') {
					$url = substr($trigger, 8);
					list($regex_url, $parameter_list) = Router::PursePathRegex($url);
					$match_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $regex_url));
					if (array_filter(array_keys(Request::GetPostData()->getArrayCopy()), function ($value) use ($match_pattern) {return preg_match($match_pattern, $value) === 1;})) {
						return $trigger;
					}
					continue;
				} else if (preg_match("/^:method:(.+)/", $trigger, $matches)) {
					foreach (explode('&', ($matches[1])) as $method_name) {
						if (($ret = $this->$method_name()) === false) {
							continue 2;
						}
						$trigger = $ret;
					}
					return $trigger;
				} else {
					foreach (explode('&', $trigger) as $post_data_name) {
						if (Request::GetPostData()->$post_data_name === null) {
							continue 2;
						}
					}
					return $trigger;
				}
			}
			return static::MEAN_DEFAULT;
		} else {
			foreach (array_keys($action_rule_list ?: $this->getActionRule()) as $trigger) {
				if (substr($trigger, 0, 8) === ':router:') {
					$url = substr($trigger, 8);
					list($regex_url, $parameter_list) = Router::PursePathRegex($url);
					$match_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $regex_url));
					if (array_filter(array_keys(Request::GetParameters()->getArrayCopy()), function ($value) use ($match_pattern) {return preg_match($match_pattern, $value) === 1;})) {
						return $trigger;
					}
					continue;
				} else if (preg_match("/^:callback:(.+)/", $trigger, $matches)) {
					foreach (explode('&', ($matches[1])) as $method_name) {
						if (($ret = $this->$method_name()) === false) {
							continue 2;
						}
						$trigger = $ret;
					}
					return $trigger;
				} else {
					foreach (explode('&', $trigger) as $parameter_name) {
						if (!isset(Request::GetParameters()[$parameter_name])) {
							continue 2;
						}
					}
					return $trigger;
				}
			}
			return static::MEAN_DEFAULT;
		}
	}

	/**
	 * アクションルールを取得します。
	 *
	 * @return	array	アクションルール
	 */
	public function getActionRule () {
		return array_merge(
			static::GetDefaultRule(),
			method_exists($this, Strings::ToLowerCamelCase($this->action.'_rule')) ? $this->{Strings::ToLowerCamelCase($this->action.'_rule')}() ?: [] : []
		);
	}

	/**
	 * アクションルールを解析して、現在のリクエスト用のアクションルールを返します。
	 *
	 * @param	string			$trigger	アクションルールトリガ
	 * @return	LazyArrayObject	現在のアクションルール
	 */
	public function purseActionRule ($trigger = null) {
		$action_rule_list = $this->currentRule ?? $this->getActionRule();
		$trigger = $this->searchTrigger($action_rule_list);

		if (substr($trigger, 0, 8) === ':router:') {
			$url = substr($trigger, 8);
			list($regex_url, $parameter_list) = Router::PursePathRegex($url);
			$match_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $regex_url));

			$source = Request::IsPostMethod() ? Request::GetPostData()->getArrayCopy() : Request::GetParameters()->getArrayCopy();

			//searchTrigger通過時点で最低一つはあることが確定している
			$target_list = array_filter(array_keys($source), function ($value) use ($match_pattern) {return preg_match($match_pattern, $value) === 1;});
			preg_match($match_pattern, current($target_list), $matches);

			//一つ目は全体マッチなので削除
			array_shift($matches);
			if (Request::IsPostMethod()) {
				foreach (array_filter($parameter_list, function ($value) {return $value !== '';}) as $idx => $parameter_name) {
					//データに追加アタッチ
					Request::OverWritePostData($parameter_name, $matches[$idx]);
				}
			} else {
				foreach (array_filter($parameter_list, function ($value) {return $value !== '';}) as $idx => $parameter_name) {
					//データに追加アタッチ
					Request::OverWriteParameters($parameter_name, $matches[$idx]);
				}
			}
		}

		$action_rule = $action_rule_list[$trigger];
		if (!isset($action_rule['before_action'])) {
			$action_rule['before_action'] = [];
		}
		if (!isset($action_rule['after_action'])) {
			$action_rule['after_action'] = [];
		}

		//事前実行追加
		foreach (['beforeForceAction', 'beforeAction'] as $name) {
			if (!method_exists($this, $name)) {
				continue;
			}
			$action_rule['before_action'] = array_merge($action_rule['before_action'], [$this->$name()]);
		}

		//事後実行追加
		foreach (['afterRule', 'afterForceRule'] as $name) {
			if (!method_exists($this, $name)) {
				continue;
			}
			$action_rule['after_action'] = array_merge($action_rule['after_action'], [$this->$name()]);
		}

		return LazyArrayObject::Create($action_rule);
	}

	/**
	 * デフォルトアクションルールを返します。
	 *
	 * @return	array	デフォルトのアクションルール
	 */
	public static function GetDefaultRule () {
		return [
			static::MEAN_DEFAULT => [
				'validate'	=> [],
				'action'	=> [],
				'error'		=> [],
				'next'		=> [],
				'render'	=> [],
			],
		];
	}
}
