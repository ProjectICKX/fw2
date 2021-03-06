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
	protected $_chains		= [];
	protected $_pinchs		= [];

	public static function instance () {
		return new static;
	}

	public function __construct() {
	}

	public function executer ($executer, $is_var = false) {
		$this->_executer	= $executer;
		$this->_isVar		= $is_var;
		return $this;
	}

	public function bindTo ($instance) {
		if (is_array($this->_executer)) {
			$this->_executer	= [$instance, $this->_executer[1]];
		} else {
			$this->_executer	= [$instance, $this->_executer];
		}
		return $this;
	}

	public function param ($param, $target = 0) {
		$this->_params[$target] = $param;
		return $this;
	}

	public function params (...$params) {
		$this->_params = $params;
		return $this;
	}

	public function pinch (...$pinchs) {
		$this->_pinchs = $pinchs;
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

	public function chains (...$chains) {
		$this->_chains	= array_merge($this->_chains, $chains);
		return $this;
	}

	public static function explodeExecuter ($executer) {
		if (is_array($executer)) {
			if ($executer[0] instanceof BindBuilder) {
				$executer[0]	= $executer[0]();
			}
			if (isset($executer[1]) && $executer[1] instanceof BindBuilder) {
				$executer[1]	= $executer[1]();
			}
		} else if ($executer instanceof BindBuilder) {
			$executer	= $executer();
		}
		return $executer;
	}

	public function toArray () {
		return [
			0	=> $this->_executer,
			1	=> $this->_params,
			2	=> $this->_alias,
			3	=> $this->_postFilter,
			4	=> $this->_isVar,
			5	=> $this->_chains,
		];
	}

	public function __invoke () {
		$executer	= static::explodeExecuter($this->_executer);

		if ($this->_isVar) {
			$result = $executer;
		} else {
			foreach ($params = array_values($this->_params ?? []) as $idx => $param) {
				if ($param instanceof BindBuilder) {
					$params[$idx] = $param();
				}
			}

			$result		= $executer(...$params);
		}

		$result = !is_null($this->_postFilter) ? $this->_postFilter($result) : $result;

		foreach ($this->_chains ?? [] as $chain) {
			switch (true) {
				case $chain instanceof \Closure:
					$result	= $chain($result);
					break;
				case $chain instanceof ActionBuilder:
					$chain	= $chain->bindTo($result);
					$result	= $chain();
					break;
				default:
					if (is_object($result)) {
						$result	= $result->$chain();
					} elseif (class_exists($result)) {
						$result	= $result::$chain();
					} else {
						$result	= $chain($result);
					}
					break;
			}
		}

		foreach ($this->_pinchs as $pinch) {
			if (is_array($result)) {
				$result = $result[$pinch];
			} elseif (is_object($result)) {
				$result = $result->{$pinch};
			}
		}

		if ($this->_alias) {
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
		}

		return $result;
	}
}
