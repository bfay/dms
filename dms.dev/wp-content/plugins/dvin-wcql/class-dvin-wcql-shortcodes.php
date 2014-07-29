<?php
/**
 * Dvin_Wcql_Shortcodes class, Handles the Listing shortcodes
 */
class Dvin_Wcql_Shortcodes {
	
	/**
     * function listing, retrieves quotelist products
	 * @param $atts Array - an associative array of attributes (preferences)
	 * @param $content  String -  the enclosed content
	 * @param $code String -  the shortcode name
	 * @code
	 * @static
     */
	static function listing($atts, $content = null, $code = "") {
		global $dvin_wcql_settings;
		ob_start();
		$qlist = array();
		if(isset($_SESSION['dvin_qlist_products'])) {
			$qlist_count[0]['cnt'] = count($_SESSION['dvin_qlist_products']);
			$qlist = $_SESSION['dvin_qlist_products'];
		}
		
		//include the template file
		if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'templates/quotelist.php')) {
			include(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'templates/quotelist.php');
		}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'templates/quotelist.php')) {
			include(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'templates/quotelist.php');
		}else{
			require('templates/quotelist.php');
		}
		 return ob_get_clean();
		//echo ob_get_clean();
	}
	
	/**
     * function Form, displays the form
	 * @param $atts Array - an associative array of attributes (preferences)
	 * @param $content  String -  the enclosed content
	 * @param $code String -  the shortcode name
	 * @code
	 * @static
     */
	static function form($atts, $content = null, $code = "") {

		global $dvin_wcql_settings;

		if($dvin_wcql_settings['use_gravity_forms'] == 'on') {

			return do_shortcode('[gravityform id="'.$dvin_wcql_settings['gravity_form_select'].'" title=false description=false ajax="true"]');

		} else {

			ob_start();
		
			if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'templates/form.php')) {
				include(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'templates/form.php');
			}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'templates/form.php')) {
				include(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'templates/form.php');
			}else{
				include('templates/form.php');
			}
			return ob_get_clean();
		}
	}
	
	/**
     * function button, displays quotelist button
	 * @param $atts Array - an associative array of attributes (preferences)
	 * @param $content  String -  the enclosed content
	 * @param $code String -  the shortcode name
	 * @codess
	 * @static
     */
	static function button($atts, $content = null, $code = "") {
		dvin_addquotelist_button();
	}
	/**
     * function button, displays quotelist button on shop page
	 * @param $atts Array - an associative array of attributes (preferences)
	 * @param $content  String -  the enclosed content
	 * @param $code String -  the shortcode name
	 * @codess
	 * @static
     */
	static function shopbutton($atts, $content = null, $code = "") {
		dvin_addquotelist_button(true,$atts['product_id']);
	}
	
}
add_shortcode("dvin-wcql-listing", array("dvin_wcql_Shortcodes", "listing"));
add_shortcode("dvin-wcql-form", array("dvin_wcql_Shortcodes", "form"));
add_shortcode("dvin-wcql-button", array("dvin_wcql_Shortcodes", "button"));
add_shortcode("dvin-wcql-shopbutton", array("dvin_wcql_Shortcodes", "shopbutton"));
?>