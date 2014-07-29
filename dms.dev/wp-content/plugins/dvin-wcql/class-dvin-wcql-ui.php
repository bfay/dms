<?php
/**
 * Dvin_Wcql_UI class, Handles UI Quotelist
 */
 class Dvin_Wcql_UI {
 	/**
  * function get_message_poupup_container, builds the messagepopup container
	* @static
  */
	public static function get_message_poupup_container($left=250,$top=190) {
		return '<script>jQuery("body").prepend(\'<div id="dvin_messagecontainer">\
						<div id="dvin-message-popup" class="dvin-message-popup" style="display: none; ">\
						<div class="dvin-message" id="dvin-message"></div>\
						</div>\
						</div>\');</script>';
	}
/**
  * function get_qlist_link, builds the quotelist button/link to place it in single product page
	* @static
	* @return string HTML of quotelist button/link
  */
	public static function get_qlist_link($url,$prod_type,$prod_exists=false) {
		global $dvin_wcql_settings,$product;
		$label 	= apply_filters('add_to_quotelist_text', __('Add to Quote',"dvinwcql"));
		$qlistlink = '';
		$qlistlink ='<div class="addquotelistlink">';
		 $qlistlink .= '<div class="quotelistadd" ';
		if($prod_type =='variable' || $prod_exists) {
			$qlistlink .=' style="display:none;"';
		}

		//determine product ID value
		if($prod_type =='variable' ) {
			$product_id = 0;
		}

		//if Quotelist link appear as add to cart as appears in theme
		if($dvin_wcql_settings['link_sameas_addtocart'] == 'on') {
			$style=""; //defines or stores the style 
			/** if not default colors **/
			if($dvin_wcql_settings['link_sameas_addtocart_default_colors'] != 'on') {
				//handle background color
				if($dvin_wcql_settings['link_bgcolor']!=''){
					$style.='background:'.$dvin_wcql_settings['link_bgcolor'].';';
				}
				//handle font color
				if($dvin_wcql_settings['link_fontcolor']!=''){
					$style .=' color:'.$dvin_wcql_settings['link_fontcolor'].';';
				}
				$style = $style!=''?' style="'.$style.'"':'';
			}//end if
				$qlistlink .= '><div style="float:left;"><button type="button" class="button alt" onClick="call_ajax_add_to_quotelist(add_to_quotelist_ajax_url,'.$product->id.');" '.$style.'>'.$label.'</button></div>';	
		} else { //display as normal link
			$qlistlink .='><a href="javascript:void(0);" onClick="call_ajax_add_to_quotelist(add_to_quotelist_ajax_url,'.$product->id.');">'.$label.'</a>';
		}
		//common code to both instances
		$qlistlink .='<div style="flaot:left;"><img style="display: none;border:0; width:16px; height:16px;" src="'.DVIN_QLIST_PLUGIN_WEBURL.'images/ajax-loader.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..."/></div></div><div class="quotelistaddedbrowse" style="display:none;">';
		$qlistlink .='<a class="quotelist-added-icon"  href="'.$url.'">'.__('Product Added to Quote',"dvinwcql").'</a><br/><a class="quotelist-remove-icon"  href="javascript:void(0)" onClick="remove_item_from_dpage();">'.__('Remove from Quote',"dvinwcql").'</a></div>';
		$display_style = $prod_exists?'block;':'none;';
		//exists
		$qlistlink .='<div class="quotelistexistsbrowse" style="display:'.$display_style.'">';
		$qlistlink .='<a class="quotelist-added-icon"  href="'.$url.'">'.__('Product Added to Quote',"dvinwcql").'</a><br/><a class="quotelist-remove-icon"  href="javascript:void(0)" onClick="remove_item_from_dpage();">'.__('Remove from Quote',"dvinwcql").'</a></div>';
		$qlistlink .='<div style="clear:both"></div><div class="quotelistaddresponse"></div></div>';		
		//clear
		$qlistlink .='<div class="clear"></div>';
		$qlistlink ='<script>document.write(\''.$qlistlink.'\');</script>';
		return $qlistlink;
	}	
	/**
  	* function get_qlist_link, builds the quotelist button/link to place it in single product page
	* @static
	* @return string HTML of quotelist button/link
  	*/
	public static function get_qlist_shoplink($url,$prod_type,$prod_exists=false, $prod_id='') {
		global $dvin_wcql_settings;
		$label 	= apply_filters('add_to_quotelist_text', __('Add to Quote',"dvinwcql"));
		$qlistlink = '';
	
		$qlistlink ='<div id="'.$prod_id.'" class="addquotelistlink">';
		 $qlistlink .= '<div class="quotelistadd" ';
		if($prod_type =='variable' || $prod_exists) {
			$qlistlink .=' style="display:none;"';
		}
		//if Quotelist link appear as add to cart as appears in theme
		if($dvin_wcql_settings['link_sameas_addtocart'] == 'on') {
			$style= ''; //stores the styles
			/** if not default colors **/
			if($dvin_wcql_settings['link_sameas_addtocart_default_colors'] != 'on') {
				//handle background color
				if($dvin_wcql_settings['link_bgcolor']!=''){
					$style.='background:'.$dvin_wcql_settings['link_bgcolor'].';';
				}
				//handle font color
				if($dvin_wcql_settings['link_fontcolor']!=''){
					$style .=' color:'.$dvin_wcql_settings['link_fontcolor'].';';
				}		
			}//end if
			$style = $style!=''?' style="'.$style.'"':'';
			$qlistlink .='><span style="float:left"><a href="javascript:void(0);" class="button add_to_cart_button addquotelistbutton" onClick="call_ajax_add_to_quotelist_from_shop(add_to_quotelist_ajax_url,'.$prod_id.');" '.$style.'>'.$label.'</a></span>';
		} else { //display as normal link
			$qlistlink .='><span style="float:left"><a href="javascript:void(0);" class="button add_to_cart_button addquotelistbutton" onClick="call_ajax_add_to_quotelist_from_shop(add_to_quotelist_ajax_url,'.$prod_id.');">'.$label.'</a></span>';
		}
		
		//common code to both instances
		$qlistlink .='<span style="float:left"><img style="display: none;border:0; width:16px; height:16px;" src="'.DVIN_QLIST_PLUGIN_WEBURL.'images/ajax-loader.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..."/></span></div><div class="quotelistaddedbrowse" style="display:none;">';
		//$qlistlink .='<a class="quotelist-remove-icon"  href="javascript:void(0)" onClick="remove_item_from_shop('.$prod_id.');">'.__('Quote',"dvinwcql").'</a></div>';
		$qlistlink .='<a href="'.$url.'">'.__('In Quotelist',"dvinwcql").'</a></div>';
		$display_style = $prod_exists?'block;':'none;';
		//exists
		$qlistlink .='<div class="quotelistexistsbrowse" style="display:'.$display_style.';">';
		$qlistlink .='<a href="'.$url.'">'.__('In Quotelist',"dvinwcql").'</a></div>';
		$qlistlink .='<div style="clear:both"></div><div class="quotelistaddresponse"></div></div>';		
		//clear
		$qlistlink .='<div class="clear"></div>';
		$qlistlink ='<script>document.write(\''.$qlistlink.'\');</script>';
		return $qlistlink;
	}
 }//end of class
 ?>