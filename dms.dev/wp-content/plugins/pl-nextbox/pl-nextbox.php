<?php
/*
Plugin Name: PLNextBox
Plugin URI: http://www.pagelines.com
Description: Add ANYTHING anywhere you like and if your host allows it even include PHP!.
Author: PageLines
PageLines: true
Version: 1.1
Section: true
Class Name: PLNextBox
Filter: component, dual-width
Loading: active
*/

/**
 * IMPORTANT
 * This tells wordpress to not load the class as DMS will do it later when the main sections API is available.
 * If you want to include PHP earlier like a normal plugin just add it above here.
 */

if( ! class_exists( 'PageLinesSectionFactory' ) )
	return;

class PLNextBox extends PageLinesSection {

	function section_head() {
		if( 1 == $this->opt( 'nextbox_divs' ) ) {
			add_action( 'pl_scripts_on_ready', array( $this, 'script' ) );
		}
	}

	function script() {

		$clone = $this->meta['clone'];		
		ob_start();
		?>jQuery( '#<?php echo $this->id.$clone; ?> div' ).removeClass('pl-section-pad fix')
		<?php
		return ob_get_contents();
	}

	function section_template() {
		$content = $this->opt( 'nextbox_content' );
		
		if( 1 == $this->opt( 'nextbox_shortcode' ) )
			$content = do_shortcode( $content );
			
		if( 1 == $this->opt( 'nextbox_wpautop' ) )
			$content = wpautop( $content );
		
		if( 1 == $this->opt( 'nextbox_php' ) && false === strpos(ini_get("disable_functions"), 'eval') ) {

			$content = $this->opt( 'nextbox_content' );
			ob_start();
			$result = eval($content);
			$content = ob_get_contents();
			ob_end_clean();	
			if( isset( $result ) )
				$content = 'Looks like your NextBox PHP resulted in an error :/';
		}
		echo $content;
	}

		function section_opts(){
			$opts = array(
				array(
					'type'		=> 'multi',
					'key'		=> 'nextbox_settings',
					'col'		=> 1,
					'opts'		=> array(
						array(
							'type' 			=> 'textarea',
							'key'			=> 'nextbox_content',
							'default'		=> '',
							'label'			=> 'Your NextBox content',
							),
						array(
							'type' 			=> 'check',
							'key'			=> 'nextbox_shortcode',
							'default'		=> 0,
							'label'			=> 'Render ShortCodes',
							),
						array(
							'type' 			=> 'check',
							'key'			=> 'nextbox_wpautop',
							'default'		=> 0,
							'label'			=> 'Apply WordPress Auto P Filters',
								),
						array(
							'type' 			=> 'check',
							'key'			=> 'nextbox_php',
							'default'		=> 0,
							'label'			=> 'Allow PHP execution',
							'help'			=> ( false !== strpos(ini_get("disable_functions"), 'eval') ) ? 'Looks like eval() is disabled on your host, so PHP will not be executed.' : ''
								),
						array(
							'type' 			=> 'check',
							'key'			=> 'nextbox_divs',
							'default'		=> 0,
							'label'			=> 'Disable DMS padding classes',
							),						
						)
				)
			);
			return $opts;		
		}
}