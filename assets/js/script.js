/* global o2HoverCards */
( function( wp, o2, $ ) {
	// Bail if not set
    if ( typeof o2HoverCards === 'undefined' ) {
        return;
	}

	// The list of fetched pointers.
	var pointers = [];

	var showHoverCard = function( event ) {
		var $link = $( event.currentTarget ), slug = $link.html(),
		    template = o2.Utilities.Template( 'hovercard' );

		if ( ! slug ) {
			return event;
		}

		var hoverCardContent = template( { loader: o2HoverCards.loader } );
		if ( pointers[ slug ] ) {
			hoverCardContent = template( pointers[ slug ] );
		}

		$link.pointer( {
			content: hoverCardContent,
			pointerClass: 'o2-hovercard',
			pointerWidth: 500,
			position: {
				edge:'top',
				offset: '-25 0'
			}
		} ).pointer( 'open' );

		if ( ! pointers[ slug ] ) {
			wp.apiRequest( {
				method: 'POST',
				dataType: 'json',
				url: o2.options.readURL + '&method=query',
				data: {
					callback: 'hovercards',
					slug: slug,
					nonce: o2HoverCards.nonce
				}
			} ).done( function( response ) {
				pointers[ slug ] = response.data;

				$link.pointer( {
					content: template( pointers[ slug ] )
				} ).pointer( 'update' );
			} ).fail( function( error ) {
				$link.pointer( {
					content: template( error.responseJSON.data )
				} ).pointer( 'update' );
			} );
		}
	};

	var hideHoverCard = function( event ) {
		var $link = $( event.currentTarget ), slug = $link.html();

		if ( ! pointers[ slug ] ) {
			return event;
		}

		setTimeout( function() {
			$link.pointer().pointer( 'close' );
		 }, 3000 );
	};

	o2.getHoverCards = function() {
		$( '.o2-hovercardify a').hoverIntent( {
			over: showHoverCard,
			out: hideHoverCard
		} );
	};

	$( document ).ready( o2.getHoverCards );
	$( 'body' ).on( 'pd-script-load', o2.getHoverCards );

} )( window.wp || {}, window.o2 || {}, jQuery );
