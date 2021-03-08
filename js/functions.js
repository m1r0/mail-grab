jQuery( function( $ ) {
	$( window ).load( function() {
		var $iframe = $( '.emc-iframe' );

		$iframe.height(
			$iframe.contents().height()
		);
	} );
} );
