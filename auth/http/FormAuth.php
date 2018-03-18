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
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\auth\http;

use ickx\fw2\core\net\http\Request;

/**
 * Form認証を扱います。
 *
 * @category	Flywheel2
 * @package		auth
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class FormAuth {
	use	\ickx\fw2\traits\singletons\Multiton,
	\ickx\fw2\traits\magic\Accessor;

	//==============================================
	// オブジェクトプロパティ
	//==============================================
	// 設定値
	//----------------------------------------------
	/**
	 * @property	string	認証フォームホスト名：設定する場合はスキーマの指定も必要
	 *				デフォルトはNULL NULLの場合は現在のスキーマとホスト名が使用される
	 */
	protected $host			= null;

	/**
	 * @property	string	認証フォームパス：認証フォームのURLパスパートを設定
	 * 						設定必須
	 */
	protected $path			= null;

	/**
	 * @property	array	認証フォームクエリ：認証フォームのクエリを設定
	 */
	protected $query		= [];

	/**
	 * @property	callable	割り込み認証回避判定処理
	 */
	protected $isAuthForm	= null;

	/**
	 * @property	callable	認証処理を行います。
	 */
	protected $execAuth		= null;

	//==============================================
	// 認証フロー設定
	//==============================================
	/**
	 * 認証が維持されているか確認します。
	 *
	 * @return	bool	認証が維持されている場合はtrue、そうでない場合はfalse
	 */
	public function auth () {
		// 有効な認証クッキーが無い場合
		if (!$this->getAuthSession()) {
			// 認証フォームにいてかつ認証用パラメータが存在する場合は認証を試行する
			if ($this->isAuthForm() && $this->hasAuthParameters()) {
				$ret = $this->execAuth();
				if (!is_array($ret)) {
					return false;
				}
			} else {
				// 認証ページへリダイレクト
				$this->redirectUrl($this->getAuthorizeUrl());

				// 認証クッキー仮発行
				$this->updateAuthSeseein(['origin_url' => $_SERVER['REQUEST_URI']]);
				return false;
			}
		}

		//　ここまで到達できている場合、有効な認証があると判断する
		return true;
	}

	/**
	 * 認可状態を剥奪します。
	 */
	public function deprive () {
		$this->authSession->close();
		$this->authSession->tmpClose();
	}

	/**
	 * 認証状態を更新します。
	 *
	 * @return	bool	認証状態の更新に成功した場合はtrue、失敗した場合はfalse
	 */
	public function updateAuthToken () {
		return $this->updateAuthSession($user_name, $password);
	}

	//==============================================
	// パブリックメソッド
	//==============================================
	/**
	 * 認証URLを返します。
	 *
	 * @return	string	認証URL
	 */
	public function getAuthorizeUrl () {
		return sprintf(
			'%s%s?%s',
			$this->host ?? Request::GetDomainName(),
			$this->path,
			empty($this->query) ? '' : '?' . http_build_query($this->query)
		);
	}
}
