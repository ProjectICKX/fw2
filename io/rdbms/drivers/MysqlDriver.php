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
 * Flywheel2 MysqlDriver
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class MysqlDriver extends abstracts\RdbmsDriverAbstract {
	use traits\RdbmsDriverTrait;

	const IDENTIFIER			= '`';

	/** @var	string	キャラクタセット名：big5 (Big5 Traditional Chinese) */
	const CHARSET_BIG5		= 'big5';

	/** @var	string	キャラクタセット名：dec8 (DEC West European) */
	const CHARSET_DEC8		= 'dec8';

	/** @var	string	キャラクタセット名：cp850 (DOS West European) */
	const CHARSET_CP850		= 'cp850';

	/** @var	string	キャラクタセット名：hp8 (HP West European) */
	const CHARSET_HP8		= 'hp8';

	/** @var	string	キャラクタセット名：koi8r (KOI8-R Relcom Russian) */
	const CHARSET_KOI8R		= 'koi8r';

	/** @var	string	キャラクタセット名：latin1 (cp1252 West European) */
	const CHARSET_LATIN1	= 'latin1';

	/** @var	string	キャラクタセット名：latin2 (ISO 8859-2 Central European ) */
	const CHARSET_LATIN2	= 'latin2';

	/** @var	string	キャラクタセット名：swe7 (7bit Swedish) */
	const CHARSET_SWE7		= 'swe7';

	/** @var	string	キャラクタセット名：ascii (US ASCII) */
	const CHARSET_ASCII		= 'ascii';

	/** @var	string	キャラクタセット名：ujis (EUC-JP Japanese) */
	const CHARSET_UJIS		= 'ujis';

	/** @var	string	キャラクタセット名：sjis (Shift-JIS Japanese) */
	const CHARSET_SJIS		= 'sjis';

	/** @var	string	キャラクタセット名：hebrew (ISO 8859-8 Hebrew) */
	const CHARSET_HEBREW	= 'hebrew';

	/** @var	string	キャラクタセット名：tis620 (TIS620 Thai) */
	const CHARSET_TIS620	= 'tis620';

	/** @var	string	キャラクタセット名：euckr (EUC-KR Korean) */
	const CHARSET_EUCKR		= 'euckr';

	/** @var	string	キャラクタセット名：koi8u (KOI8-U Ukrainian) */
	const CHARSET_KOI8U		= 'koi8u';

	/** @var	string	キャラクタセット名：gb2312 (GB2312 Simplified Chinese) */
	const CHARSET_GB2312	= 'gb2312';

	/** @var	string	キャラクタセット名：greek (ISO 8859-7 Greek) */
	const CHARSET_GREEK		= 'greek';

	/** @var	string	キャラクタセット名：cp1250 (Windows Central European) */
	const CHARSET_CP1250	= 'cp1250';

	/** @var	string	キャラクタセット名：gbk (GBK Simplified Chinese) */
	const CHARSET_GBK		= 'gbk';

	/** @var	string	キャラクタセット名：latin5 (ISO 8859-9 Turkish) */
	const CHARSET_LATIN5	= 'latin5';

	/** @var	string	キャラクタセット名：armscii8 (ARMSCII-8 Armenian) */
	const CHARSET_ARMSCII8	= 'armscii8';

	/** @var	string	キャラクタセット名：utf8 (UTF-8 Unicode) */
	const CHARSET_UTF8		= 'utf8';

	/** @var	string	キャラクタセット名：ucs2 (UCS-2 Unicode) */
	const CHARSET_UCS2		= 'ucs2';

	/** @var	string	キャラクタセット名：cp866 (DOS Russian) */
	const CHARSET_CP866		= 'cp866';

	/** @var	string	キャラクタセット名：keybcs2 (DOS Kamenicky Czech-Slovak) */
	const CHARSET_KEYBCS2	= 'keybcs2';

	/** @var	string	キャラクタセット名：macce (Mac Central European) */
	const CHARSET_MACCE		= 'macce';

	/** @var	string	キャラクタセット名：macroman (Mac West European) */
	const CHARSET_MACROMAN	= 'macroman';

	/** @var	string	キャラクタセット名：cp852 (DOS Central European) */
	const CHARSET_CP852		= 'cp852';

	/** @var	string	キャラクタセット名：latin7 (ISO 8859-13 Baltic) */
	const CHARSET_LATIN7	= 'latin7';

	/** @var	string	キャラクタセット名：utf8mb4 (UTF-8 Unicode) */
	const CHARSET_UTF8MB4	= 'utf8mb4';

	/** @var	string	キャラクタセット名：cp1251 (Windows Cyrillic) */
	const CHARSET_CP1251	= 'cp1251';

	/** @var	string	キャラクタセット名：utf16 (UTF-16 Unicode) */
	const CHARSET_UTF16		= 'utf16';

	/** @var	string	キャラクタセット名：utf16le (UTF-16LE Unicode) */
	const CHARSET_UTF16LE	= 'utf16le';

	/** @var	string	キャラクタセット名：cp1256 (Windows Arabic) */
	const CHARSET_CP1256	= 'cp1256';

	/** @var	string	キャラクタセット名：cp1257 (Windows Baltic) */
	const CHARSET_CP1257	= 'cp1257';

	/** @var	string	キャラクタセット名：utf32 (UTF-32 Unicode) */
	const CHARSET_UTF32		= 'utf32';

	/** @var	string	キャラクタセット名：binary (Binary pseudo charset) */
	const CHARSET_BINARY	= 'binary';

	/** @var	string	キャラクタセット名：geostd8 (GEOSTD8 Georgian) */
	const CHARSET_GEOSTD8	= 'geostd8';

	/** @var	string	キャラクタセット名：cp932 (SJIS for Windows Japanese) */
	const CHARSET_CP932		= 'cp932';

	/** @var	string	キャラクタセット名：eucjpms (UJIS for Windows Japanese) */
	const CHARSET_EUCJPMS	= 'eucjpms';

	/** @var	string	キャラクタセット名：alias of cp932 (SJIS for Windows Japanese) */
	const CHARSET_SJIS_WIN	= 'cp932';

	/** @var	string	キャラクタセット名：alias of eucjpms (UJIS for Windows Japanese) */
	const CHARSET_EUCJP_WIN	= 'eucjpms';

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
		$options = [
			\PDO::ATTR_AUTOCOMMIT				=> FALSE,
			\PDO::ATTR_EMULATE_PREPARES			=> FALSE,
			\PDO::MYSQL_ATTR_MULTI_STATEMENTS	=> FALSE,
		];
		if (defined('\PDO::MYSQL_ATTR_COMPRESS')) {
			$options[\PDO::MYSQL_ATTR_COMPRESS] = 1;
		}
		return $options;
	}

	public static function AdjustDsn ($dsn_config) {
		if (isset($dsn_config['unix_socket'])) {
			unset($dsn_config['host']);
			unset($dsn_config['port']);
		}

		if (!isset($dsn_config['charset'])) {
			if (isset($dsn_config['encoding'])) {
				$encoding = $dsn_config['encoding'];
				unset($dsn_config['encoding']);
			} else {
				$encoding = static::CHARSET_UTF8;
			}
			$dsn_config['charset'] = $encoding;
		}

		return $dsn_config;
	}

	public static function GetEnableCharsetList () {
		static $enable_charset_list;
		if (!isset($enable_charset_list)) {
			$enable_charset_list = [
				static::CHARSET_BIG5		=> static::CHARSET_BIG5,
				static::CHARSET_DEC8		=> static::CHARSET_DEC8,
				static::CHARSET_CP850		=> static::CHARSET_CP850,
				static::CHARSET_HP8			=> static::CHARSET_HP8,
				static::CHARSET_KOI8R		=> static::CHARSET_KOI8R,
				static::CHARSET_LATIN1		=> static::CHARSET_LATIN1,
				static::CHARSET_LATIN2		=> static::CHARSET_LATIN2,
				static::CHARSET_SWE7		=> static::CHARSET_SWE7,
				static::CHARSET_ASCII		=> static::CHARSET_ASCII,
				static::CHARSET_UJIS		=> static::CHARSET_UJIS,
				static::CHARSET_SJIS		=> static::CHARSET_SJIS,
				static::CHARSET_HEBREW		=> static::CHARSET_HEBREW,
				static::CHARSET_TIS620		=> static::CHARSET_TIS620,
				static::CHARSET_EUCKR		=> static::CHARSET_EUCKR,
				static::CHARSET_KOI8U		=> static::CHARSET_KOI8U,
				static::CHARSET_GB2312		=> static::CHARSET_GB2312,
				static::CHARSET_GREEK		=> static::CHARSET_GREEK,
				static::CHARSET_CP1250		=> static::CHARSET_CP1250,
				static::CHARSET_GBK			=> static::CHARSET_GBK,
				static::CHARSET_LATIN5		=> static::CHARSET_LATIN5,
				static::CHARSET_ARMSCII8	=> static::CHARSET_ARMSCII8,
				static::CHARSET_UTF8		=> static::CHARSET_UTF8,
				static::CHARSET_UCS2		=> static::CHARSET_UCS2,
				static::CHARSET_CP866		=> static::CHARSET_CP866,
				static::CHARSET_KEYBCS2		=> static::CHARSET_KEYBCS2,
				static::CHARSET_MACCE		=> static::CHARSET_MACCE,
				static::CHARSET_MACROMAN	=> static::CHARSET_MACROMAN,
				static::CHARSET_CP852		=> static::CHARSET_CP852,
				static::CHARSET_LATIN7		=> static::CHARSET_LATIN7,
				static::CHARSET_UTF8MB4		=> static::CHARSET_UTF8MB4,
				static::CHARSET_CP1251		=> static::CHARSET_CP1251,
				static::CHARSET_UTF16		=> static::CHARSET_UTF16,
				static::CHARSET_UTF16LE		=> static::CHARSET_UTF16LE,
				static::CHARSET_CP1256		=> static::CHARSET_CP1256,
				static::CHARSET_CP1257		=> static::CHARSET_CP1257,
				static::CHARSET_UTF32		=> static::CHARSET_UTF32,
				static::CHARSET_BINARY		=> static::CHARSET_BINARY,
				static::CHARSET_GEOSTD8		=> static::CHARSET_GEOSTD8,
				static::CHARSET_CP932		=> static::CHARSET_CP932,
				static::CHARSET_EUCJPMS		=> static::CHARSET_EUCJPMS,
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
			'int'				=> \PDO::PARAM_INT,
			'double'			=> \PDO::PARAM_INT,
			'tinyint'			=> \PDO::PARAM_INT,
			'smallint'			=> \PDO::PARAM_INT,
			'mediumint'			=> \PDO::PARAM_INT,
			'bigint'			=> \PDO::PARAM_INT,
			'float'				=> \PDO::PARAM_STR,
			'double'			=> \PDO::PARAM_STR,
			'double precision'	=> \PDO::PARAM_STR,
			'real'				=> \PDO::PARAM_STR,
			'decimal'			=> \PDO::PARAM_STR,
			'dec'				=> \PDO::PARAM_STR,
			'numeric'			=> \PDO::PARAM_STR,
			'fixed'				=> \PDO::PARAM_STR,
			'bit'				=> \PDO::PARAM_INT,
			//真偽値
			'bool'				=> \PDO::PARAM_BOOL,
			'boolean'			=> \PDO::PARAM_BOOL,
			//日付・時刻
			'datetime'			=> \PDO::PARAM_STR,
			'date'				=> \PDO::PARAM_STR,
			'time'				=> \PDO::PARAM_STR,
			'year'				=> \PDO::PARAM_STR,
			'timestamp'			=> \PDO::PARAM_INT,
			//文字・文字列
			'char'				=> \PDO::PARAM_STR,
			'varchar'			=> \PDO::PARAM_STR,
			'text'				=> \PDO::PARAM_STR,
			'mediumtext'		=> \PDO::PARAM_STR,
			'longtext'			=> \PDO::PARAM_STR,
			'national char'		=> \PDO::PARAM_STR,
			'nchar'				=> \PDO::PARAM_STR,
			'character'			=> \PDO::PARAM_STR,
			'tinytext'			=> \PDO::PARAM_STR,
			//バイナリ
			'binary'			=> \PDO::PARAM_LOB,
			'varbinary'			=> \PDO::PARAM_LOB,
			'blob'				=> \PDO::PARAM_LOB,
			'mediumblob'		=> \PDO::PARAM_LOB,
			'longblob'			=> \PDO::PARAM_LOB,
			'tinyblob'			=> \PDO::PARAM_LOB,
		];
	}

	//==============================================
	//Database Reflection
	//==============================================
	/**
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	テータベースに存在するテーブルのリスト。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	protected function _updateTables () {
		$this->_tables = $this->query('SHOW TABLES;')->fetchAll(\PDO::FETCH_COLUMN);
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
		$primary_column_name = $this->query(sprintf('SHOW INDEX FROM %s WHERE Key_name = \'PRIMARY\'', $table_name))->fetch(\PDO::FETCH_ASSOC);
		$primary_column_name = isset($primary_column_name['Column_name']) ? $primary_column_name['Column_name'] : null;

		$pdo_param_list = $this->GetPdoParamList();

		$this->_columns[$table_name] = [];
		foreach ($this->query(sprintf('SHOW FULL COLUMNS FROM %s;', $table_name), \PDO::FETCH_ASSOC) as $columns) {
			$this->_columns[$table_name][$columns['Field']] = [
				'column_name'		=> $columns['Field'],
				'type'				=> $columns['Type'],
				'not_null'			=> filter_var($columns['Null'], \FILTER_VALIDATE_BOOLEAN),
				'default'			=> $columns['Default'],
				'auto_increment'	=> $columns['Extra'] === 'auto_increment',
				'primary_key'		=> $primary_column_name === $columns['Field'],
				'comment'			=> $columns['Comment'],
				'raw_data'			=> $columns,
				'pdo_param'			=> $pdo_param_list[explode('(', $columns['Type'])[0]],
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
		$this->_indexes[$table_name] = [];
		foreach ($this->query(sprintf('SHOW INDEX FROM %s;', $table_name), \PDO::FETCH_ASSOC) as $index) {
			$this->_indexes[$table_name][$index['Key_name']][] = [
				'table_name'		=> $table_name,
				'temporary'			=> null,
				'unique'			=> !filter_var($index['Non_unique'], \FILTER_VALIDATE_BOOLEAN),
				'if_not_exists'		=> null,
				'key_name'			=> $index['Key_name'],
				'column_name'		=> $index['Column_name'],
				'seq_in_index'		=> $index['Seq_in_index'],
				'collation'			=> $index['Collation'],
				'cardinality'		=> $index['Cardinality'],
				'comment'			=> $index['Comment'],
				'index_comment'		=> $index['Index_comment'],
				'order'				=> null,
				'index_type'		=> $index['Index_type'],
				'sub_part'			=> $index['Sub_part'],
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
