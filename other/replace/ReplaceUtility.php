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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\replace;

/**
 * 文言のリプレイスを容易にするユーティリティです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ReplaceUtility {
	/**
	 * {:name}形式のパラメータ名を置換します。
	 *
	 * @param	string	$text		置換対象のパラメータ名が埋め込まれたテキスト
	 * @param	array	$parameters	置換元のパラメータ名と値を持つ配列
	 * 								置換元の配列は可変引数となっており、引数の数を任意に延伸できる
	 * @return	string	置換終了後のテキスト
	 */
	public static function ReplaceNamedParameters ($text, $parameters) {
		//置換元パラメータの可変長対応
		$parameters_set = [$parameters];
		if (func_num_args() > 2) {
			$parameters_set = array_merge($parameters_set, array_slice(func_get_args(), 2));
		}

		//初期オフセット位置
		$offset = 0;

		//先頭文字が'{'の場合だけ実行
		while (($offset = strpos($text, '{', $offset)) !== false) {
			//二文字目が':'では無い場合、スキップする。
			if (substr($text, $offset + 1, 1) !== ':') {
				continue;
			}

			//末尾検出
			$limit = strpos($text, '}', $offset + 2);

			//対となる末尾が無い場合、終了する。
			if ($limit === false) {
				return $text;
			}

			$limit -= $offset;

			//切り出し
			$name = substr($text, $offset + 2, $limit - 2);

			//置換対象文言が見つからない場合、スキップする。
			$replacement = null;
			foreach ($parameters_set as $parameters) {
				if (isset($parameters[$name])) {
					$replacement = $parameters[$name];
					break;
				}
			}
			if ($replacement === null) {
				$offset += 2;
				continue;
			}

			$text = substr($text, 0, $offset) . $replacement . substr($text, $offset + $limit + 1);
		}

		//処理の終了
		return $text;
	}
}
