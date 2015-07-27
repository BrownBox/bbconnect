<?php

function bbconnect_inbounds(){

    //Create an instance of our package class...
    $inbounds = new BBC_Inbound_Table();
    //Fetch, prepare, sort, and filter our data...
    $inbounds->prepare_items();

    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php _e( 'Inbound Activity', 'bbconnect' ); ?></h2>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="pending-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $inbounds->display() ?>
        </form>

    </div>
    <?php
}

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BBC_Inbound_Table extends WP_List_Table {

	function bbconnect_inbound_query() {
		$bbc_inbound = new WP_Query( array(
											'post_type' => apply_filters( 'bbconnect_inbound_posts', array('bb_note') ),
											'post_status' => array( 'publish', 'private' ),
											'posts_per_page' => apply_filters( 'bbconnect_inbound_per_page', -1 ),
											'meta_query' => array(
													array(
														'key' => '_bbc_action_status',
														'value' => 'archived',
														'compare' => 'NOT EXISTS'
													),
											)
		) );

		$inbound = array();
		if ( $bbc_inbound->have_posts() ) : while( $bbc_inbound->have_posts() ) : $bbc_inbound->the_post();

			global $post;

			// GET THE TYPE
			$type = apply_filters( 'bbconnect_inbound_type', '' );
			$type_label = apply_filters( 'bbconnect_inbound_type_label', '' );
			if ('bb_note' == $post->post_type) {
				$terms = wp_get_post_terms($post->ID, 'bb_note_type');
				if (count($terms) > 0) {
				    $term = $types[0];
				    $type = $term->term_slug;
				    $type_label = $term->term_name;
				} else {
				    continue;
				}
			}

			$user = get_userdata( $post->post_author);
			if ( !$user ) continue;
			$inbound[] = array(
								'ID' => $post->ID,
								'uid' => $user->ID,
								'name' => $user->first_name . ' ' . $user->last_name,
								'email' => $user->user_email,
								'avatar' => get_avatar( $user->user_email, 32 ),
								'website' => $user->user_url,
								'title' => $post->post_title,
								'content' => $post->post_content,
								'date' => $post->post_date,
								'type' => $type,
								'type_label' => $type_label,
			);

		endwhile; endif;
		return $inbound;
	}

    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'bbconnect_inbound',
            'plural'    => 'bbconnect_inbounds',
            'ajax'      => true
        ) );

    }

    function column_default($item, $column_name){
        switch($column_name){
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_name($item){
        $actions = array();
        $actions['edit'] = sprintf( '<a href="%s?page=bbconnect_edit_user&user_id=%s">edit</a>', admin_url('users.php'), $item['uid'] );
        $actions['email'] = sprintf( '<a href="mailto:%s">email</a>', $item['email'] );
        if ( !empty( $item['website'] ) )
        	$actions['web'] = sprintf( '<a href="%s">website</a>', $item['website'] );

        return sprintf('%1$s %2$s <span style="color:silver">(%3$s)</span>%4$s',
            /*$1%s*/ '<div style="float: left; margin-right: 3px;">'.$item['avatar'].'</div>',
            /*$2%s*/ $item['name'],
            /*$3%s*/ $item['uid'],
            /*$4%s*/ $this->row_actions($actions)
        );
    }

    function column_type($item){
        //return sprintf( '<a class="bbconnect-icon %1$s">&nbsp;</a> %2$s', $item['type'], $item['type_label'] );
        return $item['type_label'];
    }

    function column_status($item){
        $actions = array(
        	//'edit' => sprintf( '<a href="">edit</a>' ),
        );
        return sprintf('%1$s %2$s',
            /*$2%s*/ $item['status'],
            /*$4%s*/ $this->row_actions($actions)
        );
    }

    function column_content($item){
        return sprintf('<p><strong>%1$s</strong><p>%2$s</p>',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['content']
        );
    }

    function column_date($item){
    	$dfor = get_option( 'date_format' );
    	$tfor = get_option( 'time_format' );
        return sprintf( '%s<br />%s', date( "$dfor", strtotime( $item['date'] ) ), date( "$tfor", strtotime( $item['date'] ) ) );
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['ID']
        );
    }


    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'		=> 'Name',
            'content'	=> 'Item',
            'type' 		=> 'Type',
			//'status'	=> 'Status',
            'date'		=> 'Date',
        );
        return $columns;
    }


    function get_sortable_columns() {
        $sortable_columns = array(
            'name'		=> array('name',true),
            'content'	=> array('content',false),
            'type'		=> array('type',true),
            'status'	=> array('status',true),
            'date'		=> array('date',true),
        );
        return $sortable_columns;
    }


    function get_bulk_actions() {
        $actions = array(
            'archive'    => 'Archive'
        );
        return $actions;
    }


    function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if( 'archive' === $this->current_action() ) {
        	//print_r($_REQUEST);
        	$postids = $_REQUEST['bbconnect_inbound'];
        	foreach ( $postids as $id ) {
        		update_post_meta( $id, '_bbc_action_status', 'archived' );
        	}


        }

    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     *
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = apply_filters( 'bbconnect_inbound_per_page_paginate', 25 );


        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);


        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();


        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example
         * package slightly different than one you might build on your own. In
         * this example, we'll be using array manipulation to sort and paginate
         * our data. In a real-world implementation, you will probably want to
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->bbconnect_inbound_query();


        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');


        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/


        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);



        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;


        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

}

