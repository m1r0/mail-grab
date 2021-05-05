jQuery( function( $ ) {
	$( window ).load( function() {
		var $iframe = $( '.mlgb-body-iframe' );

		$iframe.height(
			$iframe.contents().height()
		);
	} );
} );
