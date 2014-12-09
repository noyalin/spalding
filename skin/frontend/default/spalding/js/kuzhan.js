/* 维度端前端网整理 http://www.weiduduan.com  */
$(document).ready(function(){		
	//cust_flash_s();	
	$(".cust_flash_s ul li").eq(0).css("background","#ff9000");
	FIX_TIMER = setInterval("cust_flash()",3000)
	head_faq();
})

window.onresize=function(){head_faq();}
function head_faq(){
	
	var id = $(".head_link_ico").eq(0);
	$(".head_link_ico").eq(0).hover(function(){
			$(".head_faq").css("top",id.offset().top+id.height()+"px")
			$(".head_faq").css("left",960 - $(".head_faq").width()+"px")
			$(".head_faq").show();
		},function(){
			$(".head_faq").hover(function(){
				$(".head_faq").show();
				},function(){
				$(".head_faq").hide();
				return;
				})
			$(".head_faq").hide();
			})
}

var po_now=0;
var cust_step=10000;
var FIX_TIMER;
function cust_flash(){
	if(po_now == $(".kuzhan_sele").length-1){po_now=0;}
	else{po_now++;}
	cust_flash_scroll(po_now);
	
}
function cust_flash_scroll(n){
	$(".fix_flash").stop(true,false).animate({left:0-n*$(".fix_flash li").width()+"px"},500)
	$(".kuzhan_sele").removeClass("kuzhan_over")
	$(".kuzhan_sele").eq(n).addClass("kuzhan_over")
	//var theleft = $(".kuzhan_sele").eq(n).offset().left - 0.5*$("body").width() + 550 - 48 + 5;
	var theleft =  122 + 26*n ;
	var str = "<img src='"+$(".fix_flash").find("li").eq(n).attr("imglink")+"' style='margin-left:-27px;' />"
	$(".kuzhan_flash_v div").html(str)
	$(".kuzhan_flash_v").stop(true,true).animate({"left":theleft+"px"},500)
	
	}
function cust_flash_s(){
	$(".kuzhan_sele").click(function(){
			po_now=parseInt($(this).attr("number"));
			cust_flash_scroll(po_now);
		})
}
function FIX_scroll(obj){
	clearTimeout(FIX_TIMER);
	cust_flash_scroll($(obj).attr("number"));
	FIX_TIMER = setInterval("cust_flash()",4000)
}
