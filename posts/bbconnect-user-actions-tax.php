<?php
add_post_type_support( 'bb_note', 'page-attributes' );
function tax_bb_note_type() {
    $labels = array(
        'name'              => _x( 'Note Type', 'taxonomy general name' ),
        'singular_name'     => _x( 'Note Type', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Note Types' ),
        'all_items'         => __( 'All Note Types' ),
        'parent_item'       => __( 'Parent Note Type' ),
        'parent_item_colon' => __( 'Parent Note Type:' ),
        'edit_item'         => __( 'Edit Note Type' ),
        'update_item'       => __( 'Update Note Type' ),
        'add_new_item'      => __( 'Add New Note Type' ),
        'new_item_name'     => __( 'New Note Type' ),
        'menu_name'         => __( 'Note Types' ),
    );
    $args = array(
        'labels'                => $labels,
        'hierarchical'          => true, // true = categories & false = tags
        'public'                => true,
        'show_ui'               => true,
        'show_tagcloud'         => true,
        'show_in_nav_menus'     => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_generic_term_count',
        'query_var'             => 'bb_note_type',
        'rewrite'               => array( 'slug' => 'bb_note_type' ),
    );
    register_taxonomy( 'bb_note_type', array( 'bb_note' ), $args );
}
add_action( 'init', 'tax_bb_note_type', 0 );

add_action('admin_menu', 'bbconnect_register_note_types_page');
function bbconnect_register_note_types_page() {
	add_submenu_page( 'users.php', 'Note Types', 'Note Types', 'list_users', 'edit-tags.php?taxonomy=bb_note_type&post_type=bb_note');
}

// Add tax meta
$config = array(
        'id' => 'note_type_meta_box',          // meta box id, unique per meta box
        'title' => 'Note Type Meta',          // meta box title
        'pages' => array('bb_note_type'),        // taxonomy name, accept categories, post_tag and custom taxonomies
        'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
);
$tax_meta = new Tax_Meta_Class($config);
$tax_meta->addCheckbox('initially_displayed', array('name'=> 'Initially Displayed ', 'desc' => 'If ticked, notes of this type will be displayed by default when viewing a Contact\'s notes'));
$tax_meta->Finish();