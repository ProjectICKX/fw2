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
 * Flywheel2 Controller向けRender特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait RenderTrait {
	/** @var	string	描画に使うテンプレートパス：null時は描画機能が無効になる */
	public $renderTemplate = null;

	/** @var	\ickx\fw2\vartype\arrays\LazyArrayObject	描画用データ配列 */
	public $render				= null;

	/** @var	string	設定ファイルパス */
	public $iniPath				= '';

	/** @var	string	テンプレートキャッシュディレクトリ */
	public $templateCacheDir	= '';

	/** @var	string	テンプレートタイプ */
	public $templateType		= 'default';

	/** @var	string	テンプレートファイル名 */
	public $templateFile		= null;

	/** @var	string	！！未実装！！出力時MIME TYPE */
	public $mimeType			= 'html';

	/** @var	string	テンプレートディレクトリパス */
	public $templateDirList		= [];

	/** @var	array	テンプレートディレクトリ拡張リスト */
	public $templateExtDirList	= [];

	/** @var	string	レイアウト名 */
	public $layout				= null;

	/** @var	string	テンプレート名 */
	public $template			= null;

	/** @var	array	テンプレート用拡張リスト */
	public $templateExtList		= [];

	/** @var	array	テンプレート用フィルタリスト */
	public $templateFilterList	= [];

	/**
	 * テンプレートを設定します。
	 *
	 * @param	string	$template	テンプレート名
	 */
	public function setTemplate ($template) {
		$this->template = $template;
	}

	/**
	 * レンダー初期化設定
	 */
	abstract public function renderSetting();

	/**
	 * アプリケーション毎の調整メソッド
	 */
	abstract public function adjustRender($render = null);

	/**
	 * 描画
	 */
	abstract public function render($template_file = null, $render = null, $template_dir = []);

	abstract public function textRender($template_text, $render = null, $template_dir = []);

	/**
	 * 描画しつつ出力
	 *
	 * @return	bool	常にtrue
	 */
	public function rendering () {
		print $this->render();
		return true;
	}

	/**
	 * レンダリング可能かどうかを返します。
	 *
	 * @return	bool	レンダリング可能な場合はtrue, そうでない場合はfalse
	 */
	public function isRendering () {
		return $this->renderTemplate === null;
	}
}
