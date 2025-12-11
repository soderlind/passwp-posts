/**
 * Vitest setup file.
 *
 * Mocks jQuery and WordPress globals.
 */

import { vi } from 'vitest';

// Mock jQuery
const mockSelect2 = vi.fn();

const mockJQuery = vi.fn((selector) => {
	return {
		length: selector === '#passwp_posts_excluded' ? 1 : 0,
		select2: mockSelect2,
		ready: vi.fn((callback) => callback()),
		on: vi.fn(),
		off: vi.fn(),
		find: vi.fn(() => mockJQuery(selector)),
		val: vi.fn(),
		attr: vi.fn(),
		prop: vi.fn(),
		addClass: vi.fn(),
		removeClass: vi.fn(),
		html: vi.fn(),
		text: vi.fn(),
		append: vi.fn(),
		remove: vi.fn(),
		trigger: vi.fn()
	};
});

// jQuery static methods
mockJQuery.fn = {};
mockJQuery.extend = vi.fn();
mockJQuery.ajax = vi.fn();

// Document ready shorthand
const jQuery = (arg) => {
	if (typeof arg === 'function') {
		// $(function() {}) - document ready
		arg();
		return mockJQuery;
	}
	return mockJQuery(arg);
};

Object.assign(jQuery, mockJQuery);

// Set up globals
global.jQuery = jQuery;
global.$ = jQuery;

// Mock WordPress localized script data
global.passwpPostsAdmin = {
	ajaxUrl: 'https://example.com/wp-admin/admin-ajax.php',
	nonce: 'test_nonce_12345',
	placeholder: 'Search for pages or posts...'
};

// Export mocks for test assertions
export { mockJQuery, mockSelect2 };
