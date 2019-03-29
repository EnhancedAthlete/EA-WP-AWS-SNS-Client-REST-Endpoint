(function( $ ) {
	'use strict';

	$(function() {

		$( '.ea-wp-sns-confirm' ).on( 'click', function( event ) {

			event.preventDefault();

			var data = {
				action: 'ea_aws_sns_confirm_subscription',
				subscription_topic: this.id
			};

			var button = $( this );

			$.ajax({url: ajaxurl,
				method: 'POST',
				data: data,
				success: function( response ) {

					var parsed_response = JSON.parse( response );

					button.parent().parent().removeClass( 'notice-info' );

					if( parsed_response.error != null ) {
						button.parent().parent().addClass( 'notice-error' );
					} else {
						button.parent().parent().addClass( 'notice-success' );
					}

					button.parent().html( parsed_response.message );

				},
				error: function() {

					button.parent().parent().removeClass( 'notice-info' );
					button.parent().parent().addClass( 'notice-error' );

					button.parent().html( 'Unknown error occurred.' );
				}
			});
		});


		$( '.ea-wp-sns-dismiss' ).on( 'click', function( event ) {

			event.preventDefault();

			var data = {
				action: 'ea_aws_sns_dismiss_subscription',
				subscription_topic: this.id
			};

			var button = $( this );

			$.ajax({url: ajaxurl,
				method: 'POST',
				data: data,
				success: function( response ) {

					var parsed_response = JSON.parse( response );

					button.parent().parent().removeClass( 'notice-info' );

					if( parsed_response.error != null ) {
						button.parent().parent().addClass( 'notice-error' );
					} else {
						button.parent().parent().addClass( 'notice-success' );
					}

					button.parent().html( parsed_response.message );

				},
				error: function() {

					button.parent().parent().removeClass( 'notice-info' );
					button.parent().parent().addClass( 'notice-error' );

					button.parent().html( 'Unknown error occurred.' );
				}
			});
		});


	});

})( jQuery );