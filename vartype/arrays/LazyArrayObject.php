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
 * @varsion		2.0.0
 */

namespace ickx\fw2\vartype\arrays;

/**
 * 怠惰な配列アクセスを実現するための配列クラスです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class LazyArrayObject extends \ArrayObject implements \Serializable {
	/**
	 * @var	string	デフォルトフィルタ：GetFilterList使用時にnullと空文字をフィルタする
	 * @static
	 */
	const FILTER_EMPTY_STRING	= 'empty_string';

	/**
	 * インスタンスが持つ全要素を再帰的に配列化して返します。
	 *
	 * @return	array	配列化されたインスタンスが持つ全要素
	 */
	public function getRecursiveArrayCopy () {
		$root = parent::getArrayCopy();
		foreach ($root as $idx => $element) {
			if ($element instanceof static) {
				$root[$idx] = $element->getRecursiveArrayCopy();
			}
		}
		return $root;
	}

	/**
	 * インスタンスが持つ要素をフィルタして減らします。
	 *
	 * @param	mixed	$filter_name	フィルタ名/コールバックフィルタ
	 */
	public function filterReduce ($filter_name = null) {
		$this->exchangeArray($this->getFilteredArrayCopy($filter_name));
	}

	/**
	 * インスタンスが持つ要素をフィルタして配列として返します。
	 *
	 * @param	mixed	$filter_name	フィルタ名/コールバックフィルタ
	 * @return	array	フィルタ済みの要素の配列
	 */
	public function getFilteredArrayCopy ($filter_name = null) {
		$filter_list = static::GetFilterList();
		if (isset($filter_list[$filter_name])) {
			return array_filter($this->getRecursiveArrayCopy(), $filter_list[$filter_name]);
		} else if (!is_callable($filter_name)) {
			return array_filter($this->getRecursiveArrayCopy(), $filter_name);
		}
		return array_filter($this->getRecursiveArrayCopy());
	}

	/**
	 * 配列アクセス時の要素取得処理。
	 *
	 * @param	mixed	$key	要素取得キー
	 * @return	mixed	要素
	 */
	public function __get ($key) {
		return $this->offsetGet($key);
	}

	/**
	 * オフセットを取得します。
	 *
	 * @param	mixed	$key	要素取得キー
	 * @return	mixed	要素
	 */
	public function offsetGet ($index) {
		return parent::offsetExists($index) ? parent::offsetGet($index) : null;
	}

	/**
	 * 配列をマージします。
	 *
	 * @param	array/LazyArrayObject	$array [, mixed $array... ]]
	 */
	public function merge () {
		static::_ArrayObjectMerge($this, func_get_args());
	}

	/**
	 * 利用できるフィルタの一覧を返します。
	 *
	 * @return	array	利用できるフィルタの一覧
	 */
	public static function GetFilterList () {
		return [
			static::FILTER_EMPTY_STRING	=> function ($value) {return $value != '';},
		];
	}

	/**
	 * 配列内の全配列要素を再帰的にLazyArrayObjectに変換します。
	 *
	 * @param	mixed	[$input]
	 * @param	int		[$flags = 0]
	 * @param	string	[$iterator_class = "ArrayIterator"]
	 * @return	LazyArrayObject	変換後の配列
	 */
	public static function RecursiveCreate () {
		$args = func_get_args();
		switch (func_num_args()) {
			case 3:
				return static::_RecursiveCreateRapper($args[0], $args[1], $args[2]);
			case 2:
				return static::_RecursiveCreateRapper($args[0], $args[1]);
			case 1:
				return static::_RecursiveCreateRapper($args[0]);
			default:
				return static::Create();
		}
	}

	/**
	 * 配列内の全配列要素を再帰的にLazyArrayObjectに変換するための内部ラッパー。
	 *
	 * @param	mixed	[$input]
	 * @param	int		[$flags = 0]
	 * @param	string	[$iterator_class = "ArrayIterator"]
	 * @return	LazyArrayObject	変換後の配列
	 */
	protected static function _RecursiveCreateRapper () {
		$args = func_get_args();
		if (!is_array($args[0])) {
			return $args[0];
		}

		$argc = func_num_args();
		foreach ($args[0] as $idx => $array) {
			if (is_array($array)) {
				switch ($argc) {
					case 3:
						$args[0][$idx]= static::Create($array, $args[1], $args[2]);
					case 2:
						$args[0][$idx]= static::Create($array, $args[1]);
					case 1:
						$args[0][$idx]= static::Create($array);
				}
			}
		}

		switch ($argc) {
			case 3:
				return static::Create($args[0], $args[1], $args[2]);
			case 2:
				return static::Create($args[0], $args[1]);
			case 1:
				return static::Create($args[0]);
		}
	}

	/**
	 * 配列をLazyArrayObjectに変換します。
	 *
	 * @param	mixed	[$input]
	 * @param	int		[$flags = 0]
	 * @param	string	[$iterator_class = "ArrayIterator"]
	 * @return	LazyArrayObject	変換後の配列
	 */
	public static function Create () {
		$args = func_num_args() === 0 ? [[]] : func_get_args();
		if ((isset($args[1]) || array_key_exists(1, $args)) && $args[1] === null) {
			unset($args[1]);
		} else {
			$args[1] = \ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS;
		}

		switch (count($args)) {
			case 3:
				return new static($args[0], $args[1], $args[2]);
			case 2:
				return new static($args[0], $args[1]);
			case 1:
				return new static($args[0]);
			default:
				return new static();
		}
	}

	/**
	 * ArrayObjectをmergeします。
	 *
	 * @param	\ArrayObject		$array_object	マージされるArrayObject
	 * @param	\ArrayObject/array	[, $array...]	マージする\ArrayObject/array
	 * @return	LazyArrayObject	マージ後のインスタンス
	 */
	public static function ArrayObjectMerge (\ArrayObject $array_object) {
		$args = func_get_args();
		array_shift($args);
		return static::_ArrayObjectMerge($array_object, $args);
	}

	/**
	 * 再帰的にArrayObjectをmergeするためのラッパー。
	 *
	 * @param	\ArrayObject		$array_object	マージされるArrayObject
	 * @param	\ArrayObject/array	[, $array...]	マージする\ArrayObject/array
	 * @return	LazyArrayObject	マージ後のインスタンス
	 */
	protected static function _ArrayObjectMerge (\ArrayObject $array_object, $arrays) {
		$array1 = $array_object->getArrayCopy();
		foreach ($arrays as $array) {
			if (is_array($array)) {
				$array2 = $array;
			} else if ($array instanceof \ArrayObject) {
				$array2 = $array->getArrayCopy();
			} else {
				$array2 = (array) $array;
			}
			$array1 = array_merge($array1, $array2);
		}
		$array_object->exchangeArray($array1);
		return $array_object;
	}

	/**
	 * インスタンスの文字列表現を返します。
	 *
	 * @return	string	インスタンスの文字列表現。
	 */
	public function __toString () {
		return implode(', ', $this->getArrayCopy());
	}

	/**
	 * isset() および empty() アクセサ。
	 *
	 * @return	bool	プロパティが存在する場合はtrue, そうでない場合はfalse
	 */
	public function __isset($name) {
		return !empty($this->$name);
	}

	/**
	 * シリアライザ
	 *
	 * @see ArrayObject::serialize()
	 */
	public function serialize () {
		return serialize($this->getArrayCopy());
	}

	/**
	 * アンシリアライザ
	 *
	 * @see ArrayObject::unserialize()
	 */
	public function unserialize ($string) {
		return static::Create(unserialize($string));
	}
}
