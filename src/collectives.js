import { generateUrl } from '@nextcloud/router'

window.OCA.Collectives = {
	...(window.OCA.Collectives || {}),
	CollectiveExtraAction: {
		title: t('pandoc', 'Export with pandoc'),
		click: (fileIds) => {
			window.open(generateUrl('/apps/pandoc?fileIds=' + fileIds.join(',')))
		},
	},
}
