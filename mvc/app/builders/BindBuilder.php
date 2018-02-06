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

namespace ickx\fw2\mvc\app\builders;

/**
 * Action
 *
 * @category	Flywheel2
 * @package		mvc
 * @author		wakaba <wakabadou@gmail.com>
 * @license		http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion		2.0.0
 */
class BindBuilder {
	protected $_controller	= null;
	protected $_command		= null;
	protected $_options		= null;
	protected $_pinchs		= [];

	protected const TYPE_RENDER_VAR	= 'type_render_var';
	protected const TYPE_VAR		= 'type_var';
	protected const TYPE_PROMISE	= 'type_promise';

	public function __construct() {
	}

	public function executer ($controller, $command, $options = []) {
		$this->_controller	= $controller;
		$this->_command		= $command;
		$this->_options		= $options;
		return $this;
	}

	public function typeRenderVar () {
		$this->_options['type']	= static::TYPE_RENDER_VAR;
		return $this;
	}

	public function typeVar () {
		$this->_options['type']	= static::TYPE_VAR;
		return $this;
	}

	public function typePromise () {
		$this->_options['type']	= static::TYPE_PROMISE;
		return $this;
	}

	public function params (...$args) {
		$this->_options['args'] = $args;
		return $this;
	}

	public function pinch ($pinchs) {
		$this->_pinchs	= array_merge($this->_pinchs, (array) $pinchs);
		return $this;
	}

	public function __invoke () {
		$command	= $this->_command;
		$type		= $this->_options['type'] ?? null;

		switch ($type) {
			case static::TYPE_VAR:
				foreach ($this->_pinchs as $pinch) {
					if (is_array($command)) {
						$command = $command[$pinch];
					} else if (is_object($command)) {
						$command = $command->{$pinch};
					}
				}
				return $command;
			case static::TYPE_RENDER_VAR:
				$command	= $this->_controller->render->$command ?? $this->_options['default'] ?? null;
				foreach ($this->_pinchs as $pinch) {
					if (is_array($command)) {
						$command = $command[$pinch];
					} else if (is_object($command)) {
						$command = $command->{$pinch};
					}
				}
				return $command;
			case static::TYPE_PROMISE:
				if (is_string($command)) {
					if (method_exists($this->_controller, $command)) {
						$command = [$this->_controller, $command];
					}
				}
				$args = [];
				foreach ($this->_options['args'] ?? [] as $idx => $arg) {
					if (is_callable($arg)) {
						$args[$idx] = $arg();
					} else {
						$args[$idx] = $arg;
					}
				}
				$command	= $command(...$args);
				foreach ($this->_pinchs as $pinch) {
					if (is_array($command)) {
						$command = $command[$pinch];
					} else if (is_object($command)) {
						$command = $command->{$pinch};
					}
				}
				return $command;
		}

		if (is_string($command)) {
			$command	= $this->_controller->render->$command ?? $this->_options['default'] ?? $command;
			foreach ($this->_pinchs as $pinch) {
				if (is_array($command)) {
					$command = $command[$pinch];
				} else if (is_object($command)) {
					$command = $command->{$pinch};
				}
			}
			return $command;
		}

		if (is_callable($command)) {
			$command	= $command(...($this->_options['args'] ?? []));
			foreach ($this->_pinchs as $pinch) {
				if (is_array($command)) {
					$command = $command[$pinch];
				} else if (is_object($command)) {
					$command = $command->{$pinch};
				}
			}
			return $command;
		}

		foreach ($this->_pinchs as $pinch) {
			if (is_array($command)) {
				$command = $command[$pinch];
			} else if (is_object($command)) {
				$command = $command->{$pinch};
			}
		}
		return $command;
	}
}
