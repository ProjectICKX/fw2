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

namespace ickx\fw2\io\rdbms\drivers\traits;

use ickx\fw2\vartype\strings\Strings;
use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;

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
				Arrays::AdjustValue($dsn, 'username', null),
				Arrays::AdjustValue($dsn, 'password', null),
				static::MakeOptionList(Arrays::AdjustValue($dsn, 'options', []))
			);
		} catch (\PDOException $pdo_e) {
 			throw new \Exception(sprintf(
 				'%s dsn=%s user=%s password=%s options=%s',
 				$pdo_e->getMessage(),
 				static::MakeDsn($dsn),
 				Arrays::AdjustValue($dsn, 'username', ''),
 				Arrays::AdjustValue($dsn, 'password', ''),
 				implode(', ', static::MakeOptionList(Arrays::AdjustValue($dsn, 'options', [])))
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
}
