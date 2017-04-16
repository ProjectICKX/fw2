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

namespace ickx\fw2\io\rdbms\drivers;

/**
 * Flywheel2 PgsqlDriver
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class PgsqlDriver extends abstracts\RdbmsDriverAbstract {
	use traits\RdbmsDriverTrait;

	const IDENTIFIER			= '"';

	/** @var	string	キャラクタセット名：サーバの符号化方式としてはサポートしていません BIG5（BIG5(Big Five：繁体字)） */
	const CHARSET_BIG5				= 'BIG5';

	/** @var	string	キャラクタセット名：EUC_CN（EUC_CN(Extended UNIX Code-CN：簡体字)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_EUC_CN			= 'EUC_CN';

	/** @var	string	キャラクタセット名：EUC_JP（EUC_JP(Extended UNIX Code-JP：日本語)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、SJIS(Shift JIS：日本語)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_EUC_JP			= 'EUC_JP';

	/** @var	string	キャラクタセット名：EUC_KR（EUC_KR(Extended UNIX Code-KR：韓国語)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_EUC_KR			= 'EUC_KR';

	/** @var	string	キャラクタセット名：EUC_TW（EUC_TW(Extended UNIX Code-TW：繁体字、台湾語)、BIG5(Big Five：繁体字)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_EUC_TW			= 'EUC_TW';

	/** @var	string	キャラクタセット名：サーバの符号化方式としてはサポートしていません GB18030 */
	const CHARSET_GB18030			= 'GB18030';

	/** @var	string	キャラクタセット名：サーバの符号化方式としてはサポートしていません GBK */
	const CHARSET_GBK				= 'GBK';

	/** @var	string	キャラクタセット名：ISO_8859_5（ISO_8859_5(ISO 8859-5、ECMA 113：ラテン/キリル)、KOI8(KOI8-R：キリル文字（ロシア）)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)、WIN866(Windows CP866：キリル文字)、WIN1251(Windows CP1251：キリル文字)） */
	const CHARSET_ISO_8859_5		= 'ISO_8859_5';

	/** @var	string	キャラクタセット名：ISO_8859_6（ISO_8859_6(ISO 8859-6、ECMA 114：ラテン/アラビア語)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_ISO_8859_6		= 'ISO_8859_6';

	/** @var	string	キャラクタセット名：ISO_8859_7（ISO_8859_7(ISO 8859-7、ECMA 118：ラテン/ギリシャ語)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_ISO_8859_7		= 'ISO_8859_7';

	/** @var	string	キャラクタセット名：ISO_8859_8（ISO_8859_8(ISO 8859-8、ECMA 121：ラテン/ヘブライ語)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_ISO_8859_8		= 'ISO_8859_8';

	/** @var	string	キャラクタセット名：JOHAB（(JOHAB：韓国語（ハングル）)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_JOHAB				= 'JOHAB';

	/** @var	string	キャラクタセット名：KOI8R（KOI8R(KOI8-R：キリル文字（ロシア）)、ISO_8859_5(ISO 8859-5、ECMA 113：ラテン/キリル)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)、WIN866(Windows CP866：キリル文字)、WIN1251(Windows CP1251：キリル文字)） */
	const CHARSET_KOI8R				= 'KOI8R';

	/** @var	string	キャラクタセット名：KOI8U（KOI8U(KOI8-U：キリル文字（ウクライナ）)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_KOI8U				= 'KOI8U';

	/** @var	string	キャラクタセット名：LATIN1（LATIN1(ISO 8859-1、ECMA 94：西ヨーロッパ)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN1			= 'LATIN1';

	/** @var	string	キャラクタセット名：LATIN2（LATIN2(ISO 8859-2、ECMA 94：中央ヨーロッパ)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)、WIN1250(Windows CP1250：中央ヨーロッパ)） */
	const CHARSET_LATIN2			= 'LATIN2';

	/** @var	string	キャラクタセット名：LATIN3（LATIN3(ISO 8859-3、ECMA 94：南ヨーロッパ)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN3			= 'LATIN3';

	/** @var	string	キャラクタセット名：LATIN4（LATIN4(ISO 8859-4、ECMA 94：北ヨーロッパ)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN4			= 'LATIN4';

	/** @var	string	キャラクタセット名：LATIN5（LATIN5(ISO 8859-9、ECMA 128：トルコ)(ISO 8859-9、ECMA 128：トルコ)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN5			= 'LATIN5';

	/** @var	string	キャラクタセット名：LATIN6（LATIN6(ISO 8859-10、ECMA 144：北欧)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN6			= 'LATIN6';

	/** @var	string	キャラクタセット名：LATIN7（LATIN7(ISO 8859-13：バルト語派)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN7			= 'LATIN7';

	/** @var	string	キャラクタセット名：LATIN8（LATIN8(ISO 8859-14：ケルト)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN8			= 'LATIN8';

	/** @var	string	キャラクタセット名：LATIN9（LATIN9(ISO 8859-15：LATIN1でヨーロッパと訛りを含む)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN9			= 'LATIN9';

	/** @var	string	キャラクタセット名：LATIN10（LATIN10(ISO 8859-16、ASRO SR 14111：ルーマニア)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_LATIN10			= 'LATIN10';

	/** @var	string	キャラクタセット名：MULE_INTERNAL（MULE_INTERNAL(Mule内部コード：多言語Emacs)、BIG5(Big Five：繁体字)、EUC_CN(Extended UNIX Code-CN：簡体字)、EUC_JP(Extended UNIX Code-JP：日本語)、EUC_KR(Extended UNIX Code-KR：韓国語)、EUC_TW(Extended UNIX Code-TW：繁体字、台湾語)、ISO_8859_5(ISO 8859-5、ECMA 113：ラテン/キリル)、KOI8R(KOI8-R：キリル文字（ロシア）)、LATIN1(ISO 8859-1、ECMA 94：西ヨーロッパ) to LATIN4(ISO 8859-4、ECMA 94：北ヨーロッパ)、SJIS(Shift JIS：日本語)、WIN866(Windows CP866：キリル文字)、WIN1250(Windows CP1250：中央ヨーロッパ)、WIN1251(Windows CP1251：キリル文字)） */
	const CHARSET_MULE_INTERNAL		= 'MULE_INTERNAL';

	/** @var	string	キャラクタセット名：サーバの符号化方式としてはサポートしていません SJIS */
	const CHARSET_SJIS				= 'SJIS';

	/** @var	string	キャラクタセット名：SQL_ASCII（どれでも （変換されません）） */
	const CHARSET_SQL_ASCII			= 'SQL_ASCII';

	/** @var	string	キャラクタセット名：サーバの符号化方式としてはサポートしていません UHC */
	const CHARSET_UHC				= 'UHC';

	/** @var	string	キャラクタセット名：UTF8（すべてサポートされています。） */
	const CHARSET_UTF8				= 'UTF8';

	/** @var	string	キャラクタセット名：WIN866（WIN866(Windows CP866：キリル文字)、ISO_8859_5(ISO 8859-5、ECMA 113：ラテン/キリル)、KOI8R(KOI8-R：キリル文字（ロシア）)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)、WIN1251(Windows CP1251：キリル文字)） */
	const CHARSET_WIN866			= 'WIN866';

	/** @var	string	キャラクタセット名：WIN874（WIN874、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN874			= 'WIN874';

	/** @var	string	キャラクタセット名：WIN1250（WIN1250(Windows CP1250：中央ヨーロッパ)、LATIN2(ISO 8859-2、ECMA 94：中央ヨーロッパ)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1250			= 'WIN1250';

	/** @var	string	キャラクタセット名：WIN1251（WIN1251(Windows CP1251：キリル文字)、ISO_8859_5(ISO 8859-5、ECMA 113：ラテン/キリル)、KOI8R(KOI8-R：キリル文字（ロシア）)、MULE_INTERNAL(Mule内部コード：多言語Emacs)、UTF8(Unicode、8ビット：すべて)、WIN866(Windows CP866：キリル文字)） */
	const CHARSET_WIN1251			= 'WIN1251';

	/** @var	string	キャラクタセット名：WIN1252（WIN1252(Windows CP1252：西ヨーロッパ)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1252			= 'WIN1252';

	/** @var	string	キャラクタセット名：WIN1253（WIN1253(Windows CP1253：ギリシャ)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1253			= 'WIN1253';

	/** @var	string	キャラクタセット名：WIN1254（WIN1254(Windows CP1254：トルコ)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1254			= 'WIN1254';

	/** @var	string	キャラクタセット名：WIN1255（WIN1255(Windows CP1255：ヘブライ)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1255			= 'WIN1255';

	/** @var	string	キャラクタセット名：WIN1256（WIN1256(Windows CP1256：アラビア語)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1256			= 'WIN1256';

	/** @var	string	キャラクタセット名：WIN1257（WIN1257(Windows CP1257：バルト語派)、UTF8(Unicode、8ビット：すべて)） */
	const CHARSET_WIN1257			= 'WIN1257';

	/** @var	string	キャラクタセット名：WIN1258（WIN1258(Windows CP1258：ベトナム語)、UTF8(Unicode、8ビット：すべて) ） */
	const CHARSET_WIN1258			= 'WIN1258';

	//==============================================
	//Static Method
	//==============================================
	/**
	 * データベース固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()*
	 */
	public static function GetDefaultOptions () {
		return [];
	}

	public static function AdjustDsn ($dsn_config) {
		if (isset($dsn_config['unix_socket'])) {
			unset($dsn_config['host']);
			unset($dsn_config['port']);
		}

		$encoding = static::CHARSET_UTF8;
		if (isset($dsn_config['charset'])) {
			$dsn_config['encoding'] = $dsn_config['charset'];
			unset($dsn_config['charset']);
		}
		if (isset($dsn_config['encoding'])) {
			$encoding = $dsn_config['encoding'];
			unset($dsn_config['encoding']);
		}

		$dsn_config['options'] = sprintf("'--client_encoding=%s'", $encoding);

		return $dsn_config;
	}

	public static function GetEnableCharsetList () {
		static $enable_charset_list;
		if (!isset($enable_charset_list)) {
			$enable_charset_list = [
				static::CHARSET_BIG5			=> static::CHARSET_BIG5,
				static::CHARSET_EUC_CN			=> static::CHARSET_EUC_CN,
				static::CHARSET_EUC_JP			=> static::CHARSET_EUC_JP,
				static::CHARSET_EUC_KR			=> static::CHARSET_EUC_KR,
				static::CHARSET_EUC_TW			=> static::CHARSET_EUC_TW,
				static::CHARSET_GB18030			=> static::CHARSET_GB18030,
				static::CHARSET_GBK				=> static::CHARSET_GBK,
				static::CHARSET_ISO_8859_5		=> static::CHARSET_ISO_8859_5,
				static::CHARSET_ISO_8859_6		=> static::CHARSET_ISO_8859_6,
				static::CHARSET_ISO_8859_7		=> static::CHARSET_ISO_8859_7,
				static::CHARSET_ISO_8859_8		=> static::CHARSET_ISO_8859_8,
				static::CHARSET_JOHAB			=> static::CHARSET_JOHAB,
				static::CHARSET_KOI8R			=> static::CHARSET_KOI8R,
				static::CHARSET_KOI8U			=> static::CHARSET_KOI8U,
				static::CHARSET_LATIN1			=> static::CHARSET_LATIN1,
				static::CHARSET_LATIN2			=> static::CHARSET_LATIN2,
				static::CHARSET_LATIN3			=> static::CHARSET_LATIN3,
				static::CHARSET_LATIN4			=> static::CHARSET_LATIN4,
				static::CHARSET_LATIN5			=> static::CHARSET_LATIN5,
				static::CHARSET_LATIN6			=> static::CHARSET_LATIN6,
				static::CHARSET_LATIN7			=> static::CHARSET_LATIN7,
				static::CHARSET_LATIN8			=> static::CHARSET_LATIN8,
				static::CHARSET_LATIN9			=> static::CHARSET_LATIN9,
				static::CHARSET_LATIN10			=> static::CHARSET_LATIN10,
				static::CHARSET_MULE_INTERNAL	=> static::CHARSET_MULE_INTERNAL,
				static::CHARSET_SJIS			=> static::CHARSET_SJIS,
				static::CHARSET_SQL_ASCII		=> static::CHARSET_SQL_ASCII,
				static::CHARSET_UHC				=> static::CHARSET_UHC,
				static::CHARSET_UTF8			=> static::CHARSET_UTF8,
				static::CHARSET_WIN866			=> static::CHARSET_WIN866,
				static::CHARSET_WIN874			=> static::CHARSET_WIN874,
				static::CHARSET_WIN1250			=> static::CHARSET_WIN1250,
				static::CHARSET_WIN1251			=> static::CHARSET_WIN1251,
				static::CHARSET_WIN1252			=> static::CHARSET_WIN1252,
				static::CHARSET_WIN1253			=> static::CHARSET_WIN1253,
				static::CHARSET_WIN1254			=> static::CHARSET_WIN1254,
				static::CHARSET_WIN1255			=> static::CHARSET_WIN1255,
				static::CHARSET_WIN1256			=> static::CHARSET_WIN1256,
				static::CHARSET_WIN1257			=> static::CHARSET_WIN1257,
				static::CHARSET_WIN1258			=> static::CHARSET_WIN1258,
			];
		}
		return $enable_charset_list;
	}

	/**
	 * データベースの型に対する\PDO::PARAM_*を返します。
	 *
	 * @return	array	データベースの型に対する\PDO::PARAM_*のリスト
	 */
	public static function GetPdoParamList () {
		return [
			//数値
			'smallint'			=> \PDO::PARAM_INT,
			'integer'			=> \PDO::PARAM_INT,
			'bigint'			=> \PDO::PARAM_INT,
			'decimal'			=> \PDO::PARAM_STR,
			'numeric'			=> \PDO::PARAM_STR,
			'real'				=> \PDO::PARAM_STR,
			'double precision'	=> \PDO::PARAM_STR,
			'serial'			=> \PDO::PARAM_INT,
			'bigserial'			=> \PDO::PARAM_INT,
			//通貨
			'money'				=> \PDO::PARAM_STR,
			//文字・文字列
			'char'				=> \PDO::PARAM_STR,
			'character'			=> \PDO::PARAM_STR,
			'varchar'			=> \PDO::PARAM_STR,
			'character varying'	=> \PDO::PARAM_STR,
			'text'				=> \PDO::PARAM_STR,
			//バイナリ
			'bytea'				=> \PDO::PARAM_LOB,
			//日付・時刻
			'timestamp'			=> \PDO::PARAM_STR,
			'time'				=> \PDO::PARAM_STR,
			'date'				=> \PDO::PARAM_STR,
			'interval'			=> \PDO::PARAM_STR,
			//真偽値
			'boolean'			=> \PDO::PARAM_BOOL,
			//列挙
			'enum'				=> \PDO::PARAM_LOB,
			//幾何
			'moneypoint'		=> \PDO::PARAM_LOB,
			'line'				=> \PDO::PARAM_LOB,
			'lseg'				=> \PDO::PARAM_LOB,
			'box'				=> \PDO::PARAM_LOB,
			'path'				=> \PDO::PARAM_LOB,
			'path'				=> \PDO::PARAM_LOB,
			'polygon'			=> \PDO::PARAM_LOB,
			'circle'			=> \PDO::PARAM_LOB,
			//ネットワークアドレス
			'cidr'				=> \PDO::PARAM_STR,
			'inet'				=> \PDO::PARAM_STR,
			'macaddr'			=> \PDO::PARAM_STR,
			//ビット列
			'bit'				=> \PDO::PARAM_INT,
			'bit varying'		=> \PDO::PARAM_INT,
			//テキスト検索
			'tsvector'			=> \PDO::PARAM_LOB,
			//UUID
			'uuid'				=> \PDO::PARAM_STR,
			//XML
			'xml_parse'			=> \PDO::PARAM_STR,
		];
	}

	//==============================================
	//Database Reflection
	//==============================================
	/***
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	テータベースに存在するテーブルのリスト。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	protected function _updateTables () {
		$this->_tables = $this->query('SELECT relname FROM pg_stat_user_tables')->fetchAll(\PDO::FETCH_COLUMN);
	}

	/**
	 * テーブルのステータスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのステータス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTableStatus()
	 */
	protected function _updateTableStatus ($table_name) {
		$this->_tableStatus = [];
		$stmt = $this->prepare('SHOW TABLE STATUS LIKE ?;');
		$stmt->execute([$table_name]);
		$this->_tableStatus[$table_name] = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0];
	}

	/**
	 * テーブルのカラム情報を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム情報。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateColumns ($table_name) {
		$stmt = $this->prepare('SELECT ccu.column_name FROM information_schema.table_constraints tc, information_schema.constraint_column_usage ccu WHERE tc.table_catalog = current_database() AND tc.table_name = ? AND tc.constraint_type = ? AND tc.table_catalog=ccu.table_catalog AND tc.table_schema = ccu.table_schema AND tc.table_name = ccu.table_name AND tc.constraint_name = ccu.constraint_name');
		$stmt->execute([$table_name, 'PRIMARY KEY']);
		$stmt->setFetchMode(\PDO::FETCH_NUM);
		$pkey_column_name = $stmt->fetch();
		$pkey_column_name = isset($pkey_column_name[0]) ? $pkey_column_name : null;

		$comments = [];
		$stmt = $this->prepare('SELECT pa.attname as column_name, pd.description as column_comment FROM pg_stat_all_tables psat, pg_description pd, pg_attribute pa WHERE psat.schemaname = current_schema() AND psat.relname = ? AND psat.relid = pd.objoid AND pd.objsubid <> 0 AND pd.objoid = pa.attrelid AND pd.objsubid = pa.attnum');
		$stmt->execute([$table_name]);
		foreach ($stmt as $comment) {
			$comments[$comment['column_name']] = $comment['column_comment'];
		}

		$pdo_param_list = $this->GetPdoParamList();

		$this->_columns[$table_name] = [];
		$stmt = $this->prepare('SELECT *  FROM information_schema.columns WHERE table_catalog = current_database() AND table_name = ? ORDER BY ordinal_position');
		$stmt->setFetchMode(\PDO::FETCH_ASSOC);
		$stmt->execute([$table_name]);
		foreach ($stmt as $columns) {
			$auto_increment = preg_match("/^nextval\('.+'::regclass\)$/", $columns['column_default']) !== 0;
			$this->_columns[$table_name][$columns['column_name']] = [
				'column_name'		=> $columns['column_name'],
				'type'				=> $columns['data_type'],
				'not_null'			=> filter_var($columns['is_nullable'], FILTER_VALIDATE_BOOLEAN),
				'default'			=> $columns['column_default'],
				'auto_increment'	=> $auto_increment,
				'primary_key'		=> $auto_increment ?: ($pkey_column_name === $columns['column_name']),
				'comment'			=> isset($comments[$columns['column_name']]) ? $comments[$columns['column_name']] : '',
				'raw_data'			=> $columns,
				'pdo_param'			=> $pdo_param_list[explode('(', $columns['data_type'])[0]],
			];
		}
	}

	/**
	 * テーブルのインデックスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateIndexes ($table_name) {
		$seq_in_index = [];

		$this->_indexes[$table_name] = [];

		foreach ($this->getColumns($table_name, true) as $column) {
			if ($column['primary_key']) {
				if (!isset($seq_in_index['PRIMARY'])) {
					$seq_in_index['PRIMARY'] = 1;
				}
				$this->_indexes[$table_name]['PRIMARY'][] = [
					'table_name'		=> $table_name,
					'temporary'			=> null,
					'unique'			=> true,
					'if_not_exists'		=> null,
					'key_name'			=> 'PRIMARY',
					'column_name'		=> $column['column_name'],
					'seq_in_index'		=> $seq_in_index['PRIMARY']++,
					'collation'			=> null,
					'cardinality'		=> null,
					'comment'			=> null,
					'index_comment'		=> null,
					'order'				=> null,
					'index_type'		=> null,
					'sub_part'			=> null,
					'raw_data'			=> null,
				];
			}
		}

		$stmt = $this->prepare('SELECT pis.tablename AS table_name, pis.indexname AS index_name, pa.attname AS column_name, pi.indisunique AS unique, pi.indisprimary AS primary, pis.indexdef AS sql, pam.amname AS hash_type FROM pg_indexes pis, pg_class pc_i, pg_index pi, pg_class pc_t, pg_attribute pa, pg_am pam WHERE pis.schemaname = current_schema() AND pis.tablename = ? AND pc_i.relname IN (pis.indexname) AND pi.indexrelid IN (pc_i.oid) AND pc_t.relname = ? AND pa.attrelid = pc_t.oid AND pa.attnum = ANY(pi.indkey) AND pc_i.relam = pam.oid');
		$stmt->execute([$table_name, $table_name]);
		$stmt->setFetchMode(\PDO::FETCH_ASSOC);
		foreach ($stmt as $index) {
			$name = $index['primary'] ? 'PRIMARY' : $index['index_name'];
			if (!isset($seq_in_index[$name])) {
				$seq_in_index[$name] = 1;
			}
			$this->_indexes[$table_name][$name][] = [
				'table_name'		=> $table_name,
				'temporary'			=> null,
				'unique'			=> $index['unique'],
				'if_not_exists'		=> null,
				'key_name'			=> $name,
				'column_name'		=> $index['column_name'],
				'seq_in_index'		=> $seq_in_index[$name]++,
				'collation'			=> null,
				'cardinality'		=> null,
				'comment'			=> null,
				'index_comment'		=> null,
				'order'				=> null,
				'index_type'		=> null,
				'sub_part'			=> null,
				'raw_data'			=> $index,
			];
		}
	}

	/**
	 * テーブルのプライマリキーを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updatePkeys ($table_name) {
		if (isset($this->getIndexes($table_name)['PRIMARY'])) {
			$this->_primaryKeys[$table_name] = [];
			$primary_keys = $this->getIndexes($table_name, true)['PRIMARY'];
			usort($primary_keys, function ($current, $next) {return $current['seq_in_index'] < $next['seq_in_index'];});
			foreach ($primary_keys as $primary_key) {
				$this->_primaryKeys[$table_name][] = $primary_key['column_name'];
			}
		} else {
			$this->_primaryKeys[$table_name] = null;
		}
	}

	/**
	 * デフォルト値を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateColumnDefaultValues ($table_name) {
		$this->_columnDefaultValues[$table_name] = [];
		foreach ($this->getColumns($table_name) as $column_name => $column) {
			if ($column['default'] !== null) {
				$this->_columnDefaultValues[$table_name][$column_name] = $column['default'];
			}
		}
	}

	/**
	 * NULL値を許容しないカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateNotNullColumns ($table_name, $forced_obtain = false) {
		$this->_notNullColumns[$table_name] = [];
		foreach ($this->getColumns($table_name) as $column_name => $column) {
			if ($column['not_null']) {
				$this->_notNullColumns[$table_name][$column_name] = true;
			}
		}
	}

	/**
	 * auto_incrementを持つカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateAutoIncrementColumns ($table_name, $forced_obtain = false) {
		$this->_autoIncrementColumns[$table_name] = [];
		foreach ($this->getColumns($table_name) as $column_name => $column) {
			if ($column['Extra'] == 'auto_increment') {
				$this->_autoIncrementColumns[$table_name][$column_name] = true;
			}
		}
	}

	/**
	 * インサート用の行を作成し返します。
	 *
	 * @param	string	テーブル名
	 * @param	array	上書き用配列
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function makeInsertRow ($table_name, array $merge_row = [], $forced_obtain = false) {
		$merge_row += $merge_row + $this->getColumnDefaultValues($table_name, $forced_obtain);

		$not_null_columns = $this->getNotNullColumns($table_name, $forced_obtain);
		$columns = $this->getColumns($table_name, $forced_obtain);

		$auto_increment_column_list = [];
		foreach (array_keys($merge_row) as $column_name) {
			if (isset($auto_increment_column_list[$column_name])) {
				unset($merge_row[$column_name]);
			}
			if (isset($not_null_columns[$column_name]) && $merge_row[$column_name] === null) {
				$type = static::GetType($columns[$column_name]['Type']);
				switch (static::GetPdoParamList()[$type]) {
					case \PDO::PARAM_INT:
						$value = 0;
						break;
					case \PDO::PARAM_BOOL:
						$value = false;
						break;
					case \PDO::PARAM_STR:
						$value = '';
						break;
					case \PDO::PARAM_LOB:
						$value = 0;
						break;
					default:
						$value = '';
						break;
				}
				$merge_row[$column_name] = $value;
			}
		}
		ksort($merge_row);
		return $merge_row;
	}

	/**
	 * Databaseの定義情報からカラムの型名だけを取得します。
	 *
	 * @param	string	$type	Database定義情報上のカラム名
	 * @return	カラムの型名
	 */
	public static function GetType ($type) {
		if (preg_match("/^(int|double|tinyint|smallint|mediumint|bigint|float|double precision|real|decimal|dec|numeric|fixed|bit|bool|boolean|datetime|date|time|year|timestamp|char|varchar|text|mediumtext|longtext|national char|nchar|character|tinytext|binary|varbinary|blob|mediumblob|longblob|tinyblob)(:?$|\()/", $type, $matches) === 1) {
			return $matches[1];
		}
		return $type;
	}
}
