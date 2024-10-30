<?php
/**
 * @package motordesk
 * @version 1.1.2
 */

if (!defined( 'WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Remove MotorDesk indexes
$exists = get_transient('motordesk-i');
if (!is_array($exists)) {
    $exists = array();
}

$exists_keys = array_keys($exists);
$exists_count = count($exists_keys);

for ($i=0; $i<$exists_count; $i++) {

    $exists_key = $exists_keys[$i];

    delete_transient('motordesk-v-'.$exists_key);

}

$index = array('alpha', 'type_body', 'make_model', 'price', 'monthly', 'mileage', 'age', 'engine_size', 'fuel', 'transmission', 'drivetrain', 'seats', 'doors', 'colour', 'acceleration', 'mpg', 'co2', 'emission_class', 'ulez', 'caz', 'insurance', 'type_make', 'location', 'field');
$index_count = count($index_keys);

for ($i=0; $i<$index_count; $i++) {

    delete_transient('motordesk-i-'.$index[$i]);

}

delete_transient('motordesk-i');


// Remove plugin data
if (get_option('motordesk')) {

	delete_option('motordesk');

}

?>