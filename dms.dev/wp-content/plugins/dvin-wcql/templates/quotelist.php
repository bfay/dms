<?php
/**
 * Quotelist Page
 */
 global $woocommerce,$dvin_wcql_settings, $current_user,$dvin_wcql_obj;
?>
<div id='dvin_message' align="left" style="color:red">&nbsp;</div>
<div id="dvin_wcql_success_msg">
	<?php _e('Sent email to Admin for the quote.','dvinwcql');?>
</div>
<div id="dvin_wcql_details">
<?php 
 if ( defined( 'WC_VERSION' ) && WC_VERSION ):
	wc_print_notices();
 else:
	$woocommerce->show_messages();
endif;
 ?>
<p>
<div id="content">
<?php if($dvin_wcql_settings['use_gravity_forms']!='on') { ?>
<form method="post"  class="qlist" action="dvin-qlist-sendreq">
<?php } ?>
	<input type='hidden' name='qlist_process' value='true'/>
	<input type='hidden' name='action' value='send_request'/>
<table class="shop_table cart">
	<thead>
		<tr>
			<th class="product-remove"></th>
			<th class="product-thumbnail"></th>
			<th class="product-name"><span class="nobr"><?php _e('Product Name', 'dvinwcql'); ?></span></th>
			<?php if(isset($dvin_wcql_settings['remove_price_col'] ) && $dvin_wcql_settings['remove_price_col'] != 'on') { ?>
			<th class="product-price"><span class="nobr"><?php _e('Unit Price', 'dvinwcql'); ?></span></th>
			<?php } 
			if(isset($dvin_wcql_settings['no_qty'] ) && $dvin_wcql_settings['no_qty'] != 'on') { ?>
			<th><span class="nobr"><?php _e('Quantity','dvinwcql'); ?></span></th>
			<?php } ?>

		</tr>
	</thead>
	<tbody>
		<?php	

		
		if (sizeof($qlist)>0) : 
		//loop through the list
		foreach ($qlist as $key => $values) :
			
		if($key =='')
			continue;

			//check for validay of the entry/product
			if(!isset($values['add-to-quotelist']) && !isset($values['variation_id']))
				continue;
			//initialize to avoid notices
			if(!isset($values['variation_id']))
						$values['variation_id']=0; 
						
			if (isset($values['product_id']) && isset($values['variation_id'])) {
				if(isset($values['product_id']))
					$values['prod_id'] = $values['ID'] = $values['product_id'];
				if(!empty($values['variation_id']))
					$values['ID'] = $values['product_id'].'_'.$values['variation_id'];
			} else{
				$values['prod_id'] = $values['ID'] = $values['add-to-quotelist'];
				$values['ID'] =  $values['add-to-quotelist'];
			}
			
			if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) :							
				 // WC 2.0
				$product_obj = !empty($values['variation_id'])?get_product($values['variation_id'],array('parent_id'=>$values['product_id'])): get_product($values['prod_id']);
			else:
				if(!empty($values['variation_id'])) :
					$product_obj = new WC_Product_Variation($values['variation_id'],$values['product_id']);
				else:
					$product_obj = new WC_product($values['prod_id']);
				endif;
			endif;	
				
				if ($product_obj->exists()) :
					
					?>
					<tr id="<?php echo $values['ID']?>_dvin_rowid">
						<td><div  style="cursor:pointer;" class="quotelist-remove-icon-listing" onClick="remove_item_from_qlist('<?php echo esc_url( $dvin_wcql_obj->get_remove_url($values['ID']))?>','<?php echo $values['ID']?>_dvin_rowid');"><a href="javascript:void(0)" title="<?php _e('Remove this item','dvinwcql'); ?>"></a></div></td>
						<td class="product-thumbnail">
							<a href="<?php echo esc_url( get_permalink(apply_filters('woocommerce_in_cart_product', $values['prod_id'])) ); ?>">
							<?php 
								echo $product_obj->get_image();
							?>
							</a>
						</td>
						<td class="product-name">
							<a href="<?php echo esc_url( get_permalink(apply_filters('woocommerce_in_cart_product', $values['prod_id'])) ); ?>"><?php echo apply_filters('woocommerce_in_cartproduct_obj_title', $product_obj->get_title(), $product_obj); ?></a>
							<?php
								if(!empty($values['variation_id'])):
									echo woocommerce_get_formatted_variation(unserialize($values['variation_data']),false);
								endif;							
							?>
						</td>
						<?php if( isset($dvin_wcql_settings['remove_price_col']) &&  $dvin_wcql_settings['remove_price_col'] != 'on') { ?>
						<td class="product-price">
						<?php 
						
							if (get_option('woocommerce_display_cart_prices_excluding_tax')=='yes') :
								echo apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $product_obj->get_price_excluding_tax() ), $values, '' ); 
							else :
								echo apply_filters('woocommerce_cart_item_price_html', woocommerce_price( $product_obj->get_price() ), $values, '' ); 
							endif;
							
						?></td>
						<?php }//end of checking remove price or not
						if(isset($dvin_wcql_settings['no_qty'] ) && $dvin_wcql_settings['no_qty'] != 'on') { ?>
						<td class="woocommerce" valign="center">
							<?php echo str_replace('number','text',dvin_qlist_quantity_input('qty['. $values['ID'].']',$values['quantity'],array(),$product_obj,false)); ?>
						</td>
						<?php } ?>
					</tr>					
					<?php
				else:
					continue;
				endif;
			endforeach; 
		else :
			$colspan = 5;
			//calculate the colspan
			$colspan = $dvin_wcql_settings['remove_price_col'] != 'on'?$colspan:$colspan-1;
			$colspan = $dvin_wcql_settings['no_qty'] != 'on'?$colspan:$colspan-1;
		?>
		<tr>
		<td colspan="<?php echo $colspan;?>"><center><?php _e('No products were added to list','dvinwcql') ?></center></td>
		</tr>
		<?php
		endif;
		?>
	</tbody>
</table>
</div>
</p>