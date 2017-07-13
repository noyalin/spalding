jQuery(function () {
    mageAttr=[];
    detailData=[];
    itemListNum=-1;
    delRowId="";
    jQuery(document)
        .on("click","#cusMadeBox .styleBox",function(){
            selecttab(this);
        })
        .on("click",".ullist li",function(){
            selecttab(this);
        })
        .on("click",".slideshow .aBtn",function(){
            selectpage(this);
        })
        .on("click",".madeDouble .ullist li",function(){
            var i=jQuery(this).index();
            IsSingle(i);
        })
        .on("click","#next1",function(){
            var flag=checkTextYN(".madeBoxCont1 .madePlayer input",".madeBoxCont1 .madeNum input");
            if(flag){
                selectNextcont(this);
                if(detailData.length>1){
                    madeData();
                    showCarlist();
                    jQuery(".madeBoxCont4").addClass("active").siblings("").removeClass("active");
                }
                else{
                    showsingledata();
                }
                setSuit();
            }
        })
        .on("click","#next2",function(){
            selectNextcont(this);
            showlistdata();
        })
        .on("click","#confirm2",function(){
            var flag=checkTextYN(".additemBox .player input",".additemBox .num input");
            if(flag){
                saveData();
            }
        })
        .on("click","#confirm3",function(){
            selectNextcont(this);
            showCarlist();
        })
        .on("click","#addbtn",function(){
            itemListNum=-1;
            if(detailData.length<=14){
                showInpBox();
            }else{
                alert("不能超过15条")
            }
            clearinpData();
        })
        .on("click","#backSingle,#backSingle1",function(){
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
});
function setTotal(){
    var len=detailData.length;
    var total=len*jQuery(".madeBoxTitle h2 b").text();
    jQuery(".totalbox .num").text(len);
    jQuery(".totalbox .total").text(total);
}
/*
* 判断球员名称和号码不能为空
* */
function checkTextYN(obj1,obj2){
    var player=jQuery(obj1).val();
    var num=jQuery(obj2).val();
    if(!player){
        alert("球员名称不能为空");
    }else if(!num){
        alert("球员号码不能为空")
    }else{
        return true;
    }
}

/*
* 是否显示套装
* */
function setSuit(){
    jQuery("#madeTable thead").eq(mageAttr["double"]).show().siblings("thead").hide();
    if(mageAttr["double"]==0){
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
    //rowCount++;
    if(mageAttr["double"]==0){
        html="<tr><td>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td><a href='#'  class='edit' onclick=editRow(this)>编辑</a><a href='#' class='delRow'>删除</a></td></tr>"
    }else{
        html="<tr><td>"+player+"</td><td>"+num+"</td><td>"+size+"</td><td>"+size2+"</td><td><a href='#' class='edit' onclick=editRow(this)>编辑</a><a href='#'  class='delRow'>删除</a></td></tr>";
    }
    jQuery("#madeTable tbody").append(html);
}
function tipShow(obj){
    delRowId=jQuery(obj).closest("tr").index();
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
    setTotal();
    tipHide();
}
function editRow(obj){
    var i=jQuery(obj).closest("tr").index();
    itemListNum=i;
    addinpData(itemListNum);//默认值
    showInpBox();
}
function showlistdata(){
    var html="";
    jQuery("#madeTable tbody").html("");
    jQuery.each(detailData,function(key,node){
        if(mageAttr["double"]==0){
            html+="<tr><td>"+node.player+"</td><td>"+node.num+"</td><td>"+node.size+"</td><td><a href='#' onclick=editRow(this)>编辑</a><a href='#'  class='delRow'>删除</a></td></tr>";
        }else{
            html+="<tr><td>"+node.player+"</td><td>"+node.num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td><td><a href='#' onclick=editRow(this)>编辑</a><a href='#'  class='delRow'>删除</a></td></tr>";
        }
    })
    jQuery("#madeTable tbody").append(html);
}
function showCarlist(){
    var html="";
    jQuery(".madeBoxCont4 .moreBox table tbody").html("");
    jQuery.each(detailData,function(key,node){
        if(mageAttr["double"]==0){
            html+="<tr><td>"+node.player+"</td><td>"+node.num+"</td><td>"+node.size+"</td></tr>";
        }else{
            html+="<tr><td>"+node.player+"</td><td>"+node.num+"</td><td>"+node.size+"</td><td>"+node.size2+"</td></tr>";
        }
    })
    jQuery(".madeBoxCont4 .moreBox table tbody").append(html);
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
function selectpage(obj){
    var i=jQuery(obj).index(".aBtn");
    jQuery('.slideshow ul li').eq(i).show().siblings().hide();
}
/*
*单件和套装
*/
function IsSingle(i){
    jQuery(".cusMadeShirt .viewMadebox").eq(i).show().siblings().hide();
}
/*
*判断数字
 */
function IsNum(obj) {
    jQuery(obj).value=jQuery(obj).value.replace(/\D/g,'')
}
/*
* 定制信息
* */
function madeData(){
    var color=jQuery(".madeColor li.active").attr("data");
    var size=jQuery(".madeSize li.active").text();
    var font=jQuery(".madeFont li.active").attr("data");
    var style=jQuery(".madeStyle li.active").attr("data");
    var fontColor=jQuery(".madeFontColor li.active").attr("data");
    var team=jQuery(".madeTeam input").val();
    var player=jQuery(".madePlayer input").val();
    var num=jQuery(".madeNum input").val();
    var double=jQuery(".madeDouble li.active").index();
    var size2=jQuery(".madeSize2 li.active").text();
    mageAttr={"color":color,"font":font,"style":style,"fontColor":fontColor,"team":team,"double":double}
    if(detailData && detailData.length==0){
        detailData=[{"size":size,"player":player,"num":num,"size2":size}];
    }else{
        detailData[0]={"size":size,"player":player,"num":num,"size2":size2};
    }
}
function showsingledata(){
    madeData();
    jQuery(".list-item li.color span").text(mageAttr["color"]);
    jQuery(".list-item li.size span").text(detailData[0]["size"]);
    jQuery(".list-item li.font span").text(mageAttr["font"]);
    jQuery(".list-item li.style span").text(mageAttr["style"]);
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
    var html="";
    var player=jQuery(".additemBox .player input").val();
    var num=jQuery(".additemBox .num input").val();
    var size=jQuery(".additemBox .size li.active").text();
    var size2=jQuery(".additemBox .size2 li.active").text();
    if(itemListNum==-1){//如果0就添加,否则对应的行修改
        addRow(player,num,size,size2);
        if(mageAttr["double"]==1){
            detailData.push({"size":size,"player":player,"num":num,"size2":size2});
        }else{
            detailData.push({"size":size,"player":player,"num":num,"size2":size});
        }
        setTotal();
    }else{
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(0)").text(player);
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(1)").text(num);
        jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(2)").text(size);
        if(mageAttr["double"]==1){
            jQuery("#madeTable tbody tr:eq("+itemListNum+")").find("td:eq(3)").text(size2);
            detailData[itemListNum]={"size":size,"player":player,"num":num,"size2":size2};
        }else{
            detailData[itemListNum]={"size":size,"player":player,"num":num,"size2":size};
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
        if(jQuery(this).text()==detailData[id]["size"]){
            selecttab(this);
        }
     })
    jQuery(".additemBox .size2 li").each(function(){
        if(jQuery(this).text()==detailData[id]["size2"]){
            selecttab(this);
        }
    })
}
