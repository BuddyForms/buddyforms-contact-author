<?php

function buddyforms_contact_author_admin_settings_sidebar_metabox() {
	add_meta_box( 'buddyforms_contact_author', __( "Contact the Author", 'buddyforms' ), 'buddyforms_contact_author_admin_settings_sidebar_metabox_html', 'buddyforms', 'normal', 'low' );
	add_filter( 'postbox_classes_buddyforms_buddyforms_contact_author', 'buddyforms_metabox_class' );
}


function buddyforms_contact_author_admin_settings_sidebar_metabox_html() {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$buddyform = get_post_meta( get_the_ID(), '_buddyforms_options', true );

	$form_setup = array();

	$contact_author = false;
	if ( isset( $buddyform['contact_author'] ) ) {
		$contact_author = $buddyform['contact_author'];
	}


	$form_setup[] = new Element_Checkbox( "<b>" . __( 'Enable Contact Author for this form entries', 'buddyforms' ) . "</b>", "buddyforms_options[contact_author]", array( "contact_author" => "Add contact author button to the form" ), array(
		'value'     => $contact_author,
		'shortDesc' => __( '', 'buddyforms' )
	) );

	buddyforms_display_field_group_table( $form_setup );

}

add_filter( 'add_meta_boxes', 'buddyforms_contact_author_admin_settings_sidebar_metabox' );
