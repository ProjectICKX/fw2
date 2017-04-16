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

namespace ickx\fw2\io\rdbms\drivers\abstracts;

use ickx\fw2\core\log\StaticLog;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\core\exception\CoreException;

/**
 * Flywheel2 RDBMSDriver Abstract Class
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
abstract class RdbmsDriverAbstract extends \PDO implements \ickx\fw2\io\rdbms\drivers\interfaces\IRdbmsDriver {
	//==============================================
	//Property
	//==============================================
	/** @var	array	テーブルのリスト */
	protected $_tables					= null;

	/** @var	array	テーブル設定のリスト */
	protected $_tableStatus				= [];

	/** @var	array	カラムのリスト */
	protected $_columns					= [];

	/** @var	array	カラム名のリスト */
	protected $_columnNames				= [];

	/** @var	array	インデックスのリスト */
	protected $_indexes					= [];

	/** @var	array	カラムに紐づくユニークインデックス名のリスト */
	protected $_uniqueIndexNames			= [];

	/** @var	array	プライマリキーのリスト */
	protected $_primaryKeys				= [];

	/** @var	array	カラムのデフォルト値のリスト */
	protected $_columnDefaultValues		= [];

	/** @var	not null制約の付いているカラムのリスト */
	protected $_notNullColumns			= [];

	/** @var	auto incrementが付いているカラムのリスト */
	protected $_autoIncrementColumns	= [];

	protected $_prepareList				= [];

	//==============================================
	//Static Method
	//==============================================
	/**
	 * データベース固有のデフォルトオプションを返します。
	 *
	 * @return	array	デフォルトオプション
	 * @see ickx\fw2\io\rdbms\drivers\interfaces.IRdbmsDriver::getTables()
	 */
	public static function GetDefaultOptions () {
		return [];
	}

	public static function GetValidateRuleList () {
//@TODO バージョンごとのキャラクタセットに対応
		$enable_encoding_list = static::GetEnableCharsetList();

		return [
			[
				'raise_exception'	=> true,
				'use_current_data'	=> true,
				['any_key_exists', ['host', 'unix_socket'], 'message' => '接続情報が設定されていません。{:key_set:0}のいずれかを設定してください。'],
			],
			[
				'raise_exception'	=> true,
				'use_current_data'	=> true,
				['not_any_key_exists', ['host', 'unix_socket'], 'message' => '接続設定が重複しています。{:key_set:0}のいずれかを削除してください。'],
			],
				'host'	=> [
				'raise_exception'	=> true,
				['not_string_empty'],
				['host'],
			],
			'port'	=> [
				'raise_exception'	=> true,
				'premise'			=> 'host',
				['port'],
			],
			'unix_socket' => [
				'raise_exception'	=> true,
				['file_exists'],
				['is_file'],
				['readable'],
			],
			[
				'raise_exception'	=> true,
				'use_current_data'	=> true,
				['any_key_exists', ['encoding', 'charset'], 'message' => 'キャラクタセットが指定されていません。charsetを設定してください。'],
			],
			'encoding'	=> [
				'raise_exception'	=> true,
				['not_string_empty'],
				['in', $enable_encoding_list, 'message' => '未定義のキャラクタセットが指定されました。'],
			],
			'charset'	=> [
				'raise_exception'	=> true,
				['not_string_empty'],
				['in', $enable_encoding_list, 'message' => '未定義のキャラクタセットが指定されました。'],
			],
		];
	}

	public static function GetDsnFilterTargetList () {
		return [
			'username',
			'password',
			'options',
			'schemas',
		];
	}

	public function createTable ($table_name, $options = []) {
		return $this->executeQuery($this->createTableSchema($table_name, $options['column_seed']), [], [], $options);
	}

	public function createIndex ($table_name, $options = []) {
		$ret = [];
		foreach ($this->createIndexSchema($table_name, $options['index_seed']) as $index_schema) {
			$ret[] = $this->executeQuery($index_schema, [], [], $options);
		}
		return $ret;
	}

	public function dropTable ($table_name, $options = []) {
		return $this->executeQuery(sprintf('DROP TABLE %s;', $table_name), [], [], $options);
	}

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
			$column_value = current((array) $condition);
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
		$table_name = Arrays::AdjustValue($options, 'table_name');

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

		//WHERE句以降のバインディング
		foreach ($conditions as $idx => $condition) {
			$condition = (array) $condition;
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
//@TODO mode setting
			$log_flag = false;
			if ($log_flag) {
				$execute_time = microtime(true);
			}

			$stmt->execute();
//@TODO mode setting
			if ($log_flag) {
				$execute_end_time = microtime(true);

				$parsed_execute_time = explode('.', $execute_time);

				$debug_dump_params = '';
				if ($log_flag) {
					ob_start();
					$stmt->debugDumpParams();
					$debug_dump_params = ob_get_clean();
				}

				$query_string = $stmt->queryString;

				$message = sprintf('[%s.%-4d] execute_time:%s query_string:%s dump:%s', date('Y-m-d H:i:s', $parsed_execute_time[0]), $parsed_execute_time[1], $execute_end_time - $execute_time, $query_string, $debug_dump_params);
//				StaticLog::WriteLog('sql_log', $message);
				print $message;
			}
		} catch (\PDOException $pdo_e) {
			$execute_end_time = microtime(true);

			$parsed_execute_time = explode('.', $execute_time);

			$error_info = $stmt->errorInfo();

//@TODO mode setting
			$debug_dump_params = '';
			if ($log_flag) {
				ob_start();
				$stmt->debugDumpParams();
				$debug_dump_params = ob_get_clean();
			}
			$query_string = $stmt->queryString;

			$message = sprintf(
					'[%s.%-4d] execute_time:%s SQLSTATE:%s code:%s message:%s query_string:%s dump:%s',
					date('Y-m-d H:i:s', $parsed_execute_time[0]),
					$parsed_execute_time[1],
					$execute_end_time - $execute_time,
					$error_info[0],
					$error_info[1],
					$error_info[2],
					$query_string,
					$debug_dump_params
			);

			StaticLog::WriteLog('sql_error', $message);

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
					CoreException::RaiseSystemError(sprintf('テーブルに存在しないカラムを指定されました。column name:%s', $key));
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

		$update_flag = isset($options['force_update']) ? $options['force_update'] : false;
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

	public function buildCreatePlaceholder ($table_name, array $values, $options) {
		//カラムの実在チェック
		$column_name_list = $this->getColumnNames($table_name);
		foreach ($values as $key => $value) {
			if (!isset($column_name_list[$key])) {
				CoreException::RaiseSystemError(sprintf('テーブルに存在しないカラムを指定されました。column name:%s', $key));
			}
		}

		//not null項目抜けチェック
		$auto_increment_column_list = $this->getAutoIncrementColumns($table_name);
		foreach ($this->getNotNullColumns($table_name) as $not_null_column_name) {
// 			if (isset($value[$not_null_column_name]) && $value[$not_null_column_name] === null) {
// 				CoreException::RaiseSystemError(sprintf('not nullカラムに対してnullが指定されています。column name:%s', $not_null_column_name));
// 			}
			if (!isset($auto_increment_column_list[$not_null_column_name])) {
				CoreException::RaiseSystemError(sprintf('not nullカラムに対する値が指定されていません。column name:%s', $not_null_column_name));
			}
		}

		$column_name_list = array_keys($values[0]);
		return sprintf(
			'INSERT INTO %s (%s%s%s) VALUES (%s)',
			$table_name,
			static::IDENTIFIER,
			implode(static::IDENTIFIER.', '.static::IDENTIFIER, $column_name_list),
			static::IDENTIFIER,
			implode(', ', array_fill(0, count($column_name_list), '?'))
		);
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

		$columns = $this->getColumns($table_name);

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

//@TODO mode setting
 		$log_flag = false;
		$execute_time = microtime(true);
 		try {
			$stmt = $this->prepare($query);
			foreach ($values as $value) {
				$i = 1;
				foreach ($value as $column_name => $column_value) {
					$stmt->bindValue($i++, $column_value, $columns[$column_name]['pdo_param']);
				}
				if (!$stmt->execute()) {
					//@TODO mode setting
					if ($log_flag) {
						$execute_end_time = microtime(true);

						$parsed_execute_time = explode('.', $execute_time);

						$debug_dump_params = '';
						if ($log_flag) {
							ob_start();
							$stmt->debugDumpParams();
							$debug_dump_params = ob_get_clean();
						}

						$query_string = $stmt->queryString;

						$message = sprintf('[%s.%-4d] execute_time:%s query_string:%s dump:%s', date('Y-m-d H:i:s', $parsed_execute_time[0]), $parsed_execute_time[1], $execute_end_time - $execute_time, $query_string, $debug_dump_params);
						//				StaticLog::WriteLog('sql_log', $message);
						print $message;
					}
				}
				$ret[] = $this->lastInsertId();
			}
		} catch (\PDOException $pdo_e) {
			$execute_end_time = microtime(true);

			$parsed_execute_time = explode('.', $execute_time);

			$error_info = $stmt->errorInfo();

			//@TODO mode setting
			$debug_dump_params = '';
			if ($log_flag) {
				ob_start();
				$stmt->debugDumpParams();
				$debug_dump_params = ob_get_clean();
			}
			$query_string = $stmt->queryString;

			$message = sprintf(
					'[%s.%-4d] execute_time:%s SQLSTATE:%s code:%s message:%s query_string:%s dump:%s',
					date('Y-m-d H:i:s', $parsed_execute_time[0]),
					$parsed_execute_time[1],
					$execute_end_time - $execute_time,
					$error_info[0],
					$error_info[1],
					$error_info[2],
					$query_string,
					$debug_dump_params
			);
print $message;
			StaticLog::WriteLog('sql_error', $message);

			throw $pdo_e;
		}

		return $ret;
	}

	/**
	 * データの検索を行います。
	 */
	public function read () {
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

	public function updateTableInfo ($target_table_list = []) {
		$target_table_list = (array) $target_table_list ?: $this->getTables(true);
		foreach ($target_table_list as $table_name) {
			$this->_updateTableStatus ($table_name);
			$this->_updateColumns ($table_name);
			$this->_updateIndexes ($table_name);
			$this->_updatePkeys ($table_name);
			$this->_updateColumnDefaultValues ($table_name);
			$this->_updateNotNullColumns ($table_name);
			$this->_updateAutoIncrementColumns ($table_name);
		}
	}

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

	public function renameTable ($new_table_name, $table_name) {
		return $this->exec(sprintf('ALTER TABLE %s RENAME TO %s', $table_name, $new_table_name));
}

	public static function SetCreatePrepare (array $options, $deta_filed, $where = null) {
		$prepare_name = Arrays::AdjustValue($options, 'prepare_name', ':default::'. microtime(true));
		buildCreatePlaceholder();
		$this->_prepareList[$prepare_name] = $this->prepare($query);
		return $prepare_name;
	}

	public static function ReleasePrepare (array $options) {
		$prepare_key_list = array_keys($this->_prepareList);
		$prepare_name = Arrays::AdjustValue($options, 'prepare_name', array_pop($prepare_name_list));
		if (isset($this->_prepareList[$prepare_name])) {
			unset($this->_prepareList[$prepare_name]);
		}
	}
}