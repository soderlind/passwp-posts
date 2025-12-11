/**
 * Tests for admin.js Select2 initialization.
 *
 * @package PassWP_Posts
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mockSelect2 } from './setup.js';

describe('Admin JavaScript', () => {
	beforeEach(() => {
		// Reset mocks before each test
		vi.clearAllMocks();
		
		// Reset the module cache to re-run the admin.js initialization
		vi.resetModules();
	});

	describe('Select2 Initialization', () => {
		it('should call select2() on the excluded posts field', async () => {
			// Import the admin module (triggers initialization)
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalled();
		});

		it('should configure Select2 with AJAX options', async () => {
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalledWith(
				expect.objectContaining({
					ajax: expect.objectContaining({
						url: 'https://example.com/wp-admin/admin-ajax.php',
						dataType: 'json',
						delay: 250
					})
				})
			);
		});

		it('should set minimum input length to 2', async () => {
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalledWith(
				expect.objectContaining({
					minimumInputLength: 2
				})
			);
		});

		it('should use localized placeholder text', async () => {
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalledWith(
				expect.objectContaining({
					placeholder: 'Search for pages or posts...'
				})
			);
		});

		it('should allow clearing selections', async () => {
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalledWith(
				expect.objectContaining({
					allowClear: true
				})
			);
		});

		it('should set width to 100%', async () => {
			await import('../../assets/js/admin.js');

			expect(mockSelect2).toHaveBeenCalledWith(
				expect.objectContaining({
					width: '100%'
				})
			);
		});

		it('should enable caching for AJAX requests', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			expect(select2Config.ajax.cache).toBe(true);
		});
	});

	describe('AJAX Data Function', () => {
		it('should include action parameter in AJAX data', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ term: 'test', page: 1 });

			expect(result.action).toBe('passwp_posts_search');
		});

		it('should include nonce in AJAX data', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ term: 'test', page: 1 });

			expect(result.nonce).toBe('test_nonce_12345');
		});

		it('should include search term in AJAX data', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ term: 'my search', page: 1 });

			expect(result.search).toBe('my search');
		});

		it('should handle empty search term', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ page: 1 });

			expect(result.search).toBe('');
		});

		it('should include page number in AJAX data', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ term: 'test', page: 3 });

			expect(result.page).toBe(3);
		});

		it('should default to page 1 if not provided', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const dataFn = select2Config.ajax.data;

			const result = dataFn({ term: 'test' });

			expect(result.page).toBe(1);
		});
	});

	describe('AJAX Process Results Function', () => {
		it('should return results from server response', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const processResultsFn = select2Config.ajax.processResults;

			const serverData = {
				results: [
					{ id: 1, text: 'Page One (Page)' },
					{ id: 2, text: 'Post Two (Post)' }
				],
				more: true
			};

			const result = processResultsFn(serverData, { page: 1 });

			expect(result.results).toEqual(serverData.results);
		});

		it('should set pagination.more from server response', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const processResultsFn = select2Config.ajax.processResults;

			const serverData = {
				results: [],
				more: true
			};

			const result = processResultsFn(serverData, { page: 1 });

			expect(result.pagination.more).toBe(true);
		});

		it('should handle empty results', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const processResultsFn = select2Config.ajax.processResults;

			const serverData = {
				results: [],
				more: false
			};

			const result = processResultsFn(serverData, { page: 1 });

			expect(result.results).toEqual([]);
			expect(result.pagination.more).toBe(false);
		});

		it('should update params.page from response', async () => {
			await import('../../assets/js/admin.js');

			const select2Config = mockSelect2.mock.calls[0][0];
			const processResultsFn = select2Config.ajax.processResults;

			const serverData = { results: [], more: false };
			const params = {};

			processResultsFn(serverData, params);

			expect(params.page).toBe(1);
		});
	});
});

describe('Admin JavaScript - Element Not Found', () => {
	it('should not throw error if select element does not exist', async () => {
		// Temporarily modify the mock to simulate element not found
		const originalJQuery = global.jQuery;
		
		const mockJQueryNoElement = vi.fn((arg) => {
			if (typeof arg === 'function') {
				arg();
				return mockJQueryNoElement;
			}
			return {
				length: 0,
				select2: vi.fn(),
				ready: vi.fn((callback) => callback()),
				each: vi.fn(),
				on: vi.fn(),
				val: vi.fn(() => 'all'),
				closest: vi.fn(() => ({ hide: vi.fn(), show: vi.fn() })),
				hide: vi.fn(),
				show: vi.fn()
			};
		});
		mockJQueryNoElement.ready = vi.fn((callback) => callback());
		
		global.jQuery = mockJQueryNoElement;

		vi.resetModules();
		
		// Should not throw
		await expect(import('../../assets/js/admin.js')).resolves.not.toThrow();

		// Restore
		global.jQuery = originalJQuery;
	});
});
