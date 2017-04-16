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

namespace ickx\fw2\mvc\app\controllers\traits;

/**
 * 状態特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait StatusTrait {
	/** @var	\ickx\fw2\vartype\arrays\LazyArrayObject	インフォデータ */
	public $info			= null;

	/** @var	\ickx\fw2\vartype\arrays\LazyArrayObject	警告データ */
	public $warn			= null;

	/** @var	\ickx\fw2\vartype\arrays\LazyArrayObject	エラーデータ */
	public $error			= null;

	/**
	 * インフォメーションがあるかどうかを返します。
	 *
	 * @return	bool	インフォメーションがある場合はtrue, そうでない場合はfalse
	 */
	public function isInfo () {
		return !empty($this->info);
	}

	/**
	 * インフォメーションを一括で登録します。
	 *
	 * @param	array	$info_list	インフォメーションリスト
	 */
	public function setInfoList (array $info_list) {
		$this->info = $info_list;
	}

	/**
	 * インフォメーションを登録します。
	 *
	 * 同名のインフォメーションがある場合、上書きされます。
	 *
	 * @param	string	$name	インフォメーション名
	 * @param	string	$info	インフォメーションメッセージ
	 */
	public function setInfo ($name, $info) {
		$this->info[$name] = Arrays::AdjustArray($info);
	}

	/**
	 * インフォメーションを追加します。
	 *
	 * @param	string	$name	インフォメーション名
	 * @param	string	$info	インフォメーションメッセージ
	 */
	public function appendInfo ($name, $info) {
		$this->info[$name][] = $info;
	}

	/**
	 * インフォメーションを一括で追加します。
	 *
	 * @param	array	$info_list	インフォメーションリスト
	 */
	public function appendInfoList (array $info_list) {
		foreach ($info_list as $name => $info) {
			$this->appendInfo($name, $info);
		}
	}

	/**
	 * 警告があるかどうかを返します。
	 *
	 * @return	bool	警告がある場合はtrue, そうでない場合はfalse
	 */
	public function isWarn () {
		return !empty($this->warn);
	}

	/**
	 * 警告を一括で登録します。
	 *
	 * @param	array	$warn_list	警告リスト
	 */
	public function setWarnList (array $warn_list) {
		$this->warn = $warn_list;
	}

	/**
	 * 警告を登録します。
	 *
	 * 同名の警告がある場合、上書きされます。
	 *
	 * @param	string	$name	警告名
	 * @param	string	$warn	警告メッセージ
	 */
	public function setWarn ($name, $warn) {
		$this->warn[$name] = Arrays::AdjustArray($warn);
	}

	/**
	 * 警告を追加します。
	 *
	 * @param	string	$name	警告名
	 * @param	string	$warn	警告メッセージ
	 */
	public function appendWarn ($name, $warn) {
		$this->warn[$name][] = $warn;
	}

	/**
	 * 警告を一括で追加します。
	 *
	 * @param	array	$warn_list	警告リスト
	 */
	public function appendWarnList (array $warn_list) {
		foreach ($warn_list as $name => $warn) {
			$this->appendWarn($name, $warn);
		}
	}

	/**
	 * エラーがあるかどうかを返します。
	 *
	 * @return	bool	エラーがある場合はtrue, そうでない場合はfalse
	 */
	public function isError () {
		return !empty($this->error);
	}

	/**
	 * エラーを一括で登録します。
	 *
	 * @param	array	$error_list	エラーリスト
	 */
	public function setErrorList (array $error_list) {
		$this->error = $error_list;
	}

	/**
	 * エラーを登録します。
	 *
	 * 同名のエラーがある場合、上書きされます。
	 *
	 * @param	string	$name	エラー名
	 * @param	string	$error	エラーメッセージ
	 */
	public function setError ($name, $error) {
		$this->error[$name] = Arrays::AdjustArray($error);
	}

	/**
	 * エラーを追加します。
	 *
	 * @param	string	$name	エラー名
	 * @param	string	$error	エラーメッセージ
	 */
	public function appendError ($name, $error) {
		$this->error[$name][] = $error;
	}

	/**
	 * エラーを一括で追加します。
	 *
	 * @param	array	$error_list	エラーリスト
	 */
	public function appendErrorList (array $error_list) {
		foreach ($error_list as $name => $error) {
			$this->appendError($name, $error);
		}
	}
}
