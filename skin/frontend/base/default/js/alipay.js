function setBank(url, bank, order_id) {
    if (bank == "weixin_pay") {
        jQuery("#submitbutton").attr('href', url + "?bank=" + bank + "&orderid=" + order_id);
    } else {
        if (bank) {
            jQuery("#submitbutton").attr('href', url + "?bank=" + bank);
        } else {
            jQuery("#submitbutton").attr('href', url);
        }
    }
}
function setLocation(url){
    window.location.href = url;
}

function go_pay() {
    jQuery("body").append("<div id='overlay' style='background:#000;display:block;z-index:300;width:1200px;position:absolute;top:0;left:0;'></div>"), jQuery(document).scrollTop();
    var c = jQuery(document).scrollLeft(),
        d = jQuery(window).height() ,
        h = jQuery(document).height() ,
        e = jQuery(window).width();
    jQuery("#overlay").css({
        opacity: "0.2",
        height: h,
        left: c,
        width: e
    });
    var left  = (e/2)-(jQuery('#go_pay_window').outerWidth()/2),
    top   = (jQuery(window).height()/2)-(160/2);
    //jQuery('#go_pay_window').css('left',left+"px");
    //jQuery('#go_pay_window').css('top',top+"px");
    jQuery('#go_pay_window').fadeIn();
}

function window_close() {
    jQuery("#overlay").length > 0 && jQuery("#overlay").remove(), jQuery(".popup-wrap.popup-orderEnd").fadeOut()
}
