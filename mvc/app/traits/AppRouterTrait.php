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

namespace ickx\fw2\mvc\app\traits;

/**
 * AppRouterTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppRouterTrait {
	/**
	 * utility�FRouter�ɓo�^����Ă����񂩂�URL���쐬���܂��B
	 *
	 * @param	string|ickx\fw2\mvc\app\AppController	$controller		�R���g���[�����܂��̓R���g���[��
	 * @param	string									$action_name	�A�N�V������
	 * @param	array									$parameters		�p�����[�^
	 * @param	array									$var_parameters	�x���]���p�p�����[�^
	 * @param	string									$encoding		�G���R�[�f�B���O
	 * @return	string|bool								URL �}�b�`����URL�������ꍇ��false
	 */
	public static function MakeUrl ($controller, $action_name = 'index', $parameters = [], $var_parameters = [], $encoding = null) {
		return Flywheel::MakeUrl ($controller, $action_name, $parameters, $var_parameters , $encoding);
	}
}
