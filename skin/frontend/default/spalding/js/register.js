jQuery(function(){
    jQuery("#agree_terms").click(function(){
        if("checked" == jQuery(this).attr("checked")){
            jQuery("#advice-required-entry-agree_terms").hide();
            jQuery("#submitbutton").show();
            jQuery("#disablesubmitbutton").hide();
        }else{
            jQuery("#advice-required-entry-agree_terms").show();
            jQuery("#submitbutton").hide();
            jQuery("#disablesubmitbutton").show();
        }

    });
});