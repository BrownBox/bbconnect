<?php

/**
 * Weed out multidimensional arrays.
 *
 * @since 1.0.4 ibid.
 *
 * @param $arr multidimensional arrays accepted.
 *
 * @return arr the thinned array.
 */
function array_filter_recursive( $input ) {
    foreach ( $input as &$value ) {
        if ( is_array( $value ) ) {
            $value = array_filter_recursive( $value );
        }
    }

    return array_filter( $input );
}


function bbconnect_filter_process( $post_data ) {
    // LOCAL VARIABLES
    global $wpdb, $memberquery, $membervalue, $users_per_page, $max_num_pages, $table_head, $table_body, $blog_prefix, $bid, $page_num, $query_diff, $action_search, $all_search;
    $bid = get_current_blog_id();
    $blog_prefix = $wpdb->get_blog_prefix($bid);
    $mtc = 0;
    $page_num = 1;
    $mtjoin = array();
    $mtquery = array();
    $mtselect = array( "DISTINCT $wpdb->users.ID" ); // as id
    $skip_arr = array();

    // IF THERE'S A SORT BY REQUEST, PROCESS THAT
    if ( !empty( $post_data['order_by'] ) ) {

        $order_by = apply_filters( 'bbcpres_filter_order_by', $post_data['order_by'] );

        // STANDARDIZE WORDPRESS INCONSISTENCIES FOR RESERVED FIELDS
        // EMAIL
        switch ( $order_by ) {

            case 'ID' :
                $order_by = 'ID';
                break;

            case 'email' :
                $order_by = 'user_email';
                break;

            case 'url' :
                $order_by = 'user_url';
                break;

            case 'user_login' :
                $order_by = 'user_login';
                break;

            case 'display_name' :
                $order_by = 'display_name';
                break;

            case 'user_registered' :
                $order_by = 'user_registered';
                break;

            case 'role' :
                $order_by = $blog_prefix.'capabilities';
                $ometa = true;
                break;

            case 'first_name' :
                $order_by = $order_by;
                $ometa = true;
                break;

            case 'last_name' :
                $order_by = $order_by;
                $ometa = true;
                break;

            default :
                $order_by = $order_by;
                $ometa = true;
                break;

        }

    } else {

        $order_by = 'ID';

    }

    // SET THE SORT DIRECTION, IF IT HASN'T BEEN SET ALREADY
    if ( !isset( $post_data['order'] ) )
        $post_data['order'] = 'DESC';

    if ( 'DESC' === $post_data['order'] ) {
       //uksort( $all_sort, 'insensitive_uksort_rev' );
       $order = 'DESC';
    } else {
        //uksort( $all_sort, 'insensitive_uksort' );
        $order = 'ASC';
    }

    /*
    // SET UP THE SORTING ARRAY
    $all_sort = array();
    foreach ( $all_search as $user ) {

        // IF THERE'S A SORT BY REQUEST, PROCESS THAT
        if ( isset( $order_by ) ) {

            // GET THE VALUE TO ORDER BY
            $current_user = get_userdata( $user );

            // INCLUDE IT IF IT EXISTS AND APPEND THE USER ID TO ENSURE THE KEYS ARE UNIQUE
            if ( isset( $current_user->$order_by ) ) {
                $all_key = str_replace( '-', '_', $current_user->$order_by ) . '_' . $user;
            } else {
                $all_key = $user;
            }

        } else {

            $all_key = $user;

        }

        $all_val = $user;
        $all_sort[$all_key] = $all_val;
    }
    */

    // WE CAN TRY BYPASSING THIS FOR IMPORTS BY SETTING IT THROUGH POSTDATA
    if ( !isset( $post_data['all_search'] ) ) {

        // RETURN VALUES
        $table_head = array();
        $table_body = array();
        $all_search = array();
        $member_search = array();
        $users_count = '';

        // THE POST-OPERATOR
        $post_operator = array();

        // THE COUNTER TO KEEP TRACK OF HOW MANY QUERIES WE'VE MADE
        global $u_count;
        $u_count = '0';

        // THE QUERY ARRAY CONTAINING THE RESULTING ARRAY OF USER IDS
        if ( isset( $post_data['search'] ) )
            $q_array = array();

        // LET'S GET STARTED!

        // MERGE TAGS: LOOP THROUGH THE POST VALUES AND BUILD THE QUERY
        if ( isset( $post_data['search'] ) ) {

            foreach ( $post_data['search'] as $key => $value ) {
                if ( 'user' != $value['type'] )
                    continue;

                // GO AHEAD AND TALLY
                $u_count++;

                // SET THE FIELD KEY
                $fkey = $value['field'];

                // DETERMINE THE OPERATOR
                switch ( $value['operator'] ) {

                    case '' :
                            $op = 'skip';
                            $sop = '||';
                        break;

                    case 'is' :
                            $op = '=';
                            $sop = '||';
                        break;

                    case 'not' :
                            $op = '!=';
                            $sop = '&&';
                        break;

                    case 'like' :
                            $op = 'LIKE';
                            $sop = '||';
                        break;

                    case 'notlike' :
                            $op = 'NOT LIKE';
                            $sop = '&&';
                        break;

                    case 'null' :
                            $op = 'IS NULL';
                            $sop = '||';
                        break;

                    case 'notnull' :
                            $op = 'IS NOT NULL';
                            $sop = '||';
                        break;

                    case 'lt' :
                            $op = '<';
                            $sop = '||';
                        break;

                    case 'gt' :
                            $op = '>';
                            $sop = '||';
                        break;

                }

                // DETERMINE THE SUB OPERATOR
                if ( isset( $value['sub_operator'] ) ) {
                    switch ( $value['sub_operator'] ) {

                        case 'all' :
                                $sop = '&&';
                            break;

                        case 'any' :
                                $sop = '||';
                            break;

                    }
                }

                // PREP THE RESULTS TABLE && DISTINGUISH BETWEEN TAXONOMIES & META
                if ( 'bbconnect' != substr( $fkey, 0, 9 ) ) {
                    $option_key = 'bbconnect_' . $fkey;
                } else {
                    $option_key = $fkey;
                }

                // EVALUATE THE FIELD
                $user_meta = get_option( $option_key );

                // STANDARDIZE WORDPRESS INCONSISTENCIES FOR RESERVED FIELDS
                $wp_col = '';
                if ( isset( $user_meta['source'] ) && 'wpr' == $user_meta['source'] ) {

                    // EMAIL
                    if ( 'email' == $user_meta['meta_key'] )
                        $wp_col = 'user_email';

                    // USER ID
                    if ( 'ID' == $user_meta['meta_key'])
                        $wp_col = 'ID';

                    // USER CREATED DATE
                    if ( 'user_registered' == $user_meta['meta_key'] )
                        $wp_col = 'user_registered';

                    // URL
                    if ( 'url' == $user_meta['meta_key'] )
                        $wp_col = 'user_url';

                    // LOGIN
                    if ( 'user_login' == $user_meta['meta_key'] )
                        $wp_col = 'user_login';

                    // DISPLAY NAME
                    if ( 'display_name' == $user_meta['meta_key'] )
                        $wp_col = 'display_name';

                    // ROLE
                    if ( 'role' == $user_meta['meta_key'] ) {
                        $wp_meta_col = $blog_prefix.'capabilities';
                        //if ( 'skip' != $op )
                            //$op = 'LIKE';
                    }

                }

                // SPECIAL CASE FOR SERIALIZED DATA
                if ( 'taxonomy' == $user_meta['options']['field_type'] || 'role' == $user_meta['meta_key'] ) {
                    if ( '=' == $op && isset( $value['query'] ) ) {
                        $op = 'LIKE';
                    } else if ( '!=' == $op && isset( $value['query'] ) ) {
                        $op = 'NOT LIKE';
                    }
                }

                // STANDARDIZE WORDPRESS META FIELDS
                $user_meta_col = '';
                if (isset($user_meta['source'])) {
                    if ('wp' == $user_meta['source'] && 'taxonomy' != $user_meta['options']['field_type']) {
                        $wp_meta_col = $fkey;
                    } elseif ('bbconnect' == $user_meta['source']) {
                        $user_meta_col = 'bbconnect_';
                    } else {
                        $user_meta_col = '';
                    }
                }

                $wp_col = apply_filters('bbconnect_filter_process_wp_col', $wp_col, $user_meta, $value);
                $op = apply_filters('bbconnect_filter_process_op', $op, $user_meta, $value);

                // IF WE'RE JUST DISPLAYING RESULTS, SIT THIS PART OUT
                if ( 'skip' != $op ) {

                    // SET THE QUERY PREFIX
                    if ( 0 === $mtc ) {
                        $mtc_as = '';
                        $mtc_dot = "$wpdb->usermeta.";
                    } else {
                        $mtc_as = 'AS mt' . $mtc . ' ';
                        $mtc_dot = 'mt' . $mtc . '.';
                        // FUTURE $mtselect[] = 'mt' . $mtc.'.meta_value';
                    }

                    // THIS IS A SUBQUERY
                    if ( isset( $value['query'] ) ) {
                        $value_query = $value['query'];
                    } else {
                        $value_query = '';
                    }

                    if ( is_array( $value_query ) ) {

                        // SETUP A TEMP ARRAY TO JOIN
                        $temp_arr = array();

                        foreach ( $value_query as $subkey => $subvalue ) {

                            // MIMIC THE SERIALIZED STRUCTURE FOR ROLES AND TAXONOMIES
                            if ( 'taxonomy' == $user_meta['options']['field_type'] || 'role' == $user_meta['meta_key'] ) {
                                $subvalue = substr( $wpdb->prepare( "%s", $subvalue ), 1, -1 );
                                $q_val = '\'%s:'.strlen($subvalue).':"'.$subvalue.'"%\'';
                            // CONDITIONS FOR THE VALUE
                            } else if ( 'LIKE' === $op || 'NOT LIKE' === $op ) {
                                $q_val = $wpdb->prepare( "%s", '%'.$subvalue.'%' );
                            } else if ( 'IS NULL' === $op || 'IS NOT NULL' === $op ) {
                                $q_val = "";
                            } else if ('<' === $op || '>' === $op) {
                                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$value_query)) {
                                    $is_date= true;
                                } else {
                                    $is_date = false;
                                }
                                if ($is_date) {
                                    $q_val = "DATE('" . $value_query . "')";
                                }
                                else
                                $q_val = strpos($subvalue, '.') !== false ? floatval($subvalue) : intval($subvalue);
                            } else if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$subvalue) && $wp_col != 'user_registered') {
                                if ( date( 'Y-m-d', strtotime( $subvalue ) ) == $subvalue ) {
                                    $temp_date_arr[] = "DATE('" . $subvalue . "')";
                                }
                            } else {
                                $q_val = $wpdb->prepare( "%s", $subvalue );
                            }

                            $q_val = apply_filters('bbconnect_filter_process_q_val', $q_val, $user_meta, $subvalue);

                            // THIS IS A WORDPRESS USER FIELD
                            if ( !empty( $wp_col ) ) {
                                if ( $wp_col == 'user_registered' ) {
                                    if ( date( 'Y-m-d', strtotime( $subvalue ) ) == $subvalue ) {
                                        $temp_arr[] = "DATE('" . $subvalue . "')";
                                    }
                                } else {
                                    $temp_arr[] = "(" . $wp_col . " " . $op . " " . $q_val . ")";
                                }
                            } else if ( !empty( $wp_meta_col ) ) {
                                $temp_arr[] = $mtc_dot . "meta_value " . $op . " " . $q_val;
                            } else {
                                $temp_arr[] = $mtc_dot . "meta_value " . $op . " " . $q_val;
                            }
                        }

                        // JOIN THE JOIN
                        if ( !empty( $wp_col ) ) {
                            if ( $wp_col == 'user_registered' ) {
                                if ( '!=' == $op || 'NOT LIKE' == $op ) {
                                    $reg_op = " NOT BETWEEN ";
                                } else {
                                    $reg_op = " BETWEEN ";
                                }
                                $mtquery[$mtc] = "(" . $wp_col . $reg_op . implode( ' AND ', $temp_arr ) . ")";

                            } else {
                                $mtquery[$mtc] = "(" . implode( ' '.$sop.' ', $temp_arr ) . ")";
                            }
                        }

                        else {
                            //if ( empty( $wp_col ) )
                            $mtjoin[$mtc] = "INNER JOIN $wpdb->usermeta " . $mtc_as . "ON ($wpdb->users.ID = " . $mtc_dot . "user_id)";

                            // JOIN THE QUERY TEMP ARRAY
                            if ( !empty( $wp_meta_col ) ) {
                                $mtquery[$mtc] = "(" . $mtc_dot . "meta_key = '" . $wp_meta_col . "' AND " . implode( ' '.$sop.' ', $temp_arr ) . ")";
                            } else {
                                if ( !empty( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' ) ) {
                                    $mod_key = substr( $fkey, 0, -1 );
                                    // PREPARE $mod_comp = "meta_key LIKE '%".$user_meta_col. $mod_key . "%'";
                                    $mod_comp = $wpdb->prepare( "meta_key LIKE %s", "%".$user_meta_col.$mod_key."%" );
                                } else {
                                    $mod_key = $fkey;
                                    // PREPARE $mod_comp = "meta_key = '".$user_meta_col. $mod_key . "'";
                                    $mod_comp = $wpdb->prepare( "meta_key = %s", $user_meta_col.$mod_key );
                                }

                                if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$subvalue)){
                                    if ( '!=' == $op || 'NOT LIKE' == $op ) {
                                        $reg_op = " NOT BETWEEN ";
                                    } else {
                                        $reg_op = " BETWEEN ";
                                    }

                                    $mtquery[$mtc] = "(" . $mtc_dot . $mod_comp . " AND ".$mtc_dot."meta_value ".$reg_op . implode( ' AND ', $temp_date_arr ) . ")";
                                }
                                else{
                                    $mtquery[$mtc] = "(" . $mtc_dot . $mod_comp . " AND " . implode( ' '.$sop.' ', $temp_arr ) . ")";
                                }


                            }
                        }


                    } else {
                        /* MIMIC THE SERIALIZED STRUCTURE FOR ROLES AND TAXONOMIES
                        if ( 'taxonomy' == $user_meta['options']['field_type'] || 'role' == $user_meta['meta_key'] ) {
                            if ( '' != $value_query )
                                $value_query = 's:'.strlen($value_query).':"'.$value_query.'"';
                        }
                        */

                        // CONDITIONS FOR THE VALUE
                        // AND THE OPERATOR IF APPLICABLE
                        if ( 'LIKE' === $op || 'NOT LIKE' === $op ) {
                            // PREPARE $q_val = "'%" . $value_query . "%'";
                            $q_val = $wpdb->prepare( "%s", '%'.$value_query.'%' );
                        } else if ( 'IS NULL' === $op || 'IS NOT NULL' === $op || '' == $value_query || empty( $value_query ) || !
                            isset( $value_query ) ) {
                            if ( !empty( $wp_col ) ) {
                                if ( 'IS NULL' === $op || '=' === $op ) {
                                    $op = "=";
                                } else {
                                    $op = "!=";
                                }
                                $q_val = "''";
                            } else {
                                if ( '' == $value_query || empty( $value_query ) || !isset( $value_query ) ) {
                                    if ( '=' === $op && empty( $wp_meta_col ) ) {
                                        $op = "IS NULL";
                                        $q_val = "";
                                    } else if ( '!=' == $op && empty( $wp_meta_col ) ) {
                                        $op = "IS NOT NULL";
                                        $q_val = "";
                                    } else {
                                        // PREPARE $q_val = "'" . $value_query . "'";
                                        $q_val = $wpdb->prepare( "'%s'", $value_query );
                                    }
                                }

                            }
                        } else if ('<' === $op || '>' === $op) {
                            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$value_query)){
                                    $is_date= true;
                                }else{
                                    $is_date = false;
                                }
                                if($is_date){
                                    $q_val = "DATE('" . $value_query . "')";
                                }
                                else
                                $q_val = strpos($value_query, '.') !== false ? floatval($value_query) : intval($value_query);

                        } else {
                            // PREPARE $q_val = "'" . $value_query . "'";
                            $q_val = $wpdb->prepare( "'%s'", $value_query );
                        }

                        // THIS IS A WORDPRESS USER FIELD
                        if ( !empty( $wp_col ) ) {
                            $mtquery[$mtc] = "(" . $wp_col . " " . $op . " " . $q_val . ")";
                        } else {
                            $mtjoin[$mtc] = "INNER JOIN $wpdb->usermeta " . $mtc_as . "ON ($wpdb->users.ID = " . $mtc_dot . "user_id)";

                            // TEST FOR NULL COMPARISONS
                            if ( 'IS NULL' === $op ) {
                                if ( !empty( $wp_meta_col ) ) {
                                    $mtquery[$mtc] = "(" . $mtc_dot . "meta_key = '" . $wp_meta_col . "' AND " . $mtc_dot . "meta_value = '')";
                                } else {
                                    if ( !empty( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' ) ) {
                                        $mod_key = substr( $fkey, 0, -1 );
                                        // PREPARE $mod_comp = "meta_key = '%".$user_meta_col. $mod_key . "%'";
                                        $mod_comp = $wpdb->prepare( "meta_key LIKE %s", "%".$user_meta_col.$mod_key."%" );
                                    } else {
                                        $mod_key = $fkey;
                                        // PREPARE $mod_comp = "meta_key = '".$user_meta_col. $mod_key . "'";
                                        $mod_comp = $wpdb->prepare( "meta_key = %s", $user_meta_col.$mod_key );
                                    }
                                    $mtquery[$mtc] = "( ".$mtc_dot."user_id NOT IN(SELECT DISTINCT ".$wpdb->usermeta.".user_id FROM ".$wpdb->usermeta." WHERE ".$wpdb->usermeta."." . $mod_comp . ") )";
                                }
                            } else {
                                if ( !empty( $wp_meta_col ) ) {
                                    $mtquery[$mtc] = "(" . $mtc_dot . "meta_key = '" . $wp_meta_col . "' AND " . $mtc_dot . "meta_value " . $op . " " . $q_val . ")";
                                } else {
                                    if ( !empty( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' ) ) {
                                        $mod_key = substr( $fkey, 0, -1 );
                                        // PREPARE $mod_comp = "meta_key LIKE '%".$user_meta_col. $mod_key . "%'";
                                        $mod_comp = $wpdb->prepare( "meta_key LIKE %s", "%".$user_meta_col.$mod_key."%" );
                                    } else {
                                        $mod_key = $fkey;
                                        // PREPARE $mod_comp = "meta_key = '".$user_meta_col. $mod_key . "'";
                                        $mod_comp = $wpdb->prepare( "meta_key = %s", $user_meta_col.$mod_key );
                                    }
                                    $mtquery[$mtc] = "(" . $mtc_dot . $mod_comp . " AND " . $mtc_dot . "meta_value " . $op . " " . $q_val . ")";

                                }
                            }
                        }

                    }

                    // DETERMINE ANY QUERY POST-OPS
                    if ( isset( $value['post_ops'] ) ) {

                        // SET THE SELECT FIELD FOR THE PRECEDING VALUE
                        $mtselect[] = $mtc_dot . "meta_key AS mv" . $mtc;

                        foreach ( $value['post_ops'] as $post_op ) {

                            // PUSH THE COMPARITORS
                            $kpost_op = array( 'meta' => $fkey, 'type' => $post_op, 'incl' => $value['operator'] );
                            array_push( $post_operator, $kpost_op );

                            // INCREMENT THE QUERY COUNTER
                            $mtc++;

                            // SET THE QUERY PREFIX
                            if ( 0 === $mtc ) {
                                $mtc_as = '';
                                $mtc_dot = "$wpdb->usermeta.";
                            } else {
                                $mtc_as = 'AS mt' . $mtc . ' ';
                                $mtc_dot = 'mt' . $mtc . '.';
                            }
                            $mtjoin[$mtc] = "INNER JOIN $wpdb->usermeta " . $mtc_as . "ON ($wpdb->users.ID = " . $mtc_dot . "user_id)";
                            // PREPARE $mtquery[$mtc] = "(" . $mtc_dot . "meta_key = 'bbconnect_" . $post_op . "')";
                            $mtquery[$mtc] = $wpdb->prepare( "(" . $mtc_dot . "meta_key = %s)", "bbconnect_$post_op" );

                            $mtselect[] = $mtc_dot . "meta_value AS mv" . $mtc;

                        }
                    }

                } else {
                    $skip_arr[] = 'bbconnect_'.$value['field'];
                }

                // NO MATTER WHAT, SAVE THE DATA FOR THE COLUMNS
                // IT'S A TAXONOMY!
                if ( 'taxonomy' == $user_meta['options']['field_type'] ) {

                    // PULL THE TAXONOMY DATA AND ASSIGN THE DISPLAY VALUES
                    $tab_head = get_taxonomy( $fkey );

                    // ASSIGN THE DISPLAY VALUES
                    $table_head[] = array(  false, $tab_head->labels->name );

                    if ( 'bbconnect' != $user_meta['source'] ) {
                        $switch_key = $fkey;
                    } elseif ( 'bbconnect' == $user_meta['source'] ) {
                        $switch_key = $option_key;
                    }

                    // TEST FOR DISPLAY PURPOSES
                    if ( empty( $value['query'] ) ) {
                        $table_body[$switch_key][] = '';

                    // GET ALL THE TERMS!
                    } else {

                        foreach ( $value['query'] as $term ) {
                            // GET THE TERM ID
                            $table_body[$switch_key][] = $term;

                        }

                    }

                // IT'S A META!
                } else {

                    // THE HEADER
                    $table_head[] = array( $user_meta['meta_key'], $user_meta['name'] );

                    // THE BODY
                    if ( 'wpr' == $user_meta['source'] ) {
                        if ( !empty( $wp_col ) ) {
                            $table_body[$wp_col] = $fkey;
                        } else if ( !empty( $wp_meta_col ) ) {
                            $table_body[$wp_meta_col] = $fkey;
                        }
                    } elseif ( 'bbconnect' != $user_meta['source'] ) {
                        $table_body[$fkey] = $fkey;
                    } elseif ( 'bbconnect' == $user_meta['source'] ) {
                        $table_body[$option_key] = $fkey;
                    }

                }

                // CLEANUP
                $mtc++;

                if ( isset( $temp_arr ) )
                    unset( $temp_arr );

                if ( isset( $wp_col ) )
                    unset( $wp_col );

                if ( isset( $wp_meta_col ) )
                    unset( $wp_meta_col );

                if ( isset( $user_meta_col ) )
                    unset( $user_meta_col );

            }

            // SET UP THE ORDER CLAUSE
            if ( isset( $ometa ) ) {

                // SET THE QUERY PREFIX
                if ( 0 === $mtc ) {
                    $mtc_as = '';
                    $mtc_dot = "$wpdb->usermeta.";
                } else {
                    $mtc_as = 'AS mt' . $mtc . ' ';
                    $mtc_dot = 'mt' . $mtc . '.';
                }

                $mtjoin[$mtc] = "INNER JOIN $wpdb->usermeta " . $mtc_as . "ON ($wpdb->users.ID = " . $mtc_dot . "user_id)";
                if ( in_array( $order_by, $skip_arr ) ) {

                    // GET THE META TO TEST
                    $t_order_by = bbconnect_get_option( $order_by );
                    if ( false != $t_order_by ) {
                        if ( 'bbconnect' == $t_order_by['source'] )
                            $order_by = 'bbconnect_'.$order_by;
                    }

                    $nullquo = $wpdb->prepare( "IFNULL(" . $mtc_dot . "meta_key = %s,0) = %s", $order_by, $order_by );
                    $nullval = " ORDER BY IFNULL(". $mtc_dot ."meta_value," . $mtc_dot . "meta_key) $order";
                } else {

                    // GET THE META TO TEST
                    $t_order_by = bbconnect_get_option( $order_by );
                    if ( false != $t_order_by ) {
                        if ( 'bbconnect' == $t_order_by['source'] )
                            $order_by = 'bbconnect_'.$order_by;
                    }

                    if ( isset( $t_order_by['group'] ) && false !== strpos( $t_order_by['group'], 'address' ) ) {
                        $addext = count( strrchr( $t_order_by['meta_key'], '_' ) );
                        $addapp = substr( $t_order_by['meta_key'], 0, -$addext );
                        $nullquo = $wpdb->prepare( $mtc_dot . "meta_key LIKE %s", '%bbconnect_'.$addapp.'%' );
                    } else {
                        $nullquo = $wpdb->prepare( $mtc_dot . "meta_key = %s", $order_by );
                    }

                    $nullval = " ORDER BY ". $mtc_dot ."meta_value $order";
                }
                $mtquery[$mtc] = $nullquo;
                $mtorder = $nullval;

                // CASE FOR ACTIONS
                //$actusers  = "INNER JOIN $wpdb->usermeta ON ($wpdb->posts.post_author = $wpdb->usermeta.user_id)";
                //$actquery = "$wpdb->usermeta.meta_key = '$order_by' AND";
                //$mtc++;

            } else {

                $mtorder = " ORDER BY $wpdb->users.$order_by $order";

                // CASE FOR ACTIONS
                //$actusers  = "INNER JOIN $wpdb->users ON ($wpdb->posts.post_author = $wpdb->users.ID)";
                //$actquery = ""; // "$wpdb->usermeta.meta_key = '$order_by' AND";

            }

            // JOIN THE QUERY
            $q_join = implode( ' ', $mtjoin );
            $q_query = implode( ' ' . $post_data['mod_results'] . ' ', $mtquery );
            $q_select = implode( ', ', $mtselect );
            $wpdb->flush();
            if ( empty( $q_query ) ) {
                $all_query = "SELECT $wpdb->users.ID FROM $wpdb->users $mtorder";
                $all_search = $wpdb->get_col( $all_query );
            } else {
                $all_query = "SELECT $q_select FROM $wpdb->users $q_join WHERE 1=1 AND $q_query $mtorder";
                if ( count( $mtselect ) > 1 ) {
                    $all_search = $wpdb->get_results( $all_query, ARRAY_N );
                } else {
                    $all_search = $wpdb->get_col( $all_query );
                }
            }
            $wpdb->flush();

        } else {

            // ASSUME THEY WANT TO SEARCH EVERYONE...
            if ( isset( $ometa ) ) {
                $all_order = "INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) WHERE $wpdb->usermeta.meta_key = '$order_by' ORDER BY $wpdb->usermeta.meta_value $order";
            } else {
                $all_order = "ORDER BY $wpdb->users.$order_by $order";
            }
            $all_query = "SELECT $wpdb->users.ID FROM $wpdb->users $all_order";
            $all_search = $wpdb->get_col( $all_query );
            $wpdb->flush();

        }

        // IF THE POST OPERATOR IS SET, FILTER THE RESULTS
        if ( !empty( $post_operator ) ) {

            // SET THE REFRESHED OPERATOR
            $po_all_search = array();

            // LOOP THROUGH THE OPERATORS AND TEST WHETHER THEY SHOULD BE INCLUDED
            foreach ( $all_search as $key => $value ) {

                // COUNT THE ELEMENTS IN THE RESULTS ARRAY
                if ( !isset( $po_arr_count ) )
                    $po_arr_count = count( $value ) - 1;

                // LOCATE THE TARGET INTEGER FOR ADDRESSES
                //if ( !isset( $po_index ) ) {
                    $pre_po_index = strrchr( $value[1], '_' );
                    $po_index = substr( $pre_po_index, 1 );
                    $po_count = 0 - strlen( $po_index );
                //}

                // LOOP THROUGH THE VALUES AND IF THEY DON'T AGREE, UNSET THE VALUE
                for ($i = 2; $i <= $po_arr_count; $i++) {
                    if ( substr( $value[$i], $po_count ) != $po_index )
                        $cur_{$key} = true;
                }

                // IF THERE'S A MATCH, RE-BUILD THE ALL SEARCH ARRAY
                if ( !isset( $cur_{$key} ) )
                    $po_all_search[] = $value[0];

            }

            $all_search = $po_all_search;
            unset( $po_all_search );

        }

        do_action( 'bbconnect_search_extend', $post_data, $all_search );

    } else {
        $all_search = $post_data['all_search'];
    }

    $all_search = apply_filters('bbconnect_search_results', $all_search);

    // CONTINUE ON WITH THE PAGINATION AND SORTING ACTIVITY...
    if ( !empty( $all_search ) ) {
        $users_count = count( $all_search );

        // GET THE REQUESTED PAGE NUMBER
        if ( isset( $_GET['page_num'] ) ) {
           $page_num = $_GET['page_num'];
        } else if ( isset( $post_data['page_num'] ) ) {
           $page_num = $post_data['page_num'];
        }

        // GET THE REQUESTED RESULT SET
        if ( isset( $post_data['users_per_page'] ) ) {
            //check if user per page is all
            if(strtolower($post_data['users_per_page'])=='all') $users_per_page = $users_count;
            else
            $users_per_page = $post_data['users_per_page'];

        } else {
            $users_per_page = 25; // THIS NEEDS TO BE AN OPTION!!!
        }

        // GET THE NUMBER OF PAGES
        $max_num_pages = ceil( $users_count/$users_per_page );
        $pages = (int)$page_num;

        if ( $pages > $max_num_pages )
            $pages = $max_num_pages;

        if ( $pages < 1 )
           $pages = 1;

        // GET THE PAGE RANGE
        $page_low = ( $pages - 1 ) * $users_per_page;
        $page_high = $users_per_page;

        // FINALLY, IF WE'RE PAGINATING, SLICE THE ARRAY
        $member_search = array_slice( $all_search, $page_low, $page_high );

    }

    if ( !isset( $action_search ) )
        $action_search = false;

    $ret_arr = array( 'all_search' => $all_search, 'member_search' => $member_search, 'action_search' => $action_search, 'post_vars' => $post_data, 'max_num_pages' => $max_num_pages, 'users_count' => $users_count, 'users_per_page' => $users_per_page, 'page_num' => $page_num, 'errors' => false, 'table_head' => $table_head, 'table_body' => $table_body );
    unset($all_search);
    unset($member_search);
    unset($action_search);
    return $ret_arr;
}

function bbconnect_filter_users_current_blog($all_search) {
    if (is_multisite()) {
        $blog_id = get_current_blog_id();
        $args = array(
                'blog_id' => $blog_id,
                'fields' => 'ID',
        );
        $blog_users = get_users($args);
        $all_search = array_intersect($all_search, $blog_users);
    }
    return $all_search;
}

function insensitive_uksort($a,$b) {
    return strtolower($a)<strtolower($b);
}
function insensitive_uksort_rev($a,$b) {
    return strtolower($a)>strtolower($b);
}

function bbconnect_query_builder( $key, $value ) {

    if ( is_array( $value ) ) {
        foreach ( $value as $subkey => $subvalue ) {
            if ( 'wp' == $bbconnect_{$key}['source'] ) {
                $where_clause[] = "(meta_key = '$key' AND meta_value LIKE '%{$subvalue}%')";
            } elseif ( 'bbconnect' == $bbconnect_{$key}['source'] ) {
                // IF IT'S AN ADDRESS, GROUP ALL ADDRESSES
                if ( 'address' == substr( $bbconnect_{$key}['group'], 0, 7 ) ) {
                    $where_clause[] = "(meta_key = '$option_key' AND meta_value LIKE '%{$subvalue}%') OR (meta_key = '$option_two_key' AND meta_value LIKE '%{$subvalue}%') OR (meta_key = '$option_three_key' AND meta_value LIKE '%{$subvalue}%')";
                } else {
                    $where_clause[] = "(meta_key = '$option_key' AND meta_value LIKE '%{$subvalue}%')";
                }
            }
        }
        if ( 'wp' == $bbconnect_{$key}['source'] ) {
            // SAVE THE DATA FOR THE COLUMNS
            //$table_body[$key] = 'meta';
        } elseif ( 'bbconnect' == $bbconnect_{$key}['source'] ) {
            // SAVE THE DATA FOR THE COLUMNS
            //$table_body[$option_key] = 'meta';
        }
    } else {
        if ( 'wp' == $bbconnect_{$key}['source'] ) {
            $where_clause[] = "(meta_key = '$key' AND meta_value LIKE '%{$value}%')";
            // SAVE THE DATA FOR THE COLUMNS
            //$table_body[$key] = 'meta';
        } elseif ( 'bbconnect' == $bbconnect_{$key}['source'] ) {
            if ( isset( $bbconnect_{$key}['group'] ) && 'address' == substr( $bbconnect_{$key}['group'], 0, 7 ) ) {
                $where_clause[] = "(meta_key = '$option_key' AND meta_value LIKE '%{$value}%') OR (meta_key = '$option_two_key' AND meta_value LIKE '%{$value}%') OR (meta_key = '$option_three_key' AND meta_value LIKE '%{$value}%')";
            } else {
                $where_clause[] = "(meta_key = '$option_key' AND meta_value LIKE '%{$value}%')";
            }
            // SAVE THE DATA FOR THE COLUMNS
            //$table_body[$option_key] = 'meta';
        }
    }

}