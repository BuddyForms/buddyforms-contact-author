<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function buddyforms_contact_author_admin_settings_sidebar_metabox() {
	add_meta_box( 'buddyforms_contact_author', __( "Contact the Author", 'buddyforms-contact-author' ), 'buddyforms_contact_author_admin_settings_sidebar_metabox_html', 'buddyforms', 'normal', 'low' );
	add_filter( 'postbox_classes_buddyforms_buddyforms_contact_author', 'buddyforms_metabox_class' );
}


function buddyforms_contact_author_admin_settings_sidebar_metabox_html() {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$buddyform = get_post_meta( get_the_ID(), '_buddyforms_options', true );

	if ( empty( $buddyform ) ) {
		return;
	}

	$form_setup = array();

	$contact_author = false;
	if ( isset( $buddyform['contact_author'] ) ) {
		$contact_author = $buddyform['contact_author'];
	}
	$contact_author_message_subject = isset( $buddyform['contact_author_message_subject'] ) ? $buddyform['contact_author_message_subject'] : '';
	$contact_author_message_text    = isset( $buddyform['contact_author_message_text'] ) ? $buddyform['contact_author_message_text'] : '';

	$form_setup[] = new Element_Checkbox( "<b>" . __( 'Enable Contact Author for this form entries', 'buddyforms-contact-author' ) . "</b>", "buddyforms_options[contact_author]", array( "contact_author" => __( "Add contact author button to the form", 'buddyforms-contact-author' ) ), array(
		'value'     => $contact_author,
	) );

	$contact_author_logged_in_only = isset( $buddyform['contact_author_logged_in_only'] ) ? $buddyform['contact_author_logged_in_only'] : '';
	$form_setup[]                  = new Element_Checkbox( "<b>" . __( 'Logged in user only', 'buddyforms-contact-author' ) . "</b>", "buddyforms_options[contact_author_logged_in_only]", array( "logged_in_only" => __( "Only logged in users can contact authors", 'buddyforms-contact-author' ) ), array(
		'value'     => $contact_author_logged_in_only,
	) );

	$contact_author_logged_in_only                   = isset( $buddyform['contact_author_post_action_position'] ) ? $buddyform['contact_author_post_action_position'] : '';
	$form_setup[] = new Element_Select( '<b>' . __( 'Action Position for Post', 'buddyforms-contact-author' ) . '</b>', "buddyforms_options[contact_author_post_action_position]",
		array(
			'none' => __( 'Not include', 'buddyforms-contact-author' ),
			'before' => __( 'Before the content', 'buddyforms-contact-author' ),
			'after' => __( 'After the content', 'buddyforms-contact-author' ),
		), array(
			'value'    => $contact_author_logged_in_only,
			'shortDesc' => __( 'Define the position where the contact author action will be added.', 'buddyforms-contact-author' )
		)
	);

	$form_setup[] = new Element_Textbox( "<b>" . __( 'Subject Text', 'buddyforms-contact-author' ) . "</b>", "buddyforms_options[contact_author_message_subject]", array(
		'value'     => $contact_author_message_subject,
	) );

	$all_shortcodes       = array();
	$element_name         = 'buddyforms_options[contact_author_message_text]';
	$available_shortcodes = buddyforms_available_shortcodes( $buddyform['slug'], $element_name );
	if ( ! empty( $buddyform['form_fields'] ) ) {
		foreach ( $buddyform['form_fields'] as $form_field ) {
			if ( ! in_array( $form_field['type'], buddyforms_unauthorized_shortcodes_field_type( $buddyform['slug'], $element_name ) ) ) {
				$all_shortcodes[] = '[' . $form_field['slug'] . ']';
			}
		}
	}

	$all_shortcodes  = array_merge( $all_shortcodes, $available_shortcodes );
	$shortcodes_html = buddyforms_get_shortcode_string( $all_shortcodes, $element_name );
	$form_setup[]    = new Element_Textarea( "<b>" . __( 'Message Text', 'buddyforms-contact-author' ) . "</b>", $element_name, array(
		'value'     => $contact_author_message_text,
		'shortDesc' => '<strong>' . __( 'Click on one of the available shortcode to insert on the above element at caret position:', 'buddyforms-contact-author' ) . '</strong><br/>' . $shortcodes_html
	) );

	buddyforms_display_field_group_table( $form_setup );

}

add_filter( 'add_meta_boxes', 'buddyforms_contact_author_admin_settings_sidebar_metabox' );
