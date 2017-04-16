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

namespace ickx\fw2\io\sdf\drivers\traits;

use ickx\fw2\vartype\strings\Strings;
use ickx\fw2\core\exception\CoreException;
use ickx\fw2\vartype\arrays\Arrays;

/**
 * Flywheel2 SdfDriver Trait
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait SdfDriverTrait {
	/**
	 * ファイルに接続します。
	 *
	 * @param	mixed	$dsn	DSN
	 * @throws	\Exception
	 */
	public static function Connect ($dsn) {
		try {
			$dsn = static::DsnFilter($dsn);
			return new static (
				static::MakeDsn($dsn),
				Arrays::AdjustValue($dsn, 'password', null),
				static::MakeOptionList(Arrays::AdjustValue($dsn, 'options', []))
			);
		} catch (CoreException $core_e) {
 			throw new \Exception(sprintf(
 				'%s dsn=%s password=%s options=%s',
 				$pdo_e->getMessage(),
 				static::MakeDsn($dsn),
 				Arrays::AdjustValue($dsn, 'password', '<not set>'),
 				implode(', ', static::MakeOptionList(Arrays::AdjustValue($dsn, 'options', [])))
 			), 0, $core_e);
		}
	}

	/**
	 * DSN設定配列からDSN文字列を生成します。
	 *
	 * @param	array	$dsn_config	DSN設定配列
	 * @return	string	DSN設定文字列
	 */
	public static function MakeDsn ($dsn_config) {
		return $dsn_config['path'];
	}

	/**
	 * 設定オプションリストを構築し返します。
	 *
	 * @param	追加で設定する設定オプション
	 * @return	設定オプションリスト
	 */
	public static function MakeOptionList ($inherent_options = []) {
		return $inherent_options + static::GetDefaultOptions();
	}
}
