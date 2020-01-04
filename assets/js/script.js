var buddyformsContactAuthorInstance = {
	bfIsEmail: function(email) {
		if (!email || (email && email.length === 0)) {
			return false;
		}
		var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return regex.test(email);
	},
	contactAuthor: function() {
		var actionButton = jQuery(this);
		var post_id = actionButton.attr('data-post_id');
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

		var form_slug = actionButton.attr('data-form_slug');
		actionButton.attr('disabled', true);
		var actionButtonOriginalText = actionButton.text();
		actionButton.text('Loading...');

		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: buddyformsContactAuthor.ajax,
			data: {
				'action': 'buddyforms_contact_author',
				'post_id': post_id,
				'form_slug': form_slug,
				'nonce': buddyformsContactAuthor.nonce,
				'contact_author_email_subject': contact_author_email_subject,
				'contact_author_email_message': contact_author_email_message,
				'contact_author_email_from': contact_author_email_from,
			},
			success: function(data) {
				console.log(data);
				actionButton.text('Complete');
				setTimeout(function() {
					location.reload();
				}, 2000);
			},
			error: function(request, status, error) {
				actionButton.text(actionButtonOriginalText);
				actionButton.removeAttr('disabled');
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
