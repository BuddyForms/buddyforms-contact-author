<?php

function buddyforms_contact_author_post( $post_id, $form_slug ) {
	global $post, $buddyforms;
	add_thickbox();

	?>

	<script>
        jQuery(document).ready(function () {
            jQuery(document).on("click", '#buddyforms_contact_author_<?php echo $post_id ?>', function (evt) {

                var contact_author_email_subject = jQuery('#contact_author_email_subject_<?php echo $post_id ?>').val();
                var contact_author_email_from = jQuery('#contact_author_email_from_<?php echo $post_id ?>').val();
                var contact_author_email_message = jQuery('#contact_author_email_message_<?php echo $post_id ?>').val();

                if (contact_author_email_from == '') {
                    alert('Mail From is a required field');
                    return false;
                }
                if (contact_author_email_subject == '') {
                    alert('Mail Subject is a required field');
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
                    url: ajaxurl,
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


                    },
                    error: function (request, status, error) {
                        alert(request.responseText);
                    }
                });

            });
        });
	</script>
	<style>
		#buddyforms_contact_author_wrap input[type="text"] {
			width: 100%;
		}

		div#TB_ajaxContent {
			width: 96% !important;
			height: 96% !important;
		}
	</style>

	<?php echo '<a id="buddyforms-contact-author-from-post-id- ' . $post_id . '" href="#TB_inline?width=800&height=600&inlineId=buddyforms_contact_author_modal_' .  $post_id . '" title="' . __( 'Contact the Author', 'buddyforms' ) . '" class="thickbox"><span aria-label="' . __( 'Contact the Author', 'buddyforms' ) . '" title="' . __( 'Contact the Author', 'buddyforms' ) . '" class="dashicons dashicons-trash"> </span> ' . __( 'Contact the Author', 'buddyforms' ) . '</a>'; ?>

	<div id="buddyforms_contact_author_modal_<?php echo $post_id ?>" style="display:none;">
		<div id="buddyforms_contact_author_wrap">
			<br><br>

			<?php

			// Create the form object
			$form_id = "buddyforms_contact_author_post_" . $post_id;

			$contact_author_form = new Form( $form_id );


			// Set the form attribute
			$contact_author_form->configure( array(
				"prevent" => array( "bootstrap", "jQuery", "focus" ),
				'method'  => 'post'
			) );
			$contact_author_form->addElement( new Element_Textbox( 'From', 'contact_author_email_from_' . $post_id, array( 'value' => 'Your email address' ) ) );

			$contact_author_form->addElement( new Element_Textbox( 'Subject', 'contact_author_email_subject_' . $post_id, array( 'value' => 'Your submission got contact_authored' ) ) );

			$contact_author_request_message = 'Hi [user_login], Your submitted post [published_post_title] has ben contact_authored.';

			$contact_author_form->addElement( new Element_Textarea( 'Add a Message', 'contact_author_email_message_' . $post_id , array( 'value' => $contact_author_request_message, 'class' => 'collaburative-publishiing-message' ) ) );


			$contact_author_form->render();

			?>

			<br>
			<a id="buddyforms_contact_author_<?php echo $post_id ?>"
			   data-post_id="<?php echo $post_id ?>"
			   data-form_slug="<?php echo $form_slug ?>"
			   href="#" class="button">Contact the Author</a>
		</div>
	</div>

	<?php

}

add_action( 'wp_ajax_buddyforms_contact_author', 'buddyforms_contact_author' );
function buddyforms_contact_author() {
	global $buddyforms;

	if ( ! isset( $_POST['post_id'] ) ) {
		echo __( 'There has been an error sending the message!', 'buddyforms' );
		die();

		return;
	}

	$post_id = $_POST['post_id'];

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

	$from_email = $_POST['contact_author_email_from'];
	$emailBody  = $_POST['contact_author_email_message'];

	$emailBody    = str_replace( '[user_login]', $usernameauth, $emailBody );
	$emailBody    = str_replace( '[first_name]', $first_name, $emailBody );
	$emailBody    = str_replace( '[last_name]', $last_name, $emailBody );
	$emailBody    = str_replace( '[published_post_link_plain]', $postperma, $emailBody );
	$postlinkhtml = "<a href='$postperma' target='_blank'>$postperma</a>";
	$emailBody    = str_replace( '[published_post_link_html]', $postlinkhtml, $emailBody );
	$emailBody    = str_replace( '[published_post_title]', $post_title, $emailBody );
	$emailBody    = str_replace( '[site_name]', $blog_title, $emailBody );
	$emailBody    = str_replace( '[site_url]', $siteurl, $emailBody );
	$emailBody    = str_replace( '[site_url_html]', $siteurlhtml, $emailBody );

	$emailBody = stripslashes( htmlspecialchars_decode( $emailBody ) );

	$mailheaders = "MIME-Version: 1.0\n";
	$mailheaders .= "X-Priority: 1\n";
	$mailheaders .= "Content-Type: text/html; charset=\"UTF-8\"\n";
	$mailheaders .= "Content-Transfer-Encoding: 7bit\n\n";
	$mailheaders .= "From: " . $from_email . "<" . $from_email . ">" . "\r\n";

	$message = '<html><head></head><body>' . $emailBody . '</body></html>';

	$result = wp_mail( $mail_to, $subject, $message, $mailheaders );

	if ( ! $result ) {
		$json['test'] .= __( 'There has been an error sending the message!', 'buddyforms' );
	}
	$json['test'] .= 'Author Contacted';

	echo json_encode( $json );

	die();
}


add_action( 'init', 'buddyforms_contact_author_post_request' );

function buddyforms_contact_author_post_request() {

	if ( isset($_GET['bf_contact_author_post_request']) ) {

		$key     = $_GET['key'];
		$post_id = $_GET['bf_contact_author_post_request'];
		$user_id = $_GET['user'];
		$nonce   = $_GET['nonce'];


		if ( ! wp_verify_nonce( $nonce, 'buddyform_contact_author_post_moderator_keys' ) ) {
			//	return false;
		}


		$buddyform_contact_author_post_moderator = get_user_meta( $user_id, 'buddyform_contact_author_post_moderator_key_' . $post_id, true );


		if ( isset( $buddyform_contact_author_post_moderator) ) {
			if ( $key == $buddyform_contact_author_post_moderator ) {
				// Delete moderator from meta and taxonomies
				buddyforms_cpublishing_contact_author_post( $post_id, $user_id );


				$post_moderators = wp_get_post_terms( $post_id, 'buddyforms_moderators' );

				$post_count = count($post_moderators);
				if($post_count == 0){
					do_action( 'buddyforms_contact_author_post', $post_id );
					wp_contact_author_post( $post_id );
				}

			}
		}



		// if only author is left and the author also has approved teh contact_author, the post should get contact_authord


		add_action( 'wp_head', 'buddyforms_contact_author_post_request_success' );
		//add_action('wp_head', 'buddyforms_contact_author_post_request_error');
	}
}

function buddyforms_contact_author_post_request_success() {

	?>
	<script>
        jQuery(document).ready(function () {
            alert('Delete Done');
            document.location.href = "/";
        });
	</script>
	<?php
}

function buddyforms_contact_author_post_request_error() {

	?>
	<script>
        jQuery(document).ready(function () {
            alert('Delete Error');
        });
	</script>
	<?php
}