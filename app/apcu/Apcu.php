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
 * @package		app
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\app\apcu;

/**
 * apcu manager
 *
 * @category	Flywheel2
 * @package		app
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Apcu {
	public static function info () {
		$cache_info	= apcu_cache_info();
		$cache_list	= $cache_info['cache_list'];
		unset($cache_info['cache_list']);

		return [
			'info'	=> $cache_info,
			'list'	=> $cache_list,
			'sma'	=> apcu_sma_info(),
		];
	}

	public static function conv ($value, $key = null) {
		$number_format = function ($value) {
			$is_int = false;
			if (gettype($value) === 'integer') {
				$is_int = true;
			} else {
				$exp = explode('.', $value);
				$is_int = ($exp[1] ?? 0) == 0;
			}

			return number_format($value, $is_int ? 0 : 4);
		};

		$unit_conv = function ($value) {
			$unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
			$factor = floor((strlen($value) - 1) / 3);
			return (number_format($value / pow(1024, $factor)) . $unit[$factor] ?? 'P') . '('. number_format($value) .'Byte)';
		};

		$date = function ($value) {
			return date('Y-m-d H:i:s', $value);
		};

		$ttl = function ($value) {
			if ($value === 0) {
				return '無期限';
			}
			$datetime	= new \DateTime(date('Y-m-d H:i:s', 0));
			$current	= new \DateTime(date('Y-m-d H:i:s', $value));
			$diff		= $current->diff($datetime);

			return $diff->format('%Y年 %m月 %d日 %H時 %i分 %s秒');
		};

		$array = null;
		$array = function ($value) use ($number_format, &$array) {
			$tmp = [];
			foreach ($value as $key => $value) {
				if (in_array(gettype($value), ['integer', 'double'], true)) {
					$value = $number_format($value);
				} else if (is_array($value)) {
					$value_tmp = [];
					foreach ($value as $node_key => $node) {
						$value_tmp[$node_key] = static::conv($node, $node_key);
					}
					$value = $array($value_tmp);
				}
				$tmp[] = sprintf('%s => %s', $key, $value);
			}
			return '['. implode(', ', $tmp). ']';
		};

		$cast = [
			// global
			'num_slots'			=> $number_format,
			'ttl'				=> $ttl,
			'num_hits'			=> $number_format,
			'num_misses'		=> $number_format,
			'num_inserts'		=> $number_format,
			'num_entries'		=> $number_format,
			'expunges'			=> $number_format,
			'start_time'		=> $date,
			'mem_size'			=> $unit_conv,
			'memory_type'		=> null,
			'deleted_list'		=> $array,
			'slot_distribution'	=> $array,

			// sma
			'num_seg'			=> $number_format,
			'seg_size'			=> $unit_conv,
			'avail_mem'			=> $unit_conv,
			'block_lists'		=> $array,

			// list
			'num_hits'			=> $number_format,
			'mtime'				=> $date,
			'creation_time'		=> $date,
			'deletion_time'		=> $ttl,
			'access_time'		=> $date,
			'ref_count'			=> $number_format,
			'mem_size'			=> $unit_conv,

			//common
			'size'				=> $unit_conv,
			'offset'			=> $number_format,
		];

		$value = ($cast[$key] ?? function ($value) {
			return $value;
		})($value);

		return $value;
	}

	public static function e ($value, $key = null) {
		$value = static::conv($value, $key);

		if (!is_string($value)) {
			var_dump($value);
		} else {
			echo str_replace("\n", '<br>', htmlspecialchars($value, \ENT_QUOTES, 'UTF-8'));
		}
	}

	public static function clear () {
		return apcu_clear_cache();
	}

	public static function delete ($key) {
		return apcu_delete($key);
	}

	public static function exists ($key) {
		return apcu_exists($key);
	}

	public static function fetch ($key, &$success) {
		return apcu_fetch($key, $success);
	}

	public static function store ($key, $value = null, $ttl = 0) {
		return apcu_store($key, $value, $ttl);
	}

	public static function add ($key, $value = null, $ttl = 0) {
		return apcu_add($key, $value, $ttl);
	}

	public static function view () {
		$path = parse_url($_SERVER['REQUEST_URI'])['path'];

		switch ($_GET['apcu']) {
			case 'clear':
				$ret = static::clear();
				header('Location: ' . $path . '?apcu=list', true);
				break;
			case 'delete':
				static::delete(urldecode($_GET['key']));
				header('Location: ' . $path . '?apcu=list', true);
				break;
		}

		$info	= static::info();

?><!DOCTYPE html>
<html lang="ja">
<head>
<title>apcu easy manager</title>
</head>
<body>
<h1>apcu easy manager</h1>
<ul>
	<li><a href="<?php static::e($path) ?>?apcu=clear&ts=<?php echo microtime(true);?>">all clear</a></li>
</ul>

<h2>global</h2>
<table border="1">
	<thead>
		<tr>
			<th>key</th>
			<th>value</th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($info['info'] as $key => $value) { ?>
		<tr>
			<td><?php static::e($key) ?></td>
			<td><?php static::e($value, $key) ?></td>
		</tr>
<?php } ?>
	</tbody>
</table>

<h2>sma</h2>
<table border="1">
	<thead>
		<tr>
			<th>key</th>
			<th>value</th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($info['sma'] as $key => $value) { ?>
		<tr>
			<td><?php static::e($key) ?></td>
			<td><?php static::e($value, $key) ?></td>
		</tr>
<?php } ?>
	</tbody>
</table>

<h2>list</h2>
<table border="1">
	<thead>
		<tr>
			<th>key</th>
			<th>value</th>
<?php
$tmp = $info['list'][0] ?? [];
unset($tmp['info']);
?>
<?php foreach (array_keys($tmp) as $key) { ?>
			<th><?php static::e($key); ?></th>
<?php } ?>
		</tr>
	</thead>
	<tbody>
<?php foreach ($info['list'] as $idx => $cache) { ?>
		<tr>
			<td>
				<?php static::e(str_replace('<>', "\n", $cache['info'])); ?>
				<br>
				<a href="<?php static::e($path); ?>?apcu=delete&key=<?php echo urlencode($cache['info']); ?>&ts=<?php echo microtime(true);?>">delete</a>
			</td>
			<td><?php static::e(static::fetch($cache['info'], $success)) ?></td>
<?php unset($cache['info']); ?>
<?php 	foreach ($cache as $key => $value) { ?>
			<td><?php static::e($value, $key) ?></td>
<?php 	} ?>
		</tr>
<?php } ?>
	</tbody>
</table>

</body>
</html>
<?php
	}
}
