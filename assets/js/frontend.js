/**
 * DRC Advanced Woo Widgets - Frontend Scripts
 * Handles: carousel (Swiper), countdown timer, AJAX pagination
 *
 * @version 1.0.0
 */

(function($) {
	'use strict';

	var DRC_Widgets = {

		init: function() {
			this.initCarousels();
			this.initCountdowns();
			this.initLoadMore();
		},

		/**
		 * Initialize Swiper carousels
		 */
		initCarousels: function() {
			$('.drc-carousel-container').each(function() {
				var $container = $(this);
				var $swiper = $container.find('.swiper');
				var config = $swiper.data('config') || {};

				var defaults = {
					slidesPerView: 4,
					spaceBetween: 20,
					loop: config.loop !== false,
					autoplay: config.autoplay ? {
						delay: config.autoplayDelay || 5000,
						disableOnInteraction: false,
					} : false,
					navigation: {
						nextEl: $container.find('.drc-carousel-next')[0],
						prevEl: $container.find('.drc-carousel-prev')[0],
					},
					pagination: {
						el: $container.find('.drc-carousel-pagination')[0],
						clickable: true,
					},
					breakpoints: {
						320:  { slidesPerView: config.mobileSlides || 1, spaceBetween: 10 },
						768:  { slidesPerView: config.tabletSlides || 2, spaceBetween: 15 },
						1024: { slidesPerView: config.slidesPerView || 4, spaceBetween: 20 }
					},
					lazy: {
						loadPrevNext: true,
						loadPrevNextAmount: 3,
					},
					preloadImages: false,
					watchSlidesProgress: true,
				};

				if (typeof Swiper !== 'undefined') {
					new Swiper($swiper[0], defaults);
				}
			});
		},

		/**
		 * Initialize countdown timers for flash sales
		 */
		initCountdowns: function() {
			$('.drc-countdown').each(function() {
				var $timer = $(this);
				var endTime = parseInt($timer.data('end'), 10) * 1000;

				if (!endTime) return;

				var tick = function() {
					var now = Date.now();
					var remaining = Math.max(0, endTime - now);

					if (remaining <= 0) {
						$timer.html('<span>' + drc_aww_i18n.expired + '</span>');
						return;
					}

					var h = Math.floor(remaining / 3600000);
					var m = Math.floor((remaining % 3600000) / 60000);
					var s = Math.floor((remaining % 60000) / 1000);

					$timer.find('.drc-countdown-h').text(String(h).padStart(2, '0'));
					$timer.find('.drc-countdown-m').text(String(m).padStart(2, '0'));
					$timer.find('.drc-countdown-s').text(String(s).padStart(2, '0'));
				};

				tick();
				setInterval(tick, 1000);
			});
		},

		/**
		 * AJAX Load More
		 */
		initLoadMore: function() {
			$('.drc-aww-load-more-btn').off('click').on('click', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var $wrapper = $btn.closest('.drc-aww-wrapper');
				var page = parseInt($btn.data('page'), 10) || 1;
				var widgetId = $btn.data('widget-id');
				var settings = $btn.data('settings');

				$btn.addClass('loading').text(drc_aww_i18n.loading);

				$.post(drc_aww_ajax.ajax_url, {
					action: 'drc_aww_load_more',
					nonce: drc_aww_ajax.nonce,
					page: page + 1,
					widget_id: widgetId,
					settings: settings,
				}, function(response) {
					$btn.removeClass('loading').text(drc_aww_i18n.load_more);

					if (response.success && response.data.html) {
						if (response.data.has_more) {
							$btn.data('page', page + 1);
						} else {
							$btn.remove();
						}

						if ($wrapper.find('.drc-products-masonry').length) {
							$wrapper.find('.drc-products-masonry').append(response.data.html);
						} else {
							$wrapper.find('.drc-products-grid').append(response.data.html);
						}
					}
				}).fail(function() {
					$btn.removeClass('loading').text(drc_aww_i18n.error);
				});
			});
		},
	};

	$(document).ready(function() {
		DRC_Widgets.init();

		// Re-init on Elementor frontend render
		$(window).on('elementor/frontend/init', function() {
			if (typeof elementorFrontend !== 'undefined') {
				elementorFrontend.hooks.addAction('frontend/element_ready/global', function() {
					DRC_Widgets.init();
				});
			}
		});
	});

	// Magical Shop Builder compatibility
	$(document).on('msb_ajax_content_loaded', function() {
		DRC_Widgets.initCarousels();
		DRC_Widgets.initCountdowns();
	});

})(jQuery);