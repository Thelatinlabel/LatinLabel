require([
	'jquery',
	'waypoints'
], function(jQuery){
	(function($) {
		$.fn.appear = function(fn, options) {

			var settings = $.extend({

				//arbitrary data to pass to fn
				data: undefined,

				//call fn only on the first appear?
				one: true,

				// X & Y accuracy
				accX: 0,
				accY: 0

			}, options);

			return this.each(function() {

				var t = $(this);

				//whether the element is currently visible
				t.appeared = false;

				if (!fn) {

					//trigger the custom event
					t.trigger('appear', settings.data);
					return;
				}

				var w = $(window);

				//fires the appear event when appropriate
				var check = function() {

					//is the element hidden?
					if (!t.is(':visible')) {

						//it became hidden
						t.appeared = false;
						return;
					}

					//is the element inside the visible window?
					var a = w.scrollLeft();
					var b = w.scrollTop();
					var o = t.offset();
					var x = o.left;
					var y = o.top;

					var ax = settings.accX;
					var ay = settings.accY;
					var th = t.height();
					var wh = w.height();
					var tw = t.width();
					var ww = w.width();

					if (y + th + ay >= b &&
						y <= b + wh + ay &&
						x + tw + ax >= a &&
						x <= a + ww + ax) {

						//trigger the custom event
						if (!t.appeared) t.trigger('appear', settings.data);

					} else {

						//it scrolled out of view
						t.appeared = false;
					}
				};

				//create a modified fn with some additional logic
				var modifiedFn = function() {

					//mark the element as visible
					t.appeared = true;

					//is this supposed to happen only once?
					if (settings.one) {

						//remove the check
						w.unbind('scroll', check);
						var i = $.inArray(check, $.fn.appear.checks);
						if (i >= 0) $.fn.appear.checks.splice(i, 1);
					}

					//trigger the original fn
					fn.apply(this, arguments);
				};

				//bind the modified fn to the element
				if (settings.one) t.one('appear', settings.data, modifiedFn);
				else t.bind('appear', settings.data, modifiedFn);

				//check whenever the window scrolls
				w.scroll(check);

				//check whenever the dom changes
				$.fn.appear.checks.push(check);

				//check now
				(check)();
			});
		};

		//keep a queue of appearance checks
		$.extend($.fn.appear, {

			checks: [],
			timeout: null,

			//process the queue
			checkAll: function() {
				var length = $.fn.appear.checks.length;
				if (length > 0) while (length--) ($.fn.appear.checks[length])();
			},

			//check the queue asynchronously
			run: function() {
				if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
				$.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
			}
		});

		//run checks when these methods are called
		$.each(['append', 'prepend', 'after', 'before', 'attr',
			'removeAttr', 'addClass', 'removeClass', 'toggleClass',
			'remove', 'css', 'show', 'hide'], function(i, n) {
			var old = $.fn[n];
			if (old) {
				$.fn[n] = function() {
					var r = old.apply(this, arguments);
					$.fn.appear.run();
					return r;
				}
			}
		});
		
		$(document).ready(function(){
			//$('.product-item').responsiveEqualHeightGrid();
			$("[data-appear-animation]").each(function() {
				$(this).addClass("appear-animation");
				if($(window).width() > 767) {
					$(this).appear(function() {

						var delay = ($(this).attr("data-appear-animation-delay") ? $(this).attr("data-appear-animation-delay") : 1);

						if(delay > 1) $(this).css("animation-delay", delay + "ms");
						$(this).addClass($(this).attr("data-appear-animation"));
						$(this).addClass("animated");

						setTimeout(function() {
							$(this).addClass("appear-animation-visible");
						}, delay);

					}, {accX: 0, accY: -150});
				} else {
					$(this).addClass("appear-animation-visible");
				}
			});
			// MEGAMENU JS
			$('.nav-main-menu li.mega-menu-fullwidth.menu-2columns').hover(function(){
				if($(window).width() > 1199){
					var position = $(this).position();
					var widthMenu = $("#mainMenu").width() - position.left;
					$(this).find('ul.dropdown-menu').width(widthMenu);
				}
			});
			$('.nav-main-menu .static-menu li > .toggle-menu a').click(function(){
				$(this).toggleClass('active');
				$(this).parent().parent().find('> ul').slideToggle();
			});
			// END MEGAMENU
			
			
			// RESPONSIVE
			$('.action.nav-toggle').click(function(){
				if ($('html').hasClass('nav-open')) {
					$('html').removeClass('nav-open');
					setTimeout(function () {
						$('html').removeClass('nav-before-open');
					}, 300);
				} else {
					$('html').addClass('nav-before-open');
					setTimeout(function () {
						$('html').addClass('nav-open');
					}, 42);
				}
			});
			$('.close-nav-button').click(function(){
				$('html').removeClass('nav-open');
				setTimeout(function () {
					$('html').removeClass('nav-before-open');
				}, 300);
			});
			
			/* Shipping & Discount Code */
			$('.checkout-extra > .block > .title').click(function(){
				$('.checkout-extra > .block > .title').removeClass('active');
				$('.checkout-extra > .block > .content').removeClass('active');
				$(this).addClass('active');
				$(this).parent().find('> .content').addClass('active');
			});
			
			$(document).on("click",".sidebar.sidebar-additional .block .block-title .title",function(e){
				$(this).toggleClass('active');
				$(this).parent().parent().find('.block-content').slideToggle();
			});
			
		});
		
		
	})(jQuery);
});

require([
	'jquery', 'mgs_quickview'
], function(jQuery){
	(function($) {
		$(document).ready(function(){
			$('.btn-loadmore').click(function(){
				var el = $(this);
				el.find('span').addClass('loading');
				url = $(this).attr('href');
				$.ajax({
					url: url,
					success: function(data,textStatus,jqXHR ){
						var result = $.parseJSON(data);
						if(result.content!=''){
							$('#'+result.element_id).append(result.content);
							$('.mgs-quickview').bind('click', function () {
								var prodUrl = $(this).attr('data-quickview-url');
								if (prodUrl.length) {
									reInitQuickview($,prodUrl);
								}
							});
						}

                        $("form[data-role='tocart-form']").catalogAddToCart();
						
						if(result.url){
							el.attr('href', result.url);
						}else{
							el.remove();
						}
					}
				});
				return false;
			});
		});
		
	})(jQuery);
});

function reInitQuickview($, prodUrl){
	if (!prodUrl.length) {
		return false;
	}
	var url = QUICKVIEW_BASE_URL + 'mgs_quickview/index/updatecart';
	$.magnificPopup.open({
		items: {
			src: prodUrl
		},
		type: 'iframe',
		removalDelay: 300,
		mainClass: 'mfp-fade',
		closeOnBgClick: true,
		preloader: true,
		tLoading: '',
		callbacks: {
			open: function () {
				$('.mfp-preloader').css('display', 'block');
			},
			beforeClose: function () {
				$('[data-block="minicart"]').trigger('contentLoading');
				$.ajax({
					url: url,
					method: "POST"
				});
			},
			close: function () {
				$('.mfp-preloader').css('display', 'none');
			}
		}
	});
}

function setLocation(url) {
    require([
        'jquery'
    ], function (jQuery) {
        (function () {
            window.location.href = url;
        })(jQuery);
    });
}

function showHideFormSearch(){
	require([
	'jquery'
	], function(jQuery){
		(function($) {
			$(".mobile-serch").toggleClass("open-mobile-serch")
			$('.actions-search .action-search').toggleClass('on-show');
			$('.search-form .form-search').toggleClass('on-show');
			$('.search-form').toggleClass('active');
			$('html').toggleClass('html_overflow');
			if($('.header').hasClass('header3')){
				var $widthContent = $('.header.header3 .middle-content').width() - $('.header.header3 .middle-content .logo').width() - $('.header.header3 .middle-content .middle-header-right-content').width();
				$('.header.header3 .search-form .form-search').width($widthContent);
			}
			if($('.header').hasClass('header9')){
				var $widthContent = $('.header.header9 .middle-content').width() - $('.header.header9 .middle-content .logo').width() - $('.header.header9 .middle-content .middle-header-right-content').width();
				$('.header.header9 .search-form .form-search').width($widthContent);
			}
			setTimeout(focusSearchField, 500);
		})(jQuery);
	});
}
function focusSearchField(){
	require([
	'jquery'
	], function(jQuery){
		(function($) {
			$('#search_mini_form input#search').focus();
			$("#search_mini_form").submit(function(e){
				e.preventDefault();
			});
			$('#clear_search').click(function(){
				$('#search').val('');
				$('.view_more').remove();
				$('#mgs-instant-autocomplete-wrapper').css("display", "none");
			});
			$("#search").on("keyup", function(){
				var search_lenght = $('.mgs-instant-autocomplete-wrapper .title').length;
				if ($(window).width() > 992) {
					var page_height = $(window).height() - 248;
				} else {
					var page_height = $(window).height() - 115;
				}
				$('.mgs-instant-autocomplete-wrapper').height(page_height);
				var checkResult = setInterval(() => {
					if(jQuery('.category-items li').length > 0){
						jQuery('.category-items li').each(function(){
							var thisText = jQuery(this).find('.category-item-link').text();
							if(thisText.indexOf('@') > -1 ){
								thisText = thisText.split('@');
								var updatedText = ''+thisText[0]+'<span class="result-count">'+thisText[1]+'</span>'+''
								jQuery(this).find('.category-item-link').html(updatedText)
							}
						})
						clearInterval(checkResult);
					}
				}, 100);
				updateViewMore();
				function updateViewMore(){
					let blog_total = $('.see-all span[data-bind="text: result.blog.size"]').text();
					let page_total = $('.see-all span[data-bind="text: result.page.size"]').text();
					let category_total = $('.see-all span[data-bind="text: result.category.size"]').text();
					let product_total = $('.see-all span[data-bind="text: result.product.size"]').text();
					if(blog_total == ''){blog_total = 0;}
					if(page_total == ''){page_total = 0;}
					if(category_total == ''){category_total = 0;}
					if(product_total == ''){product_total = 0;}
					let total_view = parseInt(blog_total) + parseInt(page_total) + parseInt(category_total) + parseInt(product_total);
					//console.log('total_view 1: '+total_view);
					let view_more = '<div class="view_more"><a href="/catalogsearch/result/?q='+$("#search").val()+'">View All Results <span class="total_view" style="display:none;">(<i class="print-count" style="font-style:normal;">'+ total_view +'</i>)</span></a>';
					if(total_view != undefined || total_view != null) {
						//console.log('total_view 3: '+total_view);
						$('#mgs-instant-autocomplete-wrapper div[data-bind="visible: anyResultCount()"]').find('.view_more').remove();
						$('#mgs-instant-autocomplete-wrapper div[data-bind="visible: anyResultCount()"]').append(view_more);
						//clearInterval(checkResult);
					}
					if(search_lenght > 0 && total_view >= 0 && total_view != undefined) {
						//console.log('total_view 4: '+total_view);
						//clearInterval(checkResult);
					}
				}
					
				var addViewResult = setInterval(function(){
					let blog_total = jQuery('.see-all span[data-bind="text: result.blog.size"]').text();
					let page_total = jQuery('.see-all span[data-bind="text: result.page.size"]').text();
					let category_total = jQuery('.see-all span[data-bind="text: result.category.size"]').text();
					let product_total = jQuery('.see-all span[data-bind="text: result.product.size"]').text();
					if(blog_total == ''){blog_total = 0;}
					if(page_total == ''){page_total = 0;}
					if(category_total == ''){category_total = 0;}
					if(product_total == ''){product_total = 0;}
					//let total_view = parseInt(blog_total) + parseInt(page_total) + parseInt(category_total) + parseInt(product_total);
					var total_view = parseInt(product_total);
					//console.log("total_view 0: "+total_view);
					if(total_view > 0){
						//console.log("total_view 1: "+total_view);
						jQuery('.view_more .total_view').find('.print-count').text(total_view);
						jQuery('.view_more .total_view').show();	
					}else{
						setTimeout(function(){
							//console.log("total_view 2: "+total_view);
							jQuery('.view_more .total_view').find('.print-count').text('0');
							jQuery('.view_more .total_view').show();	
						},1500)						
					}				
				},100);				
			});
		})(jQuery);
	});
}
require([
	'jquery'
], function(jQuery){
	(function($) {
		//show old price through data attribute
		if(jQuery('.old-price.sly-old-price.no-display').length > 0){
			jQuery('.old-price.sly-old-price.no-display').each(function(){
				let finalPrice = jQuery(this).prev().find('.price-wrapper').attr('data-price-amount');
				let oldPrice = jQuery(this).find('.price-wrapper').attr('data-price-amount');
				jQuery(this).find('span.price').text('$' + oldPrice);
				if (finalPrice == oldPrice) {
					jQuery(this).hide();
				} else {
					jQuery(this).show();
				}
			});
		}

		$(window).resize(function(){
			if($('.header').hasClass('header3')){
				var $widthContent = $('.header.header3 .middle-content').width() - $('.header.header3 .middle-content .logo').width() - $('.header.header3 .middle-content .middle-header-right-content').width();
				$('.header.header3 .search-form .form-search').width($widthContent);
			}
			if($('.header').hasClass('header9')){
				var $widthContent = $('.header.header9 .middle-content').width() - $('.header.header9 .middle-content .logo').width() - $('.header.header9 .middle-content .middle-header-right-content').width();
				$('.header.header9 .search-form .form-search').width($widthContent);
			}
		});
	})(jQuery);
});