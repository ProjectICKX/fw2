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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\file_system;

/**
 * 配列形式のINIファイルを扱います。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class ArrayIniFile {
	use	traits\IniFileTrait;

	/**
	 *
	 * @return multitype:
	 */
	public static function ParseIniFile ($ini_path) {
		return static::ParseSimpleArray($ini_path);
	}

	/**
	 * 配列形式のINIファイルをパースし返します。
	 *
	 * ！！注意！！
	 * 配列には文字列と数値、および配列のみが許容されます。
	 * 関数や定数、変数が設定された場合の動作は"未定義"です。
	 *
	 * ！！注意！！
	 * この関数は再帰呼び出しを行います。
	 *
	 * @param	mixed	$token_list		PHPが解析したトークンリストまたは配列形式のINIファイルパス
	 * @param	int		$count			トークンリストの最大長
	 * @param	int		$pointer		ポインタ
	 * @param	bool	$in_recursive	現在実行中の関数が再帰呼び出し中かどうか
	 * @return	mixed	解析済みの配列形式のINI
	 */
	public static function ParseSimpleArray ($token_list, $count = null, &$pointer = 0, $in_recursive = false) {
		//======================================================
		//token_listがファイルパスだった場合の処理
		//======================================================
		if (is_string($token_list)) {
			clearstatcache(true, $token_list);
			if (!file_exists($token_list)) {
				throw CoreException::RaiseSystemError('ファイルが存在しません。file path:%s', [$token_list]);
			}
			$token_list = token_get_all(file_get_contents($token_list));
		}

		//======================================================
		//実処理
		//======================================================
		//この回で扱う結果保持用配列
		$result = [];

		//もしも最大長が未定義な場合、設定
		$count !== null ?: $count = count($token_list);

		//初期化
		//配列キーとして設定する文字列
		$key = null;

		//テンポラリ
		$tmp_value = null;

		//配列の中にいるかどうか
		$in_array = false;

		//メインループ
		for (;$pointer < $count;$pointer++) {
			//現在地のtokenの切り出し
			$chunk = $token_list[$pointer];

			//------------------------------------------------------
			//トークンが配列ではない（何らかの文字）の場合の処理
			//------------------------------------------------------
			if (!is_array($chunk)) {
				switch ($chunk) {
					//PHP5.4以降の配列の開始
					case '[':
						//既に配列内の場合、一階層下の次元の開始とみなす。
						if ($in_array) {
							//ルートの呼び出し元でキーが設定されていない場合はエラーとする。
							//INIファイルを再現するため。
							if (!$in_recursive && $key === null) {
								throw CoreException::RaiseSystemError('設定名が設定されていません。');
							}

							//関数を再帰呼び出しし、一階層下の配列を生成する。
							$tmp_value = static::ParseSimpleArray($token_list, $count, $pointer, true);
							if ($key === null) {
								$result[] = $tmp_value;
							} else {
								$result[$key] = $tmp_value;
							}

							//判定に使っているため変数を全て初期化する。
							$key		= null;
							$tmp_value	= null;
						}
						$in_array = true;
						break;
					//PHP5.4以降の配列の終了
					case ']':
						$in_array = false;
					//配列の終了
					case ')':
						$in_array = false;
					//配列の要素の区切り
					case ',':
						//要素の区切りまたは配列の終了のため、テンポラリの値を配列として確定する
						if ($tmp_value !== null) {
							if ($key === null) {
								//keyが設定されていない場合
								//INIファイルでの 「key = 」表記と同様とみなすため、空文字を設定する。
								//本来のパーサーならば「$result[] = $tmp_value;」として実装される。
								$result[$tmp_value] = '';
							} else {
								//keyが設定されている場合
								$result[$key] = $tmp_value;
							}

							//判定に使っているため変数を全て初期化する。
							$key		= null;
							$tmp_value	= null;
						}
						break;
				}

				//配列ではない場合はここでスキップ
				continue;
			}

			//------------------------------------------------------
			//トークンが配列である（PHPのトークン）の場合の処理
			//------------------------------------------------------
			//トークンの取得
			$token = $chunk[0];

			//トークンが配列の場合、配列の開始とみなす
			if (\T_ARRAY === $token) {
				//既に配列内の場合、一階層下の次元の開始とみなす。
				if ($in_array) {
					//ルートの呼び出し元でキーが設定されていない場合はエラーとする。
					//INIファイルを再現するため。
					if (!$in_recursive && $key === null) {
						throw CoreException::RaiseSystemError('設定名が設定されていません。');
					}

					//関数を再帰呼び出しし、一階層下の配列を生成する。
					$tmp_value = static::ParseSimpleArray($token_list, $count, $pointer, true);
					if ($key === null) {
						$result[] = $tmp_value;
					} else {
						$result[$key] = $tmp_value;
					}

					//判定に使っているため変数を全て初期化する。
					$key		= null;
					$tmp_value	= null;
				}
				$in_array = true;

				//トークンが配列ではない場合と同様の状態なのでここでスキップ。
				continue;
			}

			//トークンがダブルアロー（=>）の場合、現在のテンポラリはキーで確定のため詰め替えを行う。
			if (\T_DOUBLE_ARROW === $token) {
				$key = $tmp_value;
				$tmp_value = null;
				continue;
			}

			//トークンが数値、文字列、エスケープされた文字列以外の場合、スキップする。
			//消極的安全対策として、上記以外の型の全てを受け付けない。
			if ($token === \T_LNUMBER) {
				//現在の値をテンポラリに代入する。
				$tmp_value = $chunk[1];
				continue;
			}

			if ($token === \T_CONSTANT_ENCAPSED_STRING) {
				//現在の値をテンポラリに代入する。
				$tmp_value = substr($chunk[1], 1, -1);
				continue;
			}
		}

		//======================================================
		//処理の終了
		//======================================================
		return $result;
	}
}
