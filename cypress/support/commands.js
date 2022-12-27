import axios from '@nextcloud/axios'
import { addCommands } from '@nextcloud/cypress' // eslint-disable-line

const url = Cypress.config('baseUrl').replace(/\/index.php\/?$/g, '')
Cypress.env('baseUrl', url)

addCommands()

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
