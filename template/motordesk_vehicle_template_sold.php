<?php

// MotorDesk container div
print '<div class="motordesk-search">';

// MotorDesk result container div
print '<div class="motordesk-search-result">';

// Loop through search results
foreach ($motordesk['sold']['result'] as $vehicle) {

	// Load search result
	include dirname(__FILE__).'/motordesk_vehicle_template_search_result.php';

}

if (count($motordesk['sold']['result'])==0) {

	print '<p>No vehicles found.</p>';

}

// End MotorDesk result container div
print '</div>';

// End MotorDesk container div
print '</div>';

?>