$(function(){
    /*about*/
    var _texttop = 0;
    var _ah = $(".about-text").height();
    var _th = 160;
    var _t = _ah - 160;

    var _aboutTop;
    var _aboutBottom;
    $(".about-box .bottom").hover(function(){
        _aboutBottom = setInterval(aboutBottom,100);
    },function(){
        clearInterval(_aboutBottom);
    });

    $(".about-box .top").hover(function(){
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
        $(".about-text").stop().animate({"top":-_texttop},200);
    }

    function aboutBottom(){
        if(_t > 0 && _t > _texttop+20){
            _texttop = _texttop+20;
        }else if(_t > 0){
            _texttop = _t;
        }else{
            _texttop  = 0;
        }
        $(".about-text").stop().animate({"top":-_texttop},200);
    }


    /*header*/
    //var _navLiW = $("#nav .nLi").width();
    //$("#nav .head-sub").find("p").width(_navLiW);
    //$("#nav .sub-nav-i").css({"margin-left":-_navLiW/2});
    $(".subli").mouseenter(function(){
        //$(".head-sub").stop(true,true).slideDown(300);
        var _subLiLength = $(this).find(".head-sub").find("p").length;
        //$(this).find(".head-sub").width(_navLiW*_subLiLength);
        //$(this).find(".head-sub").css({"margin-left":-_navLiW});
        $(this).find(".head-sub").width(100*_subLiLength);
        //$(this).find(".head-sub").css({"margin-left":-100});
        $(".head-nav-bg").stop(true,true).slideDown(300);
        $(this).find(".head-sub").stop(true,true).slideDown(300);
        $(this).find(".head-sub-row").fadeIn(300);
    })
    $(".sub-nav p").mouseleave(function(){
        $(this).find(".sub-nav-i").stop().animate({"width":0},300);
    })
    $(".sub-nav p").mouseenter(function(){
        $(this).find(".sub-nav-i").stop().animate({"width":"100%"},300);
    })
    $(".subli").mouseleave(function(){
        $(".head-sub").stop(true,true).slideUp(300);
        $(".head-nav-bg").stop(true,true).slideUp(300);
        $(this).find(".head-sub-row").fadeOut(300);
    })

    $("#head-search .search").click(function(){
        if($(this).attr("class").indexOf("on") != -1){
            $(".head-search-bg").stop(true,true).slideUp(300);
            $(this).removeClass("on");
        }else{
            $(".head-search-bg").stop(true,true).slideDown(300);
            $(this).addClass("on");
        }
    });

    /*index*/
    /*$(".index-ban,.index-ban .banner li,.index-ban .banner li #bg,.index-ban ul").css({"height":_wH-120});
    $(".index-ban-sol").css({"margin-top":(_wH-120)*0.6});

    $(".banner > ul > li").LoadImage(true,$(".banner > ul > li"));*/

    $(".index-ban").slide({mainCell:".bd ul",effect:"fold",autoPlay:true});

    $(".index-chl-01 .b50,.index-chl-02 .b50,.index-waist .b50,.index-chl-03 .b50,.index-chl-04 .b50").css({"opacity":0});
    $(".index-chl-01").hover(function(){
        $(this).find(".top,.bottom").stop(true,true).animate({"width":0},1000);
        $(this).find(".top-cur,.bottom-cur").stop(true,true).delay(300).animate({"width":"100%"},1000);
        $(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        $(this).find(".top,.bottom").stop().animate({"width":"100%"},1000);
        $(this).find(".top-cur,.bottom-cur").stop(true,true).delay(300).animate({"width":0},1000);
        $(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    

    $(".index-chl-02").hover(function(){
        $(this).find(".fl").stop().animate({"margin-left":15},500,"linear");
        $(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        $(this).find(".fl").stop().animate({"margin-left":5},500,"linear");
        $(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    $(".index-chl-03").hover(function(){
        $(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        $(this).find(".b50").stop(true,true).animate({"opacity":0});
    });
    $(".index-chl-04").hover(function(){
        $(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        $(this).find(".b50").stop(true,true).animate({"opacity":0});
    });

    $(".index-news").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"leftLoop",autoPlay:true,vis:1});

    $(".index-waist").hover(function(){
        $(this).find("i").stop().animate({"width":0,"margin-left":0},500,function(){
            $(this).stop().animate({"width":74,"margin-left":-36},500);
        });
        $(this).find(".b50").stop(true,true).animate({"opacity":1});
    },function(){
        $(this).find(".b50").stop(true,true).animate({"opacity":0});
    })

    /*footer*/
    $(".footer-links").hover(function(){
        $(this).find(".footer-links-box").stop().slideDown(300);
    },function(){
        $(this).find(".footer-links-box").stop().slideUp(300);
    });

    $(".weixin").hover(function(){
        $(this).find(".wx-div").stop().slideDown(300);
    },function(){
        $(this).find(".wx-div").stop().slideUp(300);
    });

    $(".sina").hover(function(){
        $(this).find(".sina-div").stop().slideDown(300);
    },function(){
        $(this).find(".sina-div").stop().slideUp(300);
    });

    /*his*/
    var _mouseD = false;//判断鼠标按下
    var _mousePX = 0;//鼠标按下时的x
    var _mouseX = 0;//鼠标当前的X
    var _scPL = 0;//当前纽扣的left
    var _menuNum = 0;//menu移动个数
    var _hisbdUl = 0;//ul移动个数


    var _hisLiLength = $(".his-bg .bd li").length;//li（年份）个数
    //$(".his-bg .bd ul").width(1000*_hisLiLength);
    $(".scale-div ul").width(136*(_hisLiLength - 1));
    $(".year-div ul").width(136*_hisLiLength);
    /*$(".his-bg .bd li").css({"opacity":0});
    $(".his-bg .bd li").eq(0).css({"opacity":1});*/
    $(".his-bg .bd li").eq(0).fadeIn(100);

    $("#sc-buttom").mousedown(function(e){
        e = e || window.event;
        _mouseD = true;
        _mousePX = e.pageX;
        _scPL = $("#sc-buttom").position().left;//当前纽扣的left
    });
    $(".his-bg").mouseup(function(e){
        e = e || window.event;
        _mousePX = 0;
        _mouseX = 0;

        if(_mouseD == true){
            _scPL = $("#sc-buttom").position().left;//移动后纽扣的left
            scButtomLeft();
        }
        
        _mouseD = false;
    });

    $(".his-bg").mousemove(function(e){
        e = e || window.event;
        if(_mouseD == true){
            _mouseX = e.pageX;
            var _btnL = _mouseX - _mousePX;//鼠标移动距离

            var _moveL = _scPL + _btnL;//纽扣应该移动的left
            _moveL < 0 ? _moveL = 0 : _moveL = _moveL;
            _moveL > 816 ? _moveL = 816 : _moveL = _moveL;
            $("#sc-buttom").css({"left":_moveL});
            $(document).bind('selectstart',function(e){  // 防止页面内容被选中 IE 
                return false;
            });
            e.preventDefault();
        }
    })

    $("#sc-buttom").mousemove(function(e){
        e = e || window.event;
        $(document).bind('selectstart',function(e){  // 防止页面内容被选中 IE 
            return false;
        });
        e.preventDefault();
    })

    $(".his-bg .next").click(function(){
        /*_menuNum++;
        _menuNum > _hisLiLength - 7 ? _menuNum = _hisLiLength - 7 : _menuNum = _menuNum;
        hismenuLeft();*/
        _scPL = $("#sc-buttom").position().left;//当前纽扣的left
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

    $(".his-bg .prev").click(function(){
        /*_menuNum--;
        _menuNum < 0 ? _menuNum = 0 : _menuNum = _menuNum;
        hismenuLeft();*/
        _scPL = $("#sc-buttom").position().left;//当前纽扣的left
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
        $(".scale-div ul").stop().animate({"left":-_menuNum*136},300);
        $(".year-div ul").stop().animate({"left":-_menuNum*136},300);

        hisbdLeft(_menuNum+_hisbdUl);
    }

    function hisbdLeft(k){//历史内容切换
        //$(".his-bg .bd ul").stop().animate({"left":-1000*k},300);
        //$(".his-bg .bd ul li").eq(k).stop().animate({"opacity":1},300).siblings().stop().animate({"opacity":0},300);
        $(".his-bg .bd ul li").eq(k).stop(true,true).fadeIn(300).siblings().stop(true,true).fadeOut(300);
        $(".year-div li").removeClass("on").eq(k).addClass("on");
    }

    function scButtomLeft(){//按钮移动

            for (var i = 0; i <= _hisLiLength; i++) {
                if( _scPL >= (i*136-68) && _scPL <= (i*136+67) ){
                    _scPL = i*136;
                    _hisbdUl = i;
                    break;
                }
            };//按钮移动偏差定位

            $("#sc-buttom").stop().animate({"left":_scPL},100);
            hisbdLeft(_menuNum+_hisbdUl);
    }


    /*contact*/
    $(".sselect").hover(function(){
        $(this).find(".sselect-div").stop(true,true).slideDown(300);
    },function(){
        $(this).find(".sselect-div").stop(true,true).slideUp(300);
    })

    $(".cselect").hover(function(){
        $(this).find(".cselect-div").stop(true,true).slideDown(300);
    },function(){
        $(this).find(".cselect-div").stop(true,true).slideUp(300);
    })



    /*honor*/
    $(window).scroll(function() {//定义滚动条位置改变时触发的事件。
        _scroH=$("body").get(0).scrollHeight;//得到滚动条总长，赋给hght变量
        _scroT=$(window).scrollTop();//得到滚动条当前值，赋给top变量
    });

    /*newpro*/
    //$(".newpro-left,.newpro-right,.newpro-left .b50,.newpro-right .b50").css({"height":_wH - 64 - 200});
    $(".newpro-left,.newpro-right").hover(function(){
        $(this).find(".b50").stop().animate({"opacity":1},500);
    },function(){
        $(this).find(".b50").stop().animate({"opacity":0},500);
    });
    $(".newpro-left,.newpro-right").find(".b50").css({"opacity":0});

    /*newproList*/
    $(".newpro-list li").hover(function(){
        $(this).find(".ablock").stop().animate({"opacity":1},500);
        $(this).find(".img-view").stop().animate({"opacity":1},500);
        /*$(this).stop().animate({"width":560},500);
        $(".newpro-list ul").width($(".newpro-list ul").width()+242);
        var _newproScroLeft = $(".newpro-list-box").scrollLeft();
        $(".newpro-list-box").stop().animate({scrollLeft:_newproScroLeft+242},500);*/
    },function(){
        var _newproScroLeft = $(".newpro-list-box").scrollLeft();
        $(this).find(".ablock").stop().animate({"opacity":0},500);
        $(this).find(".img-view").stop().animate({"opacity":0},500);
        /*$(this).stop().animate({"width":318},500,function(){
            $(".newpro-list ul").width($(".newpro-list ul").width()-242);

        });
        $(".newpro-list-box").stop().animate({scrollLeft:_newproScroLeft-242},500);*/
    })
    $(".newpro-list li").find(".ablock").css({"opacity":0});
    $(".newpro-list li").find(".img-view").css({"opacity":0});

    // $("a[rel='newpropic']").fancybox({
    //     "padding":0,
    //     "overlayColor":"#000",
    //     "titleShow":false
    // });

    /*prolist*/
    $(".pro-list-div").on('mouseenter mouseleave','.pro-list li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            //$(this).find(".prolist-cur-img").hide();
            $(this).find("img").addClass("on");
        }else{
            //$(this).find(".prolist-cur-img").show();
            $(this).find("img").removeClass("on");
        }  
    })

    /*if(_wH > _bH){
        $(".pro-list-menu,.store-img").css({"height":_wH - 64 - 200});
    }else{
        $(".pro-list-menu,.store-img").css({"height":_bH - 64 - 200});
    }*/

    // var _prolistConW = _wW * 0.75;
    // var _prolistConH = _wH - 64 - 200;
    // var _prolistBodyH = ((((_prolistConW * 0.2) / 287) * 379) * 2) + 42*3;
    // if( _prolistBodyH > _prolistConH){
    //     $(".pro-list-menu-pro").css({"height":_prolistBodyH});
    // }else{
    //     $(".pro-list-menu-pro").css({"height":_prolistConH});
    // }

    /*store*/
    $(".store-menu .bd").on('mouseenter mouseleave','li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            if($(this).attr("class").indexOf("cur") == -1){
                $(this).addClass("on");
            }
            
        }else{
            $(this).removeClass("on");
        }  
    })


    /*custom*/
    $(".custom-tit").hover(function(){
        $(this).find(".b50").stop().animate({"opacity":1},500);
    },function(){
        $(this).find(".b50").stop().animate({"opacity":0},500);
    });
    $(".custom-tit").find(".b50").css({"opacity":0});

    $(".custom-list").on('mouseenter mouseleave','li',function(event){
        if(event.type == "mouseout" || event.type == "mouseleave"){
            $(this).find("img").addClass("on");
            
        }else{
            $(this).find("img").removeClass("on");
        }  
    })

    
  

  //小屏
        // if(_wH < 750){
        //     $(".his-bg .year").css({"font-size":"170px"});
        //     $(".his-bg .bd,.his-bg .bd ul,.his-bg .bd li").css({"height":500});
        //     $(".his-bg .div-text").css({"margin":"60px auto 0px"});
        // }

})

// var _scroH=0;//初始化滚动条总长
// var _scroT=0;//初始化滚动条的当前位置
// var _page = 2;//加载的页码
// var _wH = $(window).height();
// var _wW = $(window).width();

// //var _bH = $("body").height();
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
    
//      $("#loadimg").show();
//      var Html = $.ajax({
//         url : _xml + "/" + _page,
//         async: false,
//         success:function(msg){
//             $("#loadimg").hide();
//             var _str = $(msg);
//             $(".honor-list ul").append(_str);//用append方法追加内容。;
//             _scroH=$("body").get(0).scrollHeight;
//             _scroT=$("body").scrollTop();
//             var _maxP = $("#zsum").val();
//             if(_page >= _maxP){
//                 clearTimeout(interval);
//             }else{
//                 _page ++;
//             }
            
//         }
//     });
// }
// function loadMedia(){
//      $("#loadimg").show();
//      var Html = $.ajax({
//         url : _xml + "/" + _page,
//         async: false,
//         success:function(msg){
//             $("#loadimg").hide();
//             var _str = $(msg);
//             $(".media-div ul").append(_str);//用append方法追加内容。;
//             _scroH=$("body").get(0).scrollHeight;
//             _scroT=$("body").scrollTop();
//             _page ++;
//         }
//     });
// }

// function loadNews(){
//     $("#loadimg").show();
//     //var _newUrl = <?php echo "'".site_url('news/ajax')."'"; ?>; 
//      var Html = $.ajax({
//                 url : _xml + "/" + _page,
//                 async: false,
//                 success:function(msg){
//                     $("#loadimg").hide();
//                     //var _str = $(Html.responseText);
//                     var _str = $(msg);
//                     $(".news-list").append(_str).masonry('appended', _str);//用append方法追加内容。;
//                     _scroH=$("body").get(0).scrollHeight;
//                     _scroT=$("body").scrollTop();
//                     _page ++;
//                 }
//             });
// }