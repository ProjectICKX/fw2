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
 * @category	Flywheel2 demo
 * @package		commons
 * @author		wakaba
 * @copyright	Copyright 2012, Project ICKX. (http://www.ickx.jp/)
 * @license		require consultation
 * @varsion		0.0.1
 */

namespace ickx\fw2\extensions\twig;

class Twig_Extension_Filter extends \Twig_Extension {
	use	traits\UtilityTrait;

	/**
	 * クラス名を返します。
	 *
	 * @return	string	クラス名
	 */
	public function getName () {
		return __CLASS__;
	}

	/**
	 * 拡張として登録する関数のリストを返します。
	 *
	 * @return	array	拡張として登録する関数のリスト
	 */
	public function getFilters () {
		return [
			new \Twig_SimpleFilter('del_lf',		[$this, 'deleteLf']),
			new \Twig_SimpleFilter('default',		[$this, 'customDefault']),
			new \Twig_SimpleFilter('default_se',	[$this, 'customDefaultStringEmpty']),
			new \Twig_SimpleFilter('str_search',	[$this, 'strSearch']),
			new \Twig_SimpleFilter('str_truncate',	[$this, 'strTruncate']),
			new \Twig_SimpleFilter('empty',			[$this, 'isEmpty']),
			new \Twig_SimpleFilter('strtotime',		[$this, 'strToTime']),
			new \Twig_SimpleFilter('var_dump',		'var_dump'),
			new \Twig_SimpleFilter('back_fill',		[$this, 'backFill']),

			new \Twig_SimpleFilter('vsprintf',		[$this, 'vsprintf']),

			new \Twig_SimpleFilter('sort',			[$this, 'sort']),
			new \Twig_SimpleFilter('rsort',			[$this, 'rSort']),
			new \Twig_SimpleFilter('ksort',			[$this, 'kSort']),
			new \Twig_SimpleFilter('krsort',		[$this, 'krSort']),

			new \Twig_SimpleFilter('count',			[$this, 'count']),

			new \Twig_SimpleFilter('self_combine',	[$this, 'selfCombine']),

			new \Twig_SimpleFilter('shift',			[$this, 'shift']),
			new \Twig_SimpleFilter('current',		'current'),
			new \Twig_SimpleFilter('key',			'key'),

			new \Twig_SimpleFilter('adjust',		[$this, 'adjust']),

			new \Twig_SimpleFilter('gmdate',		[$this, 'gmdate']),

			new \Twig_SimpleFilter('str_to_date',	[$this, 'strToDate']),

			//error
			new \Twig_SimpleFilter('adjust_error',	[$this, 'adjustError']),

			new \Twig_SimpleFilter('toggle',		[$this, 'toggle']),

			new \Twig_SimpleFilter('route_toggle',	[$this, 'routeToggle']),

			new \Twig_SimpleFilter('exit',			'exit'),

			new \Twig_SimpleFilter('str_pad',		'str_pad'),
			new \Twig_SimpleFilter('to_array',		[$this, 'toArray']),
			new \Twig_SimpleFilter('to_text',		[$this, 'toText']),
			new \Twig_SimpleFilter('camelize',		[$this, 'camelize']),

			new \Twig_SimpleFilter('method',			[$this, 'callMethod']),

			new \Twig_SimpleFilter('find_as_key',			[$this, 'findAsKey']),
			new \Twig_SimpleFilter('find_by_class_const',	[$this, 'findByClassConst']),

			new \Twig_SimpleFilter('iterate_by_map_method',	[$this, 'iterateByMapMethod']),

			new \Twig_SimpleFilter('strtoupper',	'strtoupper'),
			new \Twig_SimpleFilter('strtolower',	'strtolower'),

			//==============================================
			// is_*
			//==============================================
			new \Twig_SimpleFilter('is_array',				'is_array'),
			new \Twig_SimpleFilter('is_array_accessable',	[$this, 'isArrayAccessable']),
			new \Twig_SimpleFilter('is_bool',				'is_bool'),
			new \Twig_SimpleFilter('is_callable',			'is_callable'),
			new \Twig_SimpleFilter('is_double',				'is_double'),
			new \Twig_SimpleFilter('is_float',				'is_float'),
			new \Twig_SimpleFilter('is_int',				'is_int'),
			new \Twig_SimpleFilter('is_integer',			'is_integer'),
			new \Twig_SimpleFilter('is_iterable',			'is_iterable'),
			new \Twig_SimpleFilter('is_long',				'is_long'),
			new \Twig_SimpleFilter('is_null',				'is_null'),
			new \Twig_SimpleFilter('is_numeric',			'is_numeric'),
			new \Twig_SimpleFilter('is_object',				'is_object'),
			new \Twig_SimpleFilter('is_real',				'is_real'),
			new \Twig_SimpleFilter('is_resource',			'is_resource'),
			new \Twig_SimpleFilter('is_scalar',				'is_scalar'),
			new \Twig_SimpleFilter('is_string',				'is_string'),
			new \Twig_SimpleFilter('isset',					'isset'),

			//==============================================
			// explode
			//==============================================
			new \Twig_SimpleFilter('implode',		[$this, 'implode']),
			new \Twig_SimpleFilter('explode',		[$this, 'explode']),

			//==============================================
			//error support
			//==============================================
			new \Twig_SimpleFilter('filter_disable_error_set',	[$this, 'filterDisableErrorSet']),
		];
	}

	//==============================================
	//twig filter拡張
	//==============================================
	/**
	 * 文字列中の改行コードを全て削除します。
	 *
	 * @param	string	$value	フィルタ対象
	 * @return	string	フィルタ後の文字列
	 */
	public function deleteLf ($value) {
		return str_replace(["\r", "\n"], '', $value);
	}

	/**
	 * デフォルトのデフォルトフィルタを拡張します。
	 *
	 * @param	mixed	$value		有効な値か調べる値
	 * @param	mixed	$default	値が無効な場合に使う値
	 * @param	string	$option		オプション
	 * @return	mixed	$valueが有効な場合は$value、そうでない場合は$default
	 */
	public function customDefault ($value, $default, $option = null) {
		switch ($option) {
			case 'twig':
			case 'default':
				return $value ?: $default;
			case 'empty':
				return empty($value) ? $default : $value;
			case 'string':
				return ($value !== '' && !is_null($value)) ? $value : $default;
			case 'null':
			default:
				return is_null($value) ? $default : $value;
		}
	}

	/**
	 * 空文字用のデフォルトフィルタを定義します。
	 *
	 * @param	mixed	$value		有効な値か調べる値
	 * @param	mixed	$default	値が無効な場合に使う値
	 * @return	mixed	$valueが有効な場合は$value、そうでない場合は$default
	 */
	public function customDefaultStringEmpty ($value, $default) {
		return ($value !== '' && !is_null($value)) ? $value : $default;
	}

	/**
	 * 文字列に文字が含まれているか調べます。
	 *
	 * @param	string	$haystack	文字列
	 * @param	string	$needle		検索する文字列
	 * @param	int		$offset		検索の開始位置
	 * @return	bool	文字列が含まれている場合true, そうでない場合false
	 */
	public function strSearch ($haystack, $needle, $offset = 0) {
		return mb_strpos($haystack, $needle, $offset) !== false;
	}

	public function strToTime ($var) {
		if (is_array($var)) {
			switch (count($var)) {
				case 1:
					$pattern = '%s';
					break;
				case 2:
					$pattern = '%s/%s';
					break;
				case 3:
					$pattern = '%s/%s/%s';
					break;
				case 4:
					$pattern = '%s/%s/%s %s';
					break;
				case 5:
					$pattern = '%s/%s/%s %s:%s';
					break;
				case 6:
					$pattern = '%s/%s/%s %s:%s:%s';
					break;

			}
			$var = vsprintf($pattern, $var);
		}
		return strtotime($var);
	}

	public function backFill ($val, $width = 80, $pad_string = ' ') {
		return sprintf('%s%s', $val, str_repeat($pad_string, $width - mb_strwidth($val)));
	}

	public function sort ($value) {
		sort($value);
		return $value;
	}

	public function rSort ($value) {
		rsort($value);
		return $value;
	}

	public function kSort ($value) {
		ksort($value);
		return $value;
	}

	public function krSort ($value) {
		krsort($value);
		return $value;
	}

	public function count ($value) {
		return count($value);
	}

	public function selfCombine ($value) {
		return array_combine((array) $value, (array) $value);
	}

	public function filterDisableErrorSet ($errors) {
		foreach ($errors as $idx => $error_list) {
			foreach ($error_list as $no => $error) {
				if (isset($error['options']['disable_box_message']) && $error['options']['disable_box_message'] === true) {
					unset($errors[$idx][$no]);
				}
			}
			if (empty($errors[$idx])) {
				unset($errors[$idx]);
			}
		}
		return $errors;
	}

	public function adjust ($label, $condition, $default = null) {
		if (is_array($label)) {
			return $label[$condition] ?? $default;
		}
		return $condition ? $label : $default;
	}

	public function adjustError ($label, $name, $default = null) {
		return Arrays::ExistsLowest(DI::GetClassVar('render'), ['error', $name]) ? $label : $default;
	}

	public function gmdate ($ts, $format) {
		return gmdate($format, $ts);
	}

	public function toggle ($text, $switch = false, $default = '') {
		return $switch ? $text : $default;
	}

	public function routeToggle ($text, $route = null, $path_param = [], $default = '') {
		$render = DI::GetClassVar('render');

		$controller_status = $render['controller'] === str_replace('/', '_', $route[0] ?? $route['controller'] ?? 'index');

		$action_status = false;
		foreach ((array) ($route[1] ?? $route['action'] ?? 'index') as $action) {
			if ($render['action'] === str_replace('/', '_', $action)) {
				$action_status = true;
				break;
			}
		}

		$route_status = true;
		foreach ($path_param as $path_param_key => $path_param_data) {
			if (!isset($render['route'][$path_param_key]) || $render['route'][$path_param_key] != $path_param_data) {
				$route_status = false;
				break;
			}
		}

		return $controller_status && $action_status && $route_status ? $text : $default;
	}

	public function vsprintf ($format, $values) {
		return vsprintf($format, $values ?? []);
	}

	public function toArray ($value) {
		return is_array($value) ? $value : [$value];
	}

	public function camelize ($value) {
		return ucFirst(str_replace('-', '', $value));
	}

	public function toText ($value, $null_text = false) {
		switch (gettype($value)) {
			case 'boolean':
				return $value ? 'true' : 'false';
				break;
			case 'integer':
				return (string) $value;
				break;
			case 'double':
				return (string) $value;
				break;
			case 'string':
				return $value;
				break;
			case 'array':
				return empty($value) ? '[]' : implode(', ', $value);
				break;
			case 'object':
				return 'object';
				break;
			case 'resource':
				return 'resource';
				break;
			case 'NULL':
				return $null_text ? 'null' : '';
				break;
			case 'unknown type':
				return 'unknown type';
				break;
		}
	}

	public function strToDate ($value, $format) {
		return date($format, strtotime($value));
	}

	public function callMethod ($instance, $method_name, ...$args) {
		return $instance->$method_name(...$args);
	}

	public function findAsKey ($key, $data) {
		return is_object($data) ? ($data->$key ?? null) : ($data[$key] ?? null);
	}

	public function findByClassConst ($target_key, $class_path, $const_name = null, $default_value = null) {
		if (false !== $separate_pos = strpos($class_path, '::')) {
			$class_name = substr($class_path, 0, $separate_pos);
			$const_name = substr($class_path, $separate_pos + 2);
		} else {
			$class_name = $class_path;
			$default_value = $const_name;
		}

		$target_class_path = \ickx\fw2\extensions\twig\Twig_Extension_Store::get('use_class', $class_name, $class_name);

		if (!class_exists($target_class_path)) {
			throw new \ErrorException(sprintf('対象のクラスが見つかりませんでした。class path:%s (<= %s <= %s)', $target_class_path, $class_name, $class_path));
		}

		$define_naem = $target_class_path . '::'. $const_name;

		if (!defined($define_naem)) {
			throw new \ErrorException(sprintf('対象のクラス定数が見つかりませんでした。class const:%s', $define_naem));
		}

		return constant($define_naem)[$target_key] ?? $default_value;
	}

	public function iterateByMapMethod ($object, $method_name, $use_key = false) {
		if (!method_exists($object, $method_name)) {
			return $object;
		}

		if (!is_array($key_list = $object->$method_name())) {
			return $object;
		}

		$can_array_access = $object instanceof \ArrayAccess;
		foreach ($use_key ? array_keys($key_list) : array_values($key_list) as $key) {
			yield $can_array_access ? ($object[$key] ?? null) : ($object->$key ?? null);
		}
	}

	public function implode ($value, $separator) {
		return implode($separator, $value);
	}

	public function explode ($value, $separator) {
		return explode($separator, $value);
	}

	public function isArrayAccessable ($value) {
		return is_array($value) || $value instanceof \ArrayAccess;
	}
}
