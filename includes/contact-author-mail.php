<?php

function buddyforms_contact_author_post( $post_id, $form_slug ) {
	global $buddyforms;
	$post = get_post( $post_id );
	?>
	<?php echo '<a id="buddyforms-contact-author-from-post-id-' . $post_id . '" href="#TB_inline?width=800&height=600&inlineId=buddyforms_contact_author_modal_' . $post_id . '" title="' . __( 'Contact the Author', 'buddyforms-contact-author' ) . '" class="thickbox buddyforms-contact-author-popup"><span aria-label="' . __( 'Contact the Author', 'buddyforms-contact-author' ) . '" title="' . __( 'Contact the Author', 'buddyforms-contact-author' ) . '" class="dashicons dashicons-email"> </span> ' . __( 'Contact the Author', 'buddyforms-contact-author' ) . '</a>'; ?>

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
			if ( ! empty( $contact_author_message_subject ) ) {
				$contact_author_message_subject = buddyforms_contact_author_process_shortcode( $contact_author_message_subject, $post, $form_slug );
			}
			$contact_author_form->addElement( new Element_Textbox( 'Subject', 'contact_author_email_subject_' . $post_id, array( 'value' => $contact_author_message_subject ) ) );

			$contact_author_request_message = isset( $buddyforms[ $form_slug ]['contact_author_message_text'] ) ? $buddyforms[ $form_slug ]['contact_author_message_text'] : '';
			if ( ! empty( $contact_author_request_message ) ) {
				$contact_author_request_message = buddyforms_contact_author_process_shortcode( $contact_author_request_message, $post, $form_slug );
			}
			$contact_author_form->addElement( new Element_Textarea( 'Add a Message', 'contact_author_email_message_' . $post_id, array(
				'value' => $contact_author_request_message,
				'rows'  => '15',
				'class' => ''
			) ) );

			$contact_author_form->render();
			?>
			<br>
			<button id="buddyforms_contact_author_<?php echo $post_id ?>" data-post_id="<?php echo $post_id ?>" data-form_slug="<?php echo $form_slug ?>" class="btn-primary btn buddyforms-contact-author-action"><?php echo __( 'Contact the Author', 'buddyforms' ); ?></button>
		</div>
	</div>
	<?php
}
