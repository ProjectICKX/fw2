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

trait SecurityUtilityTrait {
	public static function GetDefaultWatchTags () {
		return [
			'script',
			'style',
		];
	}

	public static function GetDefaultWatchAttributes ($omit_list = []) {
		$attr_list = [
			'style',
			'animationend',
			'animationiteration',
			'animationstart',
			'onabort',
			'onafterprint',
			'onanimationend',
			'onanimationiteration',
			'onanimationstart',
			'onaudioprocess',
			'onbeforeprint',
			'onbeforeunload',
			'onbeginEvent',
			'onblocked',
			'onblur',
			'oncached',
			'oncanplay',
			'oncanplaythrough',
			'onchange',
			'onchargingchange',
			'onchargingtimechange',
			'onchecking',
			'onclick',
			'onclose',
			'oncompassneedscalibration',
			'oncomplete',
			'oncompositionend',
			'oncompositionstart',
			'oncompositionupdate',
			'oncontextmenu',
			'oncopy',
			'oncuechange',
			'oncut',
			'ondbclick',
			'ondblclick',
			'ondevicelight',
			'ondevicemotion',
			'ondeviceorientation',
			'ondeviceproximity',
			'ondischargingtimechange',
			'ondownloading',
			'ondrag',
			'ondragend',
			'ondragenter',
			'ondragleave',
			'ondragover',
			'ondragstart',
			'ondrop',
			'ondurationchange',
			'onemptied',
			'onended',
			'onendEvent',
			'onerror',
			'onfocus',
			'onfocusin',
			'onfocusinUnimplemented',
			'onfocusout',
			'onfocusoutUnimplemented',
			'onfullscreenchange',
			'onfullscreenerror',
			'ongamepadconnected',
			'ongamepaddisconnected',
			'onhashchange',
			'oninput',
			'oninvalid',
			'onkeydown',
			'onkeypress',
			'onkeyup',
			'onlanguagechange',
			'onlevelchange',
			'onload',
			'onloadeddata',
			'onloadedmetadata',
			'onloadend',
			'onloadstart',
			'onmessage',
			'onmousedown',
			'onmouseenter',
			'onmouseleave',
			'onmousemove',
			'onmouseout',
			'onmouseover',
			'onmouseup',
			'onmousewheel',
			'onnoupdate',
			'onobsolete',
			'onoffline',
			'ononline',
			'onopen',
			'onorientationchange',
			'onpagehide',
			'onpageshow',
			'onpaste',
			'onpause',
			'onplay',
			'onplaying',
			'onpointerlockchange',
			'onpointerlockerror',
			'onpopstate',
			'onprogress',
			'onratechange',
			'onreadystatechange',
			'onrepeatEvent',
			'onreset',
			'onresize',
			'onscroll',
			'onsearch',
			'onseeked',
			'onseeking',
			'onselect',
			'onselectstart',
			'onshow',
			'onstalled',
			'onstorage',
			'onsubmit',
			'onsuccess',
			'onsuspend',
			'ontimeout',
			'ontimeupdate',
			'ontoggle',
			'ontouchcancel',
			'ontouchend',
			'ontouchenter',
			'ontouchleave',
			'ontouchmove',
			'ontouchstart',
			'ontransitionend',
			'onunload',
			'onupdateready',
			'onupgradeneeded',
			'onuserproximity',
			'onversionchange',
			'onvisibilitychange',
			'onvolumechange',
			'onwaiting',
			'onwheel',
			'transitionend',
			'DOMActivate',
			'DOMAttributeNameChanged',
			'DOMAttrModified',
			'DOMCharacterDataModified',
			'DOMContentLoaded',
			'DOMElementNameChanged',
			'DOMFocusIn Unimplemented',
			'DOMFocusOut Unimplemented',
			'DOMNodeInserted',
			'DOMNodeInsertedIntoDocument',
			'DOMNodeRemoved',
			'DOMNodeRemovedFromDocument',
			'DOMSubtreeModified',
			'SVGAbort',
			'SVGError',
			'SVGLoad',
			'SVGResize',
			'SVGScroll',
			'SVGUnload',
			'SVGZoom',
		];

		foreach ($omit_list as $attr_name) {
			if (($idx = array_search($attr_name, $attr_list, true)) !== false) {
				unset($attr_list[$idx]);
			}
		}

		return [
			'*'	=>	$attr_list,
		];
	}

	public static function GetRestrictionSchemas () {
		return [
			'javascript',
			'data',
		];
	}

	public static function GetRestrictionSchemaTagets () {
		return [
			'a'			=> ['href'],
			'img'		=> ['src'],
			'script'	=> ['src'],
			'link'		=> ['href'],
			'meta'		=> ['content'],
			'object'	=> ['data'],
			'iframe'	=> ['src'],
		];
	}

	public static function GetEscapeCrLfCodeSet () {
		return [
			"\r\n"	=> '\r\n',
			"\r"	=> '\r',
			"\n"	=> '\n'
		];
	}

	public static function GetWatchCrLfCodeSet () {
		return array_fill_keys(static::GetEscapeCrLfCodeSet(), '');
	}

	public static function GetEscapeControlCodeSet () {
		return [
			"\x00" => '\x00',
			"\x01" => '\x01',
			"\x02" => '\x02',
			"\x03" => '\x03',
			"\x04" => '\x04',
			"\x05" => '\x05',
			"\x06" => '\x06',
			"\x07" => '\x07',
			"\x08" => '\x08',
			"\x09" => '\x09',
			"\x0B" => '\x0B',
			"\x0C" => '\x0C',
			"\x0E" => '\x0E',
			"\x0F" => '\x0F',
			"\x10" => '\x10',
			"\x11" => '\x11',
			"\x12" => '\x12',
			"\x13" => '\x13',
			"\x14" => '\x14',
			"\x15" => '\x15',
			"\x16" => '\x16',
			"\x17" => '\x17',
			"\x18" => '\x18',
			"\x19" => '\x19',
			"\x1A" => '\x1A',
			"\x1B" => '\x1B',
			"\x1C" => '\x1C',
			"\x1D" => '\x1D',
			"\x1F" => '\x1F',
			"\x7F" => '\x7F',
		];
	}

	public static function GetWatchControlCodeSet () {
		return array_fill_keys(static::GetEscapeControlCodeSet(), '');
	}

	public static function GetEscapeUnicodeControlCodeSet () {
		return [
			"\u2400"	=> '\u2400',
			"\u2401"	=> '\u2401',
			"\u2402"	=> '\u2402',
			"\u2403"	=> '\u2403',
			"\u2404"	=> '\u2404',
			"\u2405"	=> '\u2405',
			"\u2406"	=> '\u2406',
			"\u2407"	=> '\u2407',
			"\u2408"	=> '\u2408',
			"\u2409"	=> '\u2409',
			"\u240A"	=> '\u240A',
			"\u240B"	=> '\u240B',
			"\u240C"	=> '\u240C',
			"\u240D"	=> '\u240D',
			"\u240E"	=> '\u240E',
			"\u240F"	=> '\u240F',
			"\u2410"	=> '\u2410',
			"\u2411"	=> '\u2411',
			"\u2412"	=> '\u2412',
			"\u2413"	=> '\u2413',
			"\u2414"	=> '\u2414',
			"\u2415"	=> '\u2415',
			"\u2416"	=> '\u2416',
			"\u2417"	=> '\u2417',
			"\u2418"	=> '\u2418',
			"\u2419"	=> '\u2419',
			"\u241A"	=> '\u241A',
			"\u241B"	=> '\u241B',
			"\u241C"	=> '\u241C',
			"\u241D"	=> '\u241D',
			"\u241E"	=> '\u241E',
			"\u241F"	=> '\u241F',
			"\u2420"	=> '\u2420',
			"\u2421"	=> '\u2421',
			"\u2422"	=> '\u2422',
			"\u2423"	=> '\u2423',
			"\u2424"	=> '\u2424',
			"\u2425"	=> '\u2425',
			"\u2426"	=> '\u2426',
		];
	}


	public static function GetWatchUnicodeControlCodeSet () {
		return array_fill_keys(static::GetEscapeUnicodeControlCodeSet(), '');
	}
}
