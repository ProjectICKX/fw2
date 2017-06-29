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
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\other\pager;

use ickx\fw2\core\exception\CoreException;

/**
 * ページャーを実現するためのユーティリティクラスです。
 *
 * @category	Flywheel2
 * @package		other
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class PagerUtility {
	/** @var	int	デフォルトの1ページあたりの表示数 */
	const DISP_PER_PAGE	= 10;

	/**
	 * 利用できる最大ページ数を返します。
	 *
	 * @param	int		$total_count	総件数
	 * @param	int		$limit			1ページあたりの要素数
	 * @return	int		利用できる最大ページ数
	 */
	public static function GetMaxPage ($total_count, $limit) {
		//最大ページ数算出
		return ((int) ceil($total_count / $limit));
	}

 	/**
	 * ページャーデータを作成します。
	 *
	 * @param	int		$total_count	総件数
	 * @param	int		$current_page	現在のページ
	 * @param	int		$limit			1ページあたりの要素数
	 * @param	array	$options		その他オプション
	 * @return	array	ページャー用データ
	 */
	public static function CreatePagerData ($total_count, $current_page, $limit, $options) {
		//==============================================
		//件数が0件の場合は空配列を返す
		//==============================================
		if ($total_count == 0) {
			return [];
		}

		//==============================================
		//オプションの取得と展開
		//==============================================
		$in_list_position	= $options['in_list_position'] ?? 3;
		$max_link_disp		= $options['max_link_disp'] ?? 5;

		//==============================================
		//ページ数の構成
		//==============================================
		//最大ページ数算出
		$max_page = static::GetMaxPage($total_count, $limit);

		//念のための検証
		if ($current_page < 1) {
			CoreException::RaiseSystemError('最小ページ数以下のページ番号を指定されました。page:%d', [$current_page]);
		}

		if ($max_page < $current_page) {
			CoreException::RaiseSystemError('最大ページ数以上のページ番号を指定されました。page:%d, max_page:%s', [$current_page, $max_page]);
		}

		//リンクリスト用ページ番号の構築
		//開始位置
		if ($current_page < $in_list_position) {
			$link_start_page	= 1;
		} else {
			$link_start_page	= $current_page - ceil($in_list_position / 2);
			if ($link_start_page + $max_link_disp >= $max_page) {
				$link_start_page	= $max_page - $max_link_disp + 1;
			}
		}

		if ($link_start_page < 1) {
			$link_start_page = 1;
		}

		//終了位置
		$link_end_page = $link_start_page + $max_link_disp - 1;
		if ($link_end_page > $max_page) {
			$link_end_page = $max_page;
		}

		//ページ数構成
		$page	= [
			'start'		=> 1,
			'end'		=> $max_page,
			'current'	=> $current_page,
			'next'		=> ($current_page < $max_page) ? $current_page + 1 : null,
			'previous'	=> ($current_page > 1) ? $current_page - 1 : null,
			'list'		=> range($link_start_page, $link_end_page),
			'round'		=> [
				'start'		=> $link_start_page > 1,
				'end'		=> $link_end_page < $max_page,
			],
			'is_first'		=> $current_page == 1,
			'is_last'		=> $current_page == $max_page,
			'enable'		=> $total_count > $limit,
		];

		//==============================================
		//件数の構成
		//==============================================
		//件数の構成
		$count	= [
			'total'		=> $total_count,
			'start'		=> ($max_page == 1) ? 1 : ($current_page - 1) * $limit + 1,
			'end'		=> ($max_page == $current_page) ? $total_count : $current_page * $limit
		];

		//==============================================
		//結果の返却
		//==============================================
		return compact('page', 'count');
	}
}
