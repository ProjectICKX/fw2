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
 * @package		router
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\router;

/**
 * URLルーティング処理を行うクラスです。
 *
 * @category	Flywheel2
 * @package		router
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class Router {
	/** @var	string	ルーティングURL */
	const ROUTING_URL	= 'routing_url';

	/** @var	string	エスケープ文字 */
	const ESCAPE_CHAR	= "\\";

	/** @var	string	セパレータ */
	const SEPARATOR		= '/';

	/** @var	string	置換対象として許可する正規表現パターン */
	const PATH_PATTERN	= "/\{:([A-Za-z0-9_]*)(?::([A-Za-z0-9_\\\|\[\]\+\*\.\^\/\{\}]+))*\}/u";

	/** @var	string	置換対象変数名がない事を示す */
	const SKIP_FLAG		= '';

	/** @var	array	コネクションリスト */
	protected static $_connectionList = [];

	/** @var	array	GetUrl用検索結果キャッシュ */
	protected static $_reverseUrl	= [];

	/** @var	array	共通オプション */
	protected static $_commonOptions = [];

	/** @var	array	ルールベースオプション */
	protected static $_ruleBaseOptions = [];

	/**
	 * 接続パスを登録します。
	 *
	 * @param	string	$path		パス
	 * @param	array	$options	オプション
	 */
	public static function Connect ($path, array $options = []) {
		static::$_connectionList[$path] = compact('path', 'options');
	}

	/**
	 * 共通で使用するオプションを登録します。
	 *
	 * @param	array	$options	共通設定オプション
	 */
	public static function SetCommonOptions (array $options) {
		static::$_commonOptions = $options;
	}

	/**
	 * 共通で使用するオプションを登録します。
	 *
	 * @param	array	$options	共通設定オプション
	 */
	public static function SetRuleBaseOption ($rule, array $options) {
		static::$_ruleBaseOptions[$rule] = $options;
	}

	/**
	 * コントーラ名、アクション名、パラメータからURLを構築します。
	 *
	 * コントローラ名、アクション名、パラメータが完全に一致する接続パスからURLを構築します。
	 * その際、パラメータの値も見ます。
	 *
	 * GetUrl実行時には値を確定出来ないパラメータ名は$var_parametersに指定してください。
	 *
	 * ex)
	 * Router::Connect('/{:controller:index}/{:action:index}/{id:\d+}/}');
	 *
	 * Router::GetUrl('index', 'index');					// => false, パラメータなしのURLが接続パスが設定されていない
	 * Router::GetUrl('index', 'index', ['id' => 'aaa']);	// => false, idパラメータが\d+にマッチしない
	 * Router::GetUrl('index', 'index', ['id' => '123']);	// => /index/index/123/
	 * Router::GetUrl('index', 'index', [], ['id']);		// => /index/index/{:id}/
	 *
	 * @param	string	$controller_name	コントローラ名
	 * @param	string	$action_name		アクション名
	 * @param	array	$parameters			パラメータ
	 * @param	array	$var_parameters		後付けで差し替えたいパラメータ
	 * @return	mixed	接続パスに存在するURLの場合はstring URL、存在しないURLの場合はbool false
	 */
	public static function GetUrl ($controller_name = null, $action_name = null, $parameters = [], $var_parameters = []) {
		//未指定の場合はindexとみなす
		$controller_name	= $controller_name ?: 'index';
		$action_name		= $action_name ?: 'index';

		//コントローラとアクションは検索対象から外す
		$default_omit_parameters = [
			'controller',
			'action',
		];

		//簡易突合用のパラメータ名リストを作る
		$parameters = !is_array($parameters) ? [] : $parameters;
		$parameter_name_list = array_merge(array_keys($parameters), $var_parameters);
		sort($parameter_name_list);

		//検索結果キャッシュ用にserializeする
		$parameter_name_list_hath = hash('sha256', serialize($parameter_name_list));

		//極力キャッシュから引くようにする
		$is_cache = false;
		if (isset(static::$_reverseUrl[$controller_name][$action_name][$parameter_name_list_hath])) {
			list($controller_pattern, $action_pattern, $matching_set, $connection) = static::$_reverseUrl[$controller_name][$action_name][$parameter_name_list_hath];
			$is_cache = true;
			$connection_list	= [$connection];
		} else {
			$connection_list = static::$_connectionList;
		}

		//共通設定オプションの有無の取得
		$enable_common_options = !empty(static::$_commonOptions);

		//Connection単位で処理
		foreach ($connection_list as $connection) {
			//コネクションパスからデータ抽出
			$path = ltrim($connection['path'], '/');

			$path = str_replace('\ud', '[1-9][0-9]*|0', $path);
			$path = str_replace('\nn', '[1-9][0-9]*', $path);

			if (!isset(static::$_reverseUrl[':path:'][$path])) {
	 			static::$_reverseUrl[':path:'][$path] = static::PursePathRegex($path);
			}
			list($regex_url, $parameter_list, $pattern_list) = static::$_reverseUrl[':path:'][$path];

			if (!$is_cache) {
				$matching_set = array_combine($parameter_list, $pattern_list);

				//optionsを先に取得しておく
				$options = isset($connection['options']) ? $connection['options'] : null;
				if ($enable_common_options && $options !== null) {
					$options = array_merge(static::$_commonOptions, $options);
				}

				//コントローラパターンの抽出
				$controller_pattern = isset($options['controller']) ? $options['controller'] : null;
				$controller_pattern = $controller_pattern ?: (isset($options[0]) ? $options[0] : null);
				$controller_pattern = $controller_pattern ?: (isset($matching_set['controller']) ? $matching_set['controller'] : 'index');
				$controller_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $controller_pattern));

				//アクションパターンの抽出
				$action_pattern = isset($options['action']) ? $options['action'] : null;
				$action_pattern = $action_pattern ?: (isset($options[1]) ? $options[1] : null);
				$action_pattern = $action_pattern ?: (isset($matching_set['action']) ? $matching_set['action'] : 'index');
				$action_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $action_pattern));

				//コントローラ、アクションがマッチするconnectionのみ処理する
				if (preg_match($controller_pattern, $controller_name) === 1 && preg_match($action_pattern, $action_name) === 1) {
					//除外対象のパラメータを除去
					foreach ($default_omit_parameters as $omit_parameter) {
						if (isset($matching_set[$omit_parameter])) {
							unset($matching_set[$omit_parameter]);
						}
					}

					//簡易突合用にキーを取り出してソート
					$matching_parameter_name_list = array_keys($matching_set);
					sort($matching_parameter_name_list);

					//初回検証
					if ($parameter_name_list !== $matching_parameter_name_list) {
						continue;
					}
				} else {
					continue;
				}
			}

			//詳細検証：値は初回検証で必ずあると判断されている
			foreach ($matching_set as $name => $pattern) {
				if (in_array($name, $var_parameters, true)) {
					continue;
				}

				//一つでも不適合があれば終了
				$param_value = $parameters[$name];
				if (is_object($parameters[$name]) && is_callable($parameters[$name])) {
					$param_value = $parameters[$name]();
				}
				if (preg_match(sprintf('@^%s$@u', str_replace('@', '\@', $pattern)), $param_value) !== 1) {
					continue 2;
				}
			}

			//URL構築
			//controllerとactionを付与
			$parameters['controller']	= $controller_name;
			$parameters['action']		= $action_name;

			//オミットセットは交換可能にする
			foreach ($var_parameters as $var_parameter) {
				$parameters[$var_parameter] = sprintf('{:%s}', $var_parameter);
			}

			//改めてマッチングセット構築
			$work_matching_set = array_combine($parameter_list, $pattern_list);

			$url = $regex_url;
			foreach ($work_matching_set as $parameter_name => $pattern) {
				$url = preg_replace(sprintf('@%s@u', str_replace('@', '\@', preg_quote($pattern))), $parameters[$parameter_name], $url, 1);
			}

			//キャッシュ追加
			if (!isset(static::$_reverseUrl[$controller_name][$action_name][$parameter_name_list_hath])) {
				static::$_reverseUrl[$controller_name][$action_name][$parameter_name_list_hath] = [$controller_pattern, $action_pattern, $matching_set, $connection];
			}

			//マッチするURLがあった場合
			return '/' . $url;
		}

		//マッチするURLがない場合
		return false;
	}

	/**
	 * 指定されたURLが登録されているか検索します。
	 *
	 * @param	string	$url	検索対象URL
	 * @return	array	検索結果
	 */
	public static function Find ($url) {
		$url =ltrim($url, '/');

		$controller	= 'error';
		$action		= 'not_found';
		$parameter	= [];
		$options	= [];

		$default_protocol = ['http', 'https'];

		$current_protocol = static::GetCurrentProtocol();

		//共通設定オプションの有無の取得
		$enable_common_options = !empty(static::$_commonOptions);

		//ルールベースオプションの有無の判定
		$rule_base_options = [];
		$rule_base_option_master = static::$_ruleBaseOptions;
		ksort($rule_base_option_master);
		foreach ($rule_base_option_master as $rule => $options) {
			if (preg_match(sprintf("/^%s$/", str_replace('/', "\\/", ltrim($rule, '/'))), $url) === 1) {
				$rule_base_options = array_merge($rule_base_options, $options);
			}
		}

		$pre_options = array_merge(static::$_commonOptions, $rule_base_options);
		if (isset($pre_options['error'])) {
			$error_options = $pre_options['error'];
			if (isset($error_options['controller'])) {
				$controller = $error_options['controller'];
			} else if (isset($error_options[0])) {
				$controller = $error_options[0];
			}
			if (isset($error_options['action'])) {
				$action = $error_options['action'];
			} else if (isset($error_options[1])) {
				$action = $error_options[1];
			}
		}

		//Connection単位で処理
		foreach (static::$_connectionList as $connection) {
			//下準備
			$path = ltrim($connection['path'], '/');

		 	if (!isset(static::$_reverseUrl[':path:'][$path])) {
		 		static::$_reverseUrl[':path:'][$path] = static::PursePathRegex($path);
			}
			list($regex_url, $parameter_list, $pattern_list) = static::$_reverseUrl[':path:'][$path];

			//マッチング
			$match_pattern = sprintf('@^%s$@u', str_replace('@', '\@', $regex_url));

			$match_pattern = str_replace('\ud', '[1-9][0-9]*|0', $match_pattern);
			$match_pattern = str_replace('\nn', '[1-9][0-9]*', $match_pattern);

			if (preg_match($match_pattern, $url, $matches) == 1) {
				$parameter = [
					static::ROUTING_URL	=> array_shift($matches),
				];

				foreach (array_filter($parameter_list, function ($value) {return $value !== '';}) as $idx => $parameter_name) {
					$parameter[$parameter_name] = $matches[$idx];
				}

				$options = $connection['options'];
				if ($enable_common_options && $options !== null) {
					$options = array_merge(static::$_commonOptions, $options);
				}
				$options = array_merge($rule_base_options, $options);

				$protocol = (array) (isset($options['protocol']) ? $options['protocol'] : $default_protocol);

				if (!in_array($current_protocol, $protocol, true)) {
					continue;
				}

				$controller = isset($options['controller']) ? $options['controller'] : null;
				$controller = $controller ?: (isset($options[0]) ? $options[0] : null);
				$controller = $controller ?: (isset($parameter['controller']) ? $parameter['controller'] : 'index');
				$controller = str_replace('/', '_', $controller);

				$action = isset($options['action']) ? $options['action'] : null;
				$action = $action ?: (isset($options[1]) ? $options[1] : null);
				$action = $action ?: (isset($parameter['action']) ? $parameter['action'] : 'index');
				$action = str_replace('/', '_', $action);

				return compact('controller', 'action', 'parameter', 'options');
			}
		}

		$controller = str_replace('/', '_', $controller);
		$action = str_replace('/', '_', $action);

		return compact('controller', 'action', 'parameter', 'options');
	}

	/**
	 * パスから正規表現パートを抽出します。
	 *
	 * @param	string	$path		解析するパス
	 * @param	array	抽出した正規表現パート
	 */
	public static function PursePathRegex ($path) {
		//チャンク
		$chunk = str_split($path);
		$chunk_length = count($chunk);

		//エスケープレベル
		$escape_lv = 0;

		//スタッカ
		$stack = [];

		//パターンスタッカ
		$pattern_list = [];

		//パラメータスタッカ
		$parameter_list = [];

		//一括精査
		for ($i = 0;$i < $chunk_length;$i++) {
			$char = $chunk[$i];

			//エスケープ文字の積み上げ
			if ("\\" === $char) {
				$escape_lv++;
				$stack[] = $char;
				continue;
			}

			//エスケープされているかどうか見ながら処理
			if (($escape_lv % 2 === 0) && '{' === $char && ':' === $chunk[$i + 1]) {
				//ブレスレベル
				$breath_lv = 1;

				//エスケープレベルのリセット
				$escape_lv = 0;

				//パラメータ名
				$parameter_name = '';

				//パターン
				$pattern = '';

				//inner stack
				$inner_stack = [];

				//現在のモード
				$mode = 'parameter';

				//処理対象パート
				for ($i += 2;$i < $chunk_length;$i++) {
					$char = $chunk[$i];

					//パラメータモード
					if ($mode === 'parameter') {
						//パラメータ区切り文字が出るまで積み上げ
						if (($escape_lv % 2 === 0) && $char === ':') {
							$mode = 'pattern';

							//パラメータ名の抽出
							$parameter_name = implode('', $inner_stack);
							$inner_stack = [];
							continue;
						}
					}

					//エスケープ文字の積み上げ
					if ("\\" === $char) {
						$escape_lv++;
						$inner_stack[] = $char;
						continue;
					}

					//エスケープされていないbreathがあった場合積み上げ
					if (($escape_lv % 2 === 0) && '{' === $char) {
						$inner_stack[] = $char;
						$breath_lv++;
						continue;
					}

					//エスケープされていないbreathがあった場合積み下げ
					if (($escape_lv % 2 === 0) && '}' === $char) {
						$breath_lv--;
						//breathが0段になった時点で終了
						if ($breath_lv === 0) {
							break;
						}
						$inner_stack[] = $char;
						continue;
					}

					//エスケープは終わりました
					$escape_lv = 0;

					//インナースタックに積み上げ
					$inner_stack[] = $char;
				}

				//パターンの抽出
				$pattern = implode('', $inner_stack);
				$pattern = ($pattern ?: '[^/]*');

				//スタックに詰める
				$stack[] = '('. $pattern .')';

				//parameter名のリスト
				$parameter_list[] = $parameter_name;

				//パターンのリスト
				$pattern = str_replace('\ud', '[1-9][0-9]*|0', $pattern);
				$pattern = str_replace('\nn', '[1-9][0-9]*', $pattern);
				$pattern_list[] = '('. $pattern .')';

				//繰り返しもどし
				continue;
			}

			//文字が変わっているのでエスケープレベルを0に
			$escape_lv = 0;

			//スタックの積み上げ
			$stack[] = $char;
		}

		//処理の終了
		return [implode('', $stack), $parameter_list, $pattern_list];
	}

	/**
	 * 現在接続中のプロトコルを返します。
	 *
	 * @return	string	プロトコル
	 */
	public static function GetCurrentProtocol () {
		$https_flag = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
		return $https_flag ? 'https' : 'http';
	}
}
