<?php
namespace IP_Geo_Allow;
defined ('ABSPATH') || die(-1);

use \Com\Logotronic\Plugin\v0\AbstractSettings;
use \Com\Logotronic\Plugin\v0\Emit;

if (!class_exists(__NAMESPACE__.'\Settings')) {

	final class Settings extends AbstractSettings {

		public function __construct () {
			parent::__construct ($this, Plugin::getPrefix (), Plugin::getBasename (), Plugin::getOptionId ());
			$this->settings = Config::SETTINGS;
			$this->usercaps =
				(array_key_exists ('capability', $this->settings) && is_string ($this->settings ['capability'])) ?
					$this->settings ['capability'] : $this->usercaps;
		}

		// settings ['render']
		public function renderPage () {
			if ($this->authorised ()) {
				?><div class="wrap">
					<h2><?php echo $this->settings ['page']['title']; ?></h2>
					<form action="options.php" method="post">
					<?php settings_fields($this->page); ?>
					<?php do_settings_sections($this->page); ?>
					<input class="button button-primary" name="submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
					</form>
				</div><?php
			} else {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
		}


		// settings ['sections']
		public function renderSection_hosts () {
			echo 'Allows administration access from named hosts, by adding resolved IP numbers to <a href="/wp-admin/options-general.php?page=ip-geo-block">Whitelist of extra IP addresses prior to country code</a>.<br/>';
			echo 'If you do not have static DNS host-name for the your internet connection host, use <a href="https://en.wikipedia.org/wiki/Dynamic_DNS#DDNS_for_Internet_access_devices">Dynamic DNS</a>.<br/>';
		}

		// settings ['sections'] ['fields']
		public function renderField_hosts_enabled ($args) {
			$this->renderCheckBox ($args, Plugin::getOption ($args['name']));
		}

		// settings ['sections'] ['fields']
		public function renderField_hosts ($args) {
			$this->renderTextArea ($args, implode (PHP_EOL, Plugin::getOption ($args['name'])));
		}

		// settings ['sections'] ['fields']
		public function renderField_delay ($args) {
			$args ['min'] = (array_key_exists (Config::DELAY_MIN, $args) && is_int ($args [Config::DELAY_MIN]))? $args [Config::DELAY_MIN] : Config::DEFAULTS [Config::DELAY_MIN];
			$args ['max'] = (array_key_exists (Config::DELAY_MAX, $args) && is_int ($args [Config::DELAY_MAX]))? $args [Config::DELAY_MAX] : Config::DEFAULTS [Config::DELAY_MAX];
			$args ['units'] = (array_key_exists (Config::DELAY_UNITS, $args) && is_string ($args [Config::DELAY_UNITS]))? $args [Config::DELAY_UNITS] : Config::DEFAULTS [Config::DELAY_UNITS];
			$this->renderRangeSlider ($args, Plugin::getOption ($args['name']));
		}

		// settings ['sections']
		public function renderSection_rnets () {
			echo 'Allows administration access from hosts with matched Reverse-DNS lookup (Only if/after access is denied by "IP Geo Block").<br/>';
			echo 'Example DSL: Reverse lookup is 123-456.dsl.provider.net. Add ".dsl.provider.net" to allow access from that provider.<br/>';
			echo 'Example domain: To allow access from all hosts at example.com, add ".example.com".<br/>';
			echo 'Note: Same host/IP can have different DNS and Reverse-DNS names.<br/>';
			if (class_exists ('\IP_Geo_Block')) {
				$ipaddress = \IP_Geo_Block::get_ip_address();
				$reverseDns = gethostbyaddr ($ipaddress);
				defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('renderRNetsSection: '.$ipaddress.'/'.$reverseDns);
				if ($reverseDns !== $ipaddress) {
					echo "<br/>Reverse-DNS host-name for your IP ($ipaddress) is $reverseDns.";
					if (substr_count ($reverseDns, '.') >= 2) {
						$filter = substr ($reverseDns, strpos ($reverseDns, '.'));
						echo '<br/>Filter value "'. $filter.'" will match "'.$reverseDns.'".';
					}
				} else {
					echo "<br/>No Reverse-DNS host-name is found for your IP ($ipaddress).";
				}
			}
		}

		// settings ['sections'] ['fields']
		public function renderField_rnets_enabled ($args) {
			$this->renderCheckBox ($args, Plugin::getOption ($args['name']));
		}

		// settings ['sections'] ['fields']
		public function renderField_rnets ($args) {
			$this->renderTextArea ($args, implode (PHP_EOL, Plugin::getOption ($args['name'])));
		}

		// settings ['validate']
		public function validate ($input) {
			$output = Config::DEFAULTS;
			if (is_array ($input)) {
				if (array_key_exists ('hosts', $input) && is_string ($input['hosts'])) {
					$input ['hosts'] = preg_split ('~[^a-z0-9\-\.]+~i', $input['hosts'], 0, PREG_SPLIT_NO_EMPTY);
				}
				foreach ($input ['hosts'] as $host) {
					$ip = Validate::host ($host);
					if ($ip === false) {
						add_settings_error (
							$this->page,
							'gethostbyname('.$host.')',
							"Cannot resolve \"$host\" to IP number",
							'error'
						);
					} else if (WP_DEBUG) {
						add_settings_error (
							$this->page,
							'gethostbyname('.$host.')',
							"Host-name \"$host\" resolved to $ip",
							'updated'
						);
					}
				}
				if (array_key_exists ('rnets', $input) && is_string ($input['rnets'])) {
					$input ['rnets'] = preg_split ('~[^a-z0-9\-\.]+~i', $input['rnets'], 0, PREG_SPLIT_NO_EMPTY);
				}
				foreach ($input ['rnets'] as $rnet) {
					if ( substr ($rnet, -1) !== '.' // last char is not dot
						&& substr_count ($rnet, '.') >= 2 // at least two dots
						&& strpos ($rnet, '..') === false // no connected dots
					) {
						// OK
					} else {
						add_settings_error (
							$this->page,
							'Reverse-DNS-Pattern',
							"Reverse-DNS match pattern must be at least \".domain.tld\": (\"$rnet\").",
							'error'
						);
					}
				}
				foreach (array_keys ($output) as $key) {
					if (array_key_exists ($key, $input)) {
						$output [$key] = $input [$key];
					}
				}
			}
			return $output;
		}

	}
	new Settings();
}
