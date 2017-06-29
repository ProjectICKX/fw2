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

namespace ickx\fw2\io\php_ini\traits;

use ickx\fw2\vartype\arrays\Arrays;

/**
 * PHP ini file upload設定特性。
 *
 * @category	Flywheel2
 * @package		io
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait PhpIniFileUploadTrait {
	/**
	 * php iniの設定のうち、ファイルアップロードにかかる設定を全て返します。
	 *
	 * @param	bool	$details	詳細に設定をだすかどうか
	 * @return	array	ファイルアップロードにかかる全ての設定。
	 */
	public static function GetFileUploadIniAll ($details = true) {
		return Arrays::GetElementsByKeys(ini_get_all(null, $details), static::GetFileUploadIniNameList());
	}

	/**
	 * php iniの設定のうち、ファイルアップロードにかかる設定名を返します。
	 *
	 * @return	array	ファイルアップロードにかかる設定のリスト
	 */
	public static function GetFileUploadIniNameList () {
		return [
			static::FILE_UPLOADS						=> static::FILE_UPLOADS,
			static::UPLOAD_TMP_DIR						=> static::UPLOAD_TMP_DIR,
			static::UPLOAD_MAX_FILESIZE					=> static::UPLOAD_MAX_FILESIZE,
			static::MAX_FILE_UPLOADS					=> static::MAX_FILE_UPLOADS,
			static::POST_MAX_SIZE						=> static::POST_MAX_SIZE,
			static::MAX_INPUT_NESTING_LEVEL				=> static::MAX_INPUT_NESTING_LEVEL,
			static::MAX_INPUT_TIME						=> static::MAX_INPUT_TIME,
			static::MAX_INPUT_VARS						=> static::MAX_INPUT_VARS,
			static::SESSION_UPLOAD_PROGRESS_ENABLED		=> static::SESSION_UPLOAD_PROGRESS_ENABLED,
			static::SESSION_UPLOAD_PROGRESS_CLEANUP		=> static::SESSION_UPLOAD_PROGRESS_CLEANUP,
			static::SESSION_UPLOAD_PROGRESS_PREFIX		=> static::SESSION_UPLOAD_PROGRESS_PREFIX,
			static::SESSION_UPLOAD_PROGRESS_NAME		=> static::SESSION_UPLOAD_PROGRESS_NAME,
			static::SESSION_UPLOAD_PROGRESS_FREQ		=> static::SESSION_UPLOAD_PROGRESS_FREQ,
			static::SESSION_UPLOAD_PROGRESS_MIN_FREQ	=> static::SESSION_UPLOAD_PROGRESS_MIN_FREQ,
		];
	}
}
