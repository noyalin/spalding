function getAddressContent(jsonData){
    var jsonData = JSON.parse(jsonData);
    if(jsonData != undefined && jsonData.length == 1)
    {
        htmlContent = getOneAddressSelected(jsonData[0]);
        return htmlContent;
    }

    html = '';
    dataLength = jsonData.length;
    jQuery.each(jsonData, function(index,eachValue){
        selected = '';
        if(index == (dataLength*1-1)){
            selected = "selected";
        }
        var valueId = eachValue.value;
        var label = eachValue.label;
        html = html + ' <div class="step_radio ' + selected + '"><input type="radio" checked="" value="'+valueId+'" id="billing_address_id_'+valueId+'"  name="billing_address_id"><label  for="billing_address_id_'+valueId+'">'+label+' </label>' +
            '<div class="btns"><a class=""  href="javascript:;" onclick="modifyAddress('+valueId+')"><span>修改地址</span></a></div>' + '</div>';
        console.log(html);
    });
    return html;
}
function getOneAddressSelected(data){
    var valueId = data.value;
    var label = data.label;
    html = ' <div class="step_radio selected"><input type="radio" checked="" value="'+valueId+'" id="billing_address_id_'+valueId+'"  name="billing_address_id"><label  for="billing_address_id_'+valueId+'">'+label+' </label>' +
        '<div class="btns"><a class=""  href="javascript:;" onclick="modifyAddress('+valueId+')"><span>修改地址</span></a></div>' + '</div>';
    return html;
}
function getAddress(id){
    if(jQuery("#shipping_address_id")){
        jQuery("#shipping_address_id").val(id);
    }
    billingStep.save();
    //shippingMethodStep.save();
    jQuery.ajax({
        type: 'POST',
        //url: 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN',
        url: '/customer/address/getCurrentCustomerAddressesAjax/',
        success: function(data){
            var htmlContent = getAddressContent(data);
            jQuery('.step_radio_wrap').html(htmlContent);

        },
        error: function(e){}
    });
}
function isEmpty (s) {
    return s == '';
}
function checkAddressForm(){
    var flag = true;
    var firstnameJQ = jQuery("#firstname");

    if (isEmpty(firstnameJQ.val())){
        jQuery("#firstname_error").html("收货人姓名不能为空");
        firstnameJQ.addClass("error");
        flag = false;
    }else if (firstnameJQ.val().length>20){
        jQuery("#firstname_error").html("收货人姓名不能太长");
        firstnameJQ.addClass("error");
        flag = false;
    }
    var regionJQ = jQuery("#region_id");
    var cityJQ = jQuery("#city_id");
    var districtJQ = jQuery("#district_id");

    if (regionJQ.val() == 0){
        jQuery("#address_error").html("请选择省/直辖市");
        regionJQ.addClass("error");
        flag = false;
    }else if (cityJQ.val() == 0){
        jQuery("#address_error").html("请选择城市");
        cityJQ.addClass("error");
        flag = false;
    }else if (districtJQ.val() == 0){
        jQuery("#address_error").html("请选择区县");
        districtJQ.addClass("error");
        flag = false;
    }
    var postcode = jQuery("#postcode").val();
    var regionName = regionJQ.find("option:selected").text(); ;
    var indexTW = regionName.indexOf("台湾");
    var indexAM = regionName.indexOf("澳门");
    var indexXG = regionName.indexOf("香港");
    if (isEmpty(postcode) || postcode == "6位数字"){
        jQuery("#postcode_error").html("邮编不能为空");
        jQuery("#postcode").addClass("error");
        flag = false;
    }
    var streetJQ = jQuery("#street_1");
    if (isEmpty(streetJQ.val())){
        jQuery("#street_error").html("街道地址不能为空");
        streetJQ.addClass("error");
        flag = false;
    }
    var phoneJQ = jQuery("#telephone1");
    if (isEmpty(phoneJQ.val())){
        jQuery("#phone_error").html("电话不能为空");
        phoneJQ.addClass("error");
        flag = false;
    }
    return flag;
}

function saveAddressDefault(id){
    jQuery.ajax({
        type: 'POST',
        //url: 'https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN',
        url: '<?php echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB); ?>/customer/address/setDefaultAddressAjax/',
        data:{
            id:id
        },
        success: function(data){
            var dataObj = eval("("+data+")");

        },
        error: function(e){}
    });
}