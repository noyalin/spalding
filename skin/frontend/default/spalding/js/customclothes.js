var jqXHR = null;
jQuery(function () {
    mageAttr = [];
    detailData = [];
    itemListNum = -1;
    delRowId = "";
    jQuery(document)
        .on("click","#cusMadeBox .styleBox",function(){
            selecttab(this);
        })
        .on("click",".ullist li",function(){
            selecttab(this);
        })
        .on("click",".madeColor .ullist li",function(){
            selecttab(this);
            setImgsrc();
        })
        .on("click",".viewMadebox .aBtn",function(){
            selectpage();
        })
        .on("click",".madeDouble .ullist li",function(){
            var i=jQuery(this).index();
            IsSingle(i);
        })
        .on("click",".madeFontColor .ullist li,.madeFont .ullist li",function(){
          selectFont();
        })
        .on("click",".madeStyle .ullist li",function(){
            var index=jQuery(this).index();
            setFontStyle(index);
        })
        .on("click","#next1",function(){
            var flag = checkTextYN(".madeBoxCont1 .madeTeam input",".madeBoxCont1 .madePlayer input",".madeBoxCont1 .madeNum input");
            if(flag){
                madeData();
                if(detailData.length > 1){
                    showCarlist();
                    jQuery(".madeBoxCont4").addClass("active").siblings("").removeClass("active");
                }
                else{
                    selectNextcont(this);
                    showsingledata();
                }
                setTotal(mageAttr["double"]);
            }
        })
        .on("blur",".madeTeam input,.madePlayer input,.madeNum input,.madeInpBox .player input,.madeInpBox .num input",function(){
            checkTextYN(this);
        })
        .on("click",".madeTeam input,.madePlayer input,.madeNum input,.madeInpBox .player input,.madeInpBox .num input",function(){
            jQuery(this).siblings().hide();
        })
        .on("keyup",".madeTeam input",function(){
            showTxt(this,".viewbox .textMadeA",teamFontRange);
            selectFontStyle(".viewbox .textMadeA",this);
        })
        .on("keyup",".madePlayer input",function(){
            showTxt(this,".viewbox .textMadeB",memberFontRange);
            selectFontStyle(".viewbox .textMadeB",this);
        })
        .on("keyup",".madeNum input",function(){
            var text=jQuery(this).val();
            jQuery(".viewbox .textMadeC,.viewbox .textMadeD,.viewbox .textMadeE").text(text);
        })
        .on("keyup",".additemBox .player input",function(){
            showTxt(this,"","");
            selectFontStyle("",this);
        })
        .on("click","#next2",function(){
            selectNextcont(this);
            showlistdata();
            setTotal(mageAttr["double"]);
        })
        .on("click","#confirm2",function(){
            var flag = checkTextYN("team",".additemBox .player input",".additemBox .num input");
            if(flag){
                saveData();
                madeInitData();
            }
        })
        .on("click","#confirm3",function(){
            selectNextcont(this);
            showCarlist();
        })
        .on("click","#addlist",function(){
            itemListNum = -1;
            if(detailData.length<=14){
                showInpBox();
            }else{
                alert("不能超过15条")
            }
            clearinpData();
        })
        .on("click","#backSingle,#backSingle1",function(){
            madeInitData();
            jQuery(".madeBoxCont1").addClass("active").siblings("").removeClass("active");
        })
        .on("click","#backmore",function(){
            showlistdata();
            selectprevcont(this);
        })
        .on("click",".additemBox .close",function(){
            hideInpBox();
        })
        .on("click",".delRow",function(){
            tipShow(this);
        })
        .on("click","#saveYesId",function(){
            delRow();
        })
        .on("click","#saveNoId",function(){
            tipHide();
        })
        .on("click",".submitBox .submit",function(){
            setSenddata();
            setActionUrl();
            jQuery("#mainData").val(JSON.stringify(mageAttr));
            jQuery("#secondData").val(JSON.stringify(detailData));
            jQuery("#customclothes_addtocart_form").submit();
            madeLoading("提交成功","数据加载中,请耐心等待...");
        })
        .on("click",".submitBox .cancelcustom",function(){
            deleteCustomOrder();
        });
    jQuery(".rowsAgr").click(function(){
        jQuery(this).parent().parent().parent(".rows").hide();
        jQuery.ajax({
            type: 'POST',
            url: jQuery('#agree').val()
        });
    });
    jQuery('.jScrollbar3').jScrollbar();
    jQuery("#scrollbar").perfectScrollbar({suppressScrollX: true});
    setColor();
});
function deleteCustomOrder(){
    madeLoading("取消定制","数据加载中,请耐心等待...");
    jQuery.ajax({
        type: 'POST',
        url:jQuery('#deleteData').val() ,
        success: function () {
            document.location.reload();
        },
        complete:function(){
           // madeLoadingClose();
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

function setTotal(double){
    var total="";
    var len = detailData.length;
    var clothesTotal=jQuery(".madeBoxTitle h2 b").text();
    var pantsTotal=jQuery("#pantsPrice").val();
    if(double==1){
        total = len*Math.round(clothesTotal*100+pantsTotal*100)/100;
    }else{
        total = len*Math.round(clothesTotal*100)/100;
    }
    jQuery(".totalbox .num").text(len);
    jQuery(".totalbox .total").text(total);
}
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

            txtInput.focus();
            return false;
        } else if (count.length > 0) {
            var tips = '"' + jQuery(txtInput).val() + '"包含"' + count[0] + '"敏感词！';
            checktips(txtInputId,tips);
            txtInput.focus();
            return false;
        } else {
            return true;
        }
    }
    return true;
}
function checkTextYN(obj1,obj2,obj3){
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
    if(!submitYP_CheckText(obj3)) {
        return false;
    }
    var _val=jQuery(obj3).val();
    if(_val=='0' || _val=='00'){
        checktips(obj3,"球员号码不能为0");
        return false;
    }
    if(obj1!="team"){
        if(!jQuery(obj1).val()&& !jQuery(obj2).val()&& !jQuery(obj3).val()){
            checktips(obj3,"至少有一项不能为空");
            jQuery(obj3).siblings(".tips").hide();
            return false;
        }
    }else{
         if(!mageAttr.team){
             if( !jQuery(obj2).val()&& !jQuery(obj3).val()){
                 checktips(obj3,"至少有一项不能为空");
                 return false;
             }
            }
        }
    return true;
}
function checktips(obj,msg){
    jQuery(obj).parents(".styleBox").addClass("active").siblings().removeClass("active");
    jQuery(obj).siblings(".error").html(msg);
    jQuery(obj).siblings("").show();
}
//function checkinput(obj, msg){
//    if(!jQuery(obj).val()){
//        checktips
//        return false;
//    }
//
//    if(!submitYP_CheckText(obj)){
//        return false;
//    }
//    return true;
//}
/*
* 判断显示文字的个数
* */
function showTxt(obj,obj2,attr) {
    var str = formatInputString(obj);
    var _txt = str.trim();
    var text=getMadeText(_txt,obj2,attr);
    jQuery(obj).innerText=text;
    return text;
}

function formatInputString(key){
    str =jQuery(key).val();
    str = str.replace(/　　*/g," ");
    str = str.replace(/   */g," ");
    jQuery(key).val(str);
    return str;
}
function getMadeText(str,obj,attr){
    var templen=0;
    for(var i=0;i<str.length;i++){
        if(str.charCodeAt(i)>255){
            templen+=2;
        }else{
            templen++
        }
        if(templen == 20){
            setFontSize(obj,templen,attr);
            return str.substring(0,i+1);
        }else if(templen >20){
            setFontSize(obj,templen,attr);
            return str.substring(0,i);
        }
    }
    setFontSize(obj,templen,attr);
    return str;
}

function setActionUrl(){
    var productId = jQuery(".madeColor li.active").attr("data-productId");
    var actionUrl = jQuery("#customclothes_addtocart_form").attr("action");
    var pattern = new RegExp("/product\/\\d+\/form_key/");
    if(pattern.match(actionUrl)){
        var replaceStr = "/product/"+productId+"/form_key/";
        var newActionUrl = actionUrl.replace(pattern,replaceStr);
        jQuery("#customclothes_addtocart_form").attr("action",newActionUrl);
    }

}
/*
* 是否显示套装
* */
function setSuit(){
    jQuery("#madeTable thead").eq(mageAttr["double"]).show().siblings("thead").hide();
    if(mageAttr["double"] == 0){
        jQuery(".additemBox").removeClass("active");
    }else{
        jQuery(".additemBox").addClass("active");
    }
}
function hideInpBox(){
    jQuery(".additemBox").hide();
}
function showInpBox(){
    jQuery(".additemBox").show();
}

/*
* 添删件数
* */
function addRow(player,num,size,size2){
    if(mageAttr["double"] == 0){
        html = "<tr><td>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td><a href='#'  class='edit' onclick=editRow(this)></a><a href='#' class='delRow'></a></td></tr>"
    }else{
        html = "<tr><td>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td>"+size2+"</td><td><a href='#' class='edit' onclick=editRow(this)></a><a href='#'  class='delRow'></a></td></tr>";
    }
    jQuery("#madeTable tbody").append(html);
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
    jQuery("#madeTable tbody tr:eq("+delRowId+")").remove();
    detailData.splice(itemListNum,1)
    setTotal(mageAttr["double"]);
    tipHide();
    madeInitData();
}
function editRow(obj){
    var i = jQuery(obj).closest("tr").index();
    itemListNum = i;
    addinpData(itemListNum);//默认值
    showInpBox();
}
function showlistdata(){
    setSuit();
    var html = "", player , num ;
    jQuery("#madeTable tbody").html("");
    jQuery.each(detailData,function(key,node){
        player=node.player?node.player:"";
        num=node.num?node.num:"";
        if(mageAttr["double"] == 0){
            html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td><a href='#' class='edit' onclick=editRow(this)></a><a href='#'  class='delRow'></a></td></tr>";
        }else{
            html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td><td><a href='#' class='edit' onclick=editRow(this)></a><a href='#'  class='delRow'></a></td></tr>";
        }
    })
    jQuery("#madeTable tbody").append(html);
}
function showCarlist(){
    var font=setFont(mageAttr["font"]);
    var fontStyle=setFontStyleName(mageAttr["style"]);
    jQuery(".madeBoxCont4 .list-item li.font span").text(font);
    jQuery(".madeBoxCont4 .list-item li.style span").text(fontStyle);
    getColor(mageAttr["color"],".madeBoxCont4 .list-item li.color span");
    jQuery(".madeBoxCont4 .list-item li.fontColor span").text(mageAttr["fontColor"]);
    jQuery(".madeBoxCont4 .list-item li.team span").text(mageAttr["team"]?mageAttr["team"]:"");
    var html = "", player , num ;
    jQuery(".madeBoxCont4 .moreBox table tbody").html("");
    jQuery.each(detailData,function(key,node){
        player=node.player?node.player:"";
        num=node.num?node.num:"";
        if(mageAttr["double"] == 0){
            html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td></tr>";
        }else{
            html += "<tr><td>"+player+"</td><td>"+num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td></tr>";
        }
    })
    jQuery(".madeBoxCont4 .moreBox table tbody").append(html);
}
/*设置衣服的颜色*/
function setColor(){
    jQuery(".madeColor .ullist li[color='黑色']").addClass("black");
    jQuery(".madeColor .ullist li[color='白色']").addClass("white");
    jQuery(".madeColor .ullist li[color='黄色']").addClass("yellow");
    jQuery(".madeColor .ullist li[color='蓝色']").addClass("blue");
    jQuery(".madeColor .ullist li[color='灰色']").addClass("gray");
    jQuery(".madeColor .ullist li[color='橙色']").addClass("orange");
    jQuery(".madeColor .ullist li[color='深蓝']").addClass("deepBlue");
    jQuery(".madeColor .ullist li[color='粉色']").addClass("pink");
}
function getColor(color,obj){
    switch (color)
    {
        case "黑色":
            jQuery(obj).removeClass().addClass("black");
            break;
        case "白色":
            jQuery(obj).removeClass().addClass("white");
            break;
        case "黄色":
            jQuery(obj).removeClass().addClass("yellow");
            break;
        case "蓝色":
            jQuery(obj).removeClass().addClass("blue");
            break;
        case "灰色":
            jQuery(obj).removeClass().addClass("gray");
            break;
        case "橙色":
            jQuery(obj).removeClass().addClass("orange");
            break;
        case "深蓝":
            jQuery(obj).removeClass().addClass("deepBlue");
            break;
        case "粉色":
            jQuery(obj).addClass("pink");
            break;
    }
}
function setFont(ele){
    if(ele==1){
        return font="字体1";
    }else if(ele==2){
        return font="字体2";
    }else if(ele==3){
        return font="字体3";
    }else if(ele==4){
        return font="字体4";
    }
}
function setFontStyleName(ele){
    if(ele==1){
        return fontStyle="常规";
    }else if(ele==2){
        return fontStyle="弯曲";
    }
}
/*
*切换选项
*/
function selecttab(obj){
    jQuery(obj).addClass("active").siblings().removeClass("active");
}
/*
*切换下一页
* */
function selectNextcont(obj){
    jQuery(obj).parents(".madeBoxCont").removeClass("active").next(".madeBoxCont").addClass("active");
}
function selectprevcont(obj){
    jQuery(obj).parents(".madeBoxCont").removeClass("active").prev(".madeBoxCont").addClass("active");
}
/*
* 切换正反面
*/
function selectpage(){
    jQuery(".slideshow li.active").removeClass("active").siblings().addClass("active");
    var styleId=jQuery(".madeStyle .ullist li.active").index();
    setFontStyle(styleId);
}
/*
*单件和套装
*/
function IsSingle(i){
    if(i==1){
        jQuery(".slideshow li").each(function(){
            jQuery(this).find(".double").show();
        })
    }else{
        jQuery(".slideshow li").each(function(){
            jQuery(this).find(".double").hide();
        })
    }

}
/*
*判断数字
 */
function IsNum(obj) {
    jQuery(obj).value = jQuery(obj).value.replace(/\D/g,'')
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
/*
* 定制信息
* */
function selectFont(){
    var color=jQuery(".madeFontColor li.active").attr("data");
    var fontdata=jQuery(".madeFont li.active").attr("data");
    var font="font"+fontdata;
    jQuery(".viewbox").removeClass().addClass("viewbox "+color +" "+font );
}
function selectFontStyle(obj,obj2){
    var index=jQuery(".madeStyle .ullist li.active").index();
    if(index==0){
        jQuery(obj).html('<div class="dom">'+jQuery(obj2).val()+'</div>');
        jQuery(obj+" .dom").arctext({	radius:-1,dir: 1});
    }else{
        jQuery(obj).html('<div class="dom">'+jQuery(obj2).val()+'</div>');
        jQuery(obj+" .dom").arctext({radius: 300});
    }
}
function setFontStyle(index){
    if(index==0){
        jQuery(".viewbox .textMadeA").html('<div class="dom">'+jQuery(".madeTeam input").val()+'</div>');
        jQuery(".viewbox .textMadeA .dom").arctext({	radius:-1,dir: 1});
        jQuery(".viewbox .textMadeB").html('<div class="dom">'+jQuery(".madePlayer input").val()+'</div>');
        jQuery(".viewbox .textMadeB .dom").arctext({	radius:-1,dir: 1});
    }else{
        jQuery(".viewbox .textMadeA").html('<div class="dom">'+jQuery(".madeTeam input").val()+'</div>');
        jQuery(".viewbox .textMadeA .dom").arctext({radius: 300});
        jQuery(".viewbox .textMadeB").html('<div class="dom">'+jQuery(".madePlayer input").val()+'</div>');
        jQuery(".viewbox .textMadeB .dom").arctext({radius: 300});
    }
}
function madeData(){
    var color = jQuery(".madeColor li.active").attr("color");
    var size = jQuery(".madeSize li.active").text();
    var font = jQuery(".madeFont li.active").attr("data");
    var style = jQuery(".madeStyle li.active").attr("data");
    var fontColor = jQuery(".madeFontColor li.active").attr("color");
    var team = jQuery(".madeTeam input").val();
    var player = jQuery(".madePlayer input").val();
    var num = jQuery(".madeNum input").val();
    var double = jQuery(".madeDouble li.active").index();
    var size2 = jQuery(".madeSize2 li.active").text();
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
        jQuery(".madeColor li[color="+mageAttr['color']+"]").addClass("active").siblings().removeClass("active");
        jQuery(".madeSize li[size="+detailData[0]['size']+"]").addClass("active").siblings().removeClass("active");
        jQuery(".madeFont li[data="+mageAttr['font']+"]").addClass("active").siblings().removeClass("active");
        jQuery(".madeStyle li[data="+mageAttr['style']+"]").addClass("active").siblings().removeClass("active");
        jQuery(".madeFontColor li[color="+mageAttr['fontColor']+"]").addClass("active").siblings().removeClass("active");
        jQuery(".viewbox .textMadeC,.viewbox .textMadeD,.viewbox .textMadeE").text(detailData[0]["num"]?detailData[0]["num"]:"");
        jQuery(".madeTeam input").val(mageAttr['team']?mageAttr['team']:"");
        jQuery(".madePlayer input").val(detailData[0]['player']?detailData[0]['player']:"");
        jQuery(".madeNum input").val(detailData[0]['num']?detailData[0]['num']:"");
        jQuery(".madeDouble li").eq(mageAttr['double']).addClass("active").siblings().removeClass("active");
        jQuery(".madeSize2 li[size="+detailData[0]['size2']+"]").addClass("active").siblings().removeClass("active");
        selectFont();
        showTxt(".madeTeam input",".viewbox .textMadeA",teamFontRange);
        showTxt(".madePlayer input",".viewbox .textMadeB",memberFontRange);
        setFontStyle(mageAttr['style']-1);
        setImgsrc();
    }
}
//更换衣服图片
function setImgsrc(){
    var single1=jQuery(".slideshow ul li:eq(0) .single img").attr("src");
    var double1=jQuery(".slideshow ul li:eq(0) .double img").attr("src");
    var single2=jQuery(".slideshow ul li:eq(1) .single img").attr("src");
    var double2=jQuery(".slideshow ul li:eq(1) .double img").attr("src");
    var getsingle1=getImgsrc(single1);
    var getdouble1=getImgsrc(double1);
    var getsingle2=getImgsrc(single2);
    var getdouble2=getImgsrc(double2);
    jQuery(".slideshow ul li:eq(0) .single img").attr("src",getsingle1);
    jQuery(".slideshow ul li:eq(0) .double img").attr("src",getdouble1);
    jQuery(".slideshow ul li:eq(1) .single img").attr("src",getsingle2);
    jQuery(".slideshow ul li:eq(1) .double img").attr("src",getdouble2);
}
function getImgsrc(src){
    var sku=jQuery(".madeColor li.active").attr("sku");
    var pattern = new RegExp("/shirtmade\/img\/\\w+-\\w+");
    if(pattern.match(src)){
        var replaceStr = "/shirtmade/img/"+sku;
        return newActionUrl = src.replace(pattern,replaceStr);

    }
}
function previewData(){
    setTotal(mageAttr["double"]);
    IsSingle(mageAttr["double"]);
    madeInitData();
    if(detailData.length > 1){
        setBackdata();
        showCarlist();
        jQuery(".madeBoxCont4").addClass("active").siblings("").removeClass("active");
    }
    else if(detailData.length==1){
        setBackdata();
        jQuery(".madeBoxCont2").addClass("active").siblings("").removeClass("active");
        showsingledata();
    }
    else{
        jQuery(".madeBoxCont1").addClass("active").siblings("").removeClass("active");
    }
}
function showsingledata(){
    var font=setFont(mageAttr["font"]);
    var fontStyle=setFontStyleName(mageAttr["style"]);
    getColor(mageAttr["color"],".list-item li.color span");
    //jQuery(".list-item li.color span").addClass(color);
    jQuery(".list-item li.size span").text(detailData[0]["size"]);
    jQuery(".list-item li.font span").text(font);
    jQuery(".list-item li.style span").text(fontStyle);
    jQuery(".list-item li.fontColor span").text(mageAttr["fontColor"]);
    jQuery(".list-item li.team span").text(mageAttr["team"]);
    jQuery(".list-item li.player span").text(detailData[0]["player"]);
    jQuery(".list-item li.num span").text(detailData[0]["num"]);
    jQuery(".list-item li.size2 span").text(detailData[0]["size2"]);
    if(mageAttr["double"]==1){
        jQuery(".list-item li.size2").show();
    }else{
        jQuery(".list-item li.size2").hide();
    }
}
/*
* 添加多件保存数据
* */
function saveData(){
    var html = "";
    var player = jQuery(".additemBox .player input").val();
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
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(0)").text(player);
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(1)").text(num);
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(2)").text(size);
        if(mageAttr["double"] == 1){
            jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(3)").text(size2);
            detailData[itemListNum] = {"size":size,"player":player,"num":num,"size2":size2};
        }else{
            detailData[itemListNum] = {"size":size,"player":player,"num":num,"size2":size};
        }
    }
    hideInpBox();
}
/*添加时弹出框的状态*/
function clearinpData(){
    jQuery(".madeInpBox .player input").val("");
    jQuery(".madeInpBox .num input").val("");
    jQuery(".additemBox .size li:eq(0)").addClass("active").siblings("").removeClass("active");
    jQuery(".additemBox .size2 li:eq(0)").addClass("active").siblings("").removeClass("active");
}
/*编辑时弹出框的状态*/
function addinpData(id){
    jQuery(".madeInpBox .player input").val(detailData[id]["player"]);
    jQuery(".madeInpBox .num input").val(detailData[id]["num"]);
    jQuery(".additemBox .size li").each(function(){
        if(jQuery(this).text() == detailData[id]["size"]){
            selecttab(this);
        }
     })
    jQuery(".additemBox .size2 li").each(function(){
        if(jQuery(this).text() == detailData[id]["size2"]){
            selecttab(this);
        }
    })
}

//rows agree or refuse
