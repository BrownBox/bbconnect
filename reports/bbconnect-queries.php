<?php

function bbconnect_reports_queries() {

    echo '    <div id="saved-queries" class="drawer"></div>'."\n";

}
//THIS FUNCTION IS CALLED BY AJAX TO RELOAD THE SEARCH TAB WITH THE RESULT
function bbconnect_display_savedsearches(){
    global $current_user;
    $saved_searches_layout='';
    $args_private = array(
            'posts_per_page'    => -1,
            'orderby'           => 'post_name',
            'order'             => 'ASC',
            'post_type'         => 'savedsearch',
            'post_status'       => 'publish',
            'author'            =>  $current_user->ID,
            'meta_query'        => array(
                    array(
                            'key' => 'private',
                            'value' => 'true',
                    )
            ),
    );

    $args_public = array(
            'posts_per_page'    => -1,
            'orderby'           => 'post_name',
            'order'             => 'ASC',
            'post_type'         => 'savedsearch',
            'post_status'       => 'publish',
            'meta_query'        => array(
                    array(
                            'key' => 'private',
                            'value' => 'false',
                    )
            ),
    );
    $private_savedsearches = get_posts( $args_private );
    $public_savedsearches = get_posts( $args_public );

    $merged_savedsearches = array_merge($private_savedsearches,$public_savedsearches);

    usort($merged_savedsearches, "bbconnect_sort_saved_searched");

    echo '<style>'."\n".'.dashicons.dashicons-trash:hover {color: #d54e21;} .dashicons.dashicons-trash {color: #aaa;} td{border-bottom: 1px solid #eee;} tr:hover {background: none repeat scroll 0 0 rgba(20, 0, 200, 0.05);}'."\n".'</style>'."\n";
    echo '<table cellspacing="5" class="wp-list-table widefat">'."\n";
    echo '    <thead>'."\n";
    echo '        <tr>'."\n";
    echo '            <th>Archive</th>'."\n";
    echo '            <th>ID</th>'."\n";
    echo '            <th>Search Title</th>'."\n";
    echo '            <th>Private</th>'."\n";
    echo '            <th>Segment</th>'."\n";
    echo '            <th>Category</th>'."\n";
    echo '        </tr>'."\n";
    echo '    </thead>'."\n";
    echo '    <tbody>'."\n";
    $user_data = get_userdata(get_current_user_id());
    foreach ($merged_savedsearches as $merged_savedsearch) {
        $meta = get_post_meta( $merged_savedsearch->ID );
        echo '        <tr >'."\n";
        echo '            <td width="3%" class="gredit-column">'."\n";
        echo '                <span class="dashicons dashicons-trash" id="'.$merged_savedsearch->ID.'-delete" specialid="'.$merged_savedsearch->ID.'" ></span>'."\n";
        echo '                <span class="dashicons dashicons-trash" id="'.$merged_savedsearch->ID.'-undelete" specialid="'.$merged_savedsearch->ID.'" style="display:none;color:#D54E21;"></span>'."\n";
        echo '            </td>'."\n";
        echo '            <td width="5%">'."\n";
        echo '                <a href="' . admin_url( '/users.php?page=bbconnect_reports&savedsearch=' . $merged_savedsearch->ID ) .'">' . $merged_savedsearch->ID .'</a>'."\n";
        echo '            </td>'."\n";
        echo '            <td>'."\n";
        echo '                <a href="' . admin_url( '/users.php?page=bbconnect_reports&savedsearch=' . $merged_savedsearch->ID ) .'">' . $merged_savedsearch->post_title .'</a>'."\n";
        echo '            </td>'."\n";
        if ($meta['private'][0]=='true') {
            echo '            <td width="5%"><span class="dashicons dashicons-yes"></span></td>'."\n";
        } else {
            echo '            <td width="5%"><span class="dashicons dashicons-no" style="color:#aaa;"></span></td>'."\n";
        }

        if ($meta['segment'][0]=='true') {
            echo '            <td width="5%"><span class="dashicons dashicons-yes"></span></td>'."\n";
        } else {
            echo '            <td width="5%"><span class="dashicons dashicons-no" style="color:#aaa;"></span></td>'."\n";
        }

        if ($meta['category'][0]=='true') {
            echo '            <td width="5%"><span class="dashicons dashicons-yes"></span></td>'."\n";
        } else {
            echo '            <td width="5%"><span class="dashicons dashicons-no" style="color:#aaa;"></span></td>'."\n";
        }


        echo '        </tr>'."\n";
    }
    echo '    </tbody>'."\n";
    echo '    </table><br>'."\n";

    // foreach ($private_savedsearches as $private_savedsearch) {
    //     $saved_searches_layout.= '<div><input type="checkbox" checkboxid="'.$private_savedsearch->ID.'"><a href="' . admin_url( '/users.php?page=bbconnect_reports&savedsearch=' . $private_savedsearch->ID ) .'">' . $private_savedsearch->post_title .'-' . $private_savedsearch->ID .'</a></div>'."\n";
    // }

    // $public_savedsearches = get_posts( $args_public );
    // if(count($public_savedsearches) > 0) $saved_searches_layout.= '<h2>Search Archives saved as Public</h2><br>'."\n";
    // foreach ($public_savedsearches as $public_savedsearch) {
    //     $saved_searches_layout.= '<div><span class="dashicons dashicons-trash" id="'.$public_savedsearch->ID.'"></span><input type="checkbox" checkboxid="'.$public_savedsearch->ID.'"><a href="' . admin_url( '/users.php?page=bbconnect_reports&savedsearch=' . $public_savedsearch->ID ) .'">' . $public_savedsearch->post_title .'-' . $public_savedsearch->ID .'</a></div>'."\n";
    // }
    // echo $saved_searches_layout;
    die;
}

function bbconnect_sort_saved_searched($a, $b) {
    return strcasecmp($a->post_title, $b->post_title);
}

// Archive a search result with @ $post_id
//FUNCTION TO BE CALLED BY AJAX
function bbconnect_archive_saved_search(){
    // Update post @ $post_id
    $query_string =  $_POST['data'];
    $query_string = explode(',',$query_string);
    $post_id = $query_string[0];
    $action = $query_string[1];
    $my_post = array(
            'ID'           => $post_id,
            'post_status'   => $action,
    );

    // Update the post into the database
    $response = wp_update_post( $my_post );
    echo $response;
    die;
}
