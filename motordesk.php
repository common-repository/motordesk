<?php
/**
 * @package motordesk-automotive-dealership-management
 * @version 1.1.2
 */
/*
Plugin Name: MotorDesk
Plugin URI: http://wordpress.org/extend/plugins/motordesk/
Description: Connect MotorDesk's revolutionary automotive dealership management platform with your WordPress website.  Manage your auto dealership and used/new car stock with MotorDesk whilst harnessing the power of your own WordPress website.
Author: MotorDesk
Author URI: https://motordesk.com/
License: GPLv2
Version: 1.1.2
*/
/*
Copyright 2024 Chief Mechanic Limited (email: wordpress@chief-mechanic.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define('MOTORDESK_TRANSIENT_EXPIRE', 94608000);


if ((function_exists('add_action')) && (function_exists('add_filter'))) {

	if ((function_exists('is_admin')) && (is_admin())) {

		// Initiate admin functions

		require_once(dirname(__FILE__).'/include/motordesk_admin.php');

		add_action('init', 'motordesk_admin');

		add_action( 'upgrader_process_complete', 'motordesk_upgrade', 10, 2);

	} elseif ((isset($_POST['motordesk-key'])) && (isset($_POST['motordesk-secret']))) {

		// Initiate cache refresh functions, only when request contains the key and secret

		require_once(dirname(__FILE__).'/include/motordesk_cache.php');

		add_action('init', 'motordesk_cache');

	} else {

		// Initiate frontend functions

		require_once(dirname(__FILE__).'/include/motordesk_front.php');

		add_action('init', 'motordesk_front');

	}
}


function motordesk_add_option($option = array()) {

	// Save plugin configuration

	$option = base64_encode(serialize($option));

	return add_option('motordesk', $option, '', 'no');

}


function motordesk_update_option($option = array()) {

	// Update plugin configuration

	$option = base64_encode(serialize($option));

	return update_option('motordesk', $option);

}


function motordesk_get_option() {

	// Retrieve plugin configuration

	if ($option = get_option('motordesk')) {

		$option = unserialize(base64_decode($option));

		if (is_array($option)) {

			if (!isset($option['template_custom_path'])) {
				$option['template_custom_path'] = false;
			}

			return $option;

		}
	}

	return false;

}

?>