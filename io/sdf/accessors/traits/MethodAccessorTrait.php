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

namespace ickx\fw2\io\sdf\accessors\traits;

use ickx\fw2\vartype\arrays\Arrays;

use ickx\fw2\core\exception\CoreException;

use ickx\fw2\vartype\strings\Strings;

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
				'method'	=> [get_called_class(), '_CountBy'],
				'is_last'	=> true,
			],
			[
				'matcher'	=> "/^FindBy([A-Za-z0-9]+)$/",
				'method'	=> [get_called_class(), '_FindBy'],
				'is_last'	=> true,
			],
			[
				'matcher'	=> "/^DistinctBy([A-Za-z0-9]+)$/",
				'method'	=> [get_called_class(), '_DistinctBy'],
				'is_last'	=> true,
			],
		];
	}

	public static function FindAll ($options = []) {
		$conditions = isset($options['conditions']) ? $options['conditions'] : [];
		return static::ExecuteQuery(self::CreateSelectQuery($options), $conditions);
	}

	public static function CountAll ($options = []) {
		$options['columns'] = ['COUNT (*) as count'];
		$conditions = isset($options['conditions']) ? $options['conditions'] : [];
		return static::ExecuteQuery(self::CreateSelectQuery($options), $conditions);
	}

	public static function CreateSelectQuery ($options) {
		if (isset($options['columns']) && is_array($options['columns'])) {
			$columns = implode(', ', $options['columns']);
		} else {
			$columns = '*';
		}

		$query = [
			sprintf('SELECT %s FROM %s', $columns, static::GetName()),
		];
/*
		if (isset($options['where']) && is_array($options['where'])) {
			$wheres = ['WHERE'];
			foreach ($options['where'] as $where) {
				$wheres[] = vsprintf(is_array($order_by) ? '%s %s' : '%s', $order_by);
			}
			$query[] = implode(' ', $wheres);
		}
*/
		if (isset($options['direct_where'])) {
			$query[] = 'WHERE ' . $options['direct_where'];
		}

		if (isset($options['order']) && is_array($options['order'])) {
			$order = ['ORDER BY'];
			foreach ($options['order'] as $order_by) {
				$order[] = vsprintf(is_array($order_by) ? '%s %s' : '%s', $order_by);
			}
			$query[] = implode(' ', $order);
		}

		if (isset($options['group']) && is_array($options['group'])) {
			$query[] = 'GROUP BY';
			$query[] = implode(', ', $order);
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
			throw CoreException::RaiseSystemError('%s::%sメソッドの第一引数が設定されていません。', [get_called_class(), $name]);
		}
		$condition	= [[$arguments[0]]];
		$options	= isset($arguments[1]) ? $arguments[1] : null;
		$keys		= isset($arguments[2]) ? $arguments[2] : null;

		$target_column_name = Strings::ToSnakeCase($matches[1]);
		$table_name = static::GetName();
		$column_list = static::GetColumnList();

		if (!isset($column_list[$target_column_name])) {
			throw CoreException::RaiseSystemError('存在しないカラムを指定されました。%s.%s', [$table_name, $target_column_name]);
		}
		$query = sprintf('SELECT * FROM %s WHERE %s IN (?)', $table_name, $target_column_name);

		if ($keys === null) {
			return static::ExecuteQuery($query, $condition);
		}
		return static::ExecuteQuery($query, $condition);
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
