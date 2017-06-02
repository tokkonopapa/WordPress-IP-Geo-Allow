<?php
/**
 * Plugin Name IP Geo Allow
 *
 * @package     IP Geo Allow
 * @author      Dragan Đurić
 * @copyright   2016 Dragan Đurić
 * @license     AGPL-3.0+
 *
 * @wordpress-plugin
 * Plugin Name: IP Geo Allow
 * Plugin URI:  https://github.com/ddur/WordPress-IP-Geo-Allow
 * Description: Extends "IP Geo Block" plugin with [Dyn]DNS named hosts to IP Whitelist and Reverse-DNS name allow-filter.
 * Text Domain: ip-geo-allow
 * Version:     0.9.4
 * Author:      Dragan Đurić <dragan.djuritj@gmail.com>
 * Author URI:  https://github.com/ddur
 * License URI: http://www.gnu.org/licenses/agpl-3.0.txt
 * License:     AGPL-3.0+
 *  Copyright (C) 2017 Dragan Đurić
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/agpl-3.0.txt>.
 */
namespace IP_Geo_Allow;
defined ('ABSPATH') || die(-1);

require ('com.logotronic.plugin/Emit.php');
require ('com.logotronic.plugin/AbstractPlugin.php');
require ('com.logotronic.plugin/AutoConnect.php');
require ('classes/Config.php');
require ('classes/Validate.php');
require ('classes/Plugin.php');
new Plugin (__FILE__);
if (is_admin()) {
	require ('com.logotronic.plugin/AbstractSettings.php');
	require ('classes/Settings.php');
}
