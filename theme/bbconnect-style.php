<?php
function bbconnect_admin_style() {
    $admin_pages = apply_filters('bbconnect_admin_pages', array(
            'bbconnect_reports',
            'bbconnect_options',
            'bbconnect_meta_options',
            'bbconnect_edit_user',
            'bbconnect_edit_user_profile',
            'bbconnect_new_user',
            'bbconnect_activity_log',
            'donor_report_submenu',
            'segment_report_submenu',
            'work_queues_submenu',
    ));
    if (in_array($_GET['page'], $admin_pages)) {
        echo "<script type='text/javascript' >document.body.className+=' folded';</script>";

        echo '<style>';
        echo 'body {background: #ebeff5;}';
        echo 'body > * {margin-bottom: 2rem; padding-bottom: 3rem;}';
        echo '#wpadminbar {background-color: #0073aa;}';
        echo '#adminmenu, #adminmenuwrap, #adminmenuback {background-color: #35485c;}';
        echo '#wp-admin-bar-top-secondary::before {background-image: url(https://connexionscrm.com/wp-content/uploads/sites/7/2017/08/tagline.png);}';
        echo '</style>';

        echo '<div class="connexions_footer">';
        echo '  <a href="http://connexionscrm.com/" class="float-right">Powered by Connexions</a>';
        echo '</div>';
    }
}
add_action('adminmenu', 'bbconnect_admin_style', 1);
