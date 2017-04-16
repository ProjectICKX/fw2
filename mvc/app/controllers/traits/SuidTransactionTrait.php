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
 * SuidTransaction特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait SuidTransactionTrait {
	use	\ickx\fw2\mvc\app\controllers\traits\SessionTrait,
		\ickx\fw2\mvc\app\controllers\traits\TransactionDBITrait;

	/**
	 * SuidTransaction時の確認画面ルールを構築します。
	 *
	 * @param	mixed	$next_action	"完了"押下時の遷移先アクション
	 * @param	mixed	$back_action	"戻る"押下時の遷移先アクション
	 * @param	array	$post_data_keys	使用を許可するパラメータ名
	 * @param	string	$next_triger	"完了"ボタンのフォーム名
	 * @param	string	$back_triger	"戻る"ボタンのフォーム名
	 * @param	string	$default_triger	"デフォルト表示"時のパラメータ名
	 * @return	array					確認画面用ルールセット
	 */
	public function createSuidTransactionConfRule ($next_action, $back_action, $post_data_keys, $next_triger = null, $back_triger = null, $default_triger = null, $options = []) {
		$next_triger = $next_triger ?: 'submit';
		$back_triger = $back_triger ?: 'back';
		$default_triger = $default_triger ?: self::MEAN_DEFAULT;

		$next_action = (array) $next_action;
		if (!isset($next_action[1])) {
			array_unshift($next_action, $this);
		}

		$back_action = (array) $back_action;
		if (!isset($back_action[1])) {
			array_unshift($back_action, $this);
		}

		$next_parameter = isset($options['next_parameter']) ? $options['next_parameter'] : null;
		$back_parameter = isset($options['back_parameter']) ? $options['back_parameter'] : null;

		return [
			$default_triger	=> [
				'action'	=> [
					['suidSessionToAssignData', [$post_data_keys]],
				],
			],
			$next_triger	=> [
				'next'	=> [
					[static::MakeUrl($next_action[0], $next_action[1], $next_parameter)],
				],
			],
			$back_triger	=> [
				'action'	=> [
					['suidSessionToPostData', [$post_data_keys]],
				],
				'next'	=> [
					[static::MakeUrl($back_action[0], $back_action[1], $back_parameter)],
				],
			],
		];
	}

	/**
	 * SuidTransaction展開時のセーブ処理を一括して行います。
	 *
	 * @param	$save_function	callback	セーブ関数
	 * @param	$data			mixed		セーブするデータ
	 * @param	$suid_check		bool		suidの検証をするかどうか
	 */
	public function saveSuidTransaction ($save_function, $data, $suid_check = true, $args = []) {
		if (!static::SeesionOnSu()) {
			return null;
		}

		if ($suid_check === false && !static::EnableSuidSession()) {
			return null;
		}

		if (is_callable($data)) {
			$data = $data();
		}

		try {
			static::Begin();

			if (is_string($save_function) && strpos($save_function, '::') !== false) {
				//PHPネイティブでは指定できないが、'クラスパス::メソッド名'の指定に合わせるための処理
				$save_function = explode('::', $save_function, 2);
			} else if (is_callable($save_function)) {

			} else {
				//インスタンスメソッドを指定する場合
				$save_function = [$this, $save_function];
			}
			$ret = call_user_func_array($save_function, array_merge([$data], (array) $args));
		} catch (\Exception $e) {
			static::DeleteSuidSession();
			static::Rollback();
			throw $e;
		}

		static::DeleteSuidSession();
		static::Commit();

		return $ret;
	}
}
