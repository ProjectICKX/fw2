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

namespace ickx\fw2\io\sdf\drivers\abstracts;

use ickx\fw2\core\log\StaticLog;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\FileSystem;

/**
 * Flywheel2 RDBMSDriver Abstract Class
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class SdfDriverAbstract implements \ickx\fw2\io\sdf\drivers\interfaces\ISdfDriver {
	//==============================================
	//Property
	//==============================================
	/** @var	string		ファイルパス */
	protected $_filePath				= null;

	/** @var	resource	ポインタ */
	protected $_pointer					= null;

	protected $_isLock					= false;

	/** @var	array		カラムのリスト */
	protected $_columns					= [];

	/** @var	array		カラム名のリスト */
	protected $_columnNames				= [];

	/** @var	array		ファイル設定のリスト */
	protected $_fileStatus				= [];

	/** @var	array		カラムに紐づくユニークインデックス名のリスト */
	protected $_uniqueIndexNames			= [];

	/** @var	array		プライマリキーのリスト */
	protected $_primaryKeys				= [];

	/** @var	array		カラムのデフォルト値のリスト */
	protected $_columnDefaultValues		= [];

	/** @var	array		not null制約の付いているカラムのリスト */
	protected $_notNullColumns			= [];

	/** @var	array		auto incrementが付いているカラムのリスト */
	protected $_autoIncrementColumns	= [];

	protected $_counter					= 0;

	protected $_encodingInternal	= null;
	protected $_encodingFile		= null;
	protected $_quoteMode			= null;
	protected $_readLength			= null;
	protected $_eol					= null;
	protected $_delimiter			= null;
	protected $_enclosure			= null;
	protected $_escape				= null;
	protected $_fopenModeRead		= null;
	protected $_fopenModeWrite		= null;

	public function count ($options = []) {
		return $this->_counter;
	}

	public function readOpen ($reset = true) {
		if ($reset === true) {
			$this->close();
		}

		if ($this->_pointer === null) {
			$this->_counter = 0;
			$this->_pointer = FileSystem::DirtyFopenConvertEncoding($this->_filePath, $this->_encodingInternal, $this->_encodingFile);
		}
	}

	public function writeOpen ($reset = true) {
		if ($reset === true) {
			$this->close();
		}

		if ($this->_pointer === null) {
			$this->_counter = 0;
			$this->_pointer = FileSystem::FileOpen($this->_filePath, $this->_fopenModeWrite);
		}
	}

	public function read ($options = []) {
		if ($this->_pointer === null) {
			$this->readOpen();
		}

		$header_filter	= isset($options['header_filter']) ? $options['header_filter'] : null;
		$row_filter		= isset($options['row_filter']) ? $options['row_filter'] : null;

		$header = null;
		if (is_callable($header_filter) && $this->_counter === 0) {
			$header = $header_filter(fgets($this->_pointer, $this->_readLength), $options);
			if ($header === false) {
				return [];
			}
			$options['header'] = $header;

			if ($row_filter === null) {
				return $header;
			}
		}

		if ($row_filter === null) {
			$this->_counter++;
			$row = fgets($this->_pointer, $this->_readLength);
			return $row;
		}

		while (($row = fgets($this->_pointer, $this->_readLength)) !== false) {
			$this->_counter++;
			if ($row_filter($row, $this->_counter, $options) === false) {
				break;
			}
		}

		return false;
	}

	public function write ($data, $options = []) {
		$this->_pointer !== null ?: $this->writeOpen();
		$this->_encodingFile === $this->_encodingInternal ? fwrite($this->_pointer, $data) : fwrite($this->_pointer, mb_convert_encoding($data, $this->_encodingFile, $this->_encodingInternal));
	}

	public function writeLn ($data, $options = []) {
		$this->_pointer !== null ?: $this->writeOpen();
		$this->_encodingFile === $this->_encodingInternal ? fwrite($this->_pointer, $data) : fwrite($this->_pointer, mb_convert_encoding($data, $this->_encodingFile, $this->_encodingInternal));
		fwrite($this->_pointer, $this->_eol);
	}

	public function close ($options = []) {
		$this->_counter = 0;
		if ($this->_pointer !== null) {
			fclose($this->_pointer);
			$this->_pointer = null;
		}
	}



	//==============================================
	//Static Method
	//==============================================
	/**
	 * ファイル固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
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

	public static function DsnFilter ($dsn) {
		return $dsn;
	}

	public function __construct ($file_name, $password = null, $options = []) {
		$this->_filePath = $file_name;

		$options = static::GetDefaultOptions();

		$this->_encodingInternal	= $options[static::ATTR_ENCODING_INTERNAL];
		$this->_encodingFile		= $options[static::ATTR_ENCODING_FILE];
		$this->_quoteMode			= $options[static::ATTR_QUOTE_MODE];
		$this->_readLength			= $options[static::ATTR_READ_LENGTH];
		$this->_eol					= $options[static::ATTR_EOL];
		$this->_delimiter			= $options[static::ATTR_DELIMITER];
		$this->_enclosure			= $options[static::ATTR_ENCLOSURE];
		$this->_escape				= $options[static::ATTR_ESCAPE] === static::ESCAPE_CHAR_SAME_ENCLOSURE ? $options[static::ATTR_ENCLOSURE] : $options[static::ATTR_ESCAPE];
		$this->_fopenModeRead		= $options[static::ATTR_FOPEN_MODE_READ];
		$this->_fopenModeWrite		= $options[static::ATTR_FOPEN_MODE_WRITE];
	}

// 	public function open ($open_mode = "r") {
// 		$this->_pointer = $context === null ? fopen($this->filePath, $open_mode, $use_include_path) : fopen($this->filePath, $open_mode, $use_include_path, $context);
// 	}

// 	abstract public function read ($options) {
// 		return fgets($this->_pointer);
// 	}

	//==============================================
	//Database Reflection
	//==============================================
	/**
	 * データベースの情報を返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	データベースの情報
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::reflectionDatabase()
	 */
	public function reflectionDatabase ($forced_obtain = false) {
		foreach ($this->getTables($forced_obtain) as $table_name) {
			$this->getTableStatus($table_name, $forced_obtain);
			$this->getColumns($table_name, $forced_obtain);
			$this->getColumnNames($table_name, $forced_obtain);
			$this->getIndexes($table_name, $forced_obtain);
			$this->getPkeys($table_name, $forced_obtain);
		}
		return [
			'tables'		=> $this->_tables,
			'table_status'	=> $this->_tableStatus,
			'columns'		=> $this->_columns,
			'column_names'	=> $this->_columnNames,
			'indexes'		=> $this->_indexes,
			'pkeys'			=> $this->_primaryKeys,
		];
	}

	//==============================================
	//Direct Query Execute
	//==============================================
	/**
	 * クエリを実行します。
	 *
	 * @param	string	$query		クエリ
	 * @param	array	$conditions	検索パラメータ
	 * @param	array	$values		更新データ
	 * @param	array	$options	オプション
	 * @return	\PDOStatement	検索実行後のPDOStatement
	 */
	public function executeQuery ($query, array $conditions = [], array $values = [], array $options = []) {
		//==============================================
		//IN句構築
		//==============================================
		$in_list = [];
		foreach ($conditions as $idx => $condition) {
			$column_value = current($condition);
			if (is_array($column_value)) {
				$in_list[] = implode(', ', array_fill(0, count($column_value), '?'));
			}
		}

		if (!empty($in_list)) {
			$query = vsprintf($query, $in_list);
		}

		//==============================================
		//ステートメント構築
		//==============================================
		$stmt = $this->prepare($query);

		//もしもフェッチモード指定があった場合は設定
		if (isset($options[\PDO::ATTR_DEFAULT_FETCH_MODE])) {
			switch ($options[\PDO::ATTR_DEFAULT_FETCH_MODE]) {
				case \PDO::FETCH_LAZY:
				case \PDO::FETCH_ASSOC:
				case \PDO::FETCH_NAMED:
				case \PDO::FETCH_NUM:
				case \PDO::FETCH_BOTH:
				case \PDO::FETCH_OBJ:
				case \PDO::FETCH_BOUND:
				case \PDO::FETCH_FUNC:
				case \PDO::FETCH_GROUP:
				case \PDO::FETCH_UNIQUE:
				case \PDO::FETCH_KEY_PAIR:
				case \PDO::FETCH_CLASSTYPE:
				case \PDO::FETCH_SERIALIZE:
				case \PDO::FETCH_PROPS_LATE:
				case \PDO::FETCH_ORI_NEXT:
				case \PDO::FETCH_ORI_PRIOR:
				case \PDO::FETCH_ORI_FIRST:
				case \PDO::FETCH_ORI_LAST:
				case \PDO::FETCH_ORI_ABS:
				case \PDO::FETCH_ORI_REL:
					$stmt->setFetchMode($options[\PDO::ATTR_DEFAULT_FETCH_MODE]);
					break;
				case \PDO::FETCH_COLUMN:
					$stmt->setFetchMode($options[\PDO::ATTR_DEFAULT_FETCH_MODE], isset($options['colno']) ? $options['colno'] : 0);
					break;
				case \PDO::FETCH_CLASS:
					$stmt->setFetchMode($options[\PDO::ATTR_DEFAULT_FETCH_MODE], isset($options['classname']) ? $options['classname'] : null, isset($options['ctorargs']) ? $options['ctorargs'] : null);
					break;
				case \PDO::FETCH_INTO:
					$stmt->setFetchMode($options[\PDO::ATTR_DEFAULT_FETCH_MODE], isset($options['object']) ? $options['object'] : null);
					break;
			}
		}

		//==============================================
		//パラメータバインディング開始
		//==============================================
		//位置情報
		$i = 1;
/*
		//テーブル名取得
		$table_name = $options['table_name'] ?? null;

		//カラム情報
		$columns = $this->getColumns($table_name);

		//\PDO::PARAM_*リスト
		$pdo_param_list = static::GetPdoParamList();
*/
		//Update時のValueから先行してバインディングする
		foreach ($values as $column_name => $value) {
//			$stmt->bindValue($i++, $value, $pdo_param_list[$columns[$column_name]['Type']]);
			$stmt->bindValue($i++, $value);
		}

		//WHERE句のバインディング
		foreach ($conditions as $idx => $condition) {
			$column_name = key($condition);
			$column_value = current($condition);

			if (is_array($column_value)) {
				foreach ($column_value as $value) {
					$stmt->bindValue($i++, $value);
				}
			} else {
				$stmt->bindValue($i++, $column_value);
			}
		}

		//==============================================
		//クエリ実行
		//==============================================
		try {
			$stmt->execute();
		} catch (\PDOException $pdo_e) {
			$column_values = [];
			foreach ($conditions as $condition) {
				if (is_array($column_value)) {
					foreach ($column_value as $value) {
						$column_values[] = "'". $value ."'";
					}
				} else {
					$column_values[] = "'". $column_value ."'";
				}
			}

			StaticLog::WriteLog('sql_error', implode(', ', $stmt->errorInfo()));
			StaticLog::WriteLog('sql_error', str_replace(array_fill(0, count($column_values), '?'), $column_values, $query));
			throw $pdo_e;
		}

		//==============================================
		//処理の終了：PDOStatementクラスはTraversableを実装しているため、そのまま返してもforeachで利用できる。
		//==============================================
		return $stmt;
	}

	//==============================================
	//CRUD
	//==============================================
	/**
	 * セーブ
	 *
	 * 指定されたデータにプライマリキーやユニークインデックスが存在する場合はupdate、そうでない場合はinsertを行います。
	 *
	 * @param	string	$table_name	データをセーブする対象のテーブル名
	 * @param	array	$values		セーブするデータ
	 * @param	array	$options	オプション
	 * @throws	CoreException	Flywheel2 CoreException
	 * @return	number|multitype:string
	 */
	public function save ($table_name, array $values = [], $options = []) {
		$tmp_where	= [];
		$tmp_data	= [];
		$tmp_values	= [];

		$unique_indexe_names = $this->getUniqueIndexNames($table_name);
		$column_name_list = $this->getColumnNames($table_name);
		$target_indexes = [];

		//カラムの実在チェックとユニークインデックス存在確認を同時に行う
		if (isset($options['exsist_column_check']) && $options['exsist_column_check']) {
			foreach ($values as $key => $value) {
				if (!isset($column_name_list[$key])) {
					throw CoreException::RaiseSystemError(sprintf('テーブルに存在しないカラムを指定されました。column name:%s', $key));
				}

				if (isset($unique_indexe_names[$key])) {
					foreach ($unique_indexe_names[$key] as $index_name) {
						$target_indexes[$index_name][] = $key;
					}
				}
			}
		} else {
			foreach ($values as $key => $value) {
				if (!isset($column_name_list[$key])) {
					unset($values[$key]);
				}

				if (isset($unique_indexe_names[$key])) {
					foreach ($unique_indexe_names[$key] as $index_name) {
						$target_indexes[$index_name][] = $key;
					}
				}
			}
		}

		$indexes = $this->getIndexes($table_name);
		$match_indexes = [];
		foreach ($target_indexes as $index_name => $column_list) {
			if (!isset($indexes['sorted_column_list'])) {
				foreach ($indexes[$index_name] as $index) {
					$indexes['sorted_column_list'][] = $index['column_name'];
				}
				sort($indexes['sorted_column_list']);
			}
			sort($column_list);
			if ($indexes['sorted_column_list'] === $column_list) {
				$match_indexes = array_combine($column_list, $column_list);
			}
		}

		if (!empty($match_indexes)) {
			$match_indexes = array_combine($match_indexes, $match_indexes);
			foreach ($values as $key => $value) {
				if (isset($match_indexes[$key])) {
					$tmp_where[] = $key;
				} else {
					$tmp_data[] = $key;
					$tmp_values[] = $value;
				}
			}

			foreach ($tmp_where as $key) {
				$tmp_values[] = $values[$key];
			}
		} else {
			$tmp_values = $tmp_where = $tmp_data = array_keys($values);
		}

		$update_flag = false;
		foreach ((array) $match_indexes as $pkey) {
			if (isset($values[$pkey])) {
				$update_flag = true;
				break;
			}
		}

		if ($update_flag) {
			if (isset($options['update_filter']) && is_callable($options['update_filter'])) {
				$tmp_data = $options['update_filter']($tmp_data);
			}

			$query = sprintf(
				'UPDATE %s SET %s = ? WHERE %s = ?',
				$table_name,
				implode(' = ?, ',		$tmp_data),
				implode(' = ? AND ',	$tmp_where)
			);

			try {
				$stmt = $this->prepare($query);
				$i = 1;
				foreach ($tmp_values as $value) {
					$stmt->bindValue($i++, $value);
				}
				if (!$stmt->execute()) {
					//
				}
				$ret = $stmt->rowCount();
			} catch (\PDOException $pdo_e) {
				StaticLog::WriteLog('sql_error', implode(', ', $this->errorInfo()));

				StaticLog::WriteLog('sql_error', serialize($tmp_values));
				throw $pdo_e;
			}

			return $ret;
		}

		return $this->create($table_name, [$values], $options);
	}

	/**
	 * データの生成を行います。
	 *
	 * @param	string	$table_name	生成先テーブル名
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public function create ($table_name, array $values = [], array $options = []) {
		if (isset($options['create_filter']) && is_callable($options['create_filter'])) {
			$values = $options['create_filter']($values);
		}

		$column_name_list = array_keys($values[0]);
		$query = sprintf(
 			'INSERT INTO %s (%s%s%s) VALUES (%s)',
 			$table_name,
			static::IDENTIFIER,
 			implode(static::IDENTIFIER.', '.static::IDENTIFIER, $column_name_list),
			static::IDENTIFIER,
 			implode(', ', array_fill(0, count($column_name_list), '?'))
 		);

 		$ret = [];
 		try {
			$stmt = $this->prepare($query);
			foreach ($values as $value) {
				$i = 1;
				foreach ($value as $column_name => $column_value) {
					$stmt->bindValue($i++, $column_value);
				}
				if (!$stmt->execute()) {
					//
				}
				$ret[] = $this->lastInsertId();
			}
		} catch (\PDOException $pdo_e) {
			StaticLog::WriteLog('sql_error', implode(', ', $this->errorInfo()));

			StaticLog::WriteLog('sql_error', serialize($values));

			throw $pdo_e;
		}

		return $ret;
	}

	/**
	 * データの更新を行います。
	 */
	public function update () {
	}

	/**
	 * データの削除を行います。
	 */
	public function delete () {
	}

	//==============================================
	//Database Reflection
	//==============================================
	abstract protected function _updateTables ();
	abstract protected function _updateTableStatus ($table_name);
	abstract protected function _updateColumns ($table_name);
	abstract protected function _updateIndexes ($table_name);
	abstract protected function _updatePkeys ($table_name);
	abstract protected function _updateColumnDefaultValues ($table_name);
	abstract protected function _updateNotNullColumns ($table_name);
	abstract protected function _updateAutoIncrementColumns ($table_name);

	/**
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	テータベースに存在するテーブルのリスト。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	public function getTables ($forced_obtain = false) {
		if ($forced_obtain || $this->_tables === null) {
			$this->_updateTables();
		}
		return $this->_tables;
	}

	/**
	 * テーブルのステータスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのステータス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTableStatus()
	 */
	public function getTableStatus ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_tableStatus[$table_name])) {
			$this->_updateTableStatus($table_name);
		}
		return $this->_tableStatus;
	}

	/**
	 * テーブルのカラム情報を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム情報。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getColumns ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_columns[$table_name])) {
			$this->_updateColumns($table_name);
		}
		return $this->_columns[$table_name];
	}

	/**
	 * テーブルのカラム名の一覧を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム名の一覧。
	 */
	public function getColumnNames ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_columnNames[$table_name])) {
			$column_names = array_keys($this->getColumns($table_name, $forced_obtain));
			$this->_columnNames[$table_name] = array_combine($column_names, $column_names);
		}
		return $this->_columnNames[$table_name];
	}

	/**
	 * テーブルのインデックスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getIndexes ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_indexes[$table_name])) {
			$this->_updateIndexes($table_name);
		}
		return $this->_indexes[$table_name];
	}

	/**
	 * テーブルのユニークインデックス名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getUniqueIndexNames ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_uniqueIndexNames[$table_name])) {
			$this->_updateUniqueIndexNames($table_name);
		}
		return $this->_uniqueIndexNames[$table_name];
	}

	/**
	 * テーブルのプライマリキーを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getPkeys ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !Arrays::KeyExists($this->_primaryKeys, $table_name)) {
			$this->_updatePkeys($table_name);
		}
		return $this->_primaryKeys[$table_name];
	}

	/**
	 * デフォルト値を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getColumnDefaultValues ($table_name, $forced_obtain = false) {
		if ($forced_obtain || !Arrays::KeyExists($this->_columnDefaultValues, $table_name)) {
			$this->_updateColumnDefaultValues($table_name);
		}
		return $this->_columnDefaultValues[$table_name];
	}

	/**
	 * NULL値を許容しないカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getNotNullColumns ($table_name, $forced_obtain = false) {
		if ($forced_obtain || !Arrays::KeyExists($this->_notNullColumns, $table_name)) {
			$this->_updateNotNullColumns($table_name);
		}
		return $this->_notNullColumns[$table_name];
	}

	/**
	 * auto_incrementを持つカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	public function getAutoIncrementColumns ($table_name, $forced_obtain = false) {
		if ($forced_obtain || !Arrays::KeyExists($this->_autoIncrementColumns, $table_name)) {
			$this->_updateAutoIncrementColumns($table_name);
		}
		return $this->_autoIncrementColumns[$table_name];
	}

	/**
	 * テーブルのカラムに紐づくインデックス名を更新します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getColumns()
	 */
	protected function _updateUniqueIndexNames ($table_name, $forced_obtain = false) {
		if (!isset(array_flip($this->getTables($forced_obtain))[$table_name])) {
			throw CoreException::RaiseSystemError('存在しないテーブルを指定されました。table_name:%s', [$table_name]);
		}
		if ($forced_obtain || !isset($this->_indexes[$table_name])) {
			$this->_updateIndexes($table_name);
		}

		$tmp_unique_index_names = [];
		foreach ($this->_indexes[$table_name] as $index_name => $rows) {
			if ($index_name == 'PRIMARY') {
				foreach ($rows as $row) {
					$tmp_unique_index_names[$row['column_name']][$index_name] = $index_name;
				}
				continue;
			}
			foreach ($rows as $row) {
				if (!$row['unique']) {
					continue;
				}
				$tmp_unique_index_names[$row['column_name']][$index_name] = $index_name;
			}
		}
		$this->_uniqueIndexNames[$table_name] = $tmp_unique_index_names;
	}

	public function addIdentifier ($name) {
		return static::IDENTIFIER . $name. static::IDENTIFIER;
	}

	public function getIdentifier () {
		return static::IDENTIFIER;
	}
}
