
<div class="motordesk-search-vehicle" onclick="motordesk_url('/<?php print esc_attr($motordesk['url']); ?>/<?php print esc_attr($vehicle['id']).'/'.esc_attr($vehicle['url']); ?>/');">

<?php
if (isset($vehicle['photo'][0])) {
    print '<img src="'.esc_attr($vehicle['photo'][0]).'" width="100%" alt="'.esc_attr($vehicle['data']['vehicle']['text']).'" />';
}
?>

    <div>
        
        <a href="/<?php print esc_attr($motordesk['url']); ?>/<?php print esc_attr($vehicle['id']).'/'.esc_attr($vehicle['url']); ?>/"><?php print esc_html($vehicle['data']['vehicle']['make']); ?></a>
        <a href="/<?php print esc_attr($motordesk['url']); ?>/<?php print esc_attr($vehicle['id']).'/'.esc_attr($vehicle['url']); ?>/"><big><b><?php print esc_html($vehicle['data']['vehicle']['model']); ?></b></big></a>

        <hr />

        <?php print date('Y', strtotime($vehicle['data']['vehicle']['registered'])); ?> | <?php print motordesk_number($vehicle['data']['vehicle']['mileage'], 0, true); ?> <?php print motordesk_distance($motordesk, $vehicle['data']['vehicle']['mileage']); ?> | <?php print esc_html($vehicle['data']['vehicle']['body']); ?>

    </div>

</div>
