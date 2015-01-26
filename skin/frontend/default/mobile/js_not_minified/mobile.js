/* Main category menu, persistent on all pages */
jQuery.noConflict();

function validateForm(selector) {
	var validateOk = true;
	jQuery(selector + ' .required-entry').each(function(){
		if (jQuery(this).attr('id') == 'region') {
			// we're already validating the region_id
			return true; // continue
		}
		jQuery(this).parent().find('div').remove();
		if (jQuery(this).val() == '' || jQuery(this).val() == 0) {
			jQuery(this).parent().append('<div class="validation-advice">This is a required field.</div>');
			validateOk = false;
		}
	});

	// Manually check the #country field on the shipping form instead of
	// creating a template override
	if (selector === '#shipping-zip-form') {
		jQuery('#country').parent().find('div').remove();
		if (jQuery('#country').val() == '' || jQuery('#country').val() == 0) {
			jQuery('#country').parent().append('<div class="validation-advice">This is a required field.</div>');
			validateOk = false;
		}
	}

	if (!validateOk) {
		showAlert('Highlighted fields required.');
		return false;
	} else {
		return true;
	}
}

function validateSelect(selector) {
	if (jQuery(selector).val() == 0) {
		showAlert('Please select a size.');
		return false;
	} else {
		return true;
	}
}

function showAlert(message, className) {
    if (typeof className !== 'undefined') {
		jQuery('.ui-dialog-content p').toggleClass(className, true);
    }
	jQuery('#alert p').html(message);
	jQuery('#alert').dialog('open');
}

function scrollToElement(selector, selectorOffset) {
	var offset = jQuery(selector).offset();
	if (typeof offset !== 'undefined') {
		jQuery('html, body').stop().animate({
			scrollTop: offset.top - selectorOffset
		}, 500);
	}
}

function showMoreReviews(button, count) {
	var moreLength = (jQuery('.prListWrapper.more').length < count) ? jQuery('.prListWrapper.more').length : count;
	jQuery(button).fadeOut();
	jQuery('.prListWrapper.more').each(function(){
		jQuery(this).fadeIn(700);
		jQuery(this).toggleClass('more', false);
		moreLength--;
		if (!moreLength) {
			return false;
		}
	});
}