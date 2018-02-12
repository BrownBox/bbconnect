<?php
/**
 * Creates a new user. If a user is successfully created, returns false and runs the edit user function.
 *
 * @since 1.0.0
 * @param none
 * @return html
 */

function bbconnect_new_user() {

    // STOP THEM IF THEY SHOULDN'T BE HERE
    if ( !current_user_can( 'add_users' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // SET GLOBAL VARIABLES
    global $current_user, $bbconnect_cap;
    if ( current_user_can( 'list_users' ) ) {
        $bbconnect_cap = 'admin';
    } else {
        $bbconnect_cap = 'user';
    }
    define( 'IS_PROFILE_PAGE', false );

    // SET THE USER INFORMATION
    get_currentuserinfo();

    // PROCESS THE FORM
    if ( isset( $_POST['new_user'] ) ) {

        // SECURITY CHECK
        check_admin_referer( 'bbconnect-nonce' );

        $bbconnect_success = bbconnect_insert_user( array( 'ivals' => $_POST ) );

        if ( !is_wp_error( $bbconnect_success ) ) {
            ?>
                <div id="message" class="updated">
                    <p><strong><?php _e( 'User created!', 'bbconnect' ) ?></strong></p>
                </div>
            <?php
            bbconnect_edit_user( $bbconnect_success );
            return false;

        } else {
        ?>
            <div id="message" class="error">
                <p><strong><?php echo $bbconnect_success->get_error_message(); ?></strong></p>
            </div>
        <?php
        }

    }

?>

        <div id="bbconnect" class="wrap">
        <div id="icon-users" class="icon32"><br /></div>
            <h2><?php _e( 'Add New User', 'bbconnect' ); ?></h2>
            <form id="user-form" enctype="multipart/form-data" action="<?php echo admin_url( 'users.php?page=bbconnect_new_user' ); ?>" autocomplete="off" method="POST">

            <?php wp_nonce_field('bbconnect-nonce'); ?>

            <div>
                <?php bbconnect_profile_user_meta( array( 'bbconnect_cap' => $bbconnect_cap, 'action' => 'edit' ) ); ?>
            </div>
            <div>
                <input id="profile-submission" type="submit" name="new_user" value="<?php _e( 'Add Them!', 'bbconnect' ); ?>" class="button-primary" />
            </div>

            </form>
        </div>
<?php
}


function bbconnect_update_user() {

    // PROCESS THE UPDATE IF APPLICABLE
    if ( !empty( $_POST['update'] ) && isset( $_POST['edit_user_profile'] ) ) {

        // SECURITY CHECK
        check_admin_referer( 'bbconnect-edit-user-nonce' );

        $user_id = $_POST['user_id'];
        global $errors, $updated, $current_user;

        //if ( !current_user_can('edit_user', $user_id) )
        if ( !bbconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) ) )
            wp_die(__('You do not have permission to edit this user.'));

        /* PRESERVE THE WORDPRESS HOOK TO UPDATE USER META
        if ( IS_PROFILE_PAGE )
            do_action('personal_options_update', $user_id);
        else
            do_action('edit_user_profile_update', $user_id);
        */
        if ( 'meta' == $_POST['update'] ) {
            bbconnect_update_user_metadata( array( 'user_id' => $user_id, 'uvals' => $_POST, 'source' => 'back' ) );

            // IF THEY'RE EMAIL IS NULLIFIED, ZERO IT
            if ( empty( $_POST['email'] ) ) {
                $temp_user = get_user_by( 'id', $user_id );
                $_POST['email'] = $temp_user->user_login . '@noreply.invalid';
            }

            // Get the nickname into the right place in $_POST otherwise WP will complain
            if (empty($_POST['nickname'])) {
                if (!empty($_POST['bbconnect_user_meta']['nickname'])) {
                    $_POST['nickname'] = $_POST['bbconnect_user_meta']['nickname'];
                } elseif (!empty($_POST['bbconnect_user_meta']['first_name'])) {
                    $_POST['nickname'] = $_POST['bbconnect_user_meta']['first_name'];
                } else {
                    $_POST['nickname'] = 'N/A';
                }
            }

            // UPDATE THE WORDPRESS PROFILE DEFAULTS
            $errors = edit_user($user_id);

            if ( !is_wp_error( $errors ) )
                $updated = __( 'Profile Updated.', 'bbconnect' );
            /*
            $current_user = wp_get_current_user();
            if (
                !is_wp_error( $errors ) &&
                $current_user->ID == $user_id &&
                isset( $_POST['pass1'] ) &&
                !empty( $_POST['pass1'] ) &&
                isset( $_POST['pass2'] ) &&
                !empty( $_POST['pass2'] ) &&
                $_POST['pass1'] === $_POST['pass2']
            ) {
                wp_redirect( 'wp-login.php' );
                exit;
            }
            */

        } else if ( 'actions' == $_POST['update'] ) {
            $updated = __( 'History updated.', 'bbconnect' );
        }

    }

}


/**
 * The Admin-facing user edit screen
 *
 * @since 1.0.0
 * @param $user_id int. The user ID
 * @return html
 */

function bbconnect_edit_user( $user_id = '' ) {

    if ( '' ==  $user_id && isset( $_POST['uuid'] ) )
        $user_id = $_POST['uuid'];

    if ( '' != $user_id )
        $_GET['user_id'] = $user_id;

    // WORDPRESS SETUP
    wp_reset_vars(array('action', 'redirect', 'profile', 'user_id', 'wp_http_referer'));

    if ( empty( $_GET['user_id'] ) )
        define('IS_PROFILE_PAGE', true );

    // SET THE USER INFORMATION
    global $user_id;
    $user_id = (int) $user_id;
    $current_user = wp_get_current_user();
    if ( ! defined( 'IS_PROFILE_PAGE' ) )
        define( 'IS_PROFILE_PAGE', ( $user_id == $current_user->ID ) );

    if ( ! $user_id && IS_PROFILE_PAGE )
        $user_id = $current_user->ID;
    elseif ( ! $user_id && ! IS_PROFILE_PAGE )
        wp_die(__( 'Invalid user ID.' ) );
    elseif ( ! get_userdata( $user_id ) )
        wp_die( __('Invalid user ID.') );

    //if ( !current_user_can('edit_user', $user_id) )
    if ( !bbconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) ) )
        wp_die(__('You do not have permission to edit this user.'));

    $profileuser = get_user_to_edit( $user_id );

    global $errors, $updated;
    if ( isset( $updated ) ) {
?>
    <div id="message" class="updated">
        <p><strong><?php echo $updated; ?></strong></p>
    </div>
<?php
    }

    if ( isset( $errors ) && is_wp_error( $errors ) ) {
?>
    <div class="error"><p><?php echo implode( "</p>\n<p>", $errors->get_error_messages() ); ?></p></div>
<?php
    }

    // SET BBCONNECT POSITIONS
    // SET GLOBAL VARIABLES
    global $current_user, $bbconnect_cap;
    if ( current_user_can( 'list_users' ) ) {
        $bbconnect_cap = 'admin';
        $formdes = admin_url( 'users.php?page=bbconnect_edit_user&user_id=' . $user_id );
    } else {
        $bbconnect_cap = 'user';
        $formdes = admin_url( 'admin.php?page=bbconnect_edit_user_profile&user_id=' . $user_id );
    }

    $tabs = apply_filters( 'bbconnect_user_tabs', array(

        'meta' => array(
                                    'title' => __( 'Profile', 'bbconnect' ),
                                    'subs' => false,
                            ),

        /*'actions' => array(
                                    'title' => __( 'History', 'bbconnect' ),
                                    'subs' => false,
                            ),*/

        'activity' => array(
                                    'title' => __( 'Activity Log', 'bbconnect' ),
                                    'subs' => false,
                            ),

    ) );
    if ( isset( $_GET['tab'] ) ) {
        $active = $_GET['tab'];
    } else {
        $active = current( array_keys( $tabs ) );
    }
    $tab_nav = '';
    foreach ( $tabs as $key => $val ) {
        if ( $active == $key ) { $act_tab = ' nav-tab-active'; } else { $act_tab = ''; }
        $tab_nav .= '<a href="' . $formdes . '&tab=' . $key . '" class="nav-tab' . $act_tab . '">' . $val['title'] . '</a>';
    }

    if( $user_id == $current_user->ID ) echo '<style>#column_2{display:none;}</style>';

    do_action( 'bbconnect_pre_admin_profile' );

?>

    <div id="bbconnect" class="wrap">
    <div id="icon-users" class="icon32"><br /></div>
        <h2><?php echo bbconnect_get_username( $user_id ); ?></h2>
        <h2 class="nav-tab-wrapper"><?php echo $tab_nav; ?></h2>
        <?php bbconnect_profile_quicklinks($user_id); ?>
        <div>
        <?php
            switch( $active ) {

                case 'meta' :
?>
        <form id="user-form" class="bbconnect-form" enctype="multipart/form-data" action="<?php echo $formdes . '&tab=' . $active; ?>" autocomplete="off" method="POST">
            <div style="clear: both;">
                <p><input id="profile-submission-top" type="submit" name="edit_user_profile" value="<?php _e( 'Update!', 'bbconnect' ); ?>" class="button-primary"></p>
            </div>
<?php
                    wp_nonce_field( 'bbconnect-edit-user-nonce' );
                    do_action( 'bbconnect_pre_admin_profile_fields' );
                    bbconnect_profile_user_meta( array( 'user_id' => $user_id, 'bbconnect_cap' => $bbconnect_cap, 'action' => 'edit' ) );

                    /* THIS IS HERE FOR TEMPORARY HISTORICAL REFERENCES
                        if ( IS_PROFILE_PAGE )
                            do_action( 'show_user_profile', $profileuser );
                        else
                            do_action( 'edit_user_profile', $profileuser );
                    */
?>
            <input type="hidden" name="update" value="<?php echo $active; ?>" />
            <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
            <div style="clear: both;">
                <p><input id="profile-submission" type="submit" name="edit_user_profile" value="<?php _e( 'Update!', 'bbconnect' ); ?>" class="button-primary"></p>
            </div>
            <?php if ( !current_user_can( 'edit_users' ) ) : ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery('#bbconnect').on('click', '#profile-submission-top, #profile-submission', check_profile);
                });
            </script>
            <?php endif; ?>
        </form>
<?php
                    break;

                case 'actions' :
                    bbconnect_actions_editor( array( 'user_id' => $user_id, 'bbconnect_cap' => $bbconnect_cap, 'action' => 'edit' ) );
                    break;

                default:
                    do_action('bbconnect_admin_profile_'.$active);
                    break;
            }
        ?>
        </div>
    </div>
<?php
}


/**
 * Displays the profile fields for both the public and private views.
 *
 * @since 1.0.0
 *
 * @param arr $user_id optional. User id integer -- if not present, the function won't
 * attempt to fill in the data.
 *
 * @return html formatted profile fields.
 */
function bbconnect_profile_user_meta( $args = '' ) {

    // SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
    $defaults = array(
                    'user_id' => '',
                    'bbconnect_cap' => '',
                    'group_override' => false,
                    'action' => false,
                    'bbconnect_user_meta' => get_option( '_bbconnect_user_meta' ),
                    'post_arr' => false,
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    // EXAMINE THE DISPLAY GRID
    if ( isset( $bbconnect_user_meta['column_2'] ) && !empty( $bbconnect_user_meta['column_2'] ) ) {
        $column_two = true;
        $column_one = '';
    } else {
        $column_one = ' style="width: 99%;"';
    }

    // IF WE HAVE POST VARS, FLATTEN THE ARRAY FOR PROCESSING
    if ( false != $post_arr ) {
        foreach ( $post_arr as $key => $val ) {
            if ( 'bbconnect_user_meta' == $key ) {
                foreach ( $val as $pkey => $pval )
                    $post_vars[$pkey] = $pval;
            } else {
                $post_vars[$key] = $val;
            }
        }
    }

    ?>
    <div class="bbconnect-fields">
        <div id="column_1_holder"<?php echo $column_one; ?>>
            <ul id="column_1">
            <?php
                foreach ( $bbconnect_user_meta['column_1'] as $key => $value ) {

                    // GET THE OPTION
                    $user_meta = bbconnect_get_option( $value );

                    $args = array(
                                    'meta' => $user_meta,
                                    'bbconnect_cap' => $bbconnect_cap,
                                    'group_override' => $group_override,
                                    'action' => $action
                                );
                    $hide_meta = bbconnect_hide_meta( $args );
                    if ( $hide_meta )
                        continue;

                    $post_val = false;
                    if ( isset( $post_vars ) ) {
                        // FOR SECTIONS & GROUPS
                        if ( in_array( $user_meta['options']['field_type'], array( 'group', 'section' ) ) ) {
                            $post_val = $post_vars;
                        } else if ( isset( $post_vars[$user_meta['meta_key']] ) ) {
                            $post_val = $post_vars[$user_meta['meta_key']];
                        }
                    }

                    bbconnect_get_field( array( 'meta' => $user_meta, 'id' => $user_id, 'bbconnect_cap' => $bbconnect_cap, 'action' => $action, 'type' => 'user', 'post_val' => $post_val ) );
                }
            ?>
            </ul>
        </div>

        <?php if ( isset( $column_two ) ) { ?>

        <div id="column_2_holder">
            <ul id="column_2">
            <?php
                foreach ( $bbconnect_user_meta['column_2'] as $key => $value ) {

                    // GET THE OPTION
                    $user_meta = bbconnect_get_option( $value );

                    $args = array(
                                    'meta' => $user_meta,
                                    'bbconnect_cap' => $bbconnect_cap,
                                    'group_override' => $group_override,
                                    'action' => $action
                                );
                    $hide_meta = bbconnect_hide_meta( $args );
                    if ( $hide_meta )
                        continue;

                    $post_val = false;
                    if ( isset( $post_vars ) ) {
                        // FOR SECTIONS & GROUPS
                        if ( in_array( $user_meta['options']['field_type'], array( 'group', 'section' ) ) ) {
                            $post_val = $post_vars;
                        } else if ( isset( $post_vars[$user_meta['meta_key']] ) ) {
                            $post_val = $post_vars[$user_meta['meta_key']];
                        }
                    }

                    bbconnect_get_field( array( 'meta' => $user_meta, 'id' => $user_id, 'bbconnect_cap' => $bbconnect_cap, 'action' => $action, 'type' => 'user', 'post_val' => $post_val ) );
                }
            ?>
            </ul>
        </div>

        <?php } ?>

    </div>
        <?php

}


function bbconnect_actions_editor( $args = null ) {

    global $pagenow, $current_user;

    // SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
    $defaults = array(
                    'user_id' => false,
                    'bbconnect_cap' => false,
                    'action' => false,
                    'list_only' => false,
                    'action_only' => false,
                    'embed' => false,
                    'embed_id' => false,
                    'cid' => 'post_content',
                    'get_actions' => 'bbconnect_get_user_actions',
                    'type' => 'user',
                    'ok_edit' => false,
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    if ( false == $bbconnect_cap || false == $action )
        return false;

    if ( false != $embed && false == $embed_id )
        return false;

    //if ( isset( $_GET['user_id'] ) ) && $user_id == false ) {

    if ( $action_only ) {
        if ( is_int( $action_only ) ) {
            $one_style = ' style="width: '.(96 - $action_only).'%;"';
            $two_style = ' style="width: '.$action_only.'%;"';
        } else {
            $one_style = ' style="display: none;"';
            $two_style = ' style="width: 96%;"';
        }
    } else if ( $list_only ) {
        if ( is_int( $list_only ) ) {
            $two_style = ' style="width: '.(96 - $list_only).'%;"';
            $one_style = ' style="width: '.$list_only.'%;"';
        } else {
            $two_style = ' style="display: none;"';
            $one_style = ' style="width: 96%;"';
        }
    } else {
        $two_style = '';
        $one_style = '';
    }

    // CHECK TO SEE IF THE CURRENT USER CAN EDIT THE TARGET USER
    if ( false == $ok_edit )
        $ok_edit = bbconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) );

    $bbconnect_actions = call_user_func( $get_actions );

    if ( !empty( $bbconnect_actions ) ) {

        // PULL ALL POSSIBLE ACTIONS AND COMPILE THE LAUNCHER
        $bbconnect_action_types = array();
        $buttons = '';
        $bbconnect_action_launcher = array();

        $args = array(
                'hide_empty' => false,
//                 'parent' => 0,
        );
        $terms = get_terms('bb_note_type', $args);

        $default_terms = array();
        foreach ( $terms as $term ) {
            if ($term->parent == 0) {
                $bbconnect_action_launcher_left[$term->slug] = $term->name;
                $bbconnect_action_launcher_right[$term->slug] = $term->name;
                if ($term->slug == 'system') {
                    $system_term = $term->term_id;
                }
            } else {
                $child_actions[$term->parent][] = $term;
            }

            // PULL THE ARRAY FOR QUERYING
            $bbconnect_action_types[] = $term->slug;

            $is_default = (bool)get_tax_meta($term->term_id, 'initially_displayed');
            if ($is_default) {
                $default_terms[] = $term->term_id;
            }
        }

        // Now add the children of System to the filter list immediately after System
        $idx = array_search('system', array_keys($bbconnect_action_launcher_left));
        $end_actions = array_splice($bbconnect_action_launcher_left, $idx+1);
        foreach ($child_actions[$system_term] as $system_child) {
            $bbconnect_action_launcher_left[$system_child->slug] = ' - '.$system_child->name;
        }
        $bbconnect_action_launcher_left += $end_actions;

        ?>
        <div class="bbconnect-fields">

        <?php if ( !empty( $bbconnect_action_types ) ) { ?>

        <form id="user-form" class="bbconnectpanels-form" method="post" action="">

            <div class="column_holder actions-history-holder"<?php echo $one_style; ?>>
                <ul>
                    <li>
                        <span class="bbconnect-field">
                            <h3><?php _e( 'Notes', 'bbconnect' ); ?></h3>
                            <div style="text-align: right;">
                                <select class="profile-actions-filter">
                                    <option value="default"><?php _e( 'Default', 'bbconnect' ); ?></option>
                                    <option value="all"><?php _e( 'All', 'bbconnect' ); ?></option>
                                    <?php
                                        $bbconnect_ae_launcher_left = apply_filters( 'bbconnect_ae_launcher_left', $bbconnect_action_launcher_left, $embed_id );
                                        foreach ( $bbconnect_ae_launcher_left as $key => $val )
                                            echo '<option value="' . $key . '">' . $val . '</option>';
                                    ?>
                                </select>
                            </div>
                        </span>
                    </li>
                </ul>
                <ul class="bbconnect actions-history-list">
                <?php
                    if ( false != $user_id ) {

                        // MAKE THE QUERY
                        $args = array(
                                'post_type' => 'bb_note',
                                'author' => $user_id,
                                'posts_per_page' => -1,
                                'post_status' => array('publish', 'private'),
                        );
                        $action_query = new WP_Query($args);

                        // LOOP THE ITEMS
                        while ( $action_query->have_posts() ) : $action_query->the_post();
                            $visible = false;
                            $class = array();
                            $note_types = wp_get_post_terms($action_query->post->ID, 'bb_note_type');
                            foreach ($note_types as $note_type) {
                                $class[] = $note_type->slug;
                                if (!$visible && in_array($note_type->term_id, $default_terms)) {
                                    $visible = true;
                                    $class[] = 'default';
                                }
                            }
                            bbconnect_profile_action_item( array(
                                                                'act' => $action_query->post,
                                                                'type' => $type,
                                                                'user' => $user_id,
                                                                'class' => $class,
                                                                'ok_edit' => $ok_edit,
                                                                'action' => $action,
                                                                'bbconnect_cap' => $bbconnect_cap,
                                                                'visible' => $visible,
                            ) );
                        endwhile;
                    } else {
                        do_action( 'bbconnect_ae_query', $bbconnect_action_types, $embed_id );
                    }
                ?>
                </ul>
            </div>

            <div class="column_holder actions-editor"<?php echo $two_style; ?>>
                <ul>
                    <li>
                        <span class="bbconnect-field">
                            <h3>&nbsp;</h3>
                        <?php
                            // IF THE USER CAN EDIT THE TARGET USER, START THE UI
                            if ( false != $ok_edit || 'bulk-edit' == $action ) {

                                if ( ! bbconnect_is_panels() && 'user' == $bbconnect_cap ) {
                                    // IF THEY ARE A USER ON THE BACKEND, SHOW THEM NOTHING
                                } else {
                                    $bbconnect_ae_launcher_right = apply_filters( 'bbconnect_ae_launcher_right', $bbconnect_action_launcher_right, $embed_id, $action, $bbconnect_cap );

                        ?>
                            <div style="text-align: right;">
                                <?php
                                    if ( 'bulk-edit' == $action ) {
                                        echo '<a class="rui off" title="actions-bulk-edit">Select a type to edit: </a>';
                                        $e_title = __( 'Edit Actions', 'bbconnect' );
                                    } else {
                                        $e_title = __( 'Create or Edit Actions', 'bbconnect' );
                                    }
                                ?>
                                <select class="actions-launcher <?php echo $action; ?> <?php echo $type; ?>" title="<?php echo $user_id; ?>">
                                    <option value=""><?php echo $e_title; ?></option>
                                    <?php
                                        foreach ( $bbconnect_ae_launcher_right as $key => $val ) {
                                            if ( $pagenow == 'admin-ajax.php' ) {
                                                echo '<option value="rel=bbcContent&amp;bbc_type=' . $key . '&amp;uid=' . $user_id. '">' . $val . '</option>';
                                            } else {
                                                echo '<option value="' . $key . '">' . $val . '</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php
                                }
                            }
                        ?>
                        </span>
                    </li>
                </ul>
                <ul>
                    <li>
                        <div style="display: block;">
                            <div id="<?php echo $cid; ?>_viewer" class="bbconnect-viewer inside">
                                <div style="display:none;">
                                <?php
                                    if ( $pagenow != 'admin-ajax.php' ) {
                                        if ( '3.9' > get_bloginfo( 'version' ) ) {
                                            wp_editor( '', $cid, array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );
                                        }
                                    }
                                ?>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </form>
        <?php } ?>
        </div>
        <?php
    }
}

/**
 * function description
 *
 * @author your name
 * @param $act obj. Post object.
 * @param $type str. A string. Either 'user' or 'post' as a reference for where this editor is active.
 * @param $new bool. Whether or not this is a new object.
 * @return return type
 */
function bbconnect_profile_action_item( $args = null ) {

    global $pagenow, $current_user;

    // SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
    $defaults = array(
                    'act' => false,
                    'type' => 'user',
                    'new' => false,
                    'class' => array(),
                    'ok_edit' => false,
                    'action' => 'view',
                    'bbconnect_cap' => 'user',
                    'visible' => true,
                );

    // PARSE THE INCOMING ARGS
    $args = wp_parse_args( $args, $defaults );

    // EXTRACT THE VARIABLES
    extract( $args, EXTR_SKIP );

    if ( !is_object( $act ) )
        return false;

        if ($new) {
            $class[] = 'new-post';
        }

        $class = implode( ' ', $class );

        $style = array();
        if ($new || !$visible) {
            $style[] = 'display: none;';
        }
        $style = implode(' ', $style);

        echo '<li style="'.$style.'" class="' . $act->post_type . ' tn-wrapper ' .$class. '">';
    ?>
        <div class="bbconnect-view" rel="<?php echo $act->ID; ?>">
        <span class="bbconnect-field">
            <a style="display:inline-block;width:100%;" class="<?php echo implode( ' ', apply_filters( 'bbconnect_ai_class_filter', array( 'bbconnect-icon', $act->post_type ), $act ) ); ?>"><span class="bb_bbc_date"><?php echo date( 'd F Y', strtotime( $act->post_date ) ); ?></span><?php echo $act->post_title; ?></a>
            <div style="display:inline-block;width:100%;" ><?php echo substr( apply_filters( 'the_content', stripslashes( $act->post_content ) ), 0, 255 ); if(strlen(apply_filters( 'the_content', stripslashes( $act->post_content ) ) ) > 255 ) echo '...'; ?></div>
        </span>
        </div>
        <?php
            if ( $new ) {
                echo '<div title="post-' . $act->ID . '" style="display:none;">';
            } else {
                echo '<div id="post-' . $act->ID . '" style="display:none;">';
            }

            if ( defined( 'DOING_AJAX') && DOING_AJAX ) {
                if ('bb_note' == $act->post_type) {
                    $title = '<h2>'.$act->post_title.'</h2>';
                } else {
                    $title = '<h2><a href="'.get_permalink( $act->ID ).'">'.$act->post_title.'</a></h2>';
                }
            } else {
                $title = '<h2>'.$act->post_title.'</h2>';
            }
            $date = '<div class="action-date">'.date( 'd F Y', strtotime( $act->post_date ) ).'</div>';
            $content = apply_filters( 'the_content', stripslashes( $act->post_content ) );

            $createdby = '';
            $creator_id = get_post_meta($act->ID, '_bbc_agent', true);
            if (!empty($creator_id)) {
                $creator = new WP_User($creator_id);
                $createdby = $creator->display_name;
            } else {
                $createdby = 'System';
            }

            $title = apply_filters( 'bbconnect_title_filter', $title, $act );
            $date = apply_filters( 'bbconnect_date_filter', $date, $act );
            $content = apply_filters( 'bbconnect_content_filter', $content, $act );
                // PLUGINS CAN UNSET THE CONTENT, OVERRIDING THE DEFAULT EXCERPT DISPLAY.

                // IF THE FILTER WAS APPLIED, THEN THE DISPLAY IS OPEN TO FULLY DISPLAYING
                // A COMPLETE POST OBJECT IN ALL IT'S GLORY! NICE IF YOU HAVE PARTICULAR
                /* POST FORMATS IN MIND FOR DISPLAY.
                if ( false == $content ) {
                    do_action( 'bbconnect_content_output', $act );
                } else if ( is_object( $content ) ) {
                    echo ;
                }*/
            echo $title;
            echo $date;
            printf(__('Note Added By: %s', 'bbconnect'), $createdby);
            echo $content;

            $bt = __( 'Edit', 'bbconnect' );

            // RETURN THE RIGHT LINK FOR THE ENVIRONMENT
            if ( ! bbconnect_is_panels() ) {
                if ( 'user' == $bbconnect_cap ) {
                    $button = '';
                } else {
                    $button = '<a class="button-primary profile-actions-edit '.$type.'" title="edit-'.$act->ID.'">'.$bt.'</a>';
                }
            } else {
                $button = '<a class="button bbconnectpanels-toggle" href="' . home_url( '/bbconnect/?rel=bbcContent&amp;bbc_type='.$act->post_type.'&amp;uid='.$act->post_author.'&amp;pid='.$act->ID ) . '">'.$bt.'</a>';
            }

            if ( false != $ok_edit ) {
                $inline_button = apply_filters( 'bbconnect_inline_action_button', array( 'edit' => $button ), $act, $type, $action, $bbconnect_cap );
                echo '<div class="tright">';
                if ( is_array( $inline_button ) ) {
                    echo implode( ' ', $inline_button );
                } else {
                    echo $inline_button;
                }
                echo '</div>';
            }
        ?>
        </div>
    </li>
    <?php
}


function bbconnect_get_username( $u = false, $display = false ) {
    if ( empty( $u ) ) return false;
    if ( !is_object( $u ) ) {
        $u = get_user_by( 'id', $u );
    }

    $default = get_option( '_bbconnect_get_username' );
    if ( empty( $display ) ) {
        $display = $default;
    }

    switch ( $display ) {

        case 'fullname' :
            $output = $u->first_name . ' ' . $u->last_name;
        break;

        case 'organization' :
            $output = $u->organization;
        break;

        case 'username' :
        default :
            $output = $u->display_name;
        break;

    }

    return $output;

}
