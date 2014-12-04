jQuery.noConflict();
jQuery(document).ready(function() {
slider =  jQuery('#slider-content');
slider.before('<div id="stripNav0" class="stripNav">')
    .cycle({
        fx: 'fade',
        timeout: 4000,
        speed: 1000,
        next: '.stripNavL',
        prev: '.stripNavR',
        pager:'#stripNav0',
        pause: 1
    });
});
nextLink = jQuery('#stripNavLa');
prevLink = jQuery('#stripNavRa');
