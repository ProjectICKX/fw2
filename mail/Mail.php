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
 * @package		compression
 * @author		wakaba <wakabadou@gmail.com>
 * @copyright	2011- Wakabadou honpo (http://www.wakabadou.net/) / Project ICKX (http://www.ickx.jp/)
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */

namespace ickx\fw2\mail;

/**
 * Mailを扱うクラスです。
 *
 * @category	Flywheel2
 * @package		mail
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class Mail {
	public static function Send ($to, $subject, $message, $options = []) {
 		$additional_headers		= isset($options['additional_headers']) ? (array) $options['additional_headers'] : [];
 		$additional_parameters	= isset($options['additional_parameters']) ? (array) $options['additional_parameters'] : [];
 		$attachments			= isset($options['attachments']) ? (array) $options['attachments'] : [];

 		if (count($attachments) > 0) {
			$boundary = md5(uniqid(rand(),1));
			$boundary_pattern = sprintf('--%s', $boundary);
			$boundary_end_pattern = sprintf('--%s--', $boundary);

			$additional_headers[] = sprintf('Content-Type: multipart/mixed;boundary="%s"', $boundary);

			$mail_body = [
				$boundary_pattern,
				'Content-Type: text/plain; charset="ISO-2022-JP"',
				'',
				$message,
				'',
			];

			foreach ($attachments as $attachment) {
				clearstatcache(true, $attachment);
				if (!file_exists($attachment)) {
					return false;
				}
				$file_name = basename($attachment);

				$mime_type = 'text/html';

				$mail_body[] = $boundary_pattern;
				$mail_body[] = sprintf('Content-Type: %s; name="%s"', $mime_type, $file_name);
				$mail_body[] = sprintf('Content-Disposition: attachment; filename="%s"', $file_name);
				$mail_body[] = 'Content-Transfer-Encoding: base64';
				$mail_body[] = '';
				$mail_body[] = chunk_split(base64_encode(file_get_contents($attachment)));
				$mail_body[] = $boundary_end_pattern;
			}

			$mail_body = implode("\n", $mail_body);
		} else {
			$mail_body = $message;
		}

		if (count($additional_headers) > 0) {
			$additional_headers = implode("\r\n", $additional_headers);
		} else {
			$additional_headers = '';
		}

		if (count($additional_parameters) > 0) {
			$additional_parameters = implode("\r\n", $additional_parameters);
		} else {
			$additional_parameters = '';
		}
		return mail($to, $subject, $mail_body, $additional_headers, $additional_parameters);
	}
}
