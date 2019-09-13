// noinspection JSUnresolvedVariable
( function( $ ) {

	$( '.display-posts-listing ' ).on( 'click', '.excerpt-more', function( e ) {

		e.preventDefault();

		const me = $( this );

		// Endpoint from wpApiSetting variable passed from wp-api.
		let endpoint = wpApiSettings.root + 'wp/v2/posts/';
		let item     = me.closest( '.listing-item' );
		let loading  = item.find( '.dps-arm-loading-overlay' );
		let meta     = item.find( '.dps-arm-post-meta' );

		$.ajax( {
			url:    endpoint + meta.data( 'post-id' ),
			method: 'GET',
			beforeSend: function( xhr ) {

				loading.css( 'display', 'block' );
			}
		} ).done( function( response ) {

			// console.log( response );
			me.closest( '.listing-item' ).find( '.excerpt' ).replaceWith( $( '<div class="post-content">' + response.content.rendered + '</div>') );
			loading.css( 'display', 'none' );

		} ).fail( function( response ) {

			if ( typeof response.responseJSON !== 'undefined' /*|| null !== response.responseJSON.message*/ ) {
				alert( response.responseJSON.message );
			}
		} );
	} );
} )( jQuery );
