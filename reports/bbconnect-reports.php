<?php


/* -----------------------------------------------------------
    REPORT NAVIGATION
   ----------------------------------------------------------- */
   global $totalValues;
    $totalValues = array(); // hold an array of totals;

function bbconnect_pagination( $ret_res, $display = false ) {

    // IF THIS IS A GET REQUESET
    if ( false != $display ) {

        global $query_transient;

        if ( isset( $_GET['page_num'] ) ) {
           $page_num = $_GET['page_num'];
        } else {
           $page_num = 1;
        }

        if ( isset( $query_transient ) ) {
           $trail = '&trans=' . $query_transient;
        } elseif ( isset( $_GET['trans'] ) ) {
           $trail = '&trans=' . $_GET['trans'];
        } else {
            $trail = false;
        }

        if ( isset( $_GET['order_by'] ) ) {
           $order_query = '&order_by=' . $_GET['order_by'];
        } else {
           $order_query = false;
        }

        /*
        global $cur_url;
        $crumbs = explode( '?', $_SERVER['REQUEST_URI'] );
        if ( '' != $crumbs[1] ) {
            if ( isset( $_GET['page_num'] ) )
                $crumbs[1] = str_replace( "page_num=".$_GET['page_num'], "", $crumbs[1] );

            if ( '&' == substr( $crumbs[1], 0, 1 ) ) {
                $trail = $crumbs[1];
            } else {
                $trail = '&' . $crumbs[1];
            }

        } else {
            $trail = '';
        }
        */
        $cur_url = '?page_num=' . ( $page_num + 1 );
        $prev_url = '?page_num=' . ( $page_num - 1 );
        $last_url = '?page_num=' . $ret_res['max_num_pages'];
        $first_url = '?page_num=1';

    } else {

        if ( isset( $ret_res['page_num'] ) ) {
           $page_num = $ret_res['page_num'];
        } else {
           $page_num = 1;
        }

        $cur_url = ( $page_num + 1 );
        $prev_url = ( $page_num - 1 );
        $last_url = $ret_res['max_num_pages'];
        $first_url = '1';

    }

    $return_html = '<div class="pagination">';

     // DISPLAY DATA

     //check if user per page is all
     if(strtolower($ret_res['users_per_page'])=='all'){
         $cpc = $ret_res['users_count'];
     }
     else{
         $cpc = $ret_res['users_per_page'];
     }

     $cpg = $cpc * ( $page_num - 1 ) + 1;
     $ecpg = $cpc * ( $page_num - 1 ) + 1;
     $cpgr = $cpg + ( $cpc - 1 );
     $npc = $page_num + 1;
     $npg = $cpc * ( $npc - 1 ) + 1;
     $ppc = $page_num - 1;
     $ppg = $cpc * ( $ppc - 1 ) + 1;

     // TRANSLATION CONSOLIDATION
     $t_of = __( 'of', 'bbconnect' );

     // RESULT CEILING START
     $return_html .= '<span class="muted">';

     //MODIFY THE RANGE IF ON THE LAST PAGE
     if ( $page_num == $ret_res['max_num_pages'] ) {
         $return_html .= $ecpg . '-' . $ret_res['users_count'] . ' ' . $t_of . ' ' . $ret_res['users_count'] . ' ';
     } else {
         $return_html .= $cpg . '-' . $cpgr . ' ' . $t_of . ' ' . $ret_res['users_count'] . ' ';
     }

     // RESULT CEILING END
     $return_html .= '</span>';

     // IF THIS IS A GET REQUESET
     if ( false != $display ) {

         // IF THE USER IS ON THE FIRST PAGE, DISABLE THE NAVIGATION
         if ( $page_num > 1 ) {
             $return_html .= '<a href="' . $first_url . '" class="button round pppag">&laquo;</a> <a href="' . $prev_url . '" class="button round pppag">&lsaquo;</a> ';
         } else {
             $return_html .= '<a class="button disabled round pppag">&laquo;</a> <a class="button disabled round pppag">&lsaquo;</a> ';
         }

         // DISPLAY THE CURRENT PAGE AND RANGE
         $return_html .= sprintf( __( 'Page %1$s', 'bbconnect' ), $page_num.' '.$t_of.' '.$ret_res['max_num_pages'] );

         // IF THE USER IS ON THE LAST PAGE, DISABLE THE NAVIGATION
         if ( $page_num == ( $ret_res['max_num_pages'] ) ) {
             $return_html .= ' <a class="button disabled round pppag">&rsaquo;</a> <a class="button disabled round pppag">&raquo;</a>';
         } elseif ( $page_num < $ret_res['max_num_pages'] ) {
             $return_html .= ' <a href="' . $cur_url . '" class="button round pppag">&rsaquo;</a> <a href="' . $last_url . '" class="button round pppag">&raquo;</a>';
         }

     } else {

         // IF THE USER IS ON THE FIRST PAGE, DISABLE THE NAVIGATION
         // <a class="report-go outside" title="filter-form" rel="2">go again</a>
         if ( $page_num > 1 ) {
             $return_html .= '<a class="button report-go outside" title="filter-form" rel="' . $first_url . '" rev="page_num">&laquo;</a> <a class="button report-go outside" title="filter-form" rel="' . $prev_url . '" rev="page_num">&lsaquo;</a> ';
         } else {
             $return_html .= '<a class="button disabled">&laquo;</a> <a class="button disabled">&lsaquo;</a> ';
         }

         // DISPLAY THE CURRENT PAGE AND RANGE
         $return_html .= sprintf( __( 'Page %1$s', 'bbconnect' ), $page_num.' '.$t_of.' '.$ret_res['max_num_pages'] );

         // IF THE USER IS ON THE LAST PAGE, DISABLE THE NAVIGATION
         if ( $page_num == ( $ret_res['max_num_pages'] ) ) {
             $return_html .= ' <a class="button disabled">&rsaquo;</a> <a class="button disabled">&raquo;</a>';
         } elseif ( $page_num < $ret_res['max_num_pages'] ) {
             $return_html .= ' <a class="button report-go outside" title="filter-form" rel="' . $cur_url . '" rev="page_num">&rsaquo;</a> <a class="button report-go outside" title="filter-form" rel="' . $last_url . '" rev="page_num">&raquo;</a>';
         }

    }

    $return_html .= '</div>';

    return $return_html;

}


function bbctheme_toggle_view( $views ) {

    // HARD-CODED ARRAY OF OPTIONS FOR NOW
    // $views = array( 'grid', 'list', 'calendar' );
    // $display_views = '<span class="toggle-view">';

    // // CHECK FOR THE COOKIE TO REMEMBER THEIR SELECTION
    // if ( isset( $_COOKIE['view'] ) && in_array( $_COOKIE['view'], $views ) ) {
    //     $cookie_view = $_COOKIE['view'];
    // } else {
    //     $cookie_view = $views[0];
    // }

    // foreach ( $views as $key => $value ) {

    //     // SET THE DEFAULT SELECTION
    //     if ( $value === $cookie_view ) {
    //         $viewing = ' on';
    //     } else {
    //         $viewing = '';
    //     }

    //     // ECHO THE LINK
    //     $display_views .= '<a class="' . $value . '-view content-view' . $viewing . '" title="view as ' . $value . '" rev="' . $value . '">&nbsp;</a> ';
    // }

    // $display_views .= '</span>';

    return $display_views;

}

/* -----------------------------------------------------------
    REPORT QUERY
   ----------------------------------------------------------- */


// PROCESS THE AJAX REQUEST
function bbconnect_report_process() {

    // RUN A SECURITY CHECK
    if ( ! wp_verify_nonce( $_POST['bbconnect_report_nonce'], 'bbconnect-report-nonce' ) )
        die ( 'terribly sorry.' );

    // MAKE SURE WE HAVE A CLEAN START...
    $_POST = bbconnect_scrub( 'bbconnect_sanitize', $_POST );

    // UNWRAP THE VALUES
    if ( isset( $_POST['data'] ) )
        parse_str( $_POST['data'], $_POST );

    // UNWRAP THE VALUES
    if ( isset( $_POST['userdata'] ) )
        parse_str( $_POST['userdata'], $_POST );

    // UNWRAP THE VALUES
    if ( isset( $_POST['fdata'] ) )
        parse_str( $_POST['fdata'], $_POST );

    // RUN THE SWITCH
    $post_keys = array_keys( $_POST['action'] );
    $pro_func = array_shift( $post_keys );
    $pro_var = array_shift( $_POST['action'] );
    global $ret_res;
    $ret_res = $pro_func( $_POST );

    if ( false != $ret_res ) {
        //echo '<div>'.memory_get_usage().'</div>';

        // REMEMBER THE USERS SEARCH
        global $current_user;
        $filter_query = $ret_res['post_vars'];
        update_option( '_bbconnect_' . $current_user->ID . '_current', $filter_query );

        // DISPLAY THE RESULTS
        bbconnect_report_display( $ret_res );
        //echo '<div>'.memory_get_usage().'</div>';
    }

    die();

}


// THE MAIN FUNCTION...
function bbconnect_search() {

    global $ret_res, $current_user, $filter_query;

    // BROWN BOX
    // delete_transient( 'bbconnect_'.$current_user->ID.'_last_saved' );

    if( isset( $_GET['savedsearch'] ) ) {
        $saved_search = get_post( $_GET['savedsearch'] );
    } else {
        $last_saved = get_transient( 'bbconnect_'.$current_user->ID.'_last_saved' );
        if( isset( $last_saved  ) ) $saved_search = get_post( $last_saved );
    }

    if( isset( $saved_search ) ) {
        $last_query = unserialize( $saved_search->post_content );
    } else {
        $last_query = get_option( '_bbconnect_' . $current_user->ID . '_current' );
    }

    if( $_GET['save'] = 'true' && $_GET['save'] != NULL ) {
        if( $saved_search->post_content !== serialize( get_option( '_bbconnect_' . $current_user->ID . '_current' ) ) )  {
            $post = array(
              'post_content'   => serialize( get_option( '_bbconnect_' . $current_user->ID . '_current' ) ),
              'post_title'     => 'bbconnect_'.$current_user->ID.'_saved_search',
              'post_status'    => 'publish',
              'post_type'      => 'savedsearch',
              'post_author'    => $current_user->ID,
            );
            $wp_error = wp_insert_post( $post, $wp_error );
            if( !is_array( $wp_error ) ) {
                echo '<div class="updated"><p>Search has been saved as <a href="/post.php?post=' . $wp_error . '&action=edit">savedsearch-' . $wp_error . '</a></p></div>'."\n";
                if ( false === $recently_saved ) $recently_saved = array();
                set_transient( 'bbconnect_'.$current_user->ID.'_last_saved', $wp_error, 3600 );
            } else {
                var_dump( $wp_error );
            }
        }
    }

    // IF WE ARE PROCESSING A LINKED QUERY
    if ( isset( $_GET['query'] ) ) {

        $filter_query = unserialize( urldecode( get_option( $_GET['query'] ) ) );
        $edit_panel = '';
        $action_panel = '';
        $query_panel = '';

    } else if ( isset( $_GET['import'] ) ) {

        $filter_query = array( 'display' => 'close' );
        $edit_panel = '';
        $action_panel = 'open';
        $query_panel = '';

    } else {

        if ( false != $last_query ) {
            $filter_query = $last_query;
        } else {
            $filter_query = false;
        }
        $edit_panel = '';
        $action_panel = '';
        $query_panel = '';

    }

    // IF WE ARE UPLOADING MEMBERS...
    if ( isset( $_POST['upload_members'] ) || isset( $_POST['import_members'] ) ) {

        $filter_query = array( 'display' => 'close' );
        $edit_panel = '';
        $action_panel = 'open';
        $query_panel = '';

        // SET THE REPORT DISPLAY
        if ( isset( $_POST['upload_members'] ) )
            $report_upload_members = true;

        if ( isset( $_POST['import_members'] ) )
            $report_import_members = true;

    }

    ?>
    <div id="bbconnect" class="wrap">
        <div id="icon-users" class="icon32"><br /></div>
        <h2><a href="<?php echo admin_url( 'users.php' ); ?>" class="bbconnect-refresh"><?php _e( 'User Reports', 'bbconnect' ); ?></a></h2>
        <h2 id="report-nav" class="nav-tab-wrapper">
            <a id="filter-tab" class="nav-tab<?php if ( '' == $action_panel ) echo ' nav-tab-active'; ?>" title="filter">
                <span class="bbconnect-icon query">&nbsp;</span>
                <div class="bbconnect-icon-text"><?php _e( 'Search', 'bbconnect' ); ?></div>
                <div class="bbconnect-icon-ghost">&nbsp;</div>
            </a>
            <!-- <a class="nav-tab<?php if ( '' != $action_panel ) echo ' nav-tab-active'; ?>" title="group-action">
                <div class="bbconnect-icon action">&nbsp;</div>
                <div class="bbconnect-icon-text"><?php _e( 'Actions', 'bbconnect' ); ?></div>
                <div class="bbconnect-icon-ghost">&nbsp;</div>
            </a> -->
<!--             <a class="nav-tab" title="group-edit">
                <div class="bbconnect-icon edit">&nbsp;</div>
                <div class="bbconnect-icon-text"><?php //_e( 'Edits', 'bbconnect' ); ?></div>
                <div class="bbconnect-icon-ghost">&nbsp;</div>
            </a> -->
            <a class="nav-tab" id="SavedSearchesTab" title="saved-queries">
                <div class="bbconnect-icon bookmark">&nbsp;</div>
                <div class="bbconnect-icon-text"><?php _e( 'Saved Searches', 'bbconnect' ); ?></div>
                <div class="bbconnect-icon-ghost">&nbsp;</div>
            </a>
        </h2>
        <div id="report-options"><div class="inside">
            <?php /*wp_nonce_field('usermeta-search-nonce');*/ ?>
            <?php bbconnect_filter_profile( $filter_query ); ?>
            <?php bbconnect_reports_actions( $action_panel ); ?>
            <?php bbconnect_group_edit( $edit_panel ); ?>
            <?php bbconnect_reports_queries( $query_panel ); ?>
        </div></div>

        <div>
            <?php
                global $query_limit;
                echo $query_limit;
            ?>
        </div>

        <div id="report-display">
            <?php
                if ( isset( $report_upload_members ) ) {
                    bbconnect_upload_members_process();

                } else if ( isset( $report_import_members ) ) {
                    $ret_res = bbconnect_import_members_process();
                    bbconnect_report_display( $ret_res );

                } else if ( isset( $_GET['query'] ) ) {
                    $ret_res = bbconnect_filter_process( $filter_query );
                    bbconnect_report_display( $ret_res );
                    update_option( '_bbconnect_' . $current_user->ID . '_current', $filter_query );

                } else if ( isset( $_GET['import'] ) ) {
                    $transient = get_option( $_GET['import'] );
                    $ret_res = bbconnect_filter_process( $transient['data'] );
                    bbconnect_report_display( $ret_res );

                } else if ( isset( $_GET['reset'] ) ) {
                    delete_option( '_bbconnect_' . $current_user->ID . '_current' );

                } else {
                    $last_query = get_option( '_bbconnect_' . $current_user->ID . '_current' );
                    if ( false != $last_query ) {
                        $ret_res = bbconnect_filter_process( $last_query );
                        bbconnect_report_display( $ret_res );
                    }

                }
            ?>
        </div>

    </div>

        <?php
        unset($ret_res);
}

function bbconnect_report_display( $ret_res = array() ) {

    // SETUP DISPLAY GLOBALS
    global $ret_res, $table_head, $table_body, $pagenow, $action_array;
    global $totalValues, $fieldInfos;

    // EXTRACT THE VARIABLES
    extract( $ret_res, EXTR_SKIP );

    // COUNT THE ADDRESSES FOR DISPLAY
    global $bbconnect_address_count;
    $bbconnect_user_metas = bbconnect_get_user_metadata( array( 'group_break' => true ) );
    $bbconnect_address_count = 0;
    foreach ( $bbconnect_user_metas as $pmkey => $pmval ) {
        if ( false != strstr( $pmval, 'address' ) ) {
            $bbconnect_address_count++;
        }
    }

    if ( is_admin() ) {

        // IF THERE ARE ANY ERRORS, LET THE USER KNOW!
        if ( isset( $bbconnect_errors ) && !empty( $bbconnect_errors ) ) {

            echo '<div class="error">';

            if ( is_array( $bbconnect_errors ) ) {
                foreach ( $bbconnect_errors as $error ) {
                    if ( is_wp_error( $error ) ) {
                        $error->get_error_message();
                    } else {
                        echo $error;
                    }
                }
            } else {
                echo $bbconnect_errors;
            }

            echo '</div>';

        }

        // FIRE OFF ANY NOTIFICATIONS FOR PLUGINS
        do_action( 'bbconnect_report_notifications', $ret_res );

    }

    $report_nav = '';

    $report_nav .= '<div id="report-navigation"><div class="inside">';
    $report_nav .= '<span class="report-tab">';
    if ( is_admin() ) {
        $report_nav .= bbconnect_pagination( $ret_res );
    } else {
        $report_nav .= bbconnect_pagination( $ret_res, true );
    }

    // ALLOW OTHER PLUGINS TO ADD VIEWS AND DO STUFF
    if ( is_admin() ) {
        $report_nav .= bbctheme_toggle_view( apply_filters( 'bbconnect_dir_tabs', array( 'list' ) ) );
    }
    $report_nav .= '</span>';
    echo $report_nav;

    do_action( 'bbconnect_report_navigation' );

    echo '</div></div>';

            if ( !isset( $all_search) || empty( $all_search ) )
                $all_search = $member_search;

            // HANDLE ACTIONS HERE
            do_action( 'bbconnect_process_action_search', $action_search, $ret_res );
            $action_array = apply_filters( 'bbconnect_process_action_array', false, $ret_res );
        ?>

        <div id="report-data-array" style="display: none;">
            <input name="_grexport[post_vars]" type="hidden" value="<?php echo rawurlencode( serialize( $ret_res['post_vars'] ) ); ?>" />
            <input name="_grexport[table_body]" type="hidden" value="<?php echo rawurlencode( serialize( $table_body ) ); ?>" />
            <input name="_grexport[bbconnect_address_count]" type="hidden" value="<?php echo rawurlencode( serialize( $bbconnect_address_count ) ); ?>" />
            <input name="_grexport[all_search]" type="hidden" value="<?php echo implode( '|', $ret_res['all_search'] ); ?>" />
            <input name="_grexport[action_search]" type="hidden" value="<?php echo rawurlencode( serialize( $action_search ) ); ?>" />
        </div>

        <form id="report-data-form" action="" enctype="multipart/form-data" method="POST">

            <div id="list" class="content-display" style="<?php if ( !isset( $_COOKIE['view'] ) || 'list' == $_COOKIE['view'] ) { echo ' display: block;'; } ?>">

                <?php

                // DISPLAY USERS OR...
                if ( false != $all_search && false != $member_search ) {

                    // SETUP TABLE HEADER DISPLAY
                    if ( '' != $table_head ) {

                        if ( is_array( $table_head ) ) {

                            $t_head = '';

                            foreach ( $table_head as $head ) {

                                // DISPLAY THE HEADER
                                $t_head .= '<th>' . $head[1] . '</th>';

                            }

                        } else {
                            $t_head = $table_head;
                        }

                    } else {
                        $t_head = '';
                    }

                    if ( false != $action_search ) {
                        if ( is_admin() ) {
                            $t_head .= '<th><table><tr><td class="gredit-column" width="5%"><input type="checkbox" id="action-gredit" class="gredit-action"';
                            if ( !isset( $ret_res['edit'] ) ) {
                                $t_head .= ' disabled="disabled"';
                            } else {
                                $t_head .= ' style="display: block;"';
                            }
                            $t_head .= ' /><span style="padding: 0 0 0 4%;">'. __( 'Actions', 'bbconnect' ) .'</span></td></tr></table></th>';
                        } else {
                            $t_head .= '<th>'. __( 'Actions', 'bbconnect' ) .'</th>';
                        }
                    }

                    // COUNT THE NUMBER OF COLUMNS
                    $col_count = 1;
                    $col_span = 1;
                    if ( !empty( $table_body) ) {
                        $col_count = $col_count + count( $table_body );
                        $col_span = count( $table_body );
                    }

                    if ( false != $action_search ) {
                        $col_count = $col_count + 2;
                        $act_span = true;
                    }

                    // CALCULATE THE COLUMN WIDTHS
                    $tdw = 95 / $col_count;
                    if ( !isset( $act_span ) ) {
                        $stw = '95%';
                    } else {
                        $stw = ( $tdw * $col_span );
                        $atw = $tdw * 2;
                    }

                ?>

                <table class="wp-list-table widefat" cellspacing="5">
                    <thead>
                        <tr>
                            <?php if ( is_admin() ) { ?>
                            <?php } ?>
                            <th style="min-width: 185px;text-align:left;"><?php echo apply_filters( 'bbconnect_reports_user_header', __( 'Users', 'bbconnect' ) ); ?></th>
                            <?php echo $t_head; ?>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                    // DO AN ACTION FOR OTHER TABS
                    // YOU'LL HAVE TO CALL THE GLOBALS MANUALLY!
                    do_action( 'bbconnect_prep_results_display', $ret_res );

                    $user_rows = '';
                    $note_ids = array();
                    // FOR THE REST... PAGINATE!
                    foreach( $member_search as $user_id) {
                        foreach ($ret_res['post_vars'] as $post_var) {
                            foreach ($post_var as $var_details) {
                                if ($var_details['field'] == 'bb_work_queue' && $var_details['operator'] == 'is') {
                                    $work_queues = $var_details['query'];
                                    $args = array(
                                            'author' => $user_id,
                                            'tax_query' => array(
                                                    array(
                                                            'taxonomy' => 'bb_note_type',
                                                            'field' => 'term_id',
                                                            'terms' => $work_queues,
                                                    ),
                                            ),
                                    );
                                    $notes = cw_get_action_items($args);
                                    foreach ($notes as $note) {
                                        $note_ids[$note->ID] = $note->ID;
                                    }
                                }
                            }
                        }
                        $user_rows .= bbconnect_rows( array( 'table_body' => $table_body, 'user_id' => $user_id, 'action_search' => $action_search, 'action_array' => $action_array, 'bbconnect_address_count' => $bbconnect_address_count, 'post_vars' => $ret_res['post_vars'], 'tdw' => $tdw ) );
                    }
                    echo $user_rows;
                ?>
                    </tbody>
                    <tfoot>
                        <?php
                        if(count($totalValues) > 0){ 
                        ?>
                        <tr>
                            <?php if ( is_admin() ) {

                                ?>
                            <?php } ?>
                            <th><?php echo apply_filters( 'bbconnect_reports_user_header', __( 'Total for displayed results)', 'bbconnect' ) ); ?></th>
                            <?php
                                    for($i = 1; $i <= count($table_body); $i++) {
                                    $value_display = $totalValues[$i] ? $totalValues[$i] : '';
                                    if ($fieldInfos[$i]['options']['field_type'] == 'number' && $fieldInfos[$i]['options']['is_currency'] && $value_display != '') {
                                        $value_display = '$'.number_format($value_display, 2);
                                    }
                                    echo "<th style='text-align: right;'>".apply_filters('bbconnect_reports_user_header', $value_display)."</th>"."\n";
                                }
                            ?>

                        </tr>
                        <tr>
                            <?php if ( is_admin() ) {

                                ?>
                            <?php } ?>
                            <th><?php echo apply_filters( 'bbconnect_reports_user_header', __( 'Average for displayed results)', 'bbconnect' ) ); ?></th>
                            <?php
                                for($i = 1; $i <= count($table_body); $i++) {
                                    //echo "<th>".apply_filters( 'bbconnect_reports_user_header', ($valueforavg)?__($valueforavg/$ret_res["users_count"],'bbconnect'):'' )."</th>"."\n";
                                    $value_display = $totalValues[$i] ? number_format($totalValues[$i]/count($member_search), 2) : '';
                                    if ($fieldInfos[$i]['options']['field_type'] == 'number' && $fieldInfos[$i]['options']['is_currency'] && $value_display != '') {
                                        $value_display = '$'.$value_display;
                                    }
                                    echo "<th style='text-align: right;'>".apply_filters('bbconnect_reports_user_header', $value_display)."</th>"."\n";
                                }
                            ?>

                        </tr>
                        <?php
                    }
                        ?>
                    </tfoot>
                </table>

                <?php

                } else if ( isset( $export_results ) ) {

                    echo '<p>' . $export_results . '</p>';

                } else if ( isset( $errors ) ) {
                    if($errors)
                        echo '<p>' . $errors . '</p>';
                    else
                        echo '<p>No records found.</p>';

                } else {
                    echo '<p>' . __( 'terribly sorry. i did not find anything.', 'bbconnect' ) . '</p>';
                }

                ?>

            </div>

            <?php if ( is_admin() ) do_action( 'bbconnect_results_display', $ret_res ); ?>

        </form>

<?php
    if (function_exists( 'bbconnect_report_quicklinks')) {
        $args = array();
        if (!empty($note_ids)) {
            $args['note_ids'] = $note_ids;
        }
        bbconnect_report_quicklinks($member_search, $args);
    } else {
        echo '<div id="quicklinks-wrapper">You should get the quicklinks addon!</div>';
    }

    unset( $ret_res );
    unset( $table_head );
    unset( $table_body );
    unset( $pagenow );
    unset( $all_search );
    unset( $member_search );
    unset( $action_search );
    unset( $action_array );
    unset( $bbconnect_errors );
    unset( $bbconnect_query );
    unset( $bbconnect_user_metas );
    unset( $bbconnect_address_count );
    unset( $current_member );
    unset( $currency );
    unset( $currency_array );
    unset( $tdw );
    unset( $_POST );
}

function bbconnect_grex_input( $args = '' ) {
    $defaults = array(
                    'u_key' => '',
                    'g_key' => '',
                    'g_val' => ''
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    if ( is_array( $g_key ) && empty( $g_key ) )
        return false;

    if ( '' == $u_key || '' == $g_key )
        return false;

    if ( '' == $g_val || empty( $g_val ) )
        $g_val = 'null';

    return false;

    if ( is_array( $g_key ) ) {

        foreach ( $g_key as $gg_key => $gg_val ) {
            if ( is_array( $g_val ) ) {
                return '<input type="hidden" name="_grex_vals['.$u_key.']['.$gg_val.']" class="grex-vals" value="'.$g_val[$gg_key].'" />';
            } else {
                return '<input type="hidden" name="_grex_vals['.$u_key.']['.$gg_val.']" class="grex-vals" value="'.$g_val.'" />';
            }
        }

    } else {

        return '<input type="hidden" name="_grex_vals['.$u_key.']['.$g_key.']" class="grex-vals" value="'.$g_val.'" />';

    }

}


function bbconnect_rows( $args = null ) {

    global $wpdb;
    global $totalValues, $fieldInfos;
    $positionInarray = 0; //determine which position we have in array
    $blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );

    // SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
    $defaults = array(
                    'user_id' => false,
                    'action_search' => false,
                    'action_array' => false,
                    'post_vars' => false,
                    'table_body' => false,
                    'action' => 'view',
                    'bbconnect_address_count' => 0,
                    'return' => false,
                    'tdw' => false,
                    'thiskey' => '',
                    'return_def' => true,
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    // SET A LOOKUP ARRAY FOR THE INITIAL QUERY
    $post_matches = array();
    if ( isset( $post_vars['search'] ) ) {
        foreach ( $post_vars['search'] as $k => $v ) {
            if (
                isset( $v['type'] ) &&
                'user' == $v['type'] &&
                isset( $v['field'] ) &&
                isset( $v['query'] )
            ) {
                $post_matches[$v['field']] = $v['query'];
            }
        }
    }

    // GET THE USER DATA
    $current_member = get_userdata( $user_id );
    if ( !$current_member )
        return false;

    // CHANGE DISPLAY BASED ON ACTION
    if ( 'edit' == $action ) {
        $display = ' style="display: block;"';
    } else {
        $display = ' disabled="disabled"';
    }

    // RETRIEVE THE USER ROW
    if ( false == $return ) {

        // RETRIEVE THE AVATAR
        $user_avatar = apply_filters( 'bbconnect_reports_user_avatar', get_avatar( $current_member->user_email, 32 ), $current_member );

        // RETRIEVE THE PROFILE FIELDS
        $user_fields = array(
                                $current_member->first_name . ' ' . $current_member->last_name,
        );
        $user_fields = apply_filters( 'bbconnect_reports_user_fields', $user_fields, $current_member );

        // RETRIEVE THE ACTIONS
        $user_actions = array();
        if ( is_admin() ) {
            $user_actions['edit'] = '<a href="' . admin_url( '/users.php?page=bbconnect_edit_user&user_id='.$current_member->ID ) . '">' . __( 'edit' ) . '</a>';
            $user_actions['edit in new tab'] = '<a href="' . admin_url( '/users.php?page=bbconnect_edit_user&user_id='.$current_member->ID ) . '" target="_blank">' . __( 'edit in new tab' ) . '</a>';
            $user_actions['email'] = '<a href="mailto:' . $current_member->user_email . '">' . __( 'email' ) . '</a>';
        } else {
            $user_actions['view'] = '<a href="' . home_url( '/bbconnect/?rel=viewprofile&amp;uid=' . $current_member->ID ) . '" class="bbconnectpanels-toggle" title="profile-' . $current_member->ID . '">' . __( 'view', 'bbconnect' ) . '</a>';
        }
        $user_actions = apply_filters( 'bbconnect_reports_user_actions', $user_actions, $current_member );

        // USER SETUP
        $return_html = '<tr>';
        // if ( is_admin() ) {
        //     $return_html .= '<td class="gredit-column" width="3%">';
        //     $return_html .= '<input type="checkbox" name="gredit_users[]" value="'.$current_member->ID.'" class="gredit-user subgredit"'.$display.' />';
        //     $return_html .= '<input type="hidden" value="'.$current_member->user_email.'" disabled="disabled" />';
        //     $return_html .= '</td>';
        // }
        $return_html .= '<td style="text-align:left;" class="bbconnect-column-default" width="'.$tdw.'%">';
        $return_html .= '<div class="username column-username">';

        // USER AVATAR
        $return_html .= '<div class="bbconnect-reports-user-avatar">' . $user_avatar . '</div>';

        // USER INFO - OPEN
        $return_html .= '<div class="bbconnect-reports-user-info">';

        // USER FIELDS
        $return_html .= '<div class="bbconnect-reports-user-fields">';
        $return_html .= implode( '<br />', $user_fields );
        $return_html .= '</span>';
        $return_html .= '<br />';

        // USER ACTIONS
        $return_html .= '<div class="bbconnect-reports-user-actions">';
        $return_html .= implode( ' | ', $user_actions );
        $return_html .= '</span>';

        // USER INFO - CLOSE
        $return_html .= '</div>';

        // USER CLOSEOUT
        $return_html .= '</div>';
        $return_html .= '</td>';

        // RESOURCE CLEANUP
        unset( $user_avatar );
        unset( $user_fields );
        unset( $user_actions );

    } else {

        $return_val = array();
        if ( false != $return_def ) {
            $return_val['ID'] = $current_member->ID;
            $return_val['email'] = $current_member->user_email;
            $return_val['first_name'] = $current_member->first_name;
            $return_val['last_name'] = $current_member->last_name;
            //$return_val['organization'] = $current_member->bbconnect_organization;
        }

    }

    if ( !empty( $table_body) ) {
        $tempTotals = array();

        foreach ( $table_body as $key => $value ) {
            //declare an empty array to hold temporal total values
             $positionInarray++;
            // HORRIBLY HACKISH MANIPULATIONS FOR ADDRESSES...
            if ( false != strstr( $key, 'address' ) ) {
                // THE KEY WITH THE NUMERIC IDENTIFIER
                $origkey = $key;
                // THE NUMERIC IDENTIFIER
                $thiskey = str_replace( '_', '', substr( $key, -2 ) );
                // THE KEY WITH THE NUMERIC IDENTIFIER REMOVED
                if (is_numeric($thiskey)) {
                    $key = substr( $key, 0, -2 );
                }
            }

            // SET THE ARRAY KEY
            //if ( isset( $post_vars['search'] ) )
                //$thiskey = in_array_r($value, $post_vars['search'], true);

            if ( false == $return ) {
                $align = is_numeric($current_member->$key) ? 'right' : 'right';
                $return_html .= '<td width="'.$tdw.'%" style="text-align: '.$align.';">';
                //$return_html .= $key . ' ' . $value;
            }

            // TAXONOMIES
            if ( is_array( $value ) ) {

                // KEYS USED AS OBJECT VARS CANNOT HAVE DASHES
                $alt_key = str_replace( '-', '', $key );

                if ( !empty( $current_member->$key ) ) {
                    foreach ( $current_member->$key as $subkey => $subvalue ) {
                        if ( 'bbconnect' == substr( $key, 0, 12 ) )
                            $key = substr( $key, 13 );

                        $term_name = get_term_by( 'id', $subvalue, $key );
                        if ( in_array_r( $subvalue, $value ) ) {
                            $ret_arr[] = '<span class="highlight">' . $term_name->name . '</span>';
                        } else {
                            $ret_arr[] = $term_name->name;
                        }
                    }
                } else if ( !empty( $current_member->$alt_key ) ) {
                    foreach ( $current_member->$alt_key as $subkey => $subvalue ) {
                        $term_name = get_term_by( 'id', $subvalue, substr( $key, 13 ) );
                        if ( in_array_r( $subvalue, $value ) ) {
                            $ret_arr[] = '<span class="highlight">' . $term_name->name . '</span>';
                        } else {
                            $ret_arr[] = $term_name->name;
                        }
                    }
                } else {
                    $ret_arr = '';
                    //bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $key, 'g_val' => $current_member->$key ) );
                }

                if ( false == $return ) {
                    if ( !is_array( $ret_arr ) ) {
                        $return_html .= '';
                        //$return_html .= bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $key, 'g_val' => $current_member->$key ) );
                    } else {
                        $return_html .= implode( ', ', $ret_arr );
                        //$return_html .= bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $key, 'g_val' => strip_tags( implode( '|', $ret_arr ) ) ) );
                    }
                } else {
                    if ( !is_array( $ret_arr ) ) {
                        $return_val[$key] = $current_member->$key;
                    } else {
                        $return_val[$key] = strip_tags( implode( ',', $ret_arr ) );
                    }
                }
                unset( $ret_arr );
            // META
            } else {
                if ( is_array( $current_member->$key ) ) {
                    $marray_out = array();
                    foreach ( $current_member->$key as $meta_key => $meta_value ) {
                        if ( is_array($meta_value) ) {
                            if ( is_assoc( $meta_value ) ) {
                                if ( !empty( $meta_value['value'] ) ) {
                                    $hlpre = '';
                                    $hlpos = '';
                                    $meta_type = '';
                                    if ( isset( $post_matches[$key] ) && !empty( $post_matches[$key] ) ) {
                                        if ( false !== strpos( $meta_value['value'], $post_matches[$key] ) ) {
                                            $hlpre = '<span class="highlight">';
                                            $hlpos = '</span>';
                                        }
                                    }
                                    if ( isset( $meta_value['type'] ) && !empty( $meta_value['type'] ) ) {
                                        $meta_type = $meta_value['type'] . ': ';
                                    }
                                    $marray_out[] = $hlpre . $meta_type . $meta_value['value'] . $hlpos;
                                }
                            }
                        } else {
                            if ( $blog_prefix.'capabilities' == $key ) {
                                if ( in_array( $meta_key, $current_member->roles ) )
                                    $marray_out[] = $meta_key;

                            } elseif ( 1 == $meta_value || 'true' == $meta_value ) {
                                $marray_out[] = 'yes';
                            } elseif ( 0 == $meta_value || 'false' == $meta_value ) {
                                $marray_out[] = 'no';
                            } else {
                                $marray_out[] = $meta_value;
                            }
                        }
                    }

                    if ( false == $return ) {
                        $return_html .= implode( '<br />', $marray_out );
                        //$return_html .= bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $key, 'g_val' => implode( '|', $marray_out ) ) );
                    } else {
                        $return_val[$key] = implode( '|', $marray_out );
                    }
                    unset( $marray_out );

                } else {

                    // IF THIS IS AN ADDRESS FIELD, LOOP THROUGH AND PRESENT ALL RESULTS
                    if ( false != strstr( $key, 'address' ) && is_numeric($thiskey)) {

                        // PRE-PROCESS THE META KEY FOR THE GENERAL CHARACTERISTIC
                        // UPON-WHICH THE INDIVIDUAL ADDRESSES CAN BE APPENDED...
                        $pre_add_base = strrchr( $key, '_' );
                        $pro_add_base = 0 - strlen( $pre_add_base );
                        $add_base = substr( $key, 0, $pro_add_base );

                        // IF THERE ARE POST-OPS INVOLVED, LIMIT THE DISPLAY TO THE INTERESECT
                        if (
                            isset( $post_vars['search'][$thiskey]['post_ops'] ) &&
                            !empty( $post_vars['search'][$thiskey]['post_ops'] )
                        ) {
                            $post_op_arr = $post_vars['search'][$thiskey]['post_ops'];
                            $po_s = array();

                            // GET THE SUFFIX
                            foreach ( $post_op_arr as $pkey => $pval ) {
                                $cur_po_preval = 'bbconnect_'.$pval;
                                $origkey = substr( $key, 0, -2 ) . substr( $pval, 2 );
                                $cur_po_val = $current_member->$cur_po_preval;
                                $pre_po_s = strrchr( $cur_po_val, '_' );
                                $po_s[] = substr( $pre_po_s, 1 );
                            }

                            $po_s_array = array_unique( $po_s );
                            if ( count( $po_s_array ) == 1 )
                                $po_add = $po_s_array[0];

                        }

                        // SET THE VARS
                        $cur_address = array();

                        for ( $i = 1; $i <= $bbconnect_address_count; $i++ ) {
                            $cur_ite = $add_base . '_' . $i;
                            if ( isset( $po_add ) ) {
                                if ( $i != $po_add ) {
                                    continue;
                                } else {
                                    $sub_ite = $add_base . '_' . $po_add;
                                    $cur_address[$cur_ite] = $current_member->$cur_ite;
                                    $cur_grex_key = $cur_ite;
                                }
                            } else {
                                if ( isset( $post_vars['search'][$thiskey]['query'] ) && !empty( $post_vars['search'][$thiskey]['query'] ) ) {

                                    if ( is_array( $post_vars['search'][$thiskey]['query'] ) ) {
                                        foreach ( $post_vars['search'][$thiskey]['query'] as $val ) {
                                            if ( false !== stripos( $current_member->$cur_ite, $val ) ) {
                                                $cur_address[$cur_ite] = $current_member->$cur_ite;
                                                $cur_grex_key = $cur_ite;
                                                break;
                                            }
                                        }
                                    } else {
                                        if ( false !== stripos( $current_member->$cur_ite, $post_vars['search'][$thiskey]['query'] ) ) {
                                            $cur_address[$cur_ite] = $current_member->$cur_ite;
                                            $cur_grex_key = $cur_ite;
                                            break;
                                        }
                                    }

                                } else {
                                    if ( $current_member->$cur_ite ) {
                                        $cur_address[$cur_ite] = $current_member->$cur_ite;
                                        $cur_grex_key[] = $cur_ite;
                                    } else {
                                        $cur_address[$cur_ite] = false;
                                        $cur_grex_key[] = $cur_ite;
                                    }
                                }
                            }
                        }


                        if ( false == $return ) {

                            // EXCEPTIONS FOR STATES
                            $cur_address_filtered = array();
                            if ( false !== strpos( $key, 'address_state' ) ) {
                                array_filter( $cur_address );
                                foreach ( $cur_address as $ck => $cv ) {
                                    $cur_address_filtered[$ck] = bbconnect_state_lookdown( $cur_ite, $cv );
                                }
                                $cur_address = $cur_address_filtered;
                            } elseif (false !== strpos($key, 'address_country')) {
                                $bbconnect_helper_country = bbconnect_helper_country();
                                array_filter( $cur_address );
                                foreach ( $cur_address as $ck => $cv ) {
                                    if (array_key_exists($cv, $bbconnect_helper_country)) {
                                        $cur_address_filtered[$ck] = $bbconnect_helper_country[$cv];
                                    } else {
                                        $cur_address_filtered[$ck] = $cv;
                                    }
                                }
                                $cur_address = $cur_address_filtered;
                            }

                            $return_html .= implode( '<br />', array_filter( $cur_address ) );
                            if ( isset( $cur_grex_key ) && is_array( $cur_grex_key ) ) {
                                $cur_grex_val = $cur_address;
                            } else {
                                $cur_grex_val = urlencode( serialize( $cur_address ) );
                                $cur_grex_key = array();
                                //if ( !isset( $cur_grex_key ) ) $return_html .= ''; //<p>'.$value.'</p>
                            }
                            //$return_html .= bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $cur_grex_key, 'g_val' => $cur_grex_val ) );
                        } else {
                            if ( false === $post_vars ) {
                                if ( false !== strpos( $origkey, 'address_state' ) ) {
                                    $return_val[$origkey] = bbconnect_state_lookdown( $origkey, $current_member->$origkey );
                                } else {
                                    $return_val[$origkey] = $current_member->$origkey;
                                }
                            } else {
                                foreach ( $cur_address as $ka => $va ) {
                                    // EXCEPTIONS FOR STATES
                                    if ( false !== strpos( $ka, 'address_state' ) ) {
                                        $return_val[$ka] = bbconnect_state_lookdown( $ka, $va );
                                    } else {
                                        $return_val[$ka] = $va;
                                    }
                                }
                                //$return_val[$origkey] = implode( '|', $cur_address );
                            }

                        }
                        unset( $cur_address );
                        unset( $cur_grex_key );

                    } else {
                        if ( false == $return ) {
                            $fieldInfo = get_option('bbconnect_'.$key);
                            $fieldInfos[$positionInarray] = $fieldInfo;

                            if ($key == 'bbconnect_bb_work_queue') {
                                $args = array(
                                        'author' => $current_member->ID,
                                );
                                $notes = cw_get_action_items($args);
                                $type_list = array();
                                foreach ($notes as $note) {
                                    $note_types = wp_get_post_terms($note->ID, 'bb_note_type');
                                    foreach ($note_types as $note_type) {
                                        if ($note_type->parent > 0) {
                                            $type_list[$note_type->name] = $note_type->name;
                                            break;
                                        }
                                    }
                                }
                                $return_html .= implode(', ', $type_list);
                            } elseif ($key == 'bbconnect_donor_category_id' || $key == 'bbconnect_segment_id') {
                                if (!empty($current_member->$key)) {
                                    $return_html .= get_the_title($current_member->$key);
                                }
                            } elseif($fieldInfo['options']['field_type'] == 'date' && is_real_date($current_member->$key)){
                                $new_date_string = date('d F Y',strtotime($current_member->$key));
                                $return_html .= $new_date_string;
                            } elseif ($fieldInfo['options']['field_type'] == 'number' && $fieldInfo['options']['is_currency'] && $current_member->$key != '') {
                                $return_html .= '$'.number_format($current_member->$key, 2);
                            } else {
                                if (is_string($current_member->$key)) {
                                    $return_html .= $current_member->$key;
                                }
                            }

                            //check if value is numeric and find total
                            if ($fieldInfo['options']['field_type'] == 'number' && is_numeric($current_member->$key) && $current_member->$key > 0){
                                $tempTotals[$positionInarray] = (!empty($totalValues[$positionInarray])) ? $totalValues[$positionInarray] + $current_member->$key : $current_member->$key;
                            } else {
                                $tempTotals[$positionInarray] = (!empty($totalValues[$positionInarray])) ? $totalValues[$positionInarray] : 0;
                            }
                            //$positionInarray++;
                             //if reached the max position in array, then go back to zero
                            if ($positionInarray == count($table_body)) {
                                foreach ($tempTotals as $keytemp => $valuetemp) {
                                    if ($valuetemp) {
                                        $totalValues = $tempTotals;
                                        break;
                                    }
                                }

                                $positionInarray = 0;
                                $tempTotals = array();
                            }

                            //insert into totals array if tempvalues not empty

                            //$return_html .= bbconnect_grex_input( array( 'u_key' => $current_member->ID, 'g_key' => $key, 'g_val' => $current_member->$key ) );
                        } else {
                            $return_val[$key] = $current_member->$key;
                        }

                    }

                }
            }

            if ( false == $return )
                $return_html .= '</td>';

        }
    }

    //global $action_array;
    if ( is_array( $action_array ) ) {

        if ( false == $return ) {
            $return_html .= '<td width="'.($tdw * 2).'%"><table width="100%">';
        }

        if ( isset( $action_array[$current_member->ID] ) ) {
            foreach ( $action_array[$current_member->ID] as $key => $value ) {

                if ( false == $return ) {

                    $return_html .= '<tr>';
                    if ( is_admin() ) {
                        $return_html .= '<td class="gredit-column" width="3%"><input type="checkbox" name="gredit_actions['.$current_member->ID.'][]" value="'.$value['ID'].'" class="gredit-action subgredit"'.$display.' /></td>';
                    }
                    $return_html .= '<td width="'.round( ($tdw * 2) - 7, 2 ).'%" class="action-detail '.$value['post_type'].'">';

                    $inner_return_hmtl = '';
                    if ( is_admin() ) {
                        $return_html .= apply_filters( 'bbconnect_action_detail_html', $inner_return_hmtl, $value );
                    } else {
                        $return_html .= apply_filters( 'bbconnect_action_detail_html_public', $inner_return_hmtl, $value );
                    }

                    $return_html .= '</td></tr>';

                } else {

                    $return_val = apply_filters( 'bbconnect_action_detail_val', $return_val, $value );

                }
            }
        }

        if ( false == $return )
            $return_html .= '</table></td>';

    }

    unset( $current_member );

    if ( false == $return )
        $return_html .= '</tr>';

    if ( false == $return ) {
        return $return_html;
    } else {
        return $return_val;
    }

}

function is_real_date( $str ){
    $stamp = strtotime( $str );
    if (!is_numeric($stamp))
        return FALSE;
    $month = date( 'm', $stamp );
    $day   = date( 'd', $stamp );
    $year  = date( 'Y', $stamp );
    if (checkdate($month, $day, $year))
        return TRUE;
    return FALSE;
}
?>