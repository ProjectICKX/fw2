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
 * @package		date_time
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\date_time\interfaces;

/**
 * 日付処理定数管理インターフェース
 *
 * @category	Flywheel2
 * @package	container
 * @author		wakaba <wakabadou@gmail.com>
 * @license	http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
interface IDateTimeConst {
	//==============================================
	//ハイフン区切り
	//==============================================
	const YMD_HIS		= "Y-m-d H:i:s";
	const YMD_HI		= "Y-m-d H:i";
	const YMD_H			= "Y-m-d H";
	const YMD			= "Y-m-d";
	const YM			= "Y-m";
	const Y				= "Y";

	const MD_HIS		= "m-d H:i:s";
	const MD_HI			= "m-d H:i";
	const MD_H			= "m-d H";
	const MD			= "m-d";
	const M				= "m";

	const D_HIS			= "d H:i:s";
	const D_HI			= "d H:i";
	const D_H			= "d H";
	const D				= "d";

	const HIS			= "H:i:s";
	const HI			= "H:i";
	const H				= "H";

	//==============================================
	//スラッシュ区切り
	//==============================================
	const S_YMD_HIS		= "Y/m/d H:i:s";
	const S_YMD_HI		= "Y/m/d H:i";
	const S_YMD_H		= "Y/m/d H";
	const S_YMD			= "Y/m/d";
	const S_YM			= "Y/m";

	const S_MD_HIS		= "m/d H:i:s";
	const S_MD_HI		= "m/d H:i";
	const S_MD_H		= "m/d H";
	const S_MD			= "m/d";
}
