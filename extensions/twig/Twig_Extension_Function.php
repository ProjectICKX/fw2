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
use ickx\fw2\container\DI;

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
			new \Twig_SimpleFunction ('asset',					[$this, 'asset']),
			new \Twig_SimpleFunction ('asset_common',			[$this, 'assetCommon']),
			new \Twig_SimpleFunction ('asset_cdn',				[$this, 'assetCdn']),
			new \Twig_SimpleFunction ('make_url',				[$this, 'makeUrl']),
			new \Twig_SimpleFunction ('make_domain_url',		[$this, 'makeDomainUrl']),
			new \Twig_SimpleFunction ('make_force_domain_url',	[$this, 'makeForceDomainUrl']),
			new \Twig_SimpleFunction ('current_url',			[$this, 'currentUrl']),
			new \Twig_SimpleFunction ('current_full_url',		[$this, 'currentFullUrl']),
			new \Twig_SimpleFunction ('filter',				[$this, 'filter'], ['needs_environment' => true]),
			new \Twig_SimpleFunction ('replace_sub_domain',	[$this, 'replaceSubDomain']),
			new \Twig_SimpleFunction ('action_switch',			[$this, 'actionSwitch']),
			new \Twig_SimpleFunction ('same_in',				[$this, 'sameIn']),

			new \Twig_SimpleFunction ('class_const',			[$this, 'classConst']),
			new \Twig_SimpleFunction ('find_by_class_const',	[$this, 'findByClassConst']),
			new \Twig_SimpleFunction ('class_property',		[$this, 'classProperty']),
			new \Twig_SimpleFunction ('class_method',			[$this, 'classMethod']),

			//==============================================
			//var type
			//==============================================
			new \Twig_SimpleFunction ('var_dump',		'var_dump'),

			new \Twig_SimpleFunction ('empty',			[$this, 'isEmpty']),

			new \Twig_SimpleFunction ('adjust_array',	'\ickx\fw2\vartype\arrays\Arrays::AdjustArray'),
			new \Twig_SimpleFunction ('array_unshift',	[$this, 'arrayUnshift']),
			new \Twig_SimpleFunction ('array_push',	[$this, 'arrayPush']),
			new \Twig_SimpleFunction ('array_add',		[$this, 'arrayAdd']),
			new \Twig_SimpleFunction ('array_merge',	[$this, 'merge']),
			new \Twig_SimpleFunction ('implode',		'implode'),
			new \Twig_SimpleFunction ('explode',		'explode'),
			new \Twig_SimpleFunction ('array_filter',	'array_filter'),

			new \Twig_SimpleFunction ('merge',			[$this, 'merge']),

			new \Twig_SimpleFunction ('late_array_bind',	[$this, 'lateArrayBind']),

			//==============================================
			//Native Functions
			//==============================================
			new \Twig_SimpleFunction ('time',	'time'),
			new \Twig_SimpleFunction ('date',	'date'),

			new \Twig_SimpleFunction ('strtoupper',	'strtoupper'),
			new \Twig_SimpleFunction ('strtolower',	'strtolower'),

			//==============================================
			//disp support
			//==============================================
			new \Twig_SimpleFunction ('adjust',		[$this, 'adjust']),
			new \Twig_SimpleFunction ('params_adjust',	[$this, 'paramsAdjust']),
			new \Twig_SimpleFunction ('sprintf',		'sprintf'),
			new \Twig_SimpleFunction ('has_validator',	[$this, 'hasValidator']),
			new \Twig_SimpleFunction ('elvis',			[$this, 'elvis']),
			new \Twig_SimpleFunction ('switch',		[$this, 'elvis']),

			new \Twig_SimpleFunction ('make_id',		[$this, 'makeId']),

			new \Twig_SimpleFunction ('json_encode',	[$this, 'jsonEncode']),
			new \Twig_SimpleFunction ('json_decode',	[$this, 'jsonDecode']),

			//==============================================
			//form data set
			//==============================================
			//form status
			new \Twig_SimpleFunction ('form_open',				[$this, 'formOpen']),
			new \Twig_SimpleFunction ('form_status',			[$this, 'formStatus']),
			new \Twig_SimpleFunction ('is_post_form',			[$this, 'isPostForm']),
			new \Twig_SimpleFunction ('form_close',			[$this, 'formClose']),
			//file upload
			new \Twig_SimpleFunction ('file_upload',			[$this, 'fileUpload']),
			//CURRENT REQUEST data
			new \Twig_SimpleFunction ('exist_request_data',	[$this, 'existRequestData']),
			new \Twig_SimpleFunction ('request_data',			[$this, 'getRequestData']),
			new \Twig_SimpleFunction ('request_data_set',		[$this, 'getRequestDataSet']),
			//GET parameter
			new \Twig_SimpleFunction ('exist_parameter',		[$this, 'existParameter']),
			new \Twig_SimpleFunction ('parameter',				[$this, 'getParameter']),
			new \Twig_SimpleFunction ('parameters',			[$this, 'getParameters']),
			//POST data
			new \Twig_SimpleFunction ('exist_form_data',		[$this, 'existFormData']),
			new \Twig_SimpleFunction ('form_data',				[$this, 'getFormData']),
			new \Twig_SimpleFunction ('form_data_set',			[$this, 'getFormDataSet']),
			//message
			//error
			new \Twig_SimpleFunction ('exist_errors',			[$this, 'existError']),
			new \Twig_SimpleFunction ('errors',				[$this, 'getError']),
			new \Twig_SimpleFunction ('errors_set',			[$this, 'getErrorSet']),
			//warn
			new \Twig_SimpleFunction ('exist_warns',			[$this, 'existWarn']),
			new \Twig_SimpleFunction ('warns',					[$this, 'getWarn']),
			new \Twig_SimpleFunction ('warns_set',				[$this, 'getWarnSet']),
			//info
			new \Twig_SimpleFunction ('exist_infos',			[$this, 'existInfo']),
			new \Twig_SimpleFunction ('infos',					[$this, 'getInfo']),
			new \Twig_SimpleFunction ('infos_set',				[$this, 'getInfoSet']),
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
			if (!is_array($filter_set)) {
				$filter_set = [$filter_set];
			}

			$filter_name	= $filter_set[0];
			$args			= isset($filter_set[1]) ? array_slice($filter_set, 1) : [];

			$filter = $env->getFilter($filter_name);
			if ($filter instanceof \Twig_SimpleFilter) {
				$value = $filter->getCallable()($value, ...$args);
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

		return str_replace(['/'], '_', (implode('_', array_filter([
			$name,
			!is_array($value) && !is_object($value) ? $value : $optional
		], function ($value) {return !(is_null($value) || $value === '' || $value === false);}))));
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
			if (is_array($target)) {
				$target_list[$key] = Arrays::GetLowest($render, $target);
			} else if (is_callable($target)) {
				$target_list[$key] = $target($render);
			} else {
				$target_list[$key] = $render[$target];
			}
		}

		return $target_list;
	}

	public function sameIn ($value, $array) {
		return in_array($value, (array) $array, true);
	}

	public function classConst ($class_path, $const_name = null) {
		if (false !== $separate_pos = strpos($class_path, '::')) {
			$class_name = substr($class_path, 0, $separate_pos);
			$const_name = substr($class_path, $separate_pos + 2);
		} else {
			$class_name = $class_path;
		}

		$target_class_path = \ickx\fw2\extensions\twig\Twig_Extension_Store::get('use_class', $class_name, $class_name);

		if (!class_exists($target_class_path)) {
			throw new \ErrorException(sprintf('対象のクラスが見つかりませんでした。class path:%s (<= %s <= %s)', $target_class_path, $class_name, $class_path));
		}

		$define_naem = $target_class_path . '::'. $const_name;

		if (!defined($define_naem)) {
			throw new \ErrorException(sprintf('対象のクラス定数が見つかりませんでした。class const:%s', $define_naem));
		}

		return constant($define_naem);
	}

	public function findByClassConst ($class_path, $const_name = null, $target_key = null, $default_value = null) {
		if (false !== $separate_pos = strpos($class_path, '::')) {
			$class_name = substr($class_path, 0, $separate_pos);
			$const_name = substr($class_path, $separate_pos + 2);
		} else {
			$class_name = $class_path;
			$target_key = $const_name;
			$target_key = $default_value;
		}

		$target_class_path = \ickx\fw2\extensions\twig\Twig_Extension_Store::get('use_class', $class_name, $class_name);

		if (!class_exists($target_class_path)) {
			throw new \ErrorException(sprintf('対象のクラスが見つかりませんでした。class path:%s (<= %s <= %s)', $target_class_path, $class_name, $class_path));
		}

		$define_naem = $target_class_path . '::'. $const_name;

		if (!defined($define_naem)) {
			throw new \ErrorException(sprintf('対象のクラス定数が見つかりませんでした。class const:%s', $define_naem));
		}

		return constant($define_naem)[$target_key] ?? $default_value;
	}

	public function classProperty ($class_path, $property_name = null) {
		if (false !== $separate_pos = strpos($class_path, '::')) {
			$class_name = substr($class_path, 0, $separate_pos);
			$property_name = substr($class_path, $separate_pos + 2);
		} else {
			$class_name = $class_path;
		}

		$target_class_path = \ickx\fw2\extensions\twig\Twig_Extension_Store::get('use_class', $class_name, $class_name);

		if (!class_exists($target_class_path)) {
			throw new \ErrorException(sprintf('対象のクラスが見つかりませんでした。class path:%s (<= %s <= %s)', $target_class_path, $class_name, $class_path));
		}

		if (!property_exists($target_class_path, $property_name)) {
			throw new \ErrorException(sprintf('対象のクラスプロパティが見つかりませんでした。class property:%s::%s', $target_class_path, $property_name));
		}

		return $target_class_path::$property_name;
	}

	public function classMethod ($class_path, ...$args) {
		if (false !== $separate_pos = strpos($class_path, '::')) {
			$class_name = substr($class_path, 0, $separate_pos);
			$method_name = substr($class_path, $separate_pos + 2);
		} else {
			$class_name = $class_path;
			$method_name = $args[0];
			if (isset($args[1])) {
				$args = array_slice($args, 1);
			} else {
				unset($args[0]);
			}
		}

		$target_class_path = \ickx\fw2\extensions\twig\Twig_Extension_Store::get('use_class', $class_name, $class_name);

		if (!class_exists($target_class_path)) {
			throw new \ErrorException(sprintf('対象のクラスが見つかりませんでした。class path:%s (<= %s <= %s)', $target_class_path, $class_name, $class_path));
		}

		if (!is_callable([$target_class_path, $method_name])) {
			throw new \ErrorException(sprintf('対象のクラスメソッドが見つかりませんでした。class method:%s::%s()', $target_class_path, $method_name));
		}

		return $target_class_path::$method_name(...$args);
	}

}
