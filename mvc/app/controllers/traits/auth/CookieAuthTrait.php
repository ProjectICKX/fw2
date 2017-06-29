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

namespace ickx\fw2\mvc\app\controllers\traits\auth;

use ickx\fw2\core\net\http\auth\CookieAuth;

trait CookieAuthTrait {
	/**
	 * @property	string	現在設定されているアルゴリズム
	 * @static
	 */
	private static $_algorithm	= null;

	/**
	 * @property	bool	ログイン中かどうか
	 * @static
	 */
	private static $_isLogin	= false;

	protected static $_cookieAuthForceLoginName		= 'login';
	protected static $_cookieAuthIdName				= 'name';
	protected static $_cookieAuthPasswordName		= 'password';
	protected static $_cookieAuthBeforeLogin		= 'pinching';	// pinching or redirect
	protected static $_cookieAuthAfterLogout		= 'pinching';	// pinching or redirect
	protected static $_cookieAuthLogin				= ['index', 'login'];
	protected static $_cookieAuthLoginAfter			= ['index', 'index'];
	protected static $_cookieAuthLogout				= ['index', 'logout'];
	protected static $_cookieAuthErrorMessage		= 'ユーザIDまたはパスワードが違います。';

	protected function CookieAuthInit ($options) {
		foreach ([
			'force_login_name'	=> '_cookieAuthForceLoginName',
			'id_name'			=> '_cookieAuthIdName',
			'password_name'		=> '_cookieAuthPasswordName',
			'before_login'		=> '_cookieAuthBeforeLogin',
			'login'				=> '_cookieAuthLogin',
			'login_after'		=> '_cookieAuthLoginAfter',
			'logout'			=> '_cookieAuthLogout',
			'error_message'		=> '_cookieAuthErrorMessage',
		] as $key => $value) {
			if (isset($options[$key])) {
				static::$$value = $options[$key];
			}
		}
	}

	protected function IsCookieAuthenticated ($name = CookieAuth::DEFAULT_CONNECTION_NAME) {
		return CookieAuth::IsAuthenticated($name);
	}

	protected function PreCookieAuth ($name = CookieAuth::DEFAULT_CONNECTION_NAME) {
		//認証ページのため、無条件でnullとする。
		//実際の認証はaction側で行わせる。
		if ($this->controller == static::$_cookieAuthLogin[0] && $this->action == static::$_cookieAuthLogin[1]) {
			return null;
		}
		return $this->CookieAuth ($name);
	}

	protected function CookieAuthAction ($name = CookieAuth::DEFAULT_CONNECTION_NAME) {
		if (!is_string($name)) {
			$name = CookieAuth::DEFAULT_CONNECTION_NAME;
		}
		return $this->CookieAuth ($name);
	}

	/**
	 * クッキー認証を行います。
	 *
	 * @return	bool	認証に成功した場合はtrue 失敗した場合はfalse
	 */
	protected function CookieAuth ($name = CookieAuth::DEFAULT_CONNECTION_NAME) {
		if ($this->request->data->{static::$_cookieAuthForceLoginName}) {
			//強制再認証
			$user_name = $this->request->data->{static::$_cookieAuthIdName};
			$password = $this->request->data->{static::$_cookieAuthPasswordName};
			$this->removePostDataSet([static::$_cookieAuthIdName, static::$_cookieAuthPasswordName, $this->request->data->{static::$_cookieAuthForceLoginName}]);
		} else {
			$user_name	= null;
			$password	= null;
		}

		$result = CookieAuth::Auth($user_name, $password, $name);
		if ($result === CookieAuth::STATUS_UNAUTHORIZED) {
			static::CookieAuthNextAction();
			return false;
		}

		if ($result === CookieAuth::STATUS_FAILURE) {
			$this->setErrorList(['login' => [static::$_cookieAuthErrorMessage]]);
			static::CookieAuthNextAction();
			return false;
		}

		return $result;
	}

	protected function CookieAuthNextAction () {
		switch (static::$_cookieAuthBeforeLogin) {
			case 'pinching':
				$this->templateDir	= static::$_cookieAuthLogin[0];
				$this->template		= static::$_cookieAuthLogin[1];
				$this->skipAction();
				$this->cancelEvents();
				break;
			case 'redirect':
				if ($this->controller !== static::$_cookieAuthLogin[0] || $this->action !== static::$_cookieAuthLogin[1]) {
					$this->redirect(Flywheel::AssetUrl(call_user_func_array([static::class, 'MakeUrl'], static::$_cookieAuthLogin)));
					$this->skipAction();
					$this->cancelEvents();
				}
				break;
		}
	}

	protected function CookieAuthLogout ($name = CookieAuth::DEFAULT_CONNECTION_NAME) {
		if (!is_string($name)) {
			$name = CookieAuth::DEFAULT_CONNECTION_NAME;
		}
		CookieAuth::AuthDelete(null, $name);
		if (static::$_cookieAuthAfterLogout === 'redirect') {
			$this->redirect(Flywheel::AssetUrl(call_user_func_array([static::class, 'MakeUrl'], static::$_cookieAuthLogout)));
		$this->skipAction();
		}
		return true;
	}
}
