jQuery(document).ready(function(){
    jQuery("#sun").click(function(){
        jQuery( "#dialog" ).dialog( "open" );
        jQuery("#barcode").focus();
    });
    jQuery("#closebutton").click(function(){
        jQuery( "#dialog" ).dialog( "close" );
    });
    jQuery("#getcode_num").click(function(){
        jQuery(this).attr("src",'code_num.php?' + Math.random());
    });
    jQuery('.ds_dialog_yes').click(function(){
        var flag =  checkAddressForm();
        if(flag == true){
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
            url: 'barcodeVerify.php',
            beforeSend:loading,
            data: {barcode:barcode,verifycode:verifycode},
            success: function(data, textStatus){
                verifyResult = data;
            },
            //  dataType: "json",
            error: function(){
                alert('error');
            }
        });
        if(verifyResult != "1"){
            document.getElementById("barcodeVerifyResult").innerHTML = verifyResult;
        }else{
            jQuery("#verifycode_error").html("验证码不符");
            jQuery("#verifycode").addClass("error");
            jQuery('#barcodeVerifyResult').html("");
        }

    }
    function isEmpty (s) {
        return s == '';
    }
    function loading(){
        var html = '<div><img src="auth/ajax-loader-tr.gif"/></div>';
        jQuery('#barcodeVerifyResult').html(html);
    }
});