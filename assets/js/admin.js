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
	 * Document ready.
	 */
	$(document).ready(function () {
		initSelect2();
	});
})(jQuery);
