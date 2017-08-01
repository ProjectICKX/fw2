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
					'action'	=> array_merge(
						[
							['StartSuidSession'],
						],
						$lazy_evals['action'] ?? []
					),
				];
			},
			$next_trigger ?? 'submit'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'validate'	=> $lazy_evals['validate'],
					'action'	=> array_merge(
						$lazy_evals['next_pre_action'] ?? [],
						['postDataToSuidSession', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['validate']) ? $lazy_evals['validate']() : $lazy_evals['validate']);}]],
						$lazy_evals['next_post_action'] ?? []
					),
					'next'	=> [
						[static::MakeUrl($lazy_evals['next_controller'] ?? $this, $lazy_evals['next_action'], $lazy_evals['next_path_params'] ?? [], $lazy_evals['next_params'] ?? [])],
					],
					'error'	=> [
						$lazy_evals['error_action'] ?? $lazy_evals['action'] ?? []
					],
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
	 *		'next_pre_action'	=> 次アクションへ移動する際にデータをセッションに詰める前に実行する処理のリスト
	 *		'next_post_action'	=> 次アクションへ移動する際にデータをセッションに詰めた後に実行する処理のリスト
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
					'action'	=> array_merge(
						[
							['suidSessionToAssignData', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['validate']) ? $lazy_evals['validate']() : $lazy_evals['validate']);}]],
						],
						$lazy_evals['action'] ?? []
					),
				];
			},
			$next_trigger ?? 'submit'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'next'	=> [
						[static::MakeUrl($lazy_evals['next_controller'] ?? $this, $lazy_evals['next_action'], $lazy_evals['next_path_params'] ?? [], $lazy_evals['next_params'] ?? [])],
					],
				];
			},
			$back_trigger ?? 'back'	=> function () use ($lazy_evals) {
				$lazy_evals = $lazy_evals();
				return [
					'action'	=> array_merge(
						$lazy_evals['next_pre_action'] ?? [],
						[
							['suidSessionToPostData', [function () use ($lazy_evals) {return array_keys(is_callable($lazy_evals['validate']) ? $lazy_evals['validate']() : $lazy_evals['validate']);}]],
							['overWritePostData', ['has_validator', true]],
						],
						$lazy_evals['next_post_action'] ?? []
					),
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
					'action'	=> array_merge(
						$lazy_evals['pre_action'] ?? [],
						[
							static::ActionBuilder('suidSessionToAssignData')->param(array_keys(is_callable($lazy_evals['validate']) ? $lazy_evals['validate']() : $lazy_evals['validate']))->alias('assign_data'),
							$lazy_evals['save_filter'] ?? null,
							static::ActionBuilder('saveSuidTransaction')->params([$lazy_evals['save_function'], null, $lazy_evals['suid_check'] ?? true, $lazy_evals['save_args'] ?? []])->bind('assign_data', 1)->alias('data'),
						],
						$lazy_evals['post_action'] ?? []
					),
				];
			},
		];
	}
}
