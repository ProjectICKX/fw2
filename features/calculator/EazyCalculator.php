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
 * @package		features
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\features\calculator;

use ickx\fw2\vartype\arrays\Arrays;

/**
 * 簡易な計算を行います。
 *
 * @category	Flywheel2
 * @package		features
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
 class EazyCalculator {
 	/**
 	 * @var	int	比較演算子コード：==
 	 * @static
 	 */
	const COMP_OP_EQ	= 0;

 	/**
 	 * @var	int	比較演算子コード：!=
 	 * @static
 	 */
	const COMP_OP_N_EQ	= 1;

 	/**
 	 * @var	int	比較演算子コード：<
 	 * @static
 	 */
	const COMP_OP_LT	= 2;

 	/**
 	 * @var	int	比較演算子コード：<=
 	 * @static
 	 */
	const COMP_OP_LT_EQ	= 3;

 	/**
 	 * @var	int	比較演算子コード：>
 	 * @static
 	 */
	const COMP_OP_GT	= 4;

 	/**
 	 * @var	int	比較演算子コード：>=
 	 * @static
 	 */
	const COMP_OP_GT_EQ	= 5;

 	/**
 	 * @var	int	比較演算子コード：REGEX
 	 * @static
 	 */
	const COMP_OP_REGEX	= 6;

 	/**
 	 * @var	int	比較演算子コード：ANY
 	 * @static
 	 */
	const COMP_OP_ANY	= 7;

 	/**
 	 * @var	int	比較演算子コード：EMPTY
 	 * @static
 	 */
	const COMP_OP_EMPTY	= 8;

	/**
	 * 計算対象リストを元に実行します。
	 *
	 * @param	array	$calc_list			計算対象となる配列
	 * @param	array	$replace_value_list	特定文字列に対する置換用配列
	 * @return	int		計算結果
	 */
	public static function ExecuteCalcList ($calc_list, $replace_value_list = []) {
		$ret = static::ExecuteAggregateFunctions(
			static::ConvertCalcListToString($calc_list, $replace_value_list)
		);
		if ($ret === false) {
			return false;
		}

		$ret = static::RPNCalculation(
			static::InfixToPostfix($ret)
		);
		return ($ret === false) ? false : (float) $ret;
	}

	/**
	 * 計算対象リストを文字列表現に変換します。
	 *
	 * @param	array	$calc_list			計算対象リスト
	 * @param	array	$replace_value_list	特定文字列に対する置換用配列
	 * @return	string	計算対象リストの文字列表現
	 */
	public static function ConvertCalcListToString ($calc_list, $replace_value_list = []) {
		$in_function = false;
		$result = [];
		// CNT関数中かどうか
		$is_cnt_mode = false;
		// MIN関数中かどうか
		$is_min_mode = false;
		$counter_for_cnt_mode = 0;
		// 全ての項目がブランクかどうか
		$is_all_blank = true;

		foreach ($calc_list as $calc) {
			// CNT関数中かどうか
			if ($calc == 'CNT(' && !$is_cnt_mode) {
				// カウントの場合は、ここでカウントして数値に変換する
				// そのためresultにはpushしない
				$is_cnt_mode = true;
				$in_function = true;
				$counter_for_cnt_mode = 0;
				continue;
			}

			switch ($calc) {
				case 'MIN(':
					// MIN関数中かどうか
					if (!$is_min_mode) {
						$is_min_mode = true;
					}
				case 'MAX(':
				case 'SUM(':
				case 'AVE(':
				case 'CNT(':
					$in_function = true;
					$result[] = $calc;
					continue 2;
			}

			if ($in_function && $calc == ')') {
				$in_function = false;
				$is_min_mode = false;
				if (!$is_cnt_mode) {
					array_pop($result);
					$result[] = $calc;
				} else {
					$is_cnt_mode = false;
					$result[] = $counter_for_cnt_mode;
					$counter_for_cnt_mode = 0;
				}
				continue;
			}

			$result[] = Arrays::AdjustValue($replace_value_list, $calc, $calc);

			if (!$is_cnt_mode && !$is_min_mode) {
				// カウント、最小値以外の計算式は0へ変換
				$result[] = 0;
				if ($in_function) {
					$result[] = ',';
				}
			}
		}

		return implode($result);
	}

	/**
	 * 集約関数を実行します。
	 *
	 * @param	array	$numerical_formula	集約対象の計算対象リスト
	 * @return	集約関数を実行し、反映した計算対象リスト
	 */
	public static function ExecuteAggregateFunctions ($numerical_formula) {
		return preg_replace_callback(
			"/(SUM|AVG|MIN|MAX)\(([^\)]+)\)/",
			function ($matches) {
				$func_name	= $matches[1];
				$value		= $matches[2];

				if (preg_match_all("/(\-?\d+(?:\.\d+)?)/", $value, $values) === 0) {
					return $value;
				}
				$values = $values[1];
				switch ($func_name) {
					case 'SUM':
						$ret = 0;
						foreach ($values as $value) {
							if ($value == '' || $value == null) {
								continue;
							}
							if (!is_numeric($value)) {
								return false;
							}
							$ret += $value;
						}
						return $ret;
					case 'AVG':
						$count = 0;
						$ret = 0;
						foreach ($values as $value) {
							$count++;
							if ($value == '' || $value == null) {
								continue;
							}
							if (!is_numeric($value)) {
								return false;
							}
							$ret += $value;
						}
						if ($count === 0) {
							return 0;
						}
						return $ret / $count;
					case 'MIN':
						uasort(
							$values,
							function ($a, $b) {
								if ($a == $b) {
									return 0;
								}
								return ($a < $b) ? -1 : 1;
							}
						);
						return array_shift($values);
					case 'MAX':
						uasort(
							$values,
							function ($a, $b) {
								if ($a == $b) {
									return 0;
								}
								return ($a < $b) ? -1 : 1;
							}
						);
						return array_pop($values);
				}
			},
			$numerical_formula
		);
	}

	/**
	 * 計算式を逆ポーランド記法に変換します。
	 *
	 * @param	string	$numerical_formula	計算式
	 * @return	string	逆ポーランド記法化された計算式
	 */
	public static function InfixToPostfix ($numerical_formula) {
		if (preg_match_all("/[\(\)\+\-\/\*%]|\d+\.\d+|[0]|[1-9][0-9]*/", $numerical_formula, $postfix_formula) === 0) {
			return $numerical_formula;
		}

		$postfix_formula = $postfix_formula[0];

		// 数値の符号対応（マイナスの数値が渡ってきた場合＝演算子が続いた場合、符号と判断し、結合してセットする）
		$op_adjuster = [];
		for ($i = 0, $postfix_formula_length = count($postfix_formula); $i < $postfix_formula_length; $i++) {
			if ($i == 0 && static::IsOperator($postfix_formula[$i])) {
				$op_adjuster[] = ($postfix_formula[$i] . $postfix_formula[++$i]);
			} else if (static::IsOperator($postfix_formula[$i]) && static::IsOperator($postfix_formula[$i + 1]) && $i + 2 < $postfix_formula_length) {
				$op_adjuster[] = ($postfix_formula[$i]);
				$op_adjuster[] = ($postfix_formula[++$i] . $postfix_formula[++$i]);
			} else {
				$op_adjuster[] = ($postfix_formula[$i]);
			}
		}
		$postfix_formula = $op_adjuster;

		$stack = [];
		$polish = [];
		$top = '';

		foreach ($postfix_formula as $chunk) {
			if ($chunk == '(') {
				$stack[] = $chunk;
			} else if ($chunk == ')') {
				while (count($stack) != 0) {
					$top = array_slice($stack, -1)[0];
					if ($top != '(') {
						$polish[] = array_pop($stack);
					} else {
						break;
					}
				}
				array_pop($stack);	// skip '('
			} else {
				while (count($stack) != 0) {
					$top = array_slice($stack, -1)[0];
					if (static::GetOperatorPriority($chunk) <= static::GetOperatorPriority($top)) {
						$polish[] = array_pop($stack);
					} else {
						break;
					}
				}
				$stack[] = $chunk;
			}
		}
		while (count($stack) != 0) {
			$polish[] = array_pop($stack);
		}
		return $polish;
	}

	/**
	 * 文字列を逆ポーランド記法として計算します。
	 *
	 * @param	string	$polish	逆ポーランド記法の計算式
	 * @return	int		計算結果
	 */
	public static function RPNCalculation ($polish) {
		$stack = [];

		if (!isset($polish) || empty($polish)) {
			return false;
		}

		foreach ($polish as $chunk) {
			switch ($chunk) {
				case '+':
				case '-':
				case '*':
				case '/':
				case '%':
					$operand_right = array_pop($stack) * 1.0;
					$operand_left = array_pop($stack) * 1.0;
					$tmp = static::StackCalculus($operand_left, $chunk, $operand_right);
					if ($tmp === false) {
						return false;
					}
					$stack[] = $tmp;
					break;
				default:
					$stack[] = $chunk;
					break;
			}
		}
		return $stack[0];
	}

	/**
	 * チャンクが演算子かどうか判定します。
	 *
	 * @param	string	$chunk	演算子
	 * @return	boolean	チャンクが演算子の場合はtrue そうでない場合はfalse
	 */
	public static function IsOperator ($chunk) {
		switch ($chunk) {
			case '+':
			case '-':
			case '*':
			case '/':
			case '%':
				return true;
		}
		return false;
	}

	/**
	 * 演算子の優先順位を返します。
	 *
	 * @param	string	$operator	演算子
	 * @return	int		演算子の優先順位
	 */
	public static function GetOperatorPriority ($operator) {
		$priority = [
			'('	=> 1,
			'+'	=> 2,
			'-'	=> 2,
			'*'	=> 3,
			'/'	=> 3,
			'%'	=> 3
		];
		return isset($priority[$operator]) ? $priority[$operator] : 4;
	}

	/**
	 * 算術演算を行います。
	 *
	 * @param	int		$operand_left	左辺値
	 * @param	string	$operator		演算子
	 * @param	int		$operand_right	右辺値
	 * @return	int		計算結果
	 */
	public static function StackCalculus ($operand_left, $operator, $operand_right) {
		$operand_left	*= 1.0;
		$operand_right	*= 1.0;
		switch ($operator) {
			case '+':
				return $operand_left + $operand_right;
			case '-':
				return $operand_left - $operand_right;
			case '*':
				return $operand_left * $operand_right;
			case '/':
				if ($operand_right == 0) {
					return false;
				}
				return $operand_left / $operand_right;
			case '%':
				return $operand_left % $operand_right;
		}
	}

	/**
	 * 比較演算を行います。
	 *
	 * @param	mixed	$operand_left		左辺値
	 * @param	int		$operator			比較演算子
	 * @param	mixed	$operand_right		右辺値
	 * @param	array	$replace_value_list	値置換用配列
	 * @return	bool	比較演算結果
	 */
	public static function Comparison ($operand_left, $operator, $operand_right, $replace_value_list = []) {
		// チェックボックスの!=判定用に元のIDを保持しておく。
		$left_value_id = $operand_left;

		//値のどちらかがnullの場合は即死
		if ($operand_left === null || $operand_right === null) {
			return false;
		}

		//左辺値のフォーマット
		$operand_left = is_array($operand_left) ? static::ExecuteCalcList($operand_left, $replace_value_list) : Arrays::AdjustValue($replace_value_list, $operand_left, '');

		//右辺値のフォーマット
		$operand_right = is_array($operand_right) ? static::ExecuteCalcList($operand_right, $replace_value_list) : Arrays::AdjustValue($replace_value_list, $operand_right, $operand_right);

		if ($operand_left === false || $operand_right === false) {
			return false;
		}

		// データ有無チェック
		switch ($operator) {
			case static::COMP_OP_EQ:
			case static::COMP_OP_N_EQ:
			case static::COMP_OP_LT:
			case static::COMP_OP_LT_EQ:
			case static::COMP_OP_GT:
			case static::COMP_OP_GT_EQ:
			case static::COMP_OP_REGEX:
				if ($operand_left === null ||
					$operand_left === '' ||
					$operand_right === null ||
					$operand_right === '') {
					return false;
				}
		}

		//比較演算
		switch ($operator) {
			case static::COMP_OP_EQ:
				return $operand_left	==	$operand_right;
			case static::COMP_OP_N_EQ:
				return $operand_left	!=	$operand_right;
			case static::COMP_OP_LT:
				return $operand_left	<	$operand_right;
			case static::COMP_OP_LT_EQ:
				return $operand_left	<=	$operand_right;
			case static::COMP_OP_GT:
				return $operand_left	>	$operand_right;
			case static::COMP_OP_GT_EQ:
				return $operand_left	>=	$operand_right;
			case static::COMP_OP_ANY:
				return $operand_left	!=	null && ($operand_left != '' || $operand_left === 0);
			case static::COMP_OP_EMPTY:
				return $operand_left	==	null || ($operand_left == '' && $operand_left !== 0);
			case static::COMP_OP_REGEX:
				return preg_match("/". $operand_right ."/", $operand_left) === 1;
		}

		//対象となる比較演算子がない場合はfalse
		return false;
	}
}
