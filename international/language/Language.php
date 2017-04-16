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
 * @package		international
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\international\language;

/**
 * 自然言語に係る処理を扱います。
 *
 */
class Language {
	//======================================================
	//言語
	//======================================================
	const AF		= 'af';		//アフリカーンス語
	const BE		= 'be';		//白ロシア語
	const BG		= 'bg';		//ブルアリア語
	const CA		= 'ca';		//カタロニア語
	const CS		= 'cs';		//チェコ語
	const DA		= 'da';		//デンマーク語
	const DE		= 'de';		//ドイツ語
	const DE_AU		= 'de-AU';	//ドイツ語/オーストリア
	const DE_CH		= 'de-CH';	//ドイツ語/スイス
	const DE_DE		= 'de-DE';	//ドイツ語/ドイツ
	const EL		= 'el';		//ギリシア語
	const EN		= 'en';		//英語
	const EN_GB		= 'en-GB';	//英語/英国
	const EN_US		= 'en-US';	//英語/米国
	const ES		= 'es';		//スペイン語
	const ES_AR		= 'es-AR';	//スペイン語/アルゼンチン
	const ES_CO		= 'es-CO';	//スペイン語/コロンビア
	const ES_ES		= 'es-ES';	//スペイン語/スペイン
	const ES_MX		= 'es-MX';	//スペイン語/メキシコ
	const EU		= 'eu';		//バスク語
	const FI		= 'fi';		//フィンランド語
	const FO		= 'fo';		//フェロー語
	const FR		= 'fr';		//フランス語
	const FR_BE		= 'fr-BE';	//フランス語/ベルギー
	const FR_CA		= 'fr-CA';	//フランス語/カナダ
	const FR_CH		= 'fr-CH';	//フランス語/スイス
	const FR_FR		= 'fr-FR';	//フランス語/フランス
	const GA		= 'ga';		//アイルランド語
	const GD		= 'gd';		//スコッチ ゲール語
	const GL		= 'gl';		//ガリチア語
	const HR		= 'hr';		//クロアチア語
	const HU		= 'hu';		//ハンガリー語
	const ID		= 'id';		//インドネシア語
	const IS		= 'is';		//アイスランド語
	const IT		= 'it';		//イタリア語
	const JA		= 'ja';		//日本語
	const KO		= 'ko';		//韓国語
	const MK		= 'mk';		//マケドニア語
	const NL		= 'nl';		//オランダ語
	const NL_BE		= 'nl-BE';	//オランダ語/ベルギー語
	const NO		= 'no';		//ノルウェー語
	const PL		= 'pl';		//ポーランド語
	const PT		= 'pt';		//ポルトガル語
	const PT_BR		= 'pt-BR';	//ポルトガル語/ブラジル
	const RO		= 'ro';		//ルーマニア語
	const RU		= 'ru';		//ロシア語
	const SK		= 'sk';		//スロヴァキア語
	const SL		= 'sl';		//スロヴェニア語
	const SQ		= 'sq';		//アルバニア語
	const SR		= 'sr';		//セルビア語
	const SV		= 'sv';		//スウェーデン語
	const TR		= 'tr';		//トルコ語
	const UK		= 'uk';		//ウクライナ語
	const ZH		= 'zh';		//中国語
	const ZH_CN		= 'zh-CN';	//中国語/中国
	const ZH_TW		= 'zh-TW';	//中国語/台湾

	//======================================================
	//言語の日本語訳
	//======================================================
	const MSG_AF		= 'アフリカーンス語';
	const MSG_BE		= '白ロシア語';
	const MSG_BG		= 'ブルアリア語';
	const MSG_CA		= 'カタロニア語';
	const MSG_CS		= 'チェコ語';
	const MSG_DA		= 'デンマーク語';
	const MSG_DE		= 'ドイツ語';
	const MSG_DE_AU		= 'ドイツ語/オーストリア';
	const MSG_DE_CH		= 'ドイツ語/スイス';
	const MSG_DE_DE		= 'ドイツ語/ドイツ';
	const MSG_EL		= 'ギリシア語';
	const MSG_EN		= '英語';
	const MSG_EN_GB		= '英語/英国';
	const MSG_EN_US		= '英語/米国';
	const MSG_ES		= 'スペイン語';
	const MSG_ES_AR		= 'スペイン語/アルゼンチン';
	const MSG_ES_CO		= 'スペイン語/コロンビア';
	const MSG_ES_ES		= 'スペイン語/スペイン';
	const MSG_ES_MX		= 'スペイン語/メキシコ';
	const MSG_EU		= 'バスク語';
	const MSG_FI		= 'フィンランド語';
	const MSG_FO		= 'フェロー語';
	const MSG_FR		= 'フランス語';
	const MSG_FR_BE		= 'フランス語/ベルギー';
	const MSG_FR_CA		= 'フランス語/カナダ';
	const MSG_FR_CH		= 'フランス語/スイス';
	const MSG_FR_FR		= 'フランス語/フランス';
	const MSG_GA		= 'アイルランド語';
	const MSG_GD		= 'スコッチ ゲール語';
	const MSG_GL		= 'ガリチア語';
	const MSG_HR		= 'クロアチア語';
	const MSG_HU		= 'ハンガリー語';
	const MSG_ID		= 'インドネシア語';
	const MSG_IS		= 'アイスランド語';
	const MSG_IT		= 'イタリア語';
	const MSG_JA		= '日本語';
	const MSG_KO		= '韓国語';
	const MSG_MK		= 'マケドニア語';
	const MSG_NL		= 'オランダ語';
	const MSG_NL_BE		= 'オランダ語/ベルギー語';
	const MSG_NO		= 'ノルウェー語';
	const MSG_PL		= 'ポーランド語';
	const MSG_PT		= 'ポルトガル語';
	const MSG_PT_BR		= 'ポルトガル語/ブラジル';
	const MSG_RO		= 'ルーマニア語';
	const MSG_RU		= 'ロシア語';
	const MSG_SK		= 'スロヴァキア語';
	const MSG_SL		= 'スロヴェニア語';
	const MSG_SQ		= 'アルバニア語';
	const MSG_SR		= 'セルビア語';
	const MSG_SV		= 'スウェーデン語';
	const MSG_TR		= 'トルコ語';
	const MSG_UK		= 'ウクライナ語';
	const MSG_ZH		= '中国語';
	const MSG_ZH_CN		= '中国語/中国';
	const MSG_ZH_TW		= '中国語/台湾';

	//======================================================
	//言語グループ
	//======================================================
	/**
	 * @var	array	ドイツ語グループ
	 * @static
	 */
	const GROUP_DE		= [
		self::DE_AU	=> self::DE_AU,	//ドイツ語/オーストリア
		self::DE_CH	=> self::DE_CH,	//ドイツ語/スイス
		self::DE_DE	=> self::DE_DE,	//ドイツ語/ドイツ
	];

	/**
	 * @var	array	英語グループ
	 * @static
	 */
	const GROUP_EN = [
		self::EN_GB		=> self::EN_GB,	//英語/英国
		self::EN_US		=> self::EN_US,	//英語/米国
	];

	/**
	 * @var	array	スペイン語グループ
	 * @static
	 */
	const GROUP_ES = [
			self::ES_AR		=> self::ES_AR,	//スペイン語/アルゼンチン
			self::ES_CO		=> self::ES_CO,	//スペイン語/コロンビア
			self::ES_ES		=> self::ES_ES,	//スペイン語/スペイン
			self::ES_MX		=> self::ES_MX,	//スペイン語/メキシコ
	];

	/**
	 * @var	array	フランス語グループ
	 * @static
	 */
	const GROUP_FR = [
		self::FR_BE		=> self::FR_BE,	//フランス語/ベルギー
		self::FR_CA		=> self::FR_CA,	//フランス語/カナダ
		self::FR_CH		=> self::FR_CH,	//フランス語/スイス
		self::FR_FR		=> self::FR_FR,	//フランス語/フランス
	];

	/**
	 * @var	array	オランダ語グループ
	 * @static
	 */
	const GROUP_NL = [
		self::NL_BE		=> self::NL_BE,	//オランダ語/ベルギー語
	];

	/**
	 * @var	array	ポルトガル語グループ
	 * @static
	 */
	const GROUP_PT = [
		self::PT_BR		=> self::PT_BR,	//ポルトガル語/ブラジル
	];

	/**
	 * @var	array	中国語グループ
	 * @static
	 */
	const GROUP_ZH = [
		self::ZH_CN		=> self::ZH_CN,	//中国語/中国
		self::ZH_TW		=> self::ZH_TW,	//中国語/台湾
	];

	//======================================================
	//デフォルト言語
	//======================================================
	const DEFAULT_LANG	= self::JA;

	/**
	 * 許可されている言語設定を優先度付きで返します。
	 *
	 * @param	string	$http_accept_language	許可されている言語設定 HTTP accept language header上での形式に依存する。
	 * @return	array	許可されている言語設定のリスト
	 */
	public static function GetAcceptLanguageList ($http_accept_language = null) {
		$http_accept_language !== null ?: $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		$language_set = [];
		foreach (explode(',', $http_accept_language) as $element) {
			$element = explode(';', $element);
			$language_set[$element[0]] = isset($element[1]) ? (float) substr($element[1], 2) : 1.0;;
		}
		arsort($language_set);
		return $language_set;
	}

	/**
	 * デフォルトの許可言語設定名を返します。
	 *
	 * @param unknown $http_accept_language
	 * @return mixed
	 */
	public static function GetDefaultAcceptLanguage ($http_accept_language = null) {
		$accept_language_list = static::GetAcceptLanguageList($http_accept_language);
		return key($accept_language_list);
	}

	/**
	 * 言語リストを返します。
	 *
	 * @return multitype:string
	 */
	public static function GetLanguageList () {
		return [
			static::AF		=> static::MSG_AF,		//アフリカーンス語
			static::BE		=> static::MSG_BE,		//白ロシア語
			static::BG		=> static::MSG_BG,		//ブルアリア語
			static::CA		=> static::MSG_CA,		//カタロニア語
			static::CS		=> static::MSG_CS,		//チェコ語
			static::DA		=> static::MSG_DA,		//デンマーク語
			static::DE		=> static::MSG_DE,		//ドイツ語
			static::DE_AU	=> static::MSG_DE_AU,	//ドイツ語/オーストリア
			static::DE_CH	=> static::MSG_DE_CH,	//ドイツ語/スイス
			static::DE_DE	=> static::MSG_DE_DE,	//ドイツ語/ドイツ
			static::EL		=> static::MSG_EL,		//ギリシア語
			static::EN		=> static::MSG_EN,		//英語
			static::EN_GB	=> static::MSG_EN_GB,	//英語/英国
			static::EN_US	=> static::MSG_EN_US,	//英語/米国
			static::ES		=> static::MSG_ES,		//スペイン語
			static::ES_AR	=> static::MSG_ES_AR,	//スペイン語/アルゼンチン
			static::ES_CO	=> static::MSG_ES_CO,	//スペイン語/コロンビア
			static::ES_ES	=> static::MSG_ES_ES,	//スペイン語/スペイン
			static::ES_MX	=> static::MSG_ES_MX,	//スペイン語/メキシコ
			static::EU		=> static::MSG_EU,		//バスク語
			static::FI		=> static::MSG_FI,		//フィンランド語
			static::FO		=> static::MSG_FO,		//フェロー語
			static::FR		=> static::MSG_FR,		//フランス語
			static::FR_BE	=> static::MSG_FR_BE,	//フランス語/ベルギー
			static::FR_CA	=> static::MSG_FR_CA,	//フランス語/カナダ
			static::FR_CH	=> static::MSG_FR_CH,	//フランス語/スイス
			static::FR_FR	=> static::MSG_FR_FR,	//フランス語/フランス
			static::GA		=> static::MSG_GA,		//アイルランド語
			static::GD		=> static::MSG_GD,		//スコッチ ゲール語
			static::GL		=> static::MSG_GL,		//ガリチア語
			static::HR		=> static::MSG_HR,		//クロアチア語
			static::HU		=> static::MSG_HU,		//ハンガリー語
			static::ID		=> static::MSG_ID,		//インドネシア語
			static::IS		=> static::MSG_IS,		//アイスランド語
			static::IT		=> static::MSG_IT,		//イタリア語
			static::JA		=> static::MSG_JA,		//日本語
			static::KO		=> static::MSG_KO,		//韓国語
			static::MK		=> static::MSG_MK,		//マケドニア語
			static::NL		=> static::MSG_NL,		//オランダ語
			static::NL_BE	=> static::MSG_NL_BE,	//オランダ語/ベルギー語
			static::NO		=> static::MSG_NO,		//ノルウェー語
			static::PL		=> static::MSG_PL,		//ポーランド語
			static::PT		=> static::MSG_PT,		//ポルトガル語
			static::PT_BR	=> static::MSG_PT_BR,	//ポルトガル語/ブラジル
			static::RO		=> static::MSG_RO,		//ルーマニア語
			static::RU		=> static::MSG_RU,		//ロシア語
			static::SK		=> static::MSG_SK,		//スロヴァキア語
			static::SL		=> static::MSG_SL,		//スロヴェニア語
			static::SQ		=> static::MSG_SQ,		//アルバニア語
			static::SR		=> static::MSG_SR,		//セルビア語
			static::SV		=> static::MSG_SV,		//スウェーデン語
			static::TR		=> static::MSG_TR,		//トルコ語
			static::UK		=> static::MSG_UK,		//ウクライナ語
			static::ZH		=> static::MSG_ZH,		//中国語
			static::ZH_CN	=> static::MSG_ZH_CN,	//中国語/中国
			static::ZH_TW	=> static::MSG_ZH_TW,	//中国語/台湾
		];
	}

	/**
	 * 言語グループのマップを返します。
	 *
	 * @return multitype:string
	 */
	public static function GetGroupMap () {
		return [
			static::DE	=> static::GROUP_DE,
			static::EN	=> static::GROUP_EN,
			static::ES	=> static::GROUP_ES,
			static::FR	=> static::GROUP_FR,
			static::NL	=> static::GROUP_NL,
			static::PT	=> static::GROUP_PT,
			static::ZH	=> static::GROUP_ZH,
		];
	}

	/**
	 * 言語グループの逆引きマップを返します。
	 */
	public static function GetReverseGroupMap () {
		static $reverse_group_map;
		if (!isset($reverse_group_map)) {
			foreach (static::GetGroupMap() as $group_lang => $group_map) {
				foreach ($group_map as $lang) {
					$reverse_group_map[$lang] = $group_lang;
				}
			}
		}
		return $reverse_group_map;
	}
}
