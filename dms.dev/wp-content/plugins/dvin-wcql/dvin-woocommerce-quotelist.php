<?php
/*
Plugin Name: Woocommerce - Request a Quote
Plugin URI: http://www.limecuda.com/
Description: Woocommerce - Request a Quote Plugin
Version: 1.15
Author: Kiran P
Author URI: http://www.limecuda.com/
Text Domain: dvinwcql
*/
/** define constants **/
define("DVIN_QLIST_PLUGIN_WEBURL", plugin_dir_url( __FILE__ ));
define("DVIN_QLIST_PLUGIN_URL", plugin_dir_path( __FILE__ ));
//this array would be used in in determine which hook and priority  to use for which position
$dvin_wcql_link_positions = array('After Add to Cart' =>array('hook_name'=>'woocommerce_single_product_summary','priority'=>30),
						'After Thumbnail' => array('hook_name'=> 'woocommerce_product_thumbnails','priority'=>40),
						'After Product Summary'=>array('hook_name'=>'woocommerce_single_product_summary','priority'=>40),
						'Replace Add To Cart'=>array('hook_name'=>'woocommerce_after_add_to_cart_button','priority'=>40)
						);
//include required files based on admin or site
if (is_admin()) { //for admin
//admin  files 
	require_once(DVIN_QLIST_PLUGIN_URL."class-dvin-wcql.php"); //core class
	include_once(DVIN_QLIST_PLUGIN_URL."class-dvin-wcql-admin.php");
}  else { // for site
	//enqueue necessary scripts and styles
	add_action( 'wp_enqueue_scripts', 'dvin_qlist_scripts_styles' );
	
	require_once(DVIN_QLIST_PLUGIN_URL."class-dvin-wcql.php"); //core class
	require_once(DVIN_QLIST_PLUGIN_URL."class-dvin-wcql-ui.php");//ui related
	$dvin_wcql_obj = new Dvin_Wcql($_REQUEST);//create object
	//get all settings
	$dvin_wcql_settings = maybe_unserialize(get_option('dvin_wcql_settings'));	
	require_once("class-dvin-wcql-shortcodes.php"); //shortcodes inclusion
	add_action('init', 'dvin_wcql_initialize');
}
/* initializes the plugin **/
function dvin_wcql_initialize() {
		
	global $dvin_wcql_settings,$page,$post;
	//load plugin language file
		//include js file
	if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'languages/')) {
		$langdir_path = TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'languages/';
	}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'languages/')) {
		$langdir_path = STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'languages/';
	}else{
		$langdir_path =  dirname( plugin_basename( __FILE__ ) ). '/languages/';
	}
	load_plugin_textdomain( 'dvinwcql', false, $langdir_path ); 
	//initialize the seesion if not done yet
	if (!session_id()) {
        session_start();
    }	
	require_once('templates/add-to-quotelist.php');
	
	//replace Add to cart is choosen, means hide the cart button
	if(isset($dvin_wcql_settings['link_position']) && $dvin_wcql_settings['link_position']=='Replace Add To Cart') {
		add_action( 'woocommerce_after_shop_loop_item', 'dvin_qlist_remove_addtocart_button', 12);
	}
	
	//Display button on woocommerce shop page */
	if(isset($dvin_wcql_settings['show_on_shop']) && $dvin_wcql_settings['show_on_shop']=='on'){
		add_action( 'woocommerce_after_shop_loop_item', 'dvin_add_qlist_button_to_shop', 11);
	}
	if(isset($dvin_wcql_settings['no_price']) && $dvin_wcql_settings['no_price']=='on'){
		add_filter('woocommerce_get_price_html','dvin_qlist_noprice');
	}

	if(isset($dvin_wcql_settings['use_gravity_forms']) && isset($dvin_wcql_settings['gravity_form_select'])) {
		add_action("gform_pre_submission_".$dvin_wcql_settings['gravity_form_select'], "dvin_wcql_set_post_content", 10, 2);
		add_filter("gform_allowable_tags_".$dvin_wcql_settings['gravity_form_select'], "dvin_wcql_allow_basic_tags", 10, 3);
	}

	if(isset($dvin_wcql_settings['no_qty']) && $dvin_wcql_settings['no_qty']=='on'){
		add_action( 'woocommerce_after_shop_loop_item', 'dvin_remove_quantity_box', 13);
	}
}
function dvin_wcql_allow_basic_tags(){
	return true;
}
/**
* function dvin_add_qlist_button_to_shop, adds button to the products in the shop page
*/
function dvin_add_qlist_button_to_shop() {
	global $product;
    
	if($product->product_type == 'simple'){
		echo do_shortcode('[dvin-wcql-shopbutton product_id='.$product->id.']');
	}
	
}

function dvin_qlist_remove_addtocart_button() {
	 echo "<script>jQuery('.button.add_to_cart_button.product_type_simple').remove();</script>";
}
//register for installation hook
register_activation_hook( __FILE__, 'dvin_wcql_install' );
//function enques necessary scripts and styles
function dvin_qlist_scripts_styles() {
	wp_enqueue_style( 'dvin-wcql-stylesheet', plugins_url('/css/styles.css', __FILE__));
	wp_enqueue_style( 'dvin-wcql-custom_stylesheet', plugins_url('/css/custom_styles.css', __FILE__));
	wp_enqueue_style( 'dashicons' );
	
	//include js file
	if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'js/dvin_wcql.js')) {
		$js_path = get_template_directory_uri() . '/'. Dvin_Wcql::template_path().'js/dvin_wcql.js';
	}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'js/dvin_wcql.js')) {
		$js_path = get_stylesheet_directory_uri() . '/'. Dvin_Wcql::template_path().'js/dvin_wcql.js';
	}else{
		$js_path = plugins_url('/js/dvin_wcql.js', __FILE__);
	}
	
	wp_enqueue_script('dvin-wcql-js',$js_path,'jquery','','in_footer');
	
	echo "<script>var dvin_quotelist_count ='".dvin_get_wcql_count()."';var add_to_quotelist_ajax_url = '".add_to_quotelist_url()."';</script>";
	echo '<script>var dvin_wcql_plugin_ajax_prodfind_url = "'.add_query_arg('action','prod_find',DVIN_QLIST_PLUGIN_WEBURL."dvin-wcql-ajax.php").'";
			var login_redirect_url=\''.wp_login_url().'?redirect_to='.urlencode($_SERVER['REQUEST_URI']).'\';
		</script>';	
}
/**
 * function Dvin_Wcql_install, called at the action hook "register_activation_hook". Creates required tables and sets default preferences
 */
function Dvin_Wcql_install() {
  global $wpdb; //wpdb var
		//create quotelist page
		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => 1,
			'post_name' => 'Quotelist',
			'post_title' => 'Request For a Quote',
			'post_content' => '[dvin-wcql-listing][dvin-wcql-form]',
			'comment_status' => 'closed'
		);
		$quotelistpage_id = wp_insert_post($page_data);
		update_option('dvin_quotelist_pageid', $quotelistpage_id);
	
		//for default settings, if exists, do not create
		if(get_option('dvin_wcql_email_subject')=='') {
			Dvin_Wcql_default_settings();			 //set default settings (excludes email settings)
			update_option('dvin_wcql_email_subject','Sharing [%req_name%] quotelist');
		
			//if Dvin_Wcql_email_msg not exists 
			if(get_option('dvin_wcql_email_msg')=='') {
				update_option('dvin_wcql_email_msg','Hello,
				Following is the quote list of the [%req_name%]:
				[%quotelist%]
                               Comments: [%comments%]
Thanks,
[%req_name%]');
				}//end if
		}//end if
}//end of function
/** function Dvin_Wcql_default_settings - sets the default settings for the plugin options **/
function Dvin_Wcql_default_settings() {
	$settings_arr = array();
	$settings_arr['link_position'] = 'After Add to Cart';
	$settings_arr['link_bgcolor'] = '#000000';
	$settings_arr['link_fontcolor'] = '#FFFFFF';
	$settings_arr['link_sameas_addtocart_default_colors'] = 'on';
	$settings_arr['redirect_to_cart'] = '';
	$settings_arr['link_sameas_addtocart'] = 'on';
	$settings_arr['show_quotelist_link_always'] = 'on';
	$settings_arr['custom_css'] = '.addquotelistlink{}';
	update_option('dvin_wcql_settings',maybe_serialize($settings_arr));
	//update the custom_styles file
	file_put_contents(DVIN_QLIST_PLUGIN_URL."css/custom_styles.css",$settings_arr['custom_css']);
}
/**
 * function dvin_get_wcql_count, rteurn the number of products in quotelist
 *@return integer number of products in quotelist
 */
function dvin_get_wcql_count() {
	if(isset($_SESSION['dvin_qlist_products'])) {
		foreach($_SESSION['dvin_qlist_products'] as $key=>$value)
			if($key == '')
				unset($_SESSION['dvin_qlist_products'][$key]);

		return count($_SESSION['dvin_qlist_products']);

	}
}
//called by deactivate hook, do thye necessary stuff
function Dvin_Wcql_deactivate() {	
	//delete pages created for this plugin
	wp_delete_post(get_option('dvin_quotelist_pageid'),true);
}
/**
* function add_to_quotelist_url, Builds add to quotelist URL
* @string URL 
 */
function add_to_quotelist_url() {
	return DVIN_QLIST_PLUGIN_WEBURL."dvin-wcql-ajax.php";
}
/** Adds quotelist button/link to the single product page based on position selection**/
function dvin_addquotelist_button($shopbutton=false,$product_id='') {
	
	global $product,$dvin_wcql_obj;
	
	//start buffer
	ob_start();
	//if not a shop button
	if(!$shopbutton) {
	
	//if product type is variable, get link based on it
	if($product->product_type !='variable') {
		//set the product ID
		$product_id = $product->id;
		echo  Dvin_Wcql_UI::get_qlist_link($dvin_wcql_obj->get_url(),$product->product_type,$dvin_wcql_obj->isExists($product->id, ''));
	}else {
		echo  Dvin_Wcql_UI::get_qlist_link($dvin_wcql_obj->get_url(),$product->product_type,false);
	}
		//have message popup to display the messages	
		echo  Dvin_Wcql_UI::get_message_poupup_container(250,190);
	}
	else {
		echo  Dvin_Wcql_UI::get_qlist_shoplink($dvin_wcql_obj->get_url(),'simple',$dvin_wcql_obj->isExists($product->id, ''),$product_id);
	}
	echo ob_get_clean();
}
/** removes the  Add to Cart button **/
function dvin_remove_addtocart_button() {
	echo "<script>jQuery('.single_add_to_cart_button').remove();</script>";
}

//removes the quantity selector or box
function dvin_remove_quantity_box() {
	echo "<script>jQuery('.input-text.qty.text').remove();</script>";
}

function dvin_qlist_quantity_input( $input_name, $input_value, $args = array(), $product = null, $echo = true ) {
		if ( is_null( $product ) )
			$product = $GLOBALS['product'];
		$defaults = array(
			'input_name'  	=> $input_name,
			'input_value'  	=> $input_value,
			'min_value'		=> 1,
			'max_value'	 => $product->get_stock_quantity(),
			'min_value'  	=> apply_filters( 'woocommerce_quantity_input_min', '1', $product ),
			'step' 			=> apply_filters( 'woocommerce_quantity_input_step', '1', $product )
		);
		ob_start();
		//template for the quantity
		if(function_exists('wc_get_template')) {//works for WC 2.1+
			wc_get_template( 'global/quantity-input.php', $defaults );
		} else {//works for WC 2.0
			woocommerce_get_template( 'single-product/add-to-cart/quantity.php', $defaults );
		}
		
		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}
	function dvin_qlist_noprice() {
		return '';
	}
	 function dvin_wcql_set_post_content($form){
	 	$fields = $form['fields'];
	 	foreach($fields as $arr){
	 		if($arr['defaultValue']=='[quotelisttable]')
	 			$_POST['input_'.$arr['id']] = get_qlist_table();
	 	}
	 	$_SESSION['dvin_qlist_products']=array();
	 }
 
	function gf_quotelist($value){
	    return do_shortcode('[dvin-wcql-listing]');
	}
	function get_qlist_table() {

		
		global $product,$dvin_wcql_settings;
		$quote_list ='<table cellspacing="3" cellpadding="3" style="border: 1px solid black;"><tr><td>'.__('Product','dvinwcql').'</td><td>'.__('Product Name','dvinwcql').'</td>';
		$row_str = '<tr><td align="left"><a href="%s">%s</a></td><td align="left">%s</td>';

		//hide qty column if set
		if($dvin_wcql_settings['no_qty'] != 'on') {
			$quote_list .='<td>'.__('Quantity','dvinwcql').'</td>';
			$row_str .='<td align="left">%s</td>';
		}

		//hide price column if set
		if($dvin_wcql_settings['remove_price_col'] != 'on') {
			$quote_list .='<td>'.__('Unit Price','dvinwcql').'</td><td>'.__('Total Price','dvinwcql').'</td>';
			$row_str .='<td align="left">%s</td><td align="left">%s</td>';
		}

		$quote_list .='</tr>';
		$row_str .='</tr>';
		
		$qlist = array();
		if(isset($_SESSION['dvin_qlist_products'])) {
			$qlist_count[0]['cnt'] = count($_SESSION['dvin_qlist_products']);
			$qlist = $_SESSION['dvin_qlist_products'];
		}
		
		foreach($qlist as $values) {
			
			
			//initialize to avoid notices
			if(!isset($values['variation_id']))
				$values['variation_id']=0; 
			
			if (isset($values['add-to-quotelist']) && is_numeric($values['add-to-quotelist'])) {
			
				$values['prod_id'] = $values['ID'] = $values['add-to-quotelist'];
				$values['ID'] =  $values['add-to-quotelist'];
			
			}else{
					$values['prod_id'] = $values['ID'] = $values['product_id'];
					if(!empty($values['variation_id']))
						$values['ID'] = $values['product_id'].'_'.$values['variation_id'];						
			}
			
			if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
				 // WC 2.0
				$product_obj = !empty($values['variation_id'])?get_product($values['variation_id'],array('parent_id'=>$values['product_id'])): get_product($values['prod_id']);
			}else{
				if(!empty($values['variation_id'])) {
					$product_obj = new WC_Product_Variation($values['variation_id'],$values['product_id']);
				}else{
					$product_obj = new WC_product($values['prod_id']);
				}
			}
			if ($product_obj->exists()) {
				$image_str = $product_obj->get_image();
				$href_str = esc_url( get_permalink(apply_filters('woocommerce_in_cart_product', $values['prod_id'])) );
			}
			
			$product_name_str = apply_filters('woocommerce_in_cartproduct_obj_title', $product_obj->get_title(), $product_obj);
			if(!empty($values['variation_id'])){
				$product_name_str .= woocommerce_get_formatted_variation(unserialize($values['variation_data']),false);
			}
			//if only set the price as visible NOT remove
			if($dvin_wcql_settings['remove_price_col'] != 'on') {
				if(get_option('woocommerce_display_cart_prices_excluding_tax')=='yes') {
					$unit_price_str = apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $product_obj->get_price_excluding_tax() ), $values, '' ); 
					$unit_price = $product_obj->get_price_excluding_tax();
				}else {
					$unit_price_str = apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $product_obj->get_price() ), $values, '' );
					$unit_price = $product_obj->get_price();
				}	
				
				//decide the quantity
				$quantity = isset($_POST['qty'][$product_obj->id])?$_POST['qty'][$product_obj->id]:$values['quantity'];	
			
				$total_price = (float) $unit_price * (int)$quantity;
				$total_price_str = apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $total_price)); 
			} else {
				//decide the quantity
				$quantity = isset($_POST['qty'][$product_obj->id])?$_POST['qty'][$product_obj->id]:$values['quantity'];	
			}
			
								
			
			//handle hiiden elements/columns
			if($dvin_wcql_settings['no_qty'] != 'on' && $dvin_wcql_settings['remove_price_col'] != 'on') {
				$quote_list .= sprintf($row_str,$href_str,$image_str,$product_name_str,$quantity,$unit_price_str,$total_price_str );
				$overall_tot_price += $total_price;
			}else if($dvin_wcql_settings['remove_price_col'] != 'on') {
				$quote_list .= sprintf($row_str,$href_str,$image_str,$product_name_str,$unit_price_str,$total_price_str );
				$overall_tot_price += $total_price;
			}else if($dvin_wcql_settings['no_qty'] != 'on') {
				$quote_list .= sprintf($row_str,$href_str,$image_str,$product_name_str,$quantity);
			} else {
				$quote_list .= sprintf($row_str,$href_str,$image_str,$product_name_str);
			}					
		}
					
		$quote_list .='</table>';
		return $quote_list;
	}
?>