// JavaScript Document
var aName_main = [
			{ "name": " - 首页", "subNav": [], "index": "0", "clsName": "on", "conName": ".side_nav" },
			{ "name": " - 关于斯伯丁", "subNav": [], "index": "1", "clsName": "on", "conName": ".side_nav" },
			{ "name": " - 斯伯丁商城", "subNav": [], "index": "2", "clsName": "on", "conName": ".side_nav" },
            { "name": " - 产品科技", "subNav": [], "index": "3", "clsName": "on", "conName": ".side_nav" },
			{ "name": " - 新闻与媒体", "subNav": [], "index": "4", "clsName": "on", "conName": ".side_nav" }
		];
var c_title = jQuery('TITLE').html();
function navGPS(navJson, titleStr) {
    for (var i = 0; i < navJson.length; ) {
        if (titleStr.indexOf(navJson[i].name) != -1) {
            indexNum = navJson[i].index;
            conName = navJson[i].conName;
            clsNameStr = navJson[i].clsName == '' ? 'current' : navJson[i].clsName;
            jQuery(conName).children('li').eq(indexNum).addClass(clsNameStr);
            jQuery('.lower_wrap .main').children('.lower_list').eq(indexNum).show(); //二级菜单显示
        }
        i++;
    }
}
navGPS(aName_main, c_title)




jQuery(function () {

    var index = jQuery('.menu_wrap ul.side_nav>li').index(jQuery(".on"));

    jQuery('.lower_wrap .main').children('.lower_list').stop().hide().eq(index).show();

    //导航效果
    jQuery('.menu_wrap ul.side_nav>li').each(function (i) {

        jQuery(this).hover(function () {
            jQuery('.menu_wrap ul.side_nav>li').removeClass('on').eq(i).addClass('on');
            jQuery('.lower_wrap .main').children('.lower_list').stop().hide().eq(i).show();
            //alert(indexNum)
        })
    })
    jQuery('.menu_all').hover(function () {
    }, function () {
        jQuery('.lower_wrap .main').children('.lower_list').stop().hide().eq(indexNum).show();
        jQuery('.menu_wrap ul.side_nav>li').removeClass('on').eq(indexNum).addClass('on');
        //alert(indexNum)
    })




    //AJAX加载动画
    if (jQuery('#loading').length > 0) {
        jQuery("#loading").ajaxStart(function () {
            jQuery(this).show();
        });
        jQuery("#loading").ajaxStop(function () {
            jQuery(this).hide();
        });
    }


    


});  //end