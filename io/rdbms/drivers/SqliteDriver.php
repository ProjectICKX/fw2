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
 * Flywheel2 SqliteDriver
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class SqliteDriver extends abstracts\RdbmsDriverAbstract {
	use traits\RdbmsDriverTrait;

	const IDENTIFIER			= '`';

	/** @var	string	キャラクタセット名：utf8 (UTF-8 Unicode) */
	const CHARSET_UTF8		= 'utf8';

	const ON_MEMORY_DB	= ':memory:';

	/**
	 * データベース固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	public static function GetDefaultOptions () {
		return [];
	}

	public static function AdjustDsn ($dsn_config) {
		if (isset($dsn_config['file'])) {
			$dsn_config = ['file' => static::AdjustFilePathEncoding($dsn_config['file'])] + $dsn_config;

			if ($dsn_config['file'] !== static::ON_MEMORY_DB){
				clearstatcache(true, $dsn_config['file']);
				if (!file_exists($dsn_config['file'])) {
					if (!file_exists(dirname($dsn_config['file']))) {
						mkdir(dirname($dsn_config['file']), 0775, true);
					}
					touch($dsn_config['file']);
					chmod($dsn_config['file'], 0664);
				}
			}
		}
		$dsn_config['charset'] = static::CHARSET_UTF8;
		return $dsn_config;
	}

	public static function GetDsnFilterTargetList () {
		return [];
	}

	public static function DsnFilter ($dsn_config) {
		$ret = [];
		foreach (['file'] as $set_key) {
			if (isset($dsn_config[$set_key])) {
				$ret[$set_key] = $dsn_config[$set_key];
			}
		}
		return $ret;
	}

	public static function GetValidateRuleList () {
		return [
			'file'	=> [
				'raise_exception'	=> true,
				'set_validate_type'	=> function ($value, $options) {
					return $value === static::ON_MEMORY_DB ?: 'file';
				},
				'validate_set'	=> [
					'file'	=> [
						['require', 'message' => 'SQLite DSN:ファイルパートが設定されていません。'],
						['parent_is_dir', 'message' => 'SQLiteファイルの親がディレクトリではありません。{:value}'],
						['parent_exists', 'message' => 'SQLiteファイルの親ディレクトリがありません。{:value}'],
						['parent_executable', 'message' => 'SQLiteファイルの親ディレクトリを開けません。{:value}'],
						['parent_readable', 'message' => 'SQLiteファイルの親ディレクトリを読み込めません。{:value}'],
						['parent_writable', 'message' => 'SQLiteファイルの親ディレクトリに書き込めません。{:value}'],
						['file_exists', 'message' => 'SQLiteファイルがありません。{:value}'],
						['is_file', 'message' => 'SQLiteファイルパスがファイルではありません。{:value}'],
						['readable', 'message' => 'SQLiteファイルを読み込めません。{:value}'],
						['writable', 'message' => 'SQLiteファイルに書き込めません。{:value}'],
					],
				],
			],
		];
	}

	public static function GetEnableCharsetList () {
		static $enable_charset_list;
		if (!isset($enable_charset_list)) {
			$enable_charset_list = [
				static::CHARSET_UTF8		=> static::CHARSET_UTF8,
			];
		}
		return $enable_charset_list;
	}

	/**
	 * DSN設定配列からDSN文字列を生成します。
	 *
	 * @param	array	$dsn_config	DSN設定配列
	 * @return	string	DSN設定文字列
	 */
	public static function MakeDsn ($dsn_config) {
		$dsn = [$dsn_config['file']];
		unset($dsn_config['file']);
		foreach (static::DsnFilter($dsn_config) as $name => $config) {
			$dsn[] = sprintf('%s=%s', $name, $config);
		}
		return $dsn_config['driver'] .':'. implode(';', $dsn);
	}

	/**
	 * データベースの型に対する\PDO::PARAM_*を返します。
	 *
	 * @return	array	データベースの型に対する\PDO::PARAM_*のリスト
	 */
	public static function GetPdoParamList () {
		return [
			//数値
			'INTEGER'			=> \PDO::PARAM_INT,
			'NUMERIC'			=> \PDO::PARAM_INT,
			'REAL'				=> \PDO::PARAM_INT,
			//文字・文字列
			'TEXT'				=> \PDO::PARAM_STR,
			//バイナリ
			'NONE'				=> \PDO::PARAM_LOB,
		];
	}

	public function createTableSchema ($table_name, $schema_seed) {
		$schema = [];
		foreach ($schema_seed as $column_name => $column_options) {
			$schema[] = implode(' ', array_merge([$column_name], $column_options));
		}
		return sprintf('CREATE TABLE %s (%s);', $table_name, implode(', ', $schema));
	}

 	public function createIndexSchema ($table_name, $schema_seed) {
 		$schema_set = [];
 		foreach ($schema_seed as $index_name => $column_options) {
 			$columns = $column_options[0];
 			if (strlen($index_name) === strspn($index_name, '1234567890')) {
 				$index_name = sprintf('idx_%s_%s', implode('_', $columns), $table_name);
 			}
 			$schema_set[] = sprintf('CREATE INDEX %s ON %s (%s);', $index_name, $table_name, implode(', ', $columns));
 		}
 		return $schema_set;
 	}

	//==============================================
	//Database Reflection
	//==============================================
	/**
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @return	array	テータベースに存在するテーブルのリスト。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	protected function _updateTables () {
		$this->_tables = $this->query('SELECT tbl_name FROM (SELECT * FROM sqlite_master UNION ALL SELECT * FROM sqlite_temp_master) WHERE type = \'table\' AND NOT tbl_name = \'sqlite_sequence\'', \PDO::FETCH_COLUMN, 0)->fetchAll();
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
		$this->_tableStatus[$table_name] = [
			'name'	=> $table_name,
		];
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
		$auto_increment_column_name = null;
		$stmt	= $this->prepare('SELECT sql FROM sqlite_master WHERE type = ? AND tbl_name = ?');
		$stmt->execute(['table', $table_name]);
		$schema = $stmt->fetch(\PDO::FETCH_ASSOC)['sql'];
		if (preg_match("/^\s*([^\s]+).*\s+AUTOINCREMENT\s*(?:,|\))/mi", $schema, $mathes) !== 0) {
			$auto_increment_column_name = $mathes[1];
		}

		$pdo_param_list = $this->GetPdoParamList();

		$this->_columns[$table_name] = [];
		foreach ($this->query(sprintf('PRAGMA table_info(%s);', $table_name), \PDO::FETCH_ASSOC) as $columns) {
			$this->_columns[$table_name][$columns['name']] = [
				'column_name'		=> $columns['name'],
				'type'				=> $columns['type'],
				'not_null'			=> filter_var($columns['notnull'], FILTER_VALIDATE_BOOLEAN),
				'default'			=> $columns['dflt_value'],
				'auto_increment'	=> $auto_increment_column_name === $columns['name'],
				'comment'			=> '',
				'primary_key'		=> filter_var($columns['pk'], FILTER_VALIDATE_BOOLEAN),
				'raw_data'			=> $columns,
				'pdo_param'			=> $pdo_param_list[$columns['type']],
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

		//スキーマからインデックス情報を抜く
		//プライマリキーの取得
		foreach ($this->getColumns($table_name, true) as $column_info) {
			if ($column_info['primary_key']) {
				$seq_in_index = 1;
				foreach (explode(',', $column_info['column_name']) as $column_name) {
					$this->_indexes[$table_name]['PRIMARY'][] = [
						'table_name'		=> $table_name,
						'temporary'			=> null,
						'unique'			=> null,
						'if_not_exists'		=> null,
						'key_name'			=> 'PRIMARY',
						'column_name'		=> $column_name,
						'seq_in_index'		=> $seq_in_index++,
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
				break;
			}
		}

		//インデックスの取得
		$stmt	= $this->prepare('SELECT tbl_name, name, sql FROM sqlite_master WHERE type = ? AND tbl_name = ?');
		$stmt->execute(['index', $table_name]);
		$stmt->setFetchMode(\PDO::FETCH_ASSOC);
		foreach ($stmt as $index_schema) {
			$tbl_name = $index_schema['tbl_name'];
			$name = $index_schema['name'];
			$schema = preg_replace("/^\s*CREATE\s+|\s*\)\s*/i", '', str_replace(["\r\n", "\r", "\n"], '', $index_schema['sql']));

			if (false !== $temporary = preg_match("/^(?:TEMP|TEMPORARY)\s+(.+)/i", $schema, $matches) !== 0) {
				$schema = $matches[1];
			}

			if (false !== $unique = preg_match("/^UNIQUE\s+(.+)/i", $schema, $matches) !== 0) {
				$schema = $matches[1];
			}

			$schema = preg_replace("/^INDEX\s+/i", '', $schema);

			if (false !== $if_not_exists = preg_match("/^IF\s+NOT\s+EXISTS\s+(.+)/i", $schema, $matches) !== 0) {
				$schema = $matches[1];
			}

			$schema = preg_replace(sprintf("/^%s\s+ON\s+%s\s*\(/i", $name, $tbl_name), '', $schema);

			$indexed_column = [];
			$seq_in_index = 1;
			foreach (explode(',', $schema) as $element) {
				if ($element === '') {
					continue;
				}
				preg_match("/^\s*([^\s]+)(.*)/i", $element, $matches);

				$column_name = $matches[1];
				$schema = $matches[2];

				$collection_name = null;
				$order = null;

				if (preg_match("/^\s+COLLATE\s+([^\s]+)(.*)/i", $schema, $matches) !== 0) {
					$collection_name = $matches[1];
					$schema = $matches[2];
				}
				if (preg_match("/^\s+(ASC|DESC)/i", $schema, $matches) !== 0) {
					$order = $matches[1];
				}

				$this->_indexes[$table_name][$name][] = [
					'table_name'		=> $table_name,
					'temporary'			=> $temporary,
					'unique'			=> $unique,
					'if_not_exists'		=> $if_not_exists,
					'key_name'			=> $name,
					'column_name'		=> $column_name,
					'seq_in_index'		=> $seq_in_index++,
					'collation'			=> $collection_name,
					'cardinality'		=> null,
					'comment'			=> null,
					'index_comment'		=> null,
					'order'				=> $order,
					'index_type'		=> null,
					'sub_part'			=> null,
					'raw_data'			=> null,
				];
			}
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
			if (!filter_var($column['not_null'], \FILTER_VALIDATE_BOOLEAN)) {
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
			if (isset($column['Extra']) && $column['Extra'] == 'auto_increment') {
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
				$sqlite_data_type		= $columns[$column_name]['type'];
				$sqlite_default_value	= $columns[$column_name]['default'];
				$type = static::GetType($sqlite_data_type);
				switch (static::GetPdoParamList()[$type]) {
					case \PDO::PARAM_INT:
						$value = (int) $sqlite_default_value;
						break;
					case \PDO::PARAM_BOOL:
						$value = (bool) $sqlite_default_value;
						break;
					case \PDO::PARAM_STR:
						$value = (string) $sqlite_default_value;
						break;
					case \PDO::PARAM_LOB:
					default:
						$value = $sqlite_default_value;
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
