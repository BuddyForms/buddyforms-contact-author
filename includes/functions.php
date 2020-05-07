<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

add_action( 'buddyforms_front_js_css_after_enqueue', 'buddyforms_contact_author_needs_assets', 10, 2 );


function buddyforms_contact_author_include_assets() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}
	if ( BuddyFormsContactAuthor::getNeedAssets() ) {
		wp_enqueue_script( 'buddyforms-contact-author-script', BUDDYFORMS_CONTACT_AUTHOR_ASSETS . 'js/script.js', array( 'jquery' ), BuddyFormsContactAuthor::getVersion() );
		wp_localize_script( 'buddyforms-contact-author-script', 'buddyformsContactAuthor', array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( __DIR__ . 'buddyforms-contact-author' ),
			'timeout'  => apply_filters( 'buddyforms_contact_author_timeout', 500 ),
			'redirect' => apply_filters( 'buddyforms_contact_author_redirect', '' ),
			'language' => apply_filters( 'buddyforms_contact_author_language', array(
				'contact_author'        => __( 'Contact the Author', 'buddyforms-contact-author' ),
				'error'                 => apply_filters( 'buddyforms_contact_author_invalid', __( 'There has been an error sending the message!', 'buddyforms-contact-author' ) ),
				'error_invalid_email'   => apply_filters( 'buddyforms_contact_author_invalid_email', __( 'Please enter a valid email address', 'buddyforms-contact-author' ) ),
				'error_invalid_subject' => apply_filters( 'buddyforms_contact_author_invalid_subject', __( 'Please enter a valid Subject', 'buddyforms-contact-author' ) ),
				'error_invalid_message' => apply_filters( 'buddyforms_contact_author_invalid_message', __( 'Please enter a valid Message', 'buddyforms-contact-author' ) ),
				'error_invalid_form'    => apply_filters( 'buddyforms_contact_author_invalid_form', __( 'Please check the form', 'buddyforms-contact-author' ) ),
				'popup_loading'         => apply_filters( 'buddyforms_contact_author_modal_loading_string', __( 'Loading...', 'buddyforms-contact-author' ) ),
				'popup_complete'        => apply_filters( 'buddyforms_contact_author_modal_complete_string', __( 'Complete', 'buddyforms-contact-author' ) ),
			) )
		) );
		wp_enqueue_style( 'buddyforms-contact-author-style', BUDDYFORMS_CONTACT_AUTHOR_ASSETS . 'css/style.css', array(), BuddyFormsContactAuthor::getVersion() );
		add_thickbox();
	}
}

add_action( 'wp_footer', 'buddyforms_contact_author_include_assets' );


function buddyforms_contact_author_unauthorized_field_type( $shortcodes, $form_slug, $element_name ) {
	if ( ! empty( $element_name ) && strpos( $element_name, 'contact_author_message_text' ) > 0 ) {
		$shortcodes[] = 'email';
		$shortcodes[] = 'user_email';
	}

	return $shortcodes;
}

add_filter( 'buddyforms_unauthorized_shortcodes_field_type', 'buddyforms_contact_author_unauthorized_field_type', 10, 3 );


function buddyforms_contact_author_process_shortcode( $string, $post_id, $form_slug ) {
	if ( ! empty( $string ) && ! empty( $post_id ) && ! empty( $form_slug ) ) {
		$post = get_post( $post_id );
		if ( ! empty( $post ) ) {
			$post_title = get_the_title( $post_id );
			$postperma  = get_permalink( $post_id );

			global $authordata;

			if ( ! empty( $authordata ) ) {
				$user_info = $authordata;
			} else {
				$user_id   = get_the_author_meta( 'ID' );
				$user_info = get_userdata( $user_id );
			}

			$usernameauth = '';
			if ( ! empty( $user_info->user_login ) ) {
				$usernameauth = $user_info->user_login;
			}
			$user_nicename = '';
			if ( ! empty( $user_info->user_nicename ) ) {
				$user_nicename = $user_info->user_nicename;
			}
			$first_name = '';
			if ( ! empty( $user_info->user_firstname ) ) {
				$first_name = $user_info->user_firstname;
			}
			$last_name = '';
			if ( ! empty( $user_info->user_lastname ) ) {
				$last_name = $user_info->user_lastname;
			}

			$post_link_html = ! empty( $postperma ) ? sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $postperma ), $postperma ) : '';

			$blog_title  = get_bloginfo( 'name' );
			$siteurl     = get_bloginfo( 'wpurl' );
			$siteurlhtml = "<a href='" . esc_url( $siteurl ) . "' target='_blank' >$siteurl</a>";

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
			);

			// If we have content let us check if there are any tags we need to replace with the correct values.
			$string = stripslashes( $string );
			$string = buddyforms_get_field_value_from_string( $string, $post_id, $form_slug, true );

			foreach ( $short_codes_and_values as $shortcode => $short_code_value ) {
				$string = buddyforms_replace_shortcode_for_value( $string, $shortcode, $short_code_value );
			}
		}
	}

	return apply_filters( 'buddyforms_contact_author_process_shortcode', $string, $post_id, $form_slug );
}

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

		$string_error = apply_filters( 'buddyforms_contact_author_invalid', __( 'There has been an error sending the message!', 'buddyforms-contact-author' ) );

		if ( ! isset( $_POST['post_id'] ) ) {
			echo $string_error;
			die();
		}

		$post_id = intval( $_POST['post_id'] );

		$string_error_invalid_email = apply_filters( 'buddyforms_contact_author_invalid_email', __( 'Please enter a valid email address', 'buddyforms-contact-author' ) );

		if ( ! isset( $_POST['contact_author_email_from'] ) ) {
			echo $string_error_invalid_email;
			die();
		}

		$string_error_invalid_subject = apply_filters( 'buddyforms_contact_author_invalid_subject', __( 'Please enter a valid Subject', 'buddyforms-contact-author' ) );

		if ( ! isset( $_POST['contact_author_email_subject'] ) ) {
			echo $string_error_invalid_subject;
			die();
		}

		$string_error_invalid_message = apply_filters( 'buddyforms_contact_author_invalid_message', __( 'Please enter a valid Message', 'buddyforms-contact-author' ) );

		if ( ! isset( $_POST['contact_author_email_message'] ) ) {
			echo $string_error_invalid_message;
			die();
		}

		$string_error_invalid_form = apply_filters( 'buddyforms_contact_author_invalid_form', __( 'Please check the form', 'buddyforms-contact-author' ) );

		$form_slug = "buddyforms_contact_author_post_" . $post_id;
		if ( Form::isValid( $form_slug ) ) {

		} else {
			echo $string_error_invalid_form;
			die();
		}

		$email_body = ! empty( $_POST['contact_author_email_message'] ) ? wp_check_invalid_utf8( $_POST['contact_author_email_message'] ) : '';

		$email_body = wp_kses_post( $email_body );

		if ( empty( $email_body ) ) {
			echo $string_error_invalid_form;
			die();
		}

		$form_slug_parent = get_post_meta( $post_id, '_bf_form_slug', true );

		$from_email = sanitize_text_field( $_POST['contact_author_email_from'] );

		$post = get_post( $post_id );

		$user_info = get_userdata( $post->post_author );

		$mail_to = $user_info->user_email;
		$subject = sanitize_text_field( $_POST['contact_author_email_subject'] );

		$email_body = buddyforms_contact_author_process_shortcode( $email_body, $post_id, $form_slug_parent );

		$email_body = apply_filters( 'the_content', $email_body );
		$email_body = str_replace( ']]>', ']]&gt;', $email_body );

		$subject = buddyforms_contact_author_process_shortcode( $subject, $post_id, $form_slug_parent );

		$email_body = nl2br( $email_body );
		$email_cc   = apply_filters( 'buddyforms_contact_author_email_cc', array(),$form_slug_parent, $post_id );
		$email_bcc  = apply_filters( 'buddyforms_contact_author_email_bcc', array(), $form_slug_parent, $post_id );
		$result     = buddyforms_email( $mail_to, $subject, $from_email, $from_email, $email_body, $email_cc, $email_bcc, $form_slug_parent, $post_id );

		if ( ! $result ) {
			wp_send_json( $string_error, 400 );
		}

		wp_send_json( '' );
	} catch ( Exception $ex ) {
		BuddyFormsContactAuthor::error_log( $ex->getMessage() );
	}
	die();
}

add_action( 'wp_ajax_buddyforms_contact_author', 'buddyforms_contact_author' );
add_action( 'wp_ajax_nopriv_buddyforms_contact_author', 'buddyforms_contact_author' );

function buddyforms_contact_author_action_content( $post_id, $form_slug ) {
	global $buddyforms;
	if ( empty( $post_id ) || empty( $form_slug ) || empty( $buddyforms ) || empty( $buddyforms[ $form_slug ] ) ) {
		return '';
	}
	if ( empty( $buddyforms[ $form_slug ]['contact_author'] ) ) {
		return '';
	}
	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
		return '';
	}
	$action_content = '';
	if ( isset( $buddyforms[ $form_slug ]['contact_author'] ) ) {
		ob_start();
		echo '<ul class="edit_links contanct_author_action_container">';
		echo '<li>';
		buddyforms_contact_author_post( $post_id, $form_slug );
		echo '</li>';
		echo '</ul>';

		$action_content = ob_get_clean();
	}

	return $action_content;
}

/**
 * Add the action in buddyforms submission list
 *
 * @param $post_id
 * @param $form_slug
 */
function buddyforms_contact_author_buddyforms_table_edit_column( $post_id, $form_slug ) {
	echo buddyforms_contact_author_action_content( $post_id, $form_slug );
}

add_action( 'buddyforms_the_loop_after_actions', 'buddyforms_contact_author_buddyforms_table_edit_column', 10, 2 );

/**
 * Add the action to frontend datable
 *
 * @param $action_html
 * @param $post_id
 * @param $form_slug
 * @param $fields
 * @param $entry_metas
 *
 * @return false|string
 */
function buddyforms_contact_author_table_edit_column( $action_html, $post_id, $form_slug, $fields, $entry_metas ) {
	global $buddyforms;

	if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
		return $action_html;
	}

	if ( get_post_status( $post_id ) == 'completed' ) {
		return $action_html;
	}

	return buddyforms_contact_author_action_content( $post_id, $form_slug );
}

add_filter( 'buddyforms_datatable_action_html', 'buddyforms_contact_author_table_edit_column', 10, 5 );


function taxski_buddyforms_email_body( $email_body, $mail_header, $subject, $from_name, $from_email, $form_slug, $post_id ) {
	if ( ! is_user_logged_in() ) {
		return $email_body;
	}

	if ( empty( $post_id ) || empty( $form_slug ) ) {
		return $email_body;
	}

	$email_body = buddyforms_contact_author_complete_order_shortcode( $email_body, $post_id, $form_slug );

	return $email_body;
}

//add_filter( 'buddyforms_email_body', 'taxski_buddyforms_email_body', 10, 7 );


function buddyforms_contact_author_complete_order_shortcode( $string, $post_id, $form_slug ) {
	if ( empty( $post_id ) || empty( $form_slug ) || empty( $string ) ) {
		return $string;
	}

	$author = get_post_field( 'post_author', $post_id );
	if ( empty( $author ) ) {
		return $string;
	}

	$home_page   = home_url();
	$lading_page = apply_filters( 'buddyforms_contact_author_landing_url', $home_page, $post_id, $form_slug );

	$data        = array(
		'id'  => $post_id,
		'key' => buddyforms_create_nonce( 'buddyforms_bf_offer_complete_request_keys', $author, '' )
	);
	$data_string = json_encode( $data );

	$complete_offer_link = add_query_arg( array(
		'bf_offer_complete_request' => base64_encode( $data_string . '|' . wp_nonce_tick() ),
	), $lading_page );

	$string = buddyforms_replace_shortcode_for_value( $string, '[complete_offer_link]', $complete_offer_link );

	return $string;
}


function buddyforms_blocks_the_loop_post_status( $post_status, $form_slug ) {
	$post_status['completed'] = 'completed';

	return $post_status;
}

add_filter( 'buddyforms_blocks_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );
add_filter( 'buddyforms_shortcode_the_loop_post_status', 'buddyforms_blocks_the_loop_post_status', 1, 3 );

function buddyforms_contact_author_post_request() {
	try {
		if ( ! is_array( $_GET ) ) {
			return;
		}
		if ( ! isset( $_GET['bf_offer_complete_request'] ) ) {
			return;
		}

		$data = base64_decode( $_GET['bf_offer_complete_request'] );

		$tick = wp_nonce_tick();

		$data = str_replace( '|' . $tick, '', $data );

		if ( empty( $data ) ) {
			return;
		}

		$data = json_decode( $data, true );

		if ( ! isset( $data['key'] ) || ! isset( $data['id'] ) ) {
			return;
		}

		$post_id = intval( $data['id'] );
		$author  = get_post_field( 'post_author', $post_id );

		if ( empty( $author ) || is_wp_error( $author ) ) {
			return;
		}

		$expected = buddyforms_create_nonce( 'buddyforms_bf_offer_complete_request_keys', $author, '' );
		$nonce    = sanitize_text_field( $data['key'] );

		if ( ! hash_equals( $expected, $nonce ) ) {
			return;
		}

		wp_delete_post( $post_id, true );

		add_action( 'wp_head', 'buddyforms_contact_author_post_request_success' );
	} catch ( Exception $ex ) {
		BuddyFormsContactAuthor::error_log( $ex->getMessage() );
	}

	return;
}

add_action( 'parse_request', 'buddyforms_contact_author_post_request' );


function buddyforms_contact_author_post_request_success() {
	$home_page       = home_url();
	$lading_page     = apply_filters( 'buddyforms_contact_author_complete_redirection', $home_page );
	$complete_string = apply_filters( 'buddyforms_contact_author_complete_string', __( 'Offer is set to completed', 'buddyforms-contact-author' ) );
	?>
	<script>
		jQuery(document).ready(function () {
			alert('<?php echo esc_attr( $complete_string ) ?>');
			document.location.href = '<?php echo $lading_page; ?>';
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


function buddyforms_contact_author_post_action_for_post_content( $content ) {
	$post_id = get_the_ID();
	if ( ! empty( $post_id ) ) {
		$form_slug = buddyforms_get_form_slug_by_post_id( $post_id );
		if ( ! empty( $form_slug ) ) {
			global $buddyforms;
			if ( empty( $buddyforms[ $form_slug ]['contact_author'] ) ) {
				return $content;
			}
			if ( isset( $buddyforms[ $form_slug ]['contact_author_logged_in_only'] ) && ! is_user_logged_in() ) {
				return $content;
			}
//			$has_content_filter = has_filter( 'the_content', 'buddyforms_contact_author_post_action_for_post_content' );
//			if ( empty($has_content_filter) ) {
			remove_filter( 'the_content', 'buddyforms_contact_author_post_action_for_post_content', 99 );
//			}
			ob_start();
			echo '<div class="buddyforms_contact_author_action_container">';
			buddyforms_contact_author_post( $post_id, $form_slug );
			echo '</div>';
			$action_html = ob_get_clean();
			if ( ! empty( $buddyforms[ $form_slug ]['contact_author_post_action_position'] ) ) {
				if ( $buddyforms[ $form_slug ]['contact_author_post_action_position'] === 'before' ) {
					$content = $action_html . $content;
				} else if ( $buddyforms[ $form_slug ]['contact_author_post_action_position'] === 'after' ) {
					$content .= $action_html;
				}
			}
			add_filter( 'the_content', 'buddyforms_contact_author_post_action_for_post_content', 99, 1 );
		}
	}

	return $content;
}

add_filter( 'the_content', 'buddyforms_contact_author_post_action_for_post_content', 99, 1 );

