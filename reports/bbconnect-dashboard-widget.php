<?php

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function bbconnect_actions_add_dashboard_widgets() {
    wp_add_dashboard_widget('bbconnect_actions_dashboard_widget', // Widget slug.
                            'Workqueues', // Title.
                            'bbconnect_actions_dashboard_widget_function'); // Display function.
}
add_action('wp_dashboard_setup', 'bbconnect_actions_add_dashboard_widgets');

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function bbconnect_actions_dashboard_widget_function() {
    echo ' <table cellspacing="0" class="widefat gf_dashboard_view" style="border:0px;">' . "\n";
    echo '        <thead>' . "\n";
    echo '            <tr>' . "\n";
    echo '                <td class="gf_dashboard_form_title_header" style="font-style: italic; font-weight: bold; padding: 8px 18px!important; text-align: left">Workqueue</td>' . "\n";
    echo '                <td class="gf_dashboard_entries_unread_header" style="font-style: italic; font-weight: bold; padding: 8px 18px!important; text-align: center">For Action</td>' . "\n";
    echo '            </tr>' . "\n";
    echo '        </thead>' . "\n";
    echo '        <tbody class="list:user user-list">' . "\n";

    $taxonomies = array(
            'bb_note_type'
    );

    $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'fields' => 'all'
    );

    $note_types = get_terms($taxonomies, $args);

    foreach ($note_types as $note_type) {
        if ($note_type->parent == 0) continue;

        // Display whatever it is you want to show.
        $args = array(
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_type' => 'bb_note',
                'post_status' => 'publish',
                'meta_query' => array(
                        array(
                                'key' => '_pp_action_required',
                                'value' => 'true',
                        ),
                ),
                'bb_note_type' => $note_type->slug,
        );
        $notes = get_posts($args);
        if (count($notes) > 0)
            echo bbconnect_actions_dashboard_widget_row($note_type->term_id, $note_type->name, $note_type->slug, count($notes));
    }

    echo '         </tbody>' . "\n";
    echo '     </table>' . "\n";
}

function bbconnect_actions_dashboard_widget_row($term_id, $name, $slug, $count) {
    $row = '';
    $row .= '  <tr class="author-self status-inherit" valign="top">' . "\n";
    $row .= '      <td class="gf_dashboard_form_title column-title" style="padding:8px 18px;">' . "\n";
    $row .= '          <a class="form_title_unread" href="users.php?page=work_queues_submenu&queue_id=' . $term_id . '" style="font-weight:bold;" title="' . $name . ' : View All Actions">' . $name . '</a>' . "\n";
    $row .= '      </td>' . "\n";
    $row .= '      <td class="gf_dashboard_entries_unread column-date" style="padding:8px 18px; text-align:center;">' . "\n";
    $row .= '          <a class="form_entries_unread" href="users.php?page=work_queues_submenu&queue_id=' . $term_id . '" style="font-weight:bold;" title="Last Entry: 4 October, 2014 at 12:58 pm">' . $count . '</a>' . "\n";
    $row .= '      </td>' . "\n";
    $row .= '  </tr>' . "\n";

    return $row;
}

?>