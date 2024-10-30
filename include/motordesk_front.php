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


function motordesk_front() {

	if (($motordesk = motordesk_get_option())!==false) {

		// Load MotorDesk JS and CSS scripts
		add_action('wp_enqueue_scripts', 'motordesk_front_script');

		// Load content hook
		add_filter('the_content', 'motordesk_front_init', 1);

		// Load shortcode
		add_shortcode('MOTORDESK', 'motordesk_front_shortcode');
		add_shortcode('MOTORDESK-LATEST', 'motordesk_front_shortcode');
		add_shortcode('MOTORDESK-SEARCH', 'motordesk_front_shortcode');
		add_shortcode('MOTORDESK-SOLD', 'motordesk_front_shortcode');

		// Enable page title changes
		add_filter('document_title_parts', 'motordesk_front_title');
		add_filter('wpseo_title', 'motordesk_front_title_plain');

		// Enable page meta and opengraph changes
		add_filter('wpseo_metadesc', 'motordesk_front_meta_description');
		add_filter('wpseo_opengraph_desc', 'motordesk_front_meta_description');
		add_filter('wpseo_opengraph_title', 'motordesk_front_title_plain');
		add_filter('wpseo_twitter_image', 'motordesk_front_meta_image');

		// Set-up rewrite variables for vehicle pages
		if (!is_null($page = get_page_by_path(motordesk_front_url($motordesk)))) {
			if (isset($page->ID)) {

			    add_rewrite_tag('%motordesk_vehicle_id%', '([^&]+)');
			    add_rewrite_tag('%motordesk_vehicle_url%', '([^&]+)');

				add_rewrite_rule('^'.motordesk_front_url($motordesk).'/([^/]*)/([^/]*)/?', 'index.php?page_id='.$page->ID.'&motordesk_vehicle_id=$matches[1]&motordesk_vehicle_url=$matches[2]', 'top');

				flush_rewrite_rules();

			}
		}
	}
}


function motordesk_front_init($content = '', $latest = false, $search = false, $sold = false) {

	if ((!stristr($content, '{MOTORDESK}')) && (!stristr($content, '{MOTORDESK-LATEST}')) && (!stristr($content, '{MOTORDESK-SEARCH}')) && (!stristr($content, '{MOTORDESK-SOLD}'))) {
		return $content;
	}

	// Load configuration
	$motordesk = motordesk_get_option();

	if ((isset($motordesk['template_custom_path'])) && ($motordesk['template_custom_path']!==false) && (file_exists($motordesk['template_custom_path']))) {

		$path = $motordesk['template_custom_path'];
		while (substr($path, -1)=='/') {
			$path = substr($path, 0, -1);
		}

	} else {

		$path = dirname(dirname(__FILE__));

		if (file_exists($path.'/template_custom/')) {
			$path .= '/template_custom';
		} else {
			$path .= '/template';
		}
	}

	// Buffer output
	ob_start();

	// Initiate MotorDesk content
	$motordesk = motordesk_front_content($motordesk, $content);

	// Print WordPress content before {MOTORDESK} tag
	$output = $motordesk['content_before'];
	unset($motordesk['content_before']);

	if ($latest===true) {

		include $path.'/motordesk_vehicle_template_latest.php';

	} elseif ($sold===true) {

		include $path.'/motordesk_vehicle_template_sold.php';

	} elseif ($search===true) {
		if (count($motordesk['search']['field'])>0) {
			$embed = true;
			include $path.'/motordesk_vehicle_template_search_embed.php';
		}
	} elseif ($motordesk['vehicle']!==false) {

		include $path.'/motordesk_vehicle_template_vehicle.php';

	} elseif (count($motordesk['search']['field'])>0) {

		$embed = false;
		include $path.'/motordesk_vehicle_template_search.php';

	}

	// Collect & clean buffer
	$output .= ob_get_contents();
	ob_end_clean();

	// Print WordPress content before {MOTORDESK} tag
	$output .= $motordesk['content_after'];
	unset($motordesk['content_after']);

	return $output;

}


function motordesk_front_shortcode($attributes, $content = '', $shortcode = '') {

	if ($shortcode=='MOTORDESK') {

		return motordesk_front_init('{MOTORDESK}');

	} elseif ($shortcode=='MOTORDESK-LATEST') {

		return motordesk_front_init('{MOTORDESK-LATEST}', true);

	} elseif ($shortcode=='MOTORDESK-SEARCH') {

		return motordesk_front_init('{MOTORDESK-SEARCH}', false, true);

	} elseif ($shortcode=='MOTORDESK-SOLD') {

		return motordesk_front_init('{MOTORDESK-SOLD}', false, false, true);

	}
}


function motordesk_front_content($motordesk, $content) {

	// Prepare content

	global $wp;

	$output = array(
		'url' => motordesk_front_url($motordesk),
		'currency_symbol' => $motordesk['currency_symbol'],
		'distance' => $motordesk['distance'],
		'search' => array(),
		'vehicle' => false,
		'latest' => array(),
		'sold' => array(),
		'content_before' => '',
		'content_after' => ''
	);

	if (isset($motordesk['search_url'])) {
		$output['search_url'] = $motordesk['search_url'];
	}

	// Split any WordPress content to surround MotorDesk content

	$content = preg_split('/({MOTORDESK}|{MOTORDESK-LATEST}|{MOTORDESK-SEARCH}|{MOTORDESK-SOLD})/', $content);
	$output['content_before'] = $content[0];
	unset($content[0]);
	$output['content_after'] = implode('', $content);

	if ($motordesk!==false) {

		// Vehicle inputs

		$vehicle_id = get_query_var('motordesk_vehicle_id', false);
		$vehicle_url = get_query_var('motordesk_vehicle_url', false);

		if (($vehicle_id!==false) && (is_numeric($vehicle_id)) && ($vehicle_url!==false)) {

			// Load a vehicle

			require_once(dirname(__FILE__).'/motordesk_front_search.php');
			require_once(dirname(__FILE__).'/motordesk_front_vehicle.php');

			$output['vehicle'] = motordesk_front_vehicle($motordesk, $vehicle_id, $vehicle_url);	

		}

		if ($output['vehicle']===false) {

			// Load search

			require_once(dirname(__FILE__).'/motordesk_front_search.php');

			$output['search'] = motordesk_front_search($motordesk);
			$output['latest'] = motordesk_front_latest($motordesk);
			$output['sold'] = motordesk_front_sold($motordesk);

		}
	}

	return $output;

}


function motordesk_front_url($motordesk) {

	if (isset($motordesk['search_url'])) {
		return $motordesk['search_url'];
	}

	$uri = explode('/', $_SERVER['REQUEST_URI']);
	if (isset($uri[1])) {
		return $uri[1];
	}
	return '';

}

function motordesk_front_script() {

	// Load jQuery
	wp_enqueue_script('jquery');


	// Load MotorDesk JavaScript
    wp_enqueue_style('motordesk', plugins_url('/css/motordesk.css', dirname(__FILE__)));
    wp_enqueue_script('motordesk', plugins_url('/js/motordesk.js', dirname(__FILE__)));

}


function motordesk_front_title($title = array()) {

	global $motordesk;

	$vehicle_id = get_query_var('motordesk_vehicle_id', false);
	$vehicle_url = get_query_var('motordesk_vehicle_url', false);

	if (($vehicle_id!==false) && (is_numeric($vehicle_id)) && ($vehicle_url!==false)) {
		
		require_once(dirname(__FILE__).'/motordesk_front_search.php');
		require_once(dirname(__FILE__).'/motordesk_front_vehicle.php');

		$vehicle = motordesk_front_vehicle($motordesk, $vehicle_id, $vehicle_url);	

		if ((!defined('MOTORDESK_PAGE_TITLE')) && (isset($vehicle['data']['vehicle']['text']))) {
			$title['title'] = $vehicle['data']['vehicle']['text'].' - '.$title['title'];
		}	
	}

	return $title;

}


function motordesk_front_title_plain() {

	$title = motordesk_front_title(array(
		'title' => ''
	));

	if (isset($title['title'])) {
		return substr($title['title'], 0, -2);
	}	
}


function motordesk_front_meta_description($description) {

	global $motordesk;

	$vehicle_id = get_query_var('motordesk_vehicle_id', false);
	$vehicle_url = get_query_var('motordesk_vehicle_url', false);

	if (($vehicle_id!==false) && (is_numeric($vehicle_id)) && ($vehicle_url!==false)) {
		
		require_once(dirname(__FILE__).'/motordesk_front_search.php');
		require_once(dirname(__FILE__).'/motordesk_front_vehicle.php');

		$vehicle = motordesk_front_vehicle($motordesk, $vehicle_id, $vehicle_url);	

		if ((!defined('MOTORDESK_PAGE_DESCRIPTION')) && (isset($vehicle['data']['option']['description'])) && ($vehicle['data']['option']['description']!='')) {
		
			$description = substr(strip_tags($vehicle['data']['option']['description']), 0, 150);
			if (strlen(strip_tags($vehicle['data']['option']['description']))>150) {
				$description .= '...';
			}
		}	
	}

	return $description;

}


function motordesk_front_meta_image() {

	global $motordesk;

	$vehicle_id = get_query_var('motordesk_vehicle_id', false);
	$vehicle_url = get_query_var('motordesk_vehicle_url', false);

	if (($vehicle_id!==false) && (is_numeric($vehicle_id)) && ($vehicle_url!==false)) {
		
		require_once(dirname(__FILE__).'/motordesk_front_search.php');
		require_once(dirname(__FILE__).'/motordesk_front_vehicle.php');

		$vehicle = motordesk_front_vehicle($motordesk, $vehicle_id, $vehicle_url);	

		if ((!defined('MOTORDESK_PAGE_IMAGE')) && (isset($vehicle['photo'][0])) && ($vehicle['photo'][0]!='')) {
			return $vehicle['photo'][0];
		}	
	}
}


function motordesk_number($number, $decimal = '2', $comma = false, $decimal_character = '.', $thousand_character = ',') {

 	// Format number to $decimal places.  If $comma equals 1 then thousands are separated by commas.

 	if (!is_numeric($number)) {
 		$number = 0;
 	}

	$number = round($number, $decimal);
	$number = number_format($number, $decimal, $decimal_character, '');
	if ($comma!==false) {
		$number = number_format($number, $decimal, $decimal_character, $thousand_character);
	}

    return $number;

}


function motordesk_distance($motordesk, $distance) {

	if (!is_numeric($distance)) {
		$distance = 0;

	}
	$distance_s = 's';
	if ($distance==1) {
		$distance_s = '';
	}
	
	if ($motordesk['distance']=='k') {
		return 'km'.$distance_s;
	}
	
	return 'mile'.$distance_s;		
	
}

?>