<?php

function buddyforms_contact_author_post( $post_id, $form_slug ) {
	global $post, $buddyforms;
	add_thickbox();

	?>

    <script>

            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

            function bfisEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

            jQuery('#buddyforms_contact_author_<?php echo $post_id ?>').on("click", function (evt) {


                var contact_author_email_subject = jQuery('#contact_author_email_subject_<?php echo $post_id ?>').val();
                var contact_author_email_from = jQuery('#contact_author_email_from_<?php echo $post_id ?>').val();
                var contact_author_email_message = jQuery('#contact_author_email_message_<?php echo $post_id ?>').val();

                if ( ! bfisEmail(contact_author_email_from)) {
                    alert('Please enter a valid email address');
                    return false;
                }
                if (contact_author_email_subject == '') {
                    alert('Subject is a required field');
                    return false;
                }
                if (contact_author_email_message == '') {
                    alert('Message is a required field');
                    return false;
                }

                var post_id = jQuery(this).attr("data-post_id");
                var form_slug = jQuery(this).attr("data-form_slug");

                jQuery.ajax({
                    type: 'POST',
                    dataType: "json",
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    data: {
                        "action": "buddyforms_contact_author",
                        "post_id": post_id,
                        "form_slug": form_slug,
                        "contact_author_email_subject": contact_author_email_subject,
                        "contact_author_email_message": contact_author_email_message,
                        "contact_author_email_from": contact_author_email_from
                    },
                    success: function (data) {

                        console.log(data);
                        location.reload();
                        // document.getElementById(form_slug).reset();


                    },
                    error: function (request, status, error) {
                        alert(request.responseText);
                    }
                });

            });
    </script>
    <style>
        #buddyforms_contact_author_wrap input[type="text"] {
            width: 100%;
        }

        /*div#TB_ajaxContent {*/
            width: 96% !important;
            height: 96% !important;
        /*}*/



        #TB_window {
            top: 50%;
            left: 50%;
            /*transform: translate(-50%, -50%);*/
            max-height: 100%;
            max-width: 90%;
            margin: 0 !important;
        }
        #TB_ajaxContent {
            width: 100% !important;
            height: auto !important;
            max-width: 100%;
            box-sizing: border-box;
        }
    </style>

	<?php echo '<a id="buddyforms-contact-author-from-post-id- ' . $post_id . '" href="#TB_inline?width=800&height=600&inlineId=buddyforms_contact_author_modal_' . $post_id . '" title="' . __( 'Contact the Author', 'buddyforms' ) . '" class="thickbox"><span aria-label="' . __( 'Contact the Author', 'buddyforms' ) . '" title="' . __( 'Contact the Author', 'buddyforms' ) . '" class="dashicons dashicons-email"> </span> ' . __( 'Contact the Author', 'buddyforms' ) . '</a>'; ?>

    <div id="buddyforms_contact_author_modal_<?php echo $post_id ?>" style="display:none;">
        <div id="buddyforms_contact_author_wrap">
            <br><br>

			<?php

			// Create the form object
			$message_form_slug = "buddyforms_contact_author_post_" . $post_id;

			$contact_author_form = new Form( $message_form_slug );

			// Set the form attribute
			$contact_author_form->configure( array(
				"prevent" => array( "bootstrap", "jQuery", "focus", '' ),
				'method'  => 'post'
			) );
			$contact_author_form->addElement( new Element_Email( 'Your eMail Address', 'contact_author_email_from_' . $post_id, array( 'required' => 'required' ) ) );

			$contact_author_message_subject = isset( $buddyforms[ $form_slug ]['contact_author_message_subject'] ) ? $buddyforms[ $form_slug ]['contact_author_message_subject'] : '';
			$contact_author_form->addElement( new Element_Textbox( 'Subject', 'contact_author_email_subject_' . $post_id, array( 'value' => $contact_author_message_subject ) ) );

			$contact_author_request_message = isset( $buddyforms[ $form_slug ]['contact_author_message_text'] ) ? $buddyforms[ $form_slug ]['contact_author_message_text'] : '';
			$contact_author_form->addElement( new Element_Textarea( 'Add a Message', 'contact_author_email_message_' . $post_id, array(
				'value' => $contact_author_request_message,
				'class' => ''
			) ) );

			$contact_author_form->render();
			?>

            <br>
            <a id="buddyforms_contact_author_<?php echo $post_id ?>"
               data-post_id="<?php echo $post_id ?>"
               data-form_slug="<?php echo $form_slug ?>"
               href="#" class="button"><?php echo  __('Contact the Author', 'buddyforms'); ?></a>
        </div>
    </div>

	<?php

}

add_action( 'wp_ajax_buddyforms_contact_author', 'buddyforms_contact_author' );
add_action( 'wp_ajax_nopriv_buddyforms_contact_author', 'buddyforms_contact_author' );

function buddyforms_contact_author() {
	global $buddyforms;

	if ( ! isset( $_POST['post_id'] ) ) {
		echo __( 'There has been an error sending the message!', 'buddyforms' );
		die();

		return;
	}
	if ( ! isset( $_POST['contact_author_email_from'] ) ) {
		echo __( 'Please enter a valide email address', 'buddyforms' );
		die();

		return;
	}
	$post_id = $_POST['post_id'];


	$form_slug = "buddyforms_contact_author_post_" . $post_id;
	if ( Form::isValid( $form_slug ) ) {

	} else {
		echo __( 'Please enter a valide email address', 'buddyforms' );
		die();

		return;
	}

	$form_slug_parent = get_post_meta( $post_id, '_bf_form_slug', true );


	$from_email = $_POST['contact_author_email_from'];

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


	$mail_to = $user_info->user_email;
	$subject = $_POST['contact_author_email_subject'];

	$emailBody = $_POST['contact_author_email_message'];

//	$emailBody    = str_replace( '[user_login]', $usernameauth, $emailBody );
//	$emailBody    = str_replace( '[first_name]', $first_name, $emailBody );
//	$emailBody    = str_replace( '[last_name]', $last_name, $emailBody );
//	$emailBody    = str_replace( '[published_post_link_plain]', $postperma, $emailBody );
//	$postlinkhtml = "<a href='$postperma' target='_blank'>$postperma</a>";
//	$emailBody    = str_replace( '[published_post_link_html]', $postlinkhtml, $emailBody );
//	$emailBody    = str_replace( '[published_post_title]', $post_title, $emailBody );
//	$emailBody    = str_replace( '[site_name]', $blog_title, $emailBody );
//	$emailBody    = str_replace( '[site_url]', $siteurl, $emailBody );
//	$emailBody    = str_replace( '[site_url_html]', $siteurlhtml, $emailBody );

	$emailBody = apply_filters('buddyforms_contact_author_message_text', $emailBody, $post->ID, $form_slug_parent );

	//$emailBody = stripslashes( htmlspecialchars_decode( $emailBody ) );




	$short_codes_and_values = array(
		'[user_login]'                => $usernameauth,
		'[user_nicename]'             => $user_nicename,
		'[user_email]'                => $user_email,
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

	$result = buddyforms_email( $mail_to, $subject, $from_email, $from_email, $emailBody, '', '' );

	if ( ! $result ) {
		$json['test'] .= __( 'There has been an error sending the message!', 'buddyforms' );
	}

	echo json_encode( $json );

	die();
}


add_action( 'init', 'buddyforms_contact_author_post_request' );

function buddyforms_contact_author_post_request() {

	if ( isset( $_GET['bf_offer_complete_request'] ) ) {

		$key     = $_GET['key'];
		$post_id = $_GET['bf_offer_complete_request'];
		$nonce   = $_GET['nonce'];


		if ( ! wp_verify_nonce( $nonce, 'buddyform_bf_offer_complete_request_keys' ) ) {
			//	return false;
		}

		wp_update_post(array(
			'ID'    =>  $post_id,
			'post_status'   =>  'completed'
		));



		add_action( 'wp_head', 'buddyforms_contact_author_post_request_success' );
	}
}

function buddyforms_contact_author_post_request_success() {

	?>
    <script>
        jQuery(document).ready(function () {
            alert('Offer is set to completed');
            document.location.href = "/";
        });
    </script>
	<?php
}