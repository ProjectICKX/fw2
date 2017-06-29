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
 * Flywheel2 EventManage特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait EventManageTrait {
	/** @var	bool	以降のイベントを全てキャンセルするフラグ */
	public $cancelEvents	= false;

	/** @var	array	イベント管理配列 */
	protected $_events			= [];

	/** @var	array	スキップ対象イベント管理配列 */
	protected $_skipEvents		= [];

	/**
	 * イベントを実行します。
	 */
	public function dispatchEvents () {
		foreach ($this->_events as $event_name => $event_method) {
			if (isset($this->_skipEvents[$event_name])) {
				continue;
			}

			if (!is_callable($event_method)) {
				continue;
			}

			$result_part_list = $event_method($this->render);
			if (!is_array($result_part_list)) {
				$result_part_list = (array) $result_part_list;
			}
			foreach ($result_part_list as $name => $result_part) {
				$this->render[$name] = $result_part;
			}

			if ($this->cancelEvents) {
				break;
			}
		}
	}

	/**
	 * デフォルトで実行するイベントを設定します。
	 *
	 * このメソッドは必ず実行されます。
	 */
	public function setUpDefaultEvents () {
		$this->_events = [
			'set_up'	=> [$this, 'defaultSetUpEvent'],
			'action'	=> [$this, 'defaultActionEvent'],
			'next'		=> [$this, 'defaultNextEvent'],
			'tear_down'	=> [$this, 'defaultTearDownEvent'],
		];
	}

	/**
	 * 対象となるイベントの前に指定したイベントを挿入します。
	 *
	 * 同名のイベントが存在する場合、すでに登録されているイベントは解除されます。
	 *
	 * @param	string		$target_event_name	対象となるイベント
	 * @param	string		$event_name			挿入するイベント名
	 * @param	callable	$event_method		挿入するイベントメソッド
	 */
	public function insertBeforeEvent ($target_name, $event_name, $event_method) {
		$this->insertBeforeEvents($target_name, [$event_name => $event_method]);
	}

	/**
	 * 対象となるイベントの前に指定したイベント群を挿入します。
	 *
	 * 同名のイベントが存在する場合、すでに登録されているイベントは解除されます。
	 *
	 * @param	string		$target_event_name	対象となるイベント
	 * @param	array		$event_set			挿入するイベント群
	 */
	public function insertBeforeEvents ($target_name, $event_set) {
		$this->removeEvents(array_keys($event_set));
		$tmp_events = [];
		foreach ($this->_events as $current_event_name => $current_event_method) {
			if ($target_name === $current_event_name) {
				foreach ($event_set as $event_name => $event_method) {
					$tmp_events[$event_name] = $event_method;
				}
			}
			$tmp_events[$current_event_name] = $current_event_method;
		}
		$this->_events = $tmp_events;
	}

	/**
	 * 対象となるイベントの後に指定したイベントを挿入します。
	 *
	 * 同名のイベントが存在する場合、すでに登録されているイベントは解除されます。
	 *
	 * @param	string		$target_event_name	対象となるイベント
	 * @param	string		$event_name			挿入するイベント名
	 * @param	callable	$event_method		挿入するイベントメソッド
	 */
	public function insertAfterEvent ($target_name, $event_name, $event_method) {
		$this->insertAfterEvents($target_name, [$event_name => $event_method]);
	}

	/**
	 * 対象となるイベントの後に指定したイベント群を挿入します。
	 *
	 * 同名のイベントが存在する場合、すでに登録されているイベントは解除されます。
	 *
	 * @param	string		$target_event_name	対象となるイベント
	 * @param	array		$event_set			挿入するイベント群
	 */
	public function insertAfterEvents ($target_name, $event_set) {
		$this->removeEvents(array_keys($event_set));
		$tmp_events = [];
		foreach ($this->_events as $current_event_name => $current_event_method) {
			$tmp_events[$current_event_name] = $current_event_method;
			if ($target_name === $current_event_name) {
				foreach ($event_set as $event_name => $event_method) {
					$tmp_events[$event_name] = $event_method;
				}
			}
		}
		$this->_events = $tmp_events;

	}

	/**
	 * イベントを追加します。
	 *
 	 * 同名のイベントが存在する場合、すでに登録されているイベントは解除されます。
 	 *
	 * @param	string		$event_name			追加するイベント名
	 * @param	callable	$event_method		追加するイベントメソッド
	 */
	public function appendEvent ($event_name, $event_method) {
		$this->appendEvents([$event_name => $event_method]);
	}

	/**
	 * イベント群を追加します。
	 *
	 * @param	array	$event_set	追加するイベント群
	 */
	public function appendEvents ($event_set) {
		$this->removeEvents(array_keys($event_set));
		foreach ($event_set as $event_name => $event_method) {
			$this->_events[$event_name] = $event_method;
		}
	}

	/**
	 * イベントを削除します。
	 *
	 * @param	string		$event_name			削除するイベント名
	 */
	public function removeEvent ($event_name) {
		$this->removeEvents((array) $event_name);
	}

	/**
	 * イベントをまとめて削除します。
	 *
	 * @param	array		$event_name_list	削除するイベント名のリスト
	 */
	public function removeEvents ($event_name_list) {
		foreach ($event_name_list as $event_name) {
			if (isset($this->_events[$event_name])) {
				unset($this->_events[$event_name]);
			}
		}
	}

	/**
	 * 指定したイベントをスキップさせます。
	 *
	 * @param	string		$event_name			スキップするイベント名
	 */
	public function skipEvent ($event_name) {
		$this->skipEvents((array) $event_name);
	}

	/**
	 * 指定したイベント群をスキップさせます。
	 *
	 * @param	array		$event_name_list	スキップするイベント名のリスト
	 */
	public function skipEvents ($event_name_list) {
		foreach ($event_name_list as $event_name) {
			$this->_skipEvents[$event_name] = true;
		}
	}

	/**
	 * イベントスキップを解除します。
	 *
	 * @param	string		$event_name			スキップを解除するイベント名
	 */
	public function removeSkipEvent ($event_name) {
		$this->removeSkipEvents((array) $event_name);
	}

	/**
	 * 指定したイベント群のスキップ設定を解除します。
	 *
	 * @param	array		$event_name_list	スキップを解除するイベント名のリスト
	 */
	public function removeSkipEvents ($event_name_list) {
		foreach ($event_name_list as $event_name) {
			if (isset($this->_skipEvents[$event_name])) {
				unset($this->_skipEvents[$event_name]);
			}
		}
	}

	/**
	 * 以降のイベントを全てスキップさせます。
	 */
	public function cancelEvents () {
		$this->_cancelEvents = true;
	}

	/**
	 * デフォルトのセットアップイベントです。
	 */
	public function defaultSetUpEvent () {
		$ret = $this->setup();
		if ($ret === false) {
			$this->cancelEvents();
			return null;
		}
	}

	/**
	 * デフォルトのアクションイベントです。
	 */
	public function defaultActionEvent () {
		$this->allowedRequestParameter();
		$this->setErrorList($this->validate());

		$ret = $this->action($this->render);
		if ($ret === false) {
			$this->cancelEvents();
			return null;
		}
		return $ret;
	}

	/**
	 * デフォルトの次処理確定イベントです。
	 */
	public function defaultNextEvent () {
		if ($this->isError()) {
			$this->setUrl($this->getErrorNextUrl());
			$this->nextRule		= $this->getErrorNextRule();
		} else {
			$this->setUrl($this->getDefaultNextUrl());
			$this->nextRule		= $this->getDefaultNextRule();
		}
	}

	/**
	 * デフォルトのティアダウンイベントです。
	 */
	public function defaultTearDownEvent () {
		$this->cancelEvents = false;
	}

	/**
	 * setupを実行します。
	 *
	 * @return	bool	処理結果
	 */
	public function setup () {
		$target_setup = [$this, Strings::ToLowerCamelCase($this->action.'_setup')];
		return is_callable($target_setup) ? $target_setup() : null;
	}
}
