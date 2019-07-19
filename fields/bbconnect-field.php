<?php

function bbconnect_get_field( $args = '' ) {

    // SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
    $defaults = array(
                    'meta' => false, // EXPECTS AN ARRAY OF INFORMATION ABOUT THE FIELD
                    'id' => false, // replaces user_id, post_id
                    'type' => 'user', // SHOULD BE TYPE: USER, POST, OPTION
                    'action' => '', // THIS SHOULD BE ACTION
                    'post_val' => false,
                    'bbconnect_cap' => '', // NEEDED TO PASS THROUGH FOR GROUPS AND SECTIONS
                    'required' => false,
                    'swap_name' => array(), // THIS ALLOWS A TEMPORARY KEY NAME TO BE ATTACHED FOR SECONDARY PROCESSING
                    'swap_id' => false, // THIS ALLOWS A TEMPORARY ID TO BE ATTACHED FOR SECONDARY PROCESSING
                    'help' => false,
                    'return' => false,
                    'readonly' => false, // CAN OVERRIDE ACTION TO SPECIFY VIEW ALONG WITH LABEL
                    'flag' => false, // WILDCARD VARIABLE
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    // EXIT GRACEFULLY IF NEED BE
    if ( false == $meta )
        return false;

    // SET THE BASE AND WRAPPER VALUES
    $key = $meta['meta_key'];
    $wrap = true;
    $label = $meta['name'];
    $label_wrap = true;
    $lock = false;
    $pmv = '';
    $pmt = '';
    $public = $meta['options']['public'];
    $admin_only = false;
    $mayberequired = false;
    $asterix = false;

    // LET HELP HAVE AN OVERRIDE
    if ( false == $help && !empty( $meta['help'] ) )
        $help = $meta['help'];

    // LET READONLY HAVE AN OVERRIDE
    if ( isset( $meta['options']['readonly'] ) && false != $meta['options']['readonly'] )
        $readonly = true;

    // SPECIAL CASE FOR CHECKBOX
    if ( 'checkbox' == $meta['options']['field_type'] ) $label = false;

    // SET THE FIELD ARRAY
    $field = array();

    // SPECIAL CASES FOR TITLES, SECTIONS AND GROUPS
    $reserved_types = array( 'title','section', 'group' );

    // SPECIAL CASES FOR TITLES, SECTIONS AND GROUPS
    $reserved_source = array( 'email','user_login', 'display_name' );

    // IF WE HAVE POST VALS, GO AHEAD AND SANITIZE IT, PROBABLY AGAIN
    if ( false != $post_val )
        $post_val = bbconnect_scrub( 'bbconnect_sanitize', $post_val );

    // MODIFY VALUES ON CONTEXT/TYPE
    $field['type'] = $type;
    if ( 'option' === $type ) {
        $field_pre_name = '_bbc_option';
        if ( 'textarea' == $meta['options']['field_type'] ) {
            $field['value'] = bbconnect_scrub( 'bbconnect_esc_html', get_option( $key ) );
        } else {
            $field['value'] = bbconnect_scrub( 'bbconnect_esc_attr', get_option( $key ) );
        }
        if ( false != $post_val )
            $field['value'] = $post_val;
        $public = false;

    } else if ( 'post' === $type ) {
        $field_pre_name = '_bbc_post';
        if ( 'textarea' == $meta['options']['field_type'] ) {
            $field['value'] = bbconnect_scrub( 'bbconnect_esc_html', get_post_meta( $id, $key, true ) );
        } else {
            $field['value'] = bbconnect_scrub( 'bbconnect_esc_attr', get_post_meta( $id, $key, true ) );
        }
        if ( false != $post_val )
            $field['value'] = bbconnect_scrub( 'bbconnect_esc_attr', $post_val );
        $public = false;

    } else if ( 'search' === $type ) {
        $field_pre_name = 'search';
        $field['value'] = $post_val;
        $public = false;

    } else if ( 'user' === $type ) {

        $field_pre_name = 'bbconnect_user_meta';

        if ( false != $post_val ) {
            $field['value'] = $post_val;
        } else {

            if ( 'wpr' == $meta['source'] ) {
                $field['value'] = get_userdata( $id );
            } else if ( 'wp' == $meta['source'] || 'user' == $meta['source'] ) {
                $field['value'] = bbconnect_scrub( 'bbconnect_esc_attr', get_user_meta( $id, $key, true ) );
            } else {
                if ('bbconnect' == $meta['source']) {
                    $meta_prefix = 'bbconnect_';
                } else {
                    $meta_prefix = '';
                }
                if ( 'textarea' == $meta['options']['field_type'] ) {
                    $field['value'] = bbconnect_scrub( 'bbconnect_esc_html', get_user_meta( $id, $meta_prefix.$key, true ) );
                } else {
                    $field['value'] = bbconnect_scrub( 'bbconnect_esc_attr', get_user_meta( $id, $meta_prefix.$key, true ) );
                }
            }

        }

        // ADDITIONAL PROCESSING FOR PUBLIC USER FIELDS
        // BY DEFAULT, PUBLIC DATA IS SET TO FALSE AND HAS TO BE OVERRIDDEN BY AN ADMIN
        $user_opt = bbconnect_scrub( 'esc_attr', get_user_meta( $id, 'bbconnect_bbc_public', true ) );
        if ( false != $public ) {

            // SOMEONE HAS UPDATED THIS PROFILE AND SET A PREFERENCE
            if ( is_array( $user_opt ) && isset( $user_opt[$key] ) ) {

                if ( 'true' != $user_opt[$key] ) {
                    $pmt = 'off';
                    $pmv = 'false';
                } else {
                    $pmt = 'on';
                    $pmv = 'true';
                }

            // IF A USER HAS NOT DECIDED YET ON THEIR PUBLIC STATUS...
            } else if ( !is_array( $user_opt ) || !isset( $user_opt[$key] ) )  {

                // IF AN ADMIN HAS OPTED TO FORCE DISPLAY ON UNDECIDED USERS
                $admin_opt = get_option( '_bbconnect_public' );
                if ( 'true' != $admin_opt ) {
                    $pmt = 'off';
                    $pmv = 'false';
                } else {
                    $pmt = 'on';
                    $pmv = 'true';
                }

            }
        }

    } else {
        return false;

    }

    $field['cid'] = $id;
    $field['display'] = '';
    $field_swap_name = array();
    $field_name = '[' . $key . ']';
    $field_pos_name = array();

    // MODIFY VALUES ON APPEND/CONTEXT
    // VIEW IS FOR PUBLIC-FACING DATA -- CURRENTLY ONLY CORRESPONDS TO USERS
    if ( '-view' == $action || 'view' == $action ) {

        // EXIT OUT IF AN ADMIN HAS NOT SPECIFIED THIS AS A PUBLIC FIELD
        if ( empty( $public ) && !in_array( $meta['options']['field_type'], $reserved_types ) )
            return false;

        // FOR USERS -- EXIT OUT IF NOT A USER FIELD
        if ( !isset( $id ) )
            return false;

        // IF THIS IS A CONTAINER, LET IT GO
        if ( in_array( $meta['options']['field_type'], $reserved_types ) ) {

        // IF A USER HAS DECIDED ON THEIR PUBLIC STATUS AND SAID NO
        } else {

            // FOR USERS -- EXIT OUT IF NOT A USER FIELD
            if ( !isset( $user_opt ) )
                return false;

            // SOMEONE HAS UPDATED THIS PROFILE AND SET A PREFERENCE
            if ( is_array( $user_opt ) && isset( $user_opt[$key] ) ) {
                if ( 'true' != $user_opt[$key] )
                    return false;

            // IF A USER HAS NOT DECIDED YET ON THEIR PUBLIC STATUS...
            } else if ( !is_array( $user_opt ) || !isset( $user_opt[$key] ) )  {

                // IF AN ADMIN HAS OPTED TO FORCE DISPLAY ON UNDECIDED USERS
                $admin_opt = get_option( '_bbconnect_public' );
                if ( 'true' != $admin_opt || in_array( $meta['options']['field_type'], $reserved_types ) )
                    return false;

            }
        }
        $field['action'] = '-view';
        $label_wrap = false;
        if ( 'true' == get_option( 'bbconnectpanels_public_labels' ) ) {
            $label = '<span class="bbconnectpanels-profile-label">'.$label.'</span>';
        } else {
            $label = false;
        }
        $help = false;
        $public = false; // A BIT WEIRD, BUT STILL, WE DON'T WANT TO SHOW THE TOGGLE

    } else if ( '-bulk-edit' == $action || 'bulk-edit' == $action ) {

        // FOR PLUGINS LET'S REMOVE THIS
        if ( 'plugin' == $meta['options']['field_type'] )
            return false;

        // SPECIAL CASE FOR CHECKBOX
        if ( 'checkbox' == $meta['options']['field_type'] )
            $label = $meta['name'];

        // SPECIAL CASE FOR UNIQUE USER IDENTIFIERS
        if ( in_array( $meta['meta_key'], $reserved_source ) )
            return false;

        $field['action'] = '-bulk-edit';
        $field['display'] = ' disabled="disabled"';
        $lock = true;
        $public = false;

    } else if ( '-search' == $action || 'search' == $action ) {

        // FOR PLUGINS LET'S REMOVE THIS
        if ( 'plugin' == $meta['options']['field_type'] )
            return false;

        $field['action'] = '-search';
        $field_name = '';
        $field_pos_name = array( '[query]' );
        $wrap = false;
        $help = false;
        $label_wrap = false;
        $label = false;

    } else if ( 'register' == $action ) {

        // FOR PLUGINS LET'S REMOVE THIS
        if ( 'plugin' == $meta['options']['field_type'] )
            return false;

        $field['action'] = 'register';
        $public = false;

    } else if ( '-edit' == $action || 'edit' == $action ) {
        $field['action'] = '-edit';
        if ( false != $readonly )
            $field['action'] = '-view';

        // FOR PLUGINS LET'S REMOVE THIS
        if ( 'user' == $type && false != $meta['options']['admin'] && false == $meta['options']['user'] )
            $admin_only = true;

    } else if ( 'inline' == $action ) {
        $field['action'] = '-edit';

        $label_wrap = false;
        $label = false;
        $help = false;
        $public = false; // A BIT WEIRD, BUT STILL, WE DON'T WANT TO SHOW THE TOGGLE

    } else {
        return false;

    }

    // ALLOW FOR REDIRECTED PROCESSING OF FIELDS
    if ( is_array( $swap_name ) && !empty( $swap_name ) ) {
        foreach ( $swap_name as $name )
            $field_swap_name[] = '[' . $name . ']';
    }
    $field['name'] = $field_pre_name . implode( '', $field_swap_name ) . $field_name . implode( '', $field_pos_name );

    // ALLOW FOR REPLACEMENT IDS
    if ( false != $swap_id ) {
        $field['id'] = $swap_id;
        $field['title'] = $swap_id;
    } else {
        $field['id'] = $key;
        $field['title'] = $key;
    }

    $field['capabilities'] = $bbconnect_cap;

    if ( false != $required || !empty( $meta['options']['req'] ) ) {
        if ( 'bbconnect' == $meta['source'] && false !== strpos( $meta['meta_key'], 'address_state_' ) ) {
            $required = ' semi-required';
            $asterix = '<span class="asterix-required" style="display:none;">*</span>';
        } else {
            $required = ' required';
            $asterix = '<span class="asterix-required">*</span>';
        }
    }

    if ( false != $required && 'checkbox' == $meta['options']['field_type'] && false == $label )
        $mayberequired = __( 'Required', 'bbconnect' );

    // ALLOW FOR ADDITIONAL CLASSES
    if ( isset( $meta['options']['class'] ) ) {
        if ( is_array( $meta['options']['class'] ) ) {
            $field['class'] = ' ' . implode(  ' ', $meta['options']['class'] );
        } else {
            $field['class'] = ' ' . $meta['options']['class'];
        }
    } else {
        $field['class'] = '';
    }

    // SET THE WILDCARD IF APPLICABLE
    if ( false != $flag )
        $field['flag'] = $flag;


    // OVERRIDE DISPALY DEFAULTS FOR RESERVES
    if ( in_array( $meta['options']['field_type'], $reserved_types ) ) {
        $label = false;
        $label_wrap = false;
        $lock = false;
        $help = false;
        $public = false;
    }

    if ( ($admin_only == true && current_user_can('manage_padlock_fields')) || $admin_only == false ) {
        if ( $wrap ) echo '<li class="meta-item'.bbconnect_field_disabled($meta["meta_key"]).'">';

            if ( $label_wrap ) echo '<span class="bbconnect-label'.$required.'">'.$mayberequired;
                if ( $lock ) echo '<a class="rui off" title="' . $key . $action . '">';
                    if ( $label ) echo stripslashes( $label );
                if ( $lock ) echo '</a>';
                if ( $label_wrap ) echo $asterix;
                if ( $help || $public || $admin_only ) { if ( $label ) echo '<br />'; }
                if ( $help && 'checkbox' != $meta['options']['field_type'] ) echo '<a class="help" title="' . $help . '">&nbsp;</a>';
                if ( $admin_only ) echo '<a class="icon-admin" title="' . __( 'Only visible to admins.', 'bbconnect' ) . '">&nbsp;</a>';
                if ( $public ) echo '<a class="pmt '. $pmt .'" rel="' . __( 'Public or Private', 'bbconnect' ) . '" title="public_'. $key .'"><input type="hidden" id="public_'. $key .'"  name="' . $field_pre_name . '[bbc_public]['. $key .']" value="'. $pmv .'" />&nbsp;</a>';
            if ( $label_wrap ) echo '</span>';

            if ( $label_wrap ) echo '<span class="bbconnect-field">';

            // TEST FOR A DEFAULT VALUE
            if ( $field["value"] == '' || $field ["value"] == NULL ) $field["value"] = bbconnect_field_defaults( $meta["meta_key"], $field["value"] );

                bbconnect_field( $meta, $field );
            if ( $help && 'checkbox' == $meta['options']['field_type'] ) echo '<a class="help" title="' . $help . '">&nbsp;</a>';
            if ( $label_wrap ) echo '</span>';

        if ( $wrap ) echo '</li>';
    }

}

function bbconnect_field_defaults($meta_key, $value) {
    //     switch ($meta_key) {
    //         case 'address_country_1':
    //             $value = 'AU';
    //             break;
    //     }
    return $value;
}

function bbconnect_field( $meta, $args = array() ) {

    // CORNERSHOP CREATIVE CUSTOMIZATION
    $meta = apply_filters ( 'bbconnect_field_prepare', $meta, $args );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    // FOR CONVENIENCE
    $key = $meta['meta_key'];

    $disabled = bbconnect_field_disabled( $key );

    // SETUP THE LOGIC
    switch( $meta['options']['field_type'] ) :

        case 'title' : // BUMP

            echo '<h3 class="bbconnect-section">' . stripslashes( $meta['name'] ) . '</h3>';
            if ( isset( $meta['description'] ) && !empty( $meta['description'] ) )
                echo '<p>' . $meta['description'] . '</p>';

        break;

        case 'taxonomy' :

            $tax = $key;
            $taxonomy = get_taxonomy( $tax );

            // DETERMINE WHETHER OR NOT CHILDREN ARE DISPLAYED
            if ( isset( $meta['options']['children'] ) && false == $meta['options']['children'] ) {
                $children = '&parent=0';
            } else {
                $children = '';
            }

            // SET THE DISPLAY OPTIONS
            if ( isset( $meta['options']['sort_by'] ) && false == $meta['options']['sort_by'] ) {
                $sort = '&orderby=name';
            } else {
                $sort = '&orderby=id';
            }

            $terms = get_terms( $tax, 'hide_empty=0'.$sort.$children );

            if ( '-view' == $action ) {

                foreach ( $terms as $term ) {
                    if ( !empty( $value ) && in_array_r( $term->term_id, $value ) )
                        $output_arr[] = $term->name;
                }

                if ( !empty( $output_arr ) ) {
                    echo '<div> ' . implode( ', ', $output_arr ) . '</div>';
                }


            } else {

                $display = get_option( '_bbconnect_taxonomy_display' );

                // LET THE API OVERRIDE THIS
                if ( isset( $meta['options']['display'] ) ) {
                    $display = $meta['options']['display'];
                }

                if ( 'select' == $display || '-search' == $action ) {

                    echo '<select class="chzn-select" multiple="multiple" name="' . $name .'[]" style="width: 200px">';

                    $child_terms = array();

                    if ( '' == $children ) {
                        foreach ( $terms as $term ) {
                            $selected = '';
                            if ( false != $term->parent ) {
                                if ( !empty( $value ) && in_array_r( $term->term_id, $value ) ) $selected = ' selected';
                                $child_terms[$term->parent][] = '<option value="' . $term->term_id . '"' . $selected. '>&nbsp;&nbsp;&nbsp;' . $term->name . '</option>';
                            }
                        }
                    }

                    foreach ( $terms as $term ) {
                        $selected = '';
                        if ( false != $term->parent )
                            continue;

                        if ( !empty( $value ) && in_array_r( $term->term_id, $value ) ) $selected = ' selected';
                        echo '<option value="' . $term->term_id . '"' . $selected. '>' . $term->name . '</option>';

                        if ( isset( $child_terms[$term->term_id] ) ) {
                            foreach( $child_terms[$term->term_id] as $pk => $pv )
                                echo $pv;
                        }
                    }

                    echo '</select>';

                } else {

                    echo '<div class="taxonomy-panel">';

                    if ( '' == $children ) {
                        foreach ( $terms as $term ) {
                            $checked = '';
                            if ( false != $term->parent ) {
                                if ( !empty( $value ) && in_array_r( $term->term_id, $value ) ) $checked = ' checked';
                                $child_terms[$term->parent][] = '<span class="float-checkbox">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="' . $name .'[]"  value="' . $term->term_id . '"' . $checked. ' />&nbsp;' . $term->name . '</span>';
                            }
                        }
                    }

                    foreach ( $terms as $term ) {
                        $checked = '';
                        if ( false != $term->parent )
                            continue;

                        if ( !empty( $value ) && in_array_r( $term->term_id, $value ) ) $checked = ' checked';
                        echo '<span class="float-checkbox"><input type="checkbox" name="' . $name .'[]"  value="' . $term->term_id . '"' . $checked. ' />&nbsp;' . $term->name;

                        if ( isset( $child_terms[$term->term_id] ) ) {
                            foreach( $child_terms[$term->term_id] as $pk => $pv )
                                echo $pv;
                        }

                        echo '</span>';
                    }

                    echo '</div>';

                }

                if ( '-edit' == $action ) {
                    echo '<input type="hidden" name="' . $name .'[]"  value="" />';
                }

                // SPECIAL CASES
                if ( '-bulk-edit' == $action )
                    echo '<div style="clear: both; padding: 7px;"><input type="radio" name="bbconnect_user_taxonomy_options[' . $key . ']" value="append" checked="checked" /> ' . __( 'Append', 'bbconnect' ) . '<input type="radio" name="bbconnect_user_taxonomy_options[' . $key . ']" value="overwrite" /> ' . __( 'Overwrite', 'bbconnect' ) . '<input type="radio" name="bbconnect_user_taxonomy_options[' . $key . ']" value="remove" /> ' . __( 'Remove', 'bbconnect' ) . '</div>';

                if ( '-edit' == $action && current_user_can( 'list_users' ) )
                    echo '<div class="end-of-float"><a href="' . get_bloginfo('wpurl') . '/wp-admin/edit-tags.php?taxonomy=' . $tax . '&TB_iframe=true&height=450&width=920" class="thickbox button-primary">' . __( 'Add terms', 'bbconnect' ) . '</a></div>';

            }

        break;

        case 'section' :

            if ( '-view' != $action || 'true' == get_option( 'bbconnectpanels_public_labels' ) )
                echo '<h3>' . stripslashes( $meta['name'] ) . '</h3>';

            echo '<ul>';
            if ( !empty( $meta['options']['choices'] ) ) {

                // THIS IS A PASS THROUGH FOR ANY SANITIZED POST VALS
                $child_vals = array();
                if ( isset( $value ) && is_array( $value ) && !empty( $value ) )
                    $child_vals = $value;

                foreach ( $meta['options']['choices'] as $skey => $sval ) {

                    // GET THE OPTION
                    $smeta = bbconnect_get_option( $sval );

                    // CHECK TO SEE IF ANY FIELDS HAVE BEEN REMOVED FROM DISPLAY
                    $grouped_hide = bbconnect_hide_meta( array( 'meta' => $smeta, 'bbconnect_cap' => $capabilities, 'action' => $action ) );
                    if ( $grouped_hide )
                        continue;

                    $post_val = false;
                    if ( in_array( $smeta['options']['field_type'], array( 'group' ) ) ) {
                        $post_val = $child_vals;
                    } else if ( isset( $child_vals[$smeta['meta_key']] ) ) {
                        $post_val = $child_vals[$smeta['meta_key']];
                    }

                    // ECHO OUT THE FIELDS!
                    bbconnect_get_field( array( 'meta' => $smeta, 'id' => $cid, 'action' => $action, 'type' => $type, 'post_val' => $post_val ) );

                }
            }
            echo '</ul>';

        break;

        case 'textarea' :

            // ALLOW OPTIONS TO HAVE SOME PRESETS
            if ( empty( $value ) && !empty( $meta['options']['choices'] ) )
                $value = $meta['options']['choices'];

            if ( '-view' == $action ) {

                echo wpautop( $value );

            } else if ( '-edit' == $action ) {

                // ONLY DISPLAY THE TEXT AREA IF WE KNOW FOR SURE WE HAVE THE RIGHT RESOURCES
                if ( isset( $meta['options']['wp_editor'] ) && false != $meta['options']['wp_editor'] ) {

                    // CORNERSHOP CREATIVE CUSTOMIZATION
                    $html_value = utf8_encode(html_entity_decode( $value ));

                    wp_editor( $html_value, $key, array( 'textarea_name' => $name ) );
                } else {
                    echo '<textarea '.$disabled.' name="' . $name . '" id="' . $id . '" class="' . $class . '">' .  $value . '</textarea>';
                }

            } else {
                echo '<textarea '.$disabled.' name="' . $name . '" id="' . $id . '" class="' . $class . '">' .  $value . '</textarea>';

            }

        break;

        case 'select' :
        case 'multiselect' :

            if ( '-view' == $action ) {

                if ( 'wpr' == $meta['source'] && 'display_name' == $key ) {
                    echo $value->display_name;
                } else {
                    echo $value;
                }

            } else {

                // OPTIONALLY ADD LOOKUPS
                if ( '-search' == $action ) {

                    $autocomp = ' chzn-select';
                    $multiple = ' multiple="multiple" data-placeholder="' . __( 'All Options', 'bbconnect' ) . '" style="width: 150px"';
                    $name = $name . '[]';

                } else if ( 'multiselect' == $meta['options']['field_type'] ) {

                    $autocomp = ' chzn-select';
                    $multiple = ' multiple="multiple"';
                    $name = $name . '[]';

                } else if ( 'address' == substr( $key, 0, 7 ) ) {

                    if ( false !== strpos( $key, 'country' ) ) {
                        $multiple = '';
                        $autocomp = ' country-field';
                    } else if ( false !== strpos( $key, 'state' ) ) {
                        $multiple = '';
                        $autocomp = ' state-province-field';
                    } else {
                        $autocomp = '';
                        $multiple = '';
                    }

                } else {

                    $autocomp = '';
                    $multiple = '';

                }

                if ( 'wpr' == $meta['source'] ) {
                    if ( '-search' == $action ) {
                    } else if ( isset( $flag ) && 'normalize' == $flag ) {
                    } else {
                        $name = $key;
                    }
                }

                // LET'S HANDLE ROLES
                if ( 'wpr' == $meta['source'] && 'role' == $key ) {
                    if (defined('MDMR_PATH')) { // Multiple Roles plugin in use
                        $name = 'md_multiple_roles[]';
                        $multiple = ' multiple="multiple"';
                        wp_nonce_field( 'update-md-multiple-roles', 'md_multiple_roles_nonce' );
                    }
                    echo '<select '.$disabled.' name="' . $name . '" id="' . $id . '" class="regular-text' . $class . $autocomp . '"' . $multiple . '>';

                    if ( is_array( $value ) ) {
                        $user_role = $value;
                    } else {
                        $user_role = array_intersect( array_values( $value->roles ), array_keys( get_editable_roles() ) );
                    }

                    // print the full list of roles with the primary one selected.
                    bbconnect_dropdown_roles( $user_role );

                    // print the 'no role' option. Make it selected if the user has no role yet.
                    if ( '-search' == $action ) {
                        echo '<option value="no_role">' . __('No role for this site') . '</option>';
                    } else if ( $user_role ) {
                        echo '<option value="">' . __('&mdash; No role for this site &mdash;') . '</option>';
                    } else {
                        echo '<option value="" selected="selected">' . __('&mdash; No role for this site &mdash;') . '</option>';
                    }

                    echo '</select>';

                // AND DISPLAY NAMES (ADDING A FEW OPTIONS ALONG THE WAY)
                } else if ( 'wpr' == $meta['source'] && 'display_name' == $key ) {

                    if ( is_user_logged_in() && !empty( $value ) ) {

                        $cuser = $value;

                        echo '<select '.$disabled.' name="' . $name . '" id="' . $id . '" class="regular-text' . $class . $autocomp . '"' . $multiple . '>';

                        $public_display = array();
                        if ( !empty($cuser->nickname) )
                            $public_display['display_nickname']  = $cuser->nickname;

                        if ( !empty($cuser->user_login) )
                            $public_display['display_username']  = $cuser->user_login;

                        if ( !empty($cuser->first_name) )
                            $public_display['display_firstname'] = $cuser->first_name;

                        if ( !empty($cuser->last_name) )
                            $public_display['display_lastname'] = $cuser->last_name;

                        // FOR ORGS
                        if ( !empty($cuser->bbconnect_organization) )
                            $public_display['display_organization'] = $cuser->bbconnect_organization;

                        if ( !empty($cuser->first_name) && !empty($cuser->last_name) ) {

                            // SETUP FORMAL VARS
                            if ( !empty( $cuser->bbconnect_prefix ) )
                                $m_pre = $cuser->bbconnect_prefix . ' ';

                            if ( !empty( $cuser->bbconnect_suffix ) )
                                $m_suf = ' ' . $cuser->bbconnect_suffix;

                            if ( !empty( $cuser->bbconnect_middle_name ) ) {
                                $m_mid = ' ' . $cuser->bbconnect_middle_name . ' ';
                            } else {
                                $m_mid = ' ';
                            }

                            $public_display['display_formal'] = $m_pre . $cuser->first_name . $m_mid . $cuser->last_name . $m_suf;
                            $public_display['display_firstlast'] = $cuser->first_name . ' ' . $cuser->last_name;
                            $public_display['display_lastfirst'] = $cuser->last_name . ' ' . $cuser->first_name;
                        }

                        if ( !empty($cuser->display_name) ) {
                            if ( !in_array( $cuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
                                $public_display = array( 'display_displayname' => $cuser->display_name ) + $public_display;
                        }

                        $public_display = array_map( 'trim', $public_display );
                        $public_display = array_unique( $public_display );

                        foreach ( $public_display as $id => $item ) {
                            echo '<option id="' . $id . '"' . selected( $cuser->display_name, $item ) . '>' . $item . '</option>';
                        }

                        echo '</select>';

                    }

                // EVERYTHING ELSE
                } else {
                    if (!is_array($value)) {
                        $value = html_entity_decode($value); // Hack to fix issue with "Mr & Mrs"
                    }
                    echo '<select '.$disabled.' name="' . $name . '" id="' . $id . '" class="regular-text' . $class . $autocomp . '"' . $multiple . '>';
                    if ( empty( $value ) && isset( $meta['options']['default'] ) ) {
                        $value = $meta['options']['default'];
                    }

                    $setext = '';
                    echo '<option value="">' . $setext . '</option>';

                    // IF THIS IS A REFERENCE TO A HELPER, PICK IT UP
                    if ( !is_array( $meta['options']['choices'] ) ) {
                        $option_arr = call_user_func( $meta['options']['choices'] );
                    } else {
                        $option_arr = $meta['options']['choices'];
                    }

                    // IF THIS IS AN ASSOCIATIVE ARRAY, CREATE OPTION GROUPS
                    foreach( $option_arr as $choices => $choice ) {
                        if ( is_array( $choice ) ) {
                            $true_match = true;
                            if (false !== strpos( $meta['meta_key'], 'address_state_' )) {
                                $true_match = false;
                                $address_number = substr($meta['meta_key'], -1);
                                $user_country = get_user_meta($_GET['user_id'], 'bbconnect_address_country_'.$address_number, true);
                                $countries = bbconnect_helper_country();
                                $country = array_key_exists($user_country, $countries) ? $countries[$user_country] : $user_country;
                                if ($choices == $country) {
                                    $true_match = true;
                                }
                            }
                            foreach ( $choice as $topkey => $topvalue ) {
                                if ( is_array( $topvalue ) ) {
                                    echo '<optgroup label="'.$topkey.'">';
                                    foreach ( $topvalue as $subkey => $subvalue ) {
                                        echo '<option value="'.$subkey.'"';
                                        if ( is_array( $value ) ) {
                                            if (in_array( $subkey, $value ) && $true_match)
                                                echo ' selected="selected"';
                                        } elseif ($true_match) {
                                            echo selected( $value, $subkey );
                                        }
                                        echo '>'.$subvalue.'</option>';
                                    }
                                    echo '</optgroup>';
                                } else {
                                    echo '<option value="'.$topkey.'"';
                                    if ( is_array( $value ) ) {
                                        if (in_array( $topkey, $value ) && $true_match)
                                            echo ' selected="selected"';
                                    } elseif ($true_match) {
                                        echo selected( $value, $topkey );
                                    }
                                    echo '>'.$topvalue.'</option>';
                                }
                            }
                        } else {
                            echo '<option value="'.$choices.'"';
                            if ( is_array( $value ) ) {
                                if ( in_array( $choices, $value ) )
                                    echo ' selected="selected"';
                            } else {
                                echo selected( $value, $choices );
                            }
                            echo '>'.$choice.'</option>';
                        }
                     }
                echo '</select>';
                }
            }

        break;

        case 'checkbox' :

            if ( '-bulk-edit' == $action ) {

                echo '<select '.$disabled.' name="' . $name . '">';
                echo '<option value="true">' . __( 'yes', 'bbconnect' ) . '</option>';
                echo '<option value="false">' . __( 'no', 'bbconnect' ) . '</option>';
                echo '</select>';

            } else if ( '-search' === $action ) {

                echo '<select '.$disabled.' name="' . $name . '">';
                echo '<option value=""' . selected( $value, '' ) . '>' . __( 'empty', 'bbconnect' ) . '</option>';
                echo '<option value="true"' . selected( $value, 'true' ) . '>' . __( 'yes', 'bbconnect' ) . '</option>';
                echo '<option value="false"' . selected( $value, 'false' ) . '>' . __( 'no', 'bbconnect' ) . '</option>';
                echo '</select>';

            } else {

                if ( 'wp' == $meta['source'] ) {

                    // SET THE DEFAULTS
                    if ( 'true' === $meta['options']['choices'] ) {
                        $chk = 'on';
                        $val = 'true';
                    } else {
                        $chk = 'off';
                        $val = 'false';
                    }

                    // LET THE USER SAVED VALUE OVERRIDE THE DEFAULTS
                    if ( !empty( $value ) ) {
                        if ( 'true' === $value ) {
                            $chk = 'on';
                            $val = 'true';
                        } else {
                            $chk = 'off';
                            $val = 'false';
                        }
                    }

                    // SPECIAL CASE FOR THE ADMIN BARS AND SSL PREFERENCES
                    if ( 'rich_editing' == $key || 'comment_shortcuts' == $key ) {
                        $wp_class = 'upt ';
                        $wp_key = $key;
                        $field_name = ' name="' . $key . '"';
                    } else {
                        if ( 'show_admin_bar_front' == $key || 'show_admin_bar_admin' == $key || 'admin_bar_front' == $key ) {
                            $wp_key = 'admin_bar_front';
                            $wp_class = 'uwpt ';
                            if ( 'on' == $chk ) {
                                $field_name = ' name="admin_bar_front"';
                            } else {
                                $field_name = '';
                            }
                            $val = 'true';
                        } else {
                            $wp_key = $key;
                            $wp_class = 'umt ';
                            $field_name = ' name="' . $key . '"';
                        }

                    }

                    echo '<a class="' . $wp_class . $chk . $class . '" title="' . $wp_key . '"><input type="hidden" id="' . $wp_key . '"' . $field_name . ' value="' . $val . '" /> ' . $meta['name'] . '</a> ';

                } else {

                    // SET THE DEFAULTS
                    if ( 'true' === $meta['options']['choices'] ) {
                        $chk = 'on';
                        $val = 'true';
                    } else {
                        $chk = 'off';
                        $val = 'false';
                    }

                    // LET THE USER SAVED VALUE OVERRIDE THE DEFAULTS
                    if ( !empty( $value ) ) {
                        if ( 'true' === $value ) {
                            $chk = 'on';
                            $val = 'true';
                        } else {
                            $chk = 'off';
                            $val = 'false';
                        }
                    }

                    echo'<a class="upt ' . $chk . '" title="' . $title . '"><input type="hidden" id="' . $title . '"  name="' . $name . '" value="' . $val . '" /> ' . $meta['name'] . '</a> ';

                }
            }

        break;

        case 'radio' :

            if ( '-view' == $action ) {

                echo $value;

            } else {
                // IF THIS IS A REFERENCE TO A HELPER, PICK IT UP
                if ( !is_array( $meta['options']['choices'] ) ) {
                    $option_arr = call_user_func( $meta['options']['choices'] );
                } else {
                    $option_arr = $meta['options']['choices'];
                }

                echo '<ul>';

                // IF THIS IS AN ASSOCIATIVE ARRAY, CREATE OPTION GROUPS
                foreach( $option_arr as $subkey => $subvalue ) {
                    if ( '' != $value ) {
                        if ( $subkey == $value ) {
                            $checked = ' checked';
                        } else {
                            $checked = '';
                        }
                    } else {
                        $checked = '';
                    }

                    echo '<li><input '.$disabled.' type="radio" name="' . $name . '" value="' . $subkey . '"' .  $checked . ' /> ' .  $subvalue . '</li>';

                }

                echo '</ul>';
            }

        break;

        case 'group' :

            // THIS IS A PASS THROUGH FOR ANY SANITIZED POST VALS
            $child_vals = array();
            if ( isset( $value ) && is_array( $value ) && !empty( $value ) )
                $child_vals = $value;

            if ( '-view' == $action ) {

                foreach ( $meta['options']['choices'] as $gkey => $gvalue ) {

                    // GET THE OPTION
                    $gmeta = get_option( $gvalue );

                    $grouped_hide = bbconnect_hide_meta( array( 'meta' => $gmeta, 'bbconnect_cap' => $capabilities, 'action' => $action ) );
                    if ( $grouped_hide )
                        continue;

                    $post_val = false;
                    if ( isset( $child_vals[$gmeta['meta_key']] ) )
                        $post_val = $child_vals[$gmeta['meta_key']];

                    bbconnect_get_field( array( 'meta' => $gmeta, 'id' => $cid, 'action' => $action, 'type' => $type, 'post_val' => $post_val ) );
                }

            } else {

                $group_hide = bbconnect_hide_meta( array( 'meta' => $meta, 'group_override' => true, 'bbconnect_cap' => $capabilities, 'action' => $action ) );
                if ( false == $group_hide ) {
                    ?>
                    <li>
                        <div class="t-wrapper group">
                            <p class="t-trigger-wrapper group">
                                <span class="bbconnect-label group">
                                    <span class="t-trigger group" title="<?php echo $id.$action; ?>">
                                        <?php echo $meta['name']; ?>
                                    </span>
                                </span>
                                <span class="bbconnect-field">
                                <?php
                                    // LET PLUGINS EXTEND GROUPS
                                    do_action( 'bbconnect_group_markers', $meta, $cid, $action );
                                ?>
                                </span>
                            </p>
                            <div id="<?php echo $id.$action; ?>" class="t-panel">
                                <ul class="group-panel">
                                <?php
                                    foreach ( $meta['options']['choices'] as $gkey => $gvalue ) {

                                        // GET THE OPTION
                                        $gmeta = bbconnect_get_option( $gvalue );

                                        $grouped_hide = bbconnect_hide_meta( array( 'meta' => $gmeta, 'bbconnect_cap' => $capabilities, 'action' => $action ) );
                                        if ( $grouped_hide )
                                            continue;

                                        $post_val = false;
                                        if ( isset( $child_vals[$gmeta['meta_key']] ) )
                                            $post_val = $child_vals[$gmeta['meta_key']];

                                        bbconnect_get_field( array( 'meta' => $gmeta, 'id' => $cid, 'action' => $action, 'type' => $type, 'post_val' => $post_val ) );

                                    }
                                ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                <?php
                } else {

                    foreach ( $meta['options']['choices'] as $gkey => $gvalue ) {

                        // GET THE OPTION
                        $gmeta = bbconnect_get_option( $gvalue );

                        $grouped_hide = bbconnect_hide_meta( array( 'meta' => $gmeta, 'bbconnect_cap' => $capabilities, 'action' => $action ) );
                        if ( $grouped_hide )
                            continue;

                        $post_val = false;
                        if ( isset( $child_vals[$gmeta['meta_key']] ) )
                            $post_val = $child_vals[$gmeta['meta_key']];

                        bbconnect_get_field( array( 'meta' => $gmeta, 'id' => $cid, 'action' => $action, 'type' => $type, 'post_val' => $post_val ) );

                    }
                }
            }

        break;

        case 'multitext' :

            if ( '-view' == $action ) {
                $fdisplay = array();
                if ( is_array( $value ) ) {
                    foreach( $value as $fkey => $fval ) {
                        if ( is_array( $fval ) ) {
                            $fdisplay[] = '<strong>'.$fval['type'].':</strong> '.$fval['value'];
                        }
                    }
                }
                echo implode( ', ', $fdisplay );

            } else {

                echo '<ul id="' . $id . '">';
                //if ( is_array( $value ) && count( $value ) > 1 ) {

                // IF WE'RE JUST STARTING OUT WITH DATA, PREFILL THE VALUE ARRAY WITH NULLITY
                if ( !is_array( $value ) || empty( $value ) ) {
                    $value = array();

                    // GO WITH OVERRIDES FIRST
                    if ( isset( $meta['options']['add_fields'] ) && is_array( $meta['options']['add_fields'] ) ) {
                        foreach ( $meta['options']['add_fields'] as $k => $v ) {
                            $value[0][$k] = '';
                        }
                    }

                    $value[0]['value'] = '';

                    if ( is_array( $meta['options']['choices'] ) && !empty( $meta['options']['choices'] ) ) {
                        $value[0]['type'] = '';
                    }

                }/*
                $single_val = '';
                foreach( $value as $fkey => $fval ) {
                    $single_val .= $value[$fkey]['value'];
                }
                */

                    $arrcount = count( $value );
                    $nfkey = 0;
                    $key_type = '';

                    // COUNT THE FIELDS AVAILABLE
                    $add_fields = false;
                    $field_ct = 1;
                    if ( isset( $meta['options']['add_fields'] ) && is_array( $meta['options']['add_fields'] ) ) {
                        $add_fields = true;
                        $field_ct = 1 + count( $meta['options']['add_fields'] );
                    }
                    $fper = round( 52 / $field_ct );

                    foreach( $value as $fkey => $fval ) {
                        // CREATE THE TYPE FROM THE OPTION
                        // OPTIONALLY BYPASS THIS
                        if ( is_array( $meta['options']['choices'] ) ) {
                            $arr_choices = array_filter( $meta['options']['choices'] );
                            if ( !empty( $arr_choices ) ) {
                                $key_type = '<select name="'.$name.'['.$nfkey.'][type]" id="'.$id.'-'.$fkey.'-select">';
                                $key_type .= '<option value="">...</option>';
                                foreach ( $meta['options']['choices'] as $typekey => $typeval ) {
                                    $key_type .= '<option value="'.$typekey.'"';
                                    if ( isset( $value[$fkey]['type'] ) ) {
                                        $key_type .= selected( $value[$fkey]['type'], $typekey, false );
                                    }
                                    $key_type .= '>'.$typeval.'</option>';
                                }
                                $key_type .= '</select>';
                            }
                        }

                        if ( false != $add_fields ) {
                            $add_fields = '';
                            foreach ( $meta['options']['add_fields'] as $k => $v ) {
                                if ( isset( $value[$fkey][$k] ) ) {
                                    $ctv = $value[$fkey][$k];
                                } else {
                                    $ctv = '';
                                }

                                $add_name = $v['name'];

                                if ( 'text' == $v['type'] ) {
                                    if ( isset( $v['class'] ) ) $tempclass = $class . ' ' . implode( ' ', $v['class'] );
                                    $add_fields .= '<input type="text" name="' . $name . '['.$nfkey.']['.$k.']" class="regular-text regular-multi' . $tempclass . '" style="width: '.$fper.'%;" value="' . $ctv . '" placeholder="'.$add_name.'" /> ';
                                } else if ( 'checkbox' == $v['type'] ) {
                                    $chk = '';
                                    if ( 'true' == $ctv ) $chk = 'on';
                                    $add_fields .= '<a class="upt ' . $chk . '" title="' . $k . '-' . $nfkey . '"><input type="hidden" id="' . $k . '-' . $nfkey . '"  name="' . $name . '['.$nfkey.']['.$k.']" value="' . $ctv . '" /> ' . $add_name . '</a> ';
                                }
                            }
                        }
                    ?>
                        <li id="<?php echo $id .''. $fkey; ?>" class="multilist">
                            <?php /*<span class="handle"></span>*/?>
                            <span>
                                <?php echo $add_fields; ?>
                                <input type="text" name="<?php echo $name . '['.$nfkey.'][value]'; ?>" class="regular-text regular-multi<?php echo $class; ?>" style="width: <?php echo $fper; ?>%;" value="<?php echo $value[$fkey]['value']; ?>" placeholder="<?php _e( 'Value', 'bbconnect' ); ?>" />
                                <?php echo $key_type; ?>
                            </span>
                            <?php if ( $arrcount == 1 ) { ?>
                                <a id="<?php echo $id .''. $nfkey; ?>-sub" class="sub" title="<?php echo $nfkey; ?>" style="display:none;">&nbsp;</a>
                                <a id="<?php echo $id .''. $nfkey; ?>-add" class="add" title="<?php echo $nfkey; ?>">&nbsp;</a>
                            <?php } else if ( $nfkey == ( $arrcount - 1 ) ) { ?>
                                <a id="<?php echo $id .''. $nfkey; ?>-sub" class="sub" title="<?php echo $nfkey; ?>">&nbsp;</a>
                                <a id="<?php echo $id .''. $nfkey; ?>-add" class="add" title="<?php echo $nfkey; ?>">&nbsp;</a>
                            <?php } elseif ( $nfkey == 0 ) { ?>
                                <a id="<?php echo $id .''. $nfkey; ?>-sub" class="sub" title="<?php echo $nfkey; ?>" style="display:none;">&nbsp;</a>
                            <?php } else { ?>
                                <a class="sub" title="<?php echo $nfkey; ?>">&nbsp;</a>
                            <?php } ?>
                        </li>
                    <?php

                        $nfkey++;
                    }
                /*
                } else {
                    if ( !is_array( $value ) ) {
                        $value = array();
                    }
                    $single_val = '';
                    foreach( $value as $fkey => $fval ) {
                        $single_val .= $value[$fkey]['value'];
                    }
                    if ( !isset( $fkey ) ) {
                        $fkey = 0;
                    }


                    // CREATE THE TYPE FROM THE OPTION
                    // OPTIONALLY BYPASS THIS
                    if ( isset( $meta['options']['single_value'] ) ) {
                        $key_type = '';
                    } else {
                        $key_type = '<select name="'.$name.'[0][type]">';
                        $key_type .= '<option value="">...</option>';
                        foreach ( $meta['options']['choices'] as $typekey => $typeval ) {
                            if ( is_array( $value ) && array_key_exists( $fkey, $value ) && isset( $value[$fkey]['type'] ) ) {
                                $key_type .= '<option value="'.$typekey.'"'. selected( $value[$fkey]['type'], $typekey, false ) . '>'.$typeval.'</option>';
                            } else {
                                $key_type .= '<option value="'.$typekey.'">'.$typeval.'</option>';
                            }
                        }
                        $key_type .= '</select>';
                    }

                    if ( isset( $meta['options']['double_value'] ) ) {
                        $dbl = '<input type="text" name="' . $name . '[0][label]" class="regular-text regular-multi' . $class . '" value="' . $value[$fkey]['label'] . '" /> ';
                    } else {
                        $dbl = false;
                    }
                    ?>
                    <li id="<?php echo $id .''. $fkey; ?>" class="multilist"><span><?php echo $dbl; ?><input type="text" name="<?php echo $name . '[0][value]'; ?>" class="regular-text regular-multi<?php echo $class; ?>" value="<?php echo $single_val; ?>" /> <?php echo $key_type; ?></span> <a id="<?php echo $id .''. $fkey; ?>-sub" class="sub" title="<?php echo $fkey; ?>" style="display:none;">&nbsp;</a> <a id="<?php echo $id .''. $fkey; ?>-add" class="add" title="0">&nbsp;</a></li>
                    <?php
                }
                */
                echo '</ul>';
            }

        break;

        case 'password' :

            if ( '-view' != $action ) {

                echo '<ul>';

                $choices = $meta['options']['choices'];
                if ( 'wpr' == $meta['source'] ) {
                    ?>
                    <li><input type="password" autocomplete="off" name="<?php echo $key.$choices[0]; ?>" id="<?php echo $key.$choices[0].$action; ?>" class="regular-text<?php echo $class; ?>" value="" /> <?php _e( 'type password', 'bbconnect' ); ?></li>
                    <li><input type="password" autocomplete="off" name="<?php echo $key.$choices[1]; ?>" id="<?php echo $key.$choices[1].$action; ?>" class="regular-text<?php echo $class; ?>" value="" /> <?php _e( 're-type password', 'bbconnect' ); ?></li>
                    <?php
                } else {
                    ?>
                    <li><input type="password" autocomplete="off" name="<?php echo $name; ?>[<?php echo $choices[0]; ?>]" id="<?php echo $key.$choices[0].$action; ?>" class="regular-text<?php echo $class; ?>" value="" /> <?php _e( 'type password', 'bbconnect' ); ?></li>
                    <li><input type="password" autocomplete="off" name="<?php echo $name; ?>[<?php echo $choices[1]; ?>]" id="<?php echo $key.$choices[1].$action; ?>" class="regular-text<?php echo $class; ?>" value="" /> <?php _e( 're-type password', 'bbconnect' ); ?></li>
                    <?php
                }

                echo '</ul>';

            }

        break;

        case 'date' :
            if ( 'wpr' == $meta['source'] ) {
                if ( '-search' == $action ) {
                } else if ( isset( $flag ) && 'normalize' == $flag ) {
                } else {
                    $name = $key;
                }
            }

            if ( is_array( $value ) ) {
                $vds = $value[0];
                $vde = $value[1];
            } else {
                $vds = false;
                $vde = false;
                if ($disabled && !empty($value)) {
                    $value = date('d F Y', strtotime($value));
                }
            }
            if (!empty($vds) && is_real_date($vds) && $disabled) {
                $vds = date('d F Y',strtotime($vds));
            }
            if (!empty($vde) && is_real_date($vde) && $disabled) {
                $vde = date('d F Y',strtotime($vde));
            }

            if ( '-search' == $action ) {
                echo '<input '.$disabled.' type="text" name="'.$name.'[]" id="'.$id.'_start" placeholder="'.__( 'start', 'bbconnect' ).'" class="regular-text bbconnect-date'.$class.'" value="'.$vds.'" />';
                echo '<input '.$disabled.' type="text" name="'.$name.'[]" id="'.$id.'_end" placeholder="'.__( 'end', 'bbconnect' ).'" class="regular-text bbconnect-date'.$class.'" value="'.$vde.'" />';
            } else {
                echo '<input '.$disabled.' type="text" name="'.$name.'" id="'.$id.'" class="regular-text bbconnect-date'.$class.'" value="'.$value.'" />';
            }

        break;

        case 'plugin' :
            $args = array();
            if ( 'user' == $type ) {
                $args['fdata'] = get_userdata( $cid );
            } else if ( 'option' == $type ) {
                $args['fdata'] = $meta;
            } else if ( 'post' == $type ) {
                $args['fdata'] = array( 'id' => $id, 'name' => $name, 'class' => $class );
            }
            $args['fvalue'] = $value;
            $args['faction'] = $action;
            $args['ftype'] = $type;

            $func = $meta['options']['choices'];
            call_user_func( $func, $args );

        break;

        case 'number':
        case 'text' :
        case 'hidden' :

            // IF THIS IS A WORDPRESS USER FIELD, MAKE SOME SMALL MODIFICATIONS
            if ( 'wpr' == $meta['source'] ) {

                if ( !empty( $value ) && is_object( $value ) ) {
                    if ( 'email' == $key || 'url' == $key ) {
                        $user_key = 'user_'.$key;
                    } else {
                        $user_key = $key;
                    }
                    $value = $value->$user_key;
                    if ( 'user_registered' == $key && !empty( $value ) ) { $value = date( get_option( 'date_format' ), strtotime( $value ) ); }
                }

                if ( '-search' == $action ) {
                } else if ( isset( $flag ) && 'normalize' == $flag ) {
                } else {
                    $name = $key;
                }

                if ( '-search' != $action ) {
                    if ( 'user_login' == $key && !empty( $value ) ) { $disabled = ' disabled autocomplete="username"'; }
                    if ( 'ID' == $key && !empty( $value ) ) { $disabled = ' disabled'; }
                    if ( 'user_registered' == $key && !empty( $value ) ) { $disabled = ' disabled'; }
                }

            }

            // ALLOW OPTIONS TO HAVE SOME PRESETS
            if ( empty( $value ) && !empty( $meta['options']['choices'] ) )
                $value = $meta['options']['choices'];

            $field_type = $meta['options']['field_type'];
            $attr = '';
            if ($field_type == 'number') {
                $align = 'right';
                if ($meta['options']['is_currency'] && $value != '' && $disabled) {
                    $field_type = 'text';
                    $value = '$'.number_format($value, 2);
                }
                $step = $meta['options']['is_currency'] ? '0.01' : '1';
                $attr = ' step="'.$step.'"';
            } else {
                $align = 'left';
            }

            if ( '-view' == $action ) {

                if ( 'email' == $key ) {
                    echo '<a href="mailto:'.$value.'">'.$value.'</a>';
                } else if ( 'url' == $key ) {
                    echo '<a href="'.$value.'">'.$value.'</a>';
                } else {
                    echo $value;
                }

            } else {

                if ($meta['options']['field_type'] != 'text') {
                    echo '<input type="'.$field_type.'" name="'.$name.'" id="'.$id.'" class="regular-text'.$class.'" value="'.$value.'" style="text-align: '.$align.';" '.$disabled.' '.$attr.'>';
                } else if ( '-search' == $action && 'user_registered' == $key ) {
                    if ( is_array( $value ) ) {
                        $vs = $value[0];
                        $ve = $value[1];
                    } else {
                        $vs = false;
                        $ve = false;
                    }
                    echo '<input type="text" name="'.$name.'[]" id="'.$id.'_start" placeholder="'.__( 'start', 'bbconnect' ).'" class="regular-text bbconnect-date'.$class.'" value="'.$vs.'" />';
                    echo '<input type="text" name="'.$name.'[]" id="'.$id.'_end" placeholder="'.__( 'end', 'bbconnect' ).'" class="regular-text bbconnect-date'.$class.'" value="'.$ve.'" />';
                } else if ( '-search' == $action && 'ID' == $key ) {
                    echo '<input type="text" name="'.$name.'" id="'.$id.'" class="regular-text'.$class.'" value="'.$value.'"'.$disabled.' />';
                } else if ( 'user_registered' == $key || 'ID' == $key ) {
                    echo $value . '<br />';
                    if ( 'ID' == $key )
                        echo implode( '<br />', apply_filters( 'bbconnect_user_id_assets', array(), $value, $action ) );
                } else if ( '-search' == $action && is_array( $value ) ) {
                    echo '<input type="text" name="'.$name.'" id="'.$id.'" class="regular-text'.$class.'" value=""'.$disabled.' />';
                } else {
                    echo '<input type="text" name="'.$name.'" id="'.$id.'" class="regular-text'.$class.'" value="'.$value.'"'.$disabled.' />';
                }

            }

        break;

        // EXTEND THE FIELD SYSTEM
        default :
            do_action( 'bbconnect_field_switch', $meta, $args );
        break;

    endswitch;

}

function bbconnect_field_disabled($key) {
    $current_screen = get_current_screen();
    $valid_pages = array(
            'users_page_bbconnect_new_user',
            'users_page_bbconnect_edit_user',
            'toplevel_page_bbconnect_edit_user_profile',
    );
    if (!in_array($current_screen->id, $valid_pages)) {
        return '';
    }

    $match = 0;
    $disabled_fields = array('kpi', 'segment', 'disabled', 'category');
    foreach ($disabled_fields as $field) {
        if (strpos(strtolower($key), strtolower($field)) !== false) {
            $match++;
        }
    }

    $disabled = $match > 0;
    $disabled = apply_filters('bbconnect_field_disabled', $disabled, $key);

    return $disabled ? ' disabled' : '';
}
