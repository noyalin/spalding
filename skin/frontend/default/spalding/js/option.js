var Opt = {
   // pos: 1,//当前编辑区域 1:P1 , 2:P2
    init: function () {
        //Opt.pos = jQuery('#options_pos').val();
        //if (Opt.pos == null) {
        //    Opt.pos = 1;
        //}
        //Opt.P1.type = jQuery('#options_type_p1').val();
        //Opt.P1.content1 = jQuery('#options_content1_p1').val();
        //Opt.P1.content2 = jQuery('#options_content2_p1').val();
        //Opt.P2.type = jQuery('#options_type_p2').val();
        //Opt.P2.content1 = jQuery('#options_content1_p2').val();
        //Opt.P2.content2 = jQuery('#options_content2_p2').val();
    },

    completeImg: function (position) {
        var originalImg = document.getElementById('avatar').src;
        var url = document.getElementById('complete').value;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {cut_pos: getcutpos(), originalImg: originalImg, position: position, type: 1},
            success: function () {
                window.location.reload();
            }
        });
    },

    completeTxt: function (position) {
        var url = document.getElementById('complete').value;
        var text;
        if (position == 1) {
            text = document.getElementById("textInput_1").value;
        } else if (position == 2) {
            text = document.getElementById("textInput_2").value;
        }
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {text: text, size: 30, position: position, type: 2},
            success: function () {
                window.location.reload();
            }
        });
    },

    preview: function () {
        var url = document.getElementById('preview').value;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {},
            success: function () {
                window.location.reload();
            }
        });
    },

    reset: function (position) {
        var url = document.getElementById('reset').value;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {position: position},
            success: function () {
                window.location.reload();
            }
        });
    }
};

//Opt.P1 = {
//    type: null,//编辑类型  1:图片 2:文字
//    content1: null,
//    content2: null
//};
//
//Opt.P2 = {
//    type: null,//编辑类型  1:图片 2:文字
//    content1: null,
//    content2: null
//};

jQuery(document).ready(function () {
    Opt.init();
});