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
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mvc\app\models\traits;

/**
 * モデルアクセス特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ModelTrait {
	/** @staticvar	array	コネクション名保持配列 */
	protected static $_connectionNameList	= [];

	/** @staticvar	array	カラム名保持配列 */
	protected static $_nameList 			= [];

	protected static $columnNameList;

	protected static $_useSnakeCase			= true;

	//==============================================
	//ISUD
	//==============================================
	/**
	 * クエリを実行します。
	 *
	 * @param	mixed	$query		クエリ
	 * @param	array	$conditions	検索パラメータ
	 * @param	array	$values		更新データ
	 * @param	array	$options	DB接続オプション
	 * @return	\PDOStatement	検索実行後のPDOStatement
	 */
	public static function ExecuteQuery ($query, array $conditions = [], array $values = [], array $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::ExecuteQuery($query, $conditions, $values, $options);
	}

	//==============================================
	//SAVE
	//==============================================
	/**
	 * データのセーブを行います。
	 *
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function Save ($values, $options = []) {
		if (!static::InTransaction()) {
			throw CoreException::RaiseSystemError('トランザクションが展開されていません。');
		}
		$options += $options + static::GetDefaultOptions();
		return DBI::Save(static::GetName(), static::DefaultSaveFilter($values), $options);
	}

	/**
	 * データセーブ時に行うデフォルトフィルタを定義します。
	 *
	 * @param	array	$values	セーブする値
	 * @return	array	フィルタ後の値
	 */
	public static function DefaultSaveFilter ($values) {
		return $values;
	}

	//==============================================
	//CRUD
	//==============================================
	/**
	 * データの生成を行います。
	 *
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function Create ($values = [], $options = []) {
		return static::MultipleCreate([$values], $options);
	}

	/**
	 * データの生成を複数行一括で行います。
	 *
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function MultipleCreate ($values = [], $options = []) {
		if (!static::InTransaction()) {
			throw CoreException::RaiseSystemError('トランザクションが展開されていません。');
		}
		//@TODO EventListにする
		if (method_exists(static::class, 'DefaultFilter')) {
			$default_filter = [static::class, 'DefaultFilter'];
			foreach ($values as $idx => $row) {
				$values[$idx] = $default_filter($row);
			}
		}
		if (method_exists(static::class, 'CreateFilter')) {
			$create_filter = [static::class, 'CreateFilter'];
			foreach ($values as $idx => $row) {
				$values[$idx] = $create_filter($row);
			}
		}
		$options += $options + static::GetDefaultOptions();
		return DBI::Create(static::GetName(), $values, $options);
	}

	/**
	 * データの削除を行います。
	 *
	 * @param	array	$values		値
	 * @param	array	$options	オプション
	 */
	public static function Delete ($values, $options = []) {
		if (!static::InTransaction()) {
			throw CoreException::RaiseSystemError('トランザクションが展開されていません。');
		}
		$options += $options + static::GetDefaultOptions();
		return DBI::Delete(static::GetName(), $values, $options);
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
		return DBI::Begin();
	}

	/**
	 * トランザクションをロールバックします。
	 *
	 * @return	bool	成功した場合はtrue, 失敗した場合はfalse
	 */
	public static function RollBack () {
		return DBI::RollBack();
	}

	/**
	 * トランザクションをコミットします。
	 *
	 * @return	bool	成功した場合はtrue, 失敗した場合はfalse
	 */
	public static function Commit () {
		return DBI::Commit();
	}

	/**
	 * 現在トランザクションが実行中かどうかを返します。
	 *
	 * @return	bool	トランザクション実行中の場合はtrue, そうでない場合はfalse
	 */
	public static function InTransaction () {
		return DBI::InTransaction();
	}

	//==============================================
	//Utility
	//==============================================
	/**
	 * \PDOStatementインスタンスを元に値を加工します。
	 *
	 * @param	\PDOStatement	$values		\PDOStatementインスタンス
	 * @param	mixed			$keys		加工方法
	 * 								bool true	：\PDOStatement->fetchAll();
	 * 								bool false	：\PDOStatement
	 * 								null		：pkeyをキーとした配列
	 * 								array		：arrayで指定されたキーの値で階層化されたデータ
	 * 									ex)
	 * 										$value = [
	 * 											['id'	=> 1, 'group'	=> 'a'],
	 * 											['id'	=> 2, 'group'	=> 'b'],
	 * 											['id'	=> 3, 'group'	=> 'c'],
	 * 											['id'	=> 4, 'group'	=> 'a'],
	 * 										]:
	 * 										$ret = ModelTrait::Alignment($value, ['group', 'id']);
	 *										$retは次の配列となる
	 * 										$ret = [
	 * 											'a'	=> [
	 * 												1	=> ['id'	=> 1, 'group'	=> 'a'],
	 * 												4	=> ['id'	=> 4, 'group'	=> 'a'],
	 * 											],
	 * 											'b'	=> [
	 * 												2	=> ['id'	=> 2, 'group'	=> 'b'],
	 * 											],
	 * 											'c'	=> [
	 * 												3	=> ['id'	=> 3, 'group'	=> 'c'],
	 * 											],
	 * 										]:
	 * @param	array 			$options	オプション
	 * @return	array			加工後の配列
	 */
	public static function Alignment ($values, $keys = null, array $options = []) {
		if ($keys === false) {
			return $values;
		}
		if ($keys === true) {
			return $values->fetchAll();
		}
		$options += $options + static::GetDefaultOptions();
		return Arrays::MultiColumn($values, $keys ?: static::GetDefaultIndex(false, $options) ?: null);
	}

	/**
	 * デフォルトインデックスを返します。
	 *
	 * @param	string	$table_name		インデックスを取得するテーブル名
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	mixed	あればpkeyの配列、なければ最初にマッチしたインデックス、そもそもインデックスが無ければnull
	 */
	public static function GetDefaultIndex ($forced_obtain = false, array $options = []) {
		$options += $options + static::GetDefaultOptions();
		$table_name = static::GetName();

		$pkeys = DBI::GetPkeys($options, $table_name, $forced_obtain);
		if ($pkeys !== null) {
			return $pkeys;
		}

		$indexes = DBI::GetIndexes($options, $table_name, $forced_obtain);
		if (is_array($indexes) && !empty($indexes)) {
			$index_list = [];
			foreach ($indexes[key($indexes)] as $index) {
				$index_list[] = $index['column_name'];
			}
			return $index_list;
		}

		return null;
	}

	/**
	 * データベースの設定や状態を取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	array	データベースの設定や状態
	 */
	public static function ReflectionDatabase ($options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::ReflectionDatabase($options);
	}

	/**
	 * 現在の接続名を取得します。
	 *
	 * @return	string	現在の接続名
	 */
	public static function GetConnectionName () {
		$class_name = static::class;
		if (!isset(static::$_connectionNameList[$class_name])) {
			if (defined($class_name.'::CONNECTION_NAME')) {
				$connection_name = $class_name::CONNECTION_NAME;
			} else {
				$connection_name = basename(dirname(str_replace("\\", '/', $class_name)));
			}
			static::$_connectionNameList[$class_name] = $connection_name;
		}
		return static::$_connectionNameList[$class_name];
	}

	/**
	 * 現在のテーブル名を取得します。
	 *
	 * @return	string	現在のテーブル名
	 */
	public static function GetName () {
		$class_name = static::class;
		if (!isset(static::$_nameList[$class_name])) {
			if (defined($class_name.'::NAME')) {
				$name = $class_name::NAME;
			} else {
				preg_match("/^(.+)Model$/", str_replace("\\", '/', $class_name), $matches);
				$name = basename($matches[1]);
				if (static::$_useSnakeCase) {
					$name = Strings::ToSnakeCase($name);
				}
			}
			static::$_nameList[$class_name] = $name;
		}
		return static::$_nameList[$class_name];
	}

	/**
	 * データベースが持つ全テーブル名を返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	データベースが持つ全テーブル名のリスト
	 */
	public static function GetTables ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetTables($options, static::GetName(), $forced_obtain);
	}

	/**
	 * 現在のテーブルが持つ全カラム情報を返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つ全カラム情報のリスト
	 */
	public static function GetColumns ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetColumns($options, static::GetName(), $forced_obtain);
	}

	/**
	 * 現在のテーブルが持つ全カラム名を返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つ全カラム名のリスト
	 */
	public static function GetColumnList ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		$column_list = array_keys(DBI::GetColumns($options, static::GetName(), $forced_obtain));
		return array_combine($column_list, $column_list);
	}

	/**
	 * 現在のテーブルが持つ全プライマリキーを返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つプライマリキーのリスト
	 */
	public static function GetPKeys ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetPkeys($options, static::GetName(), $forced_obtain);
	}

	/**
	 * 現在のテーブルが持つ全インデックスを返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つインデックスのリスト
	 */
	public static function GetIndexes ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetIndexes($options, static::GetName(), $forced_obtain);
	}

	/**
	 * 現在のテーブルが持つ全カラム名を返します。
	 * カラム名にcolumnNameListプロパティでエイリアスが設定されている場合、そちらが優先されます。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つ全カラム名のリスト
	 */
	public static function GetColumnNameList ($forced_obtain = false, $options = []) {
		$class_name = static::class;
		$column_list =static::GetColumnList($forced_obtain, $options);

		if ($class_name::$columnNameList !== null && $column_list !== $class_name::$columnNameList) {
			if (!is_array($class_name::$columnNameList)) {
				$class_name::$columnNameList = [];
			}
			$class_name::$columnNameList += $column_list + $class_name::$columnNameList;
		} else {
			$class_name::$columnNameList = $column_list;
		}

		return $class_name::$columnNameList;
	}

	/**
	 * 現在のテーブルが持つ全カラムのデフォルト値を返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つ全カラムのデフォルト値のリスト
	 */
	public static function GetColumnDefaultValues ($forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetColumnDefaultValues($options, static::GetName(), $forced_obtain);
	}

	/**
	 * 現在のテーブルが持つオートインクリメントが効いたカラムを返します。
	 *
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	テーブルが持つオートインクリメントが効いたカラムのリスト
	 */
	public static function GetAutoIncrementColumns (array $options, $table_name, $forced_obtain = false) {
		$options += $options + static::GetDefaultOptions();
		return DBI::GetAutoIncrementColumns($options, static::GetName(), $forced_obtain);
	}

	/**
	 * インサート用の行データを構築します。
	 *
	 * @param	array	$merge_row		種となる配列 この配列に必須項目を付与して返す
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	インサート用のデータ
	 */
	public static function MakeInsertRow (array $merge_row = [], $forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::MakeInsertRow($options, static::GetName(), $merge_row, $forced_obtain);
	}

	/**
	 * マルチプルインサート用の行データを構築して返します。
	 *
	 * @param	array	$merge_row_list	種となる二次元配列 二次元目が通常のインサート用データ
	 * @param	bool	$forced_obtain	キャッシュを無視して取得するかどうか
	 * @param	array	$options		オプション
	 * @return	array	マルチプルインサート用のデータ
	 */
	public static function MakeMultipleInsertRow (array $merge_row_list, $forced_obtain = false, $options = []) {
		$options += $options + static::GetDefaultOptions();
		foreach ($merge_row_list as $idx => $merge_row) {
			$merge_row_list[$idx] = static::MakeInsertRow($merge_row, $forced_obtain, $options);
		}
		return $merge_row_list;
	}

	/**
	 * テーブルが存在するかどうか返します。
	 *
	 * @return	bool	テーブルが既に存在する場合はtrue, そうでない場合はfalse
	 */
	public static function Exist ($options = [], $forced_obtain = false) {
		$options += $options + static::GetDefaultOptions();
		return DBI::ExistTable($options, static::GetName(), $forced_obtain);
	}

	public static function GetSchemaSeed () {
		return [];
	}

	public static function GetIndexSeed () {
		return [];
	}

	public static function Drop ($options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::DropTable($options, static::GetName());
	}

	public static function Init ($options = []) {
		$options += $options + static::GetDefaultOptions();
		$dsn = DBI::GetDsn($options);
		$table_name = static::GetName();

		if (isset($dsn['schemas'][$table_name]) && !empty($dsn['schemas'][$table_name])) {
			static::ImportSchema($options, false);
		} else {
			static::CreateTable($options, false);
			static::CreateIndex($options, false);
		}
		DBI::UpdateTableInfo($options, static::GetName());
	}

	public static function ImportSchema ($options = [], $forced_obtain = true) {
		$options += $options + static::GetDefaultOptions();
		$table_name = static::GetName();

		$dsn = DBI::GetDsn($options);

		DBI::Execute(file_get_contents($dsn['schemas'][$table_name]), $options);

		if ($forced_obtain) {
			DBI::UpdateTableInfo($options, $table_name);
		}
	}

	public static function CreateTable ($options = [], $forced_obtain = true) {
		$options += $options + static::GetDefaultOptions();
		$table_name = static::GetName();

		if (static::Exist($options)) {
			if (isset($options['force_drop']) && $options['force_drop'] === true) {
				static::Drop($options);
			} else {
				CoreException::RaiseSystemError('既にテーブルが存在しています。table name:%s', [static::GetName()]);
			}
		}

		$options['column_seed'] = $options['column_seed'] ?? static::GetSchemaSeed();
		DBI::CreateTable($table_name, $options);

		if ($forced_obtain) {
			DBI::UpdateTableInfo($table_name, $options);
		}
	}

	public static function CreateIndex ($options = [], $forced_obtain = true) {
		$options += $options + static::GetDefaultOptions();
		$options['index_seed'] = $options['index_seed'] ?? static::GetIndexSeed();
		DBI::CreateIndex(static::GetName(), $options);
		if ($forced_obtain) {
			DBI::UpdateTableInfo($options, static::GetName());
		}
	}

	public static function Rename ($new_table_name, $options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::RenameTable($options, $new_table_name, static::getName());
	}

	public static function SetPrepare ($deta_filed, $where = null, $options = []) {
		$options += $options + static::GetDefaultOptions();
		DBI::SetPrepare($options, $deta_filed, $where);
		return $options['prepare_name'];
	}

	public static function ReleasePrepare ($options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::ReleasePrepare($options, $query);
	}

	public static function Prepare ($options = []) {
		$options += $options + static::GetDefaultOptions();
		return DBI::Prepare($options, $query);
	}

	public static function Filter ($array, $alias = [], $forced_obtain = false, $options = []) {
		$result = [];
		$enable_columns = static::GetColumnList($forced_obtain, $options);
		foreach ($array as $key => $value) {
			$column_name = isset($alias[$key]) ? $alias[$key] : $key;
			if (isset($enable_columns[$column_name])) {
				$result[$column_name] = $value;
			}
		}
		return $result;
	}
}
