<?php

namespace ickx\fw2\extensions\twig\node;

class Twig_Node_UseClass extends \Twig_Node {
	public function __construct($class_path, $alias, $lineno, $tag = null) {
		parent::__construct(
			['class_path'	=> $class_path, 'alias'	=> $alias],
			['safe'			=> false],
			$lineno,
			$tag
		);
	}

	public function compile(\Twig_Compiler $compiler) {
		$compiler->addDebugInfo($this);

		$compiler->write("\\ickx\\fw2\\extensions\\twig\\Twig_Extension_Store::set('use_class', ");
		$compiler->subcompile($this->getNode('alias'), true);
		$compiler->raw(', ');
		$compiler->subcompile($this->getNode('class_path'), true);
		$compiler->raw(');');
		$compiler->raw("\n");
	}
}
