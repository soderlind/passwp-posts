/**
 * Admin JavaScript for PassWP Posts.
 *
 * Initializes Select2 for the excluded posts/pages field.
 *
 * @package PassWP_Posts
 */

/* global jQuery, passwpPostsAdmin */

(function ($) {
	'use strict';

	/**
	 * Initialize Select2 on the excluded posts field.
	 */
	function initSelect2() {
		var $select = $('#passwp_posts_excluded');

		if (!$select.length) {
			return;
		}

		$select.select2({
			ajax: {
				url: passwpPostsAdmin.ajaxUrl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						action: 'passwp_posts_search',
						nonce: passwpPostsAdmin.nonce,
						search: params.term || '',
						page: params.page || 1
					};
				},
				processResults: function (data, params) {
					params.page = params.page || 1;
					return {
						results: data.results,
						pagination: {
							more: data.more
						}
					};
				},
				cache: true
			},
			minimumInputLength: 2,
			placeholder: passwpPostsAdmin.placeholder,
			allowClear: true,
			width: '100%'
		});
	}

	/**
	 * Initialize password visibility toggle.
	 */
	function initPasswordToggle() {
		var $toggle = $('.passwp-toggle-password');
		var $input = $('#passwp_posts_password');

		if (!$toggle.length || !$input.length) {
			return;
		}

		$toggle.on('click', function (e) {
			e.preventDefault();
			var $button = $(this);
			var $icon = $button.find('.dashicons');
			var input = $input.get(0);

			if (input.type === 'password') {
				input.type = 'text';
				$icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
				$button.attr('aria-label', passwpPostsAdmin.hidePassword || 'Hide password');
			} else {
				input.type = 'password';
				$icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
				$button.attr('aria-label', passwpPostsAdmin.showPassword || 'Show password');
			}
		});
	}

	/**
	 * Document ready.
	 */
	$(document).ready(function () {
		initSelect2();
		initPasswordToggle();
	});
})(jQuery);
