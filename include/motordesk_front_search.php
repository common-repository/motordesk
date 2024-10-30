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


function motordesk_front_search($motordesk) {

	// Generate search form and result data

	$output = array();

	$search_sort = 'mileage';
	if (isset($motordesk['search_option']['sort'])) {
		$search_sort = $motordesk['search_option']['sort'];
	}
	if ((isset($_GET['mt_sort'])) && (in_array($_GET['mt_sort'], array('mileage', 'year_asc', 'year_desc', 'price_asc', 'price_desc', 'monthly_asc', 'monthly_desc', 'recent')))) {
		$search_sort = sanitize_key($_GET['mt_sort']);
	}

	$search_page = 0;
	if ((isset($_GET['mt_page'])) && (is_numeric($_GET['mt_page']))) {
		$search_page = sanitize_key($_GET['mt_page']);
	}

	$search_results = 10;
	if (isset($motordesk['search_option']['results'])) {
		$search_results = $motordesk['search_option']['results'];
	}
	if ((isset($_GET['mt_results'])) && (in_array($_GET['mt_results'], array(10,12,24,25,48,50,100,1000)))) {
		$search_results = sanitize_key($_GET['mt_results']);
	}

	$search_price_monthly = 'price';
	if ((isset($_GET['mt_price_monthly'])) && ($_GET['mt_price_monthly']=='monthly')) {
		$search_price_monthly = sanitize_key($_GET['mt_price_monthly']);
	}

	// Generate HTML fields

	$output = motordesk_front_search_field($motordesk);

	// Process results

	$output['result'] = motordesk_front_search_result($motordesk, $output['search'], $search_sort, $search_price_monthly);

	// Group filter

	$group = false;
	if ((isset($_GET['mt_group'])) && ($_GET['mt_group']!='')) {
		$group = $_GET['mt_group'];
	} elseif ((isset($_GET['group'])) && ($_GET['group']!='')) {
		$group = $_GET['group'];
	}

	if ($group!==false) {
		$search_result_keys = array_keys($output['result']);
		$search_result_count = count($search_result_keys);

		$output['query']['mt_group'] = sanitize_text_field($group);

		for ($i=0; $i<$search_result_count; $i++) {

			$search_result_key = $search_result_keys[$i];

			if ((isset($output['result'][$search_result_key]['data']['vehicle']['group'])) && ($output['result'][$search_result_key]['data']['vehicle']['group']==$group)) {
			} else {
				unset($output['result'][$search_result_key]);
			}
		}
	}

	// Results pagination

	$output['pagination'] = array(
		'total_results' => count($output['result']),
		'start' => floor($search_results * $search_page)
	);

	if ($output['pagination']['start']>$output['pagination']['total_results']) {
		$search_page = 0;
		$output['pagination']['start'] = floor($search_results * $search_page);
	}

	$output['pagination']['end'] = floor($output['pagination']['start'] + $search_results);

	$search_result_keys = array_keys($output['result']);
	$search_result_count = count($search_result_keys);

	for ($i=0; $i<$search_result_count; $i++) {

		$search_result_key = $search_result_keys[$i];

		if (($i<$output['pagination']['start']) || ($i>=$output['pagination']['end'])) {

			unset($output['result'][$search_result_key]);

		}
	}

	$output['pagination']['previous'] = false;
	if (($output['pagination']['start']>0) && ($search_page>0)) {
		$output['pagination']['previous'] = floor($search_page - 1);
	}

	$output['pagination']['next'] = false;
	if ($output['pagination']['end']<$search_result_count) {
		$output['pagination']['next'] = floor($search_page + 1);
	}

	$output['pagination']['total_pages'] = ceil($search_result_count / $search_results) - 1;

	$output['pagination']['pages'] = range(0, $output['pagination']['total_pages']);

	if ($output['pagination']['total_pages']>6) {

		for ($i=0; $i<$output['pagination']['total_pages']; $i++) {

			if (($i==0) || ($i==$output['pagination']['total_pages'])) {

			} else

			if (($i>=$search_page - 1) && ($i<=$search_page + 1)) {			


			} elseif (($i==$search_page - 2) || ($i==$search_page + 2)) {			

				if ($search_page - 1<=0) {
					$i++;					
				}

				$output['pagination']['pages'][$i] = false;

			} else {
				unset($output['pagination']['pages'][$i]);
			}
		}
	}
	
	$output['sort'] = $search_sort;
	$output['page'] = $search_page;
	$output['results'] = $search_results;
	$output['price_monthly'] = $search_price_monthly;

	$output['query'] = http_build_query($output['query']);

	return $output;

}


function motordesk_front_latest($motordesk) {

	// Generate search form and result data

	$output = array();

	$search_sort = 'recent';
	$search_page = 0;
	$search_results = 10;
	$search_price_monthly = 'price';

	// Generate HTML fields

	$output = motordesk_front_search_field($motordesk);

	// Process results

	$output['result'] = motordesk_front_search_result($motordesk, $output['search'], $search_sort, $search_price_monthly);

	return array_values($output['result']);

}


function motordesk_front_sold($motordesk) {

	// Generate sold vehicles listings data

	$output = array(
		'result' => array()
	);

	$sold = get_transient('motordesk-is');

	if (is_array($sold)) {

		$sold_keys = array_keys($sold);
		$sold_count = count($sold_keys);

		for ($i=0; $i<$sold_count; $i++) {

			$sold_key = $sold_keys[$i];

			$vehicle = get_transient('motordesk-vs-'.$sold_key);

			if (is_array($vehicle)) {

				$output['result'][] = $vehicle;

			}
		}
	}

	$output['result'] = array_values($output['result']);

	return $output;

}


function motordesk_front_search_field($motordesk, $search = array()) {

	// Generate search form inputs

	$output = array();
	$output_search = array();
	$output_query = array();

	$currency_symbol = '&pound;';
	if (isset($motordesk['currency_symbol'])) {
		$currency_symbol = $motordesk['currency_symbol'];
	}
	
	$distance = 'm';
	if (isset($motordesk['distance'])) {
		$distance = $motordesk['distance'];
	}

	$conditional = array(
		'type_body' => array(),
		'make_model' => array(),
	);

	$select = array(
		'fuel' => array(),
		'transmission' => array(),
		'drivetrain' => array(),
		'colour' => array(),
		'seats' => array(),
		'doors' => array(),
		'emission_class' => array(),
		'ulez' => array(),
		'caz' => array(),
		'location' => array(),
		'group' => array()
	);

	$range = array(
		'price' => array('0','1','2','3','4','5','6','7','8','9','10','12.5','15','17.5','20','22.5','25','27.5','30','35','40','45','50','60','70','80','90','100','150','250','500','1000','1250','1500','2000','2500','5000','10000','25000','100000'),
		'monthly' => array('0', '5', '10', '15', '20', '25', '30', '40', '50', '75', '100', '125', '150', '200', '300', '500', '1000', '2500', '5000', '10000'),
		'mileage' => array('0','5','10','20','30','40','50','60','70','80','90','100','125','150','200','500','1000','1500','2000','3500','5000','10000'),
		'age' => array('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','20','25','30','40','50','60','70','80','90','100','150','200','250'),
		'engine_size' => array('0','10','12','14','16','18','20','25','30','40','50','60','70','80','90','100','250','500','1000','5000')
	);

	$greater = array(
		'mpg' => array('3','4','5','6')
	);

	$less = array(
		'acceleration' => array('3','4','5','6','7','8','12'),
		'co2' => array('0','7.5','10','11','12','13','14','15','16.5','17.5','18.5','20','22.5','25.5'),
		'insurance' => array('10','20','30','40')
	);

	if (($field = get_transient('motordesk-i-field'))!==false) {

		$field_keys = array_keys($field);
		$field_count = count($field_keys);

		for ($i=0; $i<$field_count; $i++) {

			$field_key = $field_keys[$i];

			if (isset($conditional[$field_key])) {
			} elseif (isset($range[$field_key])) {
				if ((isset($_GET[$field_key.'_min'])) && (motordesk_validate_text($_GET[$field_key.'_min']))) {
					$field_value = sanitize_text_field($_GET[$field_key.'_min']);
					$search[$field_key]['min'] = array(
						$field_value => true
					);
					$output_search[$field_key][0] = $field_value;
					$output_query[$field_key.'_min'] = $field_value;
				}
				if ((isset($_GET[$field_key.'_max'])) && (motordesk_validate_text($_GET[$field_key.'_max']))) {
					$field_value = sanitize_text_field($_GET[$field_key.'_max']);
					$search[$field_key]['max'] = array(
						$field_value => true
					);
					$output_search[$field_key][1] = $field_value;
					$output_query[$field_key.'_max'] = $field_value;
				}
			} elseif ((isset($greater[$field_key])) || (isset($less[$field_key]))) {
				if (isset($_GET[$field_key])) {
					$field_value = sanitize_text_field($_GET[$field_key]);
					if ($field_value!='') {
						$search[$field_key] = array(
							$field_value => true
						);
						$output_search[$field_key] = $field_value;
						$output_query[$field_key] = $field_value;
					}
				}
			} elseif (isset($_GET[$field_key])) {
				if (is_array($_GET[$field_key])) {
					$field_key_count = count($_GET[$field_key]);
					for ($j=0; $j<$field_key_count; $j++) {
						if (motordesk_validate_text($_GET[$field_key][$j])) {
							$field_value = sanitize_text_field($_GET[$field_key][$j]);
							if ($field_value!='') {
								if (!isset($search[$field_key])) {
									$search[$field_key] = array();
								}
								$search[$field_key][$field_value] = true;
								$output_search[$field_key] = $search[$field_key];
								if (!isset($output_query[$field_key])) {
									$output_query[$field_key] = array();
								}
								$output_query[$field_key][] = $field_value;
							}	
						}
					}
				} elseif (motordesk_validate_text($_GET[$field_key])) {
					$field_value = sanitize_text_field($_GET[$field_key]);
					if ($field_value!='') {
						$search[$field_key] = array(
							$field_value => true
						);
						$output_search[$field_key] = $search[$field_key];
						$output_query[$field_key] = $field_value;
					}
				}
			}

			$field_key_keys = array_keys($field[$field_key]);
			$field_key_count = count($field_key_keys);

			$output[$field_key] = '';

			if (isset($conditional[$field_key])) {

				$field_key_explode = explode('_', $field_key);

				if ((isset($_GET[$field_key_explode[0]])) && (motordesk_validate_text($_GET[$field_key_explode[0]]))) {

					$field_value = sanitize_text_field($_GET[$field_key_explode[0]]);

					if ($field_value!='') {

						$field_value_sub = '';
						$search[$field_key_explode[0]] = array(
							$field_value => array()
						);
						$output_query[$field_key_explode[0]] = $field_value;

						if (isset($field_key_explode[1])) {

							$field_value_clean = $field_key_explode[1].'_'.strtolower(motordesk_string_url($field_value));

							if (isset($_GET[$field_value_clean])) {

								if (!is_array($_GET[$field_value_clean])) {
									$_GET[$field_value_clean] = array($_GET[$field_value_clean]);
								}

								$value_count = count($_GET[$field_value_clean]);

								for ($j=0; $j<$value_count; $j++) {
									if (($_GET[$field_value_clean][$j]!='') && (motordesk_validate_text($_GET[$field_value_clean][$j]))) {

										$field_value_sub = sanitize_text_field($_GET[$field_value_clean][$j]);
										$search[$field_key_explode[0]][$field_value][$field_value_sub] = true;		
										if (!isset($output_query[$field_value_clean])) {
											$output_query[$field_value_clean] = array();
										}
										$output_query[$field_value_clean][] = $field_value_sub;				
	
									}
								}
							}
						}
						$output_search[$field_key] = array($field_value, $search[$field_key_explode[0]][$field_value]);
					}
				}

				$output[$field_key] = array();

				$output[$field_key]['condition'] = '<select id="motordesk_vehicle_search_'.$field_key_explode[0].'" name="'.$field_key_explode[0].'" class="motordesk-select"><option value="">All</option>';

				$field_value = 'result';
				if (!isset($output[$field_key][$field_value])) {
					$output[$field_key][$field_value] = '';
				}

				$output[$field_key][$field_value] .= '<select id="motordesk_vehicle_search_'.$field_key_explode[1].'_all" disabled="disabled" class="motordesk-select motordesk_vehicle_search_'.$field_key_explode[1].' motordesk_vehicle_search_'.$field_key_explode[1].'_all';

				$hide_all = false;
				$all_hidden = true;
				if ((isset($search[$field_key_explode[0]])) && (count($search[$field_key_explode[0]])>0)) {
					$hide_all = true;
				}

				for ($j=0; $j<$field_key_count; $j++) {

					$field_key_key = $field_key_keys[$j];
					
					if (!isset($search[$field_key_explode[0]])) {
					} elseif (!isset($search[$field_key_explode[0]][$field_key_key])) {
					} else {
						$all_hidden = false;
					}
				}

				if (($hide_all===true) && ($all_hidden===false)) {
					$output[$field_key][$field_value] .= ' motordesk-hide';
				}

				$output[$field_key][$field_value] .= '"><option value="">All</option></select>';

				for ($j=0; $j<$field_key_count; $j++) {

					$field_key_key = $field_key_keys[$j];

					$output[$field_key]['condition'] .= '<option value="'.htmlentities($field_key_key).'"';

					if (isset($search[$field_key_explode[0]][$field_key_key])) {
						$output[$field_key]['condition'] .= ' selected="selected"';
					}

					$output[$field_key]['condition'] .= '>'.htmlentities($field_key_key).'</option>';

					$output[$field_key][$field_value] .= '<select id="motordesk_vehicle_search_'.$field_key_explode[1].'_'.strtolower(motordesk_string_url($field_key_key)).'" size="1" class="motordesk-select motordesk_vehicle_search_'.$field_key_explode[1].' motordesk_vehicle_search_'.$field_key_explode[1].'_'.strtolower(motordesk_string_url($field_key_key)).'';
					
					if (!isset($search[$field_key_explode[0]])) {
						$output[$field_key][$field_value] .= ' motordesk-hide';
					} elseif (!isset($search[$field_key_explode[0]][$field_key_key])) {
						$output[$field_key][$field_value] .= ' motordesk-hide';
					} else {
						$output[$field_key][$field_value] .= '" name="'.$field_key_explode[1].'_'.strtolower(motordesk_string_url($field_key_key));
					}

					$output[$field_key][$field_value] .= '"><option value="">All</option>';

					$field_key_key_keys = array_keys($field[$field_key][$field_key_key]);
					$field_key_key_count = count($field_key_key_keys);

					for ($k=0; $k<$field_key_key_count; $k++) {

						$field_key_key_key = $field_key_key_keys[$k];

						$output[$field_key][$field_value] .= '<option value="'.htmlentities($field_key_key_key).'"';

						if (isset($search[$field_key_explode[0]][$field_key_key][$field_key_key_key])) {
							$output[$field_key][$field_value] .= ' selected="selected"';
						}

						$output[$field_key][$field_value] .= '>'.htmlentities($field_key_key_key).'</option>';

					}

					$output[$field_key][$field_value] .= '</select>';
				}

				$output[$field_key]['condition'] .= '</select>';

			} elseif (isset($group[$field_key])) {

				$field_key_explode = explode('_', $field_key);

				$output[$field_key] = '<select id="motordesk_vehicle_search_'.$field_key.'" name="'.$field_key.'" size="1" class="motordesk-select" data-live-search="true" data-actions-box="true">';

				for ($j=0; $j<$field_key_count; $j++) {

					$field_key_key = $field_key_keys[$j];

					$output[$field_key] .= '<optgroup label="'.htmlentities($field_key_key).'">';

					$field_key_key_keys = array_keys($field[$field_key][$field_key_key]);
					$field_key_key_count = count($field_key_key_keys);

					for ($k=0; $k<$field_key_key_count; $k++) {

						$field_key_key_key = $field_key_key_keys[$k];

						$output[$field_key] .= '<option value="'.htmlentities($field_key_key).'||'.htmlentities($field_key_key_key).'" data-tokens="'.htmlentities($field_key_key).': '.htmlentities($field_key_key_key).'"';

						if (isset($search[$field_key][$field_key_key][$field_key_key_key])) {
							$output[$field_key] .= ' selected="selected"';
						}

						$output[$field_key] .= '>'.htmlentities($field_key_key_key).'</option>';

					}

					$output[$field_key] .= '</optgroup>';

				}

				$output[$field_key] .= '</select>';

			} elseif (isset($select[$field_key])) {

				$output[$field_key] .= '<select id="motordesk_vehicle_search_'.$field_key.'" name="'.$field_key.'" size="1" class="motordesk-select">';

				//if ($field_key=='location') { 
					$output[$field_key] .= '<option value="">All</option>';
				//}

				for ($j=0; $j<$field_key_count; $j++) {

					$field_key_key = $field_key_keys[$j];

					$output[$field_key] .= '<option value="'.htmlentities($field_key_key).'"';

					if (isset($search[$field_key][$field_key_key])) {
						$output[$field_key] .= ' selected="selected"';
					}

					if ($field_key=='seats') { 
						if ($field_key_key==1) {
							$field_key_key .= ' seat';
						} else {
							$field_key_key .= ' seats';
						}
					} elseif ($field_key=='doors') { 
						if ($field_key_key==1) {
							$field_key_key .= ' door';
						} else {
							$field_key_key .= ' doors';							
						}
					}

					$output[$field_key] .= '>'.htmlentities($field_key_key).'</option>';

				}

				$output[$field_key] .= '</select>';

				if (($field_key=='group') && ($field_key_count==0)) {
					$output[$field_key] = '';
				}

			} elseif (isset($range[$field_key])) {

				if (isset($field_key_keys[0])) {
					$field_min = $field_key_keys[0];
				} else {
					$field_min = 0;
				}
				if (isset($field_key_keys[($field_key_count-1)])) {
					$field_max = $field_key_keys[($field_key_count-1)];
				} else {
					$field_max = 0;
				}

				$range_count = count($range[$field_key]);

				$output[$field_key] = array();

				$output[$field_key]['min'] = '<select id="motordesk_vehicle_search_'.$field_key.'_min" name="'.$field_key.'_min" class="motordesk-select">';

				$output[$field_key]['max'] = '<select id="motordesk_vehicle_search_'.$field_key.'_max" name="'.$field_key.'_max" class="motordesk-select">';

				for ($j=0; $j<$range_count; $j++) {

					$range_value = $range[$field_key][$j];

					if (((!isset($range[$field_key][($j + 1)])) || ($range[$field_key][($j + 1)]>=$field_min)) && ((!isset($range[$field_key][($j - 1)])) || ($range[$field_key][($j - 1)]<=$field_max))) {

						$output[$field_key]['min'] .= '<option value="'.htmlentities($range_value).'"';
						$output[$field_key]['max'] .= '<option value="'.htmlentities($range_value).'"';

						if (isset($search[$field_key]['min'][$range_value])) {
							$output[$field_key]['min'] .= ' selected="selected"';
						}
						if (!isset($search[$field_key]['max'])) {
							if ((!isset($range[$field_key][($j + 1)])) || ($range[$field_key][($j + 1)]>$field_max)) {
								$output[$field_key]['max'] .= ' selected="selected"';								
							}
						} elseif (isset($search[$field_key]['max'][$range_value])) {
							$output[$field_key]['max'] .= ' selected="selected"';
						}

						if (in_array($field_key, array('mileage', 'price'))) {
							$range_value = round($range_value * 1000);
						} elseif (in_array($field_key, array('monthly'))) {
							$range_value = round($range_value * 10);
						}

						if (in_array($field_key, array('mileage', 'price', 'monthly'))) {
							$range_value = motordesk_number($range_value, 0, true);
						}

						$range_value = htmlentities($range_value);

						if (in_array($field_key, array('price', 'monthly'))) {
							$range_value = $currency_symbol.$range_value;
						} elseif ($field_key=='age') {
							if ($range_value==1) {
								$range_value .= ' year';
							} else {
								$range_value .= ' years';
							}
						} elseif ($field_key=='engine_size') {
							
							if ($range_value==0) {
								$range_value = motordesk_number(($range_value / 10), 0, false).' litres';
							} elseif ($range_value!=10) {
								$range_value = motordesk_number(($range_value / 10), 1, false).' litres';
							} else {
								$range_value = motordesk_number(($range_value / 10), 1, false).' litre';
							}
						} elseif ($field_key=='mileage') {
							if ($distance=='k') {
								$range_value .= ' km';
							} else {
								$range_value .= ' miles';
							}
						}

						$output[$field_key]['min'] .= '>'.$range_value.'</option>';
						$output[$field_key]['max'] .= '>'.$range_value.'</option>';

					}
				}

				$output[$field_key]['min'] .= '</select>';
				$output[$field_key]['max'] .= '</select>';

			} elseif (isset($greater[$field_key])) {
				
				if (isset($field_key_keys[0])) {
					$field_min = $field_key_keys[0];
				} else {
					$field_min = 0;
				}
				if (isset($field_key_keys[($field_key_count-1)])) {
					$field_max = $field_key_keys[($field_key_count-1)];
				} else {
					$field_max = 0;
				}

				$greater_count = count($greater[$field_key]);

				$output[$field_key] = '<select id="motordesk_vehicle_search_'.$field_key.'" name="'.$field_key.'" class="motordesk-select"><option value="">All</option>';

				for ($j=0; $j<$greater_count; $j++) {

					$greater_value = $greater[$field_key][$j];

					if (($greater[$field_key][$j]>=$field_min) && ($greater[$field_key][$j]<=$field_max)) {

						$output[$field_key] .= '<option value="'.htmlentities($greater_value).'"';

						if (isset($search[$field_key][$greater_value])) {
							$output[$field_key] .= ' selected="selected"';
						}

						$greater_value = round($greater_value * 10);

						$greater_value = htmlentities($greater_value);

						$output[$field_key] .= '>'.$greater_value.'+';

						if ($field_key=='mpg') {
							$output[$field_key] .= ' mpg';
						}

						$output[$field_key] .= '</option>';

					}
				}

				$output[$field_key] .= '</select>';

			} elseif (isset($less[$field_key])) {

				if (isset($field_key_keys[0])) {
					$field_min = $field_key_keys[0];
				} else {
					$field_min = 0;
				}
				if (isset($field_key_keys[($field_key_count-1)])) {
					$field_max = $field_key_keys[($field_key_count-1)];
				} else {
					$field_max = 0;
				}

				$less_count = count($less[$field_key]);

				$output[$field_key] = '<select id="motordesk_vehicle_search_'.$field_key.'" name="'.$field_key.'" class="motordesk-select"><option value="">All</option>';

				for ($j=0; $j<$less_count; $j++) {

					$less_value = $less[$field_key][$j];

					if (($less[$field_key][$j]>=$field_min) && ($less[$field_key][$j]<=$field_max)) {

						$output[$field_key] .= '<option value="'.htmlentities($less_value).'"';

						if (isset($search[$field_key][$less_value])) {
							$output[$field_key] .= ' selected="selected"';
						}

						if ($field_key=='co2') {
							$less_value = round($less_value * 10);
						}

						$less_value = htmlentities($less_value);

						if ($field_key=='insurance') {
							$output[$field_key] .= '>Group '.$less_value;
						} else {
							$output[$field_key] .= '>Less than '.$less_value;
						}

						if ($field_key=='acceleration') {
							$output[$field_key] .= ' seconds';
						} elseif ($field_key=='co2') {
							$output[$field_key] .= ' g/km CO2';
						}

						$output[$field_key] .= '</option>';

					}
				}

				$output[$field_key] .= '</select>';

			}
		}
	}

	return array(
		'search' => $output_search,
		'field' => $output,
		'query' => $output_query
	);

}




function motordesk_front_search_result($motordesk, $search, $sort_by = 'mileage', $search_monthly = false) {

	// Search results

	$results = array();
	$output = array();

	$sort = array(
		'year_asc' => array(
			'target' => 'data/history/year',
			'second_target' => 'data/vehicle/registered',
			'reverse' => false
		),
		'year_desc' => array(
			'target' => 'data/history/year',
			'second_target' => 'data/vehicle/registered',
			'reverse' => true
		),
		'mileage' => array(
			'target' => 'data/vehicle/mileage',
			'reverse' => false
		),
		'price_asc' => array(
			'target' => 'data/stock/price_forecourt',
			'reverse' => false
		),
		'price_desc' => array(
			'target' => 'data/stock/price_forecourt',
			'reverse' => true
		),
		'monthly_asc' => array(
			'target' => 'data/finance/example_monthly',
			'reverse' => false
		),
		'monthly_desc' => array(
			'target' => 'data/finance/example_monthly',
			'reverse' => true
		),
		'recent' => array(
			'target' => 'created',
			'reverse' => true
		)
	);

	if (!isset($sort[$sort_by])) {
		$sort_by = 'mileage';
	}

	$filter = get_transient('motordesk-i-id');

	if ((count($search)==0) && (($results = get_transient('motordesk-i-alpha'))!==false)) {

		$results_keys = array_keys($results);
		$results_count = count($results_keys);

		for ($i=0; $i<$results_count; $i++) {

			$results_key = $results_keys[$i];

			$vehicle = get_transient('motordesk-v-'.$results[$results_key]);

			if ($vehicle!==false) {

				$output[] = $vehicle;

			}
		}

		if (($sort_by!==false) && (isset($sort[$sort_by]))) {

			if (!isset($sort[$sort_by]['second_target'])) {
				$sort[$sort_by]['second_target'] = false;
			}

			$output = motordesk_array_sort_target($output, $sort[$sort_by]['target'], $sort[$sort_by]['second_target'], $sort[$sort_by]['reverse']);

		}

		return $output;

	}

	$index = array(
		'type_body' => array(),
		'make_model' => array(),
		'price' => array(),
		'monthly' => array(),
		'mileage' => array(),
		'age' => array(),
		'engine_size' => array(),
		'fuel' => array(),
		'transmission' => array(),
		'drivetrain' => array(),
		'seats' => array(),
		'doors' => array(),
		'colour' => array(),
		'acceleration' => array(),
		'mpg' => array(),
		'co2' => array(),
		'emission_class' => array(),
		'ulez' => array(),
		'caz' => array(),
		'insurance' => array(),
		'location' => array(),
		'group' => array()
	);

	$conditional = array(
		'type_body' => array(),
		'make_model' => array(),
	);

	$range = array(
		'price' => array(),
		'monthly' => array(),
		'mileage' => array(),
		'age' => array(),
		'engine_size' => array()
	);

	$greater = array(
		'mpg' => array()
	);

	$less = array(
		'acceleration' => array(),
		'co2' => array(),
		'insurance' => array()
	);

	if (($search_monthly===false) || ($search_monthly!='monthly')) {
		unset($index['monthly']);
		unset($range['monthly']);
		if (isset($search['monthly'])) {
			unset($search['monthly']);
		}
	}

	$index_keys = array_keys($index);
	$index_count = count($index_keys);

	for ($i=0; $i<$index_count; $i++) {

		$index_key = $index_keys[$i];
		;
		if (isset($search[$index_key])) {

			$search_key = $search[$index_key];

			if ($search_key!==false) {

				if (($index[$index_key] = get_transient('motordesk-i-'.$index_key))!==false) {
					
					if (isset($conditional[$index_key])) {

						if (!is_array($search_key)) {
							$search_key = array($search_key);
						}

						if ((isset($search_key[1])) && (is_array($search_key[1])) && (count($search_key[1])>0)) {

							$search_key_keys = array_keys($search_key[1]);
							$search_key_count = count($search_key_keys);

							for ($j=0; $j<$search_key_count; $j++) {

								$search_key_key = $search_key_keys[$j];

								if (isset($index[$index_key][$search_key[0]][$search_key_key])) {

									$results = motordesk_front_search_result_key($results, $index[$index_key][$search_key[0]][$search_key_key]);

								}
							}
						} else {

							if ($search_key[0]=='') {

								$index_key_keys = array_keys($index[$index_key]);
								$index_key_count = count($index_key_keys);

								for ($j=0; $j<$index_key_count; $j++) {

									$index_key_key = $index_key_keys[$j];

									$index_key_key_keys = array_keys($index[$index_key][$index_key_key]);
									$index_key_key_count = count($index_key_key_keys);

									for ($k=0; $k<$index_key_key_count; $k++) {

										$index_key_key_key = $index_key_key_keys[$k];

										$results = motordesk_front_search_result_key($results, $index[$index_key][$index_key_key][$index_key_key_key]);													
									}
								}

							} elseif (isset($index[$index_key][$search_key[0]])) {

								$index_key_keys = array_keys($index[$index_key][$search_key[0]]);
								$index_key_count = count($index_key_keys);

								for ($j=0; $j<$index_key_count; $j++) {

									$index_key_key = $index_key_keys[$j];

									$results = motordesk_front_search_result_key($results, $index[$index_key][$search_key[0]][$index_key_key]);					
								}
							}
						}
					} elseif (isset($range[$index_key])) {

						if (!is_array($search_key)) {
							$search_key = array($search_key);
						}

						if (!isset($search_key[1])) {
							$search_key[1] = $search_key[0];
						}

						$index_key_keys = array_keys($index[$index_key]);
						$index_key_count = count($index_key_keys);

						for ($j=0; $j<$index_key_count; $j++) {

							$index_key_key = $index_key_keys[$j];

							if ((is_numeric($index_key_key)) && ($index_key_key>=$search_key[0]) && ($index_key_key<=$search_key[1])) {

								$results = motordesk_front_search_result_key($results, $index[$index_key][$index_key_key]);

							}
						}
					} elseif (isset($greater[$index_key])) {

						$index_key_keys = array_keys($index[$index_key]);
						$index_key_count = count($index_key_keys);

						for ($j=0; $j<$index_key_count; $j++) {

							$index_key_key = $index_key_keys[$j];

							if ((is_numeric($index_key_key)) && ($index_key_key>=$search_key)) {

								$results = motordesk_front_search_result_key($results, $index[$index_key][$index_key_key]);

							}
						}						
					} elseif (isset($less[$index_key])) {

						$index_key_keys = array_keys($index[$index_key]);
						$index_key_count = count($index_key_keys);

						for ($j=0; $j<$index_key_count; $j++) {

							$index_key_key = $index_key_keys[$j];

							if ((is_numeric($index_key_key)) && ($index_key_key<=$search_key)) {

								$results = motordesk_front_search_result_key($results, $index[$index_key][$index_key_key]);

							}
						}
					} elseif (is_array($search_key)) {

						$search_key_keys = array_keys($search_key);
						$search_key_count = count($search_key_keys);

						for ($j=0; $j<$search_key_count; $j++) {

							$search_key_key = $search_key_keys[$j];

							if (isset($index[$index_key][$search_key_key])) {

								$results = motordesk_front_search_result_key($results, $index[$index_key][$search_key_key]);													
							}
						}
					} elseif (isset($index[$index_key][$search_key])) {

						 $results = motordesk_front_search_result_key($results, $index[$index_key][$search_key]);						

					}

				}
			}
		}
	}

	$search_count = count($search);

	if ($results!==false) {

		$results_keys = array_keys($results);
		$results_count = count($results_keys);

		for ($i=0; $i<$results_count; $i++) {

			$results_key = $results_keys[$i];

			if ($results[$results_key]>=$search_count) {

				$output[] = get_transient('motordesk-v-'.$results_key);

			}
		}

		if (($sort_by!==false) && (isset($sort[$sort_by]))) {

			if (!isset($sort[$sort_by]['second_target'])) {
				$sort[$sort_by]['second_target'] = false;
			}

			$output = motordesk_array_sort_target($output, $sort[$sort_by]['target'], $sort[$sort_by]['second_target'], $sort[$sort_by]['reverse']);

		}
	}

	return $output;

}


function motordesk_front_search_result_key($results, $key) {

	if (is_array($key)) {

		$key_keys = array_keys($key);
		$key_count = count($key_keys);

		for ($i=0; $i<$key_count; $i++) {

			$key_key = $key_keys[$i];

			$key_key_value = $key[$key_key];

			if (!isset($results[$key_key_value])) {
				$results[$key_key_value] = 1;
			} else {
				$results[$key_key_value]++;
			}
		}
	}

	return $results;

}


function motordesk_array_sort_target($array, $target, $second_target = false, $reverse = false) {

	// Array sorting

	$position = false;
	if (strstr($target, '/')) {
		$position = true;
	}

	$sort = array();

	$array_keys = array_keys($array);
	$array_count = count($array_keys);

	$count = '0';

	for ($i=0; $i<$array_count; $i++) {

		$array_key = $array_keys[$i];

		if ($position===false) {
			if (isset($array[$array_key][$target])) {

				$array_target = $array[$array_key][$target].$count;

				if (($array_target=='') && ($second_target!==false) && (isset($array[$array_key][$second_target]))) {

					$array_target = $array[$array_key][$second_target].$count;

					while (isset($sort[$array_target])) {
						$count++;
						$array_target = $array[$array_key][$second_target].$count;
					}

					$sort[$array_target] = array($array_key => $array[$array_key]);

				} else {

					while (isset($sort[$array_target])) {
						$count++;
						$array_target = $array[$array_key][$second_target].$count;
					}

					$sort[$array_target] = array($array_key => $array[$array_key]);

				}
			}
		} else {

			if (($array_target = motordesk_array_position_value($target, $array[$array_key]))!==false) {

				if (($array_target=='') && ($second_target!==false) && (($array_target = motordesk_array_position_value($second_target, $array[$array_key]))!==false)) {

					if ($second_target=='data/vehicle/registered') {
						$array_target = date('Y', strtotime($array_target));
					}

					while (isset($sort[$array_target.$count])) {
						$count++;
					}

					$sort[$array_target.$count] = array($array_key => $array[$array_key]);

				} else {

					if ((in_array($target, array('data/stock/price_forecourt', 'data/stock/price_retail'))) && (is_numeric($array_target))) {
						$array_target = motordesk_number($array_target, 2);
					}

					while (isset($sort[$array_target.$count])) {
						$count++;
					}

					$sort[$array_target.$count] = array($array_key => $array[$array_key]);
	
				}
			} 
		}
	}

	if ($reverse===true) {
		krsort($sort);
	} else {
		ksort($sort);
	}

	$array = $sort;
	$array_keys = array_keys($array);
	$array_count = count($array_keys);

	$sort = array();

	for ($i=0; $i<$array_count; $i++) {

		$array_key = $array_keys[$i];

		$sort_key = array_keys($array[$array_key]);
		$sort_key = $sort_key[0];

		$sort[$sort_key] = $array[$array_key][$sort_key];

	}

	return $sort;

}


function motordesk_array_position_value($array_tag, $array = array(), $count = '0') {

	// Returns array value

	$array_tag_explode = explode('/', $array_tag);

	if (isset($array_tag_explode[$count])) {

		$array_tag_current = $array_tag_explode[$count];

		if (isset($array[$array_tag_current])) {

			$array = $array[$array_tag_current];

			if (($count+1)<count($array_tag_explode)) {

				$array = motordesk_array_position_value($array_tag, $array, ($count+1));

			}

			return $array;

		/*} elseif (@is_null($array[$array_tag_current])) {

			return '';

		*/}
	}

	return false;

}


function motordesk_validate_text($text) {

	// Validate text input

	if (!preg_match('/[^a-zA-Z0-9 \p{L}\p{N}\'"”“”‘’,\.…\+\-_\*\=\?\!@%&\^\[\]\{\}\$\(\)\/\:;#£€\|]/', $text)) {

		return true;

	}

	return false;

}


function motordesk_string_url($string = '') {

	// Process string to URL.

	$string = strip_tags($string);

	$string = preg_replace('/[^a-zA-Z0-9-_]/', '-', $string);

	while (strstr($string, '--')) {
		$string = str_replace('--', '-', $string);
	}

	while (strstr($string, '__')) {
		$string = str_replace('__', '_', $string);
	}

	while ((substr($string, 0, 1)=='-') || (substr($string, 0, 1)=='_')) {
		$string = substr($string, 1);
	}

	while ((substr($string, -1)=='-') || (substr($string, -1)=='_')) {
		$string = substr($string, 0, -1);
	}

	return $string;

}

?>