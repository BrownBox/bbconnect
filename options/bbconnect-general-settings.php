<?php

function bbconnect_general_settings() {

	return apply_filters( 'bbconnect_general_settings', array(

	));

}


function bbconnect_option_welcome( $active ) {
	if ( 'bbconnect_general_settings' == $active ) {
?>
	<div class="options-panel farmer">
		<div id="branding"></div>
		<div class="column_holder">
			<h2><?php _e( 'Hi there. We\'re really glad you\'re here.', 'bbconnect' ); ?></h2>
			<p><?php _e( 'We hope this plugin makes what you do in life a little better. To that end, here\'s a few helpful resources.', 'bbconnect' ); ?></p>

			<div id="setup" class="option-block">
				<h3><?php _e( 'Setup', 'bbconnect' ); ?></h3>
				<ol>
					<li><?php printf( __( '%1$sManage Fields%2$s: Set the fields and permissions to start collecting data.', 'bbconnect' ), '<a href="'.admin_url('admin.php?page=bbconnect_meta_options').'">', '</a>' ); ?></li>
					<li><?php printf( __( '%1$sCheck Your Profile%2$s: Make sure you have it the way you want it.', 'bbconnect' ), '<a href="'.admin_url('admin.php?page=bbconnect_edit_user_profile').'">', '</a>' ); ?></li>
					<li><?php printf( __( '%1$sTry a Search%2$s: Data mining is a surprising amount of fun.', 'bbconnect' ), '<a href="'.admin_url('users.php?page=bbconnect_reports').'">', '</a>' ); ?></li>
					<li><?php printf( __( '%1$sTweak Your Options%2$s: Visit the tabs above to customize Connexions for you.', 'bbconnect' ), '<strong>', '</strong>' ); ?></li>
				</ol>
			</div>

			<div id="help" class="option-block">
				<h3><?php _e( 'Help', 'bbconnect' ); ?></h3>
				<ol>
					<li><?php printf( __( '%1$sRight Now%2$s: Help options can be found in the in handy %3$s"help" tabs%4$s at the top right of your screen.', 'bbconnect' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></li>
				</ol>
			</div>

			<div id="suggestions" class="option-block">
				<h3><?php _e( 'Suggestions', 'bbconnect' ); ?></h3>
				<ol>
					<li><?php printf( __( '%1$sContact Us%2$s: We want to hear from you! Please make sure to visit our site %3$sbrownbox.net.au%4$s to let us know what you think of the application.', 'bbconnect' ), '<strong>', '</strong>', '<a href="http://brownbox.net.au/">', '</a>'  ); ?></li>
				</ol>
			</div>
		</div>
		<div class="column_holder">
<?php
	}
}

function bbconnect_option_goodbye( $active ) {
	if ( 'bbconnect_general_settings' == $active ) {

?>
		</div>
	</div>
<?php
	}
}
