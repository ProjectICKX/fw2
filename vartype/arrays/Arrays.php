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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
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
	 * @param	mixed	$name			キー名（配列を指定した場合、先頭から順にマッチしだい値を返す）
	 * @param	mixed	$default_value	デフォルト値
	 */
	public static function adjustValue ($array, $name, $default_value = null) {
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
	 * @param	mixed		$array	配列化したい変数
	 * @param	callable	$filter	フィルタとして使用するコールバック
	 * @return 	mixed		配列に統一された入力値
	 */
	public static function adjustArray ($array, $filter = null) {
		return is_array($array) ? $array : ($filter === null ? (array) $array : $filter($array));
	}

	/**
	 * 変数がTraversableな値かどうか判定します。
	 *
	 * ※Traversableな値とはたとえばforeachで使えるものです。
	 *
	 * @param	mixed	$array	判定する値
	 * @return	bool	\Traversable な値の場合はbool TRUE、そうでない場合はbool FALSE
	 * @see		\Traversable
	 */
	public static function isTraversable ($array) {
		return is_array($array) || $array instanceof \Traversable;
	}

	/**
	 * 変数が配列アクセス可能な値かどうか判定します。
	 *
	 * @param	mixed	$value	判定する値
	 * @return	boolean	配列アクセス可能な値の場合はtrue、そうでない場合はfalse
	 */
	public static function isArrayAccessable ($array) {
		return is_array($array) || $array instanceof \ArrayAccess;
	}

	/**
	 * 配列のうち、指定したキーの要素のみを結合して返します。
	 *
	 * @param	array	$array				対象の配列
	 * @param	string	$glue				連結値
	 * @param	array	$target_key_list	結合対象とするキーのリスト
	 * @return	string	結合された値
	 */
	public static function selectImplode ($array, $glue, $target_key_list) {
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
	public static function bitSum ($bit_list) {
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
	public static function keyExists ($array, $key) {
		return isset($array[$key]) || array_key_exists($key, $array);
	}

	/**
	 * 指定した階層の配列にキーが存在するか検証します。
	 *
	 * @param	array	$array	検索対象の配列
	 * @param	mixed	$key	検索するキー
	 * @return	bool	対象のキーが存在する場合はtrue、そうでない場合はfalse
	 */
	public static function existsLowest ($array, $keys) {
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
	public static function sortLowest ($array, $name) {
		uasort(
			$array,
			function ($current, $next) use ($name) {
				return strnatcmp(static::getLowest($current, $name), static::getLowest($next, $name));
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
	public static function reverseSortLowest ($array, $name) {
		uasort(
			$array,
			function ($current, $next) use ($name) {
				return -1 * strnatcmp(static::getLowest($current, $name), static::getLowest($next, $name));
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
	public static function removeLowest ($array, $keys) {
		$keys = (array) $keys;
		$target_key = array_pop($keys);
		$tmp =& $array;
		foreach ($keys as $key) {
			if (is_object($tmp)) {
				if (property_exists($tmp, $key)) {
					$tmp =& $tmp->$key;
				} elseif ($tmp instanceof \ArrayAccess && (isset($tmp[$key]) || array_key_exists($key, $tmp))) {
					$tmp =& $tmp[$key];
				} else {
					throw new \ErrorException(sprintf('対象の階層にキーに紐づく値がありません。key:%s', $key));
				}
			} else {
				if (isset($tmp[$key]) || array_key_exists($key, $tmp)) {
					$tmp =& $tmp[$key];
				} else {
					throw new \ErrorException(sprintf('対象の階層にキーに紐づく値がありません。key:%s', $key));
				}
			}
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
	public static function getLowest ($array, $keys) {
		foreach ((array) $keys as $key) {
			if (is_object($array)) {
				if (property_exists($array, $key)) {
					$array = $array->$key;
				} elseif ($array instanceof \ArrayAccess && (isset($array[$key]) || array_key_exists($key, $array))) {
					$array = $array[$key];
				} else {
					return null;
				}
			} else {
				if (isset($array[$key]) || array_key_exists($key, $array)) {
					$array = $array[$key];
				} else {
					return null;
				}
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
	public static function setLowest ($array, $keys, $value) {
		$keys = (array) $keys;
		if (empty($array)) {
			$tmp =& $array;
		} else {
			$key = array_shift($keys);
			if (is_array($array)) {
				$tmp =& $array[$key];
			} elseif (is_object($array)) {
				if ($array instanceof \ArrayAccess) {
					$tmp =& $array[$key];
				} else {
					$tmp =& $array->$key;
				}
			}
		}

		foreach (array_values($keys) as $idx => $key) {
			if (is_object($tmp)) {
				if ($tmp instanceof \ArrayAccess) {
					if (!isset($tmp[$key])) {
						$tmp[$key] = null;
					}
					$tmp->offsetSet($key, $tmp[$key]);

					if (is_object($tmp[$key])) {
						if ($tmp[$key] instanceof \ArrayAccess) {
							$tmp = $tmp[$key];
						} else {
							$tmp =& $tmp->$key;
						}
					} elseif (is_array($tmp)) {
						$tmp =& $tmp[$key];
					}
				} else {
					if (!property_exists($tmp, $key)) {
						$tmp->$key = null;
					}
					$tmp =& $tmp->$key;
				}
			} elseif (is_array($tmp)) {
				if (!isset($tmp[$key])) {
					$tmp[$key] = null;
				}
				$tmp =& $tmp[$key];
			} else {
				$tmp[$key] = null;
				$tmp =& $tmp[$key];
			}
		}

		if ($tmp instanceof \ArrayAccess) {
			$tmp->offsetSet($key, $value);
		} else {
			$tmp = $value;
		}

		unset($tmp);

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
	public static function multiColumn ($values, $keys = [], $filter = null) {
		if (is_array($keys) && empty($keys) || $keys === null) {
			return $values;
		}

		$enable_filter = $filter !== null && is_callable($filter);

		$keys = (array) $keys;
		$ret = [];

		foreach ($values as $row) {
			$tmp =& $ret;
			foreach ($keys as $key) {
				if (is_object($tmp)) {
					if (property_exists($tmp, $row[$key])) {
						$tmp =& $tmp->{$row[$key]};
					} elseif ($tmp instanceof \ArrayAccess && (isset($row[$key]) || array_key_exists($key, $row))) {
						$tmp =& $tmp[$row[$key]];
					} else {
						throw new \ErrorException(sprintf('対象の階層にキーに紐づく値がありません。key:%s', $row[$key]));
					}
				} else {
					if (isset($row[$key]) || array_key_exists($key, $row)) {
						$tmp =& $tmp[$row[$key]];
					} else {
						throw new \ErrorException(sprintf('対象の階層にキーに紐づく値がありません。key:%s', $row[$key]));
					}
				}
			}
			if ($enable_filter) {
				$row = $filter($row);
			}
			$tmp = $row;
		}

		unset($tmp);

		return $ret;
	}

	/**
	 * 配列から指定したキーに紐付く値のみを抽出し返します。
	 *
	 * 値が存在しない場合は値がnullとなります。
	 *
	 * @param	array	$array	調査対象の配列
	 * @param	array	$keys	辿るキー
	 */
	public static function findByKeyList (array $array, array $keys) {
		return array_filter(array_combine($keys, array_map(function ($key) use ($array) {
			return isset($array[$key]) ? $array[$key] : null;
		}, $keys)));
	}

	/**
	 * 再帰的に空要素を削除して配列を縮小します。
	 *
	 * @param	array		$array		空要素を削除する配列
	 * @param	callable	$call_back	独自の空判定処理
	 * @return	array		空要素を削除された配列
	 */
	public static function recursiveFilter ($array, $call_back = null) {
		if (!is_callable($call_back)) {
			foreach ($array as $idx => $element) {
				if (is_array($element)) {
					$array[$idx] = static::recursiveFilter($element, $call_back);
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
					$array[$idx] =  static::recursiveFilter($element, $call_back);
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
	public static function shuffle ($array) {
		$result = [];
		foreach(array_rand($array) as $key){
			$result[$key] = $array[$key];
		}
		return $result;
	}

	/**
	 * 多次元配列となっている配列のキーを key[key] のスタイルにし平坦化します。
	 *
	 * @param	array	$array
	 * @param	array	$keys
	 * @return	array	平坦化した配列
	 */
	public static function keyFlattening ($array, $keys = []) {
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
	 * 配列のキーすべて、あるいはその一部を返します。
	 *
	 * @param	array		$array			配列
	 * @param	mixed		$search_value	検索キーの絞り込み条件
	 * @param	bool		$strict			検索キーの絞り込み条件を指定した場合に厳密な比較を行うかどうか
	 * @return	string|int	配列の現在の値
	 */
	public static function keys ($array, $search_value = null, $strict = false) {
		return array_keys($array, $search_value, $strict);
	}

	/**
	 * 配列の全ての値を返します。
	 *
	 * @param	array	$array	配列
	 * @return	array	数字添え字を付けた配列
	 */
	public static function values ($array) {
		return array_values($array);
	}

	/**
	 * 配列の現在のキーを取得します。
	 *
	 * @param	array		$array	配列
	 * @return	string|int	配列の現在のキー
	 */
	public static function key ($array) {
		return key($array);
	}

	/**
	 * 配列の現在の値を取得します。
	 *
	 * @param	array		$array	配列
	 * @return	string|int	配列の現在の値
	 */
	public static function value ($array) {
		return current($array);
	}

	/**
	 * 配列の現在の要素を返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の現在の要素
	 */
	public static function current ($array) {
		return current($array);
	}

	/**
	 * 配列の内部ポインタを進めます。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	内部ポインタを進めた後の要素の値
	 */
	public static function next (&$array) {
		return next($array);
	}

	/**
	 * 配列の内部ポインタを戻します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	内部ポインタを戻した後の要素の値
	 */
	public static function prev (&$array) {
		return prev($array);
	}

	/**
	 * 配列の内部ポインタを先頭の要素にセットします。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の先頭要素の値
	 */
	public static function reset (&$array) {
		return reset($array);
	}

	/**
	 * 配列の内部ポインタを末尾の要素にセットします。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の最終要素の値
	 */
	public static function end (&$array) {
		return end($array);
	}

	/**
	 * 配列の内部ポインタを末尾の要素にセットします。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の最終要素の値
	 */
	public static function each (&$array) {
		if ($value = next($array) === false) {
			return false;
		}
		return [
			1		=> $value,
			'value'	=> $value,
			0		=> $key = key($array),
			'key'	=> $key,
		];
	}

	/**
	 * 配列として要素が空か判定します。
	 *
	 * @param	mixed	$array	要素が空かどうか判定する値
	 * @return	bool	要素が配列として空ならtrue、そうでないならばfalse
	 */
	public static function empty ($array) {
		return empty((array) $array);
	}

	/**
	 * 配列の要素数をカウントして返します。
	 *
	 * @param	mixed	$array	配列
	 * @return	int		配列の要素数
	 */
	public static function count ($array) {
		return count($array);
	}

	/**
	 * 自身の値を利用してコンバインします。
	 *
	 * @param	array	$array	配列
	 * @return	array	キーと値に自身を利用した配列
	 */
	public static function selfCombine ($array) {
		return array_combine($array = (array) $array, $array);
	}

	/**
	 * 配列の先頭から要素を一つ取り出して返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	先頭の要素
	 */
	public static function shift (&$array) {
		return array_shift($array);
	}

	/**
	 * 一つ以上の要素を配列の最初に加えます。
	 *
	 * @param	array	$array	配列
	 * @param	array	$values	追加する要素
	 * @return	array	追加後の配列
	 */
	public static function unshift ($array, ...$values) {
		array_unshift($array, ...$values);
		return $array;
	}

	/**
	 * 配列の末尾から要素を一つ取り出して返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	最後の要素
	 */
	public static function pop (&$array) {
		return array_pop($array);
	}

	/**
	 * 一つ以上の要素を配列の末尾に加えます。
	 *
	 * @param	array	$array	配列
	 * @param	array	$values	追加する要素
	 * @return	array	追加後の配列
	 */
	public static function push ($array, ...$values) {
		array_push($array, ...$values);
		return $array;
	}

	/**
	 * 配列の最初の要素を返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の最初の要素
	 */
	public static function first ($array) {
		return array_shift($array);
	}

	/**
	 * 配列の末尾の要素を返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の末尾の要素
	 */
	public static function last ($array) {
		return array_pop($array);
	}

	/**
	 * 配列の最初の要素のキーを返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の最初の要素
	 */
	public static function firstKey ($array) {
		reset($array);
		return key($array);
	}

	/**
	 * 配列の末尾の要素のキーを返します。
	 *
	 * @param	array	$array	配列
	 * @return	mixed	配列の末尾の要素
	 */
	public static function lastKey ($array) {
		last($array);
		return key($array);
	}

	/**
	 * 配列をソートして返します。
	 *
	 * @param	array	$array		配列
	 * @param	int		$sort_flag	ソートオプション
	 * @return	array	ソート済みの配列
	 */
	public static function sort ($array, $sort_flag = \SORT_REGULAR) {
		if (false === sort($array, $sort_flag)) {
			return false;
		}
		return $array;
	}

	/**
	 * 配列を逆順ソートして返します。
	 *
	 * @param	array	$array		配列
	 * @param	int		$sort_flag	ソートオプション
	 * @return	array	ソート済みの配列
	 */
	public static function reverse ($array, $sort_flag = \SORT_REGULAR) {
		if (false === rsort($array, $sort_flag)) {
			return false;
		}
		return $array;
	}

	/**
	 * 配列をキーソートして返します。
	 *
	 * @param	array	$array		配列
	 * @param	int		$sort_flag	ソートオプション
	 * @return	array	ソート済みの配列
	 */
	public static function keySort ($array, $sort_flag = \SORT_REGULAR) {
		if (false === ksort($array, $sort_flag)) {
			return false;
		}
		return $array;
	}

	/**
	 * 配列を逆順キーソートして返します。
	 *
	 * @param	array	$array		配列
	 * @param	int		$sort_flag	ソートオプション
	 * @return	array	ソート済みの配列
	 */
	public static function keyReverse ($array, $sort_flag = \SORT_REGULAR) {
		if (false === krsort($array, $sort_flag)) {
			return false;
		}
		return $array;
	}

	/**
	 * 配列要素を文字列により連結し返します。
	 *
	 * @param	array	$array	連結対象の配列
	 * @param	string	$glue	連結文字
	 * @return	string	連結後の文字列
	 */
	public static function implode ($array, $glue = '') {
		return implode($glue, $array);
	}

	/**
	 * 文字列を文字列により分割し、配列として返します。
	 *
	 * @param	string	$value		分割対象の文字列
	 * @param	string	$delimiter	区切り文字列
	 * @param	int		$limit		分割後最大数
	 * @return	array	分割後の配列
	 */
	public static function explode ($value, $delimiter, $limit = \PHP_INT_MAX) {
		return explode($delimiter, $value, $limit);
	}
}
