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

namespace ickx\fw2\core\status;

/**
 * 状態管理クラス
 *
 * @category	Flywheel2
 * @package		Core
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class MultiStatus implements IStatus {
	protected $_status = null;
	protected $_statusList = null;

	public function __construct($id, $code, $status_list, $message, $exception = null) {
		$this->_statusList = [];
		$this->_setId($id);
		$this->_setCode($code);
		$this->addAll($status);
		$this->_setMessage($message);
		$this->_setException($exception);
	}

	public function add(IStatus $status) {
		$this->_statusList[] = $status;
	}

	public function addAll($status_list) {
		if (!is_array($status_list)) {
			$status_list = array($status_list);
		}
		$this->_statusList += $status_list;
	}

	public function merge($status_list) {
		if (!is_array($status_list)) {
			$status_list = array($status_list);
		}
		$this->_statusList = array_merge($this->_statusList, $status_list);
	}

	public function getChildren() {
		return $this->_statusList;
	}

	public function isMultiStatus() {
		return true;
	}

	public function __toString() {
		return '';
	}

	public function getCode() {
	}
	public function getSeverity() {
	}
	public function getId() {
	}
	public function getMessage() {
	}
	public function getException() {
	}
	public function isOK() {
	}

	public function matches($severity_mask) {
	}
}
