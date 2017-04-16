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

namespace ickx\fw2\io\sdf\drivers;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\io\file_system\FileSystem;

/**
 * Flywheel2 CsvDriver
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class PlainTextDriver extends abstracts\SdfDriverAbstract {
	use traits\SdfDriverTrait;

	//==============================================
	//Overwrite Default Const
	//==============================================
	const DEFAULT_EOL				= self::EOL_LF;

	const DEFAULT_FOPEN_MODE_READ	= self::FOPEN_MODE_READ;

	const DEFAULT_FOPEN_MODE_WRITE	= self::FOPEN_MODE_WRITE;

	//==============================================
	//Static Method
	//==============================================
	/**
	 * ファイル固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()*
	 */
	public static function GetDefaultOptions () {
		return [
			static::ATTR_ENCODING_INTERNAL	=> static::DEFAULT_ENCODING_INTERNAL,
			static::ATTR_ENCODING_FILE		=> static::DEFAULT_ENCODING_FILE,
			static::ATTR_QUOTE_MODE			=> static::DEFAULT_QUOTE_MODE,
			static::ATTR_READ_LENGTH		=> static::DEFAULT_READ_LENGTH,
			static::ATTR_EOL				=> static::DEFAULT_EOL,
			static::ATTR_DELIMITER			=> static::DEFAULT_DELIMITER,
			static::ATTR_ENCLOSURE			=> static::DEFAULT_ENCLOSURE,
			static::ATTR_ESCAPE				=> static::DEFAULT_ESCAPE,
			static::ATTR_FOPEN_MODE_READ	=> static::DEFAULT_FOPEN_MODE_READ,
			static::ATTR_FOPEN_MODE_WRITE	=> static::DEFAULT_FOPEN_MODE_WRITE,
		];
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
