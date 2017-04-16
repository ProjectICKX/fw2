<?php

$detect_encoding = function ($string) {
	return mb_detect_encoding($string, ['UTF-8', 'SJIS-win', 'eucJP-win', 'JIS', 'ASCII'], true);
};

$escape = function ($string, $detect_encoding) {
	if (PHP_SAPI === 'cli') {
		return  (\PHP_OS === 'WINNT' || \PHP_OS === 'WIN32') && 'SJIS-win' !== $detect_encoding ? mb_convert_encoding($string, 'SJIS-win', $detect_encoding) : $string;
	}
	return htmlspecialchars($string, \ENT_QUOTES, $detect_encoding);
};

$message_encoding	= $detect_encoding($message);
$trace_encoding		= $detect_encoding($trace);
$request_url		= PHP_SAPI === 'cli' ? (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '<empty>') : $_SERVER['REQUEST_URI'];
$request_time		= date('Y-m-d H:i:s ', $_SERVER['REQUEST_TIME']) . explode('.', $_SERVER['REQUEST_TIME_FLOAT'])[1];
$current_time		= explode('.', microtime(true));;
$current_time		= date('Y-m-d H:i:s ', $current_time[0]) . $current_time[1];

$cli_template = <<<'EOL'
================
 System Error!!
================
request uri :%5$s
request time:%3$s
current time:%4$s

----------------
 message
----------------
%1$s

----------------
 stack trace
----------------
%2$s
EOL;

$html_template = <<<'EOL'
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=%6$s">
<title>System Error!!</title>
</head>
<body>

<table border="1">
	<tr>
		<th>request uri</th>
		<td>%5$s</td>
	</tr>
	<tr>
		<th>request time</th>
		<td>%3$s</td>
	</tr>
	<tr>
		<th>current time</th>
		<td>%4$s</td>
	</tr>
	<tr>
		<th>message</th>
		<td><pre>%1$s</pre></td>
	</tr>
	<tr>
		<th>stack trace</th>
		<td><pre>%2$s</pre></td>
	</tr>
</table>

</body>
</html>
EOL;

vprintf(PHP_SAPI === 'cli' ? $cli_template : $html_template, [$escape($message, $message_encoding), $escape($trace, $trace_encoding), $request_time, $current_time, $request_url, $message_encoding]);
