<?php

// MotorDesk container div
print '<div class="motordesk-search">';

// Load search form, $embed is false
include dirname(__FILE__).'/motordesk_vehicle_template_search_form.php';

// MotorDesk result container div
print '<div class="motordesk-search-result">';

// Loop through search results
foreach ($motordesk['search']['result'] as $vehicle) {

	// Load search result
	include dirname(__FILE__).'/motordesk_vehicle_template_search_result.php';

}

if (count($motordesk['search']['result'])==0) {

	print '<p>No vehicles found.</p>';

}

// End MotorDesk result container div
print '</div>';

// Load pagination template
include dirname(__FILE__).'/motordesk_vehicle_template_search_pagination.php';

// End MotorDesk container div
print '</div>';

?>