<?php
if ($motordesk['search']['pagination']['total_pages']>=1) {

    print '<nav>';

    if ($motordesk['search']['pagination']['previous']!==false) {

        // Previous page

        print '<a href="/'.esc_attr($motordesk['url']).'/?'.esc_attr($motordesk['search']['query']).'&mt_results='.esc_attr($motordesk['search']['results']).'&mt_page='.esc_attr($motordesk['search']['pagination']['previous']).'&mt_sort='.esc_attr($motordesk['search']['sort']).'">Prev</a>';

    }

    foreach ($motordesk['search']['pagination']['pages'] as $page) {

        if ($page===false) {

        } elseif ($page==$motordesk['search']['page']) {

            // Current page

            print '<a href="/'.esc_attr($motordesk['url']).'/?'.esc_attr($motordesk['search']['query']).'&mt_results='.esc_attr($motordesk['search']['results']).'&mt_page='.esc_attr($page).'&mt_sort='.esc_attr($motordesk['search']['sort']).'"><b>'.esc_html($page + 1).'</b></a>';

        } else {

            // Pagination options

            print '<a href="/'.esc_attr($motordesk['url']).'/?'.esc_attr($motordesk['search']['query']).'&mt_results='.esc_attr($motordesk['search']['results']).'&mt_page='.esc_attr($page).'&mt_sort='.esc_attr($motordesk['search']['sort']).'">'.esc_html($page + 1).'</a>';

        }

    }

    if ($motordesk['search']['pagination']['next']!==false) {

        // Next page

        print '<a href="/'.esc_attr($motordesk['url']).'/?'.esc_attr($motordesk['search']['query']).'&mt_results='.esc_attr($motordesk['search']['results']).'&mt_page='.esc_attr($motordesk['search']['pagination']['next']).'&mt_sort='.esc_attr($motordesk['search']['sort']).'">Next</a>';

    }

    print '</nav>';

}

?>