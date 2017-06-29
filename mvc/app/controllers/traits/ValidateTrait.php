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
 * 検証特性
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait ValidateTrait {
	public $hasValidator	= false;

	/**
	 * 許可したリクエストパラメータ以外のリクエストパラメータを削除します。
	 */
	public function allowedRequestParameter () {
		if ($this->rule->allowed === null) {
			if (isset($this->rule->allowed['parameter'])) {
				foreach (array_keys($this->request->parameter->getArrayCopy()) as $parameter) {
					if (!in_array($parameter, $this->rule->allowed['parameter'], true)) {
						Request::RemoveParameter($parameter);
						unset($this->request->parameter->$parameter);
					}
				}
			}

			if (isset($this->rule->allowed['data'])) {
				foreach (array_keys($this->request->data->getArrayCopy()) as $data_name) {
					if (!in_array($data_name, $this->rule->allowed['data'], true)) {
						Request::RemovePostData($data_name);
						unset($this->request->data->$data_name);
					}
				}
			}

			if (isset($this->rule->allowed['route'])) {
				foreach (array_keys($this->route->getArrayCopy()) as $data_name) {
					if (!in_array($data_name, $this->rule->allowed['route'], true)) {
						unset($this->route->$data_name);
					}
				}
			}
		}
	}

	/**
	 * 実際の検証処理を行います。
	 *
	 * @return	array	検証結果 検証を全て通過している場合は空配列 エラーがある場合はエラー名をキーとしたエラーメッセージのリスト
	 */
	public function validate ($validate_rule = null, $data_set = [], $force_source = false) {
		//結果の初期化
		$result = [];

		//現在のバリデーションルールを取得
		$validate_rule = $validate_rule ?? $this->rule->validate ?? null;
		if (is_callable($validate_rule)) {
			$validate_rule = $validate_rule();
		}

		//実行対象のバリデーションがあるか確認、なければ対象はなかったとして終了
		if (is_null($validate_rule)) {
			$this->hasValidator = false;
			return [];
		}
		$this->hasValidator = true;

		//外部入力値を取得
		$request_access = LazyArrayObject::Create([
			'parameter'	=> Request::GetParameters(),
			'data'		=> Request::GetPostData(),
			'post'		=> Request::GetPost(),
			'upload'	=> Request::GetUploadFileData(),
			'cookie'	=> Request::GetCookies(),
			'route'		=> $this->route,
			'direct'	=> $data_set,
		]);

		//検証の実行：入力一要素ごとに処理を行う
		foreach (array_filter(($validate_rule instanceof \ArrayObject) ? $validate_rule->getArrayCopy() : $validate_rule) as $target => $rules) {
			//短縮設定を掬い取る
			$rule_length = count($rules);
			for ($i = 0;$i < $rule_length;$i++) {
				$data_set = array_slice($rules, $i, 1);
				$idx = key($data_set);
				$rule = current($data_set);
				if (is_int($idx) && is_string($rule)) {
					$rules = array_merge(array_slice($rules, 0, $i), [$rule => true], array_slice($rules, $i + 1));
				}
			}

			//先行する検証の結果を参照するかどうか
			$premise = $rules['premise'] ?? null;
			if ($premise) {
				foreach (Arrays::AdjustArray($premise) as $error_name) {
					//一つでもエラーとなっている先行検証結果がある場合、処理そのものをスキップ
					if (isset($result[$error_name])) {
						continue 2;
					}
				}
			}

			//データ取得対象ソースの指定
			$source = $force_source ?: ($rules['rules']['source'] ?? $rules['source'] ?? (Request::IsPostMethod() ? 'data' : 'parameter'));
			$data = $request_access->$source;

			//先行確認値があった場合、突合チェック
			$pre_check_pattern = null;
			if (($position = strpos($target, '@value=')) !== false) {
				$pre_check_pattern = substr($target, $position + strlen('@value='));
				$target = substr($target, 0, $position);

				//先行入力値とマッチしなかった場合はスキップ
				if ($pre_check_pattern !== null && preg_match(sprintf("/^%s$/u", str_replace('/', "\\/", $pre_check_pattern)), $data->$target) !== 1) {
					continue;
				}
			}

			//指定があった場合、値の取得先を差し替え
			$name = $rules['name'] ?? $target;

			//検証実行：結果はどんどん末尾に追加していく
			$ret = Validator::BulkCheck($data, [$name => $rules], $result) ?? [];
			if (!empty($ret)) {
				foreach ($ret as $target => $error_message_list) {
					$result += $result + [$target => $error_message_list];
				}

				//入力要素レベルのis_lastが存在した場合、ここで処理終了となる
				if ($rules['is_last'] ?? null) {
					break;
				}
			}
		}

		//処理の終了
		return $result;
	}
}
