/**
 * PassWP Posts Customize Tab JavaScript.
 *
 * Handles live preview, color pickers, media uploaders, and preset themes.
 *
 * @package PassWP_Posts
 */

(function ($) {
	'use strict';

	// Preset theme definitions matching PHP field names.
	const presets = {
		default: {
			bg_color: '#667eea',
			bg_gradient_end: '#764ba2',
			bg_image: '',
			card_bg_color: '#ffffff',
			card_border_radius: 16,
			card_shadow: true,
			logo: '',
			logo_width: 120,
			heading_text: '',
			heading_color: '#1e1e1e',
			text_color: '#666666',
			font_family: 'system-ui, -apple-system, sans-serif',
			button_text: '',
			button_bg_color: '#667eea',
			button_text_color: '#ffffff',
			button_border_radius: 8,
			show_remember_me: true,
			input_border_radius: 8,
			footer_text: '',
			footer_link: ''
		},
		'business-blue': {
			bg_color: '#2193b0',
			bg_gradient_end: '#6dd5ed',
			bg_image: '',
			card_bg_color: '#ffffff',
			card_border_radius: 12,
			card_shadow: true,
			logo: '',
			logo_width: 120,
			heading_text: '',
			heading_color: '#1e1e1e',
			text_color: '#666666',
			font_family: 'system-ui, -apple-system, sans-serif',
			button_text: '',
			button_bg_color: '#2193b0',
			button_text_color: '#ffffff',
			button_border_radius: 6,
			show_remember_me: true,
			input_border_radius: 6,
			footer_text: '',
			footer_link: ''
		},
		'dark-mode': {
			bg_color: '#1a1a2e',
			bg_gradient_end: '',
			bg_image: '',
			card_bg_color: '#16213e',
			card_border_radius: 16,
			card_shadow: true,
			logo: '',
			logo_width: 120,
			heading_text: '',
			heading_color: '#e5e5e5',
			text_color: '#a0a0a0',
			font_family: 'system-ui, -apple-system, sans-serif',
			button_text: '',
			button_bg_color: '#6366f1',
			button_text_color: '#ffffff',
			button_border_radius: 8,
			show_remember_me: true,
			input_border_radius: 8,
			footer_text: '',
			footer_link: ''
		}
	};

	/**
	 * Initialize customize functionality.
	 */
	function init() {
		initColorPickers();
		initMediaUploaders();
		initPresetThemes();
		initRangeSliders();
		initLivePreview();
		initResetButton();
	}

	/**
	 * Initialize WordPress color pickers.
	 */
	function initColorPickers() {
		$('.passwp-color-picker').wpColorPicker({
			change: function () {
				setTimeout(updatePreview, 50);
			},
			clear: function () {
				setTimeout(updatePreview, 50);
			}
		});
	}

	/**
	 * Initialize WordPress media uploaders.
	 */
	function initMediaUploaders() {
		$('.passwp-media-upload').each(function () {
			const $wrapper = $(this);
			const $input = $wrapper.find('input[type="hidden"]');
			const $preview = $wrapper.find('.passwp-media-preview');
			const $selectBtn = $wrapper.find('.passwp-media-select');
			const $removeBtn = $wrapper.find('.passwp-media-remove');

			$selectBtn.on('click', function (e) {
				e.preventDefault();

				const frame = wp.media({
					title: passwpCustomize.selectImage || 'Select Image',
					button: { text: passwpCustomize.useImage || 'Use this image' },
					multiple: false
				});

				frame.on('select', function () {
					const attachment = frame.state().get('selection').first().toJSON();
					$input.val(attachment.url);
					$preview.html('<img src="' + attachment.url + '" alt="" />');
					$removeBtn.show();
					updatePreview();
				});

				frame.open();
			});

			$removeBtn.on('click', function (e) {
				e.preventDefault();
				$input.val('');
				$preview.empty();
				$(this).hide();
				updatePreview();
			});
		});
	}

	/**
	 * Initialize preset theme selection.
	 */
	function initPresetThemes() {
		$('.passwp-preset-card').on('click', function () {
			const preset = $(this).data('preset');
			if (!presets[preset]) return;

			$('.passwp-preset-card').removeClass('active');
			$(this).addClass('active');

			applyPreset(presets[preset]);
			updatePreview();
		});
	}

	/**
	 * Apply preset values to form fields.
	 */
	function applyPreset(values) {
		// Color fields.
		setColorPickerValue('#passwp_bg_color', values.bg_color);
		setColorPickerValue('#passwp_bg_gradient_end', values.bg_gradient_end);
		setColorPickerValue('#passwp_card_bg_color', values.card_bg_color);
		setColorPickerValue('#passwp_heading_color', values.heading_color);
		setColorPickerValue('#passwp_text_color', values.text_color);
		setColorPickerValue('#passwp_button_bg_color', values.button_bg_color);
		setColorPickerValue('#passwp_button_text_color', values.button_text_color);

		// Range sliders.
		$('#passwp_card_border_radius').val(values.card_border_radius).trigger('input');
		$('#passwp_button_border_radius').val(values.button_border_radius).trigger('input');
		$('#passwp_input_border_radius').val(values.input_border_radius).trigger('input');
		$('#passwp_logo_width').val(values.logo_width).trigger('input');

		// Checkboxes.
		$('#passwp_card_shadow').prop('checked', values.card_shadow);
		$('#passwp_show_remember_me').prop('checked', values.show_remember_me);

		// Select.
		$('#passwp_font_family').val(values.font_family);

		// Text fields.
		$('#passwp_heading_text').val(values.heading_text);
		$('#passwp_button_text').val(values.button_text);
		$('#passwp_footer_text').val(values.footer_text);
		$('#passwp_footer_link').val(values.footer_link);
	}

	/**
	 * Set color picker value programmatically.
	 */
	function setColorPickerValue(selector, value) {
		const $input = $(selector);
		if ($input.length) {
			$input.val(value);
			if ($input.hasClass('wp-color-picker')) {
				$input.wpColorPicker('color', value);
			}
		}
	}

	/**
	 * Initialize range sliders.
	 */
	function initRangeSliders() {
		$('input[type="range"]').on('input', function () {
			$(this).closest('.passwp-range-wrapper').find('.passwp-range-value').text($(this).val() + 'px');
			updatePreview();
		});
	}

	/**
	 * Initialize live preview updates.
	 */
	function initLivePreview() {
		// Debounced update for text fields.
		let debounceTimer;
		$('#passwp-customize-form').on('input', 'input[type="text"], input[type="url"]', function () {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(updatePreview, 150);
		});

		// Immediate update for selects and checkboxes.
		$('#passwp-customize-form').on('change', 'select, input[type="checkbox"]', updatePreview);
	}

	/**
	 * Update the live preview.
	 */
    /**
     * Check if a given string is a safe image URL.
     * Allows only http(s) URLs or relative/absolute paths, blocks javascript: and data: schemes.
     */
    function isSafeImageUrl(url) {
        if (typeof url !== 'string') return false;
        // Disallow empty/trivial, data:, javascript:, vbscript: or other harmful schemes.
        // Allow http, https, protocol-relative (//), or local paths, but block javascript/data/etc.
        return /^(https?:\/\/|\/(?!\/)|\.{0,2}\/|[^:]+$)/i.test(url) && !/^\s*(javascript|data|vbscript):/i.test(url);
    }

	function escapeHtml(text) {
	    if (typeof text !== 'string') return '';
	    return text.replace(/[&<>"']/g, function (c) {
	        switch (c) {
	            case '&': return '&amp;';
	            case '<': return '&lt;';
	            case '>': return '&gt;';
	            case '"': return '&quot;';
	            case "'": return '&#39;';
	            default: return c;
	        }
	    });
	}

	// Simple CSS hex color validation (accepts #RGB, #RRGGBB, #RRGGBBAA)
	function isValidHexColor(c) {
	    return typeof c === 'string' && /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(c.trim());
	}

	function updatePreview() {
		const $preview = $('.passwp-preview-frame');
		if (!$preview.length) return;

		// Get current values.
		const bgColor = $('#passwp_bg_color').val() || '#667eea';
		const bgGradientEnd = $('#passwp_bg_gradient_end').val();
		const bgImage = $('#passwp_bg_image').val();
		const cardBgColor = $('#passwp_card_bg_color').val() || '#ffffff';
		const cardBorderRadius = $('#passwp_card_border_radius').val() || 16;
		const cardShadow = $('#passwp_card_shadow').is(':checked');
        // Get logo and sanitize before use
        let logo = $('#passwp_logo').val();
        if (!isSafeImageUrl(logo)) {
            logo = '';
        }
		let logoWidth = parseInt($('#passwp_logo_width').val(), 10);
		if (isNaN(logoWidth) || logoWidth < 20 || logoWidth > 600) {
		    logoWidth = 120;
		}
		const headingText = $('#passwp_heading_text').val();
		const headingColor = $('#passwp_heading_color').val() || '#1e1e1e';
		let textColor = $('#passwp_text_color').val() || '#666666';
		const fontFamily = $('#passwp_font_family').val() || 'system-ui, -apple-system, sans-serif';
		const buttonText = $('#passwp_button_text').val();
		const buttonBgColor = sanitizeColor($('#passwp_button_bg_color').val()) || '#667eea';
		const buttonTextColor = $('#passwp_button_text_color').val() || '#ffffff';
		const buttonBorderRadius = $('#passwp_button_border_radius').val() || 8;
		const showRememberMe = $('#passwp_show_remember_me').is(':checked');
		const inputBorderRadius = $('#passwp_input_border_radius').val() || 8;
		const footerText = $('#passwp_footer_text').val();
		const footerLink = $('#passwp_footer_link').val();

		// Sanitize textColor
		if (!isValidHexColor(textColor)) {
		    textColor = '#666666';
		}

		// Build background style.
		let bgStyle;
		if (bgImage) {
			bgStyle = 'url(' + bgImage + ') center/cover no-repeat';
		} else if (bgGradientEnd) {
			bgStyle = 'linear-gradient(135deg, ' + bgColor + ' 0%, ' + bgGradientEnd + ' 100%)';
		} else {
			bgStyle = bgColor;
		}

		// Build card shadow.
		const shadowStyle = cardShadow ? '0 10px 40px rgba(0, 0, 0, 0.2)' : 'none';

		// Update preview elements.
		$preview.find('.passwp-preview-bg').css({
			'background': bgStyle,
			'font-family': fontFamily
		});

		$preview.find('.passwp-preview-card').css({
			'background-color': cardBgColor,
			'border-radius': cardBorderRadius + 'px',
			'box-shadow': shadowStyle
		});

		// Logo.
		const $logoImg = $preview.find('.passwp-preview-logo');
		if (logo) {
			if ($logoImg.length) {
				$logoImg.attr('src', logo).css('width', logoWidth + 'px').show();
			} else {
				const $newLogo = $('<img>')
					.addClass('passwp-preview-logo')
					.attr('src', logo)
					.attr('alt', '')
					.css('width', logoWidth + 'px');
				$preview.find('.passwp-preview-card').prepend($newLogo);
			}
		} else {
			$logoImg.hide();
		}

		// Heading.
		$preview.find('.passwp-preview-heading').css('color', headingColor);
		if (headingText) {
			$preview.find('.passwp-preview-heading').text(headingText);
		}

		// Text.
		$preview.find('.passwp-preview-text').css('color', textColor);

		// Form input.
		$preview.find('.passwp-preview-form input[type="password"]').css('border-radius', inputBorderRadius + 'px');

		// Remember me.
		$preview.find('.passwp-preview-remember').toggle(showRememberMe).css('color', textColor);

		// Button.
		$preview.find('.passwp-preview-form button').css({
			'background-color': buttonBgColor,
			'color': buttonTextColor,
			'border-radius': buttonBorderRadius + 'px'
		});
		if (buttonText) {
			$preview.find('.passwp-preview-form button').text(buttonText);
		}

		// Footer.
		const $footer = $preview.find('.passwp-preview-footer');
		if (footerText) {
			if (footerLink) {
				$footer.html('<a href="' + escapeHtml(footerLink) + '" style="color: ' + buttonBgColor + ';">' + escapeHtml(footerText) + '</a>');
			} else {
				$footer.html('<span style="color: ' + textColor + ';">' + escapeHtml(footerText) + '</span>');
			}
		} else {
			$footer.html('<a href="#" style="color: ' + buttonBgColor + ';">&larr; Back to home</a>');
		}
	}

	/**
	 * Initialize reset button.
	 */
	function initResetButton() {
		$('#passwp-reset-customize').on('click', function (e) {
			e.preventDefault();

			if (!confirm(passwpCustomize.resetConfirm || 'Are you sure you want to reset all settings to defaults?')) {
				return;
			}

			// Apply default preset.
			applyPreset(presets.default);

			// Clear image fields.
			$('#passwp_bg_image, #passwp_logo').val('');
			$('.passwp-media-preview').empty();
			$('.passwp-media-remove').hide();

			// Update UI.
			$('.passwp-preset-card').removeClass('active');
			$('.passwp-preset-card[data-preset="default"]').addClass('active');

			updatePreview();
		});
	}

	// Initialize when document is ready.
	$(document).ready(init);

/**
 * Strictly sanitize CSS color values.
 * Allows hex colors (#abc, #aabbcc, #aabbccdd), rgb(), rgba(), hsl(), hsla(), or color keyword.
 * Rejects anything else; returns undefined if not safe.
 */
function sanitizeColor(input) {
	if (typeof input !== "string") return undefined;
	// Hex: #abc, #aabbcc, #aabbccdd
	const hexPattern = /^#([A-Fa-f0-9]{3,4}){1,2}$/;
	// rgb() / rgba()
	const rgbPattern = /^rgb(a)?\(\s*([0-9]{1,3}\s*,\s*){2,3}[0-9\.]+\s*\)$/i;
	// hsl() / hsla()
	const hslPattern = /^hsl(a)?\(\s*([0-9]{1,3}(deg)?\s*,\s*){2,3}[0-9\.%]+\s*\)$/i;
	// CSS keywords (whitelist basic)
	const cssKeywords = /^(black|white|red|green|blue|yellow|purple|pink|orange|gray|grey|brown|cyan|magenta)$/i;
	if (hexPattern.test(input) || rgbPattern.test(input) || hslPattern.test(input) || cssKeywords.test(input.trim())) {
		return input.trim();
	}
	return undefined;
}

})(jQuery);
