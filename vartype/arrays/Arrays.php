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
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */

namespace ickx\fw2\vartype\arrays;

/**
 * 配列ユーティリティです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */
class Arrays {
	/**
	 * 配列に値がある場合は値を、無い場合はデフォルト値を返します。
	 *
	 * @param	mixed	$array			値の取得元配列
	 * @param	mixed	$name			キー名
	 * @param	mixed	$default_value	デフォルト値
	 */
	public static function AdjustValue ($array, $name, $default_value = null) {
		if (is_array($array) || $array instanceof \Traversable) {
			foreach ((array) $name as $name) {
				if (isset($array[$name])) {
					return $array[$name];
				}
			}
		}
		return $default_value;
	}

	/**
	 * 渡された変数が配列の場合はそのまま配列として、配列以外の場合は配列にして返します。
	 *
	 * @param	mixed	$array	配列化したい変数
	 * @param unknown_type $call_back
	 * @return Ambigous <unknown, array, mixed>
	 */
	public static function AdjustArray ($array, $call_back = null) {
		if ($call_back === null) {
			return is_array($array) ? $array : (array) $array;
		}
		return (is_array($array)) ? $array : call_user_func($call_back, $array);
	}

	/**
	 * 変数がTraversableな値かどうか判定します。
	 *
	 * ※Traversableな値とはたとえばforeachで使えるものです。
	 *
	 * @param	Traversable	$array	判定する値
	 * @return	bool		Traversableな値の場合はbool TRUE、そうでない場合はbool FALSE
	 */
	public static function IsTraversable ($array) {
		return is_array($array) || $array instanceof \Traversable;
	}

	/**
	 * 配列のうち、指定したキーの要素のみを結合して返します。
	 *
	 * @param	string	$glue				連結値
	 * @param	array	$array				対象の配列
	 * @param	array	$target_key_list	結合対象とするキーのリスト
	 * @return	string	結合された値
	 */
	public static function SelectImplode ($glue, $array, $target_key_list) {
		$tmp = [];
		foreach ($target_key_list as $key) {
			$tmp[] = $array[$key];
		}
		return implode($glue, $tmp);
	}

	/**
	 * 配列内の要素をbit値として合計します。
	 *
	 * @param	array	$bit_list	合計する配列
	 * @return	float	合計された値
	 */
	public static function BitSum ($bit_list) {
		if (!is_array($bit_list)) {
			return $bit_list;
		}

		$bit_sum = 0;
		foreach ($bit_list as $bit) {
			$bit_sum |= $bit;
		}

		return $bit_sum;
	}

	/**
	 * 配列の中にキーが存在するか検証します。
	 *
	 * @param	array	$array	検索対象の配列
	 * @param	mixed	$key	検索するキー
	 * @return	bool	対象のキーが存在する場合はtrue、そうでない場合はfalse
	 */
	public static function KeyExists ($array, $key) {
		return isset($array[$key]) || array_key_exists($key, $array);
	}

	/**
	 * 指定した階層の配列にキーが存在するか検証します。
	 *
	 * @param	array	$array	検索対象の配列
	 * @param	mixed	$key	検索するキー
	 * @return	bool	対象のキーが存在する場合はtrue、そうでない場合はfalse
	 */
	public static function ExistsLowest ($array, $keys) {
		foreach ((array) $keys as $key) {
			if (isset($array[$key])) {
				$array = $array[$key];
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * 指定された階層にある値でソートします。
	 *
	 * @param	array	$array	ソートする配列
	 * @param	mixed	$name	階層
	 * @return	array	ソートされた配列
	 */
	public static function SortLowest ($array, $name) {
		uasort(
			$array,
			function ($current, $next) use ($name) {
				return strnatcmp(static::GetLowest($current, $name), static::GetLowest($next, $name));
			}
		);
		return $array;
	}

	/**
	 * 指定された階層にある値で逆順ソートします。
	 *
	 * @param	array	$array	ソートする配列
	 * @param	mixed	$name	階層
	 * @return	array	ソートされた配列
	 */
	public static function ReverseSortLowest ($array, $name) {
		uasort(
			$array,
			function ($current, $next) use ($name) {
				return -1 * strnatcmp(static::GetLowest($current, $name), static::GetLowest($next, $name));
			}
		);
		return $array;
	}

	/**
	 * 指定された階層にある値を削除します。
	 *
	 * @param	array	$array	削除する値を持つ配列
	 * @param	mixed	$keys	階層
	 * @return	array	値を削除された配列
	 */
	public static function RemoveLowest ($array, $keys) {
		$keys = (array) $keys;
		$target_key = array_pop($keys);
		$tmp =& $array;
		foreach ($keys as $key) {
			$tmp =& $tmp[$key];
		}
		unset($tmp[$target_key]);
		return $array;
	}

	/**
	 * 指定された階層にある値を取得します。
	 *
	 * @param	array	$array	配列
	 * @param	mixed	$keys	階層
	 * @return	mixed	指定された改造にある値
	 */
	public static function GetLowest ($array, $keys) {
		foreach ((array) $keys as $key) {
			if (isset($array[$key])) {
				$array = $array[$key];
			} else {
				return null;
			}
		}
		return $array;
	}

	/**
	 * 指定された階層にある値を設定します。
	 *
	 * @param	array	$array	配列
	 * @param	mixed	$keys	階層
	 * @return	array	設定後の配列
	 */
	public static function SetLowest ($array, $keys, $value) {
		$keys = (array) $keys;
		if (empty($array)) {
			$tmp =& $array;
		} else {
			$tmp =& $array[array_shift($keys)];
		}

		foreach ($keys as $key) {
			if ($tmp instanceof \ickx\fw2\vartype\arrays\LazyArrayObject) {
				if ($tmp->$key === null) {
					$tmp->$key = null;
				}
				$tmp =& $tmp->$key;
			} else {
				if (!isset($tmp[$key])) {
					$tmp[$key] = null;
				}
				$tmp =& $tmp[$key];
			}
		}
		$tmp = $value;
		return $array;
	}

	/**
	 * 配列にあるキーの値を元に配列を階層化して返します。
	 *
	 * @param	array		$values	階層化する配列
	 * @param	array		$keys	階層化の元にするキー名の配列
	 * @param	callable	$filter	最終階層を詰める際に利用するコールバック
	 * @return	array		階層化された配列
	 */
	public static function MultiColumn ($values, $keys = [], $filter = null) {
		if (is_array($keys) && empty($keys) || $keys === null) {
			return $values;
		}

		$enable_filter = $filter !== null && is_callable($filter);

		$keys = (array) $keys;
		$ret = [];

		foreach ($values as $row) {
			$tmp =& $ret;
			foreach ($keys as $key) {
				$tmp =& $tmp[$row[$key]];
			}
			if ($enable_filter) {
				$row = $filter($row);
			}
			$tmp = $row;
		}

		return $ret;
	}

	/**
	 * 配列から指定したキーに紐付く値のみを抽出し返します。
	 *
	 * 値が存在しない場合は値がnullとなります。
	 *
	 * @param array $array
	 * @param array $keys
	 */
	public static function GetElementsByKeys (array $array, array $keys) {
		return array_filter(array_combine($keys, array_map(function ($key) use ($array) {
			return isset($array[$key]) ? $array[$key] : null;
		}, $keys)));
	}

	/**
	 * 再帰的に空要素を削除して配列を縮小します。
	 *
	 * @param	array		$array		空要素を削除する配列
	 * @param	callable	$call_back	独自の空判定処理
	 * @return	空要素を削除された配列
	 */
	public static function RecursiveFilter ($array, $call_back = null) {
		if (!is_callable($call_back)) {
			foreach ($array as $idx => $element) {
				if (is_array($element)) {
					$array[$idx] =  static::RecursiveFilter($element, $call_back);
					if (empty($array[$idx])) {
						unset($array[$idx]);
					}
				}
				if ($element === null) {
					unset($array[$idx]);
				}
			}
		} else {
			foreach ($array as $idx => $element) {
				if (is_array($element)) {
					$array[$idx] =  static::RecursiveFilter($element, $call_back);
					if (empty($array[$idx])) {
						unset($array[$idx]);
					}
				}
				if ($call_back($element, $idx)) {
					unset($array[$idx]);
				}
			}
		}
		return $array;
	}

	/**
	 * キーを維持したまま配列をシャッフルします。
	 *
	 * @param	array	$array	シャッフルする配列
	 * @return	array	シャッフルされた配列
	 */
	public static function Shuffle ($array) {
		$result = [];
		foreach(array_keys($array) as $key){
			$result[$key] = $array[$key];
		}
		return $result;
	}

	/**
	 * 多次元配列となっている配列のキーを key[key] のスタイルにし平坦化します。
	 *
	 * @param array $array
	 * @param array $keys
	 * @return array 平坦化した配列
	 */
	public static function keyFlattening($array, $keys = []) {
		if (is_array($array)) {
			$result = [];
			foreach ($array as $key => $value) {
				$tmp_keys = $keys;
				$tmp_keys [] = $key;
				$result = array_merge($result, static::keyFlattening($value,$tmp_keys));
			}
			return $result;
		}

		$key = array_shift($keys);
		return [
			empty($keys) ? $key : sprintf('%s[%s]', $key, implode ('][', $keys)) => $array,
		];
	}

	/**
	 * 与えられた二次元配列をCSVに変換して返します。
	 *
	 * @param array $data
	 * @return string CSV
	 */
	public static function ToCsv ($data) {
		$memory = 'php://memory';
		$mp = fopen($memory, 'bw+');
		foreach ($data as $row) {
			fputcsv($mp, $row);
		}
		rewind($mp);
		ob_start(function () use ($mp) {fclose($mp);});
		fpassthru($mp);
		return ob_get_flush();
	}
}
