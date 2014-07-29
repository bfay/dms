<?php
/**
 * Dvin_Wcql_Admin class, Handles the Admin Panel functionality of Quotelist
 */
class Dvin_Wcql_Admin {
/**
  * function init
  * @static
  */
	static function init() {
	
		//process the form, if submitted for update
		if(isset($_POST['updateSettings'])) {
			$settings_arr = array();
			
			//get all settings
			$settings_arr = maybe_unserialize(get_option('dvin_wcql_settings'));
			$settings_arr['link_position'] = $_POST['link_position'];
			$settings_arr['link_bgcolor'] = $_POST['link_bgcolor'];
			$settings_arr['link_fontcolor'] = $_POST['link_fontcolor'];
			$settings_arr['link_sameas_addtocart_default_colors'] = isset($_POST['link_sameas_addtocart_default_colors'])?'on':'';
			$settings_arr['link_sameas_addtocart'] = isset($_POST['link_sameas_addtocart'])?'on':'';
			$settings_arr['custom_css'] = $_POST['custom_css'];
			$settings_arr['show_quotelist_link_always'] = isset($_POST['show_quotelist_link_always'])?'on':'';
			$settings_arr['show_on_shop'] = isset($_POST['show_on_shop'])?'on':'';
			$settings_arr['no_price'] = isset($_POST['no_price'])?'on':'';
			$settings_arr['no_qty'] = isset($_POST['no_qty'])?'on':'';
			$settings_arr['remove_price_col'] = isset($_POST['remove_price_col'])?'on':'';
			$settings_arr['use_gravity_forms'] = isset($_POST['use_gravity_forms'])?'on':'';
			$settings_arr['gravity_form_select'] = $_POST['gravity_form_select'];
			
			//update settings in database
			update_option('dvin_wcql_settings',maybe_serialize($settings_arr));
						
			//update the custom_styles file
			file_put_contents(DVIN_QLIST_PLUGIN_URL."css/custom_styles.css",$_POST['custom_css']);
			
			header('Location: ?page=dvinqlistmainpage&updated=true');
			exit;				
		}
		//process the form, if submitted for update
		if(isset($_POST['updateEmailSettings'])) {		
			if(get_magic_quotes_gpc()) {
				$_POST['dvin_wcql_email_subject'] = stripslashes($_POST['dvin_wcql_email_subject']);
				$_POST['dvin_wcql_email_msg'] =  stripslashes($_POST['dvin_wcql_email_msg']);				
			}
			//save the settings
			update_option('dvin_wcql_email_subject',$_POST['dvin_wcql_email_subject']);
			update_option('dvin_wcql_email_msg',$_POST['dvin_wcql_email_msg']);
			update_option('dvin_wcql_admin_email',$_POST['dvin_wcql_admin_email']);
			update_option('dvin_wcql_copy_toreq', isset($_POST['dvin_wcql_copy_toreq'])?'on':'');
			
			header('Location: ?page=dvinqlistmainpage&updated=true');
			exit;			
		}//end if
		//action to reset to default settings
		if(isset($_POST['resettodefaultsettings'])) {
			Dvin_Wcql_default_settings();			
			header('Location: ?page=dvinqlistmainpage&updated=true');
			exit;
		}
	}
/**
  * function add_menu, adds menu items to the admin
  * @static
  */
	static function add_menu() {
		//create submenu items
		$dvinquotelist_page = add_submenu_page("woocommerce", 'Request a Quote', 'Request a Quote', "manage_options", "dvinqlistmainpage",array("dvin_wcql_Admin","dvin_quotelist_mainpage"));
		/* Using registered $page handle to hook script and style load */
		add_action("admin_print_scripts-{$dvinquotelist_page}",array("dvin_wcql_Admin", "dvin_admin_scripts"));
		add_action("admin_print_styles-{$dvinquotelist_page}",array("dvin_wcql_Admin", "dvin_admin_styles"));
	}
	/**
	* function dvin_quotelist_mainpage, manages the admin mainpage 
	* @static
	*/
	static function dvin_quotelist_mainpage() {		
		//get plugin data
		$dvin_wcql_plugin_data    = get_plugin_data( dirname(__FILE__).'/dvin-woocommerce-quotelist.php');
		//now include the template
		include('templates/admin_template.php');
	}
	/**
	* function dvin_settings_page, manages settings 
	* @static
	*/
	static function dvin_settings_page() {
		//retrieve from db
		$settings_arr = maybe_unserialize(get_option('dvin_wcql_settings'));
		extract($settings_arr);	
		//now include the template
		include('templates/settings_template.php');
	}
	/**
	* function dvin_emailsettings_page, manages email settings 
	* @static
	*/
	static function dvin_emailsettings_page() {
		$dvin_wcql_email_subject = get_option('dvin_wcql_email_subject');
		$dvin_wcql_email_msg = get_option('dvin_wcql_email_msg');
		$dvin_wcql_admin_email = get_option('dvin_wcql_admin_email');	
		$dvin_wcql_copy_toreq = get_option('dvin_wcql_copy_toreq');
		
		//now include the template
		include('templates/email_settings_template.php');
	}
	
	/**
     * function dvin_admin_styles, enques styles
	 * @static
     */
	static function dvin_admin_styles() {
		 wp_enqueue_style( 'dvin-wcql-ui-css', plugins_url('/css/ui-lightness/jquery-ui-1.8.23.custom.css', __FILE__));//remove, if not req	
		 wp_enqueue_style( 'dvin-wcql-admin-css', plugins_url('/css/admin.css', __FILE__));//remove, if not req	
		 wp_enqueue_style('farbtastic'); //remove, if not req	
    }
    
	/**
	 * function dvin_admin_scripts, enques scripts
	 * @static
  	*/
	static function dvin_admin_scripts() {
		wp_enqueue_script('div-wcql-adminjs',plugins_url('/js/admin.js', __FILE__),'','','in_footer'); 
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('farbtastic'); //remove, if not req
	}
}
//add actions
add_action("admin_init", array("dvin_wcql_Admin", "init"));
add_action("admin_menu", array("dvin_wcql_Admin", "add_menu"));
?>