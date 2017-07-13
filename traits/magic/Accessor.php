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
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\traits\magic;

/**
 * Flywheel2 Magic accessor
 *
 * @category	Flywheel2
 * @package		traits
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait Accessor {
	/**
	 * �C���X�^���X�𐶐����A�w�肳�ꂽ���\�b�h�����s���Ă���C���X�^���X��Ԃ��܂��B
	 *
	 * @param	string	$name		�ÓI�Ăяo�����\�b�h��
	 * @param	string	$arguments	����
	 * @return	mixed	�l���ݒ肳���ꍇ�͎��g�̃C���X�^���X�A�����Ȃ��̏ꍇ�̓v���p�e�B�̐ݒ�l
	 */
	public static function __callStatic ($name, $arguments) {
		if (method_exists(static::class, 'init')) {
			return static::init()->$name(...$arguments);
		}
		return (new static)->$name(...$arguments);
	}

	/**
	 * �v���p�e�B�}�W�b�N�A�N�Z�T
	 *
	 * @param	string	$name		�Ăяo�����\�b�h��
	 * @param	string	$arguments	����
	 * @throws	\RuntimeException	�v���p�e�B�����݂��Ȃ��ꍇ
	 * @return	mixed	�l���ݒ肳���ꍇ�͎��g�̃C���X�^���X�A�����Ȃ��̏ꍇ�̓v���p�e�B�̐ݒ�l
	 */
	public function __call ($name, $arguments) {
		if (method_exists($this, $name)) {
			return $this->$name(...$arguments);
		}

		if (!property_exists($this, $name)) {
			throw new \RuntimeException(sprintf('Property:%s not found.', $name));
		}

		if (empty($arguments)) {
			return method_exists($this, $name . 'GetPostProcess') ? $this->{$name . 'GetPostProcess'}($this->$name) : $this->$name;
		}

		$this->$name = method_exists($this, $name . 'SetPreProcess') ? $this->{$name . 'SetPreProcess'}($arguments[0]) : $arguments[0];

		return $this;
	}
}
