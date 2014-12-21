company = "sneakerhead";
sku = 1;
jQuery(document).ready(function () {
//    jQuery("#detailTabWrap").tabs();
    jQuery("#detailTab-2").show();
    jQuery(".prListWrapper").each(function () {
        if (jQuery(this).index() > 3) {
            var e = jQuery(this).remove();
            e.appendTo("#detailReviewMore");
            jQuery("#prProductReviewsDisplay").show()
        }
    });
    jQuery("#detailAvailColorMore").hide();
    if (!jQuery("#detailAvailColorMore").html()) {
        jQuery("#detailReadMoreColors").hide()
    }
    jQuery("#detailReadMoreColors").click(function () {
        jQuery("#detailAvailColorMore").fadeToggle("fast", "linear");
        if (jQuery("#detailReadMoreColors a:first").text() == "See more +") {
            jQuery("#detailReadMoreColors a:first").text("Less -")
        } else {
            jQuery("#detailReadMoreColors a:first").text("See more +")
        }
        return false
    });
    jQuery("#detailReviewMore").hide();
    if (!jQuery("#detailReviewMore").html()) {
        jQuery("#detailReadMoreReviews").hide()
    }
    jQuery("#detailReadMoreReviews").click(function () {
        jQuery("#detailReviewMore").fadeToggle("fast", "linear");
        if (jQuery("#detailReadMoreReviews a:first").text() == "See more +") {
            jQuery("#detailReadMoreReviews a:first").text("Less -")
        } else {
            jQuery("#detailReadMoreReviews a:first").text("See more +")
        }
        return false
    });
    var e = 92;
    var t = jQuery(".detailImageReel img").size();
    var n = e * t;
    var r = 0;
    jQuery(".detailImageReel").css({width: n});
    rotate = function () {
        var t = r;
        var n = t * e;
        jQuery(".detailImageReel").animate({left: -n}, 500)
    };
    jQuery(".detailArrowPrevBtn").click(function () {
        if (r > 0) {
            r--;
            rotate()
        }
        if (r == 0) {
            jQuery(".detailArrowPrevBtn").addClass("detailArrowPrevBtnOff");
            jQuery(".detailArrowPrevBtnOff").removeClass("detailArrowPrevBtn");
            jQuery(".detailArrowNextBtnOff").addClass("detailArrowNextBtn");
            jQuery(".detailArrowNextBtn").removeClass("detailArrowNextBtnOff")
        }
        return false
    });
    jQuery(".detailArrowNextBtn").click(function () {
        if (r < t - 4) {
            r++;
            rotate()
        }
        if (r == t - 4) {
            jQuery(".detailArrowNextBtn").addClass("detailArrowNextBtnOff");
            jQuery(".detailArrowNextBtnOff").removeClass("detailArrowNextBtn");
            jQuery(".detailArrowPrevBtnOff").addClass("detailArrowPrevBtn");
            jQuery(".detailArrowPrevBtn").removeClass("detailArrowPrevBtnOff")
        }
        return false
    });
    jQuery("#detailProductGallery div img").hover(function () {
        originSrc = jQuery("#mainImage").attr("data-zoom-image");
        smallSrc = jQuery(this).attr("src");
        lastSmallSrc =  smallSrc.substring(smallSrc.length-2,smallSrc.length);
        originSrcFirstPart = originSrc.substring(0,originSrc.length-6);
        originSrcSecondPart = lastSmallSrc + '.jpg';
        originSrcNew = originSrcFirstPart + originSrcSecondPart;
        jQuery("#mainImage").attr("data-zoom-image",originSrcNew);
        jQuery("#mainImage").attr("src", jQuery(this).attr("src").replace("spalding50px", "spalding330px").replace("50x50","330x330").replace("image50","image330"))
    });
    var i = 0;
    imgSwap = [];
    jQuery("#detailProductGallery div img").each(function () {
        var e = this.src.replace("viewer_thumbnail_template?&$viewer_thumbnail_image_preset", "detail-big-image?&$detail_big_image");
        imgSwap.push(e);
        i++
    });
    if (i > 4) {
        jQuery(".detailImageReelWindow").css({top: -34});
        jQuery(".detailImageReelWindow").show();
        jQuery(".detailArrowPrev").show();
        jQuery(".detailArrowNext").show()
    } else {
        jQuery(".detailImageReelWindow").show()
    }
    if (r == 0) {
        jQuery(".detailArrowPrevBtn").addClass("detailArrowPrevBtnOff");
        jQuery(".detailArrowPrevBtnOff").removeClass("detailArrowPrevBtn")
    }
    jQuery.preLoadImages(imgSwap);
    jQuery("a[rel^='productGallery']").sneakerheadLightBox();
    jQuery("a[rel^='detailGetRewarded']").sneakerheadLightBox({default_width: 400, default_height: 400, theme: "sh_dialog"});
    jQuery("a[rel^='cartValidation']").sneakerheadLightBox({default_width: 400, default_height: 100, modal: true, theme: "sh_dialog"})
});
(function (e) {
    e.preLoadImages = function (e) {
        if (!(e instanceof Array)) {
            var e = [e]
        }
        var t = [];
        var n;
        for (var r = 0; r < e.length; r++) {
            var i = document.createElement("img");
            i.src = e[r];
            t.push(i)
        }
    }
})(jQuery)