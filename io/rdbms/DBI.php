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

namespace ickx\fw2\io\rdbms;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\IniFile;
use ickx\fw2\security\validators\Validator;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\vartype\strings\Strings;

/**
 * DataBase Interface
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class DBI {
	/** @var	string	デフォルトのパスワードハッシュアルゴリズム */
	const PASSWORD_HASH_ALGORITHM	= 'sha256';

	/** @var	string	デフォルトのコネクション名 */
	const CONNECTION_NAME_DEFAULT		= 'default';

	/** @var	string	プライオリティ名：マスタ */
	const PRIORITY_MASTER				= 'master';

	/** @var	string	プライオリティ名：スレーブ */
	const PRIORITY_SLAVE				= 'slave';

	/** @var	int		デフォルトのスタックナンバー */
	const DEFAULT_STACK_NO				= 0;

	/** @var	string	ドライバクラスサフィックス */
	const DRIVER_CLASS_NAME_SAFIX		= 'Driver';

	/** @var	string	DBドライバー名：Mysql */
	const DRIVER_NAME_MYSQL				= 'mysql';

	/** @var	string	DBドライバー名：PostgreSQL */
	const DRIVER_NAME_PGSQL				= 'pgsql';

	/** @var	string	DBドライバー名：sqlite */
	const DRIVER_NAME_SQLITE			= 'sqlite';

	/** @var	string	SQLiteでのオンメモリーモード用フラグ：:memory: */
	const SQLITE_ON_MEMORY_MODE			= ':memory:';

	/**	@var	string	データベースで利用するデフォルトキャラクタセット：UTF-8 */
	const ENCODEING_DEFAULT = 'utf8';

	/** @staticvar	bool	トランザクション展開中フラグ */
	protected static $_inTransaction	= false;

	/** @staticvar	array	DSNリスト */
	protected static $_dsnList = [];

	/** @staticvar	array	コネクションリスト */
	protected static $_connectionList = [];

	/** @staticvar	array	ini設定用バリデートルール */
	protected static $_enableIniRuleList = [
		'name'		=> [],
		'driver'	=> [
			['require', 'throw_exception' => true],
		],
		'username'	=> [
			['require', 'throw_exception' => true],
		],
		'password'	=> [
			['require', 'throw_exception' => true],
		],
		'host'		=> [
			['require', 'throw_exception' => true],
		],
		'port'		=> [
			['require', 'throw_exception' => true],
		],
		'dbname'	=> [
			['require', 'throw_exception' => true],
		],
		'file'		=> [],
		'charset'	=> [],
		'options'	=> [],
		'schemas'	=> [],
	];

	/**
	 * 安全なパスワードハッシュを構築します。
	 *
	 * @param	string	$password	パスワード
	 * @param	string	$key		キー
	 * @param	string	$algorithm	ハッシュアルゴリズム
	 * @return	string	安全なパスワードハッシュ
	 */
	public static function CreateSecurePassword ($password, $key, $algorithm = self::PASSWORD_HASH_ALGORITHM) {
		return hash_hmac($algorithm, $password, $key);
	}

	//==============================================
	//Connection
	//==============================================
	/**
	 * 接続設定を登録します。
	 *
	 * @param	mixed	$dsn		DSN 設定配列または設定ファイルへのフルパス
	 * @param	array	$options	オプション
	 */
	public static function Connect ($dsn, $options = []) {
		$dsn = is_array($dsn) ? $dsn : IniFile::GetConfig($dsn, array_keys(static::$_enableIniRuleList), $options);

		if (isset($dsn['password']) && isset($options['key'])) {
			$algorithm = isset($options['algorithm']) ? $options['algorithm'] : self::PASSWORD_HASH_ALGORITHM;
			$dsn['password'] = static::CreateSecurePassword($dsn['password'], $options['key'], $algorithm);
		}

		$dsn_options = [];
		foreach (Arrays::AdjustValue($dsn, 'options', []) as $option) {
			$dsn_options[(int)$option['name']] = (int)$option['value'];
		}
		$dsn['options'] = $dsn_options;

		$dsn['schemas'] = Arrays::AdjustValue($dsn, 'schemas', []);

		static::$_dsnList[static::AdjustConnectionName($options, $dsn)][static::AdjustPriority($options)][static::AdjustStackNumber($options)] = $dsn;
	}

	/**
	 * DSNを取得します。
	 * @param	array	$options	オプション
	 * @return	array	DSN設定
	 */
	public static function GetDsn ($options) {
		return static::$_dsnList[static::AdjustConnectionName($options)][static::AdjustPriority($options)][static::AdjustStackNumber($options)];
	}

	/**
	 * コネクションを取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	￥PDO	PDOインスタンス
	 */
	public static function GetConnection ($options) {
		$connection_name = static::AdjustConnectionName($options);
		$priority = static::AdjustPriority($options);
		$number = static::AdjustStackNumber($options);

		if (!isset(static::$_connectionList[$connection_name][$priority][$number])) {
			static::_Connect($options);
		}
		return static::$_connectionList[$connection_name][$priority][$number];
	}

	/**
	 * コネクションを確立します。
	 *
	 * @param	array	$options	オプション
	 * @return	￥PDO	PDOインスタンス
	 */
	protected static function _Connect ($options) {
		$connection_name = static::AdjustConnectionName($options);
		$priority = static::AdjustPriority($options);
		$number = static::AdjustStackNumber($options);

		if (!isset(static::$_dsnList[$connection_name][$priority][$number])) {
			throw CoreException::RaiseSystemError('未定義のDB設定を用いて接続しようとしています。connection_name:%s, priority:%s, number:%s', [$connection_name, $priority, $number]);
		}

		$dsn = static::$_dsnList[$connection_name][$priority][$number];
		$driver_class_name = sprintf('%s\drivers\%s%s', __NAMESPACE__, Strings::ToUpperCamelCase($dsn['driver']), static::DRIVER_CLASS_NAME_SAFIX);
		static::$_connectionList[$connection_name][$priority][$number] = $driver_class_name::Connect($dsn);
	}

	/**
	 * 強制的にマスター系に接続します。
	 */
	protected static function _ForceMasterConnect () {
		foreach (static::$_dsnList as $connection_name => $dsn) {
			if (isset($dsn['master'])) {
				foreach ($dsn['master'] as $idx => $master_dsn) {
					static::GetConnection([$connection_name, 'master', $idx]);
				}
			}
		}
	}

	//==============================================
	//Query
	//==============================================
	/**
	 * クエリを実行します。
	 *
	 * executeQueryメソッドの実体はdrivers/abstracts/RdbmsDriverAbstract.phpまたはdrivers/にある各RDBMSドライバが持ちます。
	 *
	 * @param	mixed	$query		クエリ
	 * @param	array	$conditions	検索パラメータ
	 * @param	array	$values		更新データ
	 * @param	array	$options	DB接続オプション
	 * @return	\PDOStatement	検索実行後のPDOStatement
	 */
	public static function ExecuteQuery ($query, array $conditions = [], array $values = [], array $options = []) {
		return static::GetConnection($options)->executeQuery($query, $conditions, $values, $options);
	}


	public static function Execute ($query, array $options = []) {
		return static::GetConnection($options)->exec($query);
	}

	//==============================================
	//CRUD
	//==============================================
	/**
	 * データの生成を行います。
	 *
	 * @param	string	$table_name	生成先テーブル名
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function Create ($table_name, array $values = [], array $options = []) {
		return static::GetConnection($options)->create($table_name, $values, $options);
	}

	/**
	 * データのセーブを行います。
	 *
	 * @param	string	$table_name	生成先テーブル名
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function Save ($table_name, array $values = [], array $options = []) {
		return static::GetConnection($options)->save($table_name, $values, $options);
	}

	//==============================================
	//Transaction
	//==============================================
	/**
	 * トランザクションを開始します。
	 *
	 * @return	bool	成功した場合はtrue, 失敗した場合はfalse
	 */
	public static function Begin () {
		if (static::InTransaction()) {
			throw CoreException::RaiseSystemError('既にトランザクションが開始されています。');
		}

		static::_ForceMasterConnect();

		$ret = [];
		foreach (static::$_connectionList as $connection_name => $connection) {
			foreach ($connection[static::PRIORITY_MASTER] as $number => $node) {
				$ret[$connection_name][static::PRIORITY_MASTER][$number] = $node->beginTransaction();
			}
		}
		static::$_inTransaction = true;
		return $ret;
	}

	/**
	 * トランザクションをロールバックします。
	 *
	 * @return	bool	成功した場合はtrue, 失敗した場合はfalse
	 */
	public static function RollBack () {
		if (static::InTransaction() === false) {
			throw CoreException::RaiseSystemError('トランザクションが開始されていません。');
		}
		$ret = [];
		foreach (static::$_connectionList as $connection_name => $connection) {
			foreach ($connection[static::PRIORITY_MASTER] as $number => $node) {
				$ret[$connection_name][static::PRIORITY_MASTER][$number] = $node->rollBack();
			}
		}
		static::$_inTransaction = false;
		return $ret;
	}

	/**
	 * トランザクションをコミットします。
	 *
	 * @return	bool	成功した場合はtrue, 失敗した場合はfalse
	 */
	public static function Commit () {
		if (static::InTransaction() === false) {
			throw CoreException::RaiseSystemError('トランザクションが開始されていません。');
		}
		$ret = [];
		foreach (static::$_connectionList as $connection_name => $connection) {
			foreach ($connection[static::PRIORITY_MASTER] as $number => $node) {
				$ret[$connection_name][static::PRIORITY_MASTER][$number] = $node->commit();
			}
		}
		static::$_inTransaction = false;
		return $ret;
	}

	/**
	 * 現在トランザクションが実行中かどうかを返します。
	 *
	 * @return	bool	トランザクション実行中の場合はtrue, そうでない場合はfalse
	 */
	public static function InTransaction () {
		return static::$_inTransaction;
	}

	//==============================================
	//Utility
	//==============================================
	public static function CreateTable ($table_name, array $options = []) {
		return static::GetConnection($options)->createTable($table_name, $options);
	}

	public static function CreateIndex ($table_name, array $options = []) {
		return static::GetConnection($options)->createIndex($table_name, $options);
	}

	public static function DropTable ($table_name, array $options = []) {
		return static::GetConnection($options)->dropTable($table_name, $options);
	}

	/**
	 * オプションからコネクション名を取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	string	コネクション名
	 */
	public static function AdjustConnectionName ($options, $dsn = []) {
		return Arrays::AdjustValue($options, ['connection_name', 0], Arrays::AdjustValue($dsn, 'dbname', static::CONNECTION_NAME_DEFAULT));
	}

	/**
	 * オプションからプライオリティ名を取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	string	プライオリティ名
	 */
	public static function AdjustPriority ($options) {
		return Arrays::AdjustValue($options, ['priority', 1], DBI::PRIORITY_MASTER);
	}

	/**
	 * オプションからスタックナンバーを取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	string	スタックナンバー
	 */
	public static function AdjustStackNumber ($options) {
		return Arrays::AdjustValue($options, ['number', 2], DBI::DEFAULT_STACK_NO);
	}

	public static function UpdateTableInfo ($target_table_list = [], $options = []) {
		return static::GetConnection($options)->updateTableInfo($target_table_list);
	}

	/**
	 * データベースの情報を返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	データベースの情報
	 */
	public static function ReflectionDatabase(array $options, $forced_obtain = false) {
		return static::GetConnection($options)->reflectionDatabase();
	}

	/**
	 * データベースに存在するテーブルを全て返します。
	 *
	 * @param	bool	強制再取得フラグ
	 * @return	array	テータベースに存在するテーブルのリスト。
	 */
	public static function GetTables(array $options, $forced_obtain = false) {
		return static::GetConnection($options)->getTables();
	}

	/**
	 * テーブルのステータスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのステータス。
	 */
	public static function GetTableStatus(array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getTableStatus($table_name, $forced_obtain);
	}

	/**
	 * テーブルのカラム情報を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのカラム情報。
	 */
	public static function GetColumns(array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getColumns($table_name, $forced_obtain);
	}

	/**
	 * テーブルのインデックスを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのインデックス。
	 */
	public static function GetIndexes (array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getIndexes($table_name, $forced_obtain);
	}

	/**
	 * テーブルのプライマリキーを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public static function GetPkeys (array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getPkeys($table_name, $forced_obtain);
	}

	/**
	 * テーブルのデフォルト値を返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public static function GetColumnDefaultValues (array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getColumnDefaultValues($table_name, $forced_obtain);
	}

	/**
	 * テーブルのNULL値を許容しないカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public static function GetNotNullColumns (array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getNotNullColumns($table_name, $forced_obtain);
	}

	/**
	 * auto_incrementを持つカラム名のリストを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public static function GetAutoIncrementColumns (array $options, $table_name, $forced_obtain = false) {
		return static::GetConnection($options)->getAutoIncrementColumns($table_name, $forced_obtain);
	}

	/**
	 * テーブルのインサート用の行を作成し返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	array	テーブルのプライマリキー。
	 */
	public static function MakeInsertRow (array $options, $table_name, array $merge_row = [], $forced_obtain = false) {
		return static::GetConnection($options)->makeInsertRow($table_name, $merge_row, $forced_obtain);
	}

	public static function GetIdentifier (array $options = []) {
		return static::GetConnection($options)->getIdentifier();
	}

	/**
	 * テーブルが存在するかどうかを返します。
	 *
	 * @param	string	テーブル名
	 * @param	bool	強制再取得フラグ
	 * @return	bool	テーブルが既に存在する場合はtrue, そうでない場合はfalse
	 */
	public static function ExistTable (array $options, $table_name, $forced_obtain = false) {
		$tables = static::GetConnection($options)->getTables($forced_obtain);
		return isset($tables[$table_name]);
	}

	public static function RenameTable (array $options, $new_table_name, $table_name) {
		return static::GetConnection($options)->renameTable($new_table_name, $table_name);
	}

	public static function SetPrepare (array $options, $deta_filed, $where = null) {
		static::GetConnection($options)->setPrepare($options, $deta_filed, $where);
	}

	public static function ReleasePrepare (array $options) {
		static::GetConnection($options)->releasePrepare($options);
	}

	public static function Prepare (array $options, $query) {
		return static::GetConnection($options)->prepare($query, Arrays::AdjustArray($options, 'driver_options', []));
	}
}
