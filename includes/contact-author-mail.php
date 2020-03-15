<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function buddyforms_contact_author_post( $post_id, $form_slug ) {
	BuddyFormsContactAuthor::setNeedAssets( true, $form_slug );
	global $buddyforms;
//	$post                    = get_post( $post_id );
	$string_contact_author   = __( 'Contact the Author', 'buddyforms-contact-author' );
	$popup_action_btn        = apply_filters( 'buddyforms_contact_author_action_btn', $string_contact_author, $form_slug, $post_id );
	$popup_title             = apply_filters( 'buddyforms_contact_author_popup_title', $string_contact_author, $form_slug, $post_id );
	$popup_btn_action_string = apply_filters( 'buddyforms_contact_author_popup_btn_text', $string_contact_author, $form_slug, $post_id );

	$popup_input_email_label_string   = apply_filters( 'buddyforms_contact_author_popup_element_email', __( 'Your eMail Address', 'buddyforms-contact-author' ), $form_slug, $post_id );
	$popup_input_subject_label_string = apply_filters( 'buddyforms_contact_author_popup_element_subject', __( 'Subject', 'buddyforms-contact-author' ), $form_slug, $post_id );
	$popup_input_message_label_string = apply_filters( 'buddyforms_contact_author_popup_element_message', __( 'Add a Message', 'buddyforms-contact-author' ), $form_slug, $post_id );

	?>
	<?php echo '<a id="buddyforms-contact-author-from-post-id-' . $post_id . '" href="#TB_inline?width=800&height=100%&inlineId=buddyforms_contact_author_modal_' . $post_id . '" title="' . $popup_title . '" class="thickbox buddyforms-contact-author-popup"><span aria-label="' . $popup_action_btn . '" title="' . $popup_action_btn . '" class="dashicons dashicons-email"> </span> ' . $popup_action_btn . '</a>'; ?>

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
			$contact_author_form->addElement( new Element_Email( $popup_input_email_label_string, 'contact_author_email_from_' . $post_id, array( 'required' => 'required' ) ) );

			$contact_author_message_subject = isset( $buddyforms[ $form_slug ]['contact_author_message_subject'] ) ? $buddyforms[ $form_slug ]['contact_author_message_subject'] : '';
			if ( ! empty( $contact_author_message_subject ) ) {
				$contact_author_message_subject = buddyforms_contact_author_process_shortcode( $contact_author_message_subject, $post_id, $form_slug );
			}
			$contact_author_form->addElement( new Element_Textbox( $popup_input_subject_label_string, 'contact_author_email_subject_' . $post_id, array( 'value' => $contact_author_message_subject ) ) );

			$contact_author_request_message = isset( $buddyforms[ $form_slug ]['contact_author_message_text'] ) ? $buddyforms[ $form_slug ]['contact_author_message_text'] : '';
			if ( ! empty( $contact_author_request_message ) ) {
				$contact_author_request_message = buddyforms_contact_author_process_shortcode( $contact_author_request_message, $post_id, $form_slug );
			}
			$contact_author_form->addElement( new Element_Textarea( $popup_input_message_label_string, 'contact_author_email_message_' . $post_id, array(
				'value' => $contact_author_request_message,
				'rows'  => '15',
				'class' => '',
				'shortDesc' => apply_filters('buddyforms_contact_author_message_description', ''),
			) ) );

			$contact_author_form->render();

			$btn_html              = sprintf( '<button id="buddyforms_contact_author_%s" data-post_id="%s" data-form_slug="%s" class="btn-primary btn buddyforms-contact-author-action">%s</button>', $post_id, $post_id, $form_slug, $popup_btn_action_string );
			$contact_author_action = apply_filters( 'buddyforms_contact_author_action_button', $btn_html, $form_slug, $post_id,  $popup_btn_action_string);
			?>
			<br>
			<?php echo $contact_author_action ?>
		</div>
	</div>
	<?php
}
