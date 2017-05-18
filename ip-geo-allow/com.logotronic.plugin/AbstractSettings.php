<?php
namespace Com\Logotronic\Plugin\v0;
defined ('ABSPATH') || die(-1);


if (!class_exists(__NAMESPACE__.'\AbstractSettings')) {
	abstract class AbstractSettings {

		protected $obj;
		protected $page;
		protected $basename;
		protected $optionid;
		protected $settings;
		protected $usercaps = 'manage_options';

		protected function __construct ($obj, $prefix, $basename, $optionid) {
			$this->obj = $obj;
			$this->page = $prefix;
			$this->basename = $basename;
			$this->optionid = $optionid;
			add_action ('init', array ($this, 'onWpInit'));
		}

		public function onWpInit () {
			if (static::authorised()) {
				add_action ('admin_menu', array ($this, 'addMenuPage'));
				add_action ('admin_init', array ($this, 'addPageSettings'));
				add_filter ('plugin_action_links_'.$this->basename,
					function ($links) {
						$link = '<a href="options-general.php?page='.$this->page.'">'.__('Settings').'</a>';
						array_unshift ($links, $link);
						return $links;
					}
				);
			}
		}

		public function addMenuPage () {
			if (static::authorised()) {
				$success = false;
				if (is_array ($this->settings)) {
					if (array_key_exists ('menu', $this->settings)
						&& is_array ($this->settings ['menu'])
						&& array_key_exists ('parent-slug', $this->settings ['menu'])
						&& is_string ($this->settings ['menu']['parent-slug'])
						&& trim ($this->settings ['menu']['parent-slug']) !== '') {
						$success = add_submenu_page (
							$this->settings ['menu']['parent-slug'],
							$this->settings ['page']['title'], // Page title
							$this->settings ['menu']['title'], // Menu title
							$this->usercaps, // User capabilities
							$this->page, // Page
							array ($this->obj, $this->settings ['render']) // Render Page
						);
					} else {
						$success = add_menu_page (
							$this->settings ['page']['title'], // Page title
							$this->settings ['menu']['title'], // Menu title
							$this->usercaps, // User capabilities
							$this->page, // Page & Menu slug
							array ($this->obj, $this->settings ['render']), // Render Page
							(is_string ($this->settings ['menu']['icon'])? $this->settings ['menu']['icon'] : 'none'),
							(is_int ($this->settings ['menu']['position'])? $this->settings ['menu']['position'] : 99)
						);
					}
					if (!$success) {
						add_options_page (
							$this->settings ['page']['title'], // Page title
							$this->settings ['menu']['title'], // Menu title
							$this->usercaps, // User capabilities
							$this->page, // Page
							array ($this->obj, $this->settings ['render']) // Render Page
						);
					}
				}
			}
		}


		private function addSettingsField ($field_name, $field_settings, $section_id = 'default') {
			$args = array ();
			$args ['name'] = $field_name;
			$args ['label_for'] = $this->optionid.'_'.$field_name;
			$args ['class'] = (array_key_exists ('class', $field_settings) && is_string ($field_settings ['class'])) ? $field_settings ['class'] : $this->page;
			if (array_key_exists ('placeholder', $field_settings)) {
				$args ['placeholder'] = $field_settings ['placeholder'];
			}
			if (array_key_exists ('min', $field_settings) && is_int ($field_settings ['min'])) {
				$args ['min'] = $field_settings ['min'];
			}
			if (array_key_exists ('max', $field_settings) && is_int ($field_settings ['max'])) {
				$args ['max'] = $field_settings ['max'];
			}
			add_settings_field (
				$this->optionid.'_'.$field_name, // Field id
				$field_settings ['title'], // Field label/title
				array ($this->obj, $field_settings ['render']), // Field render function
				$this->page, // Field page
				$section_id, // Field Section id
				$args
			);
		}

		public function addPageSettings () {
			if (static::authorised ()) {
				register_setting ($this->page, $this->optionid, array ($this->obj, $this->settings ['validate']));

				// sections
				foreach ($this->settings ['sections'] as $section_name => $section) {
					$section_id = $this->optionid.'_'.$section_name.'_section';
					add_settings_section (
						$section_id, // Section id
						$section ['title'], // Section Title
						array ($this->obj, $section ['render']), // Section Render
						$this->page // Section Page
					);
					// section fields
					foreach ($section ['fields'] as $field_name => $field_settings) {
						self::addSettingsField ($field_name, $field_settings, $section_id);
					}
				}
			}
		}

		protected function authorised () {
			// Check authorisation etc...
			return current_user_can ($this->usercaps);
		}

		protected function renderCheckBox ($args, $value) {
			$fn = $args['name'];
			$id = esc_attr ($this->optionid.'_'.$fn);
			$name = esc_attr ($this->optionid.'['.$fn.']');
			switch ($value) {
				case 1:
				case true:
				case 'on':
				case 'checked="checked"':
					$value = 'checked="checked"';
					break;
				default:
					$value = '';
			}
			$class = esc_attr (array_key_exists ('class', $args)? $args ['class'] : $this->page);
			echo "<input id=\"$id\" type=\"checkbox\" name=\"$name\" class=\"$class\" $value/>";
		}

		protected function renderTextArea ($args, $value) {
			$fn = $args['name'];
			$id = esc_attr ($this->optionid.'_'.$fn);
			$name = esc_attr ($this->optionid.'['.$fn.']');
			$class = esc_attr (array_key_exists ('class', $args)? $args ['class'] : $this->page);
			$place = esc_attr (array_key_exists ('placeholder', $args)? $args ['placeholder'] : '');
			$value = esc_html ($value);
			echo "<textarea id=\"$id\" name=\"$name\" style=\"min-height:60px;max-height:300px\" class=\"$class\" placeholder=\"$place\">$value</textarea>";
		}

		protected function renderRangeSlider ($args, $value, $min = null, $max = null) {
			$fn = $args['name'];
			$id = esc_attr ($this->optionid.'_'.$fn);
			$name = esc_attr ($this->optionid.'['.$fn.']');
			$class = esc_attr (array_key_exists ('class', $args)? $args ['class'] : $this->page);
			$value = esc_html ($value);
			echo "<input type=\"range\" id=\"$id\" name=\"$name\" value=\"$value\" min=\"$min\" max=\"$max\" oninput=\"document.getElementById('$id-out').value=this.value;\">";
			echo "<output id=\"$id-out\" for=\"$id\">$value</output> minutes";
		}

	}
}
