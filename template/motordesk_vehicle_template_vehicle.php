<?php

// MotorDesk container div
print '<div class="motordesk-vehicle">';

$vehicle = $motordesk['vehicle'];

// Load vehicle content
include dirname(__FILE__).'/motordesk_vehicle_template_vehicle_content.php';

// End MotorDesk container div
print '</div>';

?>