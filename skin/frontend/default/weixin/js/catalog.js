jQuery(window).ready(function(){jQuery(".single-view").click(function(){setCookie("gridView",0);if(jQuery(".single-view").hasClass("selected"))return true;var currentlyVisibleProduct;jQuery(":visible").filter(".multi-view-box").each(function(){if(isScrolledIntoView(jQuery(this))===true){currentlyVisibleProduct=jQuery(this).attr("id");return false}});jQuery(".multi-view-wrapper").toggleClass("grid",false);jQuery(".multi-view-img").toggleClass("grid",false);jQuery(".multi-view-box").toggleClass("grid",false);jQuery(".single-view").toggleClass("selected",true);jQuery(".multi-view").toggleClass("selected",false);if(jQuery(window).scrollTop()>400&&typeof currentlyVisibleProduct!=="undefined")window.scrollTo(0,jQuery("#"+currentlyVisibleProduct).offset().top+185)});jQuery(".multi-view").click(function(){setCookie("gridView",1);if(jQuery(".multi-view").hasClass("selected"))return true;var currentlyVisibleProduct;jQuery(":visible").filter(".multi-view-box").each(function(){if(isScrolledIntoView(jQuery(this))===true){currentlyVisibleProduct=jQuery(this).attr("id");return false}});jQuery(".multi-view-wrapper").toggleClass("grid",true);jQuery(".multi-view-img").toggleClass("grid",true);jQuery(".multi-view-box").toggleClass("grid",true);jQuery(".multi-view").toggleClass("selected",true);jQuery(".single-view").toggleClass("selected",false);if(jQuery(window).scrollTop()>400&&typeof currentlyVisibleProduct!=="undefined")window.scrollTo(0,jQuery("#"+currentlyVisibleProduct).offset().top-150)});function isScrolledIntoView(elem){var docViewTop=jQuery(window).scrollTop();var docViewBottom=docViewTop+jQuery(window).height();var elemTop=jQuery(elem).offset().top;var elemBottom=elemTop+jQuery(elem).height();return elemBottom<=docViewBottom&&elemTop>=docViewTop}function fixDiv(){if(jQuery(window).scrollTop()>jQuery(".multi-view-wrapper").offset().top+400)jQuery(".view-btns .back").fadeIn();else jQuery(".view-btns .back").fadeOut()}function setGridView(){var gridView=getCookie("gridView");if(typeof mobilePreference!=="undefined"&&gridView==="0")jQuery(".single-view").click();else if(typeof mobilePreference!=="undefined"&&gridView==="1")jQuery(".multi-view").click();else jQuery(".multi-view").click()}jQuery(window).scroll(fixDiv);jQuery(window).resize(fixDiv);fixDiv();setGridView();jQuery(".view-btns .back").click(function(){scrollToElement(".multi-view-wrapper",125)})});