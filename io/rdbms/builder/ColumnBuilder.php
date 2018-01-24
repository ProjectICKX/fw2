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

namespace ickx\fw2\io\rdbms\builder;

/**
 * Flywheel2 RDBMS ColumnBuilder
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ColumnBuilder {
	protected $modelClass	= null;

	protected $distinct		= false;

	protected $name			= null;

	protected $alias		= null;

	protected $function		= null;

	protected $functionArgs	= [];

	protected function __construct ($called_class) {
		$this->modelClass	= $called_class;
	}

	public static function init ($called_class, $name = null, $options = null) {
		$columnBuilder	= new static($called_class);
		$columnBuilder->name($name);

		is_null($alias = $options['as'] ?? $options['alias'] ?? null) ?: $columnBuilder->alias($alias);

		return $columnBuilder;
	}

	public function distinct ($use_distinct) {
		$this->distinct	 = $use_distinct;
		return $this;
	}

	public function name ($name) {
		$this->name	= $name;
		return $this;
	}

	public function alias ($alias) {
		$this->alias	= $alias;
		return $this;
	}

	public function functions ($functions) {
		$this->functions	= $functions;
		return $this;
	}

	public function __invoke () {
		//@TODO `をDBDriverから取得するようにする。$this->enclosure など

		$format	= ['`%s`'];
		$values	= [implode('`.`', array_filter([$this->modelClass::GetDatabaseName(), $this->modelClass::GetName(), $this->name]))];

		if (!is_null($this->alias)) {
			$format[]	= 'AS `%s`';
			$values[]	= $this->alias;
		}

		return vsprintf(implode(' ', $format), $values);
	}
}
