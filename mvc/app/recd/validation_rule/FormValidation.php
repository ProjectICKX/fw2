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

	//==============================================
	// Form type
	//==============================================
	/**
	 * text form用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function text ($current_rule = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * email入力フィールド用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function email ($current_rule = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array' => $is_array, 'is_last' => true],
			['email_jp_limited', 'is_array' => $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * password form用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function password ($current_rule = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * textarea用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function textarea ($current_rule = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * wysiwyg用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function wysiwyg ($current_rule = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
			['html', array_merge(['script'], SecurityUtility::GetDefaultWatchAttributes(['style'])), 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * radio button用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function radio ($current_rule = [], $label_list = [], $is_array = false) {
		return array_merge([
			['require', 'raise_exception' => true],
			['not_string_empty', 'is_array'	=> $is_array, 'is_last' => true],
			['key', $label_list, 'is_array'	=> $is_array, 'is_last' => true],
		], $is_array ? $this->adjustOption($current_rule, 'is_array', $is_array) : $current_rule);
	}

	/**
	 * check box button用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function checkbox ($current_rule = [], $label_list = [], $value = '1') {
		$is_array = is_array($label_list) && !empty($label_list);
		return array_merge([
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
	}

	/**
	 * select用のお薦め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function select ($current_rule = [], $label_list = [], $multiple = false) {
		return array_merge([
			'null_skip'	=> true,
			['require', 'raise_exception' => true],
			['key', $label_list, 'is_array' => $multiple, 'raise_exception' => true],
		], $multiple ? $this->adjustOption($current_rule, 'is_array', $multiple) : $current_rule);
	}

	/**
	 * file upload用のお勧め設定を返します。
	 *
	 * @param	array	$current_rule	追加したいvalidation rule
	 * @return	array	お薦め設定
	 */
	public function file ($current_rule = [], $multiple = false) {
		return array_merge([
			'source'	=> 'upload',
			['require', 'raise_exception' => true],
			['upload_check_status', 'is_array'	=> $multiple, 'is_last' => true],
		], $multiple ? $this->adjustOption($current_rule, 'is_array', $multiple) : $current_rule);
	}

	//==============================================
	// Special
	//==============================================
	/**
	 * 複数入力項目の内容を元に検証を行う場合のお勧め設定を返します。
	 *
	 * @param	array		$current_rule	追加したいvalidation rule
	 * @param	array		$premise		検証を行うトリガーとなる先行する検証 ここで指定した項目の検証が通過していればこの検証を行う
	 * @param	callable	$filter			取得した値に対するフィルタ フィルタ済みの値に対してこの検証を行う
	 * @param	array		$force_error	エラーが発生した場合に強制的にエラー扱いとする項目
	 * @return	array		お薦め設定
	 */
	public function complexWithOutValue ($current_rule = [], $premise = [], $filter = null, $force_error = null) {
		return array_merge([
			'force_validate'	=> true,
			'premise'			=> $premise,
			'fetch_from_keys'	=> $premise,
			'filter'			=> $filter,
			'force_error'		=> $force_error ?? $premise,
		], $current_rule);
	}
}
