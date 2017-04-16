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

namespace ickx\fw2\io\sdf;

use ickx\fw2\core\exception\CoreException;
use ickx\fw2\io\file_system\IniFile;
use ickx\fw2\security\validators\Validator;
use ickx\fw2\vartype\arrays\Arrays;
use ickx\fw2\vartype\strings\Strings;

/**
 * Structured data file interface
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class SDFI {
	/** @var	string	デフォルトのパスワードハッシュアルゴリズム */
	const PASSWORD_HASH_ALGORITHM	= 'sha256';

	/** @var	string	デフォルトのコネクション名 */
	const CONNECTION_NAME_DEFAULT	= 'default';

	/** @var	string	ドライバクラスサフィックス */
	const DRIVER_CLASS_NAME_SAFIX	= 'Driver';

	/** @var	string	Fileドライバー名：CSV */
	const DRIVER_NAME_CSV			= 'csv';

	/** @var	string	Fileドライバー名：Plain Text */
	const DRIVER_NAME_PLAIN_TEXT	= 'plain_text';


	/** @var	string	Fileドライバー名：TSV */
	const DRIVER_NAME_TSV			= 'tsv';

	/** @var	string	Fileドライバー名：SQL */
	const DRIVER_NAME_SQL_SELECT	= 'sql';

	/** @staticvar	bool	ファイルロックフラグ */
	protected static $_inFilelock	= false;

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
		'password'	=> [],
		'key'		=> [],
		'path'		=> [],
		'options'	=> [],
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
	 * ファイルを接続します。
	 *
	 * @param	mixed	$dsn		ファイル設定または設定ファイルへのフルパス
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
			$dsn_options[$option['name']] = (int)$option['value'];
		}
		$dsn['options'] = $dsn_options;

		Validator::BulkCheck($dsn, static::$_enableIniRuleList);

		static::$_dsnList[static::AdjustConnectionName($options)] = $dsn;
	}

	public static function Count ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->count($options);
	}

	public static function Read ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->read($options);
	}

	public static function Write ($name, $data, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->write($data, $options);
	}

	public static function WriteLn ($name, $data, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->writeLn($data, $options);
	}

	public static function Close ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->close($options);
	}

	public static function Open ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->open($options);
	}

	public static function ReadOpen ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->readOpen($options);
	}

	public static function WriteOpen ($name, $options = []) {
		$options['connection_name'] = $name;
		return static::GetConnection($options)->writeOpen($options);
	}

	/**
	 * コネクションを取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	￥PDO	PDOインスタンス
	 */
	public static function GetConnection ($options) {
		$connection_name = static::AdjustConnectionName($options);

		if (!isset(static::$_connectionList[$connection_name])) {
			static::_Connect($options);
		}
		return static::$_connectionList[$connection_name];
	}

	/**
	 * コネクションを確立します。
	 *
	 * @param	array	$options	オプション
	 * @return	￥PDO	PDOインスタンス
	 */
	protected static function _Connect ($options) {
		$connection_name = static::AdjustConnectionName($options);

		if (!isset(static::$_dsnList[$connection_name])) {
			throw CoreException::RaiseSystemError('未定義のファイル設定を用いて接続しようとしています。connection_name:%s', [$connection_name]);
		}

		$dsn = static::$_dsnList[$connection_name];
		$driver_class_name = sprintf('%s\drivers\%s%s', __NAMESPACE__, Strings::ToUpperCamelCase($dsn['driver']), static::DRIVER_CLASS_NAME_SAFIX);
		static::$_connectionList[$connection_name] = $driver_class_name::Connect($dsn);
	}

	//==============================================
	//Utility
	//==============================================
	/**
	 * オプションからコネクション名を取得します。
	 *
	 * @param	array	$options	オプション
	 * @return	string	コネクション名
	 */
	public static function AdjustConnectionName ($options) {
		return Arrays::AdjustValue($options, ['connection_name', 0], static::CONNECTION_NAME_DEFAULT);
	}
}
