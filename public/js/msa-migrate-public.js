(function( $ ) {
	'use strict';


	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own wor k.
	 */

	$(document).ready(function() {


		/**
		 * Ajax load of table with users to be migrated with the given parameters in the migrate form.
		 */
		$('.button-list-users').click(function(){

			var uid = $('.username').val();
			var phase = $('.user-phase').val();
			var year = $('.user-period').val();
			var pathname = window.location.pathname;

			//Here we call the action function(declared en class-msa-migrate.php)
			var dataJSON = {
				'action': 'msa_list_users_action',
				'uid': uid,
				'phase': phase,
				'year': year,
			};

      $.ajax({
        method: "POST",
        url: wp_ajax.ajax_url,
        data: dataJSON,
      })
      .done(function( response ) {
        console.log('Successful AJAX Call! /// Return Data: ' + response);
        var mydata = JSON.parse( response );
        //$('.user-list-wrapper').append( mydata.message );
        window.location.replace( pathname +'/?uid='+ uid +'&phase='+ phase +'&period='+ year);
      });

			return false;
		});

		/**
		 * Ajax call to migrate user profile fields.
		 */
		$('.migrate-button').click(function() {

			var uid = $('.migrate-button').attr('data-uid');


			var dataJSON = {
      	'action': 'msa_migrate_profile_fields_action', 
      	'id': uid ,
			}; 

      $.ajax({
        method: "POST",
        url: wp_ajax.ajax_url,
        data: dataJSON,
      })
      .done(function( response ) {
        console.log('Successful AJAX Call! /// Return Data: ' + response);
        var mydata = JSON.parse( response );
        $('.profile-data-wrapper').slideToggle("slow");
        $('.ajax-profile-button').hide();
        $('.ajax-wrapper').text( 'Profile Info Migrated Successfully.' );
      });

			return false;

		});

		/**
		 * Ajax call to migrate user
		 */
		$('.migrate-user-button').click( function(event) {
			var uid = $('.migrate-user-button').attr('data-uid');
			$(this).closest('td').addClass('button-clicked');
			$(this).closest('td').parent('tr').children( 'td.column-migrated' ).addClass('new-value');

			var dataJSON = {
      	'action': 'msa_migrate_user_action', 
      	'id': uid ,
			};
			
      $.ajax({
        method: "POST",
        url: wp_ajax.ajax_url,
        data: dataJSON,
      })
      .done(function( response ) {
        console.log('Successful AJAX Call! /// Return Data: ' + response);
        var mydata = JSON.parse( response );
        $('.new-value').html(mydata.uid);
        $('.button-clicked .migrate-user-button').remove();
        $('.button-clicked .row-actions').html(mydata.profile_link);
      });

			event.preventDefault();
		});

	});

})( jQuery );

