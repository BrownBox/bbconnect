<?php
add_post_type_support( 'bb_note', 'page-attributes' );
function tax_bb_campaign() {
    $labels = array(
        'name'              => _x( 'Campaign', 'taxonomy general name' ),
        'singular_name'     => _x( 'Campaign', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Campaigns' ),
        'all_items'         => __( 'All Campaigns' ),
        'parent_item'       => __( 'Parent Campaign' ),
        'parent_item_colon' => __( 'Parent Campaign:' ),
        'edit_item'         => __( 'Edit Campaign' ),
        'update_item'       => __( 'Update Campaign' ),
        'add_new_item'      => __( 'Add New Campaign' ),
        'new_item_name'     => __( 'New Campaign' ),
        'menu_name'         => __( 'Campaigns' ),
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
        'query_var'             => 'bb_campaign',
        'rewrite'               => array( 'slug' => 'bb_campaign' ),
    );
    register_taxonomy( 'bb_campaign', array( 'bb_note', 'transaction' ), $args );
}
add_action( 'init', 'tax_bb_campaign', 0 );

add_action('admin_menu', 'bbconnect_register_campaigns_page');
function bbconnect_register_campaigns_page() {
	add_submenu_page( 'users.php', 'Campaigns', 'Campaigns', 'list_users', 'edit-tags.php?taxonomy=bb_campaign&post_type=bb_note');
}

// Add tax meta
$config = array(
        'id' => 'campaign_meta_box',          // meta box id, unique per meta box
        'title' => 'Campaign Meta',          // meta box title
        'pages' => array('bb_campaign'),        // taxonomy name, accept categories, post_tag and custom taxonomies
        'local_images' => false,          // Use local or hosted images (meta box images for add/remove)
);
$tax_meta = new Tax_Meta_Class($config);
$tax_meta->addDate('start_date', array('name'=> 'Start Date '));
$tax_meta->addText('cost', array('name'=> 'Cost ($) '));
$tax_meta->Finish();