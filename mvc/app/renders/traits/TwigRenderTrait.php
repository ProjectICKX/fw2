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

namespace ickx\fw2\mvc\app\renders\traits;

/**
 * twigを利用してレンダリングを行います。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait TwigRenderTrait {
	/**
	 * レンダリングを行います。
	 *
	 * @return	string	レンダリング結果
	 */
	public function twigRender ($template_file = null, $render = null, $template_dir_list = [], $options = []) {
		$this->renderSetting();
		$template = $this->twigLoadFileTemplate($template_file, $template_dir_list, $options);
		$render = $this->adjustRender($render);
		return $template->render($render);
	}

	public function twigTextRender ($template_text, $render = null, $template_dir_list = [], $options = []) {
		$this->renderSetting();
		$template = $this->twigLoadStringTemplate($template_text, $template_dir_list, $options);
		$render = $this->adjustRender($render);
		return $template->render($render);
	}

	public function twigTemplateSetting ($template_text = null, $template_dir_list = [], $options = []) {
		$allow_parameter_list = ['debug', 'trim_blocks', 'charset', 'base_template_class', 'cache', 'auto_reload', 'extension_list', 'filter_list'];
		$options = [
			'target'	=> 'Twig_Environment',
			'options'	=> [
				'cache'		=> ['prefix' => $this->templateCacheDir],
			],
			'name'	=> 'TwigRender用設定',
			'cache_dir'		=> FilePath::INI_CACHE_DIR(),
			'static_cache'	=> true,
		];
		$twig_config = IniFile::GetConfig($this->iniPath, $allow_parameter_list, $options);

		if (empty($template_dir_list)) {
			$template_dir_list = $this->templateDirList;
		} else {
			$template_dir_list += $this->templateDirList;
		}

		$twig = new \Twig_Environment(
			$template_text !== null || isset($this->textTemplate) ? new \Twig_Loader_String : new \Twig_Loader_Filesystem($template_dir_list),
			$twig_config
		);

		$this->templateExtList = $twig_config['extension_list'] ?? [];
		foreach ($this->templateExtList as $templateExt) {
			if (file_exists(ClassLoader::ClassPathToRealFilePath($templateExt['name']))) {
				$twig->addExtension(new $templateExt['name']($templateExt['value']));
			}
		}

		$this->templateFilterList = $twig_config['filter_list'] ?? [];
		foreach ($this->templateFilterList as $templateFilter) {
			if (file_exists(ClassLoader::ClassPathToRealFilePath($templateFilter['name']))) {
				$twig->addExtension(new $templateFilter['name']($templateFilter['value']));
			}
		}

		$this->layout = $this->layout ?: 'default';

		return $twig;
	}

	/**
	 *
	 */
	public function twigLoadStringTemplate ($template_text, $template_dir_list = [], $options = []) {
		$twig = $this->twigTemplateSetting($template_text, $template_dir_list, $options);
		return $twig->loadTemplate($template_text);
	}

	/**
	 * テンプレートファイルをロードします。
	 *
	 * @return	Object	テンプレートインスタンス
	 */
	public function twigLoadFileTemplate ($template_file = null, $template_dir_list = [], $options = []) {
		if (isset($this->textTemplate)) {
			return $this->twigLoadStringTemplate($this->textTemplate, $template_dir_list);
		}

		$twig = $this->twigTemplateSetting(null, $template_dir_list, $options);
		$this->templateDir = $this->templateDir ?: $this->controller;
		$this->template = $this->template ?: $this->action;

		if ($template_file === null) {
			if (!isset($this->templateFile) || $this->templateFile === null) {
				$this->templateFile = PathTrait::CreateFilePath([implode('.', [Strings::ToSnakeCase($this->template), $this->mimeType, 'twig'])]);
			}
			$template_file = $this->templateFile;
		}

		return $twig->loadTemplate($template_file);
	}
}
