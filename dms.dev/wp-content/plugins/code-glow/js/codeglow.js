+function( $ )
{

$(document).ready(function()
{	
	if ( 'object' !=  typeof codeglow_editors )
		return;

	$.each( codeglow_editors, function ( handle, edtr )
	{	
		$target = $( edtr.selector );
		// add editor vars to global scope
		if ( $target.length && !$target.hasClass('mirrored') )
		{
			//console.log( 'CodeGlow >>> ' + edtr.selector );
			window[ edtr.var_name ] = CodeMirror.fromTextArea( $target.addClass('mirrored').get(0), edtr.config );
		}
	});
});

}( window.jQuery );