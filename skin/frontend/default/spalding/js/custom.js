//var made_position = 0;
//var made_type = new Array(2);
function optioninit() {
    document.getElementsByName('super_attribute[2094]')[0].checked = 'checked';
}
//function getPosition() {
//    return made_position;
//}
//function setPosition(position) {
//    made_position = position;
//}
//function getType() {
//    return made_type;
//}
//function setType(type) {
//    made_type[made_position] = type;
//}

jQuery(function() {
    var spanLen = jQuery('#customMade_fontSize_1 input[type="radio"]').length;
    // jQuery('#customMade_fontSize_1 span').removeClass('checked');
    // jQuery('#customMade_fontSize_1 input[type="radio"]').removeAttr('checked');

    jQuery('#customMade_fontSize_1 span').click(function(){
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_1 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_1 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });
});

jQuery(function() {
    var spanLen = jQuery('#customMade_fontSize_2 input[type="radio"]').length;
    // jQuery('#customMade_fontSize_2 span').removeClass('checked');
    // jQuery('#customMade_fontSize_2 input[type="radio"]').removeAttr('checked');

    jQuery('#customMade_fontSize_2 span').click(function(){
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_2 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_2 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });
});



jQuery(function(){
    //获取初始数据
    var _imgIni = jQuery("#avatar");
    var _imgIniVal = _imgIni.attr("src");

    //开始定制按钮
    //暂时隐藏step1层 后期看是否是进行页面跳转
    jQuery(".step_1_Btn a").click(function(){
        jQuery(this).parents().find(".madeStep_1").css("display","none");
        jQuery(this).parents().find(".madeStep_2").css("display","block");
    });

    //判断数据是否改动，进行相应的操作
    //点击P1按钮
    jQuery(".madeP_1_btn").click(function(){
        var _imgNowVal = _imgIni.attr("src");
        var _texNow = jQuery(".madeTexWrap").find(".select_P2").html();
        var _texIni2 = jQuery(".select_N2").html();

        if(_imgNowVal == _imgIniVal && _texNow == _texIni2){
            //未改动
            jQuery(this).addClass("madeP_btn_now");
            jQuery(this).siblings(".madeP_2_btn").removeClass("madeP_btn_now");
            jQuery(this).siblings(".madeBoxCons_p1").css("display","block");
            jQuery(this).siblings(".madeBoxCons_p2").css("display","none");
            jQuery(this).parents().find(".select_P1").css("display","block");
            jQuery(this).parents().find(".select_P2").css("display","none");
            jQuery("#options_pos").val(1);


            // TODO
            jQuery.ajax({
                type: 'POST',
                url: jQuery('#check').val(),
                data: {position: 1},
                success: function (res) {
                    dataObj = ajaxEvalJson(res);
                    if (dataObj != null) {
                        resetView_1(dataObj['type'], dataObj['content1'], dataObj['content2']);
                    }
                }
            });
            //resetView_1(2, "abcdeft", 1);
        }else{
            //已改动
            jQuery(this).siblings(".madeBoxCons_p2").find(".madeBoxFuns").css("display","none");
            jQuery(this).siblings(".madeBoxCons_p2").find(".madeSubmit").css("display","none");
            jQuery(this).siblings(".madeBoxCons_p2").find(".comfBox").css("display","block");
        }



    });
    //点击P2按钮
    jQuery(".madeP_2_btn").click(function(){
        var _imgNowVal = _imgIni.attr("src");
        var _texNow = jQuery(".madeTexWrap").find(".select_P1").html();
        var _texIni1 = jQuery(".select_N1").html();
        if(_imgNowVal == _imgIniVal && _texNow == _texIni1){
            //未改动
            jQuery(this).addClass("madeP_btn_now");
            jQuery(this).siblings(".madeP_1_btn").removeClass("madeP_btn_now");
            jQuery(this).siblings(".madeBoxCons_p1").css("display","none");
            jQuery(this).siblings(".madeBoxCons_p2").css("display","block");
            jQuery(this).parents().find(".select_P2").css("display","block");
            jQuery(this).parents().find(".select_P1").css("display","none");
            jQuery("#options_pos").val(2);

            // TODO
            jQuery.ajax({
                type: 'POST',
                url: jQuery('#check').val(),
                data: {position: 2},
                success: function (res) {
                    dataObj = ajaxEvalJson(res);
                    if (dataObj != null) {
                        resetView_2(dataObj['type'], dataObj['content1'], dataObj['content2']);
                    }
                }
            });
            //resetView_2(1, "http://localhost/spalding/skin/frontend/default/spalding/images/customMade/imgPer_1.jpg", "");

        }else{
            //已改动
            jQuery(this).siblings(".madeBoxCons_p1").find(".madeBoxFuns").css("display","none");
            jQuery(this).siblings(".madeBoxCons_p1").find(".madeSubmit").css("display","none");
            jQuery(this).siblings(".madeBoxCons_p1").find(".comfBox").css("display","block");
        }
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
        var pos = jQuery("#options_pos").val();
        if (pos == 1) {
            jQuery("#options_type_p1").val(1);
        } else if (pos == 2) {
            jQuery("#options_type_p2").val(1);
        }
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
        var pos = jQuery("#options_pos").val();
        if (pos == 1) {
            jQuery("#options_type_p1").val(2);
        } else if (pos == 2) {
            jQuery("#options_type_p2").val(2);
        }
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
        var pos = jQuery("#options_pos").val();
        if (pos == 1) {
            jQuery("#options_type_p1").val(null);
        } else if (pos == 2) {
            jQuery("#options_type_p2").val(null);
        }
    });

    //设置字体
    jQuery(".madeBoxCons_p1 .setSize_40").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_40")
    });
    jQuery(".madeBoxCons_p2 .setSize_40").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_40")
    });

    //中号
    jQuery(".madeBoxCons_p1 .setSize_60").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_60")
    });
    jQuery(".madeBoxCons_p2 .setSize_60").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_60")
    });

    //大号
    jQuery(".madeBoxCons_p1 .setSize_80").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P1").attr("class","select_P1 size_80")
    });
    jQuery(".madeBoxCons_p2 .setSize_80").click(function(){
        jQuery(this).parents().find(".madeTexWrap .select_P2").attr("class","select_P2 size_80")
    });
});

function previewImage(file,imgId){
    var _formBtn = document.getElementById('formBtn');
    var _gripImg = document.getElementById('img_grip');
    var _form = document.getElementById('imgFuns');

    var _img = document.getElementById(imgId);
    var _imgSrc = _img.getAttribute("src");


    //size & format
    photoExt=file.value.substr(file.value.lastIndexOf(".")).toLowerCase();//获得文件后缀名
    if(photoExt!='.jpg' && photoExt!='.png'){
        
        _img.setAttribute("src",_imgSrc);

        _form.style.opacity = 0;
        _gripImg.style.opacity = 0;
        _formBtn.style.display = "block";

        alert("请上传后缀名为jpg或png的图片!");
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
    if(fileSize>=8*1024){
        _img.setAttribute("src",_imgSrc);
        _form.style.opacity = 0;
        _gripImg.style.opacity = 0;
        _formBtn.style.display = "block";

        alert("图片最大尺寸为8M，请重新上传!");
        return false;
    }else if(fileSize<=2*1024){
        _img.setAttribute("src",_imgSrc);
        _form.style.opacity = 0;
        _gripImg.style.opacity = 0;
        _formBtn.style.display = "block";

        alert("图片最小尺寸为2M，请重新上传!");
        return false;
    }


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


//定制提交判断
jQuery(function() {

    //提交当前PAGE按钮
    jQuery(".submitY").click(function(){
        jQuery(this).parent(".madeSubmit").css("display","none");
        jQuery(this).parent(".madeSubmit").siblings(".madeBoxFuns").css("display","none");
        jQuery(this).parent(".madeSubmit").siblings(".comfBox").css("display","block");
    });

    //保存 & 放弃定制
    /*jQuery(".saveMadeY").click(function(){
        var _comfBox = jQuery(this).parent().parent(".comfBox");
        var _btnClass = _comfBox.parent(".madeBoxCons").siblings(".madeP_btn").attr("class");
        _comfBox.css("display","none");
        _comfBox.siblings(".madeSubmit").css("display","block");
        _comfBox.siblings(".madeBoxFuns").css("display","block");
        _comfBox.parent(".madeBoxCons").css("display","none");
        _comfBox.parent(".madeBoxCons").siblings(".madeBoxCons").css("display","block");        
    });*/

    jQuery(".saveMadeN").click(function(){
        jQuery(this).parent().parent(".comfBox").css("display","none");
        jQuery(this).parent().parent(".comfBox").siblings(".madeSubmit").css("display","block");
        jQuery(this).parent().parent(".comfBox").siblings(".madeBoxFuns").css("display","block");
    });



    

    
});

//预览按钮
jQuery(function(){
    jQuery(".viewMade").click(function(){
        jQuery.ajax({
            type: 'POST',
            url: jQuery('#preview').val(),
            data: {},
            success: function () {
                document.location.reload();
            }
        });
    });
});

function SwapTxt_1(){
    var _txt_1 = document.getElementById("textInput_1").value;
    var _textP1 = document.getElementById("textMade_P1")
    var _swapTexLen = _txt_1.length;

    if(_swapTexLen <= 8){
        _textP1.innerHTML=_txt_1;
    }else{
        alert("请不要超过8个英文字符")
    }
}

function SwapTxt_2(){
    var _txt_2 = document.getElementById("textInput_2").value;
    var _textP2 = document.getElementById("textMade_P2")
    var _swapTexLen = _txt_2.length;

    if(_swapTexLen <= 8){
        _textP2.innerHTML=_txt_2;
    }else{
        alert("请不要超过8个英文字符")
    }
}