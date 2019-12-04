<?php

add_action( 'buddyforms_the_loop_after_actions', 'buddyforms_contact_author_the_loop_actions' );
function buddyforms_contact_author_the_loop_actions( $post_id ) {
	global $buddyforms;

	$form_slug = get_post_meta( $post_id, '_bf_form_slug', true );

	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
		return;
	}

	if ( get_post_status( $post_id ) == 'completed' ) {
		return;
	}

	if ( isset( $buddyforms[ $form_slug ]['contact_author'] ) ) {

		echo '<ul class="edit_links">';
		echo '<li>';
		buddyforms_contact_author_post( $post_id, $form_slug );
		echo '</li>';
		echo '</ul>';

	}

}

add_filter( 'buddyforms_contact_author_message_text', 'buddyforms_contact_author_message_text', 1, 3 );

function buddyforms_contact_author_message_text( $emailBody, $post_id, $form_slug ) {

	$permalink = get_permalink( $post_id );
	$code      = sha1( $post_id . time() );

	$complete_offer_link = add_query_arg( array(
		'bf_offer_complete_request' => $post_id,
		'key'                       => $code,
		'nonce'                     => buddyforms_create_nonce( 'buddyform_bf_offer_complete_request_keys', $post_id )
	), $permalink );

	$emailBody .= __( ' Set the offer to completed: ', 'buddyforms' ) . '<a href="' . $complete_offer_link . '"> ' . __( 'Click here!', 'buddyforms' ) . '</a>';


	return $emailBody;
}

add_filter( 'buddyforms_blocks_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );
add_filter( 'buddyforms_shortcode_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );

function buddyforms_blocks_the_loop_post_status( $post_status, $form_slug ) {
	$post_status['completed'] = 'completed';

	return $post_status;
}


/**
 * Add 'completed' post status.
 */
function buddyforms_contact_author_post_status() {
	register_post_status( 'completed', array(
		'label'                     => _x( 'Completed', 'buddyforms' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
	) );
}

add_action( 'init', 'buddyforms_contact_author_post_status' );

