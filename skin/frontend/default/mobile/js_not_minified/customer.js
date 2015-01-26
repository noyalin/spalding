jQuery(document).ready(function(){

	jQuery('#my-account-menu').accordion({
		collapsible: true,
		active: false,
		header: "h1",
		icons: false,
		heightStyle: 'content',
		animate: 200,
	});

});

function setPasswordForm(arg){
	if(arg){
		jQuery('.change-password').fadeIn();
		jQuery('#current_password').toggleClass('required-entry', true);
		jQuery('#password').toggleClass('required-entry', true);
		jQuery('#confirmation').toggleClass('required-entry', true);
	}else{
		jQuery('.change-password').fadeOut();
		jQuery('#current_password').toggleClass('required-entry', false);
		jQuery('#password').toggleClass('required-entry', false);
		jQuery('#confirmation').toggleClass('required-entry', false);

		jQuery('#current_password').parent().find('div').remove();
		jQuery('#password').parent().find('div').remove();
		jQuery('#confirmation').parent().find('div').remove();
	}
}