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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\rdbms\builder;

/**
 * Flywheel2 RDBMS WhereConditionBuilder
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 *
 * @see			\ickx\fw2\io\rdbms\accessors\traits\MethodAccessorTrait::CreateSelectQuery
 */
class WhereConditionBuilder {
	protected $modelClass	= null;
	protected $column		= null;
	protected $value		= null;
	protected $operator		= '=';

	protected function __construct ($called_class) {
		$this->modelClass	= $called_class;
	}

	public static function init ($called_class) {
		return new static($called_class);
	}

	public function column ($column) {
		$this->column	= $column;
		return $this;
	}

	public function value ($value) {
		$this->value	= $value;
		return $this;
	}

	public function getValue () {
		return $this->value;
	}

	public function operator ($operator) {
		$this->operator	= $operator;
		return $this;
	}

	public function __invoke () {
		//@TODO `をDBDriverから取得するようにする。$this->enclosure など

		$column	= sprintf('`%s`', implode('`.`', array_filter([$this->modelClass::GetDatabaseName(), $this->modelClass::GetName(), $this->column])));

		if (is_array($this->value)) {
			$operator		= $this->operator === '=' ? 'IN' : $this->operator;
			$place_folder	= implode(', ', array_fill(0 , count($this->value), '?'));
		} else {
			$operator		= $this->operator;
			$place_folder	= '?';
		}

		return sprintf('%s %s %s', $column, $operator, $place_folder);
	}
}
