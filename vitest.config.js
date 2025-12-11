import { defineConfig } from 'vitest/config';

export default defineConfig({
	test: {
		environment: 'jsdom',
		globals: true,
		setupFiles: ['./tests/js/setup.js'],
		include: ['tests/js/**/*.test.js'],
		coverage: {
			provider: 'v8',
			reporter: ['text', 'html'],
			include: ['assets/js/**/*.js'],
			exclude: ['assets/vendor/**']
		}
	}
});
