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

namespace ickx\fw2\mvc\app\controllers\traits\pager;

/**
 * Flywheel2 Pager特性です。
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
trait PagerTrait {
 	/** @var	array	ページャーデータ */
 	public $pager	= null;

 	/**
 	 * 有効なページを取得します。
 	 *
 	 * @param	int		$total_count	全体のカウント数
 	 * @param	int		$current_page	現在のページ
 	 * @param	int		$limit			1ページあたりのカウント数
 	 * @param	array	$options		その他オプション
 	 * @return	int		有効な現在のページ
 	 */
 	public function getAvailablePage ($total_count, $current_page = 1, $limit = PagerUtility::DISP_PER_PAGE, $options = []) {
 		$max_page = PagerUtility::GetMaxPage($total_count, $limit);
 		return ($current_page <= $max_page) ? $current_page : $max_page;
 	}

 	/**
 	 * ページャーとして必要なデータを返します。
 	 *
 	 * @param	int		$total_count	全体のカウント数
 	 * @param	int		$current_page	現在のページ
 	 * @param	string	$url			ページャー用URL
 	 * @param	string	$sorter_url		ソーター用URL
 	 * @param	int		$limit			1ページあたりのカウント数
 	 * @param	array	$options		その他オプション
 	 */
 	public function setPager ($total_count, $current_page, $url, $limit = PagerUtility::DISP_PER_PAGE, $options = []) {
 		$current_page	= $current_page ?: 1;
 		$options		= [
			'in_list_position'	=> Arrays::AdjustValue($options, 'in_list_position',	3),
			'max_link_disp'		=> Arrays::AdjustValue($options, 'max_link_disp',		5),
 		];
 		$this->pager = PagerUtility::CreatePagerData($total_count, $current_page, $limit, $options);
 		$this->pager['url']	= $url;
 	}
}
