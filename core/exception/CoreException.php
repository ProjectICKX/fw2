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
 * @package		core
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\core\exception;

use ickx\fw2\core\status\IStatus;
use ickx\fw2\core\status\Status;
use ickx\fw2\international\encoding\Encoding;

/**
 * コア例外クラス
 *
 * Flywheel2自身が生成する例外は全てこの例外クラスとなります。
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class CoreException extends \Exception {
	/**
	 * Statusを保持します。
	 *
	 * @var	Status
	 */
	protected $_status = null;

	/**
	 * 文字列をシステム内部エンコーディングに変換します。
	 *
	 * @param	string	$string	システム内部エンコーディングに変換する文字列
	 * @return	string	システム内部エンコーディングに変換された文字列
	 */
	public static function ConvertinternalEncoding ($string) {
		return mb_convert_encoding($string, mb_internal_encoding(), mb_detect_encoding($string, ['UTF-8', 'SJIS-win', 'eucJP-win', 'JIS', 'ASCII'], true));
	}

	/**
	 * 例外を作成します。
	 *
	 * @param	IStatus	$status	例外ステータス
	 * @return	\ickx\fw2\core\exception\CoreException
	 */
	public static function Raise (IStatus $status) {
		return new static($status);
	}

	/**
	 * SystemError例外を生成し返します。
	 *
	 * @param	string			$message	例外メッセージ
	 * @param	int				$severity	例外レベル
	 * @return	CoreException	SystemError状態の例外
	 */
	public static function RaiseSystemError ($message, $severity = Status::WARNING) {
		$message = Encoding::ConvertToDefaultEncoding($message);
		if (is_array($severity)) {
			$message = vsprintf($message, array_map(function ($value) {return Encoding::ConvertToDefaultEncoding($value);}, $severity));
			$severity = (func_num_args() > 2) ? func_get_arg(3) : Status::WARNING;
		}
		return static::Raise(Status::SystemError($message, $severity));
	}

	/**
	 * 例外を1行の文字列表現に変換します。
	 *
	 * @param	\Exception	$e	変換する例外
	 * @return	string		変換された文字列
	 */
	public static function ConvertToString (\Exception $e) {
		return sprintf(
			'#message %s %s(%s) %s',
			static::ConvertinternalEncoding($e->getMessage()),
			static::ConvertinternalEncoding($e->getFile()),
			$e->getLine(),
			static::ConvertinternalEncoding(str_replace(array("\n", "\r", "\r\n"), " ", $e->getTraceAsString()))
		);
	}

	/**
	 * 例外を文字列表現に変換します。
	 *
	 * @param	\Exception	$e	変換する例外
	 * @return	string		変換された文字列
	 */
	public static function ConvertToStringMultiLine (\Exception $e) {
		return sprintf(
			'#message %s %s(%s) %s',
			static::ConvertinternalEncoding($e->getMessage()),
			static::ConvertinternalEncoding($e->getFile()),
			$e->getLine(),
			static::ConvertinternalEncoding("\n". str_replace(array("\n", "\r", "\r\n"), "\n", $e->getTraceAsString()))
		);
	}

	/**
	 * 状態を元に例外を生成し、必要に応じてスローします。
	 *
	 * @param	Status	$status				例外の元となる状態
	 * @param	bool	$raise_exception	生成した例外オブジェクトをスローするかどうか
	 * @retun	Status	状態
	 */
	public static function ScrubbedThrow ($status, $raise_exception = false) {
		if ($raise_exception) {
			throw static::Raise($status);
		}
		return $status;
	}

	/**
	 * コンストラクタ
	 *
	 * @param	IStatus	$status	状態
	 */
	public function __construct(IStatus $status) {
	    $this->_status = $status;
	}

	/**
	 * 現在の状態を返します。
	 *
	 * @return	Status	状態
	 */
	public function getStatus () {
	    return $this->_status;
	}

	/* Overrideable */
	/**
	 * 文字列アクセス
	 *
	 * @see Exception::__toString()
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * ステータスメッセージを取得します。
	 */
	public function getStatusMessage () {
		return $this->_status->getMessage();
	}

	/**
	 * オブジェクトの文字列表現を返します。
	 *
	 * @return	string	オブジェクトの文字列表現。
	 */
	public function toString () {
		return static::ConvertToString($this);
	}
}
