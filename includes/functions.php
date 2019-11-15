<?php

add_action( 'buddyforms_the_loop_after_actions', 'buddyforms_contact_author_the_loop_actions' );
function buddyforms_contact_author_the_loop_actions( $post_id ) {
	global $buddyforms;

	$form_slug = get_post_meta( $post_id, '_bf_form_slug', true );

	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
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
	$emailBody .= 'noch was drann pappen';

	$permalink = get_permalink( $post_id );
	$code      = sha1( $post_id . time() );

	$complete_offer_link = add_query_arg( array(
		'bf_offer_complete_request' => $post_id,
		'key'                       => $code,
		'nonce'                     => buddyforms_create_nonce( 'buddyform_bf_offer_complete_request_keys', $post_id )
	), $permalink );

	$emailBody .= ' Set the offer to completed: <a href="' . $complete_offer_link . '">Set offer to complete</a>';


	return $emailBody;
}

add_filter( 'buddyforms_blocks_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );
add_filter( 'buddyforms_shortcode_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );

function buddyforms_blocks_the_loop_post_status($post_status, $form_slug){
	$post_status['completed'] = 'completed';
	return $post_status;
}


/**
 * Add 'completed' post status.
 */
function buddyforms_contact_author_post_status(){
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


// Hook into the the-loop.php template and add an new table head
add_action('buddyforms_the_thead_th_after_title', 'my_buddyforms_the_thead_tr_inner_last', 10, 2);
function my_buddyforms_the_thead_tr_inner_last($post_id, $form_slug){
	// Check if this is the correct form.
	// Change "FORM_SLUG" to your form
	if($form_slug != 'post-form-2'){
		return;
	}
	// Add a label to the table change 'Label' to your needs
	?><th class="title"><span><?php _e( 'Date', 'buddyforms' ); ?></span></th><?php
	?><th class="title"><span><?php _e( 'Text', 'buddyforms' ); ?></span></th><?php
}


// Add the td to the table row with the value of the post meta
add_action('buddyforms_the_table_td_after_title_last', 'my_buddyforms_the_table_tr_last', 10, 2);
function my_buddyforms_the_table_tr_last($post_id, $form_slug) {
	// Check if this is the correct form.
	// Change "FORM_SLUG" to your form
	if ( $form_slug != 'post-form-2' ) {
		return;
	}
	// Get the post meta by the form element slug
	// Change the "SLUG" to the form element slug.
	$date = get_post_meta( $post_id, 'date', true );
	$text = get_post_meta( $post_id, 'Text', true );

	// Display the td with the value
	echo '<td>' . $date . '</td>';
	echo '<td>' . $text . '</td>';
}