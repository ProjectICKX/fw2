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
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\io\rdbms\drivers\traits;

/**
 * Flywheel2 RDBMSDriver Trait
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait RdbmsDriverTrait {
	/**
	 * データベースに接続します。
	 *
	 * @param	mixed	$dsn	DSN
	 * @throws	\Exception
	 */
	public static function Connect ($dsn) {
		try {
			$dsn = static::AdjustDsn($dsn);

			static::ValidateDsn($dsn);

			return new static (
				static::MakeDsn($dsn),
				$dsn['username'] ?? null,
				$dsn['password'] ?? null,
				static::MakeOptionList($dsn['options'] ?? [])
			);
		} catch (\PDOException $pdo_e) {
 			throw new \Exception(sprintf(
 				'%s dsn=%s user=%s password=%s options=[%s]',
 				$pdo_e->getMessage(),
 				static::MakeDsn($dsn),
 				$dsn['username'] ?? '',
 				$dsn['password'] ?? '',
 				implode(', ', static::convReadableAttrValueList(static::MakeOptionList($dsn['options'] ?? [])))
 			), 0, $pdo_e);
		}
	}

	/**
	 * DSN設定配列からDSN文字列を生成します。
	 *
	 * @param	array	$dsn_config	DSN設定配列
	 * @return	string	DSN設定文字列
	 */
	public static function MakeDsn ($dsn_config) {
		$driver_name = $dsn_config['driver'];
		unset($dsn_config['driver']);

		$dsn = [];
		foreach (static::DsnFilter($dsn_config) as $name => $config) {
			$dsn[] = sprintf('%s=%s', $name, $config);
		}

		return $driver_name .':'. implode(';', $dsn);
	}

	public static function ValidateDsn ($dsn_config) {
		return \ickx\fw2\security\validators\Validator::BulkCheck($dsn_config, static::GetValidateRuleList());
	}

	public static function DsnFilter ($dsn_config) {
		foreach (static::GetDsnFilterTargetList() as $unset_key) {
			if (isset($dsn_config[$unset_key])) {
				unset($dsn_config[$unset_key]);
			}
		}
		return $dsn_config;
	}

	/**
	 * ファイルパスのエンコーディングを内部エンコーディングに統一します。
	 *
	 * @param	string	$file_path	エンコーディングを変換するファイルパス
	 * @return	string	エンコーディングを変換されたファイルパス
	 */
	public static function AdjustFilePathEncoding ($file_path) {
		if (\PHP_OS !== 'WINNT' && \PHP_OS !== 'WIN32') {
			return $file_path;
		}

		$internal_encoding = mb_internal_encoding();
		$file_path_encoding = mb_detect_encoding($file_path, ['UTF-8', 'SJIS-win', 'eucJP-win', 'JIS', 'ASCII'], true);

		if ($file_path_encoding === $internal_encoding) {
			return $file_path;
		}

		return mb_convert_encoding($file_path, $internal_encoding, $file_path_encoding);
	}

	/**
	 * 設定オプションリストを構築し返します。
	 *
	 * @param	追加で設定する設定オプション
	 * @return	設定オプションリスト
	 */
	public static function MakeOptionList ($inherent_options = []) {
		return $inherent_options + static::GetDefaultOptions() + [
			\PDO::ATTR_ERRMODE			=> \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_ORACLE_NULLS		=> \PDO::NULL_NATURAL,
			\PDO::ATTR_EMULATE_PREPARES	=> FALSE,
		];
	}

	/**
	 * PDO定数：属性リストを返します。
	 *
	 * @return	array	PDO定数：属性リスト
	 */
	public static function GetAttrPdoConstList () {
		$list = [
			\PDO::ATTR_AUTOCOMMIT				=> '\PDO::ATTR_AUTOCOMMIT',
			\PDO::ATTR_PREFETCH					=> '\PDO::ATTR_PREFETCH',
			\PDO::ATTR_TIMEOUT					=> '\PDO::ATTR_TIMEOUT',
			\PDO::ATTR_ERRMODE					=> '\PDO::ATTR_ERRMODE',
			\PDO::ATTR_SERVER_VERSION			=> '\PDO::ATTR_SERVER_VERSION',
			\PDO::ATTR_CLIENT_VERSION			=> '\PDO::ATTR_CLIENT_VERSION',
			\PDO::ATTR_SERVER_INFO				=> '\PDO::ATTR_SERVER_INFO',
			\PDO::ATTR_CONNECTION_STATUS		=> '\PDO::ATTR_CONNECTION_STATUS',
			\PDO::ATTR_CASE						=> '\PDO::ATTR_CASE',
			\PDO::ATTR_CURSOR_NAME				=> '\PDO::ATTR_CURSOR_NAME',
			\PDO::ATTR_CURSOR					=> '\PDO::ATTR_CURSOR',
			\PDO::ATTR_DRIVER_NAME				=> '\PDO::ATTR_DRIVER_NAME',
			\PDO::ATTR_ORACLE_NULLS				=> '\PDO::ATTR_ORACLE_NULLS',
			\PDO::ATTR_PERSISTENT				=> '\PDO::ATTR_PERSISTENT',
			\PDO::ATTR_STATEMENT_CLASS			=> '\PDO::ATTR_STATEMENT_CLASS',
			\PDO::ATTR_FETCH_CATALOG_NAMES		=> '\PDO::ATTR_FETCH_CATALOG_NAMES',
			\PDO::ATTR_FETCH_TABLE_NAMES		=> '\PDO::ATTR_FETCH_TABLE_NAMES',
			\PDO::ATTR_STRINGIFY_FETCHES		=> '\PDO::ATTR_STRINGIFY_FETCHES',
			\PDO::ATTR_MAX_COLUMN_LEN			=> '\PDO::ATTR_MAX_COLUMN_LEN',
			\PDO::ATTR_DEFAULT_FETCH_MODE		=> '\PDO::ATTR_DEFAULT_FETCH_MODE',
			\PDO::ATTR_EMULATE_PREPARES			=> '\PDO::ATTR_EMULATE_PREPARES',
		];

		if (defined('\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
			$list[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY]	= '\MYSQL_ATTR_USE_BUFFERED_QUERY';
		}

		return $list;
	}

	/**
	 * 属性定数値と属性値から表示用の属性値を返します。
	 *
	 * @param	int		$attribute	属性定数値
	 * @param	mixed	$value		属性値
	 * @return	mixed	表示用の属性値
	 */
	public static function GetReadableAttrValuePdoConst ($attribute, $value) {
		switch ($attribute) {
			case \PDO::ATTR_ERRMODE:
				return [\PDO::ERRMODE_SILENT => '\PDO::ERRMODE_SILENT', \PDO::ERRMODE_WARNING => '\PDO::ERRMODE_WARNING', \PDO::ERRMODE_EXCEPTION => '\PDO::ERRMODE_EXCEPTION'][$value];
			case \PDO::ATTR_CASE:
				return [\PDO::CASE_NATURAL => 'PDO::CASE_NATURAL', \PDO::CASE_LOWER => 'PDO::CASE_LOWER', \PDO::CASE_UPPER => 'PDO::CASE_UPPER'][$value];
			case \PDO::ATTR_ORACLE_NULLS:
				return [\PDO::NULL_NATURAL => '\PDO::NULL_NATURAL', \PDO::NULL_EMPTY_STRING => '\PDO::NULL_EMPTY_STRING', \PDO::NULL_TO_STRING => '\PDO::NULL_TO_STRING'][$value];
			case \PDO::ATTR_DEFAULT_FETCH_MODE:
				return [\PDO::FETCH_ASSOC => 'PDO::FETCH_ASSOC', \PDO::FETCH_BOTH => 'PDO::FETCH_BOTH', \PDO::FETCH_BOUND => 'PDO::FETCH_BOUND', \PDO::FETCH_CLASS => 'PDO::FETCH_CLASS', \PDO::FETCH_INTO => 'PDO::FETCH_INTO', \PDO::FETCH_LAZY => 'PDO::FETCH_LAZY', \PDO::FETCH_NAMED => 'PDO::FETCH_NAMED', \PDO::FETCH_NUM => 'PDO::FETCH_NUM', \PDO::FETCH_OBJ => 'PDO::FETCH_OBJ', \PDO::FETCH_PROPS_LATE => 'PDO::FETCH_PROPS_LATE'][$value];
			case \PDO::ATTR_AUTOCOMMIT:
			case \PDO::ATTR_STRINGIFY_FETCHES:
			case \PDO::ATTR_EMULATE_PREPARES:
				return in_array($value, [false, 'false', 'none', 'off', 'no', '0', 0], true) ? 'false' : 'true';
			case \PDO::ATTR_PREFETCH:
			case \PDO::ATTR_TIMEOUT:
			case \PDO::ATTR_SERVER_VERSION:
			case \PDO::ATTR_CLIENT_VERSION:
			case \PDO::ATTR_SERVER_INFO:
			case \PDO::ATTR_CONNECTION_STATUS:
			case \PDO::ATTR_CURSOR_NAME:
			case \PDO::ATTR_CURSOR:
			case \PDO::ATTR_DRIVER_NAME:
			case \PDO::ATTR_PERSISTENT:
			case \PDO::ATTR_STATEMENT_CLASS:
			case \PDO::ATTR_FETCH_CATALOG_NAMES:
			case \PDO::ATTR_FETCH_TABLE_NAMES:
			case \PDO::ATTR_MAX_COLUMN_LEN:
				return $value;
		}

		if (defined('\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY') && $attribute === \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY) {
			return in_array($value, [false, 'false', 'none', 'off', 'no', '0', 0], true) ? 'false' : 'true';
		}

		return 'unkown key:'. $attribute;
	}

	/**
	 * 属性値リストを読みやすい形式にして返します。
	 *
	 * @param	array	$attribute_list	属性値リスト
	 * @return	array	属性値リスト
	 */
	public static function convReadableAttrValueList ($attribute_list) {
		$pdo_const_attr_list = static::GetAttrPdoConstList();
		$attributes = [];
		foreach ($attribute_list as $attribute => $value) {
			$attributes[] = sprintf('%s => %s', $pdo_const_attr_list[$attribute] ?? $attribute, static::GetReadableAttrValuePdoConst($attribute, $value));
		}
		return $attributes;
	}
}
