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

use ickx\fw2\container\DI;

/**
 * use class.
 *
 * <pre>
 * {% use_const "path to class" as "alias" %}
 * </pre>
 */
final class Twig_TokenParser_UseClass extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
    	$class_path	= $this->parser->getExpressionParser()->parseExpression()->getAttribute('value');

		$this->parser->getStream()->expect('as');
		$alias = (new \Twig_Node_Expression_AssignName($this->parser->getStream()->expect(\Twig_Token::STRING_TYPE)->getValue(), $token->getLine()))->getAttribute('name');
		$this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

		$twig_use_class = DI::GetClassVar('TwigUseClass', []);
		$twig_use_class[$alias] = $class_path;
		DI::Connect('TwigUseClass', $twig_use_class);
    }

    public function getTag()
    {
        return 'use_class';
    }
}
