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

use ickx\fw2\security\validators\Validator;

/**
 * Action
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ValidationBuilder {
	protected $_config = [
		Validator::CONFIG_PREFIX			=> null,
		Validator::CONFIG_SAFIX				=> null,
		Validator::CONFIG_VARS				=> null,
		Validator::CONFIG_TITLE				=> null,
		Validator::CONFIG_NAME				=> null,
		Validator::CONFIG_NULL_SKIP			=> null,
		Validator::CONFIG_EMPTY_SKIP		=> null,
		Validator::CONFIG_STRING_EMPTY_SKIP	=> null,
		Validator::CONFIG_SKIP				=> null,
		Validator::CONFIG_CALLBACK_SKIP		=> null,
		Validator::CONFIG_VALUE				=> null,
		Validator::CONFIG_FORCE_USE_VALUE	=> null,
		Validator::CONFIG_FILTER			=> null,
		Validator::CONFIG_FORCE_VALIDATE	=> null,
		Validator::CONFIG_RAISE_EXCEPTION	=> null,
		Validator::CONFIG_IS_LAST			=> null,
		Validator::CONFIG_PREMISE			=> null,
		Validator::CONFIG_USE_CURRENT_DATA	=> null,
		Validator::CONTIG_SET_VALIDATE_TYPE	=> null,
		Validator::CONFIG_VALIDATE_SET		=> null,
		Validator::CONFIG_STOP_AFTER		=> null,
	];

	protected $_options	= [];

	protected $_ruleSet	= [];

	public function __construct() {
	}

	public static function instance () {
		return new static;
	}

	public function prefix ($prefix) {
		$this->_config[Validator::CONFIG_PREFIX] = $prefix;
		return $this;
	}

	public function safix ($safix) {
		$this->_config[Validator::CONFIG_SAFIX] = $safix;
		return $this;
	}

	public function vars ($vars) {
		$this->_config[Validator::CONFIG_VARS] = $vars;
		return $this;
	}

	public function title ($title) {
		$this->_config[Validator::CONFIG_TITLE] = $title;
		return $this;
	}

	public function name ($name) {
		$this->_config[Validator::CONFIG_NAME] = $name;
		return $this;
	}

	public function nullSkip ($null_skip) {
		$this->_config[Validator::CONFIG_NULL_SKIP] = $null_skip;
		return $this;
	}

	public function emptySkip ($empty_skip) {
		$this->_config[Validator::CONFIG_EMPTY_SKIP] = $empty_skip;
		return $this;
	}

	public function stringEmptySkip ($string_empty_skip) {
		$this->_config[Validator::CONFIG_STRING_EMPTY_SKIP] = $string_empty_skip;
		return $this;
	}

	public function skip ($skip) {
		$this->_config[Validator::CONFIG_SKIP] = $skip;
		return $this;
	}

	public function callbackSkip ($callback_skip) {
		$this->_config[Validator::CONFIG_CALLBACK_SKIP] = $callback_skip;
		return $this;
	}

	public function value ($value) {
		$this->_config[Validator::CONFIG_VALUE] = $value;
		return $this;
	}

	public function forceUseValue ($force_use_value) {
		$this->_config[Validator::CONFIG_FORCE_USE_VALUE] = $force_use_value;
		return $this;
	}

	public function filter ($filter) {
		$this->_config[Validator::CONFIG_FILTER] = $filter;
		return $this;
	}

	public function forceValidate ($force_validate) {
		$this->_config[Validator::CONFIG_FORCE_VALIDATE] = $force_validate;
		return $this;
	}

	public function raiseException ($raise_exception) {
		$this->_config[Validator::CONFIG_RAISE_EXCEPTION] = $raise_exception;
		return $this;
	}

	public function isLast ($is_last) {
		$this->_config[Validator::CONFIG_IS_LAST] = $is_last;
		return $this;
	}

	public function premise ($premise) {
		$this->_config[Validator::CONFIG_PREMISE] = $premise;
		return $this;
	}

	public function useCurrentData ($use_current_data) {
		$this->_config[Validator::CONFIG_USE_CURRENT_DATA] = $use_current_data;
		return $this;
	}

	public function setValidateType ($set_validate_type) {
		$this->_config[Validator::CONTIG_SET_VALIDATE_TYPE] = $set_validate_type;
		return $this;
	}

	public function validateSet ($validate_set) {
		$this->_config[Validator::CONFIG_VALIDATE_SET] = $validate_set;
		return $this;
	}

	public function stopAfter ($stop_after) {
		$this->_config[Validator::CONFIG_STOP_AFTER] = $stop_after;
		return $this;
	}

	public function option ($name, $value) {
		$this->_options[$name]	= $value;
		return $this;
	}

	public function rule ($rule_name, $callback = null) {
		$rule = ValidationRuleBuilder::instance($rule_name);
		!is_callable($callback) ?: $callback($rule);
		$this->_ruleSet[] = $rule;
		return $this;
	}

	public function toArray () {
		$rule_set = [];
		foreach ($this->_ruleSet as $rule) {
			$rule_set[] = $rule->toArray();
		}
		return array_merge($this->_config, $this->_options, $rule_set);
	}
}
