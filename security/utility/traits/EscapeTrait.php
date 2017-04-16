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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\security\utility\traits;

/**
 * エスケープ特性です。
 *
 * セキュリティに適したエスケープ処理の実体をもちます。
 *
 * @category	Flywheel2
 * @package		security
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait EscapeTrait {
	public static function Escape ($string, $type = 'html') {
		switch ($type) {
			case 'html':
			default:
				return static::EscapeHtml($html);
		}
	}

	public static function EscapeCrLf () {
		return strtr($string, static::GetEscapeCrLfCodeSet());
	}

	public static function EscapeControlCode ($string) {
		return strtr($string, static::GetEscapeControlCodeSet());
	}

	public static function EscapeUnicodeControlCode ($string) {
		return strtr($string, static::GetEscapeUnicodeControlCodeSet());
	}

	public static function EscapeHtml ($html, $encoding = null) {
		!empty($encoding) ?: $encoding = mb_internal_encoding();
		return htmlspecialchars($html, \ENT_QUOTES, $encoding);
	}

	public static function EscapeJS ($code, $encoding = null) {
		!empty($encoding) ?: $encoding = mb_internal_encoding();
		return preg_replace('/[0-9a-f]{4}/', 'u$0', bin2hex(mb_convert_encoding($code, 'UTF-16', $encoding)));
	}

	public static function EscapeSpecificTagsHtmlFragment ($html, $sanitize_tags = null, $sanitize_attributes = null, $restriction_schemas = null, $restriction_schema_target_list = null, $encoding = null) {
		!empty($encoding) ?: $encoding = mb_internal_encoding();
		!empty($sanitize_tags) ?: $sanitize_tags = static::GetDefaultSanitizeTags();
		!empty($sanitize_attributes) ?: $sanitize_attributes = static::GetDefaultSanitizeAttributes();
		!empty($restriction_schemas) ?: $restriction_schemas = static::GetRestrictionSchemas();
		!empty($restriction_schema_target_list) ?: $restriction_schema_target_list = static::GetRestrictionSchemaTagets();

		$html = static::EscapeControlCode($html);
		$html = static::EscapeUnicodeControlCode($html);
		$html = sprintf('<?xml version="1.0" encoding="%s"?><root>%s</root>', $encoding, $html);

		$dom = new \DOMDocument;

		libxml_use_internal_errors(true);
		$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $encoding));
		libxml_clear_errors();

		foreach ($sanitize_tags as $sanitize_tag) {
			$elements = $dom->getElementsByTagName($sanitize_tag);
			for ($i = $elements->length - 1;-1 < $i;$i--) {
				$element = $elements->item($i);
				$parentNode = $element->parentNode;
				$parentNode->replaceChild(new \DOMText($element->C14N(false, true)), $element);
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
