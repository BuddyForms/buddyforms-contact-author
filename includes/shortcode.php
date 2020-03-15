<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function buddyforms_contact_author_shortcode() {
	$post_id = get_the_ID();
	if ( ! empty( $post_id ) ) {
		$form_slug = buddyforms_get_form_slug_by_post_id( $post_id );
		if ( ! empty( $form_slug ) ) {
			global $buddyforms;
			if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
				return '';
			}

			remove_shortcode( 'bf-contact-author' );

			ob_start();
			echo '<div class="buddyforms_contact_author_action_container">';
			buddyforms_contact_author_post( $post_id, $form_slug );
			echo '</div>';
			$content = ob_get_clean();

			add_shortcode( 'bf-contact-author', 'buddyforms_contact_author_shortcode' );
			return $content;
		}
	}

	return '';
}

add_shortcode( 'bf-contact-author', 'buddyforms_contact_author_shortcode' );
