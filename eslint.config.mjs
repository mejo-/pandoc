/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { recommendedVue2Javascript } from '@nextcloud/eslint-config'
import { defineConfig } from 'eslint/config'
import pluginCypress from 'eslint-plugin-cypress'

export default defineConfig([
	...recommendedVue2Javascript,

	{
		name: 'cypress',
		extends: [
			pluginCypress.configs.recommended,
		],
	},
	{
		name: 'pandoc/disable',
		rules: {
			'no-console': 'off', // we use console.error
		},
	},
])
