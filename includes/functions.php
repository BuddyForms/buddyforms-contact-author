<?php

add_action( 'buddyforms_the_loop_after_actions', 'buddyforms_contact_author_the_loop_actions' );
function buddyforms_contact_author_the_loop_actions( $post_id ) {
	global $buddyforms;

	$form_slug = get_post_meta( $post_id, '_bf_form_slug', true );


	if ( isset( $buddyforms[ $form_slug ]['contact_author'] ) ) {

		echo '<ul class="edit_links">';
			echo '<li>';
				buddyforms_contact_author_post( $post_id, $form_slug );
			echo '</li>';
		echo '</ul>';

	}

}