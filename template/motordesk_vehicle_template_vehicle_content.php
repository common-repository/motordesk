<?php
// Uncomment to view full vehicle array
//print '<pre>'.print_r($vehicle,1).'</pre>';
?>
<div>

<?php
if (isset($vehicle['photo'][0])) {
    print '<img src="'.esc_attr($vehicle['photo'][0]).'" width="100%" alt="'.esc_attr($vehicle['data']['vehicle']['text']).'" />';
}
?>   

    <h3><?php print esc_html($vehicle['data']['vehicle']['text']); ?></h3>

    <hr />

    <big><b><?php print esc_html($motordesk['currency_symbol']).motordesk_number($vehicle['data']['stock']['price_forecourt'], 2, true); ?></b></big>

    <p></p>
    
    <p>
        <!-- Contact support to set-up iframes -->
        <a href="<?php print esc_attr($vehicle['url_full']); ?>/reserve/" class="button">Reserve</a>
        <a href="<?php print esc_attr($vehicle['url_full']); ?>/part-exchange/" class="button">Part Exchange</a>
        <a href="<?php print esc_attr($vehicle['url_full']); ?>/test-drive/" class="button">Test Drive</a>
    </p>

    <p><?php print wp_kses_post($vehicle['data']['option']['website']); ?></p>

    <div class="motordesk-vehicle-feature">

        <table width="100%">
            <tbody>
                <tr>
                    <td>Year:</td>
                    <td><?php print date('Y', strtotime($vehicle['data']['vehicle']['registered'])); ?></td>
                </tr>
                <tr>
                    <td>Mileage:</td>
                    <td><?php print motordesk_number($vehicle['data']['vehicle']['mileage'], 0, true); ?> <?php print motordesk_distance($motordesk, $vehicle['data']['vehicle']['mileage']); ?></td>
                </tr>
                <tr>
                    <td>Body Type:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['body']); ?></td>
                </tr>
                <tr>
                    <td>Color:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['colour']); ?></td>
                </tr>
                <tr>
                    <td>Fuel Type:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['fuel']); ?></td>
                </tr>
                <tr>
                    <td>Transmission:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['transmission']); ?></td>
                </tr>
                <tr>
                    <td>Drivetrain:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['drivetrain']); ?></td>
                </tr>
                <tr>
                    <td>Doors:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['doors']); ?></td>
                </tr>
                <tr>
                    <td>Seats:</td>
                    <td><?php print esc_html($vehicle['data']['vehicle']['seats']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <p></p>
    
<?php

if ((isset($vehicle['data']['option']['feature'])) && (count($vehicle['data']['option']['feature'])>0)) {

    foreach ($vehicle['data']['option']['feature'] as $type => $category) {

        if ($type=='Optional') {
            print '<p><b>Optional Extras</b><ul>';
        } else {
            print '<p><b>Standard Features</b><ul>';
        }

        foreach ($category as $title => $feature) {

            print '<li>'.esc_html($title).'<ul>';

            foreach ($feature as $option) {

                print '<li>'.esc_html($option).'</li>';

            }

            print '</ul></li>';

        }

        print '</ul></p>';

    }
}

if (count($vehicle['photo'])>1) {

    print '<p><b>Photos</b></p><p>';

    for ($i=1; $i<count($vehicle['photo']); $i++) {

        print '<a href="'.esc_attr($vehicle['photo'][$i]).'" target="_blank"><img src="'.esc_attr($vehicle['photo'][$i]).'" width="31%" alt="" style="margin:0.25em;display:inline-block;" /></a>';

    }

    print '</p>';

}

if (count($vehicle['related'])>0) {

    print '<p><b>Related Vehicles</b><ul>';

    for ($i=0; $i<4; $i++) {
        if (isset($vehicle['related'][$i])) {

            print '<li><a href="/'.esc_attr($motordesk['url']).'/'.esc_attr($vehicle['related'][$i]['id']).'/'.esc_attr($vehicle['related'][$i]['url']).'/">'.esc_attr($vehicle['related'][$i]['data']['vehicle']['text']).'</a></li>';

        }
    }

    print '</ul></p>';

}


?>

</div>