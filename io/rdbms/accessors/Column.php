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
* @package		io
* @author		wakaba <wakabadou@gmail.com>
* @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
* @license		http://opensource.org/licenses/MIT The MIT License MIT
* @varsion		2.0.0
*/

namespace ickx\fw2\io\rdbms\accessors;

use ickx\fw2\core\exception\CoreException;

/**
 * Flywheel2 RDBMSDriver Abstract Class
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Column {
	protected $_columnStack;
	protected $_alias;

	protected static function __constructor () {
	}

	public static function set ($column_name) {
		$column = new static;
		$column->_columnStack[] = [$column_name];
		return $column;
	}

	public function calc ($column_name, $operator = '+') {
		$this->_columnStack[] = [$column_name, 'operator', $operator];
		return $this;
	}

	public function as ($alias) {
		$this->_alias = $alias;
		return $this;
	}

	public function __invoke($model_class = null) {
		$validate_list = !is_null($model_class) ? $model_class::GetColumnList() : false;

		$column_stack = $this->_columnStack;
		krsort($column_stack);

		$expansion = [];

		if (isset($this->_alias)) {
			$expansion[] = $this->_alias;
			$expansion[] = 'AS';
		}

		foreach ($column_stack as $stack) {
			$column_name = $stack[0];
			if ($validate_list !== false && !isset($validate_list[$column_name])) {
				CoreException::RaiseSystemError(sprintf('実在しないcolumn nameを指定されました。column name:%s', $column_name));
			}

			switch ($stack[1] ?? null) {
				case 'operator':
					$expansion[] = $column_name;
					$expansion[] = $stack[2];
					break;
				default:
					$expansion[] = $column_name;
					break;
			}
		}

		krsort($expansion);

		return sprintf(implode(' ', $expansion));
	}
}
