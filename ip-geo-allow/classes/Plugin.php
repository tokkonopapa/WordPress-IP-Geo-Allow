<?php
namespace IP_Geo_Allow;
defined ('ABSPATH') || die(-1);

use \Com\Logotronic\Plugin\v0\AbstractPlugin;
use \Com\Logotronic\Plugin\v0\AutoConnect;
use \Com\Logotronic\Plugin\v0\Emit;

if (!class_exists(__NAMESPACE__.'\Plugin')) {
	final class Plugin  extends AbstractPlugin {

		private $hosts_enabled = Config::DEFAULTS [Config::HOSTS_ENABLED];
		private $hosts = Config::DEFAULTS [Config::HOSTS];
		private $delay = Config::DEFAULTS [Config::DELAY];

		private $rnets_enabled = Config::DEFAULTS [Config::RNETS_ENABLED];
		private $rnets = Config::DEFAULTS [Config::RNETS];

		public function __construct ($file) {
			parent::__construct ($file);
			$connect = new AutoConnect ($this);
			$connect->autoRegister();
			$connect->autoCallback();
		}

		public function on_plugins_loaded_action () {

			load_plugin_textdomain (Plugin::getSlug(), false, Plugin::getLanguageDir());

			// Detect IP-Geo-Block Plugin
			if (class_exists ('\IP_Geo_Block')) { // IP Geo block class is available?
	
				if (Plugin::getOption (Config::HOSTS_ENABLED)) {

					/* Get HOSTS Configuration Options */
					$this->hosts = Plugin::getOption (Config::HOSTS);
					defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->hosts is '.Validate::hosts ($this->hosts)? 'valid': 'invalid');
					$this->delay = Plugin::getOption (Config::DELAY);
					defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->delay is '.Validate::delay ($this->delay)? 'valid': 'invalid');

					/* Enable Dynamic DNS host matching */
					add_filter ('ip-geo-block-extra-ips', array ($this, 'allowDnsWhitelist'), 10, 2 );
					add_action (Plugin::getCronId (), array ($this, 'resolveDnsHosts'));

				}

				if (Plugin::getOption (Config::RNETS_ENABLED)) {

					$this->rnets = Plugin::getOption (Config::RNETS);

					/* Enable Reverse DNS Host matching */
					add_filter ('ip-geo-block-xmlrpc', array ($this, 'allowReverseMatchXmlRpc'));
					add_filter ('ip-geo-block-login', array ($this, 'allowReverseMatchLogin'));
					add_filter ('ip-geo-block-admin', array ($this, 'allowReverseMatchAdmin'));
				}

			} else {
				Emit::error ('IP_Geo_Allow: Missing IP_Geo_Block class. IP Geo Block plugin is deactivated or not installed?');
			}
		}

		/* Add Extra IP's to whitelist
		IP_Geo_Block filter for: 'ip-geo-block-extra-ips'
		*/
		public function allowDnsWhitelist ($iplist, $hook) {
			defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('Hook: '. $hook);
			if (in_array ($hook, array ('login', 'admin', 'xmlrpc'))) {
				$whitelist = get_transient (Plugin::getCacheId ());
				$whitelist = $whitelist? $whitelist : $this->resolveDnsHosts();
				$iplist = self::appendToWhitelist ($iplist, $whitelist);
			}
			defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('Whitelist: '. $iplist['white_list']);
			return $iplist;
		}

		/* PRIVATE Append/merge Extra IP's to IP_Geo_Block whitelist */
		private static function appendToWhitelist ($iplist, $whitelist) {
			if (is_string ($whitelist)
				&& trim ($whitelist) !== '') { // not empty string?
				if (array_key_exists ('white_list', $iplist)
					&& is_string ($iplist['white_list'])
					&& trim ($iplist['white_list']) !== '') { // not empty string?
					$iplist['white_list'] .= ','.$whitelist;
				} else { // empty white_list
					$iplist['white_list'] = $whitelist;
				}
			}
			return $iplist;
		}

		/* wp-cron job */
		public function resolveDnsHosts() {
			if (is_array ($this->hosts)) {
				if (count ($this->hosts) !== 0) {
					$whitelist = array ();
					foreach ($this->hosts as $host) {
						$hostip = Validate::host($host);
						if ($hostip !== false) {
						 	if (!array_key_exists ($hostip, $whitelist)) { // skip duplicate IP's
								$whitelist[$hostip] = true; // add valid ip
								defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('add: '.$host.'=>'.$hostip);
							} else {
								defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ("Host $host resolved as duplicate");
							}
						} else {
							defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ("Host $host is unresolved");
						}
					}

					if (count ($whitelist) !== 0) {
						$whitelist = implode (',', array_keys ($whitelist)); // convert array to string
						if (get_transient (Plugin::getCacheId ()) !== $whitelist) { // expired or must change?
							set_transient (Plugin::getCacheId (), $whitelist, $this->delay * 2 * MINUTE_IN_SECONDS); // transient cache
						}
						if (!wp_next_scheduled (Plugin::getCronId ())) {
							wp_schedule_single_event (time() + ($this->delay * MINUTE_IN_SECONDS), Plugin::getCronId ());
						}
						return $whitelist;
					}
				} else {
					defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->hosts is empty');
				}
			} else {
				defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->hosts is not array');
			}
			return false;
		}

		public function allowReverseMatchXmlRpc ($validate) {
			defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('matching for XmlRpc: '. print_r ($validate, true));
			return $this->allowReverseMatch ($validate);
		}
		public function allowReverseMatchLogin ($validate) {
			defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('matching for Login: '. print_r ($validate, true));
			return $this->allowReverseMatch ($validate);
		}
		public function allowReverseMatchAdmin ($validate) {
			defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('matching for Admin: '. print_r ($validate, true));
			return $this->allowReverseMatch ($validate);
		}

		/* Check if Reverse DNS matches network/domain
		IP_Geo_Block filter for: 'ip-geo-block-xmlrpc'; 'ip-geo-block-login'; 'ip-geo-block-admin'
		*/
		private function allowReverseMatch ($validate) {
			// No need to test if $validate['result'] === 'passed'
			if ((!array_key_exists('result', $validate) || $validate['result'] !== 'passed')
				&& array_key_exists('ip', $validate)) {
				if (is_array ($this->rnets)) { // Type check
					// Nets-data exists
					if (count ($this->rnets) > 0) {
						// get reverse dns
						$reverseDns = gethostbyaddr ($validate['ip']);
						defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('reverse dns: '.$reverseDns .'  $validate: '. print_r ($validate, true));
						// compute strlen once for all Nets
						$dnsLength = strlen ($reverseDns);
						// reverseDns is not empty or same as IP?
						if ($dnsLength > 0 && $reverseDns !== $validate['ip']) {
							// foreach Net
							foreach ($this->rnets as $reverseNet) {
								$netlength = strlen ($reverseNet);
								$offset = $dnsLength - $netlength;
								// Net is shorter or equal to $reverseDns and $reverseDns.caseInsensitiveEndsWith($reverseNet)?
								if ($offset >= 0 && substr_compare ($reverseDns, $reverseNet, $offset, $netlength, true) === 0) {
									// Pass
									$validate['result'] = 'passed';
									defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$reverseDns: '.$reverseDns .'  $reverseNet: '. $reverseNet);
									break;
								}
							}
						}
					} else {
						defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->rnets is empty');
					}
				} else {
					defined (WP_DEBUG) && WP_DEBUG === true && Emit::debug ('$this->rnets is not an array');
				}
			}
			return $validate;
		}

		public static function getCronId () {
			return Plugin::getPrefix().'_cron';
		}

		public static function getCacheId () {
			return Plugin::getPrefix().'_cache';
		}

		private static $optionCache = null;
		public static function getOption ($key = null) {
			if (Plugin::$optionCache === null) {
				Plugin::$optionCache = get_option (Plugin::getOptionId(), null);
				if (Plugin::$optionCache === null) {
					Plugin::$optionCache = Config::DEFAULTS;
					update_option (Plugin::getOptionId(), Plugin::$optionCache, true);
				}
			}
			if ($key === null) {
				return is_array (Plugin::$optionCache) ? Plugin::$optionCache : array ();
			} else if (is_array (Plugin::$optionCache) && array_key_exists ($key, Plugin::$optionCache)) {
				return Plugin::$optionCache [$key];
			}
			return null;
		}

		public function onPluginActivate () {

			$fail = false;
			if (is_multisite()) {
				$fail[] = 'Multisite is not supported by this plugin.';
			}

			$required_php_version = '5.6';
			if (!version_compare (phpversion(), $required_php_version, '>=')) {
				$fail[] = 'Please upgrade PHP to version '.$required_php_version.' or later.';
			}

			$required_wp_version = '4.7';
			if (!version_compare (get_bloginfo('version'), $required_wp_version, '>=')) {
				$fail[] = 'Please upgrade Wordpress to version '.$required_wp_version.' or later.';
			}
			/* Force user to upgrade WordPress core to latest release ??
			$version = @(json_decode(wp_safe_remote_get('https://api.wordpress.org/core/version-check/1.7/')['body'])->offers[0]->version);
			if (is_string ($version) && !version_compare (get_bloginfo ('version'), $version, '>=' )) {
				$fail[] = 'Please upgrade to latest WordPress release ('.$version.').';
			}
			*/

			if ($fail) {
				$reason = implode (PHP_EOL, $fail);
				die ($reason);
			}

			// get & create options
			if (get_option (self::getOptionId (), null) === null ) {
				add_option (self::getOptionId (), Config::DEFAULTS);
			}
		}

		public function onPluginDeactivate () {
			wp_clear_scheduled_hook (Plugin::getCronId ()); // Remove cron if waiting
		}

		public static function onPluginUninstall () {
			delete_option (Plugin::getOptionId ()); // Remove options
		}

	}
}
