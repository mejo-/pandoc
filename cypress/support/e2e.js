// This file is loaded before all e2e tests

import './commands.js'

before(() => {
	Cypress.on('uncaught:exception', (err) => {
		if (err.message.includes('ResizeObserver')) {
			return false
		}

		return true
	})
})
