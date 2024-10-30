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


function motordesk_front_vehicle($motordesk, $id, $url) {

	// Load vehicle data

	$output = false;

	$vehicle = get_transient('motordesk-v-'.$id);

	if ($vehicle===false) {
		$vehicle = get_transient('motordesk-vs-'.$id);
	}

	if ($vehicle!==false) {

		if ($vehicle['url']===$url) {

			// Related
			$output = motordesk_front_search_field($motordesk);
			$vehicle['related'] = motordesk_front_search_result($motordesk, $output['search'], 'recent', 'price');
			
			$related_count = count($vehicle['related']);
			for ($i=0; $i<$related_count; $i++) {
				if ($vehicle['related'][$i]['id']==$vehicle['id']) {
					unset($vehicle['related'][$i]);
					$vehicle['related'] = array_values($vehicle['related']);
					$i = $related_count;
				}
			}

			shuffle($vehicle['related']);

			return $vehicle;

		}
	}

	return $output;

}

?>