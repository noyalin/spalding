jQuery(function(){
    /*about*/
    var _texttop = 0;
    var _ah = jQuery(".about-text").height();
    var _th = 160;
    var _t = _ah - 160;

    var _aboutTop;
    var _aboutBottom;
    jQuery(".about-box .bottom").hover(function(){
        _aboutBottom = setInterval(aboutBottom,100);
    },function(){
        clearInterval(_aboutBottom);
    });

    jQuery(".about-box .top").hover(function(){
        _aboutTop = setInterval(aboutTop,100);
    },function(){
        clearInterval(_aboutTop);
    });

    function aboutTop(){
        if(_texttop-20 > 0 ){
            _texttop = _texttop-20;
        }else{
            _texttop  = 0;
        }
        jQuery(".about-text").stop().animate({"top":-_texttop},200);
    }

    function aboutBottom(){
        if(_t > 0 && _t > _texttop+20){
            _texttop = _texttop+20;
        }else if(_t > 0){
            _texttop = _t;
        }else{
            _texttop  = 0;
        }
        jQuery(".about-text").stop().animate({"top":-_texttop},200);
    }


    /*header*/
    //var _navLiW = jQuery("#nav .nLi").width();
    //jQuery("#nav .head-sub").find("p").width(_navLiW);
    //jQuery("#nav .sub-nav-i").css({"margin-left":-_navLiW/2});
    jQuery(".subli").mouseenter(function(){
        //jQuery(".head-sub").stop(true,true).slideDown(300);
        var _subLiLength = jQuery(this).find(".head-sub").find("p").length;
        //jQuery(this).find(".head-sub").width(_navLiW*_subLiLength);
        //jQuery(this).find(".head-sub").css({"margin-left":-_navLiW});
        jQuery(this).find(".head-sub").width(100*_subLiLength);
        //jQuery(this).find(".head-sub").css({"margin-left":-100});
        jQuery(".head-nav-bg").stop(true,true).slideDown(300);
        jQuery(this).find(".head-sub").stop(true,true).slideDown(300);
        jQuery(this).find(".head-sub-row").fadeIn(300);
    })
    jQuery(".sub-nav p").mouseleave(function(){
        jQuery(this).find(".sub-nav-i").stop().animate({"width":0},300);
    })
    jQuery(".sub-nav p").mouseenter(function(){
        jQuery(this).find(".sub-nav-i").stop().animate({"width":"100%"},300);
    })
    jQuery(".subli").mouseleave(function(){
        jQuery(".head-sub").stop(true,true).slideUp(300);
        jQuery(".head-nav-bg").stop(true,true).slideUp(300);
        jQuery(this).find(".head-sub-row").fadeOut(300);
    })

    jQuery("#head-search .search").click(function(){
        if(jQuery(this).attr("class").indexOf("on") != -1){
            jQuery(".head-search-bg").stop(true,true).slideUp(300);
            jQuery(this).removeClass("on");
        }else{
            jQuery(".head-search-bg").stop(true,true).slideDown(300);
            jQuery(this).addClass("on");
        }
    });

    /*index*/
    /*jQuery(".index-ban,.index-ban .banner li,.index-ban .banner li #bg,.index-ban ul").css({"height":_wH-120});
    jQuery(".index-ban-sol").css({"margin-top":(_wH-120)*0.6});

    jQuery(".banner > ul > li").LoadImage(true,jQuery(".banner > ul > li"));*/

    jQuery(".index-ban").slide({mainCell:".bd ul",effect:"fold",autoPlay:true});

    jQuery(".index-chl-01 .b50,.index-chl-02 .b50,.index-waist .b50,.index-chl-03 .b50,.index-chl-04 .b50").css({"opacity":0});
    jQuery(".index-chl-01").hover(function(){
        jQuery(this).find(".top,.bottom").stop(true,true).animate({"width":0},1000);
        jQuery(this).find(".top-cur,.bottom-cur").stop(true,true).delay(300).animate({"width":"100%"},1000);
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        jQuery(this).find(".top,.bottom").stop().animate({"width":"100%"},1000);
        jQuery(this).find(".top-cur,.bottom-cur").stop(true,true).delay(300).animate({"width":0},1000);
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    

    jQuery(".index-chl-02").hover(function(){
        jQuery(this).find(".fl").stop().animate({"margin-left":15},500,"linear");
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        jQuery(this).find(".fl").stop().animate({"margin-left":5},500,"linear");
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    jQuery(".index-chl-03").hover(function(){
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":0});
    });
    jQuery(".index-chl-04").hover(function(){
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    jQuery(".index-news").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"leftLoop",autoPlay:true,vis:1});

    jQuery(".index-waist").hover(function(){
        jQuery(this).find("i").stop().animate({"width":0,"margin-left":0},500,function(){
            jQuery(this).stop().animate({"width":74,"margin-left":-36},500);
        });
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        jQuery(this).find(".b50").stop(true,true).animate({"opacity":0});
    })

    /*footer*/
    jQuery(".footer-links").hover(function(){
        jQuery(this).find(".footer-links-box").stop().slideDown(300);
    },function(){
        jQuery(this).find(".footer-links-box").stop().slideUp(300);
    });

    jQuery(".weixin").hover(function(){
        jQuery(this).find(".wx-div").stop().slideDown(300);
    },function(){
        jQuery(this).find(".wx-div").stop().slideUp(300);
    });

    jQuery(".sina").hover(function(){
        jQuery(this).find(".sina-div").stop().slideDown(300);
    },function(){
        jQuery(this).find(".sina-div").stop().slideUp(300);
    });

    /*his*/
    var _mouseD = false;//判断鼠标按下
    var _mousePX = 0;//鼠标按下时的x
    var _mouseX = 0;//鼠标当前的X
    var _scPL = 0;//当前纽扣的left
    var _menuNum = 0;//menu移动个数
    var _hisbdUl = 0;//ul移动个数


    var _hisLiLength = jQuery(".his-bg .bd li").length;//li（年份）个数
    //jQuery(".his-bg .bd ul").width(1000*_hisLiLength);
    jQuery(".scale-div ul").width(136*(_hisLiLength - 1));
    jQuery(".year-div ul").width(136*_hisLiLength);
    /*jQuery(".his-bg .bd li").css({"opacity":0});
    jQuery(".his-bg .bd li").eq(0).css({"opacity":1});*/
    jQuery(".his-bg .bd li").eq(0).fadeIn(100);

    jQuery("#sc-buttom").mousedown(function(e){
        e = e || window.event;
        _mouseD = true;
        _mousePX = e.pageX;
        _scPL = jQuery("#sc-buttom").position().left;//当前纽扣的left
    });
    jQuery(".his-bg").mouseup(function(e){
        e = e || window.event;
        _mousePX = 0;
        _mouseX = 0;

        if(_mouseD == true){
            _scPL = jQuery("#sc-buttom").position().left;//移动后纽扣的left
            scButtomLeft();
        }
        
        _mouseD = false;
    });

    jQuery(".his-bg").mousemove(function(e){
        e = e || window.event;
        if(_mouseD == true){
            _mouseX = e.pageX;
            var _btnL = _mouseX - _mousePX;//鼠标移动距离

            var _moveL = _scPL + _btnL;//纽扣应该移动的left
            _moveL < 0 ? _moveL = 0 : _moveL = _moveL;
            _moveL > 816 ? _moveL = 816 : _moveL = _moveL;
            jQuery("#sc-buttom").css({"left":_moveL});
            jQuery(document).bind('selectstart',function(e){  // 防止页面内容被选中 IE 
                return false;
            });
            e.preventDefault();
        }
    })

    jQuery("#sc-buttom").mousemove(function(e){
        e = e || window.event;
        jQuery(document).bind('selectstart',function(e){  // 防止页面内容被选中 IE 
            return false;
        });
        e.preventDefault();
    })

    jQuery(".his-bg .next").click(function(){
        /*_menuNum++;
        _menuNum > _hisLiLength - 7 ? _menuNum = _hisLiLength - 7 : _menuNum = _menuNum;
        hismenuLeft();*/
        _scPL = jQuery("#sc-buttom").position().left;//当前纽扣的left
        _scPL = _scPL + 136;//纽扣应该移动的left
        if(_scPL > 816){
            _menuNum++;
            if(_menuNum > _hisLiLength - 7 ){
                _menuNum = 0;
                _scPL = 0;
            }else{
                _menuNum = _menuNum;
                _scPL = 816;
            }
        }else{
            _scPL = _scPL ;
        }
        hismenuLeft();
        scButtomLeft();
    });

    jQuery(".his-bg .prev").click(function(){
        /*_menuNum--;
        _menuNum < 0 ? _menuNum = 0 : _menuNum = _menuNum;
        hismenuLeft();*/
        _scPL = jQuery("#sc-buttom").position().left;//当前纽扣的left
        _scPL = _scPL - 136;//纽扣应该移动的left
        if(_scPL < 0){
            _menuNum--;
            if(_menuNum < 0){
                _menuNum = _hisLiLength - 7;
                _scPL = 816;
            }else{
                _menuNum = _menuNum;
                _scPL = 0;
            }
        }else{
            _scPL = _scPL;
        }
        hismenuLeft();
        scButtomLeft();

    });

    function hismenuLeft(){//menu切换
        jQuery(".scale-div ul").stop().animate({"left":-_menuNum*136},300);
        jQuery(".year-div ul").stop().animate({"left":-_menuNum*136},300);

        hisbdLeft(_menuNum+_hisbdUl);
    }

    function hisbdLeft(k){//历史内容切换
        //jQuery(".his-bg .bd ul").stop().animate({"left":-1000*k},300);
        //jQuery(".his-bg .bd ul li").eq(k).stop().animate({"opacity":1},300).siblings().stop().animate({"opacity":0},300);
        jQuery(".his-bg .bd ul li").eq(k).stop(true,true).fadeIn(300).siblings().stop(true,true).fadeOut(300);
        jQuery(".year-div li").removeClass("on").eq(k).addClass("on");
    }

    function scButtomLeft(){//按钮移动

            for (var i = 0; i <= _hisLiLength; i++) {
                if( _scPL >= (i*136-68) && _scPL <= (i*136+67) ){
                    _scPL = i*136;
                    _hisbdUl = i;
                    break;
                }
            };//按钮移动偏差定位

            jQuery("#sc-buttom").stop().animate({"left":_scPL},100);
            hisbdLeft(_menuNum+_hisbdUl);
    }


    /*contact*/
    jQuery(".sselect").hover(function(){
        jQuery(this).find(".sselect-div").stop(true,true).slideDown(300);
    },function(){
        jQuery(this).find(".sselect-div").stop(true,true).slideUp(300);
    })

    jQuery(".cselect").hover(function(){
        jQuery(this).find(".cselect-div").stop(true,true).slideDown(300);
    },function(){
        jQuery(this).find(".cselect-div").stop(true,true).slideUp(300);
    })



    /*honor*/
    jQuery(window).scroll(function() {//定义滚动条位置改变时触发的事件。
        _scroH=jQuery("body").get(0).scrollHeight;//得到滚动条总长，赋给hght变量
        _scroT=jQuery(window).scrollTop();//得到滚动条当前值，赋给top变量
    });

    /*newpro*/
    //jQuery(".newpro-left,.newpro-right,.newpro-left .b50,.newpro-right .b50").css({"height":_wH - 64 - 200});
    jQuery(".newpro-left,.newpro-right").hover(function(){
        jQuery(this).find(".b50").stop().animate({"opacity":1},500);
    },function(){
        jQuery(this).find(".b50").stop().animate({"opacity":0},500);
    });
    jQuery(".newpro-left,.newpro-right").find(".b50").css({"opacity":0});

    /*newproList*/
    jQuery(".newpro-list li").hover(function(){
        jQuery(this).find(".ablock").stop().animate({"opacity":1},500);
        jQuery(this).find(".img-view").stop().animate({"opacity":1},500);
        /*jQuery(this).stop().animate({"width":560},500);
        jQuery(".newpro-list ul").width(jQuery(".newpro-list ul").width()+242);
        var _newproScroLeft = jQuery(".newpro-list-box").scrollLeft();
        jQuery(".newpro-list-box").stop().animate({scrollLeft:_newproScroLeft+242},500);*/
    },function(){
        var _newproScroLeft = jQuery(".newpro-list-box").scrollLeft();
        jQuery(this).find(".ablock").stop().animate({"opacity":0},500);
        jQuery(this).find(".img-view").stop().animate({"opacity":0},500);
        /*jQuery(this).stop().animate({"width":318},500,function(){
            jQuery(".newpro-list ul").width(jQuery(".newpro-list ul").width()-242);

        });
        jQuery(".newpro-list-box").stop().animate({scrollLeft:_newproScroLeft-242},500);*/
    })
    jQuery(".newpro-list li").find(".ablock").css({"opacity":0});
    jQuery(".newpro-list li").find(".img-view").css({"opacity":0});

    // jQuery("a[rel='newpropic']").fancybox({
    //     "padding":0,
    //     "overlayColor":"#000",
    //     "titleShow":false
    // });

    /*prolist*/
    jQuery(".pro-list-div").on('mouseenter mouseleave','.pro-list li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            //jQuery(this).find(".prolist-cur-img").hide();
            jQuery(this).find("img").addClass("on");
        }else{
            //jQuery(this).find(".prolist-cur-img").show();
            jQuery(this).find("img").removeClass("on");
        }  
    })

    /*if(_wH > _bH){
        jQuery(".pro-list-menu,.store-img").css({"height":_wH - 64 - 200});
    }else{
        jQuery(".pro-list-menu,.store-img").css({"height":_bH - 64 - 200});
    }*/

    // var _prolistConW = _wW * 0.75;
    // var _prolistConH = _wH - 64 - 200;
    // var _prolistBodyH = ((((_prolistConW * 0.2) / 287) * 379) * 2) + 42*3;
    // if( _prolistBodyH > _prolistConH){
    //     jQuery(".pro-list-menu-pro").css({"height":_prolistBodyH});
    // }else{
    //     jQuery(".pro-list-menu-pro").css({"height":_prolistConH});
    // }

    /*store*/
    jQuery(".store-menu .bd").on('mouseenter mouseleave','li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            if(jQuery(this).attr("class").indexOf("cur") == -1){
                jQuery(this).addClass("on");
            }
            
        }else{
            jQuery(this).removeClass("on");
        }  
    })


    /*custom*/
    jQuery(".custom-tit").hover(function(){
        jQuery(this).find(".b50").stop().animate({"opacity":1},500);
    },function(){
        jQuery(this).find(".b50").stop().animate({"opacity":0},500);
    });
    jQuery(".custom-tit").find(".b50").css({"opacity":0});

    jQuery(".custom-list").on('mouseenter mouseleave','li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            jQuery(this).find("img").addClass("on");
            
        }else{
            jQuery(this).find("img").removeClass("on");
        }  
    })

    
  

  //小屏
        // if(_wH < 750){
        //     jQuery(".his-bg .year").css({"font-size":"170px"});
        //     jQuery(".his-bg .bd,.his-bg .bd ul,.his-bg .bd li").css({"height":500});
        //     jQuery(".his-bg .div-text").css({"margin":"60px auto 0px"});
        // }

})

// var _scroH=0;//初始化滚动条总长
// var _scroT=0;//初始化滚动条的当前位置
// var _page = 2;//加载的页码
// var _wH = jQuery(window).height();
// var _wW = jQuery(window).width();

// //var _bH = jQuery("body").height();
// var interval;
// interval = setInterval("loadAjax();",1000);//每隔1秒钟调用一次cando函数来判断当前滚动条位置。
// function loadAjax(){
//     if(_scroN == "honor"){
//         if(_scroT + _wH >_scroH - 50){//判断滚动条都滚动到底部 - 50
//             loadHonor();
//         }
//     }else if(_scroN == "media"){
//         if(_scroT + _wH >_scroH - 50){//判断滚动条都滚动到底部 - 50
//             loadMedia();
//         }
//     }else if(_scroN == "news"){
//         if(_scroT + _wH >_scroH - 50){//判断滚动条都滚动到底部 - 50
//             loadNews();
//         }
//     }
// }
// function loadHonor(){
    
//      jQuery("#loadimg").show();
//      var Html = $.ajax({
//         url : _xml + "/" + _page,
//         async: false,
//         success:function(msg){
//             jQuery("#loadimg").hide();
//             var _str = jQuery(msg);
//             jQuery(".honor-list ul").append(_str);//用append方法追加内容。;
//             _scroH=jQuery("body").get(0).scrollHeight;
//             _scroT=jQuery("body").scrollTop();
//             var _maxP = jQuery("#zsum").val();
//             if(_page >= _maxP){
//                 clearTimeout(interval);
//             }else{
//                 _page ++;
//             }
            
//         }
//     });
// }
// function loadMedia(){
//      jQuery("#loadimg").show();
//      var Html = $.ajax({
//         url : _xml + "/" + _page,
//         async: false,
//         success:function(msg){
//             jQuery("#loadimg").hide();
//             var _str = jQuery(msg);
//             jQuery(".media-div ul").append(_str);//用append方法追加内容。;
//             _scroH=jQuery("body").get(0).scrollHeight;
//             _scroT=jQuery("body").scrollTop();
//             _page ++;
//         }
//     });
// }

// function loadNews(){
//     jQuery("#loadimg").show();
//     //var _newUrl = <?php echo "'".site_url('news/ajax')."'"; ?>; 
//      var Html = $.ajax({
//                 url : _xml + "/" + _page,
//                 async: false,
//                 success:function(msg){
//                     jQuery("#loadimg").hide();
//                     //var _str = jQuery(Html.responseText);
//                     var _str = jQuery(msg);
//                     jQuery(".news-list").append(_str).masonry('appended', _str);//用append方法追加内容。;
//                     _scroH=jQuery("body").get(0).scrollHeight;
//                     _scroT=jQuery("body").scrollTop();
//                     _page ++;
//                 }
//             });
// }