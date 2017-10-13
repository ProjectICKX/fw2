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
 * @category	Flywheel2 demo
 * @package		commons
 * @author		wakaba
 * @copyright	Copyright 2012, Project ICKX. (http://www.ickx.jp/)
 * @license		require consultation
 * @varsion		0.0.1
 */

namespace ickx\fw2\extensions\twig\token_parser;

use ickx\fw2\extensions\twig\node\Twig_Node_UseClass;

/**
 * use class.
 *
 * <pre>
 * {% use_const "path to class" as "alias" %}
 * </pre>
 */
final class Twig_TokenParser_UseClass extends \Twig_TokenParser {
	public function parse(\Twig_Token $token) {
		$class_path = $this->parser->getExpressionParser()->parseStringExpression();
		$lineno	= $token->getLine();

		$stream = $this->parser->getStream();
		if ($stream->nextIf(\Twig_Token::NAME_TYPE, 'as')) {
			$alias = $this->parser->getExpressionParser()->parseMultitargetExpression()->getNode(0);
		} else {
			$string_class_path = $class_path->getattribute('value');
			$alias = new \Twig_Node_Expression_Constant(mb_substr($string_class_path, mb_strrpos($string_class_path, "\\") + 1), $lineno);
		}
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

		return new Twig_Node_UseClass($class_path, $alias, $lineno, $this->getTag());
	}

	public function getTag() {
		return 'use_class';
	}
}
