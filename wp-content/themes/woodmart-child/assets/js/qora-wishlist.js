/**
 * Qoralink Frontend Wishlist Handler
 *
 * Ensures instant loading spinner feedback on click, persistent red colored heart
 * on favorited items, protection of the heart SVG from text-replacement, and seamless
 * sync with WoodMart's cookie and AJAX wishlist subsystems.
 *
 * @package woodmart-child
 */
(function($) {
	'use strict';

	var HEART_SVG = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';

	function getWishlistCookieName() {
		var name = 'woodmart_wishlist_products';
		if (typeof woodmart_settings !== 'undefined' && woodmart_settings.is_multisite) {
			name += '_' + woodmart_settings.current_blog_id;
		}
		return name;
	}

	function getWishlistProductIds() {
		var ids = [];
		if (typeof Cookies === 'undefined') {
			return ids;
		}
		var cookie = Cookies.get(getWishlistCookieName());
		if (cookie) {
			try {
				var parsed = JSON.parse(cookie);
				if (parsed && typeof parsed === 'object') {
					ids = Object.keys(parsed).map(function(k) {
						return parseInt(k, 10);
					});
				}
			} catch (e) {
				// Ignore parse error
			}
		}
		return ids;
	}

	function syncCardWishlistState($button, isAdded) {
		var $wrap = $button.closest('.qora-showcase-card__wishlist-btn');

		if (isAdded) {
			$button.addClass('added');
			$wrap.addClass('added');
			$button.attr('title', 'Remove from wishlist');
			$button.attr('aria-label', 'Remove from wishlist');
		} else {
			$button.removeClass('added');
			$wrap.removeClass('added');
			$button.attr('title', 'Add to wishlist');
			$button.attr('aria-label', 'Add to wishlist');
		}

		// Ensure SVG exists and hasn't been replaced by text
		var $svg = $button.find('svg');
		if (!$svg.length) {
			$button.prepend(HEART_SVG);
			$svg = $button.find('svg');
		}

		// Apply fill/stroke explicitly on the SVG
		if (isAdded) {
			$svg.attr('fill', '#E53935').attr('stroke', '#E53935');
			$svg.find('path').attr('fill', '#E53935').attr('stroke', '#E53935');
		} else {
			$svg.attr('fill', 'none').attr('stroke', 'currentColor');
			$svg.find('path').attr('fill', 'none').attr('stroke', 'currentColor');
		}

		// Ensure text span is safely preserved but hidden
		var $textSpan = $button.find('.wd-action-text');
		if (!$textSpan.length) {
			$button.append('<span class="wd-action-text" style="display:none !important;">' + (isAdded ? 'Remove from wishlist' : 'Add to wishlist') + '</span>');
		} else {
			$textSpan.hide().css('display', 'none');
		}
	}

	function initCardWishlistStates() {
		var productIds = getWishlistProductIds();
		$('.qora-showcase-card__wishlist-btn a').each(function() {
			var $btn = $(this);
			var pid = parseInt($btn.data('product-id'), 10);
			if (pid && productIds.indexOf(pid) !== -1) {
				syncCardWishlistState($btn, true);
			} else if ($btn.hasClass('added')) {
				syncCardWishlistState($btn, true);
			} else {
				syncCardWishlistState($btn, false);
			}
		});
	}

	// Handle click on favorite button
	$(document).on('click', '.qora-showcase-card__wishlist-btn a', function(e) {
		var $btn = $(this);
		var productId = $btn.data('product-id');
		var isCurrentlyAdded = $btn.hasClass('added');
		var willBeAdded = !isCurrentlyAdded;

		// 1. Immediately show circular loader
		$btn.addClass('loading');

		// 2. Schedule loader removal and sync
		// For guest users (using Cookies), WoodMart acts synchronously.
		// For logged-in users, WoodMart performs an AJAX request.
		// In either case, 320ms provides a smooth, visible loading moment.
		setTimeout(function() {
			$btn.removeClass('loading');
			syncCardWishlistState($btn, willBeAdded);

			// Synchronize all other buttons for this product ID on the page (e.g. if present in multiple blocks)
			if (productId) {
				$(".qora-showcase-card__wishlist-btn a[data-product-id='" + productId + "']").each(function() {
					syncCardWishlistState($(this), willBeAdded);
				});
			}
		}, 320);

		// Safety fallback: ensure loading spinner is removed no matter what
		setTimeout(function() {
			$btn.removeClass('loading');
		}, 1200);
	});

	// Listen to WoodMart custom document events
	$(document).on('added_to_wishlist wdUpdateWishlist wdWishlistSaveFragments', function() {
		setTimeout(initCardWishlistStates, 100);
	});

	// Initialize on DOM ready
	$(document).ready(function() {
		initCardWishlistStates();
	});

})(jQuery);
