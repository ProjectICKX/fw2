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
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\sdf\drivers\interfaces;

/**
 * Flywheel2 Structured data file Interface
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface ISdfDriver {
	//==============================================
	//Class Const
	//==============================================
	//----------------------------------------------
	//デフォルト設定
	//----------------------------------------------
	const DEFAULT_ENCODING_INTERNAL	= \ickx\fw2\international\encoding\Encoding::UTF_8;

	const DEFAULT_ENCODING_FILE		= \ickx\fw2\international\encoding\Encoding::UTF_8;

	const DEFAULT_QUOTE_MODE		= null;

	const DEFAULT_READ_LENGTH		= self::READ_LENGTH;

	const DEFAULT_WRITE_LENGTH		= self::WRITE_LENGTH;

	const DEFAULT_EOL				= self::EOL_LF;

	const DEFAULT_DELIMITER			= null;

	const DEFAULT_ENCLOSURE			= null;

	const DEFAULT_ESCAPE			= null;

	const DEFAULT_FOPEN_MODE_READ	= self::FOPEN_MODE_READ;

	const DEFAULT_FOPEN_MODE_WRITE	= self::FOPEN_MODE_WRITE;










	//----------------------------------------------
	//CSV文字列クォート方法
	//----------------------------------------------
	/** @var	string	QUOTE_CSV_AUTOMATIC		CSV文字列クォート方法：自動：-01や090など、MS EXCELで読込んだ際に数字に変更されてしまうパターンをクォートする */
	const QUOTE_CSV_AUTOMATIC	= 'QUOTE_AUTOMATIC';

	/** @var	string	QUOTE_CSV_FORCE_QUOTE	CSV文字列クォート方法：強制：強制的に全てをクォートする */
	const QUOTE_CSV_FORCE_QUOTE	= 'QUOTE_FORCE_QUOTE';

	/** @var	string	QUOTE_CSV_MS_EXCEL		CSV文字列クォート方法：MS EXCEL CSV出力準拠：MS EXCELでCSV出力した場合と同様の出力にする */
	const QUOTE_CSV_MS_EXCEL	= 'QUOTE_MS_EXCEL_CSV';

	//----------------------------------------------
	//最大読込バイト長：読込時のみ使用
	//----------------------------------------------
	/** @avar	int		READ_LENGTH	fgetcsvで読み込む最大バイト長：0（無制限） */
	const READ_LENGTH	= 0;

	//----------------------------------------------
	//最大書込バイト長：書込時のみ使用
	//----------------------------------------------
	/** @avar	int		WRITE_LENGTH	fwriteで書き込む最大バイト長：0（無制限） */
	const WRITE_LENGTH	= 0;

	//----------------------------------------------
	//改行コード
	//----------------------------------------------
	/** @var	string	EOL_CR		改行コード：CR (\r) */
	const EOL_CR	= "\r";

	/** @var	string	EOL_LF		改行コード：LF (\n) */
	const EOL_LF	= "\n";

	/** @var	string	EOL_CRLF	改行コード：CRLF (\r\n) */
	const EOL_CRLF	= "\r\n";

	/** @var	string	EOL_OS		改行コード：現在実行中OSの改行コード */
	const EOL_OS	= \PHP_EOL;

	//----------------------------------------------
	//デリミタ：区切り文字
	//----------------------------------------------
	/** @var	string	DELIMITER_COMMA		デリミタ：カンマ (,) */
	const DELIMITER_COMMA		= ',';

	/** @var	string	DELIMITER_TAB		デリミタ：水平タブ (\t) */
	const DELIMITER_TAB			= "\t";

	/** @var	string	DELIMITER_SEMICOLON	デリミタ：セミコロン (;) */
	const DELIMITER_SEMICOLON	= ';';

	/** @var	string	DELIMITER_COLON		デリミタ：コロン (:) */
	const DELIMITER_COLON		= ':';

	/** @var	string	DELIMITER_PERIOD	デリミタ：ピリオド (.) */
	const DELIMITER_PERIOD		= '.';

	/** @var	string	DELIMITER_DOT		デリミタ：ドット (.) */
	const DELIMITER_DOT		= '.';

	//----------------------------------------------
	//エンクロージャ：囲み文字
	//----------------------------------------------
	/** @var	string	ENCLOSURE_DOUBLE_QUOTE	エンクロージャ：ダブルクォート (") */
	const ENCLOSURE_DOUBLE_QUOTE		= '"';

	/** @var	string	ENCLOSURE_SINGLE_QUOTE	エンクロージャ：シングルクォート (') */
	const ENCLOSURE_SINGLE_QUOTE		= "'";

	//----------------------------------------------
	//エスケープ：エンクロージャエスケープ方法
	//----------------------------------------------
	/** @var	null	ESCAPE_CHAR_SAME_ENCLOSURE	エンクロージャエスケープ方法：エンクロージャと同じ文字を使う */
	const ESCAPE_CHAR_SAME_ENCLOSURE	= null;

	/** @var	string	ESCAPE_CHAR_SLASH			エンクロージャエスケープ方法：スラッシュ (/) */
	const ESCAPE_CHAR_SLASH				= '/';

	/** @var	string	ESCAPE_CHAR_SLASH			エンクロージャエスケープ方法：バックスラッシュ (\) */
	const ESCAPE_CHAR_BACKSLASH			= "\\";

	/** @var	string	ESCAPE_CHAR_DOUBLE_QUOTE	エンクロージャエスケープ方法：ダブルクォート (") */
	const ESCAPE_CHAR_DOUBLE_QUOTE		= '"';

	/** @var	string	ESCAPE_CHAR_SINGLE_QUOTE	エンクロージャエスケープ方法：シングルクォート (') */
	const ESCAPE_CHAR_SINGLE_QUOTE		= "'";

	//----------------------------------------------
	//fopen設定
	//----------------------------------------------
	/** @var	string	FOPEN_MODE_READ		読込用のfopen mode (rb)：読み込みのみのバイナリモードでオープンします。ファイルポインタをファイルの先頭に置きます。 */
	const FOPEN_MODE_READ	= 'rb';

	/** @var	string	FOPEN_MODE_WRITE	書込用のfopen mode (wb)：書き出しのみのバイナリモードでオープンします。ファイルポインタをファイルの先頭に置き、 ファイルサイズをゼロにします。ファイルが存在しない場合には、 作成を試みます。 */
	const FOPEN_MODE_WRITE	= 'wb';

	//----------------------------------------------
	//属性名
	//----------------------------------------------
	//エンコーディング
	/** @var	string	ATTR_ENCODING_INTERNAL	属性名 internal_encoding：PHP側で期待する文字エンコーディング */
	const ATTR_ENCODING_INTERNAL		= 'internal_encoding';

	/** @var	string	ATTR_ENCODING_FILE		属性名 file_encoding：ファイルに期待する文字エンコーディング */
	const ATTR_ENCODING_FILE			= 'file_encoding';

	//ファイル I/O
	/** @var	string	ATTR_FOPEN_READ_MODE	属性名 enclosure：fopen readモード */
	const ATTR_FOPEN_MODE_READ			= 'fopen_read_mode';

	/** @var	string	ATTR_FOPEN_WRITE_MODE	属性名 enclosure：fopen writeモード */
	const ATTR_FOPEN_MODE_WRITE			= 'fopen_write_mode';

	/** @var	string	ATTR_READ_LENGTH		属性名 read_length：ファイルポインタでファイルを読み込む際の最大バイト長 */
	const ATTR_READ_LENGTH				= 'read_length';

	//フォーマット
	/** @var	string	ATTR_DELIMITER			属性名 delimiter：区切り文字 */
	const ATTR_DELIMITER				= 'delimiter';

	/** @var	string	ATTR_ENCLOSURE			属性名 enclosure：括り文字 */
	const ATTR_ENCLOSURE				= 'enclosure';

	/** @var	string	ATTR_ESCAPE				属性名 escape：括り文字のエスケープ文字 */
	const ATTR_ESCAPE					= 'escape';

	/** @var	string	ATTR_QUOTE_MODE			属性名 quote_mode：書き出し時のクォート方法 */
	const ATTR_QUOTE_MODE				= 'quote_mode';

	/** @var	string	ATTR_EOL				属性名 eol：書き出し時の改行文字 */
	const ATTR_EOL							= 'eol';

	//処理
	/** @var	string	ATTR_CALLBACK			属性名 callback：行に対するコールバックフィルタ設定 */
	const ATTR_CALLBACK					= 'callback';

	//外部設定
	/** @var	string	ATTR_PHP_PATH			属性名 php_path：読込時PHPバイナリパス設定 */
	const ATTR_PHP_PATH					= 'php_path';






	//----------------------------------------------
	//オプション名：出力時のみ使用
	//----------------------------------------------
	/** @var	string	OPT_HEADER			オプション名 heder：CSVのヘッダ行設定 */
	const OPT_HEADER		= 'header';

	/** @var	string	OPT_FOOTER			オプション名 footer：CSVのフッタ行設定 */
	const OPT_FOOTER		= 'footer';

	/** @var	string	OPT_ECHO			オプション名 echo：書出時エコー出力設定 */
	const OPT_ECHO			= 'echo';

	/** @var	string	OPT_HTTP_OUTPUT		オプション名 http_output：書出時http response出力設定 */
	const OPT_HTTP_OUTPUT	= 'http_output';

	/** @var	string	OPT_STDOUT			オプション名 stdout：書出時標準出力設定 */
	const OPT_STDOUT		= 'stdout';

	//----------------------------------------------
	//オプション名：読込時のみ使用
	//----------------------------------------------
		/** @var	string	OPT_RETURN_ARRAY	オプション名 return_array：読込時配列取得設定 */
	const OPT_RETURN_ARRAY	= 'return_array';

	const OPT_READ_LENGTH	= 'read_length';










	//==============================================
	//Static Method
	//==============================================
	/**
	 * データベース固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 */
	public static function GetDefaultOptions ();

	/**
	 * データベースの型に対する\PDO::PARAM_*を返します。
	 *
	 * @return	array	データベースの型に対する\PDO::PARAM_*のリスト
	 */
	public static function GetPdoParamList ();

	//==============================================
	//Database Reflection
	//==============================================
	/**
	 * データベースの情報を返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	データベースの情報
	 */
	public function reflectionDatabase($forced_obtain = false);

	/**
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	テータベースに存在するテーブルのリスト。
	 */
	public function getTables($forced_obtain = false);

	/**
	 * テーブルのステータスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのステータス。
	 */
	public function getTableStatus($table_name, $forced_obtain = false);

	/**
	 * テーブルのカラム情報を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム情報。
	 */
	public function getColumns($table_name, $forced_obtain = false);

	/**
	 * テーブルのカラム名の一覧を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム名の一覧。
	 */
	public function getColumnNames($table_name, $forced_obtain = false);

	/**
	 * テーブルのインデックスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 */
	public function getIndexes ($table_name, $forced_obtain = false);

	/**
	 * テーブルのプライマリキーを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public function getPkeys ($table_name, $forced_obtain = false);

	/**
	 * デフォルト値を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public function getColumnDefaultValues ($table_name, $forced_obtain = false);

	/**
	 * NULL値を許容しないカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public function getNotNullColumns ($table_name, $forced_obtain = false);

	/**
	 * auto_incrementを持つカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public function getAutoIncrementColumns ($table_name, $forced_obtain = false);

	/**
	 * インサート用の行を作成し返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public function makeInsertRow ($table_name, array $row, $forced_obtain = false);
}
