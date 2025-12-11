/**
 * Vitest setup file.
 *
 * Mocks jQuery and WordPress globals.
 */

import { vi } from 'vitest';

// Mock jQuery
const mockSelect2 = vi.fn();

const mockJQuery = vi.fn((selector) => {
	const hasMatch = selector === '#passwp_posts_excluded' || 
		selector === '#passwp_posts_protected' ||
		selector === '.passwp-posts-select2';
	
	const instance = {
		length: hasMatch ? 1 : 0,
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
		trigger: vi.fn(),
		data: vi.fn(() => null), // select2 not yet initialized
		each: vi.fn(function(callback) {
			if (hasMatch) {
				callback.call(instance, 0, instance);
			}
			return instance;
		}),
		slideUp: vi.fn(),
		slideDown: vi.fn(),
		hide: vi.fn(),
		show: vi.fn(),
		closest: vi.fn(() => mockJQuery(selector)),
		get: vi.fn(() => ({ type: 'password' })),
		siblings: vi.fn(() => mockJQuery(selector))
	};
	return instance;
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
	placeholder: 'Search for pages or posts...',
	showPassword: 'Show password',
	hidePassword: 'Hide password',
	protectionMode: 'all'
};

// Export mocks for test assertions
export { mockJQuery, mockSelect2 };
