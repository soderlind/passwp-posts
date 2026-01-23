/**
 * Admin JavaScript for PassWP Posts.
 *
 * Initializes Select2 for the post/page selection fields.
 *
 * @package PassWP_Posts
 */

/* global jQuery, passwpPostsAdmin */

(function ($) {
	'use strict';

	/**
	 * Initialize Select2 on all select fields with the passwp-posts-select2 class.
	 */
	function initSelect2() {
		$('.passwp-posts-select2').each(function () {
			var $select = $(this);

			if ($select.data('select2')) {
				return; // Already initialized
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
	 * Initialize protection mode toggle.
	 */
	function initProtectionModeToggle() {
		var $modeRadios = $('.passwp-protection-mode');
		var $excludedWrapper = $('#passwp-excluded-posts-wrapper');
		var $protectedWrapper = $('#passwp-protected-posts-wrapper');

		if (!$modeRadios.length) {
			return;
		}

		// Get the parent table rows for both fields
		var $excludedRow = $excludedWrapper.closest('tr');
		var $protectedRow = $protectedWrapper.closest('tr');

		// Set initial visibility based on current mode
		var currentMode = passwpPostsAdmin.protectionMode || 'all';
		if (currentMode === 'selected') {
			$excludedRow.hide();
			$protectedRow.show();
		} else {
			$excludedRow.show();
			$protectedRow.hide();
		}

		$modeRadios.on('change', function () {
			if ($(this).val() === 'selected') {
				$excludedRow.hide();
				$protectedRow.show();
			} else {
				$excludedRow.show();
				$protectedRow.hide();
			}
		});
	}

	/**
	 * Initialize auto-redirect toggle.
	 */
	function initAutoRedirectToggle() {
		var $autoRedirect = $('#passwp_posts_auto_redirect');
		var $redirectWrapper = $('#passwp-redirect-page-wrapper');

		if (!$autoRedirect.length || !$redirectWrapper.length) {
			return;
		}

		// Get the parent table row for the redirect page field
		var $redirectRow = $redirectWrapper.closest('tr');

		// Set initial visibility based on current state
		if ($autoRedirect.is(':checked')) {
			$redirectRow.show();
		} else {
			$redirectRow.hide();
		}

		$autoRedirect.on('change', function () {
			if ($(this).is(':checked')) {
				$redirectRow.show();
			} else {
				$redirectRow.hide();
			}
		});
	}

	/**
	 * Document ready.
	 */
	$(document).ready(function () {
		initSelect2();
		initPasswordToggle();
		initProtectionModeToggle();
		initAutoRedirectToggle();
	});
})(jQuery);
