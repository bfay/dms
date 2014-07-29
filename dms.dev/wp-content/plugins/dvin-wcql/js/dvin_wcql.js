//handles ajax call add to quotelist from product details page

function call_ajax_add_to_quotelist(url,prod_id) {

	

	//check for variable or single product
	if(jQuery('input[name="add-to-cart"]').length >0 && jQuery('input[name="product_id"]').length >0) { //variable product
			serializedcart_data = jQuery('.cart').serialize();
	} else if(jQuery('input[name="add-to-cart"]').length >0 && jQuery('.cart').length >0){ //single product and form exists with cart class
			serializedcart_data = jQuery('.cart').serialize();
	} else if(jQuery('input[name="add-to-cart"]').length == 0) {
			serializedcart_data = 'quantity=1&add-to-cart='+prod_id;
	}
	
	/*	//if no proce set, product becomes non-purchasable, so there will not be form. need to get the prod id
		if(jQuery('.cart').serialize() =='')
			serializedcart_data = 'quantity=1&add-to-cart='+prod_id;
		else
			serializedcart_data = jQuery('.cart').serialize();
	*/

	jQuery('.ajax-loading-img').fadeIn();

	jQuery.ajax({

	 type: 'POST',

	  url: url,

	  data: serializedcart_data,

	  success: function( response ) {

					var msg = jQuery('#dvin-message-popup');

					var loading = jQuery('.ajax-loading-img');

					loading.fadeOut();  

					response_arr = response.split("##");

					//if div exists, display count

					if(jQuery('#dvin-quotelist-count').length > 0) {

						jQuery('#dvin-quotelist-count').html(response_arr[1]);

					}

					jQuery('.quotelistadd').css('display','none');

					jQuery('.quotelistaddedbrowse').css('display','block');

		}

	});

}

//handles ajax call add to quotelist 

function call_ajax_add_to_quotelist_from_shop(url,prod_id) {

	jQuery('#'+prod_id).children('.ajax-loading-img').fadeIn();

	jQuery.ajax({

	 type: 'POST',

	  url: url,

	  data: 'add-to-cart='+prod_id+'&action=add_to_qlist',

	  success: function( response ) {

					var loading = jQuery('#'+prod_id).children('.ajax-loading-img');

					loading.fadeOut();  

					response_arr = response.split("##");

					//if div exists, display count

					if(jQuery('#dvin-quotelist-count').length > 0) {

						jQuery('#dvin-quotelist-count').html(response_arr[1]);

					}

					jQuery('#'+prod_id).children('.quotelistadd').css('display','none');

					jQuery('#'+prod_id).children('.quotelistaddedbrowse').css('display','block');

		}

	});

}

//handles ajax call add to quotelist 

function remove_item_from_shop(prod_id) {

	jQuery.ajax({

	 type: 'POST',

	  url: add_to_quotelist_ajax_url,

	  data: 'add-to-quotelist='+prod_id+'&action=remove',

	  success: function( response ) {

	  

					response_arr = response.split("##");

					//if div exists, display count

					if(jQuery('#dvin-quotelist-count').length > 0) {

						jQuery('#dvin-quotelist-count').html(response_arr[1]);

					}

					jQuery('#'+prod_id).children('.quotelistadd').css('display','block');

					jQuery('#'+prod_id).children('.quotelistaddedbrowse').css('display','none');

					jQuery('#'+prod_id).children('.quotelistexistsbrowse').css('display','none');

		}

	});

}

//handles ajax call post form

function call_ajax_submitform_to_admin(url) {

	

	//if list has no products

	if(jQuery('.dvin_no_prod_qlist').length >0){

		alert(empty_productlist_msg);

		return false;

	}

jQuery('.ajax-loading-img').fadeIn();

	jQuery.ajax({

	 type: 'POST',

	  url: url,

	  data: jQuery('.qlist').serialize(),

	  success: function( response ) {

					var msg = jQuery('#dvin-message-popup');

				var loading = jQuery('.ajax-loading-img');

					loading.fadeOut();  

					response_arr = response.split("##");

					if(response_arr[0]=='error'){

						jQuery('#dvin_messages').html(response_arr[1]);

					}else {

						jQuery('#dvin_wcql_details').css('display','none');

						jQuery('#dvin_wcql_success_msg').css('display','block');

						jQuery('#dvin_messages').html('&nbsp;');

						jQuery('html, body').animate({ scrollTop: 0 }, 'slow');

					}

					

					msg.fadeIn();

					window.setTimeout(function(){

					   msg.fadeOut(); 

					}, 2000);

		}

	});

}

//when variatioon_id changes, take appropriate action on quotelist button (show/hide)

jQuery('[name|="variation_id"]').change(function() {

	if(jQuery('[name|="variation_id"]').val()!='') {

	//find whether it is already exists in quotelist

		jQuery.ajax({

		 type: 'POST',

			url: dvin_wcql_plugin_ajax_prodfind_url,

			data: 'prod_id='+jQuery('[name|="product_id"]').val()+'&variation_id='+jQuery('[name|="variation_id"]').val(),

			success: function( response ) {

				if(response == "true") {

					jQuery('.quotelistadd').css('display','none');

					jQuery('.quotelistexistsbrowse').css('display','block');

					jQuery('.quotelistaddedbrowse').css('display','none');

				} else {

					jQuery('.quotelistadd').css('display','block');

					jQuery('.quotelistexistsbrowse').css('display','none');

					jQuery('.quotelistaddedbrowse').css('display','none');

				}

			}

		});

		jQuery('.quotelistaddresponse').html('');

	}

	

	});

	

//handles Ajax request to remove item from list

function remove_item_from_dpage() {

	jQuery.ajax({

	 type: 'POST',

	  url: add_to_quotelist_ajax_url+'?action=remove',

	  data: jQuery('.cart').serialize(),

	  success: function( response ) {

	  

					response_arr = response.split("##");

					//if div exists, display count

					if(jQuery('#dvin-quotelist-count').length > 0) {

						jQuery('#dvin-quotelist-count').html(response_arr[1]);

					}

					jQuery('.quotelistadd').css('display','block');

					jQuery('.quotelistaddedbrowse').css('display','none');

					jQuery('.quotelistexistsbrowse').css('display','none');

		}

	});

}

//handles ajax request on removing product from quotelist

function remove_item_from_qlist(url,rowid) {

	

	jQuery('#dvin_messages').html('&nbsp;');

	jQuery.ajax({

	 type: 'GET',

	  url: url,

	  success: function( response ) {

			if(rowid !=0) {

				jQuery("#"+rowid).remove();	

				arr = response.split('##');

				jQuery('#dvin_message').html(arr[0]);

				

				//if div exists reflect the total count

				if(jQuery('#dvin-quotelist-count').length>0) {

					jQuery('#dvin-quotelist-count').html(arr[1]);

				}

				//display no products message, if exists

				if(arr[2]!='') {

					jQuery('.cart').append('<tr class="dvin_no_prod_qlist"><td colspan="6"><center>'+arr[2]+'</center></td></tr>');	

				}

			}

	

			}

	

	});

}

//handles ajax request on adding product to cart from quotelist 

function add_tocart_fromqlist(url) {

	jQuery('#dvin_messages').html('&nbsp;');

	jQuery.ajax({

	 type: 'GET',

	  url: url,

	  success: function( response ) {

			jQuery('#dvin_messages').html(response);

		}

	});

}

//Update Message popup

jQuery.fn.center = function () {

 this.css("position","absolute");

    this.css("top", ((jQuery(window).height() - this.outerHeight()) / 2) + 

                                                jQuery(window).scrollTop() + "px");

    this.css("left", ((jQuery(window).width() - this.outerWidth()) / 2) + 

                                                jQuery(window).scrollLeft() + "px");

    return this;

}

//center the message popup

jQuery('#dvin-message-popup').center();

jQuery('.dvin_popup').center();

jQuery(window).scroll(function() { 

	jQuery('#dvin-message-popup').center();

});

jQuery(document).ready(function() {

	//if list has some products

	if(jQuery('#dvin-quotelist-count').length>0) {

		//if DIV for count exists, update it

		jQuery('#dvin-quotelist-count').html(dvin_quotelist_count);

	} 

				

});