<?php
global $dvin_wcql_link_positions;
?>
 <form id='settingsform' name='settingsform' method='POST'>
 <input type='hidden' name='action' id='action' value='preview_update'> <!--used for ajax call action -->
	<input type='hidden' name='plugin_weburl' id='plugin_weburl' value='<?php echo DVIN_QLIST_PLUGIN_WEBURL?>'>
		<table class='widefat dvin_table' cellpadding='5' cellspacing='2' width='100%'>
				<thead>
				<tr>
					<td colspan='2' style='text-align:left;'><h1><?php _e('General Settings','dvinwcql')?></h1></td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><input type='checkbox' name='show_on_shop'/></td>
					<td><?php _e('Show "Add to Quotelist" on Products list page','dvinwcql');?></td>								
				</tr>
				<tr>
					<td><?php _e('Position','dvinwcql');?>:</td>
					<td><select name="link_position" id="link_position"  style="height:30px;">
					<?php
						foreach($dvin_wcql_link_positions as $key => $val) {?>
								<option value='<?php echo $key?>'><?php _e($key,'dvinwcql');?></option>
					<?php } ?>
								<option value='useshortcode'><?php _e('Use Shortcode','dvinwcql');?></option>
							</select>
					<br/><?php _e('If you want to position quotelist link on the single page --> Select position of "Add to Quotelist" link/button on single product page','dvinwcql');?></td>
				</tr>
				<thead>
				<tr>
					<td colspan='2' style='text-align:left;'><h3><?php _e('Add to quotelist link settings','dvinwcql')?></h3></td>
				</tr>
				</thead>
				<tr>
					<td><input type='checkbox' name='link_sameas_addtocart'/></td>
					<td><?php _e('Show "Add to Quotelist" as a button instead of a link','dvinwcql')?></td>								
				</tr>
					<tr>
					<td><input type='checkbox' name='link_sameas_addtocart_default_colors'/></td>
					<td><?php _e('Default to theme colors (or leave unchecked and select custom colors below)','dvinwcql')?></td>								
				</tr>
				<tr>
					<td><?php _e('Change Background Color','dvinwcql');?>:</td>
					<td><input type="text" id="link_bgcolor" name="link_bgcolor" value="<?php echo $link_bgcolor;?>"  maxlength="7" />
					<div   id="link_bgcolorpicker"></div><br/>
					<?php _e('"Default to theme colors" must be unchecked','dvinwcql');?>
					</td>							
				</tr>
				<tr>
					<td><?php _e('Change Font Color','dvinwcql');?>:</td>
					<td><input type="text" id="link_fontcolor" name="link_fontcolor" value="<?php echo $link_fontcolor;?>"  maxlength="7" />
					<div   id="link_fontcolorpicker"></div><br/>
					<?php _e('"Default to theme colors" must be unchecked','dvinwcql');?>
					</td>							
				</tr>
				<tr>
					<td colspan='2' style='text-align:left;'><h3><?php _e('Other Settings','dvinwcql')?></h3></td>
				</tr>
				<tr>
					<td><input type='checkbox' name='no_price'/></td>
					<td><?php _e('Hide Price from Shop and Product Pages','dvinwcql');?></td>								
				</tr>
				<tr>
					<td><input type='checkbox' name='remove_price_col'/></td>
					<td><?php _e('Remove/Hide Price column from Request For Quote Page','dvinwcql')?></td>								
				</tr>
				<tr>
					<td><input type='checkbox' name='no_qty'/></td>
					<td><?php _e('Remove/Hide Quantity from Product Pages and Request For Quote Page','dvinwcql')?></td>								
				</tr>
				<tr>
					<td colspan='2' style='text-align:left;'><h3><?php _e('Use Third Party Forms and Email','dvinwcql')?></h3></td>
				</tr>
				<tr>
					<td colspan='2' style='text-align:left;'><span style="color:red;"><?php _e('Note:Form and Email setiings has to be  handled from Third Party Pluign itself.','dvinwcql')?></span></td>
				</tr>
				<tr>
					<td><input type='checkbox' name='use_gravity_forms'/></td>
					<td><?php _e('Use Gravity Forms','dvinwcql')?> - <select name='gravity_form_select' id='gravity_form_select'>
						<option value=''>Select Form</option>
						<?php
						if (class_exists('RGFormsModel')) {
							$forms = RGFormsModel::get_forms( null, 'title' );
						} else {
							$forms = array();
						}
						foreach($forms as $form) {?>
								<option value='<?php echo $form->id?>'><?php echo $form->title;?></option>
					<?php } ?>
					</select></td>								
				</tr>
				<tr>
					<td colspan='2' style='text-align:left;'>
					<ul>
						<li>
						1)<?php _e('Please remove [dvin-wcql-listing] shortcode in "Request For Quote" Page.','dvinwcql')?>
						</li>
						<li>
						2)<?php _e('Must add [dvin-wcql-listing] shortcode as one of the HTML elements in Gravity Form where you want to show the listing.','dvinwcql')?>
						</li>
						<li>
						3)<?php _e('Must add hidden field name of "quotelisttable" with value of  "[quotelisttable]" shortcode as one of the HTML elements in Gravity Form.','dvinwcql')?>
						</li>
					</ul>
					</td>
				</tr>
				<thead>
				<tr>
					<td colspan='2' style='text-align:left;'><h3><?php _e('Custom CSS','dvinwcql')?></h3></td>
				</tr>
				<tr>
					<td colspan='2' style='text-align:left;'><textarea cols="100%" rows="10" name="custom_css"><?php echo $custom_css;?></textarea><br/>
					<?php _e('Use addquotelistlink class to style the button/link e.g. for alignment, float:left, float:right etc.','dvinwcql');?>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<center><input class='button button-primary' type="submit" id="updateSettings" name="updateSettings" value="Update" />&nbsp;<input class='button button-secondary' type="submit" id="resettodefaultsettings" name="resettodefaultsettings" value="<?php _e('Reset to Default Settings','dvinwcql');?>" /></center>
					</td>
				</tr>
				</thead>
				<tbody>
		</table>
			
			
		<div id="lefttinycontainer">
			<div id="div_preview"></div>
		</div>
</form>
<!-- script to select the appropriate values in dropdown boxes -->
<script type='text/javascript'>
jQuery("#link_position").val('<?php echo $link_position;?>');
jQuery("#gravity_form_select").val('<?php echo $gravity_form_select;?>');
<?php
if( isset($link_sameas_addtocart) && $link_sameas_addtocart == "on") {?>
jQuery('input[name=link_sameas_addtocart]').attr('checked', true);
<?php }
if( isset($link_sameas_addtocart_default_colors) && $link_sameas_addtocart_default_colors == "on") {?>
jQuery('input[name=link_sameas_addtocart_default_colors]').attr('checked', true);
<?php }
if(isset($show_on_shop) && $show_on_shop == "on") {?>
jQuery('input[name=show_on_shop]').attr('checked', true);
<?php }
if(isset($remove_price_col) && $remove_price_col == "on") {?>
jQuery('input[name=remove_price_col]').attr('checked', true);
<?php } 
if(isset($no_price) && $no_price == "on") {?>
jQuery('input[name=no_price]').attr('checked', true);
<?php } 
if(isset($use_gravity_forms) && $use_gravity_forms == "on") {?>
jQuery('input[name=use_gravity_forms]').attr('checked', true);
<?php }
if(isset($no_qty) && $no_qty == "on") {?>
jQuery('input[name=no_qty]').attr('checked', true);
<?php } ?>
</script>