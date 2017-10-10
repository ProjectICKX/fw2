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
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mvc\app\recd\validation_rule;

use ickx\fw2\security\utility\SecurityUtility;

/**
 * お薦め設定
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class FormValidation {
	//==============================================
	// Utility
	//==============================================
	/**
	 * オプションフラグを調整します。
	 *
	 * @param	array	$current_rule	検証ルール
	 * @param	string	$option_name	オプション名
	 * @param	bool	$flag			フラグ
	 * @return	array	オプションフラグ調整済み検証ルール
	 */
	public function adjustOption ($current_rule, $option_name, $flag) {
		foreach ($current_rule as $idx => $rule) {
			if (isset($rule[$option_name])) {
				$current_rule[$idx][$option_name] = $flag;
				continue;
			}

			if (($key = array_search($option_name, $rule, true)) !== false && is_int($key)) {
				unset($current_rule[$idx][$key]);
				$current_rule[$idx][$option_name] = $flag;
				continue;
			}

			$current_rule[$idx][$option_name] = $flag;
		}

		return $current_rule;
	}

	/**
	 * 指定した名称のルールを除去します。
	 *
	 * @param	array	$rule_list		ルールリスト
	 * @param	array	$target_list	ターゲットリスト
	 * @return	array	指定した名称のルールを削除したルールリスト
	 */
	public function removeRule ($rule_list, $target_list) {
		$remove_idx_list = [];
		foreach ((array) $target_list as $target) {
			$remove_idx_list = array_merge($remove_idx_list, array_keys(array_column($rule_list, 0), $target, true));
		}
		$remove_idx_list = array_unique($remove_idx_list);
		rsort($remove_idx_list);

		foreach ($remove_idx_list as $idx) {
			unset($rule_list[$idx]);
		}

		return $rule_list;
	}

	//==============================================
	// Form type
	//==============================================
	/**
	 * text form用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function text ($current_rule = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * email入力フィールド用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function email ($current_rule = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
			['email_jp_limited', 'is_array' => $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * password form用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function password ($current_rule = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * textarea用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function textarea ($current_rule = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * wysiwyg用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function wysiwyg ($current_rule = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
			['html', array_merge(['script'], SecurityUtility::GetDefaultWatchAttributes(['style'])), 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * radio button用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function radio ($current_rule = [], $label_list = [], $is_array = false, $remove_target = []) {
		$rule_list = array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
			['key', $label_list, 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * check box button用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function checkbox ($current_rule = [], $label_list = [], $value = '1', $remove_target = []) {
		$is_array = is_array($label_list) && !empty($label_list);
		$rule_list = array_merge([
		'null_skip'	=> true,
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_last' => true],
		],
		$is_array ? [
			['key', $label_list, 'is_array' => true, 'array_keys' => true, 'raise_exception' => true],
			['==', $value, 'is_array' => $is_array, 'raise_exception' => true],
		] : [
			['==', $value, 'is_array' => $is_array, 'raise_exception' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * select用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function select ($current_rule = [], $label_list = [], $multiple = false, $remove_target = []) {
		$rule_list = array_merge([
			'null_skip'	=> true,
			['require', 'raise_exception' => true],
			['key', $label_list, 'is_array' => $multiple, 'raise_exception' => true],
		], $multiple ? $this->adjustOption($current_rule, 'is_array', $multiple) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * file upload用のお勧め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function upload ($current_rule = [], $multiple = false, $remove_target = []) {
		$rule_list = array_merge([
			'source'	=> 'upload',
			['require',				'raise_exception' => true],
			['upload_check_status',	'is_array'	=> $multiple, 'is_last' => true],
		], $multiple ? $this->adjustOption($current_rule, 'is_array', $multiple) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	//==============================================
	// Special
	//==============================================
	/**
	 * datetime text field用のお勧め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @param	array	$options		オプション設定
	 * @param	bool	$is_array		配列か否か
	 * @param	array	$remove_target	除去する検証対象
	 * @return	array	お薦め設定
	 */
	public function datetime ($current_rule = [], $options = [], $is_array = false, $remove_target = []) {
		$rule_list = [
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
			['datetime', 'is_array' => $is_array, 'is_last' => true, 'format' => $options['format'] ?? null],
		];

		if (!is_null($premise = $options['premise'] ?? null)) {
			$rule_list['force_validate']	= true;
			$rule_list['premise']			= $premise;
			$rule_list['fetch_from_keys']	= $premise;
			$rule_list['filter']			= $options['filter'] ?? null;
			$rule_list['force_error']		= $options['force_error'] ?? $premise;
		}

		$rule_list = array_merge($rule_list, $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * date text field用のお勧め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @param	array	$options		オプション設定
	 * @param	bool	$is_array		配列か否か
	 * @param	array	$remove_target	除去する検証対象
	 * @return	array	お薦め設定
	 */
	public function date ($current_rule = [], $options = [], $is_array = false, $remove_target = []) {
		$rule_list = [
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
			['date', 'is_array' => $is_array, 'is_last' => true, 'format' => $options['format'] ?? null],
		];

		if (!is_null($premise = $options['premise'] ?? null)) {
			$rule_list['force_validate']	= true;
			$rule_list['premise']			= $premise;
			$rule_list['fetch_from_keys']	= $premise;
			$rule_list['filter']			= $options['filter'] ?? null;
			$rule_list['force_error']		= $options['force_error'] ?? $premise;
		}

		$rule_list = array_merge($rule_list, $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}

	/**
	 * 複数入力項目の内容を元に検証を行う場合のお勧め設定を返します。
	 *
	 * @param	array		$current_rule	追加したいvalidation rule
	 * @param	array		$premise		検証を行うトリガーとなる先行する検証 ここで指定した項目の検証が通過していればこの検証を行う
	 * @param	callable	$filter			取得した値に対するフィルタ フィルタ済みの値に対してこの検証を行う
	 * @param	array		$force_error	エラーが発生した場合に強制的にエラー扱いとする項目
	 * @return	array		お薦め設定
	 */
	public function complexWithOutValue ($current_rule = [], $premise = [], $filter = null, $force_error = null, $remove_target = []) {
		$rule_list = array_merge([
			'force_validate'	=> true,
			'premise'			=> $premise,
			'fetch_from_keys'	=> $premise,
			'filter'			=> $filter,
			'force_error'		=> $force_error ?? $premise,
		], $current_rule);
		return empty($remove_target) ? $rule_list : $this->removeRule($rule_list, $remove_target);
	}
}
