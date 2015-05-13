jQuery(document).ready(function(){
    jQuery("#sun").click(function(){
        jQuery( "#dialog" ).dialog( "open" );
        jQuery("#barcode").focus();
    });
    jQuery("#closebutton").click(function(){
        jQuery( "#dialog" ).dialog( "close" );
    });
    jQuery("#getcode_num").click(function(){
        jQuery(this).attr("src",'/code_num.php?' + Math.random());
    });

    jQuery(".ds_dialog_buttons").delegate(".ds_dialog_yes","click",function(){
        var flag =  checkAddressForm();
        if(flag == true){
            jQuery(this).removeClass("ds_dialog_yes");
            jQuery(this).addClass("ds_dialog_process");
            var barcode = jQuery("#barcode");
            var verifycode = jQuery("#verifycode");
            jQuery("#barcode_error").html("");
            jQuery("#verifycode_error").html("");
            barcode.removeClass("error");
            verifycode.removeClass("error");
            sure_button_onclick();
        }
    });
    function checkAddressForm(){
        var flag = true;
        var barcode = jQuery("#barcode");
        var verifycode = jQuery("#verifycode");
        if (isEmpty(barcode.val())){
            jQuery("#barcode_error").html("防伪码不能为空");
            barcode.addClass("error");
            flag = false;
        }
        if (isEmpty(verifycode.val())){
            jQuery("#verifycode_error").html("验证码不能为空");
            verifycode.addClass("error");
            flag = false;
        }
        return flag;
    }
    function sure_button_onclick(){
        var barcode =jQuery("#barcode").val();
        var verifycode =jQuery("#verifycode").val();
        var verifyResult;
        jQuery.ajax({
            async:false,
            type: 'POST',
            url: '/barcodeVerify.php',
            beforeSend:loading,
            data: {barcode:barcode,verifycode:verifycode},
            success: function(data, textStatus){
                verifyResult = data;
                jQuery(".ds_dialog_process").addClass("ds_dialog_process_second");
                jQuery(".ds_dialog_process_second").removeClass("ds_dialog_process");
            },
            //  dataType: "json",
            error: function(){
                //alert('error');
            }
        });
        if(verifyResult != "1"){
            document.getElementById("barcodeVerifyResult").innerHTML = verifyResult;
            if(verifyResult.indexOf("没有此数码，请当心该产品是假冒产品")>=0  ||   verifyResult.indexOf("该防伪码已超过规定查询次数")>=0 ||  verifyResult.indexOf("没有此防伪码，请当心该产品是假冒产品")>=0){
                jQuery(".ds_dialog_process_second").addClass("ds_dialog_yes");
                jQuery(".ds_dialog_process_second").removeClass("ds_dialog_process");
            }else{
                jQuery("#checkmessage").html("");
            }
        }else{
            jQuery(".ds_dialog_process_second").addClass("ds_dialog_yes");
            jQuery(".ds_dialog_process_second").removeClass("ds_dialog_process");
            jQuery("#verifycode_error").html("验证码不符");
            jQuery("#verifycode").addClass("error");
            jQuery('#barcodeVerifyResult').html("");
        }

    }
    function isEmpty (s) {
        return s == '';
    }

});