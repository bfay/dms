( function( $ ) {

	$codeglow_modal = $('#codeglow_modal');
	$codeglow_clone = $('#codeglow_content');
	
	$wp_post_title  = $('#title');
	$wp_content     = $('#content');

	var codeglow = new Object;
	// initial backup value
	codeglow.backup = $wp_content.val();

	// add quicktags
	QTags.addButton( 'codeglow_modal_btn', 'Code Glow', codeglow_editor_btn );
	QTags.addButton( 'codeglow_undo_btn', 'Undo', codeglow_undo_btn );

	function codeglow_editor_btn() {
		$codeglow_modal.modal( window.codeglowModalConfig );
	}
	function codeglow_undo_btn() {
		$wp_content.val( codeglow.backup );
	}

	/*  Modal Actions  */

	/**
	 * Show
	 * Update stored pre-fancy-editor content from wp editor, and set codeglow textarea content
	 */
	$codeglow_modal.on('show', function () {

		// update backup
		codeglow.backup = $wp_content.val();
		
		// update editor
		codeglow_modal_editor.setValue( codeglow.backup );

		// update title
		codeglow.postTitle = ( $wp_post_title.val().length ) ? $wp_post_title.val() : '<em style="color:silver;">Untitled</em>';

		// set the title		
		$codeglow_modal
			.find('.modal-title')
			.html(codeglow.postTitle);

		// disable content from scrolling behind modal
		$('body').css('overflow','hidden');
	});

	/**
	 * Shown
	 * Refresh fancy editor
	 */
	$codeglow_modal.on('shown', function () {
		codeglow_modal_editor.refresh();
	});


	/**
	 * Hide
	 * Transfer from fancy to WP textarea
	 */
	$codeglow_modal.on('hide', function () {
		$wp_content.val( codeglow_modal_editor.getValue() );
		$('body').css('overflow','visible');
	});

})(jQuery);