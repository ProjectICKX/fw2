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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\lang;

use ickx\fw2\international\language\Language;
use ickx\fw2\io\file_system\IniFile;
use ickx\fw2\io\file_system\ArrayIniFile;
use ickx\fw2\core\exception\CoreException;

/**
 * Flywheel2 lang supporter
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait LangTrait {
	protected static $_LangTrait_langMap = [];

	public static function LoadLang ($locale_dir, $lang_name = null) {
		$lang_name !== null ?: $lang_name = Language::GetDefaultAcceptLanguage();

		$lang_file_path = sprintf('%s/%s/message.php', $locale_dir, $lang_name);
		clearstatcache(true, $lang_file_path);

		if (!file_exists($lang_file_path)) {
			$lang_reverse_map = Language::GetReverseGroupMap();
			if (isset($lang_reverse_map[$lang_name])) {
				$lang_name = $lang_reverse_map[$lang_name];

				$lang_file_path = sprintf('%s/%s/message.php', $locale_dir, $lang_name);
				clearstatcache(true, $lang_file_path);

				if (!file_exists($lang_file_path)) {
					throw CoreException::RaiseSystemError('該当する言語にマッチする設定ファイルがありませんでした。file path:%s', [$lang_file_path]);
				}
			}
		}

		$lang_set = ArrayIniFile::GetConfig($lang_file_path, [], ['valid_exsist_key' => false]);
		static::SetLang($lang_set, $lang_name);
	}

	public static function _ ($id, $lang_name = null) {
		$lang_name !== null ?: $lang_name = Language::GetDefaultAcceptLanguage();
		if (!isset(static::$_LangTrait_langMap[$lang_name])) {
			$lang_reverse_map = Language::GetReverseGroupMap();
			$lang_name = $lang_reverse_map[$lang_name];
		}
		return isset(static::$_LangTrait_langMap[$lang_name][$id]) ? static::$_LangTrait_langMap[$lang_name][$id] : $id;
	}

	public static function SetLangMap ($lang_map) {
		static::$_LangTrait_langMap = $lang_map;
	}

	public static function SetLang ($lang_set, $lang_name = null) {
		$lang_name !== null ?: $lang_name = Language::GetDefaultAcceptLanguage();
		if (!empty(static::$_LangTrait_langMap) && !isset(static::$_LangTrait_langMap[$lang_name])) {
			$lang_reverse_map = Language::GetReverseGroupMap();
			$lang_name = $lang_reverse_map[$lang_name];
		}
		static::$_LangTrait_langMap[$lang_name] = $lang_set;
	}

	public static function SetLangMessage ($id, $message, $lang_name = null) {
		$lang_name !== null ?: $lang_name = Language::GetDefaultAcceptLanguage();
		if (!empty(static::$_LangTrait_langMap) && !isset(static::$_LangTrait_langMap[$lang_name])) {
			$lang_reverse_map = Language::GetReverseGroupMap();
			$lang_name = $lang_reverse_map[$lang_name];
		}
		static::$_LangTrait_langMap[$lang_name][$id] = $message;
	}
}
