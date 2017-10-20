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

namespace ickx\fw2\mvc\app\controllers\traits;

/**
 * セッション特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait SessionTrait {
	/**
	 * 全てのセッションを破壊します。
	 */
	public static function DestroySession () {
		Request::RemovePostData('__suid');
		Session::Destroy();
	}

	//======================================================
	//コントローラクラス内セッション
	//======================================================
	/**
	 * コントローラクラス内で共有されるセッションに書き込みます。
	 *
	 * @param	string	$name	名前
	 * @param	mixed	$value	型
	 */
	public static function WriteClassSession ($name, $value, $class_path = null) {
		Session::Write(static::_GetSeesionClassLayerPath($name, $class_path ?: static::class), $value);
	}

	/**
	 * コントローラクラス内で共有されるセッションに値があるか調べます。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	値
	 */
	public static function ExistsClassSession ($name = null, $class_path = null) {
		return Session::Exists(static::_GetSeesionClassLayerPath($name, $class_path ?: static::class));
	}

	/**
	 * コントローラクラス内で共有されるセッションから読み込みます。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	値
	 */
	public static function ReadClassSession ($name = null, $class_path = null) {
		return Session::Read(static::_GetSeesionClassLayerPath($name, $class_path ?: static::class));
	}

	/**
	 * SUIDセッション内で共有されるセッション名のリストを返します。
	 *
	 * @return	array	SUIDセッション内で共有されているセッション名のリスト。
	 */
	public static function GetClassSessionNames ($class_path = null) {
		return Session::Read(static::_GetSeesionClassLayerPath([], $class_path ?: static::class));
	}

	/**
	 * コントローラクラス内で共有されるセッションから削除します。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	削除された値
	 */
	public static function RemoveClassSession ($name, $class_path = null) {
		return Session::Delete(static::_GetSeesionClassLayerPath($name, $class_path ?: static::class));
	}

	/**
	 * コントローラクラス内で共有するセッションを削除します。
	 */
	public static function DeleteClassSession ($class_path = null) {
		Session::Delete(static::_GetSeesionClassLayerPath(null, $class_path ?: static::class));
	}

	//======================================================
	//SUIDセッション
	//======================================================
	/**
	 * SUIDセッション内で共有されるセッションに書き込みます。
	 *
	 * @param	string	$name	名前
	 * @param	mixed	$value	型
	 */
	public static function WriteSuidSession ($name, $value) {
		if (!static::SeesionOnSu()) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('suidセッションが開始されていません。');
		}
		Session::Write(static::_GetSeesionLayerPath($name), $value);
	}

	/**
	 * SUIDセッション内で共有されるセッションに値があるか調べます。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	値
	 */
	public static function ExistsSuidSession ($name = null) {
		if (!static::SeesionOnSu()) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('suidセッションが開始されていません。');
		}
		return Session::Exists(static::_GetSeesionLayerPath($name));
	}

	/**
	 * SUIDセッション内で共有されるセッションから読み込みます。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	値
	 */
	public static function ReadSuidSession ($name = null) {
		if (!static::SeesionOnSu()) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('suidセッションが開始されていません。');
		}
		return Session::Read(static::_GetSeesionLayerPath($name));
	}

	/**
	 * SUIDセッション内で共有されるセッション名のリストを返します。
	 *
	 * @return	array	SUIDセッション内で共有されているセッション名のリスト。
	 */
	public static function GetSuidSessionNames () {
		if (!static::SeesionOnSu()) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('suidセッションが開始されていません。');
		}
		return array_keys(Session::Read(static::_GetSeesionLayerPath()));
	}

	/**
	 * SUIDセッション内で共有されるセッションから削除します。
	 *
	 * @param	string	$name	名前
	 * @return	mixed	削除された値
	 */
	public static function RemoveSuidSession ($name = null) {
		if (!static::SeesionOnSu()) {
			throw \ickx\fw2\core\exception\CoreException::RaiseSystemError('suidセッションが開始されていません。');
		}
		Session::Delete(static::_GetSeesionLayerPath($name));
	}

	/**
	 * SUIDセッション内で共有されるセッションを削除します。
	 */
	public static function DeleteSuidSession () {
		Request::RemovePostData('__suid');
		Session::Delete(static::_GetSeesionSuidLayerPath());
	}

	/**
	 * SUIDセッションをリスタートさせます。
	 */
	public static function RestartSuidSession () {
		static::DeleteSuidSession();
		$new_suid = static::GenerateSuid();
		Request::OverWritePostData('__suid', $new_suid);
		static::CreateSuidLayer($new_suid);
	}

	/**
	 * 現在のSUIDセッションを消します。
	 */
	public static function ExtinguishSession () {
		Request::RemovePostData('__suid');
		Session::Extinguish();
	}

	/**
	 * SUIDセッションを開始します。
	 */
	public static function StartSuidSession () {
		$suid = static::SeesionSuid();
		if ($suid === false) {
			$suid = static::GenerateSuid();
			static::CreateSuidLayer($suid);
		}
		Request::OverWritePostData('__suid', $suid);
	}

	/**
	 * SUIDを構築します。
	 */
	public static function GenerateSuid ($prefix = 'R-S2O ', $safix = ' Y1', $hash_arlg = 'sha256') {
		return hash($hash_arlg, $prefix . microtime(true) . $safix);
	}

	/**
	 * セッション内にSUIDレイヤーを構築します。
	 *
	 * @param	string	$new_suid	SUID
	 */
	public static function CreateSuidLayer ($new_suid) {
		Session::Write(static::_GetSeesionSuidLayerPath($new_suid), []);
	}

	/**
	 * SUIDを取得します。
	 */
	public static function SeesionSuid () {
		if ($suid = Request::GetPostData()->__suid) {
			if (!static::EnableSuidSession()) {
				throw CoreException::RaiseSystemError('存在しない__suidを指定されました。__suid:%s', [Request::GetPostData()->__suid]);
			}
			return $suid;
		}
		return false;
	}

	/**
	 * 有効なSuidSessionかどうか返します。
	 *
	 * @return	bool	trueの場合は有効なsuid sessionがある、falseの場合はない
	 */
	public static function EnableSuidSession () {
		return !(Request::GetPostData()->__suid && !isset(Session::Read(static::_GetSeesionTransactionLayerPath())[Request::GetPostData()->__suid]));
	}

	/**
	 * 現在のSUIDを取得します。
	 *
	 * @return	string	現在のSUID
	 */
	public static function SeesionCurrentSuid () {
		return Request::GetPostData()->__suid ?: static::GenerateSuid();
	}

	/**
	 * SUID内にいるかどうかを返します。
	 *
	 * @return bool	SUID内にいる場合はtrue, そうでない場合はfalse
	 */
	public static function SeesionOnSu () {
		return (Request::GetPostData()->__suid ?? null !== null);
	}

	//======================================================
	//セッションレイヤー構築配列
	//======================================================
	/**
	 * コントローラクラス内共有セッション用レイヤー指定配列を取得します。
	 *
	 * @param	$name	下位層の名前
	 * @return	array	コントローラクラス内共有セッション用レイヤー指定配列
	 */
	protected static function _GetSeesionClassLayerPath ($name = [], $class_path = null) {
		$classes_layer_path = [
			'fw2',
			'classes',
			$class_path ?: static::class
		];
		return array_merge($classes_layer_path, Arrays::AdjustArray($name));
	}

	/**
	 * SUIDセッション用レイヤー指定配列を取得します。
	 *
	 * @return	array	SUIDセッション用レイヤー指定配列
	 */
	protected static function _GetSeesionTransactionLayerPath () {
		return [
			'fw2',
			'transactions',
		];
	}

	/**
	 * SUIDセッション内共有セッション用レイヤー指定配列を取得します。
	 *
	 * @return	array	SUIDセッション内共有セッション用レイヤー指定配列
	 */
	protected static function _GetSeesionSuidLayerPath ($suid = null) {
		return array_merge(
			static::_GetSeesionTransactionLayerPath(),
			[$suid ?: static::SeesionSuid() ?? static::GenerateSuid()]
		);
	}

	/**
	 * セッションレイヤー指定配列を取得します。
	 *
	 * @param	$name	下位層の名前
	 * @return	array	セッションレイヤー指定配列
	 */
	protected static function _GetSeesionLayerPath ($name = null) {
		$base_layer_path = array_merge(
			static::_GetSeesionSuidLayerPath(),
			[static::class]
		);
		return array_merge($base_layer_path, Arrays::AdjustArray($name));
	}

	//======================================================
	//セッション利用支援
	//======================================================
	//------------------------------------------------------
	//SUIDセッション
	//------------------------------------------------------
	/**
	 * SUIDセッションを初期化します。
	 */
	public function initSuidSession () {
		if (!static::SeesionOnSu()) {
			static::StartSuidSession();
		}
	}

	/**
	 * 現在のポストデータをSUIDセッションに設定します。
	 *
	 * 絞り込み用のkey_listが指定されない場合、ポストデータで送られてきた全ての要素がSUIDセッションに登録されます。
	 *
	 * @param	array	$key_list			SUIDセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 */
	public function postDataToSuidSession ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? array_keys($this->request->data->getArrayCopy()) : $key_list;

		$parent_path_list = (array) $parent_path_list;

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				static::WriteSuidSession($key, $this->request->data[$key]);
			}
		} else {
			$recursive_array_creater = function ($path_list, $array = [], $value = null) use (&$recursive_array_creater) {
				$array[array_shift($path_list)] = empty($path_list) ? $value : $recursive_array_creater($path_list, $array, $value);
				return $array;
			};

			$tmp_list = empty($key_list) ? null : [];
			foreach ($key_list as $key) {
				$tmp_list[$key] = $this->request->data[$key];
			}

			$base_path = array_shift($parent_path_list);
			if (!empty($parent_path_list)) {
				$tmp_list = $recursive_array_creater($parent_path_list, [], $tmp_list);
			}

			static::WriteSuidSession($base_path, $tmp_list);
		}
	}

	/**
	 * SUIDセッションに展開されているデータを表示用変数にアサインします。
	 *
	 * @param	array	$key_list			SUIDセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 * @return	array	$assign_data_set	アサイン用データ配列
	 */
	public function suidSessionToAssignData ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? static::GetSuidSessionNames() : $key_list;

		$parent_path_list = (array) $parent_path_list;

		$assign_data_set = [];

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				$assign_data_set[$key] = static::ReadSuidSession($key);
			}
		} else {
			$suid_session = static::ReadSuidSession($parent_path_list);
			foreach ($key_list as $key) {
				$assign_data_set[$key] = isset($suid_session[$key]) ? $suid_session[$key] : null;
			}
		}

		return $assign_data_set;
	}

	/**
	 * SUIDセッションに展開されているデータをポストデータに上書きします。
	 *
	 * @param	array	$key_list			SUIDセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 */
	public function suidSessionToPostData ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? static::GetSuidSessionNames() : $key_list;

		$parent_path_list = (array) $parent_path_list;

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				static::overWritePostData($key, static::ReadSuidSession($key));
			}
		} else {
			$suid_session = static::ReadSuidSession($parent_path_list);
			foreach ($key_list as $key) {
				static::overWritePostData($key, isset($suid_session[$key]) ? $suid_session[$key] : null);
			}
		}
	}

	//------------------------------------------------------
	//クラスセッション
	//------------------------------------------------------
	/**
	 * 現在のポストデータをクラスセッションに設定します。
	 *
	 * 絞り込み用のkey_listが指定されない場合、ポストデータで送られてきた全ての要素がクラスセッションに登録されます。
	 *
	 * @param	array	$key_list			クラスセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 */
	public function postDataToClassSession ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? array_keys($this->request->data->getArrayCopy()) : $key_list;
		$parent_path_list = (array) $parent_path_list;

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				static::WriteClassSession($key, $this->request->data[$key]);
			}
		} else {
			$recursive_array_creater = function ($path_list, $array = [], $value = null) use (&$recursive_array_creater) {
				$array[array_shift($path_list)] = empty($path_list) ? $value : $recursive_array_creater($path_list, $array, $value);
				return $array;
			};

			$tmp_list = empty($key_list) ? null : [];
			foreach ($key_list as $key) {
				$tmp_list[$key] = $this->request->data[$key];
			}

			$base_path = array_shift($parent_path_list);
			if (!empty($parent_path_list)) {
				$tmp_list = $recursive_array_creater($parent_path_list, [], $tmp_list);
			}

			static::WriteClassSession($base_path, $tmp_list);
		}
	}

	/**
	 * クラスセッションに展開されているデータを表示用変数にアサインします。
	 *
	 * @param	array	$key_list			クラスセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 * @return	array	$assign_data_set	アサイン用データ配列
	 */
	public function classSessionToAssignData ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? static::GetClassSessionNames() : $key_list;

		$parent_path_list = (array) $parent_path_list;

		$assign_data_set = [];

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				$assign_data_set[$key] = static::ReadClassSession($key);
			}
		} else {
			$class_session = static::ReadClassSession($parent_path_list);
			foreach ($key_list as $key) {
				$assign_data_set[$key] = isset($class_session[$key]) ? $class_session[$key] : null;
			}
		}

		return $assign_data_set;
	}

	/**
	 * クラスセッションに展開されているデータをポストデータに上書きします。
	 *
	 * @param	array	$key_list			クラスセッション登録対象絞り込み用キー配列
	 * @param	array	$parent_path_list	値の混在を避けるためのセッション側パス
	 */
	public function classSessionToPostData ($key_list = [], $parent_path_list = []) {
		$key_list = (array) $key_list;
		$key_list = empty($key_list) ? static::GetClassSessionNames() : $key_list;

		$parent_path_list = (array) $parent_path_list;

		if (empty($parent_path_list)) {
			foreach ($key_list as $key) {
				static::overWritePostData($key, static::ReadClassSession($key));
			}
		} else {
			$class_session = static::ReadClassSession($parent_path_list);
			foreach ($key_list as $key) {
				static::overWritePostData($key, isset($class_session[$key]) ? $class_session[$key] : null);
			}
		}
	}

	//------------------------------------------------------
	//クラスフラッシュセッション
	//------------------------------------------------------
	/**
	 * 次リクエストでだけ有効なセッションを破棄します。
	 */
	public function clearFlashClassSession () {
		if (static::ExistsClassSession('flash')) {
			static::RemoveClassSession('flash');
		}

		if (static::ExistsClassSession('tmp_flash')) {
			static::WriteClassSession('flash', static::ReadClassSession('tmp_flash'));
			static::RemoveClassSession('tmp_flash');
		}
	}

	/**
	 * 次リクエストでだけ有効なセッションを設定します。
	 *
	 * @param	string	$name	キー名
	 * @param	mixed	$data	データ
	 */
	public function setFlashClassSession ($name, $data) {
		static::WriteClassSession(['flash', $name], $data);
	}

	/**
	 * 今リクエストでだけ有効なセッションを取得します。
	 *
	 * @param	string	$name		キー名
	 * @param	mixed	$default	対象が存在しなかった場合のデフォルト値
	 * @return	mixed	値
	 */
	public function getFlashClassSession ($name, $default = null) {
		return static::ExistsClassSession(['flash', $name]) ? static::ReadClassSession(['flash', $name]) : $default;
	}

	/**
	 * 今リクエストでだけ有効なセッションを次リクエストにフォワードします。
	 *
	 * @param	string	$name	キー名
	 */
	public function forwardFlashClassSession ($name) {
		if (static::ExistsClassSession(['flash', $name])) {
			static::WriteClassSession(['tmp_flash', $name], $before_request = static::ReadClassSession(['flash', $name]));
		}
	}

	/**
	 * 現在のリクエストデータを次リクエストのみ有効なセッションに設定します。
	 *
	 * @param	string	$alias
	 */
	public function setRequestFlashClassSession ($alias = 'default') {
		$route = $this->route;
		unset($route['routing_url']);

		if (isset($route['controller'])) {
			unset($route['controller']);
		}
		if (isset($route['action'])) {
			unset($route['action']);
		}

		static::WriteClassSession(['tmp_flash', 'request', $alias], [
			'controller'	=> $this->rawController,
			'action'		=> $this->rawAction,
			'route'			=> $route->getRecursiveArrayCopy(),
			'parameter'		=> $this->request->parameter->getRecursiveArrayCopy(),
		]);
	}

	/**
	 * 前リクエストでのリクエストデータを返します。
	 *
	 * @param	string	$alias	エイリアス
	 * @return	mixed	リクエストデータ
	 */
	public function getRequestFlashClassSession ($alias = 'default') {
		if (is_null($request = static::ReadClassSession(['flash', 'request', $alias]))) {
			return [];
		}

		$url	= static::MakeUrl($request['controller'], $request['action'], $request['route']);

		return [
			'request'	=> $request,
			'url'		=> $url === false ? null : $url .= empty($request['parameter']) ? '' : '?'. http_build_query($request['parameter']),
		];
	}

	/**
	 * 今リクエストでだけ有効なセッションを次リクエストにフォワードします。
	 *
	 * @param	string	$alias	エイリアス
	 */
	public function forwardRequestFlashClassSession (...$alia_list) {
		foreach ($alia_list as $alias) {
			if (static::ExistsClassSession(['flash', 'request', $alias])) {
				static::WriteClassSession(['tmp_flash', 'request', $alias], $before_request = static::ReadClassSession(['flash', 'request', $alias]));
			}
		}
	}

	/**
	 * 今リクエストだけで有効なリクエスト情報をエイリアスリスト順に走査し、マッチしたリクエスト情報を返します。
	 *
	 * @param	string|array	$alia_list		エイリアスリスト
	 * @param	string			$default_url	見つからなかった場合のURL
	 */
	public function choiceRequestFlashClassSession ($alia_list, $default_url = null) {
		if (!static::ExistsClassSession(['flash', 'request'])) {
			return [
				'url'	=> $default_url,
			];
		}
		$request = static::ReadClassSession(['flash', 'request']);

		foreach ((array) $alia_list as $alias) {
			if (isset($request[$alias])) {
				return $this->getRequestFlashClassSession($alias);
			}
		}
		return [
			'url'	=> $default_url,
		];
	}

	/**
	 * 今リクエストだけで有効なリクエスト情報をエイリアスリスト順に走査し、マッチしたリクエスト情報を返します。
	 *
	 * @param	string|array	$alia_list		エイリアスリスト
	 * @param	string			$default_url	見つからなかった場合のURL
	 */
	public function choiceRequestUrlFlashClassSession ($alia_list, $default_url = null) {
		return $this->choiceRequestFlashClassSession($alia_list, $default_url)['url'];
	}
}
