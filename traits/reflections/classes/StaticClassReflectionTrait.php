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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\reflections\classes;

/**
 * クラスリフレクション特性です。
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait StaticClassReflectionTrait {
	/**
	 * 継承で上書きされるデフォルトプロパティを返します。
	 *
	 * @param	string	$property_name	取得するプロパティの名称
	 * @return	mixed	継承上書き前のデフォルトプロパティ値
	 */
	public static function GetDefaultPropertyGreedy ($property_name) {
		//開始地点
		$reflection_class = new \ReflectionClass(static::class);
		$reflection_class->getDefaultProperties();
		$current_default_property[] = isset($reflection_class[$property_name]) ? $reflection_class[$property_name] : [];

		while ($reflection_class = $reflection_class->getParentClass()) {
			$reflection_class->getDefaultProperties();
			$current_default_property[] = isset($reflection_class[$property_name]) ? $reflection_class[$property_name] : [];
		}

		return $current_default_property;
	}

	/**
	 * 継承して上書きされた分も含めたデフォルトプロパティの一覧を返します。
	 *
	 * @return	array	継承前の値も含めたデフォルトプロパティセット
	 */
	public static function GetDefaultPropertysGreedy () {
		$result = [];

		//開始地点
		$reflection_class = new \ReflectionClass(static::class);
		$namespace = $reflection_class->getNamespaceName();
		$class_name = $reflection_class->getShortName();

		$result[$namespace] = [];
		$result[$namespace][$class_name] = [];
		foreach ($reflection_class->getDefaultProperties() as $key => $value) {
			$result[$namespace][$class_name] = new \ReflectionProperty($reflection_class->getName(), $key);
		}

		while ($reflection_class = $reflection_class->getParentClass()) {
			$namespace = $reflection_class->getNamespaceName();
			$class_name = $reflection_class->getShortName();

			if (!isset($result[$namespace])) {
				$result[$namespace] = [];
			}
			if (!isset($result[$namespace][$class_name])) {
				$result[$namespace][$class_name] = [];
			}

			foreach ($reflection_class->getDefaultProperties() as $key => $value) {
				$result[$namespace][$class_name] = new \ReflectionProperty($reflection_class->getName(), $key);
			}
		}

		return $result;
	}
}
