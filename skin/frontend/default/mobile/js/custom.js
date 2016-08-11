/*同意条款*/
function agreeRule(obj) {
    jQuery(obj).parents(".rowsWrap").hide();
    jQuery(".content-wrapper").removeAttr("style");
    jQuery.ajax({
        type: 'POST',
        url: jQuery('#agree').val()
    });
}
//判断定制页面
function setPageid(){
    var i="";
    if(jQuery(".madeP_1_btn").hasClass("madeP_btn_now")){
        i=0;
    }else{
        i=1;
    }
    return i;
}

/*下拉菜单*/
function dropDownMeun(obj){
    jQuery(obj).siblings("ul").slideToggle();
}

/*选择字号*/
function selectfontSize(obj){
    var i=setPageid();
    var num=jQuery(obj).index();
    var size=jQuery(obj).attr("size");
    var _dataFamTex = jQuery(obj).html();
    var _datas=jQuery(obj).attr("datas");
    var str=jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(0) input").val().trim();
    var str2=jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(1) input").val().trim();
    var len=getTxtCnt(size);
    jQuery(obj).parent("ul").slideUp();
    jQuery(obj).parent("ul").siblings(".chosFam").html(_dataFamTex);
    jQuery(obj).parent("ul").siblings(".chosFam").attr("size",size);
    jQuery(".madeTexBox .madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 "+_datas);
    if(num==3){
        jQuery(".madeTexBox .madeTexWrap").eq(i).find(".select_P2").show();
        jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(1)").removeClass("smaSizeInp");
        jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(1) input").removeAttr("disabled");
        str2=getMadeText(str2, len);
        jQuery(".madeTexBox .madeTexWrap").eq(i).find(".select_P2").html(str2);
    }else{
        jQuery(".madeTexBox .madeTexWrap").eq(i).find(".select_P2").hide();
        jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(1)").addClass("smaSizeInp");
        jQuery(obj).parents(".selectsize").find(".madeTextInpBox:eq(1) input").attr("disabled","disabled");
        str=getMadeText(str, len);
        jQuery(".madeTexBox .madeTexWrap").eq(i).find(".select_P1").html(str);
    }
}
//判断长度
function getTxtCnt(size) {
    if (size == 1 || size == 4) {
        return 16;
    } else if (size == 2) {
        return 10;
    } else {
        return 6;
    }
}
//输入内容
function formatInputString(str){
    var str = str.replace(/   */g," ");
     str = str.replace(/   */g," ");
    return str;
}
function getMadeText(str, len){
    if(!str) return "";
    if(len<= 0) return "";
    var templen=0;
    for(var i=0;i<str.length;i++){
        if(str.charCodeAt(i)>255){
            templen+=2;
        }else{
            templen++
        }
        if(templen == len){
            return str.substring(0,i+1);
        }else if(templen >len){
            return str.substring(0,i);
        }
    }
    return str;
}

function madeinput(obj){
    var str = jQuery(obj).val();
    var i=jQuery(obj).parent(".madeTextInpBox").index();
    var size=jQuery(obj).parents(".selectsize").find(".fontFamilySel Span").attr("size");
    var _len=getTxtCnt(size);
    var pageid = setPageid();
    if(i==0){
        var _text =jQuery(".cusMadeCons  .madeTexWrap").eq(pageid).find(".select_P1");
    }else if(i==1){
        var _text =jQuery(".cusMadeCons  .madeTexWrap").eq(pageid).find(".select_P2");
    }
    var str=cut_str(str,_len);
    _text.html(str);
}
function getSwapTxt(obj){
    var i=jQuery(obj).parents(".madeBoxCons").index();
    var _val =jQuery(obj).val();
    var str = formatInputString(_val);
    var _txt = str.trim();
    var size=jQuery(".madeBoxCons").eq(i).find(".fontFamilySel Span").attr("size");
    var _cnt =getTxtCnt(size);
    return getMadeText(_txt, _cnt);
}
function cut_str(str, len){
    var str = formatInputString(str);
    str = str.trim();
    var char_length = 0;
    for (var i = 0; i < str.length; i++){
        var son_str = str.charAt(i);
        encodeURI(son_str).length > 2 ? char_length += 2 : char_length += 1;
        if (char_length >= len){
            var sub_len = char_length == len ? i+1 : i;
            return str.substr(0, sub_len);
        }
    }
    return str;
}
/*切换选项*/
function selecttab(obj){
    var i=jQuery(obj).index();
    var _cls=jQuery(obj).attr("class");
    if(_cls=="madesize"){
        madeTabTex();
    }else if(_cls=="madefont"){
        madeTabFont();
    }
    jQuery(obj).addClass("active").siblings().removeClass("active");
    var idx=jQuery(obj).parents(".madeBoxCons").attr("id");
    jQuery("#"+idx+" .madeinfobox").eq(i).addClass("active").siblings(".madeinfobox").removeClass("active");
}
/*
 *
 * */
function selectpage(i){
    jQuery(".cusRemind .cusRemindBox").eq(i).css("opacity","1").siblings().css("opacity","0");
    jQuery(".cusMadebtn .madeP_btn").eq(i).addClass("madeP_btn_now").siblings("").removeClass("madeP_btn_now");
    jQuery(".cusMadeBox .madeBoxCons").eq(i).show().siblings().hide();
    jQuery(".madeStepNone .madeTex_ball").find("div").eq(i).show().siblings().hide();
    jQuery(".madeStepTex .madeTex_ball").find("div").eq(i).show().siblings().hide();
    jQuery(".madeStepFont .cusMadeBox:eq(0) .madeTex_ball").find("div").eq(i).show().siblings().hide();
    jQuery(".madeStepFont .cusMadeBox:eq(1) .madeTex_ball").find("div").eq(i).show().siblings().hide();
    jQuery(".madeStepTex .madeTexWrap").eq(i).show().siblings().hide();
    var madeNowtxt=jQuery(".madeBoxCons").eq(i).find(".madeKindTitNon").hasClass("madeNow");
    if(madeNowtxt){
        showoriginBall();
    }else{
        if(jQuery(".madetitle .madefont").hasClass("active")){
            jQuery(".madeStepFont").show().siblings(".madeStepTex,.madeStepNone").hide();
            if(jQuery(".madeKindTitTex .font1").hasClass("active")){
                jQuery(".madeStepFont .cusMadeBox:eq(0)").show().siblings().hide();
            }else if(jQuery(".madeKindTitTex .font2").hasClass("active")){
                jQuery(".madeStepFont .cusMadeBox:eq(1) ").show().siblings().hide();
            }
        }
        if(jQuery(".madetitle .madesize").hasClass("active")){
            jQuery(".madeStepTex").show().siblings(".madeStepFont,.madeStepNone").hide();
        }
    }
}
//是否可以提交“完成定制”  0:不可以 1：可以
var __isCanBooking = null;
//切换定制按钮
function madeBtn(obj){
    var i=jQuery(obj).index();
    if(setPageid() != i){   //当切换到不同的选项的时候，需要去检测一下是否需要去保存
        var _canNext = true;        //默认表示可以通过
        if(i==0){
            _canNext=checksubmit('#made_p2 .submitY');
        }else if(i==1){
            _canNext=checksubmit('#made_p1 .submitY');
        }
        //当需要保存的时候，不可以切换选项；
        if(_canNext==true){
            var _position=i+1;
            jQuery.ajax({
                type: 'POST',
                url: jQuery('#check').val(),
                data: {position: _position, sku:jQuery('#sku').val()},
                success: function (res) {
                    dataObj = ajaxEvalJson(res);
                    if (dataObj != null) {
                        resetView(dataObj['type'], dataObj['content1'], dataObj['content2'],
                            dataObj['content3'], dataObj['content4'], _position-1);
                    }
                }
            });
            selectpage(i);
        }
    }else{
        console.log('同一个id，不做更新');
    }


}

/*显示原始球皮*/
function showoriginBall(){
    jQuery(".madeStepNone").show().siblings(".madeStepTex,.madeStepFont").hide();
}
// notDo
function notTodo(tit,cons){
    var _madeLoading = jQuery(".notTodo");
    var _madeLoadingBox = jQuery("#noTodoBox");
    var _h2 = _madeLoading.find("h2");
    var _p = _madeLoading.find("p");
    _madeLoading.show();
    _h2.text(tit);
    _p.text(cons);
}

//提交判断
function submitYP_CheckText(txtInputId){
    var reg = new RegExp("^[(\u0000-\u00ff|\u4e00-\u9fa5|\uff00-\uffff)]+$","g");
    var txtInput = jQuery(txtInputId);
    if(jQuery(txtInput).val()){
        //判断中文字黑名单;
        var count=[];//存放敏感词的个数；
        var arr = jQuery("#blacklist").html().split(';')
        jQuery.each(arr,function(i){
            if(jQuery(txtInput).val().indexOf(arr[i])>=0)
            {
                count.push(arr[i]);
            }
        });
        if(!reg.test(txtInput.val())){
            notTodo("提示","不能输入特殊符号！");
            txtInput.focus();
        }else{
            if(count.length>0){
                var tips2='"'+jQuery(txtInput).val()+'"包含"'+count[0]+'"敏感词！';
                notTodo("提示",tips2);
                txtInput.focus();
                return false;
            }else{
                return true;
            }
        }
    }else{
        return true;
    }
}

/* 选择文字球皮*/
function setfontBall(num) {
    var i = setPageid();
    jQuery(".madeBoxCons").eq(i).find(".madeKindTitTex").addClass("madeNow").siblings(".madeKindTitNon").removeClass("madeNow");
    jQuery(".madeBoxCons").eq(i).find(".madeKindTitNon span").removeClass("active");
    madeTabFont();
    if (num == 3) {
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitTex .font1").addClass("active").siblings().removeClass("active");
        jQuery(".madeStepFont .cusMadeBox:eq(0)").show().siblings().hide();
    } else if (num == 4) {
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitTex .font2").addClass("active").siblings().removeClass("active");
        jQuery(".madeStepFont .cusMadeBox:eq(1)").show().siblings().hide();
    }else{
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitNon").addClass("madeNow").siblings(".madeKindTitTex").removeClass("madeNow").find("span").removeClass("active");
        jQuery(".madeBoxCons").eq(i).find(".madeBoxCons").find(".disable").show();
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitNon span").addClass("active");
        showoriginBall();
    }
}
function  disable_hd(obj){
    jQuery(obj).parents(".madeBoxCons").find(".disable").hide();
}
function  disable_sh(obj){
    jQuery(obj).parents(".madeBoxCons").find(".disable").show();
}
function madeTabFont(){
    if(jQuery(".madeKindTitTex").hasClass("madeNow")){
        jQuery(".madeStepFont").show().siblings(".madeStepTex,.madeStepNone").hide();
    }else{
        showoriginBall();
    }

}
/*切换字号球皮*/
function madeTabTex(){
    jQuery(".madeStepTex").show().siblings(".madeStepNone,.madeStepFont").hide();
}

function showComfBox(){
    jQuery(".comfPop,.comfBox").show();
}
function showcomfResBox(){
    jQuery(".comfPop,.comfResBox").show();
}
function closecomfResBox(){
    jQuery(".comfPop,.comfResBox").hide();
}
function closeComfBox(){
    jQuery(".comfPop,.comfBox").hide();
}

function saveDatas(obj){
    var i=setPageid();
    if(i==0){
        var _madeValue = jQuery("#made_p1 .madeKindTitTex").attr("dataVal");
        var text1 = getSwapTxt(jQuery("#made_p1 .madeTextInpBox:eq(0) input"));
        var text3 = getSwapTxt(jQuery("#made_p1 .madeTextInpBox:eq(1) input"));
        var size = jQuery("#made_p1 .fontFamilySel").find(".chosFam").attr("size");
        var font = jQuery("#made_p1 .madeKindTitTex").find(".active").attr("data-family");
        var cutPos ="";
        var pic="";
        var sku = jQuery('#sku').val();
        var datas = {
            position: 1,
            type: _madeValue,
            cut_pos: cutPos,
            originalImg: pic,
            text1: text1,
            text3: text3,
            size: size,
            font: font,
            sku: sku
        };
    }else if(i==1){
        var _madeValue = jQuery("#made_p2 .madeKindTitTex").attr("dataVal");
        var text2 = getSwapTxt(jQuery("#made_p2 .madeTextInpBox:eq(0) input"));
        var text4 = getSwapTxt(jQuery("#made_p2 .madeTextInpBox:eq(1) input"));
        var size = jQuery("#made_p2 .fontFamilySel").find(".chosFam").attr("size");
        var font = jQuery("#made_p2 .madeKindTitTex").find(".active").attr("data-family");
        var cutPos ="";
        var pic="";
        var sku = jQuery('#sku').val();
        var datas = {
            position:2,
            type: _madeValue,
            cut_pos: cutPos,
            originalImg: pic,
            text2: text2,
            text4: text4,
            size: size,
            font: font,
            sku: sku
        };
    }
    madeLoading("提交成功","数据加载中,请耐心等待...");
    jQuery.ajax({
        type: 'POST',
        url: jQuery('#complete').val(),
        data: datas,
        success: function (res) {
            dataObj = ajaxEvalJson(res);
            if (dataObj != null) {
                resetView(dataObj['type'], dataObj['content1'], dataObj['content2'], dataObj['content3'], dataObj['content4'],i);
            }
        },
        complete:function(){
            madeLoadingClose();
            __isCanBooking = 1;
        }
    });
}
//检查是否需要保存
function checksubmit(obj){
    var parent=jQuery(obj).parents(".madeBoxCons").attr("id");
    var parent="#"+parent;
    var text=jQuery(parent).find(".selectsize .chosFam").text();
    var _val=jQuery(parent).find(" .madeTextInpBox:eq(0) input").val();
    var _val2=jQuery(parent).find(" .madeTextInpBox:eq(1) input").val();
    var str1 = formatInputString(_val);
    var str2 = formatInputString(_val2);
        str1 = str1.trim();
        str2 = str2.trim();
    if(jQuery(parent).find(".madeKindTitTex").hasClass("madeNow") ){
        if(text=="小号双"){
            if (str1.length == 0 && str2.length == 0) {
                notTodo("提示","至少输入一行内容");
            }
            else if(!submitYP_CheckText(parent+" .madeTextInpBox:eq(0) input")) {
                console.log("1")
            } else if(!submitYP_CheckText(parent+" .madeTextInpBox:eq(1) input")) {
                console.log("2")
            }else{
                if(jQuery(parent).find(".submitY").attr("disabled")=="disabled" ){
                    return true;
                }else{
                    showComfBox(obj);
                }
            }
        }
        else{       //单行文字
            if (str1.length == 0) {
                notTodo("提示","输入的内容不能为空！");
            }else if(!submitYP_CheckText(parent+" .madeTextInpBox:eq(0) input")) {
                console.log("1")
            }else{
                if(jQuery(parent).find(".submitY").attr("disabled")=="disabled" ){
                    return true;
                }else{
                    showComfBox(obj);
                }
            }
        }
    }
    else{
        return true;
    }
}
// made loading
function madeLoading(tit,cons){
    var _madeLoading = jQuery(".madeLoading");
    var _madeLoadingBox = jQuery("#madeLoadingBox");
    var _h2 = _madeLoading.find("h2");
    var _p = _madeLoading.find("p");
    _madeLoading.show();
    _h2.text(tit);
    _p.text(cons);
}
//made loading colse
function madeLoadingClose(){
    var _madeLoading = jQuery(".madeLoading");
    _madeLoading.hide();
}

// notDo
function notTodo(tit,cons){
    var _madeLoading = jQuery(".notTodo");
    var _madeLoadingBox = jQuery("#noTodoBox");
    var _h2 = _madeLoading.find("h2");
    var _p = _madeLoading.find("p");
    _madeLoading.show();
    _h2.text(tit);
    _p.text(cons);
}
function ajaxEvalJson(data) {
    var dataObj = eval("(" + jQuery.trim(data) + ")");
    return dataObj;
}
function resetView(_dataType, value1, value2, value3, value4,position) {
    var i=position;
    closeComfBox();
    setfontBall(value4);
    selectpage(i);
    if(value4){
        jQuery("#custom_flag"+(i+1)).val(2);
        madeTabTex();
        jQuery(".madeBoxCons").eq(i).find(".madetitle span").eq(2).addClass("active").siblings("").removeClass("active");
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitTex").addClass("madeNow").siblings("").removeClass("madeNow");
        jQuery(".madeBoxCons").eq(i).find(".madeinfobox").eq(2).addClass("active").siblings("").removeClass("active");
        jQuery(".madeBoxCons").eq(i).find(".submitN").removeAttr("disabled").siblings(".submitY").attr("disabled","disabled");
        jQuery(".madeBoxCons").eq(i).find(".madetitle .disable2").show();
        jQuery(".madeBoxCons").eq(i).find(".madetitle .disable").hide();
        jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam").attr("size",value3);
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(0).find("input").val(value1);
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(1).addClass("smaSizeInp");
        var text1=jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1");
        if(value1==null){
            text1.text("");
        }else{
            text1.text(value1);
        }
        if(value3==4){
            jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam ").text("小号双");
            var text2=jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P2");
            if(value2==null){
                text2.text("");
            }else{
                text2.text(value2);
            }
            jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(1).find("input").val(value2);
            jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(1).removeClass("smaSizeInp");
            jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 size_40 lineTwo");
        }else if(value3==3){
            jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam ").text("大号");
            jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 size_80");
        }else if(value3==2){
            jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam ").text("中号");
            jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 size_60");
        }else if(value3==1){
            jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam ").text("小号单");
            jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 size_40");
        }
    }else{
        showoriginBall();
        jQuery(".madeBoxCons").eq(i).find(".madetitle span").eq(0).addClass("active").siblings("").removeClass("active");
        jQuery(".madeBoxCons").eq(i).find(".madeinfobox").eq(0).addClass("active").siblings("").removeClass("active");
        jQuery(".madeBoxCons").eq(i).find(".madeKindTitNon").addClass("madeNow").siblings(".madeKindTitTex").removeClass("madeNow");
        jQuery(".madeBoxCons").eq(i).find(".submitY").removeAttr("disabled").siblings(".submitN").attr("disabled","disabled");
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(0).find("input").val("");
        jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1,.select_P2").text("");
        jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam ").text("大号");
        jQuery(".madeBoxCons").eq(i).find(".selectsize .chosFam").attr("size",3);
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(0).find("input").attr("maxlength",100);
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(1).find("input").val("");
        jQuery(".madeBoxCons").eq(i).find(".madeTextInp .madeTextInpBox").eq(1).addClass("smaSizeInp");
        jQuery(".madeStepTex").find(".madeTexWrap").eq(i).find(".select_P1").removeClass().addClass("select_P1 size_80");
        jQuery(".madeBoxCons").eq(i).find(".madetitle .disable").show();
        jQuery(".madeBoxCons").eq(i).find(".madetitle .disable2").hide();
    }
}
function comfResh() {
    closecomfResBox();
    var position=setPageid()+1;
    jQuery.ajax({
        type: 'POST',
        url: jQuery('#reset').val(),
        data: {position: position, sku:jQuery('#sku').val()},
        success: function (res) {
            resetView(3, "", "", "", "",position-1);
            jQuery("#custom_flag"+position).val(0);
            __isCanBooking=0;
        }
    });
}
function inputCart(){
    var i=setPageid()+1;
    var familyflag=jQuery("#made_p"+i).find(".madeKindTitTex").hasClass("madeNow");
    var saveflag=jQuery("#made_p"+i).find(".submitY").attr("disabled");
    if(saveflag=="disabled"){
        saveflag=true;
    }else{
        saveflag=false;
    }
    console.log(familyflag+"+"+saveflag)
    if (familyflag!=saveflag) {
        notTodo("提示","定制条件已变更，请先保存定制，才可以加入购物车，为您带来的不便，还请谅解！");
        return false;
    }else{
        return true;
    }
}

function isCustom() {
    var _custom_flag1 = jQuery("#custom_flag1").val();
    var _custom_flag2 = jQuery("#custom_flag2").val();
    if (_custom_flag1 == "0" && _custom_flag2 == "0") {
        notTodo("提示", "请先进行定制，才可以加入购物车，为您带来的不便，还请谅解！");
        return false;
    } else {
        if (_custom_flag1 != "0" && _custom_flag2 != "0") {
            jQuery("#custom_num").val(jQuery("#double").val());
            jQuery("#sub_sku").val("-ID02");
        } else {
            jQuery("#custom_num").val(jQuery("#single").val());
            jQuery("#sub_sku").val("-ID01");
        }
        return true;
    }
}
/*
 * 完成定制
 * */
function cartSub_login() {
    if (inputCart() && isCustom()) {
        return true;
    } else {
        return false;
    }
}
function notTodo(tit,cons){
    var _madeLoading = jQuery(".notTodo");
    var _madeLoadingBox = jQuery("#noTodoBox");
    var _h2 = _madeLoading.find("h2");
    var _p = _madeLoading.find("p");
    _madeLoading.show();
    _h2.text(tit);
    _p.text(cons);
}

jQuery(function () {
    var bind_name = 'input';
    if (navigator.userAgent.indexOf("MSIE") != -1){
        bind_name = 'propertychange';
    }
    jQuery(".rowsAgr").click(function(){
        agreeRule(this);
    });
    jQuery(".chosFam").click(function(){
        dropDownMeun(this);
    });
    jQuery(".font1").click(function(){
        setfontBall(3);
        disable_hd(this);
    });
    jQuery(".font2").click(function(){
        setfontBall(4);
        disable_hd(this);
    });
    jQuery(".madeKindTitNon").click(function(){
        setfontBall();
        disable_sh(this);
    });
    jQuery(".madetitle .disable").click(function(){
        var tips="提示";
        var cont="请选择字体";
        notTodo(tips,cont);
    });
    jQuery(".fontSizeList li").click(function(){
        selectfontSize(this);
    });
    jQuery(".madetitle span").click(function(){
        selecttab(this);
    });
    jQuery(".cusMadebtn .madeP_btn").click(function(){
        madeBtn(this);
    });
    jQuery('.madeTextInpBox input').bind(bind_name, function(){
        madeinput(this);
    })
    jQuery(".submitY").click(function(){
        checksubmit(this);
    });
    jQuery(".saveMadeN").click(function(){
        closeComfBox();
    });
    jQuery(".saveMadeY").click(function(){
        saveDatas(this);
    });
    jQuery(".submitN").click(function(){
        showcomfResBox();
    });
    jQuery(".comfResBox .resY").click(function(){
        comfResh();
    });
    jQuery(".comfResBox .resN").click(function(){
        closecomfResBox();
    });
    jQuery(".closeTodo").click(function(){
        jQuery(this).parents(".notTodo").hide();
    });
})
