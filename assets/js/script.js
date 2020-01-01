var buddyformsContactAuthorInstance = {
	bfIsEmail: function(email) {
		if (!email || (email && email.length === 0)) {
			return false;
		}
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		return regex.test(email);
	},
	contactAuthor: function() {
		var post_id = jQuery(this).attr('data-post_id');
		if (!post_id) {
			console.log('something went wrong, please contact the admin');
			return false;
		}

		var contact_author_email_subject = jQuery('#contact_author_email_subject_' + post_id).val();
		var contact_author_email_from = jQuery('#contact_author_email_from_' + post_id).val();
		var contact_author_email_message = jQuery('#contact_author_email_message_' + post_id).val();

		if (!buddyformsContactAuthorInstance.bfIsEmail(contact_author_email_from)) {
			alert('Please enter a valid email address');
			return false;
		}
		if (contact_author_email_subject && contact_author_email_subject.length === 0) {
			alert('Subject is a required field');
			return false;
		}
		if (contact_author_email_message && contact_author_email_message.length === 0) {
			alert('Message is a required field');
			return false;
		}

		var form_slug = jQuery(this).attr('data-form_slug');

		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: buddyformsContactAuthor.ajax,
			data: {
				'action': 'buddyforms_contact_author',
				'post_id': post_id,
				'form_slug': form_slug,
				'nonce': buddyformsContactAuthor.nonce, //todo @sven I add this for security purpose
				'contact_author_email_subject': contact_author_email_subject,
				'contact_author_email_message': contact_author_email_message,
				'contact_author_email_from': contact_author_email_from,
			},
			success: function(data) {
				console.log(data);
				location.reload();
				// document.getElementById(form_slug).reset();
			},
			error: function(request, status, error) {
				alert(request.responseText);
			},
		});
	},
	init: function() {
		if (buddyformsContactAuthor) {
			jQuery(document).on('click', '.buddyforms-contact-author-action', buddyformsContactAuthorInstance.contactAuthor);
		}
	},
};

jQuery(document).ready(function() {
	buddyformsContactAuthorInstance.init();
});
