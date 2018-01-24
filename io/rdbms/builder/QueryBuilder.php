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
 * Flywheel2 RDBMS QueryBuilder
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 *
 * @see			\ickx\fw2\io\rdbms\accessors\traits\MethodAccessorTrait::CreateSelectQuery
 */
class QueryBuilder {
	public const ORDER_ASC	= 'ASC';
	public const ORDER_DESC	= 'DESC';

	protected $modelClass	= null;

	protected $columns		= [];

	protected $sortOrder	= [];

	protected function __construct ($called_class) {
		$this->modelClass	= $called_class;
	}

	public static function init ($called_class) {
		return new static($called_class);
	}

	public function column ($column, $options = []) {
		$this->columns[]	= ColumnBuilder::init($this->modelClass, $column, $options);
		return $this;
	}

	public function where ($column, $operator = '=') {
	}

	public function group ($column) {
	}

	public function sort ($column, $order = self::ORDER_ASC) {
		$this->sortOrder	= is_array($column) ? $column : [$column, $order];
		return $this;
	}

	/**
	 *
	 * @param	array	$options	検索オプション
	 * [
	 *     'distinct'       => bool
	 *     'columns'        => array or null    配列で指定したカラム名を指定した場合、指定したカラムのみ取得します。指定が無い場合は「[*]」が指定されたものとみなします。
	 *     'direct_where'   => string           where句以降の記述を直接指定します。
	 *     'where'          => array            WHERE句を指定します。
	 *     'order'          => string or array  ORDER BY句を指定します。ソート方向を指定したい場合は引数を配列とし、二つ目の要素にソート方向を指定してください。
	 *     'group'          => string or array  GROUP BY句を指定します。
	 *     'limit'          => string           LIMIT句を指定します。
	 *     'offset'         => string           OFFSET句を指定します。
	 * ]
	 * @return string	構築したクエリ
	 */
	public function query ($fetch_type = null, ...$fetch_type_options) {
		$Columns	= [];
		foreach ($this->columns as $idx => $column) {
			$columns[$idx] = $column();
		}
		!empty($columns) ?: ($columns = null);

		$options	= [
			'columns'		=> $columns,
			'direct_where'	=> null,
			'where'			=> null,
			'order'			=> empty($this->sortOrder) ? null : $this->sortOrder,
			'group'			=> null,
			'limit'			=> null,
			'offset'		=> null,
		];

		if (!is_null($fetch_type)) {
			$options[\PDO::ATTR_DEFAULT_FETCH_MODE] = $fetch_type;
			switch ($fetch_type) {
				case \PDO::FETCH_COLUMN:
					$options['colno']	= $fetch_type_options[0] ?? 0;
					break;
				case \PDO::FETCH_CLASS:
					$options['classname']	= $fetch_type_options[0] ?? null;
					$options['ctorargs']	= $fetch_type_options[1] ?? null;
					break;
				case \PDO::FETCH_INTO:
					$options['object']	= $fetch_type_options[0] ?? null;
					break;
			}
		}

		return $this->modelClass::FindAll($options);
	}

	public function exec () {
	}
}
