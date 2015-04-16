jQuery(window).ready(function(){

	/* BEGIN: Maintain the currently visible product(s) when switching views */
	jQuery('.single-view').click(function(){
		// Save view preference
		setCookie('gridView', 0);

		if (jQuery('.single-view').hasClass('selected')) {
			return true;
		}

		/* Get the currently visible product to jump back to when switching views */
		var currentlyVisibleProduct;
		jQuery(':visible').filter('.multi-view-box').each(function() {
			if (isScrolledIntoView(jQuery(this)) === true) {
				currentlyVisibleProduct = jQuery(this).attr('id');
				return false;
			}
		});

		jQuery('.multi-view-wrapper').toggleClass('grid', false);
		jQuery('.multi-view-img').toggleClass('grid', false);
		jQuery('.multi-view-box').toggleClass('grid', false);
		jQuery('.single-view').toggleClass('selected', true);
		jQuery('.multi-view').toggleClass('selected', false);

		/* Jump to the product that was in view before switching */
		if (jQuery(window).scrollTop() > 400
			&& typeof currentlyVisibleProduct !== 'undefined') {
			window.scrollTo(0, jQuery('#' + currentlyVisibleProduct).offset().top + 185);
		}
	});

	jQuery('.multi-view').click(function(){
		// Save view preference
		setCookie('gridView', 1);

		if (jQuery('.multi-view').hasClass('selected')) {
			return true;
		}

		/* Get the currently visible product to jump back to when switching views */
		var currentlyVisibleProduct;
		jQuery(':visible').filter('.multi-view-box').each(function() {
			if (isScrolledIntoView(jQuery(this)) === true) {
				currentlyVisibleProduct = jQuery(this).attr('id');
				return false;
			}
		});

		jQuery('.multi-view-wrapper').toggleClass('grid', true);
		jQuery('.multi-view-img').toggleClass('grid', true);
		jQuery('.multi-view-box').toggleClass('grid', true);
		jQuery('.multi-view').toggleClass('selected', true);
		jQuery('.single-view').toggleClass('selected', false);

		/* Jump to the product that was in view before switching */
		if (jQuery(window).scrollTop() > 400
			&& typeof currentlyVisibleProduct !== 'undefined') {
			window.scrollTo(0, jQuery('#' + currentlyVisibleProduct).offset().top - 150);
		}
	});

	function isScrolledIntoView(elem) {
		var docViewTop = jQuery(window).scrollTop();
		var docViewBottom = docViewTop + jQuery(window).height();

		var elemTop = jQuery(elem).offset().top;
		var elemBottom = elemTop + jQuery(elem).height();

		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}
	/* END: Maintain the currently visible product(s) when switching views */

	/* BEGIN: Snap the category-view-header to top (below menu-header) when scroll-to-top.
	 * Adjust top margin so that visible products don't jump to top behind header.
	 */

	function fixDiv() {

		// Show the "to top" button only if the user scrolls more than 2 screen lengths (approx)
		if (jQuery(window).scrollTop() > jQuery('.multi-view-wrapper').offset().top + 400) {
			jQuery('.view-btns .back').fadeIn();
		} else {
			jQuery('.view-btns .back').fadeOut();
		}
	}

	/* Set default view mode */
	function setGridView() {
		var gridView = getCookie('gridView');
		// 0 = single-view, 1 = multi-view
		if (typeof mobilePreference !== 'undefined' && gridView === "0") {
			jQuery('.single-view').click();
		} else if (typeof mobilePreference !== 'undefined' && gridView === "1") {
			jQuery('.multi-view').click();
		// If no preference set...
		} else {
			// FIXME: Revisit 2-wide & 3-wide, instead of 1 vs 2
			//if (jQuery(window).width() < 525) {
				// Set the single-view mode as selected on page-load (portrait)
			//	jQuery('.single-view').click();
			//} else {
				// Set the multi-view mode as selected otherwise
				jQuery('.multi-view').click();
			//}
		}
	}

	jQuery(window).scroll(fixDiv);
	jQuery(window).resize(fixDiv);

	fixDiv();
	setGridView();
	/* END: Snap the category-view-header to top */

	/* BACK TO TOP button */
	jQuery('.view-btns .back').click(function(){
		scrollToElement(".multi-view-wrapper", 125);
	});
});