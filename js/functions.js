jQuery( function( $ ) {
	$( '.ec-iframe' ).load( function() {
		$( this ).height( $( this ).contents().height() );
	} );
} );
