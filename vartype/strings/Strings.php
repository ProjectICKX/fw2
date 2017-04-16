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
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */

namespace ickx\fw2\vartype\strings;

use ickx\fw2\international\encoding\Encoding;
use ickx\fw2\text\pcre\Regex;

/**
 * 文字列ユーティリティクラスです。
 *
 * @category	Flywheel2
 * @package		vartype
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @version		2.0.0
 */
class Strings implements \ArrayAccess, \Iterator {
	//==============================================
	//定数
	//==============================================
	/** @var	string	空文字 */
	const STRING_EMPTY = '';

	/** @var	string	タブ文字 */
	const TAB	= "\t";

	/** @var	string	改行コード：CR */
	const CR	= "\r";

	/** @var	string	改行コード：LF */
	const LF	= "\n";

	/** @var	string	改行コード：CRLF */
	const CRLF	= "\r\n";

	//==============================================
	//プロパティ
	//==============================================
	/** @property	string	文字列の実体 */
	private $_string = self::STRING_EMPTY;

	/** @property	int		文字列の長さ */
	private $_length = 0;

	/** @property	int		現在のポインタ位置 */
	private $_offset = 0;

	//==============================================
	//インスタンス使用時の処理
	//==============================================
	/**
	 * コンストラクタ
	 *
	 * @param	string	$string	文字列
	 */
	public function __construct ($string) {
		$this->_string = $string;
		$this->_length = static::Length($this->_string);
	}

	/**
	 * 正規表現を用いて文字列マッチングを行います。
	 *
	 * @param	string	$pattern	正規表現パターン
	 * @param	mixed	$matches	マッチ結果
	 * @param	int		$flags		オフセット設定 0または\PREG_OFFSET_CAPTURE
	 * @param	int		$offset		マッチング回数
	 * @param	int		$start		文字列切り出し開始位置
	 * @param	int		$end		文字列切り出し終了位置
	 * @return	int		パターンがマッチした回数
	 */
	public function matching ($pattern, &$matches = [], $flags = 0, $offset = 0, $start = 0, $end = null) {
		if (is_null($end)) {
			$end = $this->_length;
		}
		return static::Match($pattern, static::Substring($this->_string, $start, $end), $matches, $flags, $offset);
	}

	/**
	 * 現在位置から指定された長さを文字列として切り出します。
	 *
	 * @param	int		$length	切り出す長さ
	 * @return	string	切り出された文字列
	 */
	public function currentSubstring ($length) {
		return static::Substring($this->_string, $this->_offset, $length);
	}

	/**
	 * オフセットが存在するかどうか
	 * @param offset 調べたいオフセット
	 * @return 成功した場合に TRUE を、失敗した場合に FALSE を返します。
	 */
	public function offsetExists ($offset) {
		return !($offset < 0 || $this->_length < $offset);
	}

	/**
	 * オフセットを取得する
	 * @param offset 調べたいオフセット
	 * @return 指定したオフセットの値
	 */
	public function offsetGet ($offset) {
		return static::Substring($this->_string, $offset, 1);
	}

	/**
	 * オフセットを設定する
	 * @param offset 調べたいオフセット
	 * @param value 設定したい値
	 */
	public function offsetSet ($offset, $value) {
		if (static::Length($value) !== 1) {
			throw new \Exception('1文字しか設定できません。');
		}
		$this->_string = $this->offsetBefore($offset) . $value . $this->offsetAfter($offset + 1);
		$this->_length++;
	}

	/**
	 * オフセットの設定を解除する
	 * @param offset 設定解除したいオフセット
	 */
	public function offsetUnset ($offset) {
		$this->_string = $this->offsetBefore($offset) . $this->offsetAfter($offset + 1);
		$this->_length--;
	}

	/**
	 * オフセットより前の文字列を取得する
	 *
	 * @param	int		$offset	オフセット
	 * @return	string	オフセットより前の文字列
	 */
	public function offsetBefore ($offset) {
		return ($offset > 0) ? static::Substring($this->_string, 0, $offset) : static::EMPTY_CHAR;
	}

	/**
	 * オフセットより後の文字列を取得する
	 *
	 * @param	int		$offset	オフセット
	 * @return	string	オフセットより後の文字列
	 */
	public function offsetAfter ($offset) {
		return ($offset < $this->_length) ? static::Substring($this->_string, $offset) : static::EMPTY_CHAR;
	}

	/**
	 * 指定位置にオフセットを移動する
	 *
	 * @param	int		$offset	オフセット位置
	 */
	public function offsetMove ($offset) {
		if ($offset < 0) {
			$this->reset();
		}
		if ($offset > $this->_length) {
			$this->end();
		}
		$this->_offset = $offset;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::current()
	 */
	public function current () {
		return $this->offsetGet($this->_offset);
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::key()
	 */
	public function key () {
		return $this->_offset;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::next()
	 */
	public function next () {
		$this->_offset++;
	}

	/**
	 * オフセット位置をリセットする
	 */
	public function reset () {
		$this->_offset = 0;
	}

	/**
	 * オフセット位置を最後に移動する
	 */
	public function end () {
		$this->_offset = $this->_length;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::rewind()
	 */
	public function rewind () {
		$this->_offset = 0;
	}

	/**
	 * (non-PHPdoc)
	 * @see Iterator::valid()
	 */
	public function valid () {
		return ($this->_offset < $this->_length);
	}

	/**
	 * 現在の文字列長を返す。
	 *
	 * @return	int		現在の文字列長
	 */
	public function count () {
		return static::Length($this->_string);
	}

	/**
	 * オブジェクトの文字列表現をかえす。
	 */
	public function __toString() {
		return $this->_string;
	}

	//==============================================
	//スタティック時の処理
	//==============================================
	/**
	 * 空文字列かどうか判定します。
	 *
	 * @param	string	$str	空かどうか判定する文字列
	 * @param	mixed	$trim	トリムを行うかどうか
	 * @return	bool	空文字列の場合はbool true、そうでない場合はfalse
	 */
	public static function IsEmpty($str, $trim = false) {
		return is_null($str) || static::STRING_EMPTY === ((!$trim) ? $str : trim($str, $trim));
	}

	/**
	 * HTML向けエスケープを行います。
	 *
	 * @param	string	$subject	文字列
	 * @param	string	$encoding	文字エンコーディング
	 * @return	HTMLエスケープのかかった文字列
	 */
	public static function Escape ($subject, $encoding = Encoding::UTF8) {
		return htmlspecialchars($subject, ENT_QUOTES, $encoding);
	}

	/**
	 * 文字列をスネークケースに変換します。
	 *
	 * @param	string	$subject	スネークケースに変換する文字列
	 * @param	bool	$trim		変換後に先頭の"_"をトリムするかどうか trueの場合はトリムする
	 * @return	string	スネークケースの変換された文字列。
	 */
	public static function ToSnakeCase ($subject, $trim = true) {
		$subject = Regex::ReplaceCallback(
			"/([A-Z])/",
			function ($matches) {return '_'. strtolower($matches[1]);},
			$subject
		);
		return ($trim) ? ltrim($subject, '_') : $subject;
	}

	/**
	 * 文字列をキャメルケースに変換します。
	 *
	 * @param	string	$subject	キャメルケースに変換する文字列
	 * @param	string	$prefix		単語の閾に用いる文字
	 * @return	string	キャメルケースに変換された文字列
	 */
	public static function ToCamelCase ($subject, $prefix = '_') {
		return Regex::ReplaceCallback(
			"/(?:^|". $prefix .")([a-z])/",
			function ($matches) {return strtoupper($matches[1]);},
			$subject
		);
	}

	/**
	 * 文字列をアッパーキャメルケースに変換します。
	 *
	 * @param	string	$subject	アッパーキャメルケースに変換する文字列
	 * @param	string	$prefix		単語の閾に用いる文字
	 * @return	string	アッパーキャメルケースに変換された文字列
	 */
	public static function ToUpperCamelCase ($subject, $prefix = '_') {
		return ucfirst(static::ToCamelCase($subject, $prefix));
	}

	/**
	 * 文字列をロウアーキャメルケースに変換します。
	 *
	 * @param	string	$subject	ロウアーキャメルケースに変換する文字列
	 * @param	string	$prefix		単語の閾に用いる文字
	 * @return	string	ロウアーキャメルケースに変換された文字列
	 */
	public static function ToLowerCamelCase ($subject, $prefix = '_') {
		return lcfirst(static::ToCamelCase($subject, $prefix));
	}

	/**
	 * 文字列の長さを取得します。
	 *
	 * @param	string	$string
	 * @param	string	$encoding
	 * @return	string
	 */
	public static function Length ($string, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_strlen($string, $encoding);
	}

	/**
	 * 文字列を切り出します。
	 *
	 * @param	string	$string
	 * @param	int		$start
	 * @param	mixed	$length
	 * @param	string	$encoding
	 */
	public static function Substring ($string, $start, $length = null, $encoding = Encoding::DEFAULT_ENCODING) {
		if ($length === null) {
			$length = self::Length($string) - $start;
		}
		return mb_substr($string, $start, $length, $encoding);
	}

	/**
	 * 文字列に正規表現マッチをかけます。
	 *
	 * @param	string	$pattern
	 * @param	string	$subject
	 * @param	array	&$matches
	 * @param	int		$flags
	 * @param	int		$offset
	 * @param	string	$encoding
	 * @return	int
	 */
	public static function Match ($pattern, $subject, array &$matches = array(), $flags = 0, $offset = 0, $from_encoding = Encoding::DEFAULT_ENCODING) {
		return Regex::match($pattern, $subject, $matches, $flags, $offset, $from_encoding);
	}

	/**
	 * 文字列の中に指定した文字列が最初に現れる位置を見つけます。
	 *
	 * @param	string	$haystack
	 * @param	string	$needle
	 * @param	int		$offset
	 * @param	string	$encoding
	 * @return	int		文字列が最初に現れた位置
	 */
	public static function Position ($haystack, $needle, $offset = 0, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_strpos($haystack, $needle, $offset, $encoding);
	}

	/**
	 * 文字列の中に指定した文字列が最後に現れる位置を見つけます。
	 *
	 * @param	string	$haystack
	 * @param	string	$needle
	 * @param	int		$offset
	 * @param	string	$encoding
	 * @return	int		文字列が最初に現れた位置
	 */
	public static function RearPosition ($haystack, $needle, $offset = 0, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_strrpos($haystack, $needle, $offset, $encoding);
	}

	/**
	 * 文字列に文字が入っているかどうか調査します。
	 *
	 * @param	string	$haystack	調査対象の文字列
	 * @param	string	$needle		探す文字
	 * @param	bool	$part		返す値
	 * @param	string	$encoding	文字エンコーディング
	 * @return	bool	文字が見つかった場合はbool true、そうでない場合はfalse
	 */
	public static function InString ($haystack, $needle, $part = FALSE, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_strstr($haystack, $needle, $part, $encoding) !== false;
	}

	/**
	 * 大文字小文字を区別せず、 文字列の中で指定した文字列が最初に現れる位置を探す 。
	 *
	 * @param	string	$haystack	調査対象の文字列
	 * @param	string	$needle		探す文字
	 * @param	int		$offset		調査を開始する位置
	 * @param	string	$encoding	文字エンコーディング
	 */
	public static function CaseInsensitivePosition($haystack, $needle, $offset = 0, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_stripos($haystack, $needle, $offset, $encoding);
	}

	/**
	 * 大文字小文字を区別せず、 文字列の中で指定した文字列が最初に現れる位置を探す 。
	 *
	 * @param	string	$haystack	調査対象の文字列
	 * @param	string	$needle		探す文字
	 * @param	bool	$part		見つかった位置より前を返す場合はtrue、そうでない場合はfalse
	 * @param	string	$encoding	文字エンコーディング
	 */
	public static function CaseInsensitiveInString ($haystack, $needle, $before_needle = FALSE, $encoding = Encoding::DEFAULT_ENCODING) {
		return mb_stristr($haystack, $needle, $before_needle, $encoding);
	}

	/**
	 * 文字列を比較します。
	 *
	 * @param	string	$string1	比較する文字列1
	 * @param	string	$string2	比較する文字列2
	 * @param	string	$encoding	文字エンコーディング
	 * @return	int		$string1が$string2より小さい場合は負、$string1が$string2より大きい場合は正、等しい場合は 0 を返します。
	 */
	public static function Compare ($string1, $string2, $encoding = Encoding::DEFAULT_ENCODING) {
		return strcmp(Encoding::Adjust($string1, $encoding), Encoding::Adjust($string2, $encoding));
	}

	/**
	 * 大文字小文字を区別しないバイナリセーフな文字列比較を行います。
	 *
	 * @param	string	$string1	比較する文字列1
	 * @param	string	$string2	比較する文字列2
	 * @param	string	$encoding	文字エンコーディング
	 * @return	int		$string1が$string2より小さい場合は負、$string1が$string2より大きい場合は正、等しい場合は 0 を返します。
	 */
	public static function CaseCompare ($string1, $string2, $encoding = Encoding::DEFAULT_ENCODING) {
		return strcasecmp(Encoding::Adjust($string1, $encoding), Encoding::Adjust($string2, $encoding));
	}

	/**
	 * 文字列がイコールかどうか調べます。
	 *
	 * @param	string	$string1	比較する文字列1
	 * @param	string	$string2	比較する文字列2
	 * @param	string	$encoding	文字エンコーディング
	 * @return	bool	文字列同士がイコールの場合は bool true、そうでない場合はfalse
	 */
	public static function Equal ($string1, $string2, $encoding = Encoding::DEFAULT_ENCODING) {
		return (self::Compare($string1, $string2, $encoding) === 0);
	}

	/**
	 * 大文字小文字を区別せず文字列がイコールかどうか調べます。
	 *
	 * @param	string	$string1	比較する文字列1
	 * @param	string	$string2	比較する文字列2
	 * @param	string	$encoding	文字エンコーディング
	 * @return	bool	文字列同士がイコールの場合は bool true、そうでない場合はfalse
	 */
	public static function CaseEqual ($string1, $string2, $encoding = Encoding::DEFAULT_ENCODING) {
		return (self::CaseCompare($string1, $string2, $encoding) === 0);
	}

	/**
	 * 文字列中の改行コードを削除します。
	 *
	 * @param	string	$subject	改行コードを削除する文字列
	 * @return	string	改行コードを削除された文字列
	 */
	public static function DeleteLfCode ($subject) {
		return str_replace(["\r\n", "\r", "\n"],  '', $subject);
	}

	/**
	 * 指定された文字列に含まれるキーパターンを配列にある要素に置き換えます。
	 *
	 * デフォルトでは次のキーパターンにマッチします。
	 * $targetに'vars'が設定されている場合
	 *
	 * {:vars:titile}
	 *
	 * @param	mixed	$target			$replacementの1次元目のキー名
	 * @param	array	$replacement	置換用データ配列 1次元目のキーが::の間の値、2次元目のキーが:}の間の値であること
	 * @param	string	$subject		置換する文字列
	 * @param	string	$default		置換対象がなかった場合のデフォルト文字列
	 * @param	string	$pattern		マッチパターン
	 */
	public static function ReplaceKeyPattern ($target, $replacement, $subject, $default = '', $pattern = '/{:(%s):([^}]+)}/') {
		if (is_array($target)) {
			$target = sprintf('%s', implode('|', array_map('preg_quote', $target)));
		}
		$pattern = sprintf($pattern, $target);
		return preg_replace_callback($pattern, function ($matches) use ($replacement, $default) {
			return isset($replacement[$matches[1]][$matches[2]]) ? $replacement[$matches[1]][$matches[2]] : $default;
		}, $subject);
	}

	/**
	 * 文字列同士を比較し、先頭から同一の部分を返します。
	 *
	 * @param	string	$string1		比較する文字列1
	 * @param	string	$string2		比較する文字列2
	 * @param	string	$string1_encode	比較する文字列1の文字エンコーディング デフォルトはUTF-8
	 * @param	string	$string2_encode	比較する文字列2の文字エンコーディング デフォルトはUTF-8
	 * @return	string	先頭から同一の部分 もしも同一の部分が無い場合は空文字を返す
	 */
	public static function Intersect ($string1, $string2, $string1_encode = Encoding::DEFAULT_ENCODING, $string2_encode = Encoding::DEFAULT_ENCODING) {
		$string1_length = mb_strlen($string1, $string1_encode);
		$string2_length = mb_strlen($string2, $string2_encode);

		$long_string = $string1_length > $string2_length ? 'string1' : 'string2';
		$loop_length = ${$long_string . '_length'};

		for ($i = 0;$i < $loop_length;$i++) {
			if (mb_substr($string1, $i, 1, $string1_encode) !== mb_substr($string2, $i, 1, $string2_encode)) {
				break;
			}
		}

		return mb_substr($$long_string, 0, $i, ${$long_string . '_encode'});
	}

	/**
	 * 文字列同士を比較し、差分が出た箇所以降の部分を返します。
	 *
	 * @param	string	$string1		比較する文字列1
	 * @param	string	$string2		比較する文字列2
	 * @param	string	$string1_encode	比較する文字列1の文字エンコーディング デフォルトはUTF-8
	 * @param	string	$string2_encode	比較する文字列2の文字エンコーディング デフォルトはUTF-8
	 * @return	string	差分が出た箇所以降の部分 もしも差分が無い場合は空文字を返す
	 */
	public static function Difference ($string1, $string2, $string1_encode = Encoding::DEFAULT_ENCODING, $string2_encode = Encoding::DEFAULT_ENCODING) {
		$string1_length = mb_strlen($string1, $string1_encode);
		$string2_length = mb_strlen($string2, $string2_encode);

		$long_string = $string1_length > $string2_length ? 'string1' : 'string2';
		$loop_length = ${$long_string . '_length'};

		for ($i = 0;$i < $loop_length;$i++) {
			if (mb_substr($string1, $i, 1, $string1_encode) !== mb_substr($string2, $i, 1, $string2_encode)) {
				break;
			}
		}

		return mb_substr($$long_string, $i, $loop_length, ${$long_string . '_encode'});
	}

	/**
	 * 文字列の前を埋めます。
	 *
	 * @param	string	$string		前を埋めたい文字列
	 * @param	int		$width		埋め幅 デフォルトは半角80文字
	 * @param	string	$pad_string	埋め文字列
	 * @return	string	前を埋めた文字列
	 */
	public static function FrontFill ($string, $width = 80, $pad_string = ' ') {
		return sprintf('%s%s', str_repeat($pad_string, $width - static::Width($val)), $val);
	}

	/**
	 * 文字列の後を埋めます。
	 *
	 * @param	string	$string		後を埋めたい文字列
	 * @param	int		$width		埋め幅 デフォルトは半角80文字
	 * @param	string	$pad_string	埋め文字列
	 * @return	string	後を埋めた文字列
	 */
	public static function BackFill ($string, $width = 80, $pad_string = ' ') {
		return sprintf('%s%s', $val, str_repeat($pad_string, $width - static::Width($val)));
	}

	/**
	 * 文字列の表示幅を返します。
	 *
	 * 文字列はUTF-8である必要があります。
	 *
	 * @param	string	$string	表示幅を計りたい文字列
	 * @return	mixed	文字列の表示幅 UTF-8以外の文字列が渡された場合はfalse
	 */
	public static function Width ($string, $east = true) {
		$detect_encoding = Encoding::Detect($string);
		if ($detect_encoding !== Encoding::UTF_8 && $detect_encoding !== Encoding::ASCII) {
			return false;
		}

		$length = mb_strlen($string);

		$list = [\IntlChar::EA_FULLWIDTH, \IntlChar::EA_WIDE];

		if ($east) {
			$list[] = \IntlChar::EA_AMBIGUOUS;
		}

		$list = array_combine($list, $list);

		$width = 0;

		for ($i = 0;$i < $length;$i++) {
			$width += isset($list[\IntlChar::getIntPropertyValue(mb_substr($string, $i, 1), \IntlChar::PROPERTY_EAST_ASIAN_WIDTH)]) ? 2 : 1;
		}

		return $width;
	}

	/**
	 * 指定された文字列が指定された行数、文字幅に収まっているかを判定します。
	 *
	 * @param	string	$string		判定する文字列
	 * @param	int		$cols		半角文字で計算する1行当たりの文字幅 デフォルトは80文字分
	 * @param	int		$rows		最大行数 デフォルトは1行
	 * @param	bool	$word_wrap	列をはみ出た文字を改行して列内に収めるかどうか デフォルトはfalse
	 * @return	bool	文字列が行、幅に収まっている場合はtrue、そうでない場合はfalse
	 */
	public static function InFrame ($string, $cols = 80, $rows = 1, $word_wrap = false) {
		$string = str_replace(["\r\n", "\r"], "\n", $string);
		$row_count = substr_count($string, "\n") + 1;

		if ($row_count > $rows) {
			return false;
		}

		$row_width = 0;
		$length = mb_strlen($string);
		for ($i = 0;$i < $length;$i++) {
			$char = mb_substr($string, $i, 1);
			$width = static::Width($char);

			if ($char === "\n") {
				$row_width = 0;
				continue;
			}

			if ($row_width + $width > $cols) {
				if (!$word_wrap) {
					return false;
				}
				$row_count++;
				if ($row_count > $rows) {
					return false;
				}
				$row_width = $width;
				continue;
			}

			$row_width += $width;
		}

		if (!$word_wrap && $row_width > $cols) {
			return false;
		}

		return $row_count <= $rows;
	}

	/**
	 * 文字列中に含まれるサロゲートペア文字を抽出し、返します。
	 *
	 * @param	string	$string	検索する文字列
	 * @return	array	見つかったサロゲートペア文字 1件も見つからなかった場合、空の配列が返されます。
	 * @link	http://qiita.com/mpyw/items/8dd5378cb01c877e1f7b
	 */
	public static function ExtractionSurrogatePairs ($string) {
		if (!preg_match_all('/[\xd8-\xdb][\x00-\xff][\xdc-\xdf][\x00-\xff]/', mb_convert_encoding($string, 'UTF-16', 'UTF-8'), $matches)) {
			return [];
		}
		mb_convert_variables('UTF-8', 'UTF-16', $matches[0]);
		return $matches[0];
	}
}
