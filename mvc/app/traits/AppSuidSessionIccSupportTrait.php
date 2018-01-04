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
 * AppWebIccSupportTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppSuidSessionIccSupportTrait {

	public function buildSuidSessionChoiceValidateRule ($lazy_evals, $check_target = false) {
		if (is_null($validater = ($check_target ? ($lazy_evals['target'] ?? null) : null) ?? $lazy_evals['validate'] ?? null)) {
			return [];
		}
		return static::ActionBuilder('assign')->params('validate_rule_list', $this->promise($validater));
	}

	public function buildSimpleSearchFormRule ($lazy_evals) {
		return [
			static::MEAN_DEFAULT	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'validate'	=> $lazy_evals['validate'],
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals),
						],
						$lazy_evals['action'] ?? []
					),
					'error'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals),
						],
						$lazy_evals['error_action'] ?? $lazy_evals['action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
				];
			},
		];
	}

	/**
	 * SUIDセッション 入力画面用 ルールクリエイタ
	 *
	 * @param	array	$lazy_evals	遅延評価値のリスト
	 *	[
	 *		'action'			=> 画面初期表示時に実行する処理のリスト
	 *		'validate'			=> バリデーションルール
	 *		'next_pre_action'	=> 次アクションへ移動する際にデータをセッションに詰める前に実行する処理のリスト
	 *		'next_post_action'	=> 次アクションへ移動する際にデータをセッションに詰めた後に実行する処理のリスト
	 *		'next_controller'	=> バリデーション成功時に遷移するコントローラ
	 *		'next_action'		=> バリデーション成功時に遷移するアクション名
	 *		'next_path_params'	=> バリデーション成功時に使用するパスパラメータ
	 *		'next_params'		=> バリデーション成功時に使用するパラメータ
	 *		'error_action'		=> バリデーションエラー時に実行する処理のリスト
	 *	]
	 * @param	string	$trigger	ネクストアクション実行トリガー名
	 * @return	array	ルールリスト
	 */
	public function buildSuidSessionInputRule ($lazy_evals, $next_trigger = null) {
		return [
			static::MEAN_DEFAULT	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						[
							['StartSuidSession'],
							$this->buildSuidSessionChoiceValidateRule($lazy_evals),
						],
						$lazy_evals['action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
				];
			},
			$next_trigger ?? 'submit'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'validate'	=> $lazy_evals['validate'],
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals),
						],
						$lazy_evals['next_pre_action'] ?? [],
						['postDataToSuidSession', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['validate']) ? $lazy_evals['validate']() : $lazy_evals['validate']);}]],
						$lazy_evals['next_post_action'] ?? []
					),
					'next'	=> [
						[static::MakeUrl($lazy_evals['next_controller'] ?? $this, $lazy_evals['next_action'], $lazy_evals['next_path_params'] ?? [], $lazy_evals['next_params'] ?? [])],
					],
					'error'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals),
						],
						$lazy_evals['error_action'] ?? $lazy_evals['action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
				];
			},
		];
	}

	/**
	 * SUIDセッション 確認画面用 ルールクリエイタ
	 *
	 * @param	array	$lazy_evals	遅延評価値のリスト
	 *	[
	 *		'action'			=> 画面初期表示時に実行する処理のリスト
	 *		'validate'			=> バリデーションルール
	 *		'target'
	 *		'next_pre_action'	=> 次アクションへ移動する際にデータをセッションに詰める前に実行する処理のリスト
	 *		'next_controller'	=> バリデーション成功時に遷移するコントローラ
	 *		'next_action'		=> バリデーション成功時に遷移するアクション名
	 *		'next_path_params'	=> バリデーション成功時に使用するパスパラメータ
	 *		'next_params'		=> バリデーション成功時に使用するパラメータ
	 *		'back_pre_action'	=> 前アクションへ移動する際にデータをポストデータに詰める前に実行する処理のリスト
	 *		'back_post_action'	=> 前アクションへ移動する際にデータをポストデータに詰めた後に実行する処理のリスト
	 *		'back_controller'	=> 前コントローラ
	 *		'back_action'		=> 前アクション名
	 *		'back_path_params'	=> 前画面へ戻るときに使用するパスパラメータ
	 *		'back_params'		=> 前画面へ戻るときに使用するパラメータ
	 *	]
	 * @param	string	$trigger	ネクストアクション実行トリガー名
	 * @return	array	ルールリスト
	 */
	public function buildSuidSessionConfirmRule ($lazy_evals, $next_trigger = null, $back_trigger = null) {
		return [
			static::MEAN_DEFAULT	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'validate'	=> $lazy_evals['validate'] ?? [],
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals, true),
							['suidSessionToAssignData', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['target']) ? $lazy_evals['target']() : $lazy_evals['target']);}]],
						],
						$lazy_evals['action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
				];
			},
			$next_trigger ?? 'submit'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'post_process'	=> $lazy_evals['post_process'] ?? [],
					'next'	=> [
						[static::MakeUrl($lazy_evals['next_controller'] ?? $this, $lazy_evals['next_action'], $lazy_evals['next_path_params'] ?? [], $lazy_evals['next_params'] ?? [])],
					],
				];
			},
			$back_trigger ?? 'back'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						$lazy_evals['back_pre_action'] ?? [],
						[
							['suidSessionToPostData', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['target']) ? $lazy_evals['target']() : $lazy_evals['target']);}]],
							['overWritePostData', ['has_validator', true]],
						],
						$lazy_evals['back_post_action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
					'next'	=> [
						[static::MakeUrl($lazy_evals['back_controller'] ?? $this, $lazy_evals['back_action'], $lazy_evals['back_path_params'] ?? [], $lazy_evals['back_params'] ?? [])],
					],
				];
			},
		];
	}

	/**
	 * SUIDセッション 完了画面用 ルールクリエイタ
	 *
	 * @param	array	$lazy_evals	遅延評価値のリスト
	 *	[
	 *		'validate'			=> バリデーションルール
	 *		'target'
	 *		'pre_action'	=> セーブ前に実行する処理のリスト
	 *		'post_action'	=> セーブ後に実行する処理のリスト
	 *		'save_filter'	=> セーブ対象のデータに対する最終フィルタ
	 *		'save_function'	=> セーブ関数
	 *		'save_args'		=> 実際に保存するデータ以外のセーブ関数の引数
	 *	]
	 * @param	string	$trigger	ネクストアクション実行トリガー名
	 * @return	array	ルールリスト
	 */
	public function buildSuidSessionCompleteRule ($lazy_evals, $next_trigger = null, $back_trigger = null) {
		return [
			static::MEAN_DEFAULT	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'validate'	=> $lazy_evals['validate'] ?? [],
					'pre_process'	=> $lazy_evals['pre_process'] ?? [],
					'action'	=> array_merge(
						[
							$this->buildSuidSessionChoiceValidateRule($lazy_evals, true),
						],
						$lazy_evals['pre_action'] ?? [],
						[
							static::ActionBuilder('suidSessionToAssignData')->params(array_keys(is_callable($lazy_evals['target']) ? $lazy_evals['target']() : $lazy_evals['target']))->alias('assign_data'),
							isset($lazy_evals['save_filter']) && is_callable($lazy_evals['save_filter']) ? static::ActionBuilder($lazy_evals['save_filter'])->params($this->bind('assign_data')) : function () {},
							static::ActionBuilder('saveSuidTransaction')->params($lazy_evals['save_function'] ?? null, $this->bind('assign_data'), $lazy_evals['suid_check'] ?? true, $lazy_evals['save_args'] ?? [])->alias('data'),
						],
						$lazy_evals['post_action'] ?? []
					),
					'post_process'	=> $lazy_evals['post_process'] ?? [],
				];
			},
		];
	}
}
