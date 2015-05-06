jQuery(function(){
    jQuery("#agree_terms").click(function(){
        if("checked" == jQuery(this).attr("checked")){
            jQuery("#advice-required-entry-agree_terms").hide();
        }else{
            jQuery("#advice-required-entry-agree_terms").show();
        }

    });
});