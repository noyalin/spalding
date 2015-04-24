/* 维度端前端网整理 http://www.weiduduan.com  */
jQuery(document).ready(function(){
	//cust_flash_s();	
//	$(".cust_flash_s ul li").eq(0).css("background","#ff9000");
	FIX_TIMER = setInterval("cust_flash()",5000)
	head_faq();
})

window.onresize=function(){head_faq();}
function head_faq(){
	
	var id = jQuery(".head_link_ico").eq(0);
	jQuery(".head_link_ico").eq(0).hover(function(){
			jQuery(".head_faq").css("top",id.offset().top+id.height()+"px")
			jQuery(".head_faq").css("left",960 - jQuery(".head_faq").width()+"px")
			jQuery(".head_faq").show();
		},function(){
			jQuery(".head_faq").hover(function(){
				jQuery(".head_faq").show();
				},function(){
				jQuery(".head_faq").hide();
				return;
				})
			jQuery(".head_faq").hide();
			})
}

var po_now=0;
var cust_step=10000;
var FIX_TIMER;
function cust_flash(){
	if(po_now == jQuery(".kuzhan_sele").length-1){po_now=0;}
	else{po_now++;}
	cust_flash_scroll(po_now);
	
}
function cust_flash_scroll(n){
	jQuery(".fix_flash").stop(true,false).animate({left:0-n*jQuery(".fix_flash li").width()+"px"},500)
	jQuery(".kuzhan_sele").removeClass("kuzhan_over")
	jQuery(".kuzhan_sele").eq(n).addClass("kuzhan_over")
	//var theleft = jQuery(".kuzhan_sele").eq(n).offset().left - 0.5*jQuery("body").width() + 550 - 48 + 5;
	var theleft =  122 + 26*n ;
	var str = "<img src='"+jQuery(".fix_flash").find("li").eq(n).attr("imglink")+"' style='margin-left:-27px;' />"
	jQuery(".kuzhan_flash_v div").html(str)
	jQuery(".kuzhan_flash_v").stop(true,true).animate({"left":theleft+"px"},500)
	
	}
function cust_flash_s(){
	jQuery(".kuzhan_sele").click(function(){
			po_now=parseInt(jQuery(this).attr("number"));
			cust_flash_scroll(po_now);
		})
}
function FIX_scroll(obj){
	clearTimeout(FIX_TIMER);
	cust_flash_scroll(jQuery(obj).attr("number"));
	FIX_TIMER = setInterval("cust_flash()",4000)
}
