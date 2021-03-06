jQuery(function () {
    jQuery('#customMade_fontSize_1 span').click(function () {
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_1 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_1 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });

    jQuery('#customMade_fontSize_2 span').click(function () {
        var radioId = jQuery(this).attr('name');
        jQuery('#customMade_fontSize_2 span').removeClass('checked') && jQuery(this).addClass('checked');
        jQuery('#customMade_fontSize_2 input[type="radio"]').removeAttr('checked') && jQuery('#' + radioId).attr('checked', 'checked');
    });
});

function inputCart(){
    var _imgIni = jQuery("#avatar");
    //p2判断
    var _imgNowVal = _imgIni.attr("src");
    var _texNow_2 = jQuery(".madeTexWrap").find(".select_P2").html();
    var _imgNow_2 = jQuery(".madeTexWrap").find(".select_P2").attr("src");
    var _texIni2 = jQuery(".select_N2").html();
    var _imgIni2 = jQuery(".select_N2").attr("src");

    //p1 判断
    var _imgNowVal = _imgIni.attr("src");
    var _texNow_1 = jQuery(".madeTexWrap").find(".select_P1").html();
    var _imgNow_1 = jQuery(".madeTexWrap").find(".select_P1").attr("src");
    var _texIni1 = jQuery(".select_N1").html();
    var _imgIni1 = jQuery(".select_N1").attr("src");

    //定制队徽新增判断
	var nowPageId = jQuery(".madeP_btn_now").attr("id");
	var isViewPage1Show = jQuery('.viewPage_P1').css('display') === 'block';
	var isViewPage2Show = jQuery('.viewPage_P2').css('display') === 'block';
	//队徽选了但没有保存的情况下，提示保存
	var empImgEle = nowPageId == "btn_page1" ? jQuery('.madeEmbWrap_1') : jQuery('.madeEmbWrap_2');
	var isChooseEmbImg = empImgEle.find('img').attr('src');
	var nowPageKind = nowPageId == "btn_page1"
        ? jQuery(".madeBoxCons_p1").find(".madeNow").attr("dataVal")
        : jQuery(".madeBoxCons_p2").find(".madeNow").attr("dataVal");
	if(nowPageKind == 4 && isChooseEmbImg && ((nowPageId == "btn_page2" && !isViewPage2Show) || (nowPageId == "btn_page1" && !isViewPage1Show))){
		notTodo("提示","定制条件已变更，请先保存定制，才可以加入购物车，为您带来的不便，还请谅解！");
		return false;
	}

    //定制文字判断
    if (_imgNow_2 == _imgIni2 && _texNow_2 == _texIni2 && _imgNow_1 == _imgIni1 && _texNow_1 == _texIni1) {
        //未改动
        return true;
    }else{
        //已改动
        // alert("已改动");
        notTodo("提示","定制条件已变更，请先保存定制，才可以加入购物车，为您带来的不便，还请谅解！");
        return false;
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

function cartSub_out() {
    if (inputCart() && isCustom()) {
        jQuery('#modal-login').addClass('md-show');
    } else {
        return false;
    }
}

function cartSub_login() {
    if (inputCart() && isCustom()) {
        return true;
        //jQuery('#product_addtocart_form').submit();
    } else {
        return false;
    }
}


jQuery(function () {
    //获取初始数据
    var _imgIni = jQuery("#avatar");
    var _imgIniVal = _imgIni.attr("src");

    //开始定制按钮
    //暂时隐藏step1层 后期看是否是进行页面跳转
    jQuery(".step_1_Btn a").click(function () {
        jQuery(this).parents().find(".madeStep_1").hide();
        jQuery(this).parents().find(".madeStep_2").show();
    });

    //判断数据是否改动，进行相应的操作
    //点击page按钮
    jQuery(".madeP_btn").click(function () {
        clickPageBtn (this);  //this 当前操作pageBtnc对象
    });

    //点击page按钮时，执行page转换时需要进行的操作
    //显示对应page的对应的DIV，并传入初始值
    //隐藏另一个Page对应的DIV
    function clickPageBtn (_PageBtn){

        var _pageBtnId = jQuery(_PageBtn).attr("id");                    //获取当前操作pageBtn 的 ID
        clickInit(_pageBtnId);
        var _init_1 = jQuery(".select_N1").html();                       //获取存储在暂存N1里以备比较数据是否改变的P1数据
        var _init_2 = jQuery(".select_N2").html();                       //获取存储在暂存N2里以备比较数据是否改变的P2数据

        var _now_1 = jQuery(".madeTexWrap").find(".select_P1").html();   //获取当前P1数据
        var _now_2 = jQuery(".madeTexWrap").find(".select_P2").html();   //获取当前P2数据
        var _imgIntSrc = jQuery("#imgIntSrc").val();             //当是图片定制时，获取图片初始地址的值

        if(isClickOne){                                                       //点击page 1按钮
            var nowKind = jQuery(".madeBoxCons_p2").find(".madeNow").attr("dataVal");
            console.log("按钮1");
            //点亮对应icon
            jQuery(".remind_1").css("opacity","1");
            jQuery(".remind_2").css("opacity","0");

            if (nowKind == 1) {
                _now_2 = jQuery("#avatar").attr("src");
                if (_init_2 == "") {
                    _init_2 = _imgIntSrc;
                }
            } else if (nowKind == 2){
                _now_2 = jQuery(".madeTexWrap").find(".select_P2").html();
            } else if (nowKind == 3){
                _now_2 = "";
            } else if (nowKind == 4){
	            _now_2 = jQuery('.madeEmbWrap_2 img').attr("src");
	            if(_init_2 == _now_2){
		            jQuery('.madeStepEmb').hide();
		            jQuery('.madeEmbWrap_1').show();
		            jQuery('.madeEmbWrap_2').hide();
		            var img = jQuery('.madeEmbWrap_1').find('img').attr('src');
		            if(!img) jQuery('.embImg').removeClass('active');
                }else {
		            jQuery(".remind_1").css("opacity","0");
		            jQuery(".remind_2").css("opacity","1");
	            }
            }
            if(_init_2 == _now_2){
                noChange(_PageBtn);
            }else{
                isChange();
            }
        }else{                                                  //点击page 2按钮
            //alert(_pageBtnId);
            console.log("按钮2");
            var nowKind = jQuery(".madeBoxCons_p1").find(".madeNow").attr("dataVal");
            jQuery(".remind_1").css("opacity","0");
            jQuery(".remind_2").css("opacity","1");
            if (nowKind == 1) {
                _now_1 = jQuery("#avatar").attr("src");
                if (_init_1 == "") {
                    _init_1 = _imgIntSrc
                }

            } else if (nowKind == 2){
                _now_1 = jQuery(".madeTexWrap").find(".select_P1").html();
            } else if (nowKind == 3){
                _now_1 = "";
            } else if (nowKind == 4) {
	            _now_1 = jQuery('.madeEmbWrap_1 img').attr("src");

	            if(_init_1 == _now_1) {
		            jQuery('.madeEmbWrap_1').hide();
		            jQuery('.madeEmbWrap_2').show();

		            var img = jQuery('.madeEmbWrap_2').find('img').attr('src');
		            if (!img) jQuery('.embImg').removeClass('active');
	            }else {
		            jQuery(".remind_1").css("opacity","1");
		            jQuery(".remind_2").css("opacity","0");
                }
            }

            if(_init_1 == _now_1){
                noChange(_PageBtn);
            }else{
                isChange();
            }
        }

        //判断值是否有改动,以执行相应操作
        //数据没有改动时，执行操作
        //noChange
        function noChange(_PageBtn){
            jQuery(_PageBtn).addClass("madeP_btn_now");
            if(isClickOne){
                var _position = 1;
                jQuery(_PageBtn).siblings(".madeP_2_btn").removeClass("madeP_btn_now");
                jQuery(".madeBoxCons_p1").show();
                jQuery(".madeBoxCons_p2").hide();
            }else{
                var _position = 2;
                jQuery(_PageBtn).siblings(".madeP_1_btn").removeClass("madeP_btn_now");
                jQuery(".madeBoxCons_p1").hide();
                jQuery(".madeBoxCons_p2").show();
            }
            //未改动

            jQuery(_PageBtn).parents().find("#textMade_P3").hide();
            jQuery(_PageBtn).parents().find("#textMade_P4").hide();

            //重置图片定制
            jQuery("#img_grip").css("opacity","0");
            jQuery("#imgFuns").css("opacity","0");
            jQuery("#formBtn").show();

            //初始化到原始球皮
            jQuery(_PageBtn).parents().find(".madeStepNone").show();
            jQuery(_PageBtn).parents().find(".madeStepImg").hide();
            jQuery(_PageBtn).parents().find(".madeStepTex").hide();
	        jQuery(_PageBtn).parents().find(".madeStepEmb").hide();
            //jQuery("#options_pos").val(1);

            // TODO
            jQuery.ajax({
                type: 'POST',
                url: jQuery('#check').val(),
                data: {position: _position, sku:jQuery('#sku').val()},
                success: function (res) {
                    dataObj = ajaxEvalJson(res);

                    if (dataObj != null) {
                        resetView(dataObj['type'], dataObj['content1'], dataObj['content2'],
                            dataObj['content3'], dataObj['content4']);
                    }
                }
            });
            //resetView_1(2, "abcdeft", 1);
        }//noChange() end


        //数据有改动时，执行操作
        //isChange
        function isChange(){
            //isClickOne true 按钮1 false按钮2
            if(isClickOne){
	            //点亮对应icon
	            jQuery(".remind_1").css("opacity","0");
	            jQuery(".remind_2").css("opacity","1");
                jQuery('#made_p2 .submitY').trigger("click");
            }else{
	            //点亮对应icon
	            jQuery(".remind_1").css("opacity","1");
	            jQuery(".remind_2").css("opacity","0");
                jQuery('#made_p1 .submitY').trigger("click");
            }
        }
    }

    //定制图案或文字时 另一定制收起
    var _madeImgDt = jQuery(".madeKindImg");
    var _madeTexDt = jQuery(".madeKindTex");
    var _imgWrap = jQuery(".madeStepImg");
    var _texWrap = jQuery(".madeStepTex");

    //选择图案
    jQuery(".madeKindTitImg").click(function () {
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        if (_madeTexDt.is(":visible")) {
            jQuery(this).siblings(".madeKindImg").slideDown();
            jQuery(this).siblings(".madeKindTex").slideUp().removeClass("madeNow");
        }
        if (_madeImgDt.is(":hidden")) {
            jQuery(this).siblings(".madeKindImg").slideDown()
        }
        //隐藏显示编辑
        jQuery("#formBtn").show();
        jQuery(this).parents().find(".madeStepImg").show();
        jQuery(this).parents().find(".madeStepTex").hide();
        jQuery(this).parents().find(".madeStepNone").hide();
        jQuery(".cusRemindBox").show();
        jQuery("#viewMadeFun").show();

        //获取存储图片初始值
        var _imgIntSrc = jQuery("#imgIntSrc").val();
        jQuery("#avatar").attr("src" , _imgIntSrc);
        jQuery("#img_grip").css("opacity",0);
        jQuery("#imgFuns").css("opacity",0);




    });

    //选择文本
    jQuery(".madeKindTitTex").click(function () {
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        if (_madeImgDt.is(":visible")) {
            jQuery(this).siblings(".madeKindTex").slideDown();
            jQuery(this).siblings(".madeKindImg").slideUp().removeClass("madeNow");
        }
        if (_madeTexDt.is(":hidden")) {
            jQuery(this).siblings(".madeKindTex").slideDown();
        }
        //隐藏显示编辑
        jQuery(this).parents().find(".madeStepTex").show();
        jQuery(this).parents().find(".madeStepImg").hide();
        jQuery(this).parents().find(".madeStepNone").hide();
	    jQuery(this).parents().find(".madeStepEmb").hide();
        jQuery(".cusRemindBox").show();
        jQuery("#viewMadeFun").show();
    });

    //选择空白
    jQuery(".madeKindTitNon").click(function () {
        jQuery(this).addClass("madeNow");
        jQuery(this).siblings("dd").removeClass("madeNow");
        jQuery(this).siblings("dt").slideUp();

        //隐藏显示编辑
        jQuery(this).parents().find(".madeStepNone").show();
        jQuery(this).parents().find(".madeStepTex").hide();
        jQuery(this).parents().find(".madeStepImg").hide();
	    jQuery(this).parents().find(".madeStepEmb").hide();
        jQuery(".cusRemindBox").show();
        jQuery("#viewMadeFun").show();
    });

	//选择队徽
	jQuery(".madeKindTitEmb").click(function () {
		jQuery(this).addClass("madeNow");
		jQuery(this).siblings("dd").removeClass("madeNow");
		jQuery(this).siblings("dt").slideUp();

		//隐藏显示编辑
		jQuery(this).parents().find(".madeStepEmb").show();
		jQuery(this).parents().find(".madeStepTex").hide();
		jQuery(this).parents().find(".madeStepImg").hide();
		jQuery(this).parents().find(".madeStepNone").hide();
		jQuery(".cusRemindBox").show();
		jQuery("#viewMadeFun").show();
	});

	jQuery('.embBtn_prev').click(function () {
		var ele = '';
		if(jQuery('.embCon-tab1').css('display') === 'block'){
			ele = jQuery(".embCon-tab1")
		}else{
			ele = jQuery(".embCon-tab2")
		}
		ele.find('.first').show();
		ele.find('.second').hide()
    });

	jQuery('.embBtn_next').click(function () {
		var ele = '';
		if(jQuery('.embCon-tab1').css('display') === 'block'){
			ele = jQuery(".embCon-tab1")
		}else{
			ele = jQuery(".embCon-tab2")
		}
		ele.find('.second').show();
		ele.find('.first').hide()
	});

	jQuery('.embImg ').click(function () {
		jQuery(this).siblings().removeClass("active");
		jQuery(this).addClass("active");
		if(jQuery('.embCon-tab1').css('display') === 'none') jQuery('.embCon-tab1').find('.embImg').removeClass('active');
		if(jQuery('.embCon-tab2').css('display') === 'none') jQuery('.embCon-tab2').find('.embImg').removeClass('active');

	    var imgUrl = jQuery(this).find('.embDefault').attr('src');
	    var madeEmbWrap = '';
	    if(isClickOne){
		    madeEmbWrap = jQuery('.madeEmbWrap_1 img');
		    jQuery('.madeEmbWrap_1').show();
        } else {
		    madeEmbWrap = jQuery('.madeEmbWrap_2 img');
		    jQuery('.madeEmbWrap_2').show();
        }
		madeEmbWrap.attr('src', imgUrl)
	});

	jQuery('.embMonochrome').click(function () {
		jQuery(this).siblings().removeClass("active");
		jQuery(this).addClass("active");
		jQuery('.embCon-tab1').css("display","block");
		jQuery('.embCon-tab2').css("display","none");
		jQuery('.embImg.first').css("display","block");
		jQuery('.embImg.second').css("display","none");
	});

	jQuery('.embColour').click(function () {
		jQuery(this).siblings().removeClass("active");
		jQuery(this).addClass("active");
		jQuery('.embCon-tab1').css("display","none");
		jQuery('.embCon-tab2').css("display","block");
		jQuery('.embImg.first').css("display","block");
		jQuery('.embImg.second').css("display","none");
	});

	//设置字体
    // 小号
    jQuery(".madeBoxCons_p1 .setSize_40").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P1").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTexWrap .select_P1").addClass("size_40");
        jQuery(this).parents().find(".madeTexWrap .select_P3").addClass("size_40");
        jQuery(this).parent().parent().parent().siblings(".smalLine").show();
        //jQuery("#textInput_1").attr("maxlength", "10");
        SwapTxt_1();
        SwapTxt_3();

        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");

        jQuery(".smalLine").find(".lineTwo_p1 input").removeAttr("checked");
        jQuery(".smalLine").find(".lineOne_p1 input").attr("checked","checked");
        jQuery(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");
        jQuery(".zz_t").removeClass("zz_t_2");



    });
    jQuery(".madeBoxCons_p2 .setSize_40").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P2").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTexWrap .select_P2").addClass("size_40");
        jQuery(this).parents().find(".madeTexWrap .select_P4").addClass("size_40");
        jQuery(this).parent().parent().parent().siblings(".smalLine").show();

        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");

        jQuery(".smalLine").find(".lineTwo_p2 input").removeAttr("checked");
        jQuery(".smalLine").find(".lineOne_p2 input").attr("checked","checked");
        jQuery(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");
        jQuery(".zz_t").removeClass("zz_t_2");
        SwapTxt_2();
        SwapTxt_4();
    });

    //中号
    jQuery(".madeBoxCons_p1 .setSize_60").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P1").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTexWrap .select_P1").addClass("size_60");
        SwapTxt_1();

        jQuery(this).parent().parent().parent().siblings(".smalLine").hide();
        jQuery(this).parents().find(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(this).parents().find(".select_P3 , .select_P4").hide();
        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");

        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");

        jQuery(".zz_t").removeClass("zz_t_2");
    });
    jQuery(".madeBoxCons_p2 .setSize_60").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P2").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTexWrap .select_P2").addClass("size_60");
        SwapTxt_2();

        jQuery(this).parent().parent().parent().siblings(".smalLine").hide();
        jQuery(this).parents().find(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(this).parents().find(".select_P3 , .select_P4").hide();
        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");
        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");

        jQuery(".zz_t").removeClass("zz_t_2");
    });

    //大号
    jQuery(".madeBoxCons_p1 .setSize_80").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P1").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTexWrap .select_P1").addClass("size_80");
        SwapTxt_1();

        jQuery(this).parent().parent().parent().siblings(".smalLine").hide();
        jQuery(this).parents().find(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(this).parents().find(".select_P3 , .select_P4").hide();
        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");
        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");

        jQuery(".zz_t").removeClass("zz_t_2");

    });
    jQuery(".madeBoxCons_p2 .setSize_80").click(function () {
        jQuery(this).parents().find(".madeTexWrap .select_P2").removeClass("size_40 size_60 size_80");
        jQuery(this).parents().find(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(this).parents().find(".madeTexWrap .select_P2").addClass("size_80");
        jQuery(".madeTexWrap").find(".select_P1").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P2").removeClass("twoLine");
        SwapTxt_2();

        jQuery(this).parent().parent().parent().siblings(".smalLine").hide();
        jQuery(this).parents().find(".select_P3 , .select_P4").hide();
        jQuery(".lineOne").css("color","#fcb805").removeClass("labelNoc").addClass("labelCheck");
        jQuery(".lineTwo").css("color","#9b9b9b").removeClass("labelCheck").addClass("labelNoc");

        jQuery(".zz_t").removeClass("zz_t_2");
    });


    //单双行设置
    jQuery(".lineOne").click(function(){
        jQuery(this).parent().siblings(".madeTextInp").find(".smaSizeInp").hide();
        jQuery(this).css("color","#fcb805");
        jQuery(this).siblings(".lineTwo").css("color","#9b9b9b");
        jQuery(".madeTexWrap").find(".select_P1,.select_P2").removeClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P3,.select_P4").hide();

        jQuery(this).removeClass("labelNoc").addClass("labelCheck");
        jQuery(this).siblings(".lineTwo").removeClass("labelCheck").addClass("labelNoc");
        jQuery(this).find("input").attr("checked","checked");
        jQuery(this).siblings(".lineTwo").find("input").removeAttr("checked");

        jQuery(".zz_t").removeClass("zz_t_2");
    });
    jQuery(".lineTwo").click(function(){
        jQuery(this).parent().siblings(".madeTextInp").find(".smaSizeInp").css("display","inline-block");
        jQuery(this).css("color","#fcb805");
        jQuery(this).siblings(".lineOne").css("color","#9b9b9b");

        jQuery(this).removeClass("labelNoc").addClass("labelCheck");
        jQuery(this).siblings(".lineOne").removeClass("labelCheck").addClass("labelNoc");
        jQuery(this).find("input").attr("checked","checked");
        jQuery(this).siblings(".lineOne").find("input").removeAttr("checked");

        jQuery(".zz_t").addClass("zz_t_2");
    });

    jQuery(".lineTwo_p1").click(function(){
        jQuery(".madeTexWrap").find(".select_P1").addClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P4").hide();
        jQuery(".madeTexWrap").find(".select_P3").show();
    });

    jQuery(".lineTwo_p2").click(function(){
        jQuery(".madeTexWrap").find(".select_P2").addClass("twoLine");
        jQuery(".madeTexWrap").find(".select_P3").hide();
        jQuery(".madeTexWrap").find(".select_P4").show();
    });


    //start 选择字体
    jQuery(".chosFam").click(function () {
        jQuery(this).siblings("ul.fontFmList").slideToggle();
    });

    jQuery(".madeBoxCons_p1 .fontFamBox").click(function(){
        var _dataFamVals = jQuery(this).attr("data-familys");
        var _dataFamTex = jQuery(this).html();
        jQuery(this).parent("ul").slideUp();
        jQuery(this).parent("ul").siblings(".chosFam").html(_dataFamTex);
        jQuery(this).parent("ul").siblings(".chosFam").attr("data-family",_dataFamVals);
        madeFamily_1();
    });

    jQuery(".madeBoxCons_p2 .fontFamBox").click(function(){
        var word=jQuery("#blacklist").html();
        var _dataFamVals = jQuery(this).attr("data-familys");
        var _dataFamTex = jQuery(this).html();
        jQuery(this).parent("ul").slideUp();
        jQuery(this).parent("ul").siblings(".chosFam").html(_dataFamTex);
        jQuery(this).parent("ul").siblings(".chosFam").attr("data-family",_dataFamVals);
        madeFamily_2();
    });
    //end 选择字体

});

//设置字体-
// 0 -- Conv_CustomGrotesque-Regular
// 1 -- Sans-Serif
// 2 -- Arial
// 3 -- 宋体
// 4 --楷体



function setFamily_1(_dataFamVal){

    if(_dataFamVal==0){
        jQuery("#textMade_P1,#textMade_P3,.viewPage_p1_wrap").css({"font-family":"Conv_CustomGrotesque-Regular","letter-spacing":"0"});
        jQuery(".big_pic").find(".textPag_1").css({"font-family":"Conv_CustomGrotesque-Regular","letter-spacing":"0"});
        //jQuery(".chosFam_p1").html("CustomGrotesque");
    }else if(_dataFamVal==1){
        jQuery("#textMade_P1,#textMade_P3,.viewPage_p1_wrap").css("font-family","Sans-Serif");
        jQuery(".big_pic").find(".textPag_1").css("font-family","Sans-Serif");
        //jQuery(".chosFam_p1").html("SansSerif");
    }else if(_dataFamVal==2){
        jQuery("#textMade_P1,#textMade_P3,.viewPage_p1_wrap").css({"font-family":"Arial","letter-spacing":"-4px"});
        jQuery(".big_pic").find(".textPag_1").css({"font-family":"Arial","letter-spacing":"-4px"});
        //jQuery(".chosFam_p1").html("Aril");
    }else if(_dataFamVal==3){
        jQuery("#textMade_P1,#textMade_P3,.viewPage_p1_wrap").css("font-family","宋体");
        jQuery(".chosFam_p1").html("字体1");
    }else if(_dataFamVal==4){
        jQuery("#textMade_P1,#textMade_P3,.viewPage_p1_wrap").css("font-family","楷体");
        jQuery(".chosFam_p1").html("字体2");
    }
}

function setFamily_2(_dataFamVal){

    if(_dataFamVal==0){
        jQuery("#textMade_P2,#textMade_P4,.viewPage_p2_wrap").css({"font-family":"Conv_CustomGrotesque-Regular","letter-spacing":"0"});
        jQuery(".big_pic").find(".textPag_2").css({"font-family":"Conv_CustomGrotesque-Regular","letter-spacing":"0"});
       // jQuery(".chosFam_p2").html("CustomGrotesque");
    }else if(_dataFamVal==1){
        jQuery("#textMade_P2,#textMade_P4,.viewPage_p2_wrap").css("font-family","Sans-Serif");
        jQuery(".big_pic").find(".textPag_2").css("font-family","Sans-Serif");
        //jQuery(".chosFam_p2").html("SansSerif");
    }else if(_dataFamVal==2){
        jQuery("#textMade_P2,#textMade_P4,.viewPage_p2_wrap").css({"font-family":"Arial","letter-spacing":"-4px"});
        jQuery(".big_pic").find(".textPag_2").css({"font-family":"Arial","letter-spacing":"-4px"});
        //jQuery(".chosFam_p2").html("Aril");
    }else if(_dataFamVal==3){
        jQuery("#textMade_P2,#textMade_P4,.viewPage_p2_wrap").css("font-family","宋体");
        jQuery(".chosFam_p2").html("字体1");
    }else if(_dataFamVal==4){
        jQuery("#textMade_P2,#textMade_P4,.viewPage_p2_wrap").css("font-family","楷体");
        jQuery(".chosFam_p2").html("字体2");
    }

}
function madeFamily_1(){
    var _dataFamVal = jQuery(".chosFam_p1").attr("data-family");
    setFamily_1(_dataFamVal);
}
function madeFamily_2(){
    var _dataFamVal = jQuery(".chosFam_p2").attr("data-family");
    setFamily_2(_dataFamVal);
}

function previewImage(file, imgId) {
    var _formBtn = document.getElementById('formBtn');
    var _gripImg = document.getElementById('img_grip');
    var _form = document.getElementById('imgFuns');

    var _img = document.getElementById(imgId);
    var _imgSrc = _img.getAttribute("src");

    var _viewMadeFun = document.getElementById('viewMadeFun');

    //size & format
    photoExt = file.value.substr(file.value.lastIndexOf(".")).toLowerCase();//获得文件后缀名
    if (photoExt != '.jpg' && photoExt != '.png') {

        _img.setAttribute("src", _imgSrc);

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
        var file = fileSystem.GetFile(filePath);
        fileSize = file.Size;
    } else {
        fileSize = file.files[0].size;
    }
    fileSize = Math.round(fileSize / 1024 * 100) / 100; //单位为KB
    if (fileSize >= 8 * 1024) {
        _img.setAttribute("src", _imgSrc);
        _form.style.opacity = 0;
        _gripImg.style.opacity = 0;
        _formBtn.style.display = "block";

        alert("图片最大尺寸为8M，请重新上传!");
        return false;
    } else if (fileSize <= 500) {//2 * 1024
        _img.setAttribute("src", _imgSrc);
        _form.style.opacity = 0;
        _gripImg.style.opacity = 0;
        _formBtn.style.display = "block";

        alert("图片最小尺寸为2M，请重新上传!");
        return false;
    }


    if (file.files && file.files[0]) {
        var img = document.getElementById(imgId);
        var reader = new FileReader();
        reader.onload = function (evt) {
            img.removeAttribute("width");
            img.removeAttribute("height");
            img.src = evt.target.result;
            jQuery("#customImageHidden").attr("src", evt.target.result);
            var t= setTimeout(function(){
                naturalWidth = jQuery("#customImageHidden").width();
                naturalHeight = jQuery("#customImageHidden").height();
                avatarinit();
            },1000)
            //alert("reader.onload OK!!");
        };
        reader.readAsDataURL(file.files[0]);
        _form.style.opacity = 1;
        _gripImg.style.opacity = 1;
        _formBtn.style.display = "none";
        _viewMadeFun.style.display = "none";
    } else {
        file.select();
        var src = document.selection.createRange().text;
        jQuery("#" + imgId).attr("src", src);
    }
}

var cut_div;  //裁减图片外框div
var avatar;  //裁减图片
var imgdefw;  //图片默认宽度
var imgdefh;  //图片默认高度
var offsetx = 0; //图片位置位移x
var offsety = -332; //图片位置位移y
var divx = 705; //外框宽度
var divy = 330; //外框高度
var cutx = 705;  //裁减宽度
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

var naturalWidth = 0;
var naturalHeight = 0;


//图片初始化
function imageinit() {
    avatar = document.getElementById('avatar');

    //1.jpg
    cut_div = document.getElementById('cut_div');
    cut_img = document.getElementById('avatar');
    imgdefw = naturalWidth; //默认402  //正确2100  //错误402
    imgdefh = naturalHeight; //默认402 //正确 768   //错误402
    if(imgdefw > divx){
        //正确时候会进次判断
        zoom = divx / imgdefw;
        cut_img.width = divx;
        cut_img.height = Math.round(imgdefh * zoom);
    }
    cut_img.style.left = Math.round((divx - cut_img.width) / 2); //正常时候  0  //错误时候 152
    cut_img.style.top = Math.round((divy - cut_img.height) / 2) - divy;//正常时候 -294 //错误时候 -549

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


    Drag.init(cut_div, cut_img);
    cut_img.onDrag = when_Drag;


}
//图片逐步缩放
function imageresize(flag) {
    if (flag) {
        zoom = zoom * 1.5;
    } else {
        zoom = zoom / 1.5;
    }
    if (zoom < zmin) zoom = zmin;
    if (zoom > zmax) zoom = zmax;
    avatar.width = Math.round(imgdefw * zoom);
    avatar.height = Math.round(imgdefh * zoom);
    checkcutpos();
    grip_pos = 5 * (Math.log(zoom * zmax) / Math.log(zmax));
    img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
}

//获得style里面定位
function getStylepos(e) {
    return {x: parseInt(e.style.left), y: parseInt(e.style.top)};
}

//获得绝对定位
function getPosition(e) {
    var t = e.offsetTop;
    var l = e.offsetLeft;
    while (e = e.offsetParent) {
        t += e.offsetTop;
        l += e.offsetLeft;
    }
    return {x: l, y: t};
}

//检查图片位置
function checkcutpos() {
    var imgpos = getStylepos(avatar);

    max_x = Math.max(offsetx, offsetx + cutx - avatar.clientWidth);
    min_x = Math.min(offsetx + cutx - avatar.clientWidth, offsetx);
    if (imgpos.x > max_x) avatar.style.left = max_x + 'px';
    else if (imgpos.x < min_x) avatar.style.left = min_x + 'px';

    max_y = Math.max(offsety, offsety + cuty - avatar.clientHeight);
    min_y = Math.min(offsety + cuty - avatar.clientHeight, offsety);

    if (imgpos.y > max_y) avatar.style.top = max_y + 'px';
    else if (imgpos.y < min_y) avatar.style.top = min_y + 'px';
}

//图片拖动时触发
function when_Drag(clientX, clientY) {
    checkcutpos();
}

//获得图片裁减位置
function getcutpos() {
    var imgpos = getStylepos(avatar);
    var x = offsetx - imgpos.x;
    var y = offsety - imgpos.y;
    //var cut_pos = document.getElementById('cut_pos');
    //cut_pos.value = x + ',' + y + ',' + avatar.width + ',' + avatar.height;
    //return true;
    return x + ',' + y + ',' + avatar.width + ',' + avatar.height;
}

//缩放条初始化
function gripinit() {
    img_grip = document.getElementById('img_grip');
    img_track = document.getElementById('img_track');
    track_pos = getPosition(img_track);

    grip_y = track_pos.y;
    grip_minx = track_pos.x + 1;
    grip_maxx = track_pos.x + img_track.clientWidth - img_grip.clientWidth - 5;

    // img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
    // img_grip.style.top = grip_y + "px";

    img_grip.style.left = (grip_minx + (grip_pos / 10 * (grip_maxx - grip_minx))) + "px";
    img_grip.style.top = grip_y - 115 + "px";

    Drag.init(img_grip, img_grip);
    img_grip.onDrag = grip_Drag;

}

//缩放条拖动时触发
function grip_Drag(clientX, clientY) {
    var posx = clientX;
    img_grip.style.top = grip_y - 115 + "px";
    if (clientX < grip_minx) {
        img_grip.style.left = grip_minx + "px";
        posx = grip_minx;
    }
    if (clientX > grip_maxx) {
        img_grip.style.left = grip_maxx + "px";
        posx = grip_maxx;
    }

    grip_pos = (posx - grip_minx) * 10 / (grip_maxx - grip_minx);
    zoom = Math.pow(zmax, grip_pos / 5) / zmax;
    if (zoom < zmin) zoom = zmin;
    if (zoom > zmax) zoom = zmax;
    avatar.width = Math.round(imgdefw * zoom);
    avatar.height = Math.round(imgdefh * zoom);
    checkcutpos();
}

//页面载入初始化
function avatarinit() {
    imageinit();
    gripinit();
}

function avatarinitLoad() {
    avatarinit();
    document.getElementById("avatar").removeAttribute("width");
    document.getElementById("avatar").removeAttribute("height");
}

if (document.all) {
    window.attachEvent('onload', avatarinitLoad);
} else {
    window.addEventListener('load', avatarinitLoad, false);
}

jQuery(document).ready(function () {
    function adjust(el, selection) {
        var scaleX = jQuery(el).width() / (selection.width || 1);
        var scaleY = jQuery(el).height() / (selection.height || 1);
        jQuery(el + ' img').css({
            width: Math.round(scaleX * jQuery('#avatar').width()) + 'px',
            height: Math.round(scaleY * jQuery('#avatar').height()) + 'px',
            marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
            marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
        });
    }
});

var value = 0;
function avatarrotateleft() {
    value -= 90;
    jQuery('#avatar').rotate({animateTo: value});
}
function avatarrotateright() {
    value += 90;
    jQuery('#avatar').rotate({animateTo: value});
}

var productAddToCartForm = new VarienForm('product_addtocart_form');
productAddToCartForm.submit = function (button, url) {

    madeLoading("提交成功","拼命加载中，请耐心等待...");
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

productAddToCartForm.submitLight = function (button, url) {
    if (this.validator) {
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
jQuery(function () {

    //提交当前PAGE按钮
    jQuery(".submitY").click(function () {
        var subId = jQuery(this).attr("id");
        if(subId == "submitYP1"){
            clickStatusChange(true);
            var _comfBox = jQuery(".comfBox_1");
        }else if(subId == "submitYP2"){
            clickStatusChange(false);
            var _comfBox = jQuery(".comfBox_2");
        }
        console.log("0")
        var _madeValue = _comfBox.siblings(".madeBoxFuns").find("dd.madeNow").attr("dataVal");
        if (_madeValue == 1) {
            if (submitYP_CheckImg("")) {
                showComfBox(this);
            }
        } else if (_madeValue == 2) {
            var size = getTxtSize(1);
            if (size == 4) {//双行
                if(isClickOne){
                    if (jQuery("#textInput_1").val().length == 0 && jQuery("#textInput_3").val().length == 0) {
                        alert('至少输入一行内容');
                    } else if(!submitYP_CheckText("#textInput_1","#made_p1")) {
                        console.log("1")

                    } else if(!submitYP_CheckText("#textInput_3","#made_p1")) {
                        console.log("2")

                    }else{
                        showComfBox(this);
                    }
                }else{
                    if (jQuery("#textInput_2").val().length == 0 && jQuery("#textInput_4").val().length == 0) {
                        alert('至少输入一行内容');
                    }else if(!submitYP_CheckText("#textInput_2","#made_p2")) {


                    } else if(!submitYP_CheckText("#textInput_4","#made_p2")) {

                    }else{
                        showComfBox(this);
                    }
                }
            } else {//一行
                if(isClickOne){
                    if (jQuery("#textInput_1").val()=="") {
                        alert('输入内容不能为空！');

                    }else if(!submitYP_CheckText("#textInput_1","#made_p1")){

                    }
                    else{
                        showComfBox(this);
                    }
                }else{
                    if (jQuery("#textInput_2").val()=="") {
                        alert('输入内容不能为空！');

                    }else if(!submitYP_CheckText("#textInput_2","#made_p2")){

                    }
                    else{
                        showComfBox(this);
                    }
                }
            }
        } else if (_madeValue == 4) {
            var type  = 0;
            var img = '';
	        if(subId == "submitYP1"){
		        type = 1;
		        img = jQuery('.madeEmbWrap_1').find('img').attr('src');
            }else if(subId == "submitYP2"){
		        type = 2;
		        img = jQuery('.madeEmbWrap_2').find('img').attr('src');
            }
            if(!img){
	            alert('队徽不能为空！');
	            return;
            }
	        if(isHasEmbPic(type)) showComfBox(this);
        }
    });

    function submitYP_CheckText(txtInputId,madeId){
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
	            alert("不能输入特殊符号！");
	            txtInput.focus();
	        }else{
	            if(count.length>0){
	                var font_family=jQuery(madeId).find(".chosFam").text();
	                var tips="当前中文字体："+font_family;
	                jQuery("#noTodoBox").addClass("fonttips");
	                var tips2='"'+jQuery(txtInput).val()+'"包含"'+count[0]+'"敏感词！'
	                notTodo(tips,tips2)
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
   /* function submitYP_CheckText(txtInputId,madeId){//判断文字是否满足条件
        var re = /^([A-Za-z0-9]|\s|@|-|_|\.|&|:)*$/;
        var zre=/[^\uFF00-\uFFFF\u4E00-\u9FA5]/;
        var _placeholder = jQuery(txtInputId).attr("placeholder");
        var txtInput = jQuery(txtInputId);
        if(_placeholder=="只限输入字母，数字，空格以及@-_.&:"){
            if (!re.exec(txtInput.val())){
                jQuery("#noTodoBox").addClass("fonttips");
                var font_family=jQuery(madeId).find(".chosFam").text();
                var tips="当前英文字体："+font_family;
                notTodo(tips,'只限输入字母，数字，空格以及@-_.&:')
                //alert('只限输入字母，数字，空格以及@-_.&:');
                txtInput.focus();
                return false;

            }else{
                return true;
            }
        }else{
            //判断中文字黑名单;
            var count=[];//存放敏感词的个数；
            var arr = jQuery("#blacklist").html().split(';')
            jQuery.each(arr,function(i){
                if(jQuery(txtInput).val().indexOf(arr[i])>=0)
                {
                    count.push(arr[i]);
                }
            });
            if(count.length>0){
                var font_family=jQuery(madeId).find(".chosFam").text();
                var tips="当前中文字体："+font_family;
                jQuery("#noTodoBox").addClass("fonttips");
                var tips2='"'+jQuery(txtInput).val()+'"包含"'+count[0]+'"敏感词！'
                notTodo(tips,tips2)
                txtInput.focus();
                return false;
            }else{
                if (zre.exec(txtInput.val())){
                    var font_family=jQuery(madeId).find(".chosFam").text();
                    var tips="当前中文字体："+font_family;
                    jQuery("#noTodoBox").addClass("fonttips");
                    notTodo(tips,'只限输入中文及全角标点符号')
                    //alert('只限输入中文及全角标点符号');
                    txtInput.focus();
                    return false;
                }else{
                    return true;
                }
            }
        }
    }*/
    function submitYP_CheckImg(txtInputId){

        return true;
    }
    function showComfBox(thisObj){
        jQuery(thisObj).parent(".madeSubmit").hide();
        jQuery(thisObj).parent(".madeSubmit").siblings(".madeBoxFuns").hide();
        jQuery(thisObj).parent(".madeSubmit").siblings(".comfBox").show();
    }

    function isHasEmbPic(type){
        var madeEmbWrap = '.madeEmbWrap_'+ type;
    	var img = jQuery(madeEmbWrap).find('img').attr('src');
    	return img || '';
    }

    jQuery(".saveMadeN").click(function () {
        jQuery(this).parent().parent(".comfBox").hide();
        jQuery(this).parent().parent(".comfBox").siblings(".madeSubmit").show();
        jQuery(this).parent().parent(".comfBox").siblings(".madeBoxFuns").show();
    });

    //预览按钮
    jQuery(".viewMade").click(function(){
        jQuery(this).parents().find(".cusMadeRigZz").show();
    });
    //预览
    jQuery("#viewMadeFun").click(function () {
        var pageData_init;
        var pageData_now;
        var nowPageKind;
        var nowPageId = jQuery(".madeP_btn_now").attr("id");

        var _imgIntSrc = jQuery("#imgIntSrc").val();

	    var isViewPage1Show = jQuery('.viewPage_P1').css('display') === 'block';
        var isViewPage2Show = jQuery('.viewPage_P2').css('display') === 'block';
        if(nowPageId == "btn_page1"){
            pageData_init = jQuery(".select_N1").html();
            nowPageKind = jQuery(".madeBoxCons_p1").find(".madeNow").attr("dataVal");
            if(nowPageKind == 1){
                if (pageData_init == "") {
                    pageData_init = _imgIntSrc;
                }
                pageData_now = jQuery("#avatar").attr("src");
            }else if(nowPageKind == 2){
                pageData_now = jQuery(".madeTexWrap").find(".select_P1").html();
            }else if(nowPageKind == 3){
                pageData_now = ""
            }else if(nowPageKind == 4){
	            pageData_now = ""
            }
        }else if(nowPageId == "btn_page2"){
            pageData_init = jQuery(".select_N2").html();
            nowPageKind = jQuery(".madeBoxCons_p2").find(".madeNow").attr("dataVal");
            if(nowPageKind == 1){
                if (pageData_init == "") {
                    pageData_init = _imgIntSrc;
                }
                pageData_now = jQuery("#avatar").attr("src");
            }else if(nowPageKind == 2){
                pageData_now = jQuery(".madeTexWrap").find(".select_P2").html();
            }else if(nowPageKind == 3){
                pageData_now = ""
            }else if(nowPageKind == 4){
	            pageData_now = "";
            }
        }

        //队徽选了但没有保存的情况下，提示保存
        var empImgEle = nowPageId == "btn_page1" ? jQuery('.madeEmbWrap_1') : jQuery('.madeEmbWrap_2');
        var isChooseEmbImg = empImgEle.find('img').attr('src');
        if(nowPageKind == 4 && isChooseEmbImg && ((nowPageId == "btn_page2" && !isViewPage2Show) || (nowPageId == "btn_page1" && !isViewPage1Show))){
	        warnAlert(nowPageId, this);
	        return
        }

        if(pageData_init == pageData_now){
            SubView();
        }else{
	        //队徽以外选项选了但没有保存的情况下，提示保存
	        warnAlert(nowPageId, this);
        }

    });
    //退出预览
    jQuery("#exitView").click(function () {
        SubView();
    });
});

function warnAlert(nowPageId, self) {
	if(nowPageId == "btn_page1"){
		jQuery('#made_p1 .submitY').trigger("click");
	}else if(nowPageId == "btn_page2"){
		jQuery('#made_p2 .submitY').trigger("click");
	}
	jQuery(self).parents().find(".cusMadeRigZz").hide()
}

function SubView(){
    madeLoading("提交成功","拼命加载中，请耐心等待...");
    jQuery.ajax({
        type: 'POST',
        url: jQuery('#preview').val(),
        data: {sku:jQuery('#sku').val()},
        success: function () {
            document.location.reload();
        }
    });
}

function formatInputString(key){
    str = document.getElementById(key).value;
    str = str.replace(/　　*/g," ");
    str = str.replace(/   */g," ");
    document.getElementById(key).value = str;
    return str;
}

function SwapTxt_1() {
    str = formatInputString("textInput_1");
    var _txt = str.trim();
    var _text = document.getElementById("textMade_P1");
    var _cnt = getTxtCnt(1);

    setMadeText(_text, _txt, _cnt);
}

function SwapTxt_2() {
    str = formatInputString("textInput_2");
    var _txt = str.trim();
    var _text = document.getElementById("textMade_P2");
    var _cnt = getTxtCnt(2);

    setMadeText(_text, _txt, _cnt);
}

function SwapTxt_3() {
    str = formatInputString("textInput_3");
    var _txt = str.trim();
    var _text = document.getElementById("textMade_P3");
    var _cnt = getTxtCnt(1);

    setMadeText(_text, _txt, _cnt);
}

function SwapTxt_4() {
    str = formatInputString("textInput_4");
    var _txt = str.trim();
    var _text = document.getElementById("textMade_P4");
    var _cnt = getTxtCnt(2);

    setMadeText(_text, _txt, _cnt);
}

function getSwapTxt_1() {
    var _txt = document.getElementById("textInput_1").value.trim();
    var _cnt = getTxtCnt(1);

    return getMadeText(_txt, _cnt);
}

function getSwapTxt_2() {
    var _txt = document.getElementById("textInput_2").value.trim();
    var _cnt = getTxtCnt(2);

    return getMadeText(_txt, _cnt);
}

function getSwapTxt_3() {
    var _txt = document.getElementById("textInput_3").value.trim();
    var _cnt = getTxtCnt(1);

    return getMadeText(_txt, _cnt);
}

function getSwapTxt_4() {
    var _txt = document.getElementById("textInput_4").value.trim();
    var _cnt = getTxtCnt(2);

    return getMadeText(_txt, _cnt);
}

function getMadeText(str,len){
    if(!str) return "";
    if(len<= 0) return "";
    //if(!suffix) suffix = "";
    var templen=0;
    for(var i=0;i<str.length;i++){
        if(str.charCodeAt(i)>255){
            templen+=2;
        }else{
            templen++
        }
        if(templen == len){
            //return str.substring(0,i+1)+suffix;
            return str.substring(0,i+1);
        }else if(templen >len){
            //return str.substring(0,i)+suffix;
            return str.substring(0,i);
        }
    }
    return str;
}

function setMadeText(_text, _txt, _cnt){
    _text.innerText = getMadeText(_txt, _cnt);
}

//选中字号大小
function getTxtSize(position) {
    var size = 2;
    var obj = null;
    if (position == 1) {
        obj = document.getElementsByName("size-p1");//position 1.定制1 2.定制2

    } else if (position == 2) {
        obj = document.getElementsByName("size-p2");
    }
    if (obj != null) {
        for (var i = 0; i < obj.length; i++) {
            var _checked = obj[i].getAttribute("checked");

            if (_checked=="checked") {
                // if (obj[i].checked) {
                size = parseInt(obj[i].value);//获取input的vaule; 1小号 2中号 3大号
                break;
            }
        }
    }

    if (size == 1) {//小号
        if (position == 1) {
            obj1 = document.getElementsByName("smalSizeCho_p1");//文字内容单双行显示
        } else if (position == 2) {
            obj1 = document.getElementsByName("smalSizeCho_p2");
        }
        if (obj1 != null) {
            for (var i = 0; i < obj1.length; i++) {
                var _checked = obj1[i].getAttribute("checked");

                if (_checked=="checked") {
                    size += parseInt(obj1[i].value); //size =4双行 size=1单行
                    break;
                }
            }
        }
    }
    return size;
}
//文字获取的内容长度
function getTxtCnt(position) {
    var size = getTxtSize(position);
    if (size == 1 || size == 4) {
        return 20;
    } else if (size == 2) {
        return 12;
    } else {
        return 8;
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

//获取队徽图片名
function getEmbPicName(type){
	var madeEmbWrap = '.madeEmbWrap_'+ type;
    var img =jQuery(madeEmbWrap).find('img').attr('src');
    var name = '';
	if(img){
		var arr = img.split('imagesEmblem/');
		name = arr[1].split('.')[0];
	}
	return name;
}

jQuery(function(){
    jQuery(".loadingTest").click(function(){
        madeLoading("提交成功","数据加载中，由于上传图片体积较大，请耐心等待...");
    });

    // 关闭警告框
    jQuery(".closeTodo").click(function(){
        jQuery(this).parent(".notTodo").hide();
        if(jQuery("#noTodoBox").hasClass("fonttips")){
            jQuery("#noTodoBox").removeClass("fonttips");
        }
    });

    jQuery(".cusMadeRigZz").click(function(){
        notTodo("提示","退出预览后才能进行操作！");
    });
    jQuery(".zz").click(function(){
        notTodo("提示","定制已提交，若要重新定制，请先“重置”！");
    });


    //rows agree or refuse
    jQuery(".rowsAgr").click(function(){
        jQuery(this).parent().parent().parent(".rows").hide();
        jQuery.ajax({
            type: 'POST',
            url: jQuery('#agree').val(),
        });
    });

    jQuery('.jScrollbar, .jScrollbar2, .jScrollbar3, .jScrollbar4, .jScrollbar5').jScrollbar();
});
