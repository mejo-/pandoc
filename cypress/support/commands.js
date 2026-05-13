/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import axios from '@nextcloud/axios'
import { addCommands } from '@nextcloud/e2e-test-server/cypress'
import { emit } from '@nextcloud/event-bus'

const url = Cypress.config('baseUrl').replace(/\/index.php\/?$/g, '')
Cypress.env('baseUrl', url)
const silent = { log: false }

addCommands()

// Prepare the csrf-token for axios
Cypress.Commands.overwrite('login', (login, user) => {
	cy.window(silent).then((win) => {
		win.location.href = 'about:blank'
	})
	login(user)
	cy.request('/csrftoken', silent).then(({ body }) =>
		emit('csrf-token-update', body),
	)
	cy.wrap(user, silent).as('currentUser')
})

Cypress.Commands.add('uploadFile', (fileName, mimeType) => {
	return cy
		.fixture(fileName, 'binary')
		.then(Cypress.Blob.binaryStringToBlob)
		.then((blob) => {
			return axios
				.put(
					`${url}/remote.php/webdav/${fileName}`,
					blob.size > 0 ? blob : '',
					{ headers: { 'Content-Type': mimeType } },
				)
				.then((response) => {
					const fileId = Number(
						response.headers['oc-fileid']?.split('oc')?.[0],
					)
					cy.log(`Uploaded ${fileName} to ${fileId}`, response.status)
					return cy.wrap(fileId, silent)
				})
		})
})
