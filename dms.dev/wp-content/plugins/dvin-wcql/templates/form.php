<?php
 global $woocommerce,$dvin_wcql_obj;
 ?>
<script>
var  empty_productlist_msg = '<?php _e('Please select products to send.','dvinwcql');?>';
</script>
<div>
<p>
<div id='dvin_messages' align="left" style="color:red">&nbsp;</div>
<table>
<tr>
	<td colspan="5">
		<div id="req_error_msgs"></div>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td><font color="red">*</font><?php _e('Name','dvinwcql')?>:</td>
				<td><input type="text" name="req_name" id="req_name" value="<?php if(isset($_POST['req_name'])) { echo $_POST['req_name'];}?>"/></td>									
			</tr>
			<tr>
				<td><font color="red">*</font><?php _e('Email','dvinwcql')?>:</td>
				<td><input type="text" name="req_email" id="req_email" value="<?php if(isset($_POST['req_email'])) { echo $_POST['req_email'];}?>"/></td>																		
			</tr>
			<tr>
				<td><?php _e('Additional details/comments','dvinwcql')?>:</td>
				<td><textarea cols="40" rows="5" name="req_details" id="req_details"><?php if(isset($_POST['req_details'])) { echo $_POST['req_details'];}?></textarea></td>																		
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align="right"><input name="requestquote_submit" id="requestquote_submit" type="button" value="<?php _e('Request a Quote','dvinwcql');?>" onClick="call_ajax_submitform_to_admin('<?php echo DVIN_QLIST_PLUGIN_WEBURL.'dvin-wcql-ajax.php';?>')" /><img style="display: none;border:0; width:16px; height:16px;" src="<?php echo DVIN_QLIST_PLUGIN_WEBURL;?>images/ajax-loader.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..."></td>
			</tr>
		</table>
	</td>
</tr>
</table>
</div>
</form> <!-- close the form -->
</p>
</div><!-- close of Dvin_Wcql_details DIV -->