<?php

add_action( 'buddyforms_front_js_css_after_enqueue', 'buddyforms_contact_author_needs_assets', 10, 2 );

function buddyforms_contact_author_needs_assets( $content, $form_slug ) {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}
	if ( empty( $form_slug ) ) {
		return;
	}

	global $buddyforms;

	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
		return;
	}

	$needs_assets = isset( $buddyforms[ $form_slug ]['contact_author'] );
	BuddyFormsContactAuthor::setNeedAssets( $needs_assets, $form_slug );
}

add_action( 'wp_footer', 'buddyforms_contact_author_include_assets' );

function buddyforms_contact_author_include_assets() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}
	if ( BuddyFormsContactAuthor::getNeedAssets() ) {
		wp_enqueue_script( 'buddyforms-contact-author-script', BUDDYFORMS_CONTACT_AUTHOR_ASSETS . 'js/script.js', array( 'jquery', 'buddyforms-datatable' ), BuddyFormsContactAuthor::getVersion() );
		wp_localize_script( 'buddyforms-contact-author-script', 'buddyformsContactAuthor', array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( __DIR__ . 'buddyforms-contact-author' ),
			'language' => apply_filters( 'buddyforms_contact_author_language', array(
				'contact_author' => __( 'Contact the Author', 'buddyforms-contact-author' ),
			) )
		) );
		wp_enqueue_style( 'buddyforms-contact-author-style', BUDDYFORMS_CONTACT_AUTHOR_ASSETS . 'css/style.css', array(), BuddyFormsContactAuthor::getVersion() );
		add_thickbox();
	}
}

add_action( 'wp_ajax_buddyforms_contact_author', 'buddyforms_contact_author' );
add_action( 'wp_ajax_nopriv_buddyforms_contact_author', 'buddyforms_contact_author' );

function buddyforms_contact_author() {
	try {
		if ( ! ( is_array( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			die();
		}
		if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) || empty( $_POST['form_slug'] ) ) {
			die();
		}
		if ( ! wp_verify_nonce( $_POST['nonce'], __DIR__ . 'buddyforms-contact-author' ) ) {
			die();
		}

		if ( ! isset( $_POST['post_id'] ) ) {
			echo __( 'There has been an error sending the message!', 'buddyforms-contact-author' );
			die();
		}
		if ( ! isset( $_POST['contact_author_email_from'] ) ) {
			echo __( 'Please enter a valid email address', 'buddyforms-contact-author' );
			die();
		}

		$post_id = intval( $_POST['post_id'] );

		$form_slug = "buddyforms_contact_author_post_" . $post_id;
		if ( Form::isValid( $form_slug ) ) {

		} else {
			echo __( 'Please enter a valid email address', 'buddyforms-contact-author' );
			die();
		}

		$form_slug_parent = get_post_meta( $post_id, '_bf_form_slug', true );

		$from_email = sanitize_text_field( $_POST['contact_author_email_from'] );

		$post       = get_post( $post_id );
		$post_title = $post->post_title;
		$postperma  = get_permalink( $post->ID );

		$user_info = get_userdata( $post->post_author );

		$usernameauth  = $user_info->user_login;
		$user_nicename = $user_info->user_nicename;
		$first_name    = $user_info->user_firstname;
		$last_name     = $user_info->user_lastname;

		$blog_title  = get_bloginfo( 'name' );
		$siteurl     = get_bloginfo( 'wpurl' );
		$siteurlhtml = "<a href='$siteurl' target='_blank' >$siteurl</a>";

		$post_link_html = ! empty( $postperma ) ? sprintf( '<a href="%s" target="_blank">%s</a>', $postperma, $postperma ) : '';

		$mail_to = $user_info->user_email;
		$subject = sanitize_text_field( $_POST['contact_author_email_subject'] );

		$emailBody = sanitize_text_field( $_POST['contact_author_email_message'] );

		global $buddyforms;

		$emailBody = apply_filters( 'buddyforms_contact_author_message_text', $emailBody, $post->ID, $form_slug_parent );

		$short_codes_and_values = array(
			'[user_login]'                => $usernameauth,
			'[user_nicename]'             => $user_nicename,
			'[first_name]'                => $first_name,
			'[last_name]'                 => $last_name,
			'[published_post_link_plain]' => $postperma,
			'[published_post_link_html]'  => $post_link_html,
			'[published_post_title]'      => $post_title,
			'[site_name]'                 => $blog_title,
			'[site_url]'                  => $siteurl,
			'[site_url_html]'             => $siteurlhtml,
			'[form_elements_table]'       => buddyforms_mail_notification_form_elements_as_table( $form_slug_parent, $post ),
		);

		// If we have content let us check if there are any tags we need to replace with the correct values.
		if ( ! empty( $emailBody ) ) {
			$emailBody = stripslashes( $emailBody );
			$emailBody = buddyforms_get_field_value_from_string( $emailBody, $post->ID, $form_slug_parent );

			foreach ( $short_codes_and_values as $shortcode => $short_code_value ) {
				$emailBody = buddyforms_replace_shortcode_for_value( $emailBody, $shortcode, $short_code_value );
			}
		} else {
			if ( isset( $buddyforms[ $form_slug_parent ]['form_fields'] ) ) {
				$emailBody = $short_codes_and_values['[form_elements_table]'];
			}
		}

		$emailBody = nl2br( $emailBody );

		if ( ! empty( $subject ) ) {
			$subject = stripslashes( $subject );
			$subject = buddyforms_get_field_value_from_string( $subject, $post->ID, $form_slug_parent );
			foreach ( $short_codes_and_values as $shortcode => $short_code_value ) {
				$subject = buddyforms_replace_shortcode_for_value( $subject, $shortcode, $short_code_value );
			}
		}

		$result = buddyforms_email( $mail_to, $subject, $from_email, $from_email, $emailBody, '', '' );

		if ( ! $result ) {
			wp_send_json( __( 'There has been an error sending the message!', 'buddyforms-contact-author' ) );
		}

		wp_send_json( '' );
	} catch ( Exception $ex ) {
		BuddyFormsContactAuthor::error_log( $ex->getMessage() );
	}
	die();
}

add_filter( 'buddyforms_datatable_action_html', 'buddyforms_contact_author_table_edit_column', 10, 5 );
function buddyforms_contact_author_table_edit_column( $action_html, $post_id, $form_slug, $fields, $entry_metas ) {
	global $buddyforms;

	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
		return $action_html;
	}

	if ( get_post_status( $post_id ) == 'completed' ) {
		return $action_html;
	}

	if ( isset( $buddyforms[ $form_slug ]['contact_author'] ) ) {
		ob_start();
		echo '<ul class="edit_links">';
		echo '<li>';
		buddyforms_contact_author_post( $post_id, $form_slug );
		echo '</li>';
		echo '</ul>';

		$action_html = ob_get_clean();
		BuddyFormsContactAuthor::setNeedAssets( true, $form_slug );
	}

	return $action_html;
}

add_filter( 'buddyforms_contact_author_message_text', 'buddyforms_contact_author_message_text', 1, 3 );
function buddyforms_contact_author_message_text( $emailBody, $post_id, $form_slug ) {
	$home_page = home_url();
	$lading_page = apply_filters('buddyforms_contact_author_landing_url', $home_page, $post_id, $form_slug);

	$complete_offer_link = add_query_arg( array(
		'bf_offer_complete_request' => $post_id,
		'key'                       => wp_create_nonce( 'buddyforms_bf_offer_complete_request_keys' . __DIR__ )
	), $lading_page );

	$emailBody .= __( ' Set the offer to completed: ', 'buddyforms-contact-author' ) . '<a href="' . $complete_offer_link . '"> ' . __( 'Click here!', 'buddyforms-contact-author' ) . '</a>';

	return $emailBody;
}

add_filter( 'buddyforms_blocks_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );
add_filter( 'buddyforms_shortcode_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );

function buddyforms_blocks_the_loop_post_status( $post_status, $form_slug ) {
	$post_status['completed'] = 'completed';

	return $post_status;
}

add_action( 'init', 'buddyforms_contact_author_post_request' );

function buddyforms_contact_author_post_request() {
	try {
		if ( ! is_array( $_GET ) ) {
			return;
		}
		if ( ! isset( $_GET['key'] ) || ! isset( $_GET['bf_offer_complete_request'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_GET['key'], 'buddyforms_bf_offer_complete_request_keys' . __DIR__ ) ) {
			return;
		}

		$post_id = intval( $_GET['bf_offer_complete_request'] );

		wp_delete_post( $post_id, true );

		add_action( 'wp_head', 'buddyforms_contact_author_post_request_success' );
	} catch ( Exception $ex ) {
		BuddyFormsContactAuthor::error_log( $ex->getMessage() );
	}

	return;
}

function buddyforms_contact_author_post_request_success() {
	$complete_string = apply_filters( 'buddyforms_contact_author_complete_string', __( 'Offer is set to completed', 'buddyforms-contact-author' ) );
	?>
	<script>
		jQuery(document).ready(function() {
			alert('<?php esc_attr( $complete_string ) ?>');
			document.location.href = '/';
		});
	</script>
	<?php
}

/**
 * Add 'completed' post status.
 */
function buddyforms_contact_author_post_status() {
	register_post_status( 'completed', array(
		'label'                     => _x( 'Completed', 'buddyforms-contact-author' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
	) );
}

add_action( 'init', 'buddyforms_contact_author_post_status' );

