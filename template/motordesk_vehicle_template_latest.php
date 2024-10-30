<?php

if (!isset($motordesk['search_url'])) {

	print 'Please set Search Page URI in plugin settings.';

} elseif (count($motordesk['latest'])>0) {

	// MotorDesk container div
	print '<div class="motordesk-latest">';

	// Loop through search results
	for ($i=0; $i<4; $i++) {

		if (isset($motordesk['latest'][$i])) {

			$vehicle = $motordesk['latest'][$i];

			// Load search result
			include dirname(__FILE__).'/motordesk_vehicle_template_search_result.php';

		}
	}

	// End MotorDesk result container div
	print '</div>';

}

?>