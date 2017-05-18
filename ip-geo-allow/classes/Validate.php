<?php
namespace IP_Geo_Allow;
defined ('ABSPATH') || die(-1);

if (!class_exists(__NAMESPACE__.'\Validate')) {
	final class Validate {

		private static function arrayOfStrings ($array) {
			if (is_array ($array)) {
				foreach ($array as $string) {
					if (is_string ($string) && trim ($string) !=='') {
						// OK
					} else {
						return false;
					}
				}
				return $array;
			}
			return false;
		}

		static function hosts ($hosts) {
			return Validate::arrayOfStrings ($hosts);
		}

		static function rnets ($rnets) {
			return Validate::arrayOfStrings ($rnets);
		}

		static function host ($host) {
			if (is_string ($host) && trim ($host) !== '') {
				$host = trim ($host);
				$host = rtrim ($host, '.') . '.'; // Add last dot if not present
				if (substr_count ($host, '.') >= 3 && strpos ($host, '..') === false) {
					 // Must have at least 3, not connected dots.
					$hostip = gethostbyname ($host);
		 			if (filter_var ($hostip, FILTER_VALIDATE_IP)) {
		 				return $hostip;
		 			}
				}
			}
			return false;
		}

		static function delay ($delay) {
			if (is_int ($delay) && $delay >= Config::DEFAULTS [Config::DELAY_MIN] && $delay <= Config::DEFAULTS [Config::DELAY_MAX]) {
				return $delay;
			}
			return false;
		}
	}
}
