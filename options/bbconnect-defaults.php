<?php

/**
 * Runs on activation to install options.
 *
 * @since 0.0.0
 */
function bbconnect_activate() {

    // SET LOCAL VARIABLES
    $dbv = get_option( '_bbconnect_version' );

    if ( false === $dbv )
        $dbv = get_option( 'bbconnect_version' );

    // FIRST, CHECK TO SEE IF THIS IS A FIRST INSTALL
    if ( false === $dbv ) {

        include_once( 'bbconnect-updates.php' );

        $bbconnect_defaults = array(
            '_bbconnect_version' => BBCONNECT_VER,
            '_bbconnect_user_meta' => bbconnect_default_user_meta(),
            'bbconnect_bbc_public' => bbconnect_bbc_public(),
            'bbconnect_bbc_primary' => bbconnect_bbc_primary(),
            'bbconnect_bbc_billing' => bbconnect_bbc_billing(),
            'bbconnect_bbc_shipping' => bbconnect_bbc_shipping(),
            '_bbconnect_user_forms' => bbconnect_form_create(),
        );

        foreach( $bbconnect_defaults as $key => $value ) {
            add_option( $key, $value );
        }

    // ALTERNATIVELY, COMPARE THE DATABASE AND SCRIPT VERSION OF THE PLUGIN
    } else if ( $dbv != BBCONNECT_VER ) {

        include_once( 'bbconnect-updates.php' );
        $bbconnect_versions = bbconnect_versions();
        $update_log = array();

        foreach( $bbconnect_versions as $key => $value ) {
            if ( $key > $dbv ) {
                $update_log[$key] = call_user_func( $value );
                update_option( '_bbconnect_version', $key );
                update_option( 'bbconnect_version', $key );
            }
        }

        // UPDATE ANY ADMIN MESSAGES
        update_option( '_bbconnect_admin_messages', $update_log );

        // VERIFY THAT WE'RE ON THE CURRENT VERSION AS DB UPDATES ARE INFREQUENT
        $udbv = get_option( '_bbconnect_version' );

        if ( $udbv != BBCONNECT_VER )
            update_option( '_bbconnect_version', BBCONNECT_VER );
    }

    if (!wp_next_scheduled('bbconnect_do_daily_updates')) {
        wp_schedule_event(strtotime('6am'), 'daily', 'bbconnect_do_daily_updates');
    }
}

/**
 * Deactivation hook to clean up as needed
 * @since 2.2.2
 */
function bbconnect_deactivate() {
    wp_clear_scheduled_hook('bbconnect_do_daily_updates');
}

add_action('bbconnect_do_daily_updates', 'bbconnect_daily_updates');
function bbconnect_daily_updates() {
    bbconnect_update_days_since_kpi();
}

/**
 * Recalculate days since last donation for all users.
 * Intended to be run as a daily cron - shouldn't ever be called directly!
 */
function bbconnect_update_days_since_kpi() {
    global $blog_id;
    $args = array(
            'blog_id' => $blog_id,
    );
    $users = get_users($args);

    $today = bbconnect_get_current_datetime();
    foreach ($users as $userkey => $user) {
        $last_transaction_date = get_user_meta($user->ID, 'bbconnect_kpi_last_transaction_date', true);
        if (!empty($last_transaction_date)) {
            $date_last_transaction = bbconnect_get_datetime($last_transaction_date);
            $days_since_last_transaction = $date_last_transaction->diff($today, true);
            update_user_meta($user->ID, 'bbconnect_kpi_days_since_last_transaction', $days_since_last_transaction->days);
        }
    }
}

/**
 * Define the default User Meta Fields. This will include a reference to the default WordPress fields as well as the default BB Connect fields. Doesn't really have parameters but there is a reference for the array below.
 *
 * @since 0.0.0
 *
 * @param 'source' =>
 * @param 'meta_key' =>
 * @param 'tag' =>
 * @param 'name' =>
 * @param 'options' => array(
 * @param     'admin' => true,
 * @param     'user' => true,
 * @param     'reports' => true,
 * @param     'public' => false,
 * @param     'unique' => false. for imports, allows you to match on this field
 * @param     'req' => false,
 * @param     'field_type' => 'multitext',
 * @param     'choices' => array()
 * @param )
 *
 */
function bbconnect_default_user_meta() {
    return array(
        'column_1' => bbconnect_process_defaults( bbconnect_column_one(), 'column_1', '' ),
        'column_2' => bbconnect_process_defaults( bbconnect_column_two(), 'column_2', '' ),
    );
}

function bbconnect_process_defaults( $def_arr, $column, $section = false ) {

    foreach ( $def_arr as $key => $value ) {

        // SET THE POSITIONS
        $value['column'] = $column;
        $value['section'] = $section;

        // SET A NAMED VALUE FOR THE BBCONNECT_USER_META ARRAY AND
        $ret_arr[] = 'bbconnect_'.$value['meta_key'];
        // ADD THE OPTION
        add_option( 'bbconnect_'.$value['meta_key'], $value );

    }

    return $ret_arr;

}


function bbconnect_column_two() {
    return array(
            // ACCOUNT INFORMATION -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'account_information', 'tag' => '', 'name' => __( 'Account Information', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'section', 'choices' => bbconnect_process_defaults( bbconnect_account_information_fields(), 'section_account_information', 'account_information' ) ), 'help' => '' ),

            // PREFERENCES -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'preferences', 'tag' => '', 'name' => __( 'Preferences', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'section', 'choices' => bbconnect_process_defaults( bbconnect_preferences_fields(), 'section_preferences', 'preferences' ) ), 'help' => '' ),
    );
}


function bbconnect_column_one() {
    return array(
            // BASIC INFORMATION -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'basic_information', 'tag' => '', 'name' => __( 'Basic Information', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'section', 'choices' => bbconnect_process_defaults( bbconnect_basic_information_fields(), 'section_basic_information', 'basic_information' ) ), 'help' => '' ),

            // CONTACT INFORMATION -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'contact_information', 'tag' => '', 'name' => __( 'Contact Information', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'section', 'choices' => bbconnect_process_defaults( bbconnect_contact_information_fields(), 'section_contact_information', 'contact_information' ) ), 'help' => '' ),
    );
}

function bbconnect_account_information_fields() {
    return array(
            // WORDPRESS RESERVED DEFAULTS
            // EMAIL
            array( 'source' => 'wpr', 'meta_key' => 'email', 'tag' => 'EMAIL', 'name' => __( 'Email', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => true, 'public' => false, 'req' => true, 'unique' => true, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // USERNAME -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'user_login', 'tag' => '', 'name' => __( 'Username', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // USERID -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'ID', 'tag' => '', 'name' => __( 'User ID', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // USERDATE -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'user_registered', 'tag' => '', 'name' => __( 'Date Registered', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // TYPE OF USER
            array( 'source' => 'user', 'meta_key' => 'user_type', 'tag' => 'TYPE', 'name' => __( 'User Type', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'radio', 'choices' => array( __( 'person', 'bbconnect' ) => __( 'Person', 'bbconnect' ), __( 'organization', 'bbconnect' ) => __( 'Organization', 'bbconnect' ), __( 'household', 'bbconnect' ) => __( 'Household', 'bbconnect' ) ) ), 'help' => '' ),
            // ROLE -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'role', 'tag' => '', 'name' => __( 'Role', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => array() ), 'help' => '' ),
            // DISPLAY NAME -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'display_name', 'tag' => '', 'name' => __( 'Display Name', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => array() ), 'help' => '' ),
            // PASSWORD -- WILL NOT BY SYNCED
            array( 'source' => 'wpr', 'meta_key' => 'pass', 'tag' => '', 'name' => __( 'Password', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'password', 'choices' => array( '1', '2' ) ), 'help' => '' ),
            // User Source
            array('source' => 'bbconnect', 'meta_key' => 'source', 'tag' => '', 'name' => __('User Source', 'bbconnect'), 'options' => array('admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => array('manual' => __('Manually Created', 'bbconnected'), 'form' => __('Form Submission', 'bbconnect'), '' => __('Unknown', 'bbconnect'))), 'help' => ''),

            // Donor Segment
            array( 'source' => 'bbconnect', 'meta_key' => 'segment_id', 'tag' => '', 'name' => __( 'Donor Segment', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => 'bbconnect_helper_segment' ), 'help' => false ),

            // Default KPIs
            array('source' => 'bbconnect', 'meta_key' => 'kpi_transaction_amount', 'tag' => '', 'name' => __('Total Transactions ($)', 'bbconnect'), 'options' => array('admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'number', 'choices' => array(), 'is_currency' => true), 'help' => ''),
            array('source' => 'bbconnect', 'meta_key' => 'kpi_transaction_count', 'tag' => '', 'name' => __('Transaction Count', 'bbconnect'), 'options' => array('admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'number', 'choices' => array()), 'help' => ''),
            array('source' => 'bbconnect', 'meta_key' => 'kpi_last_transaction_date', 'tag' => '', 'name' => __('Last Transaction Date', 'bbconnect'), 'options' => array('admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array()), 'help' => ''),
            array('source' => 'bbconnect', 'meta_key' => 'kpi_days_since_last_transaction', 'tag' => '', 'name' => __('Days Since Last Transaction', 'bbconnect'), 'options' => array('admin' => true, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'number', 'choices' => array()), 'help' => ''),
    );
}

function bbconnect_basic_information_fields() {
    return array(
            // TITLE
            array( 'source' => 'user', 'meta_key' => 'title', 'tag' => 'TITLE', 'name' => __( 'Title', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // FIRST NAME
            array( 'source' => 'wp', 'meta_key' => 'first_name', 'tag' => 'FNAME', 'name' => __( 'First Name', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // MIDDLE NAME
            array( 'source' => 'user', 'meta_key' => 'middle_name', 'tag' => 'MNAME', 'name' => __( 'Middle Name', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // LAST NAME
            array( 'source' => 'wp', 'meta_key' => 'last_name', 'tag' => 'LNAME', 'name' => __( 'Last Name', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => true, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // ORGANIZATION
            array( 'source' => 'user', 'meta_key' => 'organization', 'tag' => 'ORG', 'name' => __( 'Organization', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => '' ),
            // NICKNAME -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'nickname', 'tag' => '', 'name' => __( 'Nickname', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => 'an alternate name to use' ),
            // BIO -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'description', 'tag' => 'DESC', 'name' => __( 'Biography', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'textarea', 'choices' => array() ) ),
    );
}



function bbconnect_contact_information_fields() {
    return array(
            // WEBSITE
            array( 'source' => 'wpr', 'meta_key' => 'url', 'tag' => 'WEB', 'name' => __( 'Website', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'help' => 'http://www.example.com' ),
            // TELEPHONE -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'telephone', 'tag' => 'TEL', 'name' => __( 'Telephone', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'multitext', 'choices' => array( __( 'home', 'bbconnect' ) => __( 'Home', 'bbconnect' ), __( 'work', 'bbconnect' ) => __( 'Work', 'bbconnect' ), __( 'mobile', 'bbconnect' ) => __( 'Mobile', 'bbconnect' ), __( 'other', 'bbconnect' ) => __( 'Other', 'bbconnect' ) ) ), 'help' => '' ),
            // ADDITIONAL EMAIL -- WILL NOT BE SYNCED DUE TO FIELD TYPE CONSTRAINT
            array( 'source' => 'user', 'meta_key' => 'additional_email', 'tag' => '', 'name' => __( 'Additional Email Addresses', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'multitext', 'choices' => array( __( 'home', 'bbconnect' ) => __( 'Home', 'bbconnect' ), __( 'work', 'bbconnect' ) => __( 'Work', 'bbconnect' ), __( 'other', 'bbconnect' ) => __( 'Other', 'bbconnect' ) ) ), 'help' => '' ),
            // AIM -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'aim', 'tag' => 'AIM', 'name' => __( 'AIM', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ) ),
            // YIM -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'yim', 'tag' => 'YIM', 'name' => __( 'Yahoo IM', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ) ),
            // JABBER -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'jabber', 'tag' => 'JABBER', 'name' => __( 'Jabber / Google Talk', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ) ),

            // ADDRESS ONE
            array( 'source' => 'bbconnect', 'meta_key' => 'address_1', 'tag' => '', 'name' => __( 'Address One', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'group', 'choices' => bbconnect_default_address( '1' ) ), 'group_type' => 'address', 'help' => __( 'Click to expand', 'bbconnect' ) ),
            // ADDRESS TWO
            array( 'source' => 'bbconnect', 'meta_key' => 'address_2', 'tag' => '', 'name' => __( 'Address Two', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'group', 'choices' => bbconnect_default_address( '2' ) ), 'group_type' => 'address', 'help' => __( 'Click to expand', 'bbconnect' ) ),
            // ADDRESS THREE
            array( 'source' => 'bbconnect', 'meta_key' => 'address_3', 'tag' => '', 'name' => __( 'Address Three', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'group', 'choices' => bbconnect_default_address( '3' ) ), 'group_type' => 'address', 'help' => __( 'Click to expand', 'bbconnect' ) ),
    );
}

function bbconnect_preferences_fields() {
    return array(
            // WORDPRESS UNRESERVED DEFAULTS
            // ADMIN BAR FRONT -- WILL NOT BY SYNCED
            array( 'source' => 'wp', 'meta_key' => 'show_admin_bar_front', 'tag' => '', 'name' => __( 'Show Admin Bar: when viewing the site', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'checkbox', 'choices' => array( 'true' ) ) ),

            // KEYBOARD SHORTCUTS -- WILL NOT BE SYNCED
            array( 'source' => 'wp', 'meta_key' => 'comment_shortcuts', 'tag' => '', 'name' => __( 'Keyboard Shortcuts', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => false, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'checkbox', 'choices' => array( 'false' ) ) ),

            // EMAIL PREFERENCES -- WILL NOT BE SYNCED
            array( 'source' => 'bbconnect', 'meta_key' => 'bbc_subscription', 'tag' => '', 'name' => __( 'Subscribe to email updates', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'checkbox', 'choices' => array( 'false' ) ), 'help' => false ),

            // CONTACT PREFERENCES -- WILL NOT BE SYNCED
            array( 'source' => 'bbconnect', 'meta_key' => 'bbc_contact', 'tag' => '', 'name' => __( 'Allow others to contact me', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'checkbox', 'choices' => array( 'false' ) ), 'help' => false ),
    );
}

function bbconnect_default_address( $location ) {
    // APPEND THE LOCATION
    $loc = '_'.$location;
    $loc_tag = strtoupper( substr( $location, 0, 1 ) );

    // ADDRESS TYPE
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_type'.$loc, 'tag' => 'ADDTYPE'.$loc_tag, 'name' => __( 'Address Location', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => array( __( 'home', 'bbconnect' ) => __( 'Home', 'bbconnect' ), __( 'work', 'bbconnect' ) => __( 'Work', 'bbconnect' ), __( 'other', 'bbconnect' ) => __( 'Other', 'bbconnect' ) ) ), 'group' => 'address'.$loc, 'help' => '' );
    // ADDRESS RECIPIENT
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_recipient'.$loc, 'tag' => 'ADREP'.$loc_tag, 'name' => __( 'Address Recipient', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The person receiving deliveries at this address.', 'bbconnect' ) );
    // ADDRESS ORGANIZATION
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_organization'.$loc, 'tag' => 'ADORG'.$loc_tag, 'name' => __( 'Address Organization', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'The company or organization at this address.', 'bbconnect' ) );
    // ADDRESS LINE ONE
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_one'.$loc, 'tag' => 'ADD1'.$loc_tag, 'name' => __( 'Address Line One', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'Typically, this is a street address', 'bbconnect' ) );
    // ADDRESS LINE TWO
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_two'.$loc, 'tag' => 'ADD2'.$loc_tag, 'name' => __( 'Address Line Two', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'Typically, this is a unit, suite or apartment', 'bbconnect' ) );
    // ADDRESS LINE THREE
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_three'.$loc, 'tag' => 'ADD3'.$loc_tag, 'name' => __( 'Address Line Three', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc, 'help' => __( 'Typically, this is the title of a business or in-care-of notation', 'bbconnect' ) );
    // CITY
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_city'.$loc, 'tag' => 'CITY'.$loc_tag, 'name' => __( 'City', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc );
    // STATE OR PROVINCE
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_state'.$loc, 'tag' => 'STATE'.$loc_tag, 'name' => __( 'State or Province', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => 'bbconnect_helper_state' ), 'group' => 'address'.$loc );
    // COUNTRY
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_country'.$loc, 'tag' => 'COUNTRY'.$loc_tag, 'name' => __( 'Country', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'select', 'choices' => 'bbconnect_helper_country' ), 'group' => 'address'.$loc );
    // POSTAL CODE
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_postal_code'.$loc, 'tag' => 'POSTAL'.$loc_tag, 'name' => __( 'Postal Code', 'bbconnect' ), 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'text', 'choices' => array() ), 'group' => 'address'.$loc );
    // LATITUDE -- HIDDEN BY DEFAULT -- WILL NOT BE SYNCED
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_latitude'.$loc, 'tag' => '', 'name' => __( 'Latitude', 'bbconnect' ), 'options' => array( 'admin' => false, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'hidden', 'choices' => array() ), 'group' => 'address'.$loc );
    // LONGITUDE -- HIDDEN BY DEFAULT -- WILL NOT BE SYNCED
    $address[] = array( 'source' => 'bbconnect', 'meta_key' => 'address_longitude'.$loc, 'tag' => '', 'name' => __( 'Longitude', 'bbconnect' ), 'options' => array( 'admin' => false, 'user' => false, 'signup' => false, 'reports' => true, 'public' => false, 'req' => false, 'field_type' => 'hidden', 'choices' => array() ), 'group' => 'address'.$loc );

    // SET THE MASTER USER META ARRAY TO BE USED FOR SORTING
    $address_keys = array();

    foreach ( $address as $key => $value ) {
        // SET A NAMED VALUE FOR THE BBCONNECT_USER_META ARRAY AND
        $address_keys[] = 'bbconnect_'.$value['meta_key'];
        // ADD THE OPTION
        add_option( 'bbconnect_'.$value['meta_key'], $value );
    }

    return $address_keys;
}


function bbconnect_bbc_public() {
    return array( 'source' => 'bbconnect', 'meta_key' => 'bbc_public', 'tag' => '', 'name' => __( 'Public Option', 'bbconnect' ), 'options' => array( 'admin' => false, 'user' => false, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'plugin', 'choices' => 'bbconnect_public_status' ), 'help' => __( 'Use this to toggle the public status of all available fields.', 'bbconnect' ) );
}

function bbconnect_bbc_primary() {
    return array( 'source' => 'bbconnect', 'meta_key' => 'bbc_primary', 'tag' => '', 'name' => 'Primary', 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'radio', 'choices' => array( array( 'label' => 'primary', 'value' => '' ) ) ), 'help' => '' );
}

function bbconnect_bbc_billing() {
    return array( 'source' => 'bbconnect', 'meta_key' => 'bbc_billing', 'tag' => '', 'name' => 'Billing', 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'radio', 'choices' => array( array( 'label' => 'billing', 'value' => '' ) ) ), 'help' => '' );
}

function bbconnect_bbc_shipping() {
    return array( 'source' => 'bbconnect', 'meta_key' => 'bbc_shipping', 'tag' => '', 'name' => 'Shipping', 'options' => array( 'admin' => true, 'user' => true, 'signup' => false, 'reports' => false, 'public' => false, 'req' => false, 'field_type' => 'radio', 'choices' => array( array( 'label' => 'shipping', 'value' => '' ) ) ), 'help' => '' );
}

function bbconnect_form_create() {
    $contact_form = array( 'source' => 'bbconnect', 'msg' => '', 'confirm' => '', 'column_1' => array( 'first_name', 'last_name', 'email' ), 'column_2' => array( '_bbc_form_subject', '_bbc_form_message', '_bbc_form_cc' ) );
    add_option( '_bbconnect_form_contact_form', $contact_form );

    return array( 'contact_form' => __( 'Contact Form', 'bbconnect' ) );
}
