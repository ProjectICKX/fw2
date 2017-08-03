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
 * AppOneTimeAccessParamTrait
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait AppOneTimeAccessParamTrait {
	/**
	 * One Time Token用オブジェクトをアサインするためのアクションを返します。
	 *
	 * @param	\ickx\fw2\other\token\LazyToken	$token		トークン管理オブジェクト
	 * @param	string										$token_name	トークン名
	 * @return	\ickx\fw2\mvc\app\builders\ActionBuilder	One Time Token用オブジェクトをアサインするためのアクション
	 */
	public function buildOneTimeAccessSetAction (\ickx\fw2\other\token\LazyToken $token, $token_name = null) {
		return static::ActionBuilder('assign')->params([$token_name ?? $token->tokenName() ?? 'token', $token]);
	}

	/**
	 * Ont Time Tokenを確認し、有効な場合にアクションを実行するルールを返します。
	 *
	 * @param	\ickx\fw2\other\token\LazyToken	$token		トークン管理オブジェクト
	 * @param	callable						$lazy_evals	遅延実行値
	 * @param	string							$token_name	トークン名
	 * @param	string							$trigger	イベント発火トリガー名
	 * @return	array							ルール
	 */
	public function buildOneTimeActionRule (\ickx\fw2\other\token\LazyToken $token, $lazy_evals, $token_name = null, $trigger = null) {
		return [
			$trigger ?? static::MEAN_DEFAULT	=> function () use ($token, $lazy_evals, $token_name) {
				$token_name = $token_name ?? $token->tokenName() ?? 'token';

				$lazy_evals		= $lazy_evals();
				$verify_options	= $lazy_evals['verify_options'];

				$rule = [
					'validate'	=> [
						'id'	=> [
							'source'	=> 'route',
							'title'		=> 'ID',
							['require'],
						],
						'token'	=> [
							'source'	=> 'parameter',
							'title'		=> 'トークン',
							['require'],
							['callback', function ($value, $args, $options, $meta) use ($token) {
								return $token->exists($value);
							}, 'message' => '存在しない{:title}を渡されました。', 'raise_exception'],
							['callback', function ($value, $args, $options, $meta) use ($token, $verify_options) {
								return $token->verify($value, $verify_options);
							}, 'message' => '不正な{:title}を渡されました。', 'raise_exception'],
						],
					],
					'action'	=> array_merge(
						$lazy_evals['pre_action'] ?? [],
						[
							[function () use ($token) {return $token->destroyToken();}],
						],
						$lazy_evals['post_action'] ?? []
					),
				];

				if (isset($lazy_evals['next'])) {
					$rule['next'] = $lazy_evals['next'];
				}

				return $rule;
			},
		];
	}
}
