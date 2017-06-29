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

use ickx\fw2\router\Router;
use ickx\fw2\vartype\objects\Objects;
use ickx\fw2\vartype\arrays\Arrays;

class Twig_Extension_Function extends \Twig_Extension {
	use	\ickx\fw2\traits\data_store\ClassVariableTrait,
		traits\RequestDataSetTrait,
		traits\UtilityTrait;

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
	public function getFunctions () {
		return [
			//==============================================
			//base
			//==============================================
			new \Twig_Function('asset',					[$this, 'asset']),
			new \Twig_Function('asset_common',			[$this, 'assetCommon']),
			new \Twig_Function('asset_cdn',				[$this, 'assetCdn']),
			new \Twig_Function('make_url',				[$this, 'makeUrl']),
			new \Twig_Function('make_domain_url',		[$this, 'makeDomainUrl']),
			new \Twig_Function('make_force_domain_url',	[$this, 'makeForceDomainUrl']),
			new \Twig_Function('current_url',			[$this, 'currentUrl']),
			new \Twig_Function('current_full_url',		[$this, 'currentFullUrl']),
			new \Twig_Function('filter',				[$this, 'filter'], ['needs_environment' => true]),
			new \Twig_Function('replace_sub_domain',	[$this, 'replaceSubDomain']),
			new \Twig_Function('action_switch',			[$this, 'actionSwitch']),

			//==============================================
			//var type
			//==============================================
			new \Twig_Function('var_dump',		'var_dump'),

			new \Twig_Function('empty',			[$this, 'isEmpty']),

			new \Twig_Function('adjust_array',	'\ickx\fw2\vartype\arrays\Arrays::AdjustArray'),
			new \Twig_Function('array_unshift',	[$this, 'arrayUnshift']),
			new \Twig_Function('array_push',	[$this, 'arrayPush']),
			new \Twig_Function('array_add',		[$this, 'arrayAdd']),
			new \Twig_Function('array_merge',	[$this, 'merge']),
			new \Twig_Function('implode',		'implode'),
			new \Twig_Function('explode',		'explode'),
			new \Twig_Function('array_filter',	'array_filter'),

			new \Twig_Function('merge',			[$this, 'merge']),

			new \Twig_Function('late_array_bind',	[$this, 'lateArrayBind']),

			//==============================================
			//Native Functions
			//==============================================
			new \Twig_Function('time',	'time'),
			new \Twig_Function('date',	'date'),

			//==============================================
			//disp support
			//==============================================
			new \Twig_Function('adjust',		[$this, 'adjust']),
			new \Twig_Function('params_adjust',	[$this, 'paramsAdjust']),
			new \Twig_Function('sprintf',		'sprintf'),
			new \Twig_Function('has_validator',	[$this, 'hasValidator']),
			new \Twig_Function('elvis',			[$this, 'elvis']),
			new \Twig_Function('switch',		[$this, 'elvis']),

			new \Twig_Function('make_id',		[$this, 'makeId']),

			new \Twig_Function('json_encode',	[$this, 'jsonEncode']),
			new \Twig_Function('json_decode',	[$this, 'jsonDecode']),

			//==============================================
			//form data set
			//==============================================
			//form status
			new \Twig_Function('form_open',				[$this, 'formOpen']),
			new \Twig_Function('form_status',			[$this, 'formStatus']),
			new \Twig_Function('is_post_form',			[$this, 'isPostForm']),
			new \Twig_Function('form_close',			[$this, 'formClose']),
			//file upload
			new \Twig_Function('file_upload',			[$this, 'fileUpload']),
			//CURRENT REQUEST data
			new \Twig_Function('exist_request_data',	[$this, 'existRequestData']),
			new \Twig_Function('request_data',			[$this, 'getRequestData']),
			new \Twig_Function('request_data_set',		[$this, 'getRequestDataSet']),
			//GET parameter
			new \Twig_Function('exist_parameter',		[$this, 'existParameter']),
			new \Twig_Function('parameter',				[$this, 'getParameter']),
			new \Twig_Function('parameters',			[$this, 'getParameters']),
			//POST data
			new \Twig_Function('exist_form_data',		[$this, 'existFormData']),
			new \Twig_Function('form_data',				[$this, 'getFormData']),
			new \Twig_Function('form_data_set',			[$this, 'getFormDataSet']),
			//message
			//error
			new \Twig_Function('exist_errors',			[$this, 'existError']),
			new \Twig_Function('errors',				[$this, 'getError']),
			new \Twig_Function('errors_set',			[$this, 'getErrorSet']),
			//warn
			new \Twig_Function('exist_warns',			[$this, 'existWarn']),
			new \Twig_Function('warns',					[$this, 'getWarn']),
			new \Twig_Function('warns_set',				[$this, 'getWarnSet']),
			//info
			new \Twig_Function('exist_infos',			[$this, 'existInfo']),
			new \Twig_Function('infos',					[$this, 'getInfo']),
			new \Twig_Function('infos_set',				[$this, 'getInfoSet']),
		];
	}

	//==============================================
	//twig function拡張
	//==============================================
	/**
	 * doc_rootからのパスを追記します
	 *
	 * @param	string	$path_part	指定するパス。
	 * @return	string	doc_rootからのパスが追加されたパス
	 */
	public function asset ($path_part) {
		return Flywheel::AssetUrl($path_part);
	}

	/**
	 * 共通ファイル用のdoc_rootからのパスを追記します。
	 *
	 * @param	string	$path_part	指定するパス
	 * @return	string	共通ファイル用のdoc_rootからのパスが追加されたパス
	 */
	public function assetCommon ($path_part) {
		return Flywheel::AssetUrl('/com' . $path_part);
	}

	/**
	 * CDN自動切換え用のパス追記を行います。
	 * ※未実装
	 *
	 * @param	string	$path_part	指定するパス
	 * @return	string	共通ファイル用のdoc_rootからのパスが追加されたパス
	 */
	public function assetCdn ($path_part) {
		return static::assetCommon($path_part);
	}

	/**
	 * RouterからURLを取得します。
	 *
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
	public function makeUrl ($controller_name, $action_name = '', $parameters = [], $var_parameters = [], $query = []) {
		if ($controller_name === null) {
			return '';
		}

		$query = !empty($query) ? '?'. http_build_query($query) : '';
		return $this->asset(Router::GetUrl($controller_name, $action_name ?: '', $parameters ?: [], $var_parameters ?: [])) . $query;
	}

	/**
	 * Routerからドメイン名付のURLを取得します。
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
	public function makeDomainUrl ($controller_name, $action_name = '', $parameters = [], $var_parameters = [], $query = []) {
		if ($controller_name === null) {
			return '';
		}
		$url = Router::GetUrl($controller_name, $action_name, $parameters, $var_parameters);
		$connection = Router::Find($url);
		$options = isset($connection['options']) ? $connection['options'] : [];
		$protocol = isset($options['protocol']) ? $options['protocol'] : Router::GetCurrentProtocol();

		if (is_array($protocol)) {
			$schema = count($protocol) > 1 ? '//' : $protocol[0] . '://';
		} else {
			$schema = $protocol . '://';
		}

		$query = !empty($query) ? '?'. http_build_query($query) : '';

		return sprintf('%s%s%s', $schema, $_SERVER['HTTP_HOST'], $this->asset($url)) . $query;
	}

	/**
	 * Routerから別ドメイン名付のURLを取得します。
	 *
	 */
	public function makeForceDomainUrl ($host_name, $controller_name, $action_name = '', $parameters = [], $var_parameters = [], $query = []) {
		if ($controller_name === null) {
			return '';
		}
		$url = Router::GetUrl($controller_name, $action_name, $parameters, $var_parameters);
		$connection = Router::Find($url);
		$options = isset($connection['options']) ? $connection['options'] : [];
		$protocol = isset($options['protocol']) ? $options['protocol'] : Router::GetCurrentProtocol();

		if (preg_match("@^https?://@", $host_name) === false) {
			if (is_array($protocol)) {
				$schema = count($protocol) > 1 ? '//' : $protocol[0] . '://';
			} else {
				$schema = $protocol . '://';
			}
		} else {
			$schema = '';
		}

		$query = !empty($query) ? '?'. http_build_query($query) : '';

		return sprintf('%s%s%s%s', $schema, $host_name, $this->asset($url), $query);
	}

	/**
	 * ドメイン名のサブドメインを指定した文字列で置き換えます。
	 *
	 * @param unknown $subject
	 * @param unknown $replacement
	 */
	public function replaceSubDomain ($replacement, $protocol = null, $domain_name = null) {
		$domain_name = $domain_name !== null ? $domain_name : $_SERVER['HTTP_HOST'];

		if (preg_match("@^https?://@", $domain_name, $match) !== false) {
			$domain_name = str_replace($match[1], '', $domain_name);
		} else {
			preg_match("@^https?://@", $_SERVER['HTTP_HOST'], $match);
		}
		$protocol = $protocol !== null ? $protocol . '://' : $match[1];

		$domain = explode('.', $domain_name);
		$length = count($domain);

		$offset = in_array($domain[$length - 1], [
			'co',
			'or',
			'ne',
			'ac',
			'ad',
			'ed',
			'go',
			'gr',
			'lg',
		], true) ? 3 : 2;

		return sprintf('%s%s.%s', $protocol, $replacement, implode('.', array_slice($domain, $length - $offset, $length)));
	}

	/**
	 * アクセス時のURLを返します。
	 *
	 * @return	string	アクセス時のURL
	 */
	public function currentUrl () {
		return Flywheel::AssetUrl(Flywheel::GetCurrnetUrl());
	}

	/**
	 * アクセス時のプロトコル、ドメイン名まで含めたURLを返します。
	 *
	 * @return	string	アクセス時の完全なURL
	 */
	public function currentFullUrl () {
		return Flywheel::AssetFullUrl(Flywheel::GetCurrnetUrl());
	}

	public function filter ($env, $value, $filter_list = null) {
		if ($filter_list === null) {
			return $value;
		}
		if (!is_array($filter_list)) {
			$filter_list = [$filter_list];
		}

		foreach ($filter_list as $filter_set) {
			if (is_array($filter_set)) {
				$filter_name = key($filter_set);
				$args = current($filter_set);
			} else {
				$filter_name = $filter_set;
				$args = [];
			}
			$args = $args ?: [];

			$filter = $env->getFilter($filter_name);
			if ($filter instanceof \Twig_Filter_Function) {
				$function = Objects::ForceGetProperty($filter, 'function');
				array_unshift($args, $value);
				$value = call_user_func_array($function, $args);
			} elseif ($filter instanceof \Twig_Filter_Method) {
				$extension = Objects::ForceGetProperty($filter, 'extension');
				$method = Objects::ForceGetProperty($filter, 'method');
				array_unshift($args, $value);

				$value = call_user_func_array([$extension, $method], $args);
			}
		}
		return $value;
	}

	public function arrayUnshift ($array, $var) {
		array_unshift($array, $var);
		return $array;
	}

	public function arrayPush ($array, $var) {
		return $array[] = $var;
	}

	public function arrayAdd ($array, $name, $var) {
		if ($array === null) {
			$array = [];
		}
		$array[$name] = $var;
		return $array;
	}

	public function adjust ($condition, $label) {
		return $condition ? $label : '';
	}

	public function merge () {
		$result = [];
		foreach (func_get_args() as $array) {
			if ($array === null) {
				continue;
			}
			$result = array_merge($result, (array) $array);
		}
		return $result;
	}

	public function actionSwitch ($action_name, $text, $default = '') {
		return DI::GetClassVar('render')['action'] === str_replace('/', '_', $action_name ?? 'index') ? $text : $default;
	}

	public function paramsAdjust ($params, $adjuster = []) {
		$add_list = $adjuster[0] ?? $adjuster['add'] ?? [];
		$remove_list =$adjuster[1] ?? $adjuster['remove'] ?? $adjuster['delete'] ?? [];

		foreach ($add_list as $key => $value) {
			$params[$key] = $value;
		}

		foreach ((array) $remove_list as $key) {
			unset($params[$key]);
		}

		return $params;
	}

	public function hasValidator () {
		return $_POST['data']['has_validator'] ?? DI::GetClassVar('render')['has_validator'] ?? false;
	}

	public function elvis ($condition, $true, $false = '') {
		return $condition ? $true : $false;
	}

	public function makeId ($attribute_list, $name, $value, $optional) {
		if (isset($attribute_list['id'])) {
			return $attribute_list['id'];
		}

		return (implode('_', array_filter([
			$name,
			!is_array($value) && !is_object($value) ? $value : $optional
		], function ($value) {return !(is_null($value) || $value === '' || $value === false);})));
	}

	public function jsonEncode ($value) {
		return json_encode($value, \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT);
	}

	public function jsonDecode ($value) {
		return json_decode($value);
	}

	public function lateArrayBind ($target_list, $sub_data = []) {
		$render = DI::GetClassVar('render');

		foreach ($target_list as $key => $target) {
			if (is_callable($target)) {
				$target_list[$key] = $target($render);
			} else if (is_array($target)){
				$target_list[$key] = Arrays::GetLowest($render, $target);
			} else {
				$target_list[$key] = $render[$target];
			}
		}

		return $target_list;
	}
}
