import { randUser } from '../utils/index.js'
const user = randUser()
let fileId

describe('Open pandoc app frontend', function() {
	before(function() {
		cy.createUser(user)
	})

	beforeEach(function() {
		cy.login(user)
	})

	it('Without fileIds in query string', function() {
		cy.visit('/apps/pandoc')
		cy.get('.empty-content__action')
			.should('contain', 'Missing fileIds')
	})

	it('Without non-integer fileIds in query string', function() {
		cy.visit('/apps/pandoc?fileIds=test')
		cy.get('.toast-error')
			.should('contain', 'Invalid fileId: test')
	})

	it('With non-existent fileIds in query string', function() {
		cy.visit('/apps/pandoc?fileIds=123')
		cy.get('.empty-content__action', { timeout: Cypress.config('defaultCommandTimeout') * 3 })
			.should('contain', 'Could not convert the content of file with fileId 123')
	})

	it('With uploaded markdown file', function() {
		// Upload test file
		cy.uploadFile('test.md', 'text/markdown')
			.then((id) => {
				fileId = id
			})
			.then(() => {
				cy.visit(`/apps/pandoc?fileIds=${fileId}`)
				cy.get('.empty-content__title')
					.should('contain', 'Converting files')
				cy.get('.file-content-pre', { timeout: Cypress.config('defaultCommandTimeout') * 3 })
					.should('contain', 'Paragraph with some')
			})

	})
})
