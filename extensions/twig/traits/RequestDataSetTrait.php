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
 * @package		commons
 * @author		wakaba
 * @copyright	Copyright 2012, Project ICKX. (http://www.ickx.jp/)
 * @license		require consultation
 * @varsion		0.0.1
 */

namespace ickx\fw2\extensions\twig\traits;

use ickx\fw2\container\DI;
use ickx\fw2\core\net\http\Request;
use ickx\fw2\vartype\arrays\Arrays;

trait RequestDataSetTrait {
	//form status
	public function formOpen ($value = 'POST') {
		static::SetClassVar(['static_var', 'form_status'], strtoupper($value));
	}

	public function isPostForm () {
		return static::GetClassVar(['static_var', 'form_status']) === 'POST';
	}

	public function formStatus () {
		return static::GetClassVar(['static_var', 'form_status']);
	}

	public function formClose () {
		static::SetClassVar(['static_var', 'form_status'], null);
	}

	//file upload
	public function fileUpload ($option_name = null) {
		$render = DI::GetClassVar('render');
		if (!isset($render['file_upload'])) {
			return false;
		}
		if ($option_name === null) {
			return true;
		}
		return isset($render['file_upload'][$option_name]) ? $render['file_upload'][$option_name] : null;
	}

	//CURRENT REQUEST data
	public function existRequestData ($name) {
		return Request::IsPostMethod() ? $this->existFormData($name) : $this->existParameter($name);
	}

	public function getRequestData ($name, $value = null) {
		return $name === null ? $value : (Request::IsPostMethod() ? $this->getFormData($name, $value) : $this->getParameter($name, $value));
	}

	public function getRequestDataSet () {
		return Request::IsPostMethod() ? $this->getFormDataSet() : $this->getParameters();
	}

	//GET parameter
	public function existParameter ($name) {
		return is_array($name) ? Arrays::ExistsLowest(Request::GetParameters(), $name) : Arrays::KeyExists(Request::GetParameters(), $name);
	}

	public function getParameter ($name, $value = null) {
		return (($ret = Arrays::GetLowest(Request::GetParameters(), array_map(function ($value) {return str_replace(['[', ']'], '', $value);}, Arrays::AdjustArray($name)))) === null) ? $value : $ret;
	}

	public function getParameters () {
		return Request::GetParameters();
	}

	//POST data
	public function existFormData ($name) {
		return is_array($name) ? Arrays::ExistsLowest(Request::GetPostData(), $name) : Arrays::KeyExists(Request::GetPostData(), $name);
	}

	public function getFormData ($name, $value = null) {
		return (($ret = Arrays::GetLowest(Request::GetPostData(), array_map(function ($value) {return str_replace(['[', ']'], '', $value);}, Arrays::AdjustArray($name)))) === null) ? $value : $ret;
	}

	public function getFormDataSet () {
		return Request::GetPostData();
	}

	//message
	//error
	public function existError ($name) {
		return Arrays::ExistsLowest(DI::GetClassVar('render'), array_merge(['error'], (array) $name));
	}

	public function getError ($name) {
		return Arrays::GetLowest(DI::GetClassVar('render'), array_merge(['error'], (array) $name));
	}

	public function getErrorSet () {
		return Arrays::KeyExists(DI::GetClassVar('render'), 'error') ? DI::GetClassVar('render')['error'] : null;
	}

	//warn
	public function existWarn ($name) {
		return Arrays::ExistsLowest(DI::GetClassVar('render'), array_merge(['warn'], (array) $name));
	}

	public function getWarn ($name) {
		return Arrays::GetLowest(DI::GetClassVar('render'), array_merge(['warn'], (array) $name));
	}

	public function getWarnSet () {
		return Arrays::KeyExists(DI::GetClassVar('render'), 'warn') ? DI::GetClassVar('render')['warn'] : null;
	}

	//info
	public function existInfo ($name) {
		return Arrays::ExistsLowest(DI::GetClassVar('render'), array_merge(['info'], (array) $name));
	}

	public function getInfo ($name) {
		return Arrays::GetLowest(DI::GetClassVar('render'), array_merge(['info'], (array) $name));
	}

	public function getInfoSet () {
		return Arrays::KeyExists(DI::GetClassVar('render'), 'info') ? DI::GetClassVar('render')['info'] : null;
	}
}
