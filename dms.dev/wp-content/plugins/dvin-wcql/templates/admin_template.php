<?php
global $dvin_wcql_link_positions;
?>
<div class="dvininterface">
	<!-- #dvin_header -->
	<div id="dvin_header">
		<div class="logo">
			<img src="<?php echo DVIN_QLIST_PLUGIN_WEBURL.'images/admin_logo.png';?>" alt="Dvin" />
		</div>
		<div class="panelinfo">
			<span class="themename"><?php echo $dvin_wcql_plugin_data['Name']; ?> Plugin</span>
			<span class="themename"><?php _e('Plugin Version','dvinwcql');?> : <?php  echo $dvin_wcql_plugin_data['Version']; ?></span>
		</div>
	</div>
	<div id="dvin_content_wrap">
	 <?php
	 //if updated successfully , display the message
	 if(isset($_GET['updated']) && $_GET['updated']=='true'){
		echo "<span class='status'><h3><font color='green'>"._e('Updated Successfully','dvinwcql')."</font></h3></span>";	
	 } 
	?>
		<div id="dvinqlisttabs">
			<ul>
				<li><a href="#dvinqlisttabs-1">Settings</a></li>
				<li><a href="#dvinqlisttabs-2">Email</a></li>
			</ul>
			<div id="dvinqlisttabs-1">
				<?php Dvin_Wcql_Admin::dvin_settings_page(); ?>
			</div>
			<div id="dvinqlisttabs-2">
				<?php Dvin_Wcql_Admin::dvin_emailsettings_page(); ?>
			</div>
		</div>
	</div>
</div>