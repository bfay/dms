<?php
/**
 * Dvin_Wcql class, Handles the core Quotelist functionality
 */
 class Dvin_Wcql {
 
	public $errors_arr; //stores the errors
	public $details_arr; //stores details array
	public $messages_arr; //stores details array
	
	/**
	* Constructor initializes the variables with respective values
	*@access public
	*/
	public function __construct($details_arr) {
		$this->errors_arr = array();
		$this->details_arr = $details_arr;
	}
	
	/**
   * function isExists, checks for existence of the product
	 * @access  public
	 * @boolean
   */	
	public function isExists($prod_id, $variation_id) {
	
		global $wpdb;
		//if variation_id is null
		if($variation_id =='')
			$variation_id="''";	
		$cnt = 0; //initialize with zero
		if(isset($_SESSION['dvin_qlist_products'])) {
			$variation_id = $variation_id == "''"?'':$variation_id;
			if(array_key_exists ( rtrim($prod_id.'_'.$variation_id,'_'), $_SESSION['dvin_qlist_products']))
				$cnt = 1;
			else
				$cnt = 0;
		}
		
		//execute and return the boolean based on its existence
		if($cnt>0)
			return true;
		return false;
		
	}
	
	/**
   * function remove, removes product from quotelist based on its ID
	 * @access  public
	 * @boolean 
   */	
	public function remove($id) {
	
			unset($_SESSION['dvin_qlist_products'][$id]);
			return true;
	}	
	
	/**
	* function get_url, Builds the quotelist page URL
	* @access  public
	* @return string URL 
  */		
	public static function get_url() {
		return add_query_arg('page_id', get_option('dvin_quotelist_pageid'),home_url());
	}
	
	
	/**
	* function get_remove_url, Builds the generic URL which handles removal of product from quotelist
	* @access  public
	* @string URL 
  */	
	public static function get_remove_url($id) {
		return add_query_arg('action','remove_from_quotelist',add_query_arg('qlist_item_id', $id,DVIN_QLIST_PLUGIN_WEBURL."dvin-wcql-ajax.php"));
	}
	
	/**
	* function add_to_quotelist_url, Builds add to quotelist URL
	* @access  public
	* @string URL 
  */
	public function add_to_quotelist_url() {
			
		return DVIN_QLIST_PLUGIN_WEBURL."dvin-wcql-ajax.php";
	}
/**
	* function add, Adds product to quotelist
	* @access  public
	* @return string "error" or "true"  , "error" indicates, error occurred
  */
	public function add() {
	
		global $wpdb,$woocommerce;
		
		//handle simple product
		if (!isset($this->details_arr['variation_id']) && !isset($this->details_arr['product_id'])) :
		//single product
			$quantity = (isset($this->details_arr['quantity'])) ? (int) $this->details_arr['quantity'] : 1;
			
			// check for existence,  product ID, variation ID, variation data, and other cart item data
			if($this->isExists( $this->details_arr['add-to-quotelist'], '')) {
				return "exists";
			}
			
			//add to session
			$_SESSION['dvin_qlist_products'][$this->details_arr['add-to-quotelist']]['add-to-quotelist'] = $this->details_arr['add-to-quotelist'];
			$_SESSION['dvin_qlist_products'][$this->details_arr['add-to-quotelist']]['quantity'] = $quantity;
			$ret_val = true;
			
			return "true";
			
	
		elseif (isset($this->details_arr['variation_id']) && isset($this->details_arr['product_id'])) :
			
			// Variation add to cart
			if (empty($this->details_arr['variation_id']) || !is_numeric($this->details_arr['variation_id'])) :
				
				$this->errors_arr[]= __('Please choose product options&hellip;', 'dvinwcql');
				return "error";
		
		   else :
				
				$product_id 	= (int) $this->details_arr['product_id'];
				$variation_id 	= (int) $this->details_arr['variation_id'];
				$quantity 		= (isset($this->details_arr['quantity'])) ? (int) $this->details_arr['quantity'] : 1;
				
				$attributes = (array) maybe_unserialize(get_post_meta($product_id, '_product_attributes', true));
				$variations = array();
				$all_variations_set = true;
				foreach ($attributes as $attribute) :
					if ( !$attribute['is_variation'] ) continue;
					$taxonomy = 'attribute_' . sanitize_title($attribute['name']);
					if (!empty($this->details_arr[$taxonomy])) :
						// Get value from post data
						$value = esc_attr(stripslashes($this->details_arr[$taxonomy]));
						// Use name so it looks nicer in the cart widget/order page etc - instead of a sanitized string
						$variations[esc_attr($attribute['name'])] = $value;
					else :
						$all_variations_set = false;
					endif;
				endforeach;
				if ($all_variations_set && $variation_id > 0) :
					
					
					// check for existence,  product ID, variation ID, variation data, and other cart item data
					if($this->isExists( $product_id, $variation_id)) {
						return "exists";
					}
					//push the variable data into session
					$this->details_arr['variation_data'] = serialize($variations);
					$_SESSION['dvin_qlist_products'][$product_id.'_'.$variation_id]=$this->details_arr;
					$ret_val = true;
				
					//if added, return true, otherwise error
					if($ret_val) :
						return "true";
					else :
						$this->errors_arr[]= __('Error occurred while adding product to quotelist','dvinwcql');
						return "error";
					endif;
				else :
					$this->errors_arr[]= __('Please choose product options&hellip;', 'dvinwcql') ;
					return "error";
				endif;
				
			endif; 
		
		endif;
	}
	
	/**
	* function send_email, Sends Email
	* @access  public static
	* @return boolean 
  */
	public static function send_email() {
		
		global $dvin_wcql_settings;
		$overall_tot_price = 0;
		$email = get_option('dvin_wcql_admin_email');
		$subject = get_option('dvin_wcql_email_subject');
		$message = get_option('dvin_wcql_email_msg');
		$cc = get_option('dvin_wcql_copy_toreq');
		
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
				$quantity = isset($_POST['qty['.$product_obj->product_id.']'])?$_POST['qty['.$product_obj->product_id.']']:$values['quantity'];	
			
				$total_price = (float) $unit_price * (int)$quantity;
				$total_price_str = apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $total_price)); 
			} else {
				//decide the quantity
				$quantity = isset($_POST['qty['.$product_obj->product_id.']'])?$_POST['qty['.$product_obj->product_id.']']:$values['quantity'];	
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
		$overall_tot_price_str = isset($overall_tot_price_str)?apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $overall_tot_price)):'';			
		
		$needle_arr = array('[%req_name%]','[%req_email%]','[%quotelist%]','[%total_price%]','[%comments%]');
		$replace_with_arr = array(ucwords($_POST['req_name']),$_POST['req_email'],$quote_list,$overall_tot_price_str,$_POST['req_details']);
		
		//apply filters  to capture custom fileds names in [%filed_name%]
		$needle_arr = apply_filters('dvin_wcwl_custom_fields_needles',$needle_arr);
		
		//apply filters for 
		$replace_with_arr = apply_filters('dvin_wcwl_custom_fields_replacements',$replace_with_arr);

		//include the validation file if exists
		if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'custom-field-arr.php')) {
			include(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'custom-field-arr.php');
		}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'custom-field-arr.php')) {
			include( STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'custom-field-arr.php');
		}

		$subject = str_replace($needle_arr,$replace_with_arr,$subject);
		$message = str_replace($needle_arr,$replace_with_arr,$message);
		$message ='<html><body><style>table, th, td{border: 1px solid black;}</style>'.$message.'</body></html>';
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		
		// Additional headers
		$to_admin_headers = $headers.'From: '.$_POST['req_email']."\r\n";
		//check for whether to send copy to customer
		if($cc == "on"){
			$to_customer_headers = $headers.'From: '.$email."\r\n";
		}
		
		//send the email 
		if(wp_mail($email, $subject, nl2br($message),$to_admin_headers)) {
			if($to_customer_headers!=''){
				if(wp_mail($_POST['req_email'], $subject, nl2br($message),$to_customer_headers)){
					$_SESSION['dvin_qlist_products']=array();
					return true;
				}							
			} else {
				$_SESSION['dvin_qlist_products']=array();
				return true;
			}
		}

		return false;
	}
	
		
	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public static function template_path() {
		return apply_filters( 'DVIN_QLIST_TEMPLATE_PATH', 'dvin-wcql/');
	}
 }
 ?>