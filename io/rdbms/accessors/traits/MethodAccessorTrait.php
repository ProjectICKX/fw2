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

namespace ickx\fw2\io\rdbms\accessors\traits;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\strings\Strings;
use ickx\fw2\io\rdbms\builder\QueryBuilder;
use ickx\fw2\io\rdbms\builder\ColumnBuilder;
use ickx\fw2\io\rdbms\builder\WhereConditionBuilder;

/**
 * Flywheel2 Model Method Accessor
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait MethodAccessorTrait {
	public static function _bulkAppendStaticCallMethod_FindBy () {
		return [
			[
				'matcher'	=> "/^CountBy([A-Za-z0-9]+)$/",
				'method'	=> [static::class, '_CountBy'],
				'is_last'	=> true,
			],
			[
				'matcher'	=> "/^FindBy([A-Za-z0-9]+)$/",
				'method'	=> [static::class, '_FindBy'],
				'is_last'	=> true,
			],
			[
				'matcher'	=> "/^DistinctBy([A-Za-z0-9]+)$/",
				'method'	=> [static::class, '_DistinctBy'],
				'is_last'	=> true,
			],
		];
	}

	public static function ColumnBuilder ($name = null) {
		return ColumnBuilder::init(static::class, $name);
	}

	public static function QueryBuilder () {
		return QueryBuilder::init(static::class);
	}

	public static function Select () {
		return QueryBuilder::init(static::class, QueryBuilder::FORCE_MODE_SELECT);
	}

	public static function Column ($name = null) {
		return ColumnBuilder::init(static::class, $name);
	}

	public static function From () {
	}

	public static function Where () {
	}

	/**
	 * グループ条件を設定したクエリビルダを返します。
	 *
	 * @param	string|array	$column	ソート対象カラム名または複数のソート対象カラム
	 * @param	string			$order	ソート方向
	 * @return	\ickx\fw2\io\rdbms\builder\QueryBuilder	クエリビルダ
	 */
	public static function GroupBy ($column, $order) {
		return QueryBuilder::init(static::class)->groupBy($column, $order);
	}

	public static function Having () {
	}

	/**
	 * ソート条件を設定したクエリビルダを返します。
	 *
	 * @param	string|array	$column	ソート対象カラム名または複数のソート対象カラム
	 * @param	string			$order	ソート方向
	 * @return	\ickx\fw2\io\rdbms\builder\QueryBuilder	クエリビルダ
	 */
	public static function OrderBy ($column, $order = self::ORDER_ASC) {
		return QueryBuilder::init(static::class)->orderBy($column, $order);
	}

	/**
	 * ソート条件を設定したクエリビルダを返します。
	 *
	 * @param	string|array	$column	ソート対象カラム名または複数のソート対象カラム
	 * @param	string			$order	ソート方向
	 * @return	\ickx\fw2\io\rdbms\builder\QueryBuilder	クエリビルダ
	 */
	public static function Sort ($column, $order = self::ORDER_ASC) {
		return QueryBuilder::init(static::class)->orderBy($column, $order);
	}

	public static function Limit () {
	}

	public static function Offset () {
	}

	/**
	 * 全件を対象に検索を行います。
	 *
	 * 取得対象、検索結果は$optionsで指定します。
	 *
	 * @param	array			$options	検索オプション
	 * [
	 *     'columns'        => array or null    配列で指定したカラム名を指定した場合、指定したカラムのみ取得します。指定が無い場合は「[*]」が指定されたものとみなします。
	 *     'direct_where'   => string           where句以降の記述を直接指定します。
	 *     'where'          => array            WHERE句を指定します。
	 *     'order'          => string or array  ORDER BY句を指定します。ソート方向を指定したい場合は引数を配列とし、二つ目の要素にソート方向を指定してください。
	 *     'group'          => string or array  GROUP BY句を指定します。
	 *     'limit'          => string           LIMIT句を指定します。
	 *     'offset'         => string           OFFSET句を指定します。
	 * ]
	 * @return	\PDOStatement	検索実行後のプリペアドステートメントインスタンス
	 */
	public static function FindAll ($options = []) {
		if (isset($options['conditions'])) {
			$conditions = $options['conditions'];
		} else if (isset($options['where'])) {
			$conditions = [];
			foreach ($options['where'] as $where) {
				$conditions[] = $where instanceof WhereConditionBuilder ? $where : $where[1];
			}
		} else {
			$conditions = [];
		}
		return static::ExecuteQuery(static::CreateSelectQuery($options), $conditions, [], $options);
	}

	public static function CountAll ($options = []) {
		$options['columns'] = ['COUNT (*) as count'];
		if (isset($options['conditions'])) {
			$conditions = $options['conditions'];
		} else if (isset($options['where'])) {
			$conditions = [];
			foreach ($options['where'] as $where) {
				$conditions[] = $where[1];
			}
		} else {
			$conditions = [];
		}
		return static::ExecuteQuery(static::CreateSelectQuery($options), $conditions, [], $options);
	}

	/**
	 *
	 * @param	array	$options	検索オプション
	 * [
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
	public static function CreateSelectQuery ($options) {
		if (isset($options['columns']) && is_array($options['columns'])) {
			$columns = [];
			foreach ($options['columns'] as $column) {
				if (is_array($column)) {
//@TODO $column[1]に対してSUM、COUNTで検証
//@TODO DBごとのエスケープ
					$columns[] = sprintf('%s(%s%s)%s', $column[1], (isset($column[3]) ? sprintf('%s ', $column[3]) : ''), $column[0], isset($column[2]) ? sprintf(' AS %s', $column[2]) : '');
				} else if (is_object($column) && $column instanceof \ickx\fw2\io\rdbms\accessors\Column) {
					$columns[] = $column(static::class);
				} else {
					$columns[] = $column;
				}
			}
			$columns = implode(', ', $columns);
		} else {
			$columns = '*';
		}

		$query = [
			sprintf('SELECT %s FROM %s', $columns, static::GetName()),
		];

		if (isset($options['direct_where'])) {
			$query[] = 'WHERE ' . $options['direct_where'];
		} else if (isset($options['where']) && is_array($options['where'])) {
			$wheres = ['WHERE'];
			$is_not_first = false;
			foreach ($options['where'] as $where) {
				if ($is_not_first) {
					$wheres[] = 'AND';
				}
				$is_not_first = true;

				if ($where instanceof WhereConditionBuilder) {
					$wheres[] = $where();
				} else {
					$value_length = count($where[1]);
					$placeholder = $value_length > 1 ? '('. implode(', ', array_fill(0, $value_length, '?')) .')' : '?';
					$operator = isset($where[2]) ? $where[2] : ($value_length > 1 ? 'in' : '=');

					$wheres[] = sprintf('%s %s %s', $where[0], $operator, $placeholder);
				}
			}
			$query[] = implode(' ', $wheres);
		}

		if (isset($options['group']) && !empty($options['group'])) {
			$query[] = 'GROUP BY';
			$query[] = implode(', ', (array) $options['group']);
		}

		if (isset($options['order']) && is_array($options['order']) && !empty($options['order'])) {
			$order = ['ORDER BY'];
			foreach ($options['order'] as $order_by) {
				$order[] = vsprintf(is_array($order_by) ? '%s %s' : '%s', $order_by);
			}
			$query[] = implode(' ', $order);
		}

		if (isset($options['limit'])) {
			$query[] = 'LIMIT';
			$query[] = $options['limit'];
		}

		if (isset($options['offset'])) {
			$query[] = 'OFFSET';
			$query[] = $options['offset'];
		}

		return implode(' ', $query);
	}

	public static function _FindBy ($name, $arguments, $matches) {
		if (empty($arguments)) {
			throw CoreException::RaiseSystemError('%s::%sメソッドの第一引数が設定されていません。', [static::class, $name]);
		}

		//初期データセット
		$column_list = static::GetColumnList();
		$table_name = static::GetName();

		//特殊ルール適用
		if ($matches[1] === 'PKey') {
			$matches[1] = static::GetPkeys()[0];
		}

		//Find target set
		$target_column_name = Strings::ToSnakeCase($matches[1]);
		if (!isset($column_list[$target_column_name])) {
			throw CoreException::RaiseSystemError('存在しないカラムで検索しようとしました。%s.%s', [$table_name, $target_column_name]);
		}

		//target value set and adjust operator
		$condition	= $arguments[0];
		$operator	= static::_AdjustOperator((isset($arguments[1]) ? $arguments[1] : '='), $condition);

		$condition		= (array) $condition;
		$placeholder	= '?';
		if ($operator === 'IN' || $operator === 'NOT IN') {
			$placeholder	= sprintf('(%s)', implode(',', array_fill(0, count($condition), '?')));
		}

		$wheres = [
			sprintf('%s %s %s', $target_column_name, $operator, $placeholder),
		];

		//Adjust columns
		if (isset($arguments[2])) {
			$columns = [];
			foreach ((array) $arguments[2] as $idx => $column_name) {
				if (!isset($column_list[$column_name])) {
					throw CoreException::RaiseSystemError('存在しないカラムを指定されました。%s.%s', [$table_name, $target_column_name]);
				}
				$columns[$idx] = $column_list[$column_name];
			}
		} else {
			$columns = ['*'];
		}

		//option set
		$option_wheres = [];
		if (isset($arguments[3])) {
			$options = $arguments[3];

			if (isset($options['where'])) {
				foreach ((array) $options['where'] as $idx => $where) {
					$option_column_name = array_shift($where);
					if (!isset($column_list[$option_column_name])) {
						throw CoreException::RaiseSystemError('存在しないカラムを指定されました。%s.%s', [$table_name, $option_column_name]);
					}

					$option_value = array_shift($where);
					if ($option_value === null) {
						CoreException::RaiseSystemError('カラムに値が指定されていません。column_name:%s', [$option_column_name]);
					}

					$option_operator	= array_shift($where);
					$option_operator	= $option_operator === null ? '=' : $option_operator;
					$option_operator	= static::_AdjustOperator($option_operator, $option_value);

					$option_placeholder	= '?';

					if ($option_operator === 'IN' || $option_operator === 'NOT IN') {
						$option_value		= (array) $option_value;
						$option_placeholder	= sprintf('(%s)', implode(',', array_fill(0, count($option_value), '?')));
					}

					$wheres[] = sprintf('%s %s %s', $option_column_name, $option_operator, $option_placeholder);
					$condition[] = $option_value;
				}
			}
		}

		//query create
		$query = sprintf('SELECT %s FROM %s WHERE %s ', implode(', ', $columns), $table_name, implode(' AND ', $wheres));

		//execute
		$stmt = static::ExecuteQuery($query, $condition, []);
		if (isset($options['post_execution'][0]) && is_callable($options['post_execution'][0])) {
			$post_execution = $options['post_execution'];
			$post_execution[1] = isset($post_execution[1]) ? (array) $post_execution[1] : [];
			array_unshift($post_execution[1], $stmt);
			return call_user_func_array($post_execution[0], $post_execution[1]);
		}

		return $stmt;
	}

	public static function _AdjustOperator ($operator, $condition) {
		//@TODO Get From DBDrivers
		$enable_operators = [
			'='				=> '=',
			'>'				=> '>',
			'<'				=> '<',
			'>='			=> '>=',
			'<='			=> '<=',
			'<>'			=> '<>',
			'!='			=> '!=',
			'IN'			=> 'IN',
			'NOT IN'		=> 'NOT IN',
			'IS NOT NULL'	=> 'IS NOT NULL',
			'IS NOT'		=> 'IS NOT',
			'IS NULL'		=> 'IS NULL',
			'LIKE'			=> 'LIKE',
			'NOT LIKE'		=> 'NOT LIKE',
		];

		$operator = strtoupper($operator);
		if (!isset($enable_operators[$operator])) {
			CoreException::RaiseSystemError('利用出来ないオペレータを指定されました。operator:%s', $operator);
		}
		$operator = $enable_operators[$operator];

		//Find target value set
		if (is_array($condition)) {
			switch ($operator) {
				case '=':
					$operator = 'IN';
					break;
				case '<>':
				case '!=':
					$operator = 'NOT IN';
					break;
				default:
					CoreException::RaiseSystemError('複数の値を指定されていますが、オペレータが単一の値用のものとなっています。operator:%s', [$operator]);
			}
		}

		return $operator;
	}

	public static function _CountBy ($name, $arguments, $matches) {
		$target_column_name = Strings::ToSnakeCase($matches[1]);
		$table_name = static::GetName();
		$column_list = static::GetColumnList();

		if (!isset($column_list[$target_column_name])) {
			throw CoreException::RaiseSystemError('存在しないカラムを指定されました。%s.%s', [$table_name, $target_column_name]);
		}
		$query = sprintf('SELECT COUNT(%s) as count FROM %s', $target_column_name, $table_name);

		return static::ExecuteQuery($query);
	}

	public static function _DistinctBy ($name, $arguments, $matches) {
		$target_column_name = Strings::ToSnakeCase($matches[1]);
		$table_name = static::GetName();
		$column_list = static::GetColumnList();

		if (!isset($column_list[$target_column_name])) {
			throw CoreException::RaiseSystemError('存在しないカラムを指定されました。%s.%s', [$table_name, $target_column_name]);
		}
		$query = sprintf('SELECT DISTINCT %s FROM %s', $target_column_name, $table_name);

		return static::ExecuteQuery($query);
	}
}
