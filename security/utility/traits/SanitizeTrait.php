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
 * @package		security
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\security\utility\traits;

/**
 * エスケープ特性です。
 *
 * セキュリティに適したサニタイズ処理の実体をもちます。
 *
 * @category	Flywheel2
 * @package		security
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait SanitizeTrait {
	public static function Sanitize ($string, $type = 'html') {
		switch ($type) {
			case 'html':
			default:
				return static::SanitizeHtml($html);
		}
	}

	public static function 	SanitizeCrLf () {
		return strtr($string, static::GetWatchCrLfCodeSet());
	}

	public static function SanitizeControlCode ($string) {
		return strtr($string, static::GetWatchControlCodeSet());
	}

	public static function SanitizeUnicodeControlCode ($string) {
		return strtr($string, static::GetWatchUnicodeControlCodeSet());
	}

	public static function SanitizeHtml ($html) {
		return strip_tags($html);
	}

	public static function SanitizeSpecificTagsHtmlFragment ($html, $sanitize_tags = null, $sanitize_attributes = null, $restriction_schemas = null, $restriction_schema_target_list = null, $encoding = null) {
		!empty($encoding) ?: $encoding = mb_internal_encoding();
		!empty($sanitize_tags) ?: $sanitize_tags = static::GetDefaultWatchTags();
		!empty($sanitize_attributes) ?: $sanitize_attributes = static::GetDefaultWatchAttributes();
		!empty($restriction_schemas) ?: $restriction_schemas = static::GetRestrictionSchemas();
		!empty($restriction_schema_target_list) ?: $restriction_schema_target_list = static::GetRestrictionSchemaTagets();

		$html = static::SanitizeControlCode($html);
		$html = static::SanitizeUnicodeControlCode($html);
		$html = sprintf('<?xml version="1.0" encoding="%s"?><root>%s</root>', $encoding, $html);

		$dom = new \DOMDocument;

		libxml_use_internal_errors(true);
		$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		libxml_clear_errors();

		foreach ($sanitize_tags as $sanitize_tag) {
			$elements = $dom->getElementsByTagName($sanitize_tag);
			for ($i = $elements->length - 1;-1 < $i;$i--) {
				$element = $elements->item($i);
				$element->parentNode->removeChild($element);
			}
		}

		foreach ($sanitize_attributes as $target_tag_name => $sanitize_attribute_list) {
			$elements = $dom->getElementsByTagName($target_tag_name);
			foreach ($elements as $element) {
				foreach ($sanitize_attribute_list as $sanitize_attribute_name) {
					$element->removeAttribute($sanitize_attribute_name);
				}
			}
		}

		foreach ($restriction_schemas as $restriction_schema) {
			$restriction_schema = $restriction_schema . ':';
			foreach ($restriction_schema_target_list as $target_tag_name => $target_attribute_list) {
				$elements = $dom->getElementsByTagName($target_tag_name);
				foreach ($elements as $element) {
					foreach ($target_attribute_list as $target_attribute_name) {
						$attribute_value = $element->getAttribute($target_attribute_name);
						if (stripos($attribute_value, $restriction_schema) !== false) {
							$element->removeAttribute($target_attribute_name);
						}
						$element->removeAttribute($target_attribute_name);
					}
				}
			}
		}

		return substr(substr($dom->saveHTML($dom->getElementsByTagName('root')->item(0)), 0, -7), 6);
	}
}
