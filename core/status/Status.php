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
class Status implements IStatus {
	const NAME	= 'status';

	protected $_name		= '';
	protected $_severity	= null;
	protected $_id			= null;
	protected $_code		= null;
	protected $_message		= null;
	protected $_exception	= null;
	protected $_container	= null;

	public function __construct($severity, $id, $code, $message, $exception = null, $name = null, $container = []) {
		$this->_setSeverity ($severity);
		$this->_setId ($id);
		$this->_setCode ($code);
		$this->_setMessage ($message);
		$this->_setException ($exception);
		$this->_setName ($name ?: static::NAME);
		$this->_setContainer($container);
	}

	public function getChildren () {
		return null;
	}

	public function getCode () {
		return $this->_code;
	}

	public function getSeverity () {
		return $this->_severity;
	}

	public function getId () {
		return $this->_id;
	}

	public function getMessage () {
		return $this->_message;
	}

	public function getException () {
		return $this->_exception;
	}

	public function getName () {
		return $this->_name;
	}

	public function getContainer () {
		return $this->_container;
	}

	public function isMultiStatus () {
		return false;
	}

	public function isOK () {
		return (($this->_severity & self::OK) !== 0);
	}

	public function matches ($severity_mask) {
		return (($severity_mask & (self::OK | self::NG | self::INFO | self::ERROR | self::WARNING | self::FATAL)) !== 0);
	}

	protected function _setSeverity ($severity) {
		$this->_severity = $severity;
	}

	protected function _setId ($id) {
		$this->_id = $id;
	}

	protected function _setCode ($code) {
		$this->_code = $code;
	}

	protected function _setMessage ($message) {
		$this->_message = $message;
	}

	protected function _setException ($exception) {
		$this->_exception = $exception;
	}

	protected function _setName ($name) {
		$this->_name = $name;
	}

	protected function _setContainer ($container) {
		$this->_container = $container;
	}

	/* Overrideable */
	public function __toString() {
		return sprintf(
			'%s %s: %s',
			static::NAME,
			static::GetStatusText()[$this->getCode()],
			$this->getMessage()
		);
	}

	public static function NotFound ($message, $values = []) {
		return new static(self::FATAL, self::ID_FLYWHEEL, self::NOT_FOUND, vsprintf($message, $values));
	}

	public static function Found ($message, $values = []) {
		return new static(self::FATAL, self::ID_FLYWHEEL, self::FOUND, vsprintf($message, $values));
	}

	public static function GetStatusText () {
		return [
			static::OK			=> 'OK',
			static::NG			=> 'NG',
			static::RESERVE		=> 'reserve',
			static::CANCEL		=> 'cancel',
			static::FOUND		=> 'found',
			static::NOT_FOUND	=> 'not found',
			static::LEGAL		=> 'legal',
			static::ILLEGAL		=> 'illegal',
			static::INFO		=> 'info',
			static::NOTICE		=> 'notice',
			static::ERROR		=> 'error',
			static::WARNING		=> 'warning',
			static::FATAL		=> 'fatal',
			static::UNKOWN		=> 'unkown',
			static::USER		=> 'user',
			static::SYSTEM		=> 'system',
		];
	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function UnkownException ($message, Exception $exception, $container = []) {
		return new static(self::FATAL, self::ID_FLYWHEEL, self::UNKOWN, 'Unkown:'. $message, $exception, null, null, $container);
	}

	public static function ClassFileNotFound ($class_file_name, $container = []) {
		return new static(self::FATAL, self::ID_FLYWHEEL, self::NOT_FOUND, 'Class file not found'. $class_file_name, null, null, $container);
	}

	public static function ClassNotFound ($class_name, $container = []) {
		return new static(self::FATAL, self::ID_FLYWHEEL, self::NOT_FOUND, 'Class not found:'. $class_name, null, null, $container);
	}

	public static function MethodNotFound ($class_name, $method_name, $container = []) {
		$message = sprintf('%s::%s', $class_name, $method_name);
		return new static(self::FATAL, self::ID_FLYWHEEL, self::NOT_FOUND, 'Method not found:'. $message, null, null, $container);
	}

	public static function IllegalGetParameter ($message, $container = []) {
		return new static(self::WARNING, self::ID_FLYWHEEL, self::ILLEGAL, 'Illegal get parameter:'. $message, null, null, $container);
	}

	public static function IllegalPostData ($message, $container = []) {
		return new static(self::WARNING, self::ID_FLYWHEEL, self::ILLEGAL, 'Illegal post data:'. $message, null, null, $container);
	}

	public static function SystemError ($message, $severity = self::WARNING, $container = []) {
		return new static($severity, self::ID_FLYWHEEL, self::SYSTEM, 'System Error!!:'. $message, null, null, $container);
	}
}
