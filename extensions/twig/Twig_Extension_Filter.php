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
			new \Twig_Filter('del_lf',			[$this, 'deleteLf']),
			new \Twig_Filter('default',			[$this, 'customDefault']),
			new \Twig_Filter('str_search',		[$this, 'strSearch']),
			new \Twig_Filter('str_truncate',	[$this, 'strTruncate']),
			new \Twig_Filter('empty',			[$this, 'isEmpty']),
			new \Twig_Filter('strtotime',		[$this, 'strToTime']),
			new \Twig_Filter('var_dump',		'var_dump'),
			new \Twig_Filter('back_fill',		[$this, 'backFill']),

			new \Twig_Filter('sort',			[$this, 'sort']),
			new \Twig_Filter('rsort',			[$this, 'rSort']),
			new \Twig_Filter('ksort',			[$this, 'kSort']),
			new \Twig_Filter('krsort',			[$this, 'krSort']),

			new \Twig_Filter('count',			[$this, 'count']),

			new \Twig_Filter('self_combine',	[$this, 'selfCombine']),

			new \Twig_Filter('shift',			[$this, 'shift']),

			new \Twig_Filter('adjust',			[$this, 'adjust']),

			//error
			new \Twig_Filter('adjust_error',	[$this, 'adjustError']),

			//==============================================
			//error support
			//==============================================
			new \Twig_Filter('filter_disable_error_set',	[$this, 'filterDisableErrorSet']),
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
				return ($value !== '' && $value !== null) ? $value : $default;
			case 'null':
			default:
				return ($value === null) ? $default : $value;
		}
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
		return strpos($haystack, $needle, $offset) !== false;
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
		return $condition ? $label : $default;
	}

	public function adjustError ($label, $name, $default = null) {
		return Arrays::ExistsLowest(DI::GetClassVar('render'), ['error', $name]) ? $label : $default;
	}
}
