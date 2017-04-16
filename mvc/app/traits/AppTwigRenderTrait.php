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

namespace ickx\fw2\mvc\app\traits;

use ickx\fw2\io\php_ini\PhpIni;

/**
 * AppTwigRenderTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppTwigRenderTrait {
	use \ickx\fw2\mvc\app\renders\traits\TwigRenderTrait;

	/**
	 * レンダー初期化設定
	 */
	public function renderSetting () {
		$this->templateDirList = array_merge([
			FilePath::TEMPLATES_PATH(),
			FilePath::CreateDirPath([FilePath::TEMPLATES_PATH(), 'layouts']),
			FilePath::APP_PATH(),
			FilePath::CONTROLLER_PATH(),
			FilePath::CreateDirPath([FilePath::CONTROLLER_PATH(), $this->templateDir, 'template', 'layout', '/']),
			FilePath::CreateDirPath([FilePath::CONTROLLER_PATH(), $this->templateDir, 'template', '/']),
			FilePath::COMS_TEMPLATES_PATH(),
			FilePath::FW2_CONTROLLER_PATH([$this->controller, 'template', '/']),
			FilePath::FW2_EXT_TEMPLATES_PATH(),
		], $this->templateExtDirList);
		$this->templateDirList = array_filter($this->templateDirList, function ($value) {clearstatcache(true, $value);return file_exists($value);});

		$cache_dir_path = FilePath::TWIG_CACHE_DIR();
		clearstatcache(true, $cache_dir_path);

		if (!file_exists($cache_dir_path)) {
			$parent_dir = dirname($cache_dir_path);
			if (is_writable($parent_dir)) {
				if (!mkdir($cache_dir_path, 0777, true)) {
					throw CoreException::RaiseSystemError('Twig用キャッシュディレクトリを作成できません。親ディレクトリに書き込み権限がありません。dir path:%s', [$parent_dir]);
				}
			} else {
				throw CoreException::RaiseSystemError('Twig用キャッシュディレクトリを作成できません。親ディレクトリに書き込み権限がありません。dir path:%s', [$parent_dir]);
			}
		}

		if ((\PHP_OS === 'WINNT' || \PHP_OS === 'WIN32')) {
			$cache_dir_path = mb_convert_encoding($cache_dir_path, 'SJIS-win', 'UTF-8');
		}

		if (!is_executable($cache_dir_path) && \PHP_OS !== 'WINNT' && \PHP_OS !== 'WIN32') {
			for ($parent_dir = dirname($cache_dir_path);is_executable($parent_dir);$parent_dir = dirname($parent_dir));
			throw CoreException::RaiseSystemError('Twig用キャッシュディレクトリを開けません。error dir:%s, dir path:%s', [$parent_dir, $cache_dir_path]);
		}
		if (!is_writable($cache_dir_path)) {
			throw CoreException::RaiseSystemError('Twig用キャッシュディレクトリに書き込めません。dir path:%s', [$cache_dir_path]);
		}

		$this->templateCacheDir	= $cache_dir_path;

		$ini_path = FilePath::TWIG_INI_PATH();
		if (!file_exists($ini_path)) {
			$ini_path = implode('/', [FilePath::VENDOR_DIR(), 'ickx', 'fw2', 'mvc', 'defaults', 'config', 'twig', 'twig.ini']);
		}
		$this->iniPath			= $ini_path;
	}

	/**
	 * アプリケーション毎の調整メソッド
	 */
	public function adjustRender ($render = null) {
		$render = $render ?: $this->render;

		if ($render === null) {
			$render = [];
		} else if ($render instanceof \ickx\fw2\vartype\arrays\LazyArrayObject) {
			$render = $render->getRecursiveArrayCopy();
		} else if ($render instanceof \ArrayObject) {
			$render = $render->getArrayCopy();
		}

		if (!isset($render['data'])) {
			$render['data'] = [];
		}

		$render['file_upload'] = isset($this->fileUpload) ? $this->fileUpload : null;

		$render['url'] = (isset($render['url']) ? $render['url'] : '');
		$render['data'] += (isset($render['data']) ? $render['data'] : []) + Request::GetPostData()->getArrayCopy();
		$render['parameters'] = $this->request->parameter instanceof \ArrayObject ? $this->request->parameter->getArrayCopy() : $this->request->parameter;
		$render['querys'] = Request::GetParameters() instanceof \ArrayObject? Request::GetParameters()->getArrayCopy() : Request::GetParameters();

		$render['controller']	= $this->controller;
		$render['action']		= $this->action;

		$render['error'] = $this->error;
		$render['warn'] = $this->warn;
		$render['info'] = $this->info;

		$options = $this->options->getArrayCopy();
		foreach ($options as $key => $value) {
			$options[$key] = preg_replace_callback("/\{:(.+?)\}/", function (array $matches) use ($render, $options) {$replace = isset($render[$matches[1]]) ? $render[$matches[1]] : (isset($options[$matches[1]]) ? $options[$matches[1]] : $matches[0]);return is_callable($replace) ? $replace($render, $options) : $replace;}, is_callable($value) ? $value($render, $options) : $value);
		}
		$render += $render + $options;

		$render['canonical_url']	= isset($render['canonical_url']) ? $render['canonical_url'] : parse_url(''.$_SERVER['REQUEST_URI'], \PHP_URL_PATH);

		$render['layout_path']	= implode('.', [Strings::ToSnakeCase($this->layout), $this->mimeType, 'twig']);

		DI::Connect('render', $render);

		return $render;
	}

	/**
	 * レンダリング
	 */
	public function render ($template_file = null, $render = null, $template_dir = []) {
		return $this->twigRender($template_file, $render, $template_dir);
	}

	public function textRender ($template_text, $render = null, $template_dir = []) {
		return $this->twigTextRender($template_text, $render, $template_dir);
	}
}
