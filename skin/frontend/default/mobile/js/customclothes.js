    mageAttr = [];
    detailData = [];
    itemListNum = -1;
    delRowId = "";
    /*
     * 判断球员名称和号码不能为空
     * */
    function submitYP_CheckText(txtInputId) {
        var reg = new RegExp("^[(\u0000-\u00ff|\u4e00-\u9fa5|\uff00-\uffff)]+$", "g");
        var txtInput = jQuery(txtInputId);
        var html="";
        if (jQuery(txtInput).val()) {
            //判断中文字黑名单;
            var count = [];//存放敏感词的个数；
            var arr = jQuery("#blacklist").html().split(';');
            jQuery.each(arr, function (i) {
                if (jQuery(txtInput).val().indexOf(arr[i]) >= 0) {
                    count.push(arr[i]);
                }
            });
            if (!reg.test(txtInput.val())) {
                tab(".swiper-slide:eq(4)");
                tab(".tabCont:eq(4)");
                alert("不能输入特殊符号！");
                txtInput.focus();
                return false;
            } else if (count.length > 0) {
                tab(".swiper-slide:eq(4)");
                tab(".tabCont:eq(4)");
                var tips = '"' + jQuery(txtInput).val() + '"包含"' + count[0] + '"敏感词！';
                alert(tips);
                txtInput.focus();
                return false;
            } else {
                return true;
            }
        }
        return true;
    }
    function checkTextYN(obj1,obj2,obj3){
        // 检查球队名称
        if(obj1!="team"){
            // 检查球队名称
            if(!submitYP_CheckText(obj1)) {
                return false;
            }
        }
        // 检查球员名称
        if(!submitYP_CheckText(obj2)) {
            return false;
        }

        // 检查球员号码
        if(!submitYP_CheckText(obj3)) {
            return false;
        }
            var _val=jQuery(obj3).val();
            var pattern = /^\d{1,2}$/;
            if(pattern.test(_val)){
                if(_val=='0' || _val=='00'){
                    alert("球员号码不能为0");
                    return false;
                }
            }
        if(obj1!="team"){
            if(!jQuery(obj1).val()&& !jQuery(obj2).val()&& !jQuery(obj3).val()){
                tab(".swiper-slide:eq(4)");
                tab(".tabCont:eq(4)");
                alert("至少有一项不能为空");
                return false;
            }
        }else{
            if(!mageAttr.team){
                if( !jQuery(obj2).val()&& !jQuery(obj3).val()){
                    alert("至少有一项不能为空");
                    return false;
                }
            }
        }
        return true;
    }

    //function checkinput(obj, msg){
    //    if(!jQuery(obj).val()){
    //        tab(".swiper-slide:eq(4)");
    //        tab(".tabCont:eq(4)");
    //        alert(msg);
    //        return false;
    //    }
    //
    //    if(!submitYP_CheckText(obj)){
    //        return false;
    //    }
    //    return true;
    //}

    function selectFont(){
        var color=jQuery(".fontcolor li.active").attr("data");
        var fontdata=jQuery(".font li.active").attr("data");
        var font="font"+fontdata;
        jQuery(".viewbox").removeClass().addClass("viewbox "+color +" "+font );
        jQuery(".fontpic").each(function(){
            jQuery(this).find("b:eq("+(fontdata-1)+")").show().siblings("b").hide();
        });
    }
    function setTotal(double){
        var total="";
        var len = detailData.length;
        var clothesTotal=jQuery(".madeBoxVal .font_yel b").text();
        var pantsTotal=jQuery("#pantsPrice").val();
        if(double==1){
            total = len*Math.round(clothesTotal*100+pantsTotal*100)/100;
        }else{
            total = len*Math.round(clothesTotal*100)/100;
        }
        jQuery(".totalbox .num").text(len);
        jQuery(".totalbox .total").text(total);
    }
    function setSize2(size2txt){
        var txt= "您所需球裤尺码：<b class='fontyellow'>"+size2txt+"</b>";
        jQuery(".madeSize2 span").html(txt);
    }
    /*
     *显示的页面
      */
    function tab(obj){
        jQuery(obj).addClass("active").siblings().removeClass("active");
    }
    function showpage(obj){
        tab(obj);
        switch(obj)
        {
            case ".cusContBox .custompage:eq(0)":
                jQuery(".cusMadeShirt").removeAttr("style");
                jQuery(".madeBoxVal").show();
                break;
            case ".cusContBox .custompage:eq(1)":
                jQuery(".arrowBtn").show();
                jQuery(".headbtn").hide();
                jQuery(".madeBoxVal").show();
                jQuery(".cusMadeShirt").removeAttr("style");
                jQuery(".cusMadeShirt").addClass("active");
                var swiper = new Swiper('.swiper-container', {
                    slidesPerView: 3.2,
                    paginationClickable: true,
                    spaceBetween:0
                });
                break;
            case ".cusContBox .custompage:eq(2)":
                jQuery(".cusMadeShirt").removeAttr("style");
                showtitle();
                jQuery(".madeBoxVal").show();
                break;
            case ".cusContBox .custompage:eq(3)":
                jQuery(".headbtn").hide();
                jQuery(".arrowBtn").hide();
                jQuery(".madeBoxVal").hide();
                controlitemBox(".cusmoreBox");
                break;
            case ".cusContBox .custompage:eq(4)":
                jQuery(".cusMadeShirt").removeAttr("style");
                showtitle();
                jQuery(".madeBoxVal").show();
                break;
            default:
        }

    }
    /*
     * 切换正反面
     */
    function selectpage(idx){
        var obj1 = jQuery(".arrowBtn .btn:eq("+idx+")");
        showpage(obj1);
        var obj2 = jQuery(".slideshow li:eq("+idx+")");
        showpage(obj2);
        showTxt(".tabContslide .madeTeam input",".viewbox .textMadeA",teamFontRange);
        showTxt(".tabContslide .madePlayer input",".viewbox .textMadeB",memberFontRange);
    }

    function selectFontStyle(obj,text){
        var index = jQuery(".tabCont  .style li.active").index();
        if(index==0){
            jQuery(obj).html('<div class="dom">'+text+'</div>');
            jQuery(obj+" .dom").arctext({	radius:-1,dir: 1});
        }else{
            jQuery(obj).html('<div class="dom">'+text+'</div>');
            jQuery(obj+" .dom").arctext({radius: 300});
        }
    }
    function deleteCustomOrder(){
        madeLoading("取消定制","数据加载中,请耐心等待...");
        jQuery.ajax({
            type: 'POST',
            url:jQuery('#deleteData').val() ,
            success: function () {
                document.location.reload();
            },
            complete:function(){
                //madeLoadingClose();
            }
        })
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

    function setSenddata(){
        if(mageAttr["double"]==0){
            jQuery.each(detailData,function(key,node){
                detailData[key]["size2"] = '';
            })
        }
    }
    function setBackdata(){
        if(mageAttr["double"]==0){
            jQuery.each(detailData,function(key,node){
                var size2=detailData[key].size;
                detailData[key]["size2"] = size2;
            })
        }
    }

    function addRow(player,num,size,size2){
        if(mageAttr["double"] == 0){
            html = "<tr><td  class='icon'>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td><a href='#' class='delRow'></a></td></tr>"
        }else{
            html = "<tr><td  class='icon'>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td>"+size2+"</td><td><a href='#'  class='delRow'></a></td></tr>";
        }
        jQuery(".editmoreBox tbody").append(html);
    }
    function tipShow(obj){
        delRowId = jQuery(obj).closest("tr").index();
        if(detailData.length>1){
            jQuery(".comfBox").show();
            jQuery(".maskbg").show();
        }else{
            alert("至少一条定制");
        }
    }
    function tipHide(){
        jQuery(".comfBox").hide();
        jQuery(".maskbg").hide();
    }
    function delRow(){
        itemListNum=delRowId;
        jQuery(".editmoreBox .madeTable tbody tr:eq("+delRowId+")").remove();
        detailData.splice(itemListNum,1)
        setTotal(mageAttr["double"]);
        tipHide();
        madeInitData();
    }
    function editRow(obj){
        var i = jQuery(obj).closest("tr").index();
        itemListNum = i;
        addinpData(itemListNum);//默认值
        controlitemBox(".additemBox");
    }
    function showCarlist(){
        setSuit();
        setTotal(mageAttr["double"]);
        var html = "", player , num;
        jQuery(".cusmoreBox .madeitembox table tbody").html("");
        jQuery.each(detailData,function(key,node){
            player=node.player?node.player:"";
            num=node.num?node.num:"";
            if(mageAttr["double"] == 0){
                html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td></tr>";
            }else{
                html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td></tr>";
            }
        })
        jQuery(".cusmoreBox .madeitembox table tbody").append(html);
    }
    /*
     * 判断显示文字的个数
     * */
    function showTxt(obj,obj2,attr) {
        jQuery(".fontpic").hide();
        var _val = jQuery(obj).val();
        var text = cut_str(_val,obj2,attr);
        selectFontStyle(obj2,text);
    }
    function showTxt2(obj) {
        var _val = jQuery(obj).val();
        var text =cut_str2(_val);
       return text;
    }
    function cut_str2(str){
        var str = formatInputString(str);
        str = str.trim();
        var char_length = 0;
        for (var i = 0; i < str.length; i++){
            var son_str = str.charAt(i);
            encodeURI(son_str).length > 2 ? char_length += 2 : char_length += 1;
            if (char_length >= 20){
                var sub_len = char_length == 20 ? i+1 : i;
                return str.substr(0, sub_len);
            }
        }
        return str;
    }
    function formatInputString(str){
        var str = str.replace(/   */g," ");
        str = str.replace(/   */g," ");
        return str;
    }
    function cut_str(str,obj2,attr){
        var str = formatInputString(str);
        str = str.trim();
        var char_length = 0;
        for (var i = 0; i < str.length; i++){
            var son_str = str.charAt(i);
            encodeURI(son_str).length > 2 ? char_length += 2 : char_length += 1;
            if (char_length >= 20){
                var sub_len = char_length == 20 ? i+1 : i;
                return str.substr(0, sub_len);
            }
        }
        setFontSize(obj2,char_length,attr);
        return str;
    }

    function setFontSize(obj,len,arr){
        var fontSize;
        jQuery.each(arr, function(i, n){
            if(len>i){
                fontSize=n;
            }
        })
        jQuery(obj).css("fontSize",fontSize);
    }
    function setActionUrl(){
        var productId = jQuery(".color li.active").attr("data-productId");
        var actionUrl = jQuery("#customclothes_addtocart_form").attr("action");
        var pattern = new RegExp("/product\/\\d+\/form_key/");
        if(pattern.test(actionUrl)){
            var replaceStr = "/product/"+productId+"/form_key/";
            var newActionUrl = actionUrl.replace(pattern,replaceStr);
            jQuery("#customclothes_addtocart_form").attr("action",newActionUrl);
        }

    }
    function showeditdata(){
        setSuit();
        var html = "", player , num;
        jQuery(".editmoreBox .madeTable tbody").html("");
        jQuery.each(detailData,function(key,node){
            player=node.player?node.player:"";
            num=node.num?node.num:"";
            if(mageAttr["double"] == 0){
                html += "<tr><td class='icon'>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td><a href='#'  class='delRow'></a></td></tr>";
            }else{
                html += "<tr><td class='icon'>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td><td><a href='#'  class='delRow'></a></td></tr>";
            }
        })
        jQuery(".editmoreBox .madeTable tbody").append(html);
    }
    /*
     * 是否显示套装
     * */
    function setSuit(){
        jQuery(".madeTable").each(function(){
            jQuery(this).find("thead:eq("+mageAttr['double']+")").show().siblings("thead").hide();
         });
        if(mageAttr["double"] == 0){
            jQuery(".additemBox").removeClass("active");
        }else{
            jQuery(".additemBox").addClass("active");
        }
    }
    function controlitemBox(obj){
        jQuery(obj).show().siblings(".itemBox").hide();

        switch(obj)
        {
            case ".cusmoreBox":
                showCarlist();
                break;
            case ".editmoreBox":
                showeditdata();
                break;
            case ".additemBox":
                jQuery(".additemBox").addClass("animated bounceIn");
                break;
            default:
        }
        jQuery(".cusMadeShirt").removeClass("active");
        var _height=jQuery(".custompage:eq(3)").height()-58;
        jQuery(".cusMadeShirt").css("height",_height);
    }
     function showtitle(){
         jQuery(".arrowBtn").hide();
         jQuery(".headbtn").show();
         if(detailData.length==1){
             jQuery(".headbtn ul li:eq(1)").hide();
         }else{
             jQuery(".headbtn ul li:eq(1)").show();
         }
     }
    /*
     *单件和套装
     */
    function IsSingle(i){
        if(i==1){
            jQuery(".madeSize2").show();
            jQuery(".pant").removeClass("p25");
            jQuery(".slideshow li").each(function(){
                jQuery(this).find(".double").show();
            })
        }else{
            jQuery(".madeSize2").hide();
            jQuery(".pant").addClass("p25");
            jQuery(".slideshow li").each(function(){
                jQuery(this).find(".double").hide();
            })
        }

    }

//更换衣服图片
    function setImgsrc(){
        var single1=jQuery(".slideshow ul li:eq(0) .single .img").attr("src");
        var double1=jQuery(".slideshow ul li:eq(0) .double .img").attr("src");
        var single2=jQuery(".slideshow ul li:eq(1) .single .img").attr("src");
        var double2=jQuery(".slideshow ul li:eq(1) .double .img").attr("src");
        var getsingle1=getImgsrc(single1);
        var getdouble1=getImgsrc(double1);
        var getsingle2=getImgsrc(single2);
        var getdouble2=getImgsrc(double2);
        jQuery(".slideshow ul li:eq(0) .single .img").attr("src",getsingle1);
        jQuery(".slideshow ul li:eq(0) .double .img").attr("src",getdouble1);
        jQuery(".slideshow ul li:eq(1) .single .img").attr("src",getsingle2);
        jQuery(".slideshow ul li:eq(1) .double .img").attr("src",getdouble2);
    }
    function getImgsrc(src){
        var sku=jQuery(".color li.active").attr("sku");
        var pattern = new RegExp("/shirtmade\/img\/\\w+-\\w+");
        if(pattern.test(src)){
            var replaceStr = "/shirtmade/img/"+sku;
            return newActionUrl = src.replace(pattern,replaceStr);
        }
    }
    function madeData(){
        var color = jQuery(".tabContslide .color li.active").attr("color");
        var size = jQuery(".tabContslide .size li.active span").text();
        var font = jQuery(".tabContslide .font li.active").attr("data");
        var style = jQuery(".tabContslide .style li.active").attr("data");
        var fontColor = jQuery(".tabContslide .styleBox li:eq(0) b").attr("color");
        var team = showTxt2(".tabContslide .madeTeam input");
        var player = showTxt2(".tabContslide .madePlayer input");
        var num = jQuery(".tabContslide .madeNum input").val();
        var double = jQuery(".tabContslide .pant li.active").index();
        var size2 = jQuery(".tabContslide .size2 li.active span").text();
        selectFont();
        mageAttr = {"color":color,"font":font,"style":style,"fontColor":fontColor,"team":team,"double":double};
        if(mageAttr["double"]==0){
            detailData[0]={"size":size,"player":player,"num":num,"size2":size};
        }else{
            detailData[0] = {"size":size,"player":player,"num":num,"size2":size2};
        }
        jQuery(".viewbox .textMadeC,.viewbox .textMadeD,.viewbox .textMadeE").text(num);
    }
    function madeInitData(){
        if(detailData.length){
            tab(".tabContslide .color li[color="+mageAttr['color']+"]");
            tab(".tabContslide .size li[size="+detailData[0]['size']+"]");
            tab(".tabContslide .font li[data="+mageAttr['font']+"]");
            tab(".tabContslide .font li[data="+mageAttr['style']+"]");
            tab(".tabContslide .fontcolor li[color="+mageAttr['fontColor']+"]");
            tab(".tabContslide .pant li:eq("+mageAttr['double']+")");
            tab(".tabContslide .size2 li[size="+detailData[0]['size2']+"]");
            var size2txt=jQuery(".tabContslide .size2 li.active").attr("size");
            setSize2(size2txt);
            jQuery(".tabContslide .styleBox li:eq(0) b").attr("color",mageAttr['fontColor']);
            getColor(mageAttr['fontColor'],".tabContslide .styleBox li:eq(0) b");
            jQuery(".viewbox .textMadeC,.viewbox .textMadeD,.viewbox .textMadeE").text(detailData[0]["num"]?detailData[0]["num"]:"");
            jQuery(".tabContslide .madeTeam input").val(mageAttr['team']?mageAttr['team']:"");
            jQuery(".tabContslide .madePlayer input").val(detailData[0]['player']?detailData[0]['player']:"");
            jQuery(".tabContslide .madeNum input").val(detailData[0]['num']?detailData[0]['num']:"");
            selectFont();
            showTxt(".tabContslide .madeTeam input",".viewbox .textMadeA",teamFontRange);
            showTxt(".tabContslide .madePlayer input",".viewbox .textMadeB",memberFontRange);
            //setFontStyle(mageAttr['style']-1);
            setImgsrc();
        }
    }
    /*设置衣服的颜色*/
    function setColor(){
        jQuery(".tabContslide .color  li[color='黑色'] span").addClass("black");
        jQuery(".tabContslide .color  li[color='白色'] span").addClass("white");
        jQuery(".tabContslide .color  li[color='黄色'] span").addClass("yellow");
        jQuery(".tabContslide .color  li[color='蓝色'] span").addClass("blue");
        jQuery(".tabContslide .color  li[color='灰色'] span").addClass("gray");
        jQuery(".tabContslide .color  li[color='橙色'] span").addClass("orange");
        jQuery(".tabContslide .color  li[color='深蓝'] span").addClass("deepBlue");
        jQuery(".tabContslide .color  li[color='粉色'] span").addClass("pink");
    }
    function getColor(color,obj){
        jQuery(obj).removeClass();
        switch (color)
        {
            case "黑色":
                jQuery(obj).addClass("black");
                break;
            case "白色":
                jQuery(obj).addClass("white");
                break;
            case "黄色":
                jQuery(obj).addClass("yellow");
                break;
            case "蓝色":
                jQuery(obj).addClass("blue");
                break;
            case "灰色":
                jQuery(obj).addClass("gray");
                break;
            case "橙色":
                jQuery(obj).addClass("orange");
                break;
            case "深蓝":
                jQuery(obj).addClass("deepBlue");
                break;
            case "粉色":
                jQuery(obj).addClass("pink");
            case "红色":
                jQuery(obj).addClass("red");
                break;
        }
    }
    /*
     * 添加多件保存数据
     * */
    function saveData(){
        var html = "";
        var player = showTxt2(".additemBox .player input");
        var num = jQuery(".additemBox .num input").val();
        var size = jQuery(".additemBox .size li.active").text();
        var size2 = jQuery(".additemBox .size2 li.active").text();
        if(itemListNum == -1){//如果0就添加,否则对应的行修改
            addRow(player,num,size,size2);
            if(mageAttr["double"] == 1){
                detailData.push({"size":size,"player":player,"num":num,"size2":size2});
            }else{
                detailData.push({"size":size,"player":player,"num":num,"size2":size});
            }
            setTotal(mageAttr["double"]);
        }else{
            jQuery(".editmoreBox tbody tr:eq("+itemListNum+")").find("td:eq(0)").text(player);
            jQuery(".editmoreBox tbody tr:eq("+itemListNum+")").find("td:eq(1)").text(num);
            jQuery(".editmoreBox tbody tr:eq("+itemListNum+")").find("td:eq(2)").text(size);
            if(mageAttr["double"] == 1){
                jQuery(".editmoreBox tbody tr:eq("+itemListNum+")").find("td:eq(3)").text(size2);
                detailData[itemListNum] = {"size":size,"player":player,"num":num,"size2":size2};
            }else{
                detailData[itemListNum] = {"size":size,"player":player,"num":num,"size2":size};
            }
        }
       controlitemBox(".editmoreBox");
    }
    /*添加时弹出框的状态*/
    function clearinpData(){
        jQuery(".additemBox .player input").val("");
        jQuery(".additemBox .num input").val("");
        showpage(".additemBox .size li:eq(0)");
        showpage(".additemBox .size2 li:eq(0)");
    }
    /*编辑时弹出框的状态*/
    function addinpData(id){
        jQuery(".additemBox .player input").val(detailData[id]["player"]);
        jQuery(".additemBox .num input").val(detailData[id]["num"]);
        jQuery(".additemBox .size li").each(function(){
            var text=jQuery(this).find("span").text();
            if(text== detailData[id]["size"]){
                showpage(this);
            }
        })
        jQuery(".additemBox .size2 li").each(function(){
            var text=jQuery(this).find("span").text();
            if(text == detailData[id]["size2"]){
                showpage(this);
            }
        })
    }
    function previewData(){
        setTotal(mageAttr["double"]);
        IsSingle(mageAttr["double"]);
        madeInitData();
        if(detailData.length > 1){
            setBackdata();
            showpage(".cusContBox .custompage:eq(3)");
        }
        else if(detailData.length==1){
            setBackdata();
            showpage(".cusContBox .custompage:eq(1)");
        }
        else{
            showpage(".cusContBox .custompage:eq(0)");
        }
    }
jQuery(function () {
    var bind_name = 'input';
    if (navigator.userAgent.indexOf("MSIE") != -1){
        bind_name = 'propertychange';
    }
    jQuery(".tabCont li,.additemBox .col_four li").click(function(){
        showpage(this);
    })

    jQuery("#confirm2").click(function(){
        var flag = checkTextYN("team",".additemBox .player input",".additemBox .num input");
        if(flag){
            saveData();
            madeInitData();
        }
    })

    jQuery(".arrowBtn .btn").click(function(){
        var idx=jQuery(this).index();
        selectpage(idx);
    });

    jQuery(".style li").click(function(){
        showTxt(".tabContslide .madeTeam input",".viewbox .textMadeA",teamFontRange);
        showTxt(".tabContslide .madePlayer input",".viewbox .textMadeB",memberFontRange);
    })
    /*输入内容*/
    jQuery(".madeTeam input").bind(bind_name,function(){
       showTxt(this,".viewbox .textMadeA",teamFontRange);
    })
    jQuery(".madePlayer input").bind(bind_name,function(){
        showTxt(this,".viewbox .textMadeB",memberFontRange);
    })
    jQuery(".madeNum input").keyup(function(){
        jQuery(".fontpic").hide();
        var text=jQuery(this).val();
        jQuery(".viewbox .textMadeC,.viewbox .textMadeD,.viewbox .textMadeE").text(text);
    })
    jQuery(".additemBox .player input").bind(bind_name,function(){
        showTxt2(this);
    })
    /*开始定制*/
    jQuery("#startBtn").click(function(){
        showpage(".cusContBox .custompage:eq(1)");
        jQuery(".tipgif").show();
        setTimeout(function(){
            jQuery(".tipgif").hide();
        },4000)
    })
    /*切换定制内容*/
    jQuery(".swiper-container  .swiper-slide").click(function(){
        var i=jQuery(this).index();
        if(detailData.length>=1){
            if(i==2){
                jQuery(".fontpic").show().siblings("div").hide();
            }else{
                jQuery(".fontpic").hide().siblings("div").show();
            }
        }else{
            if(jQuery(".styleBox .madeTeam input").val()||jQuery(".styleBox .madePlayer input").val()||jQuery(".styleBox .madeNum input").val()||i==4){
                jQuery(".fontpic").hide().siblings("div").show();
            }else{
                jQuery(".fontpic").show().siblings("div").hide();
            }
        }
        showpage(this);
        var obj1= jQuery(".tabContslide .tabCont:eq("+i+")");
        showpage(obj1);
    })

    jQuery("#setColor").click(function(){
        jQuery(".fontcolor").show().addClass("animated slideInUp").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
            jQuery(".fontcolor").removeClass("slideInUp animated");
        });
    })

    jQuery("#getColor").click(function(){
        var _class=jQuery(".fontcolor ul li.active span").attr("class");
        var _color=jQuery(".fontcolor ul li.active").attr("color");
        jQuery("#setColor").removeClass().addClass(_class);
        jQuery("#setColor").attr("color",_color);
        jQuery(".fontcolor").addClass("animated slideOutDown").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
            jQuery(".fontcolor").removeClass("slideOutDown animated").hide();
        });
    })

    jQuery(".tabContslide .pant li").click(function(){
        var i=jQuery(this).index();
        IsSingle(i);
    })
    jQuery(".tabContslide .color li").click(function(){
        setImgsrc();
    })
    jQuery(".madeSize2 span").click(function(){
        jQuery(".tabContslide .size2").show().addClass("slideInUp animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
            jQuery(".tabContslide .size2").removeClass("slideInUp animated");
        });
    })
    jQuery(".tabContslide .size2 li").click(function(){
        var size2=jQuery(this).attr("size");
        setSize2(size2);
        jQuery(".tabContslide .size2").addClass("slideOutDown animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
            jQuery(".tabContslide .size2").removeClass("slideOutDown animated").hide();
        });
    })
    /*显示衣服的字体颜色和字体*/
    jQuery(".fontcolor li,.font li").click(function(){
        selectFont();
    })

    jQuery(".addMoreBtn").click(function(){
        showpage(".cusContBox .custompage:eq(3)");
    })
    jQuery("#confirm4").click(function(){
        showpage(".cusContBox .custompage:eq(4)");
    })
    jQuery("#confirm3").click(function(){
        controlitemBox(".cusmoreBox")
    })
    jQuery("#addbtn").click(function(){
        itemListNum = -1;
        if(detailData.length<=14){
            controlitemBox(".additemBox")
        }else{
            alert("不能超过15条")
        }
        clearinpData();
    })
    jQuery(".additemBox .close").click(function(){
        controlitemBox(".editmoreBox")
    })
    jQuery("#saveBtn").click(function(){
        var flag = checkTextYN(".styleBox .madeTeam input",".styleBox .madePlayer input",".styleBox .madeNum input");
        if(flag){
            madeData();
            if(detailData.length > 1){
                showpage(".cusContBox .custompage:eq(3)");
            }
            else{
                showpage(".cusContBox .custompage:eq(2)");
            }
            setTotal(mageAttr["double"]);
        }
    });
    jQuery(".headbtn li:eq(0)").click(function(){
        showpage(".cusContBox .custompage:eq(1)");
    })
    jQuery(".headbtn li:eq(1)").click(function(){
        showpage(".cusContBox .custompage:eq(3)");
    })
    jQuery("#editBtn").click(function(){
        controlitemBox(".editmoreBox");
    })
    jQuery(document)
        .on("click",".editmoreBox tbody tr td",function(){
            editRow(this);
        })
        .on("click",".delRow",function(e){
            e.stopPropagation();
            tipShow(this);
        })
    jQuery("#saveYesId").click(function(){
        delRow();
    })
    jQuery("#saveNoId").click(function(){
        tipHide();
     })
    jQuery(".submit").click(function(){
        setSenddata();
        setActionUrl();
        jQuery("#mainData").val(JSON.stringify(mageAttr));
        jQuery("#secondData").val(JSON.stringify(detailData));
        jQuery("#customclothes_addtocart_form").submit();
        madeLoading("提交成功","数据加载中,请耐心等待...");
    })
    jQuery(".submitBox .cancelcustom").click(function(){
            deleteCustomOrder();
    })
    setColor();
    jQuery("#scrollbar").perfectScrollbar({suppressScrollX: true});
    jQuery("#scrollbar1").perfectScrollbar({suppressScrollX: true});
    jQuery(".rowsAgr").click(function(){
        jQuery(".JrowsWrap").hide();
        jQuery.ajax({
            type: 'POST',
            url: jQuery('#agree').val()
        });
    });
});