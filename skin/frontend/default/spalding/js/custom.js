var made_position = 0;
var made_type = new Array(2);
function optioninit() {
    document.getElementsByName('super_attribute[2094]')[0].checked = 'checked';
}
function getPosition() {
    return made_position;
}
function setPosition(position) {
    made_position = position;
}
function getType() {
    return made_type;
}
function setType(type) {
    made_type[made_position] = type;
}

jQuery(function() {
    var spanLen = jQuery('#customMade_fontSize_1 input[type="radio"]').length;
    jQuery('#customMade_fontSize_1 span').removeClass('checked');
    jQuery('#customMade_fontSize_1 input[type="radio"]').removeAttr('checked');

    jQuery('#customMade_fontSize_1 span').click(function(){
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_1 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_1 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });
});

jQuery(function() {
    var spanLen = jQuery('#customMade_fontSize_2 input[type="radio"]').length;
    jQuery('#customMade_fontSize_2 span').removeClass('checked');
    jQuery('#customMade_fontSize_2 input[type="radio"]').removeAttr('checked');

    jQuery('#customMade_fontSize_2 span').click(function(){
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_2 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_2 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });
});

jQuery(function(){
    //开始定制按钮
    //暂时隐藏step1层 后期看是否是进行页面跳转
    jQuery(".step_1_Btn a").click(function(){
        jQuery(this).parents().find(".madeStep_1").css("display","none");
        jQuery(this).parents().find(".madeStep_2").css("display","block");
    });
    //点击P1按钮
    jQuery(".madeP_1_btn").click(function(){
        jQuery(this).addClass("madeP_btn_now");
        jQuery(this).siblings(".madeP_2_btn").removeClass("madeP_btn_now");
        jQuery(this).siblings(".madeBoxCons_p1").css("display","block");
        jQuery(this).siblings(".madeBoxCons_p2").css("display","none");
        jQuery(".select_P1").css("display","block");
        jQuery(".select_P2").css("display","none");
        setPosition(0);
    });
    //点击P2按钮
    jQuery(".madeP_2_btn").click(function(){
        jQuery(this).addClass("madeP_btn_now");
        jQuery(this).siblings(".madeP_1_btn").removeClass("madeP_btn_now");
        jQuery(this).siblings(".madeBoxCons_p2").css("display","block");
        jQuery(this).siblings(".madeBoxCons_p1").css("display","none");
        jQuery(".select_P2").css("display","block");
        jQuery(".select_P1").css("display","none");
        setPosition(1);
    });

    //定制图案或文字时 另一定制收起
    var _madeImgDt = jQuery(".madeKindImg");
    var _madeTexDt = jQuery(".madeKindTex");
    var _imgWrap = jQuery(".madeStepImg");
    var _texWrap = jQuery(".madeStepTex");

    //选择图案
    jQuery(".madeKindTitImg").click(function(){
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        if(_madeTexDt.is(":visible")){
            jQuery(this).siblings(".madeKindImg").slideDown();
            jQuery(this).siblings(".madeKindTex").slideUp().removeClass("madeNow");
        }
        if(_madeImgDt.is(":hidden")){
            jQuery(this).siblings(".madeKindImg").slideDown()
        }
        //隐藏显示编辑
        jQuery(this).parents().find(".madeStepImg").css("display","block");
        jQuery(this).parents().find(".madeStepTex").css("display","none");
        jQuery(this).parents().find(".madeStepNone").css("display","none");
        setType("图片");
    });

    //选择文本
    jQuery(".madeKindTitTex").click(function(){
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        if(_madeImgDt.is(":visible")){
            jQuery(this).siblings(".madeKindTex").slideDown();
            jQuery(this).siblings(".madeKindImg").slideUp().removeClass("madeNow");
        }
        if(_madeTexDt.is(":hidden")){
            jQuery(this).siblings(".madeKindTex").slideDown();
        }
        //隐藏显示编辑
        jQuery(this).parents().find(".madeStepTex").css("display","block");
        jQuery(this).parents().find(".madeStepImg").css("display","none");
        jQuery(this).parents().find(".madeStepNone").css("display","none");
        setType("文字");
    });
    //选择空白
    jQuery(".madeKindTitNon").click(function(){
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        jQuery(this).siblings("dt").slideUp();

        //隐藏显示编辑
        jQuery(this).parents().find(".madeStepNone").css("display","block");
        jQuery(this).parents().find(".madeStepTex").css("display","none");
        jQuery(this).parents().find(".madeStepImg").css("display","none");
        setType("无");
    });

    //设置字体
    jQuery(".madeBoxCons_p1 .setSize_20").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_40")
    });
    jQuery(".madeBoxCons_p2 .setSize_20").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_40")
    });

    //中号
    jQuery(".madeBoxCons_p1 .setSize_30").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_60")
    });
    jQuery(".madeBoxCons_p2 .setSize_30").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_60")
    });

    //大号
    jQuery(".madeBoxCons_p1 .setSize_40").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_80")
    });
    jQuery(".madeBoxCons_p2 .setSize_40").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_80")
    });
});

    function previewImage(file,imgId){
        var _formBtn = document.getElementById('formBtn');
        var _gripImg = document.getElementById('img_grip');
        var _form = document.getElementById('imgFuns');
        if (file.files && file.files[0]){
            var img = document.getElementById(imgId);
            var reader = new FileReader();
            reader.onload = function(evt){img.src = evt.target.result;}
            reader.readAsDataURL(file.files[0]);
            _form.style.opacity = 1;
            _gripImg.style.opacity = 1;
            _formBtn.style.display = "none";
        }else{
            file.select();
            var src = document.selection.createRange().text;
            jQuery("#"+imgId).attr("src",src);
        }


        //size
        photoExt=file.value.substr(file.value.lastIndexOf(".")).toLowerCase();//获得文件后缀名
        if(photoExt!='.jpg'){
            alert("请上传后缀名为jpg的照片!");
            return false;
        }
        var fileSize = 0;
        var isIE = /msie/i.test(navigator.userAgent) && !window.opera;            
        if (isIE && !file.files) {          
             var filePath = file.value;            
             var fileSystem = new ActiveXObject("Scripting.FileSystemObject");   
             var file = fileSystem.GetFile (filePath);               
             fileSize = file.Size;         
        }else {  
             fileSize = file.files[0].size;     
        } 
        fileSize=Math.round(fileSize/1024*100)/100; //单位为KB
        if(fileSize>=10){
            alert("照片最大尺寸为10KB，请重新上传!");
            return false;
        }
    }

var cut_div;  //裁减图片外框div
var avatar;  //裁减图片
var imgdefw;  //图片默认宽度
var imgdefh;  //图片默认高度
var offsetx = 0; //图片位置位移x
var offsety = -303; //图片位置位移y
var divx = 400; //外框宽度
var divy = 400; //外框高度
var cutx = 400;  //裁减宽度
var cuty = 186;  //裁减高度
var zoom = 1; //缩放比例

var zmin = 0.1; //最小比例
var zmax = 10; //最大比例
var grip_pos = 5; //拖动块位置0-最左 10 最右
var img_grip; //拖动块
var img_track; //拖动条
var grip_y; //拖动块y值
var grip_minx; //拖动块x最小值
var grip_maxx; //拖动块x最大值


//图片初始化
function imageinit(){
    cut_div = document.getElementById('cut_div');
    avatar = document.getElementById('avatar');
    imgdefw = avatar.width;
    imgdefh = avatar.height;
    if(imgdefw > divx){
        zoom = divx / imgdefw;
        avatar.width = divx;
        avatar.height = Math.round(imgdefh * zoom);
    }

    avatar.style.left = Math.round((divx - avatar.width) / 2);
    avatar.style.top = Math.round((divy - avatar.height) / 2) - divy;

    if(imgdefw > cutx){
        zmin = cutx / imgdefw;
    }else{
        zmin = 1;
    }
    zmax =  zmin > 0.25 ? 8.0: 4.0 / Math.sqrt(zmin);
    if(imgdefw > cutx){
        zmin = cutx / imgdefw;
        grip_pos = 5 * (Math.log(zoom * zmax) / Math.log(zmax));
    }else{
        zmin = 1;
        grip_pos = 5;
    }

    Drag.init(cut_div, avatar);
    avatar.onDrag = when_Drag;
}

//图片逐步缩放
function imageresize(flag){
    if(flag){
        zoom = zoom * 1.5;
    }else{
        zoom = zoom / 1.5;
    }
    if(zoom < zmin) zoom = zmin;
    if(zoom > zmax) zoom = zmax;
    avatar.width = Math.round(imgdefw * zoom);
    avatar.height = Math.round(imgdefh * zoom);
    checkcutpos();
    grip_pos = 5 * (Math.log(zoom * zmax) / Math.log(zmax));
    img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
}

//获得style里面定位
function getStylepos(e){
    return {x:parseInt(e.style.left), y:parseInt(e.style.top)};
}

//获得绝对定位
function getPosition(e){
    var t=e.offsetTop;
    var l=e.offsetLeft;
    while(e=e.offsetParent){
        t+=e.offsetTop;
        l+=e.offsetLeft;
    }
    return {x:l, y:t};
}

//检查图片位置
function checkcutpos(){
    var imgpos = getStylepos(avatar);

    max_x = Math.max(offsetx, offsetx + cutx - avatar.clientWidth);
    min_x = Math.min(offsetx + cutx - avatar.clientWidth, offsetx);
    if(imgpos.x > max_x) avatar.style.left = max_x + 'px';
    else if(imgpos.x < min_x) avatar.style.left = min_x + 'px';

    max_y = Math.max(offsety, offsety + cuty - avatar.clientHeight);
    min_y = Math.min(offsety + cuty - avatar.clientHeight, offsety);

    if(imgpos.y > max_y) avatar.style.top = max_y + 'px';
    else if(imgpos.y < min_y) avatar.style.top = min_y + 'px';
}

//图片拖动时触发
function when_Drag(clientX , clientY){
    checkcutpos();
}

//获得图片裁减位置
function getcutpos(){
    var imgpos = getStylepos(avatar);
    var x = offsetx - imgpos.x;
    var y = offsety - imgpos.y;
    //var cut_pos = document.getElementById('cut_pos');
    //cut_pos.value = x + ',' + y + ',' + avatar.width + ',' + avatar.height;
    //return true;
    return x + ',' + y + ',' + avatar.width + ',' + avatar.height;
}

//缩放条初始化
function gripinit(){
    img_grip = document.getElementById('img_grip');
    img_track = document.getElementById('img_track');
    track_pos = getPosition(img_track);

    grip_y = track_pos.y;
    grip_minx = track_pos.x + 4;
    grip_maxx = track_pos.x + img_track.clientWidth - img_grip.clientWidth - 5;

    // img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
    // img_grip.style.top = grip_y + "px";

    img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
    img_grip.style.top = grip_y - 115 + "px";

    Drag.init(img_grip, img_grip);
    img_grip.onDrag = grip_Drag;

}

//缩放条拖动时触发
function grip_Drag(clientX , clientY){
    var posx = clientX;
    img_grip.style.top = grip_y - 115 + "px";
    if(clientX < grip_minx){
        img_grip.style.left = grip_minx + "px";
        posx = grip_minx;
    }
    if(clientX > grip_maxx){
        img_grip.style.left = grip_maxx + "px";
        posx = grip_maxx;
    }

    grip_pos = (posx - grip_minx) * 10 / (grip_maxx - grip_minx);
    zoom = Math.pow(zmax, grip_pos / 5) / zmax;
    if(zoom < zmin) zoom = zmin;
    if(zoom > zmax) zoom = zmax;
    avatar.width = Math.round(imgdefw * zoom);
    avatar.height = Math.round(imgdefh * zoom);
    checkcutpos();
}

//页面载入初始化
function avatarinit(){
    imageinit();
    gripinit();
    optioninit();
}

if (document.all){
    window.attachEvent('onload',avatarinit);
}else{
    window.addEventListener('load',avatarinit,false);
}

    jQuery(document).ready(function (){
        function adjust(el, selection) {
            var scaleX = jQuery(el).width() / (selection.width || 1);
            var scaleY = jQuery(el).height() / (selection.width || 1);
            jQuery(el+' img').css({
                width: Math.round(scaleX*jQuery('#avatar').width() ) + 'px',
                height: Math.round(scaleY*jQuery('#avatar').height() ) + 'px',
                marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
                marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
            });
        }
    });

var value = 0;
function avatarrotateleft(){
    value -=90;
    jQuery('#avatar').rotate({ animateTo:value});
}
function avatarrotateright(){
    value +=90;
    jQuery('#avatar').rotate({ animateTo:value});
}

//function completeCustomMade() {
//    //var originalImg = document.getElementById('avatar').src;
//    //jQuery.ajax({
//    //    type: 'POST',
//    //    url: url,
//    //    data: {originalImg: originalImg},
//    //    success: function (data) {
//    //    }
//    //
//    //});
//
//    var select_type = made_type[0] + "-" + made_type[1];
//    var options = jQuery(".super_attribute_2112");
//    for (var i = 0; i < options.length; i++) {
//        if (select_type == options[i].firstElementChild.innerHTML) {
//            options[i].lastElementChild.checked = 'checked';
//        }
//    }
//}
function completeCustomMade(url, pos) {
    var originalImg = document.getElementById('avatar').src;
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: {cut_pos: getcutpos(), originalImg: originalImg, position: pos},
        success: function (res) {
            //jQuery('.madeStep_2').innerHTML(res);
            window.location.reload();
        }
    });
}

function resetCustomMade(url, pos) {
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: {position: pos},
        success: function (res) {
            window.location.reload();
        }
    });
}

var productAddToCartForm = new VarienForm('product_addtocart_form');
productAddToCartForm.submit = function(button, url) {

//        if (this.validator.validate()) {
    var form = this.form;
    var oldUrl = form.action;

    if (url) {
        form.action = url;
    }
    var e = null;
    try {
        this.form.submit();
    } catch (e) {
    }
    this.form.action = oldUrl;
    if (e) {
        throw e;
    }

    if (button && button != 'undefined') {
        button.disabled = true;
    }
//        }
}.bind(productAddToCartForm);

productAddToCartForm.submitLight = function(button, url){
    if(this.validator) {
        var nv = Validation.methods;
        delete Validation.methods['required-entry'];
        delete Validation.methods['validate-one-required'];
        delete Validation.methods['validate-one-required-by-name'];
        // Remove custom datetime validators
        for (var methodName in Validation.methods) {
            if (methodName.match(/^validate-datetime-.*/i)) {
                delete Validation.methods[methodName];
            }
        }

        if (this.validator.validate()) {
            if (url) {
                this.form.action = url;
            }
            this.form.submit();
        }
        Object.extend(Validation.methods, nv);
    }
}.bind(productAddToCartForm);

