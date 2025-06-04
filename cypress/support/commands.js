import axios from '@nextcloud/axios'
import { createUser, logout } from '@nextcloud/cypress/commands'

const url = Cypress.config('baseUrl').replace(/\/index.php\/?$/g, '')
Cypress.env('baseUrl', url)

// Copy of the new login command as long as we are blocked to upgrade @nextcloud/cypress by cypress crashes
const login = function(user) {
	cy.session(user, function() {
	    cy.request('/csrftoken').then(({ body }) => {
	        const requestToken = body.token
	        cy.request({
	            method: 'POST',
	            url: '/login',
	            body: {
	                user: user.userId,
	                password: user.password,
	                requesttoken: requestToken,
	            },
	            headers: {
	                'Content-Type': 'application/x-www-form-urlencoded',
	                // Add the Origin header so that the request is not blocked by the browser.
	                Origin: (Cypress.config('baseUrl') ?? '').replace('index.php/', ''),
	            },
	            followRedirect: false,
	        })
	    })
	}, {
	    validate() {
	        cy.request('/apps/files').its('status').should('eq', 200)
	    },
	})
}

// Import commands from @nextcloud/cypress
Cypress.Commands.add('login', login)
Cypress.Commands.add('logout', logout)
Cypress.Commands.add('createUser', createUser)

Cypress.Commands.add('uploadFile', (fileName, mimeType) => {
	return cy.fixture(fileName, 'base64')
		.then(Cypress.Blob.base64StringToBlob)
		.then(blob => {
			const file = new File([blob], fileName, { type: mimeType })
			return cy.request('/csrftoken')
				.then(({ body }) => body.token)
				.then(requesttoken => {
					return axios.put(`${url}/remote.php/webdav/${fileName}`, file, {
						headers: {
							requesttoken,
							'Content-Type': mimeType,
						},
					}).then((response) => {
						const ocFileId = response.headers['oc-fileid']
						const fileId = parseInt(ocFileId)
						cy.log(`Uploaded ${fileName} to ${fileId}`, response.status)
						return fileId
					})
				})
		})
})
