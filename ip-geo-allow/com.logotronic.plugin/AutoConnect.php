<?php
namespace Com\Logotronic\Plugin\v0;
defined ('ABSPATH') || die(-1);

if (!class_exists (__NAMESPACE__.'\AutoConnect')) {

	final class AutoConnect {

		/* Object Namespace */
		private $namespace;

		/* Object Instance */
		private $object;

		/* Plugin main file */
		private $file;

		/* $this->Object instance __CLASS__ */
		private $class;

		/* $this->Class Parent Root Class */
		private $root;

		private $auto_register_done = false;
		private $auto_callback_done = false;

		private $auto_hook_method_prefix = 'on';
		private $auto_hook_method_sufix = array ('hook', 'action', 'filter', 'event');

		private $requiredWpVersion = null;
		private $requiredPhpVersion = null;

		final public function __construct ($object, $connect = false) {

			// Store object argument
			$this->object = $object;

			/* 	$Object value MUST be of object-type (class instance) */
			if (!is_object ($this->object)) {
				$msg = __CLASS__.'__construct: argument is not of object-type.';
				error_log ($msg);
				WP_Die($msg);
			}

			// Get __CLASS__
			$this->class = get_class($this->object);

			// Use Reflection To Get __FILE__ And Root __CLASS__
			$reflection = new \ReflectionClass($this->class);

			// Get Root Class __CLASS__
			$reflParent = $reflection; // keep $reflection unchanged
			while ($reflParent = $reflParent->getParentClass()) {
				$this->root = $reflParent->getName();
			}

			/* Extract Namespace From __CLASS__ */
			$classpath = explode ('\\', $this->class);
			$classbase = array_pop ($classpath);
			$this->namespace = implode ('\\', $classpath);

			if (!$this->isCallableStatic ('getFile')) {
				$msg = __CLASS__.'__construct: argument object has no getFile() method.';
				error_log ($msg);
				WP_Die($msg);
			}

			$this->file = $object::getFile();

		}


		/* Automagically use Special Method Names To Register Into WordPress Plugin Registration Hooks
			Special Method Names: 
				onPluginActivate, 
				onPluginDeactivate, 
				onPluginUninstall
		*/
		public function autoRegister () {
			if ($this->auto_register_done === false) {
				if (is_admin()) {
					if ($this->isCallableMethod ('onPluginActivate'))    register_activation_hook ($this->file, $this->getCallable ('onPluginActivate'));
					if ($this->isCallableMethod ('onPluginDeactivate'))  register_deactivation_hook ($this->file, $this->getCallable ('onPluginDeactivate'));
					if ($this->isCallableStatic ('onPluginUninstall'))   register_uninstall_hook ($this->file, $this->getCallable ('onPluginUninstall'));
				}
				$this->auto_register_done = true;
			}
		}

		/* Auto-magically Use Dedicated Method Names As Handlers For WordPress Hooks (Actions/Filters)
			Recognised Method Names: on_(Required: {%HookName%})[Optional: {_%priority%}](Required: _{%hook|action|filter|event%})
			Ie 'init' action: on_init_action, on_init_hook, on_init_filter, on_init_0_action, on_init_100_event .....
		*/
		public function autoCallback () {
			if ($this->auto_callback_done === false) {

				// Get&Connect Only Public Methods Available
				$methods = get_class_methods ($this->class);

				foreach ($methods as $method) {

					$method_items = explode ('_', $method);

					if (count ($method_items) >= 3
					&& (array_shift ($method_items) === $this->auto_hook_method_prefix)
					&& in_array (strtolower (array_pop ($method_items)), $this->auto_hook_method_sufix)) {

						$priority = count ($method_items) > 1 && is_numeric (end ($method_items)) ? (int) array_pop ($method_items) : false;
						$hook = implode ('_', $method_items);
						if ($priority === false) {
							$this->addWPCallback ($hook, $method);
						} else {
							$this->addWPCallback ($hook, $method, $priority);
						}
					}
				}
				$this->auto_callback_done === true;
			}
		}

		/* Private Methods */
		private function addWPCallback ($hook, $method, $priority = 10, $arguments = null) {
			$callable = $this->getCallable ($method);
			// Test With IsHooked To Prevent Hooking $method On Same Hook More Than Once
			if ($callable !== false & self::isHookHooked ($hook, $callable) === false) {
				if (!isset($arguments)) {
					$arguments = $this->getArgumentCount($method);
				}
				//error_log ("add_filter (\"$hook\", \"$method\", $priority, $arguments)");
				return add_filter ($hook, $callable, $priority, $arguments);
			}
			return false;
		}

		private function isCallableMethod ($method) {
			if (method_exists ($this->class, $method)) {
				$r = new \ReflectionMethod($this->class, $method);
				return $r->isPublic()
					&& !$r->isConstructor()
					&& !$r->isDestructor() ?
						$r : false;
			}
			return false;
		}

		private function isCallableStatic ($method) {
			$r = $this->isCallableMethod ($method);
			if ($r !== false) {
				return $r->isStatic()? $r : false;
			}
			return false;
		}

		private function getCallable ($method) {
			$r = $this->isCallableMethod ($method);
			if ($r !== false) {
				if ($r->isStatic()) {
					return array ($this->class, $method);
				}
				return array ($this->object, $method);
			}
			return false;
		}

		private function getArgumentCount ($method) {
			$r = $this->isCallableMethod ($method);
			if ($r !== false) {
				return count($r->getParameters());
			}
			return false;
		}

		/* Public Static Methods */
		public static function isHookActive ($hook) {
			return isset ($GLOBALS['wp_filter']) 
			&& is_array ($GLOBALS['wp_filter']) 
			&& array_key_exists ($hook, $GLOBALS['wp_filter']);
		}

		public static function isHookHooked ($hook, $callable = false) {
			return has_filter ($hook, $callable);
		}
	}

}
