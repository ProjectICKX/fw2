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

namespace ickx\fw2\other\misc;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\container\DI;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\vartype\strings\Strings;

/**
 * 設定ファイルなどのテキストファイルにPHPのクラス定数やメソッドへのアクセスを与えるユーテリティです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class ConstUtility {
	/**
	 * 定数名から値を引きます。
	 *
	 * @param	string	$const_name				定数名
	 * @param	string	$default_value			定数が存在しなかった場合に与えるデフォルト値
	 * @param	string	$undefined_exception	定数が存在しなかった場合に例外を発生させるかどうか
	 * @return	mixed	定数値
	 */
	public static function Get ($const_name, $default_value = NULL, $undefined_exception = FALSE) {
		$separator_pos = Strings::RearPosition($const_name, '::');
		if ($separator_pos && !class_exists(Strings::Substring($const_name, 0, $separator_pos), TRUE) && $undefined_exception) {
			throw CoreException::RaiseSystemError('存在しないクラスが設定されています。const name:%s', [$const_name]);
		}
		if (!defined($const_name)) {
			if ($undefined_exception) {
				throw CoreException::RaiseSystemError('存在しない定数名が設定されています。const name:%s', [$const_name]);
			}
			return $default_value;
		}
		return constant($const_name);
	}

	/**
	 * ファイルを開いた際に目標とする行だけ取得します。
	 *
	 * @param	resource	$fp			ファイルポインタ
	 * @param	int			$target_row	目標とする行
	 * @return	string		一行分のデータ
	 */
	public static function GetTargeRow ($fp, $target_row) {
		if ($fp) {
			$i = 1;
			while ($row = fgets($fp)) {
				if ($i++ == $target_row) {
					return trim($row, "\r\n");
				}
			}
		}
		return false;
	}

	/**
	 * 与えられた文字列中に含まれるPHP定数値を全て置換します。
	 *
	 * @param	string	$value	変換したい文字列
	 * @return	string	変換後の文字列
	 */
	public static function ReplacePhpConstValue ($value) {
		$match_count	= 0;
		$null_count		= 0;
		$ret = preg_replace_callback(
			"/\{PHP_(CONST|METHOD|FILE|FW2_DI_STATIC):(.+)\}/",
			function ($matches) use (&$null_count) {
				switch ($matches[1]) {
					case 'METHOD':
						$matches[2] = static::ReplacePhpConstValue($matches[2]);
						$chunk = explode('|', $matches[2]);

						$method = explode('::', $chunk[0]);
						if (is_array($method) && !is_callable($chunk[0])) {
							throw CoreException::RaiseSystemError('実行できないメソッド名が設定されています。%s::%s', [$method[0], $method[1]]);
						} else if (!is_array($method) && !function_exists($method[0])) {
							throw CoreException::RaiseSystemError('実行できない関数名が設定されています。%s', [$method[0]]);
						}
						$const_value = call_user_func($method, Arrays::AdjustValue($chunk, 1));
						$const_value !== null ?: $null_count++;
						return $const_value;
					case 'CONST':
						$matches[2] = static::ReplacePhpConstValue($matches[2]);
						$chunk = explode('|', $matches[2]);

						try {
							$const_value = ConstUtility::Get($chunk[0], (isset($chunk[1])) ? $chunk[1] : NULL, TRUE);
						} catch (CoreException $ce) {
							throw CoreException::RaiseSystemError('設定ファイルに存在しない定数名が設定されています。const name:%s', [$chunk[0]]);
						}
						$const_value !== null ?: $null_count++;
						return $const_value;
					case 'FILE':
						$matches[2] = static::ReplacePhpConstValue($matches[2]);
						$chunk = explode('|', $matches[2]);

						$file_path = $chunk[0];
						$target_row = Arrays::AdjustValue($chunk, 1, 1);
						$default_value = Arrays::AdjustValue($chunk, 2, '');

						if ($file_path == '') {
							return $default_value;
						}

						$handle = fopen($file_path, "r");
						$value = static::GetTargeRow($handle, $target_row) ?: $default_value;
						fclose($handle);
						return $value;
					case 'FW2_DI_STATIC':
						$chunk = explode('::', $matches[2]);
						$class_path = [DI::GetClassPath($chunk[0]), $chunk[1]];
						$const_value = $class_path();
						$const_value !== null ?: $null_count++;
						return $const_value;
				}
			},
			$value,
			-1,
			$match_count
		);
		return $match_count === $null_count && $ret === '' ? null : $ret;
	}
}
