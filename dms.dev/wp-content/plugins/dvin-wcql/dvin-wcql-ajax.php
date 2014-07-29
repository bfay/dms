<?php
//assign add-to-cart to add-to-quotelist
$_POST['add-to-quotelist'] = !isset($_POST['add-to-quotelist'])?$_REQUEST['add-to-cart']:$_POST['add-to-quotelist'];
//remove unecessary attributes
$_REQUEST['add-to-cart']= $_POST['add-to-cart']=$_GET['add-to-cart']='';
require_once( "../../../wp-load.php" );
//initialize the seesion if not done yet
if (!session_id()) {
	session_start();
}	
//create object, if it is not created
include_once(dirname( __FILE__ )."/class-dvin-wcql.php"); //core class
$dvin_wcql_obj = new Dvin_Wcql($_REQUEST);
$_REQUEST['action'] = isset($_REQUEST['action'])?$_REQUEST['action']:'';
//take necessary action
switch($_REQUEST['action']) {
	case 'remove':
								
								if(isset($_REQUEST['variation_id']))
									$product_id = $_REQUEST['product_id'].'_'.$_REQUEST['variation_id'];
								else
									$product_id = $_REQUEST['add-to-quotelist'];
								
									//recreate obj and initialize
									if($dvin_wcql_obj->remove($product_id)) {
										_e("Successfully removed","dvinwcql");
									} else {
										_e("Error Occured","dvinwcql");
									}
									
									//based on count, displat no products message 
									$count = dvin_get_wcql_count();
									echo '##'.$count.'##';//seperate the message and total count with double-hash
									break;
					
	case 'remove_from_quotelist' :
									//recreate obj and initialize
									if($dvin_wcql_obj->remove($_GET['qlist_item_id'])) {
										_e("Successfully removed","dvinwcql");
									} else {
										_e("Error Occured","dvinwcql");
									}
									
									//based on count, displat no products message 
									$count = dvin_get_wcql_count();
									echo '##'.$count.'##';//seperate the message and total count with double-hash
									//if no products andd the message to the response	
									if($count <=0) {
										echo __('No Products added to quotelist',"dvinwcql");
									}
									break;
	case 'prod_find' :
								if($dvin_wcql_obj->isExists($_POST['prod_id'],$_POST['variation_id'])){
										echo "true";
								}else{
										echo "false";
								}
								break;
								
	case 'send_request' :
								//update the quantities in session
								foreach($_POST['qty'] as $key => $qty) {
										$_SESSION['dvin_qlist_products'][$key]['quantity']=(int)$qty;
								}
								
								//now prepare and send email
								dvin_qlist_sendreq_func();
								break;
	case 'add_to_qlist' :
	default:
								//add to quotelist
								$ret_val = $dvin_wcql_obj->add();
								if($ret_val=="true") {
									echo __("Successfully added","dvinwcql").'##'.dvin_get_wcql_count();
								}elseif($ret_val=="exists") {
									echo __("Already Exists. Browse Quotelist.","dvinwcql");
								}elseif(count($dvin_wcql_obj->errors_arr)>0) {
									//display error
									echo $dvin_wcql_obj->errors_arr[0];
								}
								break;
	
}
//validates the form and sends the email
function dvin_qlist_sendreq_func() {
	$errors = array();
	
	//validate the form
	if(isset($_POST['req_name']) && trim($_POST['req_name'])==''){
		$errors[] = "Please enter ".__('Name','dvinwcql');		
	}
	if(isset($_POST['req_email']) && trim($_POST['req_email'])==''){
		$errors[] = "Please enter ".__('Email','dvinwcql');		
	} else if(isset($_POST['req_email'])) {
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 
		// Run the preg_match() function on regex against the email address
		if (!preg_match($regex, $_POST['req_email'])) {
			 $errors[] = "Please enter valid ".__('Email','dvinwcql');
		} 
	}
	//apply filters for to do validations for the custom fileds
	$errors = apply_filters('dvin_wcwl_custom_fields_validation',$errors);
	
	//include the validation file if exists
	if(file_exists(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'form-validation.php')) {
		include(TEMPLATEPATH . '/'. Dvin_Wcql::template_path().'form-validation.php');
	}elseif(file_exists(STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'form-validation.php')) {
		include( STYLESHEETPATH . '/'. Dvin_Wcql::template_path().'form-validation.php');
	}
	
	if(count($errors)>0){
		echo 'error##'.join($errors,'<br/>');
		exit;
	}
	//if no products in the list
	if(isset($_SESSION['dvin_qlist_products']) && !empty($_SESSION['dvin_qlist_products'])) {
			//send an email to Admin
			if(Dvin_Wcql::send_email()) {
				echo 'success##'.__('Email Sent Successfully',"dvinwcql");
				$_SESSION['dvin_qlist_products'] = array();				
			}else{
				echo 'error##'.__('There was a problem sending your message. Please try again.',"dvinwcql");
			}
	}
	else {
			echo 'error##'.__('Please select products to send.',"dvinwcql");		
	}
	exit;
}
?>