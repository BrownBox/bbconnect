<?php

function bbconnect_group_edit( $display = '' ) {
?>
<div id="group-edit" class="drawer disabled"<?php if ( 'open' === $display ) { echo ' style="display: block;"'; } ?>>
	<?php
		// if ( !defined( 'PAUPRO_VER' ) ) {
		// 	printf( __( '%1$s This is a pro feature and we\'re really sorry we couldn\'t include it here. Consider upgrading - each purchase supports future development and improvement! %2$s %3$s', 'bbconnect' ), '<div class="goodtoknow">', '</div>', '<div class="goodtoknow"><a href="http://bbconnect.com" class="button-primary">learn more!</a></div>' );
		// } else {
			do_action( 'bbconnect_group_edits' );
		//}
		
	?>
</div>
<?php
}

?>