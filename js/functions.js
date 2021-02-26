jQuery( function( $ ) {
	$( '.emc-iframe' ).load( function() {
		$( this ).height( $( this ).contents().height() );
	} );
} );
