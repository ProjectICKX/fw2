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

namespace ickx\fw2\vartype\objects;

/**
 * オブジェクトユーティリティクラスです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Objects {
	/** @var	string	継承先から実行 */
	const INVOKE_VECTOR_UP		= 1;

	/** @var	string	継承元から実行 */
	const INVOKE_VECTOR_DOWN	= 2;

	/**
	 * 指定したオーバーライドされているメソッドを全て実行します。
	 *
	 * @param	object	$instance		メソッドが含まれるオブジェクトインスタンス
	 * @param	string	$method_name	実行するメソッド名
	 * @param	int		$vector			処理する方向 Objects::INVOKE_VECTOR_UP：継承先から実行、Objects::INVOKE_VECTOR_DOWN：継承元から実行
	 * @return	array	メソッドの実行結果
	 */
	public static function InvokeOverrideMethod ($instance, $method_name, $vector = self::INVOKE_VECTOR_UP) {
		$target_classes = [];

		$target_class = new \ReflectionObject($instance);
		do {
			if (!$target_class->hasMethod($method_name)) {
				continue;
			}
			if ($target_class->getMethod($method_name)->getDeclaringClass()->getName() !== $target_class->getName()) {
				continue;
			}
			$target_classes[] = $target_class;
		} while ($target_class = $target_class->getParentClass());

		$ret_pool = [];
		$fetch_function = self::INVOKE_VECTOR_DOWN === $vector ? 'array_shift' : 'array_pop';
		while (($target_class = $fetch_function($target_classes)) !== null) {
			$ret_pool[] = $target_class->getMethod($method_name)->invoke($instance);
		}

		return $ret_pool;
	}

	/**
	 * オブジェクトのプロパティをアクセス範囲無視して取得します。
	 *
	 * @param unknown_type $object
	 * @param unknown_type $name
	 */
	public static function ForceGetProperty ($object, $name) {
		$ro = new \ReflectionObject($object);
		$rp = $ro->getProperty($name);
		$rp->setAccessible(true);
		return $rp->getValue($object);
	}

	/**
	 * プロパティに値を設定します。
	 *
	 * @param	Object	$instance		プロパティに値を設定されるインスタンス
	 * @param	array	$array			値の入った配列
	 * @param	string	$name			プロパティと配列の両方に名前があるキー名
	 * @param	mixed	$default_value	配列に値が無かった場合に代入される値
	 */
	public static function AdjustProperty ($instance, $array, $name, $default_value = null) {
		$instance->$name = $array[$name] ?? $default_value;
	}

	/**
	 * 一括でプロパティに値を設定します。
	 *
	 * @param	Object	$instance		プロパティに値を設定されるインスタンス
	 * @param	array	$array			値の入った配列
	 * @param	array	$name_list		プロパティと配列の両方に名前があるキー名の配列
	 * @param	mixed	$default_value	配列に値が無かった場合に代入される値
	 */
	public static function AdjustPropertyFromArray ($instance, $array, $name_list, $default_value = null) {
		foreach ($name_list as $name) {
			$instance->$name = $array[$name] ?? $default_value;
		}
	}
}
