<?php

/**
 * Simple array to define the actions that directly affect users.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return arr. Standardized array of action data.
 */

function bbconnect_user_actions() {
    return apply_filters( 'bbconnect_user_actions', array(
            'bb_note' => array(
                                'source' => 'bbconnect',
                                'type' => 'bb_note',
                                'single' => __( 'Note', 'bbconnect' ),
                                'plural' => __( 'Notes', 'bbconnect' ),
                                'class' => 'origin',
                                'options' => array(
                                                    'admin' => true,
                                                    'user' => false,
                                                    'public' => false,
                                                    'reports' => true,
                                                    'choices' => false
                                )
            ),
    ) );
}


/**
 * Retrieves the user actions before registering them as a CPT.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function bbconnect_get_init_user_actions() {
    return apply_filters( 'bbconnect_get_init_user_actions', bbconnect_user_actions() );
}

/**
 * Retrieves the user actions for general use within the system.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */

function bbconnect_get_user_actions() {
    return apply_filters( 'bbconnect_get_user_actions', bbconnect_get_init_user_actions() );
}


/**
 * Removes particular post types from the user-admin panel for user actions.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters but anticipates the slug of the desired post type to exclude.
 *
 * @return null If you see your actions, it didn't work. :)
 */

function bbconnect_exclude_user_actions() {
    return apply_filters( 'bbconnect_exclude_user_actions', array() );
}




/**
 * Instantiates a WordPress Custom Post Type (CPT) as an Action on INIT.
 *
 * @since 1.0.2
 *
 * @param This function accepts no parameters.
 *
 * @return null If you don't see your actions, it didn't work. :)
 */
function bbconnect_init_user_actions() {

      $bbconnect_user_actions = bbconnect_get_init_user_actions();

    foreach ( $bbconnect_user_actions as $action ) {

        if ( isset( $action['exception'] ) &&  false != $action['exception'] )
            continue;

        $cptype = $action['type'];
        $cpsingle = $action['single'];
        $cpplural = $action['plural'];

        $labels = array();
        $labels['name'] = $cpplural;
        $labels['singular_name'] = $cpsingle;
        $labels['add_new'] = 'New '.$cpsingle;
        $labels['add_new_item'] = 'Add New '.$cpsingle;
        $labels['edit_item'] = 'Edit '.$cpsingle;
        $labels['new_item'] = 'New '.$cpsingle;
        $labels['view_item'] = 'View '.$cpplural;
        $labels['search_items'] = 'Search '.$cpplural;
        $labels['not_found'] = 'No '.$cpplural.' found';
        $labels['not_found_in_trash'] = 'No '.$cpplural.' found in Trash';
        $labels['parent_item_colon'] = '';

          // THESE WILL NEED TO BE OPTIONS
          // FOR NOW, WE'RE SETTING THEM
        $args = array();
        //$args['label'] = null;
        $args['labels'] = $labels;
        $args['description'] = '';
        $args['public'] = false;
        $args['publicly_queryable'] = false;
        $args['exclude_from_search'] = true;
        $args['show_ui'] = true;
        $args['show_in_menu'] = false;
        $args['menu_position'] = null;
        $args['menu_icon'] = null;
        $args['capability_type'] = 'post';
        $args['capabilities'] = array(
                'publish_posts'         => 'manage_'.$cptype,
                'edit_posts'             => 'manage_'.$cptype,
                'edit_others_posts'     => 'manage_'.$cptype,
                'delete_posts'             => 'manage_'.$cptype,
                'delete_others_posts'    => 'manage_'.$cptype,
                'read_private_posts'    => 'manage_'.$cptype,
                'edit_post'             => 'manage_'.$cptype,
                'delete_post'             => 'manage_'.$cptype,
                'read_post'             => 'manage_'.$cptype
            );
        $args['hierarchical'] = false;
        $args['register_meta_box_cb'] = 'bbconnect_user_actions_meta_box_cb';
        $args['has_archive'] = false;
        $args['rewrite'] = false; // array( 'slug' => $cploc . '/'.( $cpplural ) );
        $args['query_var'] = true;
        $args['can_export'] = true;
        $args['show_in_nav_menus'] = false;
        $args['supports'] = array( 'title', 'editor' );

        // THESE ARE THE WP OPTIONS
        // 'title','editor','excerpt','trackbacks','comments','thumbnail','author'

        register_post_type( $cptype, $args );

    }

}


/**
 * Generic nonce field for all Connexions data.
 *
 * @since 1.0.2
 * @param arr. The Connexions User Actions Array.
 * @return arr. The filtered Actions Array.
 */

function bbconnect_user_actions_nonce_field() {
    global $post;
    wp_nonce_field( 'bbconnect-' . $post->post_type.'-meta', 'bbconnect-' . $post->post_type.'-nonce' );
}


/**
 * Pushes the user actions to Connexions.
 *
 * @since 1.0.2
 * @param arr. The Connexions User Actions Array.
 * @return arr. The filtered Actions Array.
 */

function bbconnect_push_user_actions( $bbconnect_get_user_actions ) {

    $bbconnect_post_types = get_option( '_bbconnect_post_types' );

    if ( empty( $bbconnect_post_types ) )
        return $bbconnect_get_user_actions;

    $bbconnect_get_user_actions = $bbconnect_post_types;

    return $bbconnect_get_user_actions;
}

/**
 * Loads the correct fields for the selected actions under Profile > Actions.
 *
 * @since 1.0.0
 * @param $data str. The type of action being requested.
 * @return html. The fields.
 */

function bbconnect_get_post_to_edit() {

    if ( ! wp_verify_nonce( $_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce' ) )
        die (  __( 'There was an issue loading the data you requested. Please refresh the page and try again.', 'bbconnect' ) );

    if ( isset( $_POST['data'] ) ) {

        $cid = $_POST['cid'];
        $type = $_POST['type'];
        $post_val = array();
        global $post;

        // WE'RE EDITING AN EXISTING FILE
        if ( false !== strpos( $_POST['data'], 'edit-' ) ) {
            $post_id = (int) substr( $_POST['data'], 5 );
            $post = get_post( $post_id );
            $post_author = $post->post_author;
            $post_type = $post->post_type;
            $action = 'edit';

            if ($post_type == 'bb_note') {
                $note_types = wp_get_post_terms($post_id, 'bb_note_type');
                foreach ($note_types as $note_type) {
                    if ($note_type->parent == 0) {
                        $parent_term = $note_type;
                    } else {
                        $child_term = $note_type;
                    }
                }
            }
        } else {
            if ( 'user' == $type ) {
                $bbconnect_actions = bbconnect_get_user_actions();
                $post_type = 'bb_note';
                if (empty($_POST['data'])) {
                    echo '';
                    die();
                }
            } else {
                $bbconnect_actions = bbconnect_get_post_actions();
                $post_type = false;
                $post_type = $_POST['data'];
                if ( false == $post_type ) {
                    echo '';
                    die();
                }
            }

            $post = get_default_post_to_edit( $post_type, true );
            $post_author = $_POST['uid'];
            $action = $_POST['actung'];
        }

        // SET THE NONCE
        if ( 'user' == $type ) {
            bbconnect_user_actions_nonce_field();
        } else {
            bbconnect_post_actions_nonce_field();
        }

        $post_fields = array(
            array( 'meta' => array(
                                    'source' => 'wpr',
                                    'meta_key' => 'post_title',
                                    'name' => __( 'Title', 'bbconnect' ),
                                    'help' =>'',
                                    'options' => array(
                                                        'field_type' => 'text',
                                                        'req' => true,
                                                        'public' => false,
                                                        'choices' => false
                                    )
            ) ),

            array( 'meta' => array(
                                    'source' => 'wpr',
                                    'meta_key' => 'post_date',
                                    'name' => __( 'Date', 'bbconnect' ),
                                    'help' =>'',
                                    'options' => array(
                                                        'field_type' => 'date',
                                                        'req' => true,
                                                        'public' => false,
                                                        'choices' => false
                                    )
            ) ),
        );

        if ('user' == $type) {
            if (!empty($parent_term)) { // Editing existing note
                $post_fields[] = array(
                        'post_val' => $parent_term->slug,
                        'meta' => array(
                            'source' => 'tax',
                            'meta_key' => 'bb_note_type_parent',
                            'name' => __( 'Type', 'bbconnect' ),
                            'help' =>'',
                            'options' => array(
//                                     'readonly' => true,
                                    'field_type' => 'select',
                                    'req' => true,
                                    'public' => true,
                                    'choices' => array(
                                            $parent_term->slug => $parent_term->name,
                                    ),
                            )
                      )
                );
                $post_fields[] = array(
                        'post_val' => $child_term->slug,
                        'meta' => array(
                            'source' => 'tax',
                            'meta_key' => 'bb_note_type',
                            'name' => __( '', 'bbconnect' ),
                            'help' =>'',
                            'options' => array(
//                                     'readonly' => true,
                                    'field_type' => 'select',
                                    'req' => false,
                                    'public' => true,
                                    'choices' => array(
                                            $child_term->slug => $child_term->name,
                                    ),
                            )
                    )
                );
            } else { // New note
                $parent_term = get_term_by('slug', $_POST['data'], 'bb_note_type');
                $terms = get_terms('bb_note_type', array('hide_empty' => false, 'parent' => $parent_term->term_id));
                $choices = array();
                foreach ($terms as $term) {
                    $choices[$term->slug] = $term->name;
                }
                $post_fields[] = array( 'meta' => array(
                        'source' => 'tax',
                        'meta_key' => 'bb_note_type',
                        'name' => __( 'Type', 'bbconnect' ),
                        'help' =>'',
                        'options' => array(
                                'field_type' => 'select',
                                'req' => true,
                                'public' => false,
                                'choices' => $choices,
                        )
                ) );
                $post_fields[] = array( 'meta' => array(
                        'source' => 'tax',
                        'meta_key' => 'bb_note_type_parent',
                        'name' => __( '', 'bbconnect' ),
                        'help' =>'',
                        'options' => array(
                                'field_type' => 'hidden',
                                'req' => false,
                                'public' => false,
                                'choices' => $parent_term->slug,
                        )
                ) );
            }
        }

        foreach ( $post_fields as $field ) {
            $meta_key = $field['meta']['meta_key'];
            $field['type'] = 'post';
            $field['action'] = $action;
            $field['id'] = $post->ID;
            $field['swap_name'] = $meta_key;

            if ( isset( $post->{$meta_key} ) ) {
                $field['post_val'] = $post->{$meta_key};
            }
            echo '<p><ul style="display: block; float: none;">';
            bbconnect_get_field( $field );
            echo '</ul></p>';
        }

        if ( 'bulk-edit' == $action ) {
            echo '<ul><li class="meta-item"><span class="bbconnect-label">';
            echo '<a class="rui off" title="'.$cid.'bulk-edit">Enable Text</a>';
            echo '</span><span class="bbconnect-field">';
        }

        echo '<div style="width: 90%;padding: .3em;margin: .2em 0;">&nbsp;</div>';
        //echo '<p>'. __( 'Title', 'bbconnect' ) .'<br /><input type="text" name="post_title" class="regular-text" value="'.$post->post_title.'" /></p>';
        //echo '<p>'. __( 'Date', 'bbconnect' ) .'<br /><input type="text" class="bbconnect-date" name="post_date" class="regular-text" value="'.$post->post_date.'" /></p>';
        if (preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT'])) {
            wp_editor( stripslashes( $post->post_content ), $cid, array( 'tinymce' => false, 'textarea_name' => 'post_content', 'teeny' => true, 'quicktags' => true ) );
        } else {
            wp_editor( stripslashes( $post->post_content ), $cid, array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );
        }

        if ( 'bulk-edit' == $action ) {
            echo '</span></li></ul>';
        }

        // SET THE META
        if ( 'user' == $type ) {
            bbconnect_user_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => bbconnect_get_user_actions_meta(), 'action' => $action, 'post_val' => $post_val ) );
        } else {
            bbconnect_post_actions_meta_fields( array( 'post_id' => $post->ID, 'fields' => bbconnect_get_post_actions_meta(), 'action' => $action, 'post_val' => $post_val ) );
        }

        ?>
        <input type="hidden" name="post_ID" value="<?php echo $post->ID; ?>" />
        <input type="hidden" name="post_status" value="publish" />
        <input type="hidden" name="post_author" value="<?php echo $post_author; ?>" />
        <input type="hidden" name="post_type" value="<?php echo $post_type; ?>" />
        <?php
        $inline_button = apply_filters( 'bbconnect_inline_do_action_button', array(
                '<input type="submit" class="bbconnect-actions-save button-primary '.$type.'" name="save" value="'.__( 'Save', 'bbconnect' ).'" />',
            ), $post_type, $type, $action
        );
        echo '<div class="tright">';
        echo implode( ' ', $inline_button );
        echo '</div>';

    } else {
        echo 'error';
    }
    if ( '3.9' <= get_bloginfo( 'version' ) ) {
        _WP_Editors::enqueue_scripts();
        //print_footer_scripts();
        _WP_Editors::editor_js();
        echo '<script src="'.admin_url( 'js/editor.js' ).'" />';
    }
    die();

}


/**
 * Saves for the selected actions under Profile > Actions.
 *
 * @since 1.0.0
 * @param $data arr. The POST data.
 * @return html. The content and miscellaneous followups.
 */

function bbconnect_save_new_post() {

    if ( ! wp_verify_nonce( $_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce' ) )
        die (  __( 'Terribly sorry.', 'bbconnect' ) );

    if ( isset( $_POST['data'] ) ) {

        parse_str( $_POST['data'], $postarr );
        $sid = $_POST['sid'];
        $type = $_POST['type'];

        if ( isset( $postarr['post_ID'] ) ) {
            $postarr['ID'] = $postarr['post_ID'];
            //unset( $postarr['post_ID'] );
        }

        $pid = wp_insert_post( $postarr );
//         $postarr['id'] = $pid;
        bbconnect_save_action_meta( array( 'post_data' => $postarr, 'override' => true ) );
        $act = get_post( $pid );
        $class = apply_filters( 'bbconnect_save_new_class', array(), $act );
        bbconnect_profile_action_item( array(
                                            'act' => $act,
                                            'type' => $type,
                                            'new' => true,
                                            'class' => $class,
                                            'ok_edit' => true,
                                            'action' => 'edit',
                                            'bbconnect_cap' => 'admin'
        ) );
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function () {
            // IF THE ELEMENT EXISTS, REMOVE IT & THE PARENT
            if ( jQuery('#post-<?php echo $pid; ?>').length != 0 ) {
                jQuery('#post-<?php echo $pid; ?>').parent('li').remove();
            }

            // CLONE THE NEW ELEMENT
            jQuery('.new-post').clone().prependTo('#<?php echo $sid; ?> .actions-history-list');

            // UPDATE AND REMOVE THE NEW-POST IDENTIFIER
            jQuery('#<?php echo $sid; ?> .actions-history-list .new-post').each(function(){
                jQuery(this).find('div[title="post-<?php echo $pid; ?>"]').attr('id','post-<?php echo $pid; ?>').attr('title','');
                jQuery(this).removeClass('new-post').fadeIn('fast');
            });

            // UPGRADE THE ORIGINAL
            var newparent = jQuery('.new-post').parent('.bbconnect-viewer').attr('id');
            jQuery('#'+newparent+' .new-post').each(function(){
                jQuery(this).find('div[title="post-<?php echo $pid; ?>"]').attr('id','new-post-<?php echo $pid; ?>').attr('title','');
                jQuery('#new-post-<?php echo $pid; ?>').prependTo('#'+newparent).fadeIn('fast');
                jQuery(this).remove();
            });

        });
        </script>
    <?php
    } else {
        echo 'error';
    }

    die();

}

/**
 * Excludes specific actions from BBCContent consideration.
 *
 * @since 1.0.2
 *
 * @param arr. The Connexions User Actions Array.
 *
 * @return arr. The filtered Actions Array.
 */

function bbconnect_exclude_accepted_post_types( $bbccontent_accepted_post_types ) {

    $bbconnect_user_actions = bbconnect_user_actions();

    foreach ( $bbconnect_user_actions as $key => $value )
        array_push( $bbccontent_accepted_post_types, $value['type'] );

    return $bbccontent_accepted_post_types;
}

function bbconnect_exclude_restricted_post_types( $bbccontent_restricted_post_types ) {

    $bbconnect_user_actions = bbconnect_user_actions();

    foreach ( $bbconnect_user_actions as $key => $value )
        array_push( $bbccontent_restricted_post_types, $value['type'] );

    return $bbccontent_restricted_post_types;
}


function bbconnect_modal_action(){

    switch ( $_GET['action'] ) {

        case 'test' :
            global $pagenow;
            echo 'howdy, user# ' . $_GET['ID'] . ' ' . $pagenow;
            //bbconnect_actions_editor( array( 'user_id' => $_GET['ID'], 'bbconnect_cap' => 'admin', 'action' => 'edit', 'embed' => true, 'embed_id' => 'new-action-mini' ) );

        break;

        default :
            echo 'hello world!';
            wp_editor( '', 'post_content', array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );

        break;

    }

}

function bbconnect_add_nodes( $wp_admin_bar ) {
    global $current_user;
    $args = array(
          'id' => 'new-action',
          'parent' => 'new-content',
          'title' => __( 'Connexions Action', 'bbconnect' ),
          'href' => admin_url( '/users.php?page=bbconnect_modal_action&amp;action=&amp;ID='.$current_user->ID.'&amp;TB_iframe=true&amp;height=300&amp;width=900' ),
          'meta' => array( 'html' => "<script>jQuery('#wp-admin-bar-new-action a').addClass('thickbox');</script>" )
    );

    $wp_admin_bar->add_node($args);

}

// REMOVE THE WORDPRESS ADMIN MENU FOR NOTES AND SUCH
function bbconnect_action_minified() {
    global $pagenow;

    if ( is_admin() ) {
        if ( isset( $_GET['page'] ) && 'bbconnect_modal_action' == $_GET['page'] ) {
            //add_action( 'admin_head', 'bbconnect_action_minify' );
        }
    }

}

function bbconnect_action_minify() {
    if ( isset( $_GET['ID'] ) ) {
        echo "<style>\r\n";
        echo "#adminmenuback, #adminmenuwrap, #wphead, #footer, #wpfooter, #wpadminbar { display: none; }\r\n";
        echo "div { margin: 0 !important; padding: 0 !important; max-width: 180px !important; min-width: 180px !important; }\r\n";
        echo "body.wp-admin { min-width: 180px !important; }\r\n";
        echo "#wpbody-content { width: 250px; padding: 10px !important; text-align: center; }\r\n";
        echo "</style>\r\n";
    }

}


function bbconnect_insert_user_note($author_id, $title, $description, $note_type = array('type' => 'system', 'subtype' => 'miscellaneous'), $receipt_number = '') {
    $terms = bbconnect_get_note_types($note_type);

    $post = array(
            'post_title'    => $title,
            'post_status'   => 'publish',
            'post_type'     => 'bb_note',
            'post_content'  => $description,
            'post_author'   => $author_id,
    );

    $post_id = wp_insert_post($post);
    wp_set_object_terms($post_id, array($terms['type']->term_id, $terms['subtype']->term_id), 'bb_note_type');

    if ($post_id) {
        if ($receipt_number) {
            add_post_meta($post_id, 'note_receipt_number', $receipt_number);
        }
        if (is_user_logged_in()) {
            add_post_meta($post_id, '_bbc_agent', get_current_user_id());
        }
    }

    return $post_id;
}

function bbconnect_get_note_types($note_type) {
    if (!is_array($note_type)) {
        $note_type = array('subtype' => $note_type);
    }
    if (empty($note_type['subtype'])) {
        return false;
    }

    $terms = array();

    if (is_numeric($note_type['subtype'])) {
        $terms['subtype'] = get_term_by('id', $note_type['subtype'], 'bb_note_type');
    } else {
        $terms['subtype'] = get_term_by('slug', $note_type['subtype'], 'bb_note_type');
    }

    if (empty($note_type['type'])) {
        $terms['type'] = get_term_by('id', $terms['subtype']->parent, 'bb_note_type');
    } elseif (is_numeric($note_type['type'])) {
        $terms['type'] = get_term_by('id', $note_type['type'], 'bb_note_type');
    } else {
        $terms['type'] = get_term_by('slug', $note_type['type'], 'bb_note_type');
    }

    return $terms;
}
