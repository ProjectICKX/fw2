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

namespace ickx\fw2\mvc\app\builders;

/**
 * Action
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ActionBuilder {
	protected $_executer	= null;
	protected $_isVar		= false;
	protected $_params		= null;
	protected $_alias		= null;
	protected $_postFilter	= null;
	protected $_binds		= null;

	public function __construct() {
	}

	public function executer ($executer, $is_var = false) {
		$this->_executer	= $executer;
		$this->_isVar		= $is_var;
		return $this;
	}

	public function param ($param, $target = 0) {
		$this->_params[$target] = $param;
		return $this;
	}

	public function params ($params) {
		$this->_params = $params;
		return $this;
	}

	public function alias ($alias) {
		$this->_alias = $alias;
		return $this;
	}

	public function postFilter ($post_filter) {
		$this->_postFilter = (array) $post_filter;
		return $this;
	}

	public function bind ($render_var_name, $target = 0) {
		$this->_binds[$target] = $render_var_name;
		return $this;
	}

	public function binds ($binds) {
		$this->_binds = $binds;
		return $this;
	}

	public function toArray () {
		return [
			0	=> $this->_executer,
			1	=> $this->_paramBind(),
			2	=> $this->_alias,
			3	=> $this->_postFilter,
			4	=> $this->_isVar,
		];
	}

	public function __invoke () {
		$result = $this->_isVar ? $this->_executer(...$this->_params) : $this->_executer;
		$result = !is_null($this->_postFilter) ? $this->_postFilter($result) : $result;
		if ($this->_alias) {
			if (is_array($this->_alias)) {
				$tmp = [];
				$end = false;
				reset($result);
				foreach ($this->_alias as $alias) {
					if (!$end) {
						$tmp[$alias] = current($result);
						$end = next($result);
					} else {
						$tmp[$alias] = null;
					}
				}
				$result = $tmp;
			} else {
				$result = [$this->_alias => $result];
			}
		}
		return $result;
	}

	protected function _paramBind () {
		$params = $this->_params;
		foreach ($this->_binds ?? [] as $idx => $render_var_name) {
			$params[$idx] = function ($data) use ($render_var_name) {
				return $data->$render_var_name;
			};
		}
		return $params;
	}
}
