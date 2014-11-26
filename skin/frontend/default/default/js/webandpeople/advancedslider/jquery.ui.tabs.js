(function(a){a.ui=a.ui||{};a.fn.tabs=function(c,b){if(c&&c.constructor==Object){b=c;c=null}b=b||{};c=c&&c.constructor==Number&&--c||0;return this.each(function(){new a.ui.tabs(this,a.extend(b,{initial:c}))})};a.each(["Add","Remove","Enable","Disable","Click","Load","Href"],function(b,c){a.fn["tabs"+c]=function(){var d=arguments;return this.each(function(){var e=a.ui.tabs.getInstance(this);e[c.toLowerCase()].apply(e,d)})}});a.fn.tabsSelected=function(){var d=-1;if(this[0]){var b=a.ui.tabs.getInstance(this[0]),c=a("li",this);d=c.index(c.filter("."+b.options.selectedClass)[0])}return d>=0?++d:-1};a.ui.tabs=function(c,b){this.source=c;this.options=a.extend({initial:0,event:"click",disabled:[],cookie:null,unselected:false,unselect:b.unselected?true:false,spinner:"Loading&#8230;",cache:false,idPrefix:"ui-tabs-",ajaxOptions:{},fxSpeed:"normal",add:function(){},remove:function(){},enable:function(){},disable:function(){},click:function(){},hide:function(){},show:function(){},load:function(){},tabTemplate:'<li><a href="#{href}"><span>#{text}</span></a></li>',panelTemplate:"<div></div>",navClass:"ui-tabs-nav",selectedClass:"ui-tabs-selected",unselectClass:"ui-tabs-unselect",disabledClass:"ui-tabs-disabled",panelClass:"ui-tabs-panel",hideClass:"ui-tabs-hide",loadingClass:"ui-tabs-loading"},b);this.options.event+=".ui-tabs";this.options.cookie=a.cookie&&a.cookie.constructor==Function&&this.options.cookie;a.data(c,a.ui.tabs.INSTANCE_KEY,this);this.tabify(true)};a.ui.tabs.INSTANCE_KEY="ui_tabs_instance";a.ui.tabs.getInstance=function(b){return a.data(b,a.ui.tabs.INSTANCE_KEY)};a.extend(a.ui.tabs.prototype,{tabId:function(b){return b.title?b.title.replace(/\s/g,"_"):this.options.idPrefix+a.data(b)},tabify:function(r){this.$lis=a("li:has(a[href])",this.source);this.$tabs=this.$lis.map(function(){return a("a",this)[0]});this.$panels=a([]);var s=this,d=this.options;this.$tabs.each(function(o,n){if(n.hash&&n.hash.replace("#","")){s.$panels=s.$panels.add(n.hash)}else{if(a(n).attr("href")!="#"){a.data(n,"href",n.href);var t=s.tabId(n);n.href="#"+t;s.$panels=s.$panels.add(a("#"+t)[0]||a(d.panelTemplate).attr("id",t).addClass(d.panelClass).insertAfter(s.$panels[o-1]||s.source))}else{d.disabled.push(o+1)}}});if(r){a(this.source).hasClass(d.navClass)||a(this.source).addClass(d.navClass);this.$panels.each(function(){var i=a(this);i.hasClass(d.panelClass)||i.addClass(d.panelClass)});for(var h=0,j;j=d.disabled[h];h++){this.disable(j)}this.$tabs.each(function(t,n){if(location.hash){if(n.hash==location.hash){d.initial=t;if(a.browser.msie||a.browser.opera){var o=a(location.hash),u=o.attr("id");o.attr("id","");setTimeout(function(){o.attr("id",u)},500)}scrollTo(0,0);return false}}else{if(d.cookie){var v=parseInt(a.cookie(a.ui.tabs.INSTANCE_KEY+a.data(s.source)));if(v&&s.$tabs[v]){d.initial=v;return false}}else{if(s.$lis.eq(t).hasClass(d.selectedClass)){d.initial=t;return false}}}});var e=this.$lis.length;while(this.$lis.eq(d.initial).hasClass(d.disabledClass)&&e){d.initial=++d.initial<this.$lis.length?d.initial:0;e--}if(!e){d.unselected=d.unselect=true}this.$panels.addClass(d.hideClass);this.$lis.removeClass(d.selectedClass);if(!d.unselected){this.$panels.eq(d.initial).show().removeClass(d.hideClass);this.$lis.eq(d.initial).addClass(d.selectedClass)}var b=!d.unselected&&a.data(this.$tabs[d.initial],"href");if(b){this.load(d.initial+1,b)}if(!/^click/.test(d.event)){this.$tabs.bind("click",function(i){i.preventDefault()})}}var c={},q=d.fxShowSpeed||d.fxSpeed,p={},g=d.fxHideSpeed||d.fxSpeed;if(d.fxSlide||d.fxFade){if(d.fxSlide){c.height="show";p.height="hide"}if(d.fxFade){c.opacity="show";p.opacity="hide"}}else{if(d.fxShow){c=d.fxShow}else{c["min-width"]=0;q=1}if(d.fxHide){p=d.fxHide}else{p["min-width"]=0;g=1}}var k={display:"",overflow:"",height:""};if(!a.browser.msie){k.opacity=""}function m(n,i,o){i.animate(p,g,function(){i.addClass(d.hideClass).css(k);if(a.browser.msie&&p.opacity){i[0].style.filter=""}d.hide(n,i[0],o&&o[0]||null);if(o){l(n,o,i)}})}function l(n,o,i){if(!(d.fxSlide||d.fxFade||d.fxShow)){o.css("display","block")}o.animate(c,q,function(){o.removeClass(d.hideClass).css(k);if(a.browser.msie&&c.opacity){o[0].style.filter=""}d.show(n,o[0],i&&i[0]||null)})}function f(n,t,i,o){t.addClass(d.selectedClass).siblings().removeClass(d.selectedClass);m(n,i,o)}this.$tabs.unbind(d.event).bind(d.event,function(){var t=a(this).parents("li:eq(0)"),i=s.$panels.filter(":visible"),o=a(this.hash);if((t.hasClass(d.selectedClass)&&!d.unselect)||t.hasClass(d.disabledClass)||d.click(this,o[0],i[0])===false){this.blur();return false}if(d.cookie){a.cookie(a.ui.tabs.INSTANCE_KEY+a.data(s.source),s.$tabs.index(this),d.cookie)}if(d.unselect){if(t.hasClass(d.selectedClass)){t.removeClass(d.selectedClass);s.$panels.stop();m(this,i);this.blur();return false}else{if(!i.length){s.$panels.stop();if(a.data(this,"href")){var n=this;s.load(s.$tabs.index(this)+1,a.data(this,"href"),function(){t.addClass(d.selectedClass).addClass(d.unselectClass);l(n,o)})}else{t.addClass(d.selectedClass).addClass(d.unselectClass);
l(this,o)}this.blur();return false}}}s.$panels.stop();if(o.length){if(a.data(this,"href")){var n=this;s.load(s.$tabs.index(this)+1,a.data(this,"href"),function(){f(n,t,i,o)})}else{f(this,t,i,o)}}else{throw"jQuery UI Tabs: Mismatching fragment identifier."}if(a.browser.msie){this.blur()}return false})},add:function(d,g,b){if(d&&g){b=b||this.$tabs.length;var f=this.options,i=a(f.tabTemplate.replace(/#\{href\}/,d).replace(/#\{text\}/,g));var h=d.indexOf("#")==0?d.replace("#",""):this.tabId(a("a:first-child",i)[0]);var e=a("#"+h);e=e.length&&e||a(f.panelTemplate).attr("id",h).addClass(f.panelClass).addClass(f.hideClass);if(b>=this.$lis.length){i.appendTo(this.source);e.appendTo(this.source.parentNode)}else{i.insertBefore(this.$lis[b-1]);e.insertBefore(this.$panels[b-1])}this.tabify();if(this.$tabs.length==1){i.addClass(f.selectedClass);e.removeClass(f.hideClass);var c=a.data(this.$tabs[0],"href");if(c){this.load(b+1,c)}}f.add(this.$tabs[b],this.$panels[b])}else{throw"jQuery UI Tabs: Not enough arguments to add tab."}},remove:function(b){if(b&&b.constructor==Number){var d=this.options,e=this.$lis.eq(b-1).remove(),c=this.$panels.eq(b-1).remove();if(e.hasClass(d.selectedClass)&&this.$tabs.length>1){this.click(b+(b<this.$tabs.length?1:-1))}this.tabify();d.remove(e.end()[0],c[0])}},enable:function(b){var c=this.options,d=this.$lis.eq(b-1);d.removeClass(c.disabledClass);if(a.browser.safari){d.css("display","inline-block");setTimeout(function(){d.css("display","block")},0)}c.enable(this.$tabs[b-1],this.$panels[b-1])},disable:function(b){var c=this.options;this.$lis.eq(b-1).addClass(c.disabledClass);c.disable(this.$tabs[b-1],this.$panels[b-1])},click:function(b){this.$tabs.eq(b-1).trigger(this.options.event)},load:function(f,b,j){var k=this,c=this.options,d=this.$tabs.eq(f-1),i=d[0],g=a("span",i);if(b&&b.constructor==Function){j=b;b=null}if(b){a.data(i,"href",b)}else{b=a.data(i,"href")}if(c.spinner){a.data(i,"title",g.html());g.html("<em>"+c.spinner+"</em>")}var h=function(){k.$tabs.filter("."+c.loadingClass).each(function(){a(this).removeClass(c.loadingClass);if(c.spinner){a("span",this).html(a.data(this,"title"))}});k.xhr=null};var e=a.extend(c.ajaxOptions,{url:b,success:function(l){a(i.hash).html(l);h();if(j&&j.constructor==Function){j()}if(c.cache){a.removeData(i,"href")}c.load(k.$tabs[f-1],k.$panels[f-1])}});if(this.xhr){this.xhr.abort();h()}d.addClass(c.loadingClass);setTimeout(function(){k.xhr=a.ajax(e)},0)},href:function(b,c){a.data(this.$tabs.eq(b-1)[0],"href",c)}})})(jqAdvSlider);