<?php
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


function motordesk_cache() {

	// Authenticate and process cache updates

	if (($motordesk = motordesk_get_option())!==false) {

		if (isset($motordesk['token_param'])) {

			$motordesk_token_param = $motordesk['token_param'];

			if ((isset($_POST['motordesk-token-'.$motordesk_token_param])) && (isset($motordesk['key'])) && (isset($motordesk['secret'])) && ($_POST['motordesk-key']===$motordesk['key']) && ($_POST['motordesk-secret']===$motordesk['secret']) && (strlen($_POST['motordesk-token-'.$motordesk_token_param])=='32') && (!preg_match('/[^a-zA-Z0-9]/', $_POST['motordesk-token-'.$motordesk_token_param]))) {

				// Request authenticated

				// Attempt to increase timeout and memory limits

				@set_time_limit(3600);
				@ini_set('memory_limit', '512M');

				// Connect to MotorDesk

				$motordesk_url = false;
				$motordesk_url_param = array(
					'wordpress_param' => $motordesk_token_param,
					'wordpress_token' => sanitize_text_field($_POST['motordesk-token-'.$motordesk_token_param])
				);
				$motordesk_action = false;
				$motordesk_request = array();

				if ((isset($_POST['motordesk-action'])) && (in_array($_POST['motordesk-action'], array('search-index', 'search-vehicle')))) {

					$motordesk_action = sanitize_key($_POST['motordesk-action']);

					if ($motordesk_action=='search-index') {
						$motordesk_url = 'vehicle/search-index';
					} elseif ($motordesk_action=='search-vehicle') {
						$motordesk_url = 'vehicle/search-vehicle';
						$motordesk_request = array(
							'include_sold' => true
						);
					}
				}

				if ($motordesk_url!==false) {

					add_filter('https_ssl_verify', '__return_false');
				
					$motordesk_api_url = 'https://api.motordesk.com';
					
					$motordesk_connect = array(
						'request' => wp_remote_post($motordesk_api_url.'/1.0/'.$motordesk_url.'/?'.http_build_query($motordesk_url_param), array(
							'headers' => array(
								'Content-Type' => 'application/json; charset=utf-8'
							),
							'body' => json_encode($motordesk_request)
						)),
						'response' => '',
						'code' => '0'
					);

					$motordesk_connect['code'] = wp_remote_retrieve_response_code($motordesk_connect['request']);

					if ($motordesk_connect['code']=='200') {

						$motordesk_connect['response'] = wp_remote_retrieve_body($motordesk_connect['request']);

						if (($motordesk_connect['response'] = json_decode($motordesk_connect['response'], true))!==false) {

							// Successful connection

							if ((isset($motordesk_connect['response']['success'])) && ($motordesk_connect['response']['success']===true)) {

								if ($motordesk_action=='search-index') {

									motordesk_cache_search_index($motordesk, $motordesk_connect['response']);

								} elseif ($motordesk_action=='search-vehicle') {

									motordesk_cache_search_vehicle($motordesk, $motordesk_connect['response']);

								}

							} else {

								// API authentication or action failed

								motordesk_json(array(
									'success' => false,
									'reason' => 'api'
								));

							}
						}
					}
				}

				// Connection failed

				motordesk_json(array(
					'success' => false,
					'reason' => 'connect'
				));

			}
		}
	}

	// Authentication failed, ignore

}


function motordesk_cache_search_index($motordesk, $index) {

	// Loop through search indexes and save as transients

	if (isset($index['index'])) {

		if (isset($index['localisation'])) {

			$motordesk['currency_symbol'] = $index['localisation']['currency_symbol'];
			$motordesk['distance'] = $index['localisation']['distance'];

			motordesk_update_option($motordesk);

		}

		$index_keys = array_keys($index['index']);
		$index_count = count($index_keys);

		if ($index_count>0) {

			for ($i=0; $i<$index_count; $i++) {

				$index_key = $index_keys[$i];

				set_transient('motordesk-i-'.$index_key, $index['index'][$index_key], MOTORDESK_TRANSIENT_EXPIRE);

			}

			motordesk_json(array(
				'success' => true
			));

		}
	}
}


function motordesk_cache_search_vehicle($motordesk, $index) {

	// Load transient index

	$exists = get_transient('motordesk-i');
	if (!is_array($exists)) {
		$exists = array();
	}

	$exists_sold = get_transient('motordesk-is');
	if (!is_array($exists_sold)) {
		$exists_sold = array();
	}

	if (!isset($index['sold'])) {
		$index['sold'] = array();
	}

	// Loop through vehicles and save as transients

	if (isset($index['vehicle'])) {

		$index_keys = array_keys($index['vehicle']);
		$index_count = count($index_keys);

		if ($index_count>0) {

			for ($i=0; $i<$index_count; $i++) {

				$index_key = $index_keys[$i];

				if ((isset($index['vehicle'][$index_key]['status'])) && ($index['vehicle'][$index_key]['status']=='2')) {

					set_transient('motordesk-v-'.$index_key, $index['vehicle'][$index_key], MOTORDESK_TRANSIENT_EXPIRE);

					$exists[$index_key] = true;

					$index['vehicle'][$index_key] = true;

				} elseif ((isset($index['vehicle'][$index_key]['status'])) && ($index['vehicle'][$index_key]['status']=='3')) {

					set_transient('motordesk-vs-'.$index_key, $index['vehicle'][$index_key], MOTORDESK_TRANSIENT_EXPIRE);

					$exists_sold[$index_key] = true;

					unset($index['vehicle'][$index_key]);
					$index['sold'][$index_key] = true;

				}
			}
		}

		// Remove old transient data		

		$exists_keys = array_keys($exists);
		$exists_count = count($exists_keys);

		for ($i=0; $i<$exists_count; $i++) {

			$exists_key = $exists_keys[$i];

			if (!isset($index['vehicle'][$exists_key])) {
				delete_transient('motordesk-v-'.$exists_key);
			}
		}	

		$exists_sold_keys = array_keys($exists_sold);
		$exists_sold_count = count($exists_sold_keys);

		for ($i=0; $i<$exists_sold_count; $i++) {

			$exists_sold_key = $exists_sold_keys[$i];

			if (!isset($index['sold'][$exists_sold_key])) {
				delete_transient('motordesk-vs-'.$exists_sold_key);
			}
		}

		// Save transient index

		set_transient('motordesk-i', $exists, MOTORDESK_TRANSIENT_EXPIRE);
		set_transient('motordesk-is', $exists_sold, MOTORDESK_TRANSIENT_EXPIRE);

		// Update last update time

		$motordesk['last_update'] = time();
		
		motordesk_update_option($motordesk);

		motordesk_json(array(
			'success' => true
		));

	}
}


function motordesk_json($output = false) {

	// Output JSON and exit

	if ($output===false) {

		$output = array(
			'success' => false,
			'reason' => 'error'
		);

	}

	print json_encode($output, JSON_PRETTY_PRINT);
	exit;

}

?>