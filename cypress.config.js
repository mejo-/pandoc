/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import vue from '@vitejs/plugin-vue2'
import { defineConfig } from 'cypress'
import vitePreprocessor from 'cypress-vite'
import { nodePolyfills } from 'vite-plugin-node-polyfills'

module.exports = defineConfig({
	viewportWidth: 1280,
	viewportHeight: 900,
	e2e: {
		setupNodeEvents(on, config) {
			on(
				'file:preprocessor',
				vitePreprocessor({
					plugins: [vue(), nodePolyfills()],
					configFile: false,
				}),
			)

			return config
		},

		baseUrl: 'http://localhost:8081/index.php/',
		specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
	},
	component: {
		devServer: {
			framework: 'vue',
			bundler: 'vite',
		},
	},
	retries: {
		runMode: 2,
		// do not retry in `cypress open`
		openMode: 0,
	},
	numTestsKeptInMemory: 0,
})
