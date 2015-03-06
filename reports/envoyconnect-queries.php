<?php

function envoyconnect_reports_queries() {
?>
	<div id="saved-queries" class="drawer">
		<?php
			if ( !defined( 'PAUPRO_VER' ) ) {
				printf( __( '%1$s This is a pro feature and we\'re really sorry we couldn\'t include it here. Consider upgrading - each purchase supports future development and improvement! %2$s %3$s', 'envoyconnect' ), '<div class="goodtoknow">', '</div>', '<div class="goodtoknow"><a href="http://envoyconnect.com" class="button-primary">learn more!</a></div>' );
			} else {
				do_action( 'envoyconnect_report_queries' );
			}
			
		?>
	</div>
<?php
}

?>