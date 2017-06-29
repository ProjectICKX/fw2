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
class ValidationRuleBuilder {
	protected $_ruleName	= null;

	protected $_params		= [];

	protected $_options		= [
		Validator::OPTION_RAISE_EXCEPTION	=> null,
		Validator::OPTION_IS_LAST			=> null,
		Validator::OPTION_NOT_DEAL_ARRAY	=> null,
		Validator::OPTION_EMPTY_SKIP		=> null,
		Validator::OPTION_SEE_ARRAY_KEYS	=> null,
		Validator::OPTION_FORCE_VALLIDATE	=> null,
		Validator::OPTION_PREMISE			=> null,
	];

	public static function instance ($rule_name) {
		return new static($rule_name);
	}

	public function __construct($rule_name) {
		$this->_ruleName	= $rule_name;
	}

	public function raiseException ($raise_exception) {
		$this->_options[Validator::OPTION_RAISE_EXCEPTION] = $raise_exception;
		return $this;
	}

	public function isLast ($is_last) {
		$this->_options[Validator::OPTION_IS_LAST] = $is_last;
		return $this;
	}

	public function notDealArray ($not_deal_array) {
		$this->_options[Validator::OPTION_NOT_DEAL_ARRAY] = $not_deal_array;
		return $this;
	}

	public function emptySkip ($empty_skip) {
		$this->_options[Validator::OPTION_EMPTY_SKIP] = $empty_skip;
		return $this;
	}

	public function arrayKeys ($array_keys) {
		$this->_options[Validator::OPTION_SEE_ARRAY_KEYS] = $array_keys;
		return $this;
	}

	public function forceValidate ($force_validate) {
		$this->_options[Validator::OPTION_FORCE_VALLIDATE] = $force_validate;
		return $this;
	}

	public function premise ($premise) {
		$this->_options[Validator::OPTION_PREMISE] = $premise;
		return $this;
	}

	public function param ($value, $key = null) {
		if (is_null($key)) {
			$this->_params[] = $value;
		} else {
			$this->_params[$key] = $value;
		}
		return $this;
	}

	public function toArray () {
		return array_merge([$this->_ruleName], $this->_params, $this->_options);
	}
}
