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


function motordesk_admin() {

	// Initiate admin functions

	if ((function_exists('wp_get_current_user')) && (current_user_can('manage_options'))) {

		define('WP_MOTORDESK_VERSION', '1.1.1');

		add_action('admin_menu', 'motordesk_admin_menu');

	}
}


function motordesk_admin_menu() {

	// Initiate admin menu

	add_menu_page('MotorDesk', 'MotorDesk', 'manage_options', 'motordesk', 'motordesk_admin_home', '', 82.14);

}


function motordesk_admin_home() {

	// Admin plugin output

	$motordesk_plugin_url = admin_url('admin.php?page=motordesk');

	$motordesk = false;
	$notices = '';

	if (isset($_GET['motordesk-reset'])) {
		delete_option('motordesk');
	}

	if (($motordesk = motordesk_get_option())===false) {

		// No configuration data, generate key and secret.

		$motordesk = array(
			'key' => motordesk_random_string(32),
			'secret' => motordesk_random_string(32),
			'token_param' => motordesk_random_string(32),
			'last_update' => false,
			'currency_symbol' => '&pound;',
			'distance' => 'm',
			'template_custom_backup' => array(
				'file' => array(),
				'time' => 0
			),
			'template_custom_path' => false,
			'search_option' => array()
		);

		if (motordesk_add_option($motordesk)!==true) {
			$motordesk = false;
		}
	}

	if (isset($_POST['motordesk-templates-path'])) {

		// Change path to templates directory

		$motordesk_templates_path = sanitize_text_field($_POST['motordesk-templates-path']);

		if (($motordesk_templates_path=='') || ((file_exists($motordesk_templates_path)) && (is_dir($motordesk_templates_path)))) {

			$templates_exist = true;

			if ($motordesk_templates_path!='') {

				if (substr($motordesk_templates_path, -1)!='/') {
					$motordesk_templates_path .= '/';
				}

				$templates = array(
					'motordesk_vehicle_template_search_form.php',
					'motordesk_vehicle_template_search_pagination.php',
					'motordesk_vehicle_template_search_result.php',
					'motordesk_vehicle_template_search.php',
					'motordesk_vehicle_template_vehicle_content.php',
					'motordesk_vehicle_template_vehicle.php'
				);
				$templates_count = count($templates);

				for ($i=0; $i<$templates_count; $i++) {
					if (!file_exists($motordesk_templates_path.$templates[$i])) {

						$notices .= '<div class="updated settings-error error is-dismissible">Template \''.esc_html($motordesk_templates_path.$templates[$i]).'\' does not exist.</div>';

						$templates_exist = false;
						$i = $templates_count;

					}
				}
			}

			if ($templates_exist===true) {

				$motordesk['template_custom_path'] = $motordesk_templates_path;

				motordesk_update_option($motordesk);

				$notices .= '<div class="updated settings-error success is-dismissible">Path to templates directory updated successfully!</div>';

			}
		} else {

			$notices .= '<div class="updated settings-error error is-dismissible">Invalid path to templates directory.</div>';

		}
	}

	if (isset($_POST['motordesk-search-url'])) {

		// Change URL to search page

		$motordesk_search_url = sanitize_text_field($_POST['motordesk-search-url']);

		if ($motordesk_search_url!='') {

			while (substr($motordesk_search_url, -1)=='/') {
				$motordesk_search_url = substr($motordesk_search_url, 0, -1);
			}
			while (substr($motordesk_search_url, 0, 1)=='/') {
				$motordesk_search_url = substr($motordesk_search_url, 1, 0);
			}

			$motordesk['search_url'] = $motordesk_search_url;

		} elseif (isset($motordesk['search_url'])) {

			unset($motordesk['search_url']);

		}

		motordesk_update_option($motordesk);

		$notices .= '<div class="updated settings-error success is-dismissible">Search page URL updated successfully!</div>';

	}

	$search_sort_options = array('mileage', 'year_asc', 'year_desc', 'price_asc', 'price_desc', 'monthly_asc', 'monthly_desc', 'recent');
	$search_results_options = array(10,12,24,25,48,50,100,1000);

	if ((isset($_POST['motordesk-search-sort'])) && (isset($_POST['motordesk-search-results']))) {

		// Save search options

		if (in_array($_POST['motordesk-search-sort'], $search_sort_options)) {
			$motordesk['search_option']['sort'] = sanitize_text_field($_POST['motordesk-search-sort']);
		} elseif (isset($motordesk['search_option']['sort'])) {
			unset($motordesk['search_option']['sort']);
		}

		if (in_array($_POST['motordesk-search-results'], $search_results_options)) {
			$motordesk['search_option']['results'] = sanitize_text_field($_POST['motordesk-search-results']);
		} elseif (isset($motordesk['search_option']['results'])) {
			unset($motordesk['search_option']['results']);
		}

		motordesk_update_option($motordesk);

		$notices .= '<div class="updated settings-error success is-dismissible">Search options updated successfully!</div>';

	}

	// Generate copy and paste string to establish connection

	$motordesk_copy_string = array(
		'key' => $motordesk['key'],
		'secret' => $motordesk['secret'],
		'param' => $motordesk['token_param'],
		'url' => site_url('/')
	);

	$motordesk_copy_string = base64_encode(json_encode($motordesk_copy_string));

	// Display last update notice

	if ((isset($motordesk['last_update'])) && ($motordesk['last_update']!==false) && ($motordesk['last_update']!=0)) {

		$notices .= '<div class="updated settings-error success is-dismissible">Vehicle data last updated '.human_time_diff($motordesk['last_update']).' ago.</div>';

	}

	// Output

	$plugin_directory = dirname(dirname(__FILE__));
	$motordesk_templates_path = $motordesk['template_custom_path'];
	$motordesk_templates_placeholder = $plugin_directory.'/template/';
	$motordesk_search_url = '';
	if (isset($motordesk['search_url'])) {
		$motordesk_search_url = $motordesk['search_url'];
	}

	$motordesk_search_sort_option = '';

	$search_sort_options_count = count($search_sort_options);
	for ($i=0; $i<$search_sort_options_count; $i++) {

		$sort_option = ucwords(str_replace('_', ' ', $search_sort_options[$i]));

		$motordesk_search_sort_option .= '<option value="'.$search_sort_options[$i].'"';

		if ((isset($motordesk['search_option']['sort'])) && ($motordesk['search_option']['sort']==$search_sort_options[$i])) {
			$motordesk_search_sort_option .= ' selected="selected"';
		}

		$motordesk_search_sort_option .= '>'.$sort_option.'</option>'."\n";

	}

	$motordesk_search_results_option = '';

	$search_results_options_count = count($search_results_options);
	for ($i=0; $i<$search_results_options_count; $i++) {

		$motordesk_search_results_option .= '<option value="'.$search_results_options[$i].'"';

		if ((isset($motordesk['search_option']['results'])) && ($motordesk['search_option']['results']==$search_results_options[$i])) {
			$motordesk_search_results_option .= ' selected="selected"';
		}

		$motordesk_search_results_option .= '>'.$search_results_options[$i].' per Page</option>'."\n";

	}

	print<<<END

<div class="wrap">

	<h1>MotorDesk</h1>

	{$notices}

	<h3>Connect MotorDesk</h3>

	<p>To establish a connection between your MotorDesk account and this WordPress installation please select the button below:</p>

	<p><a href="https://my.motordesk.com/business/channel/wordpress/{$motordesk_copy_string}/" class="button button-primary">Connect MotorDesk</a></p>

	<br />

	<h3>Set-Up Instructions</h3>

	<ol>
		<li>Once connected go to the 'Pages' section in your WordPress administration panel and create a page to be used for your vehicle search results.  We recommend setting a permalink URL slug of 'vehicles', 'used-cars' or similar.</li>
		<li>Add the [MOTORDESK] shortcode to the page content, and you're done!</li>
		<li>Visit the page on your website and you will see your vehicle search form and vehicles, and when selected you will be taken to an information page for each vehicle.</li>
		<li>When you're ready to customise your templates go to the plugin directory and duplicate the /template/ directory to be called /template_custom/, then modify the files in the /template_custom/ directory as you require - this will protect against your changes being overwritten when the plugin is updated.  Please ensure you maintain a separate backup of your /template_custom/ directory in case of upgrade failures.</li>
		<li>Alternatively you can maintain the template files in another location in which case you can set the path to your templates directory below.</li>
		<li>You can include the search form, latest vehicles and sold vehicles in other pages of your website using the shortcodes [MOTORDESK-SEARCH], [MOTORDESK-LATEST] and [MOTORDESK-SOLD].</li>
	</ol>

	<br />

	<h3>Templates Directory Path</h3>

	<p>If you would like to use a custom template location please enter the full file system path to the directory which contains your MotorDesk plugin template files.  By default this field should be left blank.</p>

	<form action="$motordesk_plugin_url" method="POST">
		<div class="card" style="max-width:700px;">
			&nbsp;<br /><p class="description"><b>Path to Templates Directory</b></p>
			<input type="text" id="motordesk-templates-path" name="motordesk-templates-path" size="30" value="{$motordesk_templates_path}" style="width:100%;margin-bottom:4px;" /><br />
			<small>Default location: {$motordesk_templates_placeholder}</small><br />&nbsp;<br />
			<input type="submit" value="Update Path" class="button button-primary" />
			<br />&nbsp;
		</div>
	</form><br />

	<br />

	<h3>Search Page URI</h3>

	<p>By default the URL of your search page and therefore your vehicle pages will be detected automatically.  When including features like the 'latest vehicles' on other pages then this automatic detection will not work correctly, in this case please set the URI of your search page below.  e.g. vehicles or used-cars without a leading or trailing slash.</p>

	<form action="$motordesk_plugin_url" method="POST">
		<div class="card" style="max-width:700px;">
			&nbsp;<br /><p class="description"><b>Search Page URI</b></p>
			<input type="text" id="motordesk-search-url" name="motordesk-search-url" size="30" value="{$motordesk_search_url}" style="width:100%;margin-bottom:4px;" /><br />
			&nbsp;<br /><input type="submit" value="Update URI" class="button button-primary" />
			<br />&nbsp;
		</div>
	</form><br />

	<br />

	<h3>Search Options</h3>

	<form action="$motordesk_plugin_url" method="POST">
		<div class="card" style="max-width:700px;">
			&nbsp;<br /><p class="description"><b>Sort By</b></p>
			<select id="motordesk-search-sort" name="motordesk-search-sort">
				{$motordesk_search_sort_option}
			</select><br />
			&nbsp;<br /><p class="description"><b>Vehicles per Page</b></p>
			<select id="motordesk-search-results" name="motordesk-search-results">
				{$motordesk_search_results_option}
			</select><br />
			&nbsp;<br /><input type="submit" value="Save Changes" class="button button-primary" />
			<br />&nbsp;
		</div>
	</form><br />
</div>
END;

	if ((!isset($motordesk['template_custom_backup']['time'])) || ($motordesk['template_custom_backup']['time']<time() - 300)) {

		motordesk_template_backup();

	}

}


function motordesk_upgrade($upgrader_object, $options) {

	// Re-create custom template files after upgrade
	if (($options['action']=='update') && ($options['type']=='plugin')) {

		foreach($options['plugins'] as $plugin) {
			if ($plugin=='motordesk/motordesk.php') {

				motordesk_template_restore();

			}
		}
	}
}


function motordesk_template_backup() {

	// Backup template_custom files.
	if (($motordesk = motordesk_get_option())!==false) {

		$path = dirname(dirname(__FILE__)).'/template_custom/';

		if (file_exists($path)) {

			$motordesk['template_custom_backup']['file'] = array();

			foreach (list_files($path) as $file) {

				$motordesk['template_custom_backup']['file'][basename($file)] = file_get_contents($file);

			}

			$motordesk['template_custom_backup']['time'] = time();

			motordesk_update_option($motordesk);

		}
	}
}


function motordesk_template_restore() {

	// Restore template_custom files.
	if (($motordesk = motordesk_get_option())!==false) {

		if (isset($motordesk['template_custom_backup']['file'])) {

			$file_keys = array_keys($motordesk['template_custom_backup']['file']);
			$file_count = count($file_keys);

			if ($file_count>0) {

				$path = dirname(dirname(__FILE__)).'/template_custom/';

				if (!file_exists($path)) {
					mkdir($path);
				}

				for ($i=0; $i<$file_count; $i++) {

					$file = $file_keys[$i];

					file_put_contents($path.$file, $motordesk['template_custom_backup']['file'][$file]);

				}
			}
		}
	}
}


function motordesk_random_string($length = '32', $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {

	// Generate random string

	$string = '';
	for ($i=0; $i<$length; $i++) {
		$string .= $characters[rand(0,strlen($characters)-1)];
	}
	return $string;

}

?>