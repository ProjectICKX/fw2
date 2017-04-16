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
 * @package		text
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */

namespace ickx\fw2\text\pcre;

use \ickx\fw2\international\encoding\Encoding;

/**
 * 正規表現によるテキスト操作を行うクラスです。
 *
 * @category	Flywheel2
 * @package		text
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 * @see			http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
 */
class Regex {
	/**
	 * @var	string	MODIFIER_CASELESS		この修飾子を設定すると、パターンの中の文字は大文字にも小文字にもマッチします。
	 * 											(PCRE_CASELESS)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_CASELESS		= 'i';

	/**
	 * @var	string	MODIFIER_MULTILINE		デフォルトで、PCREは、検索対象文字列を（実際には複数行からなる場合でも）単一の行からなるとして処理します。
	 * 											「行頭」メタ文字(^)は、対象文字列の最初にしかマッチしません。
	 * 											一方、「行末」メタ文字($)は、文字列の最後、または（D修飾子が設定されていない場合）最後にある改行記号の前のみにしかマッチしません。
	 * 											この動作はPerlと同じです。
	 * 											この修飾子を設定すると、「行頭」および「行末」メタ文字は対象文字列において、文字列の最初と最後に加えて、各改行の直前と直後にそれぞれマッチします。
	 * 											この動作は、Perlの/m修飾子と同じです。
	 * 											対象文字列の中に"\n"文字がない場合や、またはパターンに^または$がない場合は、この修飾子を設定しても意味はありません。
	 * 											(PCRE_MULTILINE)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_MULTILINE		= 'm';

	/**
	 * @var	string	MODIFIER_DOTALL			この修飾子を設定すると、パターン中のドットメタ文字は改行を含む全ての文字にマッチします。
	 *						 					これを設定しない場合は、改行にはマッチしません。この修飾子は、Perlの/s修飾子と同じです。
	 * 											[^a]のような否定の文字クラスは、この修飾子の設定によらず、常に改行文字にマッチします。
	 * 											(PCRE_DOTALL)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_DOTALL			= 's';

	/**
	 * @var	string	MODIFIER_EXTENDED		この修飾子を設定すると、エスケープするか文字クラスの内部を除き、パターンの空白文字は完全に無視されます。
	 * 											文字クラスの外にあって、かつエスケープされていない#と次の改行文字の間の文字も無視されます。
	 * 											この動作は、Perlの/x修飾子と同じであり、複雑なパターンの内部にコメントを記述することが可能となります。
	 * 											しかし、この修飾子は、データ文字にのみ適用されることに注意してください。
	 * 											空白文字をパターンの特殊文字の並びの中、例えば条件付きサブパターン(?(の内部に置くことはできません。
	 * 											(PCRE_EXTENDED)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_EXTENDED			= 'x';

	/**
	 * @var	string	MODIFIER_REPLACE_EVAL	この修飾子を設定すると、preg_replace()は、置換文字列において後方参照に関する通常の置換を行った後、PHPコードとして評価し、検索文字列を置換するためにその結果を使用します。
	 * 											置換された後方参照においては、単引用符や二重引用符、バックスラッシュ(\)およびNULL文字はバックスラッシュでエスケープされます。
	 * 											replacementがPHPのコードとして妥当な文字列であることを確認しましょう。
	 * 											そうでない場合は、preg_replace()を含む行でPHPのパースエラーが発生します。
	 * 											注意:
	 * 											この修飾子を使用するのは、preg_replace()のみです。他のPCRE関数では無視されます。
	 * 											(PREG_REPLACE_EVAL)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_REPLACE_EVAL		= 'e';

	/**
	 * @var	string	MODIFIER_ANCHORED		この修飾子を設定すると、パターンは強制的に固定(anchored)となります。
	 * 											つまり、検索対象文字列の先頭でのみマッチするように制限されます。
	 * 											パターン自体の中に適当な指定を行うことでも同様の効果を得ることが可能です。
	 * 											Perlではパターン中に指定する方法しか使用できません。
	 * 											(PCRE_ANCHORED)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_ANCHORED			= 'A';

	/**
	 * @var	string	MODIFIER_DOLLAR_ENDONLY	この修飾子を設定すると、パターン内のドルメタ文字は、検索対象文字列の終わりにのみマッチします。
	 * 											この修飾子を設定しない場合、ドル記号は、検索対象文字列の最後の文字が改行文字であれば、その直前にもマッチします。
	 * 											この修飾子は、mを設定している場合に無視されます。
	 * 											Perlには、この修飾子に等価なものはありません。
	 * 											(PCRE_DOLLAR_ENDONLY)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_DOLLAR_ENDONLY	= 'D';

	/**
	 * @var	string	MODIFIER_EXTRA_ANALYSIS	あるパターンを複数回使用する場合は、マッチングにかかる時間を高速化することを目的として、パターンの分析に幾分か時間をかけても良いでしょう。
	 * 											この修飾子を設定すると、追加のパターン分析が行われます。
	 * 											現在、パターン分析は、最初の文字が単一ではなく、かつ固定でないパターンに対してのみ有用です。
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_EXTRA_ANALYSIS	= 'S';

	/**
	 * @var	string	MODIFIER_UNGREEDY		この修飾子を設定すると、量指定子の「貪欲さ」が反転します。
	 * 											つまり、量指定子は、デフォルトで貪欲でなく、疑問符を後ろに付けてはじめて貪欲になるようになります。
	 * 											この修飾子はPerl互換では有りません。
	 * 											同様の設定は、(?U)修飾子をパターン内で設定するか、（.*?のように）量指定子の後に疑問符を付けるかすることで行うこともできます。
	 * 											注意:
	 * 											通常は、非貪欲モードではpcre.backtrack_limit文字を超えるマッチができません。
	 * 											(PCRE_UNGREEDY)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_UNGREEDY			= 'U';

	/**
	 * @var	string	MODIFIER_EXTRA			この修正子は、Perl非互換なPCREの機能を有効にします。
	 * 											パターン内で後ろに文字が続くバックスラッシュで特別な意味がないものは、将来的な拡張の際の互換性の維持のため、エラーになります。
	 * 											デフォルトでは、Perlのように文字が後ろに続くバックスラッシュで特に意味がないものは、リテラルとして処理されます。
	 * 											この修飾子により制御される機能は、現在の所、これだけです。
	 * 											(PCRE_EXTRA)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_EXTRA			= 'X';

	/**
	 * @var	string	MODIFIER_EXTRA			(?J)内部オプションは、ローカルのオプションPCRE_DUPNAMESの設定を変更します。
	 * 											サブパターンで重複した名前を使用できるようになります。
	 * 											(PCRE_INFO_JCHANGED)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_INFO_JCHANGED	= 'J';

	/**
	 * @var	string	MODIFIER_PCRE8		この修正子は、Perl非互換なPCREの機能を有効にします。
	 * 										パターン文字列は、UTF-8エンコードされた文字列として処理されます。
	 * 										この修正子は、UNIXではPHP4.1.0以降、Win32ではPHP4.2.3以降で使用可能です。
	 * 										また、PHP4.3.5以降では、パターンのUTF-8としての妥当性も確認されます。
	 * 										(PCRE8)
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	const MODIFIER_PCRE8		= 'u';

	/**
	 * マルチバイトセーフな正規表現マッチを行います。
	 *
	 * @param	string	$pattern		正規表現パターン
	 * @param	string	$subject		マッチ対象の文字列
	 * @param	string	$matches		マッチ結果
	 * @param	int		$flags			マッチ方法フラグ 0または\PREG_OFFSET_CAPTUREを設定
	 * @param	string	$offset			マッチ開始オフセット
	 * @param	string	$from_encoding	マッチ対象文字列のエンコーディング
	 * @return	mixed	マッチ回数 エラー時はbool false
	 */
	public static function Match ($pattern, $subject, &$matches = [], $flags = 0, $offset = 0, $from_encoding = Encoding::DEFAULT_ENCODING) {
		//マルチバイトセーフにするため、強制的にPCRE8オプションを付与する
		$pattern = static::ForcePcre8Modifiers($pattern);

		//PCRE8オプション付与時は正規表現処理時のエンコーディングがUTF-8となるため、$subjectもエンコーディングを変更する
		$subject = Encoding::Convert($subject, Encoding::UTF_8, $from_encoding);

		//preg_matchの実行
		$ret = preg_match ($pattern, $subject, $matches, $flags, $offset);

		//$matchesのエンコーディングを元に戻す
		$matches = Encoding::ConvertArrayRecursive($matches, $from_encoding, Encoding::UTF_8);

		//処理の終了
		return $ret;
	}

	/**
	 * マルチバイトセーフな繰り返し正規表現マッチを行います。
	 *
	 * @param	string	$pattern		正規表現パターン
	 * @param	string	$subject		マッチ対象の文字列
	 * @param	string	$matches		マッチ結果
	 * @param	int		$flags			マッチ方法フラグ \PREG_PATTERN_ORDER, \PREG_SET_ORDER, \PREG_OFFSET_CAPTUREを設定できる。
	 * @param	int		$offset			マッチ開始オフセット
	 * @param	string	$from_encoding	マッチ対象文字列のエンコーディング
	 * @return	mixed	マッチ回数 エラー時はbool false
	 */
	public static function MatchAll ($pattern, $subject, &$matches = [], $flags = \PREG_PATTERN_ORDER, $offset = 0, $from_encoding = Encoding::DEFAULT_ENCODING) {
		//マルチバイトセーフにするため、強制的にPCRE8オプションを付与する
		$pattern = static::ForcePcre8Modifiers($pattern);

		//PCRE8オプション付与時は正規表現処理時のエンコーディングがUTF-8となるため、$subjectもエンコーディングを変更する
		$subject = Encoding::Convert($subject, Encoding::UTF_8, $from_encoding);

		//preg_matchの実行
		$ret = preg_match_all ($pattern, $subject, $matches, $flags, $offset);

		//$matchesのエンコーディングを元に戻す
		$matches = Encoding::ConvertArrayRecursive($matches, $from_encoding, Encoding::UTF_8);

		//処理の終了
		return $ret;
	}

	/**
	 * 正規表現検索を行い、コールバック関数を使用して置換を行います。
	 *
	 * @param	string		$pattern		正規表現パターン
	 * @param	callable	$callback		リプレイスコールバックファンクション
	 * @param	string		$subject		マッチ対象の文字列
	 * @param	int			$limit			置換回数
	 * @param	int			$count			置換を行った回数
	 * @param	string		$from_encoding	マッチ対象文字列のエンコーディング
	 * @return	mixed		マッチ回数 エラー時はbool false
	 */
	public static function ReplaceCallback ($pattern , callable $callback, $subject, $limit = -1, &$count = null, $from_encoding = Encoding::DEFAULT_ENCODING) {
		//マルチバイトセーフにするため、強制的にPCRE8オプションを付与する
		$pattern = static::ForcePcre8Modifiers($pattern);

		//PCRE8オプション付与時は正規表現処理時のエンコーディングがUTF-8となるため、$subjectもエンコーディングを変更する
		$subject = Encoding::Convert($subject, Encoding::UTF_8, $from_encoding);

		//preg_replace_callbackの実行
		$ret = preg_replace_callback($pattern, $callback, $subject, $limit, $count);

		//$matchesのエンコーディングを元に戻す
		$ret = Encoding::ConvertArrayRecursive([$ret], $from_encoding, Encoding::UTF_8)[0];

		//処理の終了
		return $ret;
	}

	/**
	 * 指定した文字列を、正規表現で分割します。
	 *
	 * @param	string	$pattern		正規表現パターン
	 * @param	string	$subject		マッチ対象の文字列
	 * @param	int		$limit			最大分割数
	 * @param	int		$flags			PREG_SPLIT_NO_EMPTY, PREG_SPLIT_DELIM_CAPTURE, PREG_SPLIT_OFFSET_CAPTURE
	 * @param	string	$from_encoding	マッチ対象文字列のエンコーディング
	 * @return	mixed	マッチ回数 エラー時はbool false
	 */
	public static function Split ($pattern, $subject, $limit = -1, $flags = 0, $from_encoding = Encoding::DEFAULT_ENCODING) {
		//マルチバイトセーフにするため、強制的にPCRE8オプションを付与する
		$pattern = static::ForcePcre8Modifiers($pattern);

		//PCRE8オプション付与時は正規表現処理時のエンコーディングがUTF-8となるため、$subjectもエンコーディングを変更する
		$subject = Encoding::Convert($subject, Encoding::UTF_8, $from_encoding);

		//preg_splitの実行
		$split_list = preg_split ($pattern, $subject, $limit, $flags);

		//$matchesのエンコーディングを元に戻す
		$split_list = Encoding::ConvertArrayRecursive($split_list, $from_encoding, Encoding::UTF_8);

		//処理の終了
		return $split_list;
	}

	/**
	 * 与えられたpreg*関数向け正規表現パターンに"u"修正子を強制的に付与します。
	 *
	 * <pre>
	 * u (PCRE8)
	 * この修正子は、Perl非互換なPCREの機能を有効にします。
	 * パターン文字列は、UTF-8エンコードされた文字列として処理されます。
	 * この修正子は、UNIXではPHP4.1.0以降、Win32ではPHP4.2.3以降で使用可能です。
	 * また、PHP4.3.5以降では、パターンのUTF-8としての妥当性も確認されます。
	 * </pre>
	 *
	 * @param	string	$pattern	正規表現パターン
	 * @return	string	"u"修正子を付与した正規表現パターン
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	public static function ForcePcre8Modifiers ($pattern) {
		//u修正子を利用するためにエンコーディング変換
		$pattern = Encoding::Convert($pattern, Encoding::UTF_8);

		//PCRE8調査用パターンの構築
		$pcre_modifiers_pattern = sprintf("/\/([%s]+)$/u", implode('', static::getPcreModifierList()));

		//修正子の検出と取得
		if (preg_match($pcre_modifiers_pattern, $pattern, $matches) === 0) {
			//マッチ0件の場合は末尾に付与して返す
			//パターンの末尾に"/"がついているかどうかの判定は呼び出し側で済みのものとする
			return Encoding::ConvertToDefaultEncoding($pattern . static::MODIFIER_PCRE8, Encoding::UTF_8);
		}

		//既存の修正子の中にPCRE8が指定されているか検出
		if (strpos($matches[1], static::MODIFIER_PCRE8) === FALSE) {
			//含まれていないので末尾に付与して返す
			return Encoding::ConvertToDefaultEncoding($pattern . static::MODIFIER_PCRE8, Encoding::UTF_8);
		}

		//PCRE8修正子は付与済みのため何もしない
		return Encoding::ConvertToDefaultEncoding($pattern, Encoding::UTF_8);
	}

	/**
	 * preg*関数で利用できる修正子のリストを返します。
	 *
	 * @return	array	preg*関数で利用できる修正子のリスト
	 * @see		http://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
	 */
	public static function getPcreModifierList () {
		return array(
			static::MODIFIER_CASELESS			=> static::MODIFIER_CASELESS,
			static::MODIFIER_MULTILINE			=> static::MODIFIER_MULTILINE,
			static::MODIFIER_DOTALL				=> static::MODIFIER_DOTALL,
			static::MODIFIER_EXTENDED			=> static::MODIFIER_EXTENDED,
			static::MODIFIER_REPLACE_EVAL		=> static::MODIFIER_REPLACE_EVAL,
			static::MODIFIER_ANCHORED			=> static::MODIFIER_ANCHORED,
			static::MODIFIER_DOLLAR_ENDONLY		=> static::MODIFIER_DOLLAR_ENDONLY,
			static::MODIFIER_EXTRA_ANALYSIS		=> static::MODIFIER_EXTRA_ANALYSIS,
			static::MODIFIER_UNGREEDY			=> static::MODIFIER_UNGREEDY,
			static::MODIFIER_EXTRA				=> static::MODIFIER_EXTRA,
			static::MODIFIER_INFO_JCHANGED		=> static::MODIFIER_INFO_JCHANGED,
			static::MODIFIER_PCRE8				=> static::MODIFIER_PCRE8,
		);
	}
}
