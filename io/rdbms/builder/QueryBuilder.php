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
	public const FORCE_MODE_SELECT	= 'select';
	public const FORCE_MODE_UPDATE	= 'update';
	public const FORCE_MODE_INSERT	= 'insert';
	public const FORCE_MODE_DELETE	= 'delete';

	public const ORDER_ASC	= 'ASC';
	public const ORDER_DESC	= 'DESC';

	protected $forceMode		= null;

	protected $modelClass		= null;

	protected $columns			= [];

	protected $whereConditions	= [];

	protected $orderConditions	= [];

	protected function __construct ($called_class, $force_mode = null) {
		$this->forceMode	= $force_mode;
		$this->modelClass	= $called_class;
	}

	public static function init ($called_class, $force_mode	= null) {
		return new static($called_class, $force_mode);
	}

	public function column ($columns, $options = []) {
		foreach ((array) $columns as $column) {
			$this->columns[]	= $column instanceof ColumnBuilder ? $column : ColumnBuilder::init($this->modelClass, $column, $options);
		}
		return $this;
	}

	public function from () {
	}

	public function where (...$condition) {
		if (is_array($condition[0])) {
			$targets	= [];
			foreach ($condition as $target) {
				$this->whereConditions[]	= $target instanceof WhereConditionBuilder ? $target : WhereConditionBuilder::init($this->modelClass)->column($target[0])->value($target[1])->operator($target[2] ?? '=');
			}
		} else if (isset($condition[2])) {
			$this->whereConditions[]	= WhereConditionBuilder::init($this->modelClass)->column($condition[0])->value($condition[1])->operator($condition[2]);
		} else if (isset($condition[1])) {
			$this->whereConditions[]	= WhereConditionBuilder::init($this->modelClass)->column($condition[0])->value($condition[1])->operator('=');
		} else {
			throw new \Exception('引数のパターンがマッチしません。');
		}

		return $this;
	}

	public function groupBy ($column) {
	}

	public function having () {
	}

	public function sort ($column, $order = self::ORDER_ASC) {
		$this->orderConditions	= array_merge($this->orderConditions, is_array($column) ? $column : [$column, $order]);
		return $this;
	}

	public function orderBy ($column, $order = self::ORDER_ASC) {
		$this->orderConditions	= array_merge($this->orderConditions, is_array($column) ? $column : [$column, $order]);
		return $this;
	}

	public function limit () {
	}

	public function offset () {
	}

	public function intoOutFile () {
	}

	public function forUpdate () {
	}

	public function setAttribute () {
	}

	public function setFetchMode () {
	}

	public function getOoption ($fetch_type = null, ...$fetch_type_options) {
		$columns	= [];
		foreach ($this->columns as $idx => $column) {
			$columns[$idx] = $column();
		}
		!empty($columns) ?: ($columns = null);

		$options	= [
				'columns'		=> $columns,
				'direct_where'	=> null,
				'where'			=> empty($this->whereConditions) ? null : $this->whereConditions,
				'order'			=> empty($this->orderConditions) ? null : $this->orderConditions,
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

		return $options;
	}

	public function build ($fetch_type = null, ...$fetch_type_options) {
		return $this->modelClass::CreateSelectQuery($this->getOoption($fetch_type, ...$fetch_type_options));
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
		return $this->modelClass::FindAll($this->getOoption($fetch_type, ...$fetch_type_options));
	}

	public function exec () {
	}

	public function fetch () {
	}

	public function fetchAll ($fetch_type = null, ...$fetch_type_options) {
		return $this->query($fetch_type, ...$fetch_type_options)->fetchAll();
	}
}
