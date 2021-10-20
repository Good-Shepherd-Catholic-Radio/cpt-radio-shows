( function( $ ) {

    $( document ).on( 'rbm-field-helpers-ready', function() {

        var $repeater = rbmFHgetFieldObject( 'rbm_cpts_radio_show_times', 'rbm_cpts', 'repeater' );

        $repeater.repeater.on( 'repeater-before-delete-item', function( event, $item ) {

            var $post_ids = $item.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids"] input' ),
                post_ids = $post_ids.val().trim();

            post_ids = ( post_ids.length == 0 ) ? {} : JSON.parse( post_ids );

            var $post_ids_to_delete = $item.find( '.fieldhelpers-field-hidden[data-fieldhelpers-name="post_ids_to_delete"] input' ),
                post_ids_to_delete = $post_ids_to_delete.val().trim();

            post_ids_to_delete = ( post_ids_to_delete.length == 0 ) ? {} : JSON.parse( post_ids_to_delete );

            // Make sure that we delete everything. They may have had pending deletions
            post_ids = Object.assign( post_ids, post_ids_to_delete );

            if ( typeof post_ids == 'object' ) {

                let post_ids_to_delete = $( '[name="rbm_cpts_radio_show_occurrences_to_delete"]' ).val().trim();

                post_ids_to_delete = post_ids_to_delete.split( ',' ).filter( Boolean );

                for ( var day in post_ids ) {

                    let post_id = post_ids[ day ]

                    post_ids_to_delete.push( post_id );

                }

                $( '[name="rbm_cpts_radio_show_occurrences_to_delete"]' ).val( post_ids_to_delete.join( ',' ) );

            }

        } );

    } );

} )( jQuery );