<?php
namespace IP_Geo_Allow;
if (!defined ('ABSPATH')) die(-1);

if (!class_exists(__NAMESPACE__.'\Config')) {

	final class Config {

		const HOSTS_ENABLED = 'hosts_enabled';
		const HOSTS = 'hosts';
		const DELAY = 'delay';
		const DELAY_MIN = 'delay_min';
		const DELAY_MAX = 'delay_max';

		const RNETS_ENABLED = 'rnets_enabled';
		const RNETS = 'rnets';

		const DEFAULTS =
			array (
				self::HOSTS_ENABLED => false,
				self::HOSTS => array (),
				self::DELAY => 45,
				self::DELAY_MIN => 5,
				self::DELAY_MAX => 60,
				self::RNETS_ENABLED => false,
				self::RNETS => array ()
			);

		const SETTINGS = 
			array (
				'menu' => array (
					'title' => 'IP Geo Allow',
					'parent-slug' => 'options-general.php'//, // tools.php
					#'icon' => 'dashicons-hammer', // 'none'
					//'position' => 32
				),
				'page' => array ('title' => 'IP Geo Allow'),
				'capability' => 'manage_options',
				'render' => 'renderPage', # method name
				'validate' => 'validate', # method name
				'sections' => array (
					'hosts' => array (
						'title' => 'DNS or Dynamic-DNS Hostnames Whitelist',
						'render' => 'renderSection_hosts', # method name
						'fields' => array (
							'hosts_enabled' => array (
								'title' => 'Enable Hostnames Whitelist',
								'render' => 'renderField_hosts_enabled', # method name
								'default' => Config::DEFAULTS [Config::HOSTS_ENABLED]
							),
							'hosts' => array (
								'title' => 'Hostnames Whitelist',
								'render' => 'renderField_hosts', # method name
								'placeholder' => 'static-host.example.com                      dyndns-host.example.net                      www.example.com                              www.example.net',
								'default' => Config::DEFAULTS [Config::HOSTS]
							),
							'delay' => array (
								'title' => 'Cache DNS Lookup for',
								'render' => 'renderField_delay', # method name
								'default' => Config::DEFAULTS [Config::DELAY],
								'min' => Config::DEFAULTS [Config::DELAY_MIN],
								'max' => Config::DEFAULTS [Config::DELAY_MAX]
							)
						)
					),
					'rnets' => array (
						'title' => 'Reverse-DNS Lookup Filters',
						'render' => 'renderSection_rnets', # method name
						'fields' => array (
							'rnets_enabled' => array (
								'title' => 'Enable Reverse-DNS Filters',
								'render' => 'renderField_rnets_enabled', # method name
								'default' => Config::DEFAULTS [Config::RNETS_ENABLED]
							),
							'rnets' => array (
								'title' => 'Reverse-DNS Filters (list)',
								'render' => 'renderField_rnets', # method name
								'placeholder' => '.example.com                                 .adsl.example.net',
								'default' => Config::DEFAULTS [Config::RNETS]
							)
						)
					)
				)
			);
	}
}
