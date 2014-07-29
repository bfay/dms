<?php

if ( !function_exists('pagelines_draw_confirms')) :
function pagelines_draw_confirms(){

	$confirms = pagelines_admin_confirms();
	$save_text = sprintf( '%s Settings Saved. &nbsp;<a class="btag" href="%s/" target="_blank">View Your Site &rarr;</a>', PL_NICECHILDTHEMENAME, home_url());
	printf( '<div id="message" class="confirmation slideup_message fade c_ajax"><div class="confirmation-pad c_response">%s</div></div>', $save_text);

	if( !empty( $confirms ) ){
		foreach ( $confirms as $c ){

			$class = ( isset($c['class'] ) ) ? $c['class'] : null;

			printf( '<div id="message" class="confirmation slideup_message fade %s"><div class="confirmation-pad">%s</div></div>', $class, $c['text'] );
		}
	}

}
endif;

if ( !function_exists('pagelines_admin_confirms')) :
function pagelines_admin_confirms(){

	$confirms = array();

	if( isset( $_GET['settings-updated'] ) )
		$confirms[]['text'] = sprintf( __( "%s Settings Saved. &nbsp;<a class='sh_preview' href='%s/' target='_blank'>View Your Site &rarr;</a>", 'pagelines' ), PL_NICECHILDTHEMENAME, home_url() );
	if( isset($_GET['pageaction']) ){

		if( $_GET['pageaction']=='activated' && !isset($_GET['settings-updated']) ){
			$confirms['activated']['text'] = sprintf( __( 'Congratulations! %s Has Been Successfully Activated.', 'pagelines' ), PL_NICECHILDTHEMENAME );
			$confirms['activated']['class'] = 'activated';
		}

		elseif( $_GET['pageaction']=='import' && isset($_GET['imported'] )){
			$confirms['settings-import']['text'] = __( 'Congratulations! New settings have been successfully imported.', 'pagelines' );
			$confirms['settings-import']['class'] = "settings-import";
		}

		elseif( $_GET['pageaction']=='import' && isset($_GET['error']) && !isset($_GET['settings-updated']) ){
			$confirms['settings-import-error']['text'] = __( 'There was an error with import. Please make sure you are using the correct file.', 'pagelines' );
		}

	}

	if( isset( $_GET['reset'] ) ){

		if( isset( $_GET['opt_id'] ) && $_GET['opt_id'] == 'resettemplates' )
			$confirms['reset']['text'] = __( 'Template Configuration Restored To Default.', 'pagelines' );

		elseif( isset($_GET['opt_id'] ) && $_GET['opt_id'] == 'resetlayout' )
			$confirms['reset']['text'] = __( 'Layout Dimensions Restored To Default.', 'pagelines' );

		else
			$confirms['reset']['text'] = __( 'Settings Restored To Default.', 'pagelines' );

	}
	if ( isset( $_GET['plinfo'] ) )
		$confirms[]['text'] = __( 'Launchpad settings saved.', 'pagelines' );

	if ( isset( $_GET['extend_upload'] ) )
		$confirms[]['text'] = sprintf( __( 'Successfully uploaded your %s', 'pagelines' ), $_GET['extend_upload'] );

	if ( isset( $_GET['extend_text'] ) )
		switch( $_GET['extend_text'] ) {

			case 'section_delete':
				$confirms[]['text'] = __( 'Section was deleted.', 'pagelines' );
			break;

			case 'section_install':
				$confirms[]['text'] = __( 'Section was installed.', 'pagelines' );
			break;

			case 'section_upgrade':
				$confirms[]['text'] = __( 'Section was upgraded.', 'pagelines' );
			break;

			case 'plugin_install':
				$confirms[]['text'] = __( 'Plugin was installed.', 'pagelines' );
			break;

			case 'plugin_delete':
				$confirms[]['text'] = __( 'Plugin was deleted.', 'pagelines' );
			break;

			case 'plugin_upgrade':
				$confirms[]['text'] = __( 'Plugin was upgraded.', 'pagelines' );
			break;

			case 'theme_install':
				$confirms[]['text'] = __( 'Theme installed.', 'pagelines' );
			break;

			case 'theme_upgrade':
				$confirms[]['text'] = __( 'Theme upgraded.', 'pagelines' );
			break;
			case 'theme_delete';
				$confirms[]['text'] = __( 'Theme deleted.', 'pagelines' );
			break;

		}
		if ( ! empty( $confirms ) )
			do_action( 'extend_flush' );

	return apply_filters( 'pagelines_admin_confirms', $confirms );

 }

endif;

if ( !function_exists('pagelines_admin_errors')) :
function pagelines_admin_errors(){

	$errors = array();

/*	if( ie_version() && ie_version() < 8){

		$errors['ie']['title'] = sprintf( __( 'You are using Internet Explorer version: %s', 'pagelines' ), ie_version() );
		$errors['ie']['text'] = __( "Advanced options don't support Internet Explorer version 7 or lower. Please switch to a standards based browser that will allow you to easily configure your site (e.g. Firefox, Chrome, Safari, even IE8 or better would work).", 'pagelines' );

	}*/

	if( floatval( phpversion() ) < 5.0){
		$errors['php']['title'] = sprintf( __( 'You are using PHP version %s', 'pagelines' ), phpversion() );
		$errors['php']['text'] = __( 'Version 5 or higher is required for this theme to work correctly. Please check with your host about upgrading to a newer version.', 'pagelines' );
	}
	if ( isset( $_GET['extend_error'] ) ) {
		$errors['extend']['title'] = __( 'Extension problem found', 'pagelines' );

		switch( $_GET['extend_error'] ) {

			case 'blank':
				$errors['extend']['text'] = __( 'No file selected!', 'pagelines' );
			break;

			case 'filename':
				$errors['extend']['text'] = __( 'The file did not appear to be a PageLines section.', 'pagelines' );
			break;

			default:
				$errors['extend']['text'] = sprintf( __( 'Unknown error: %s', 'pagelines' ), $_GET['extend_error'] );
			break;
		}

	}
	return apply_filters( 'pagelines_admin_notifications', $errors );

}
endif;

if ( !function_exists('pagelines_error_messages')) :
function pagelines_error_messages(){

	$errors = pagelines_admin_errors();
	if( !empty( $errors ) ):
		foreach ( $errors as $e ): ?>
	<div id="message" class="confirmation plerror fade">
		<div class="confirmation-pad">
				<div class="confirmation-head">
					<?php echo $e['title'];?>
				</div>
				<div class="confirmation-subtext">
					<?php echo $e['text'];?>
				</div>
		</div>
	</div>

<?php 	endforeach;
	endif;
}
endif;


if ( !function_exists('pl_action_confirm')) :
function pl_action_confirm($name, $text){
	?>
	<script language="jscript" type="text/javascript"> function <?php echo $name;?>(){
			var a = confirm ("<?php echo esc_js( $text );?>");
			if(a) {
				jQuery("#input-full-submit").val(1);
				return true;
			} else return false;
		}
	</script>
<?php }
endif;