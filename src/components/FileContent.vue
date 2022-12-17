<template>
	<div>
		<!-- loading -->
		<NcEmptyContent v-show="loading"
			:title="t('pandoc', 'Converting files')">
			<template #icon>
				<DownloadIcon />
			</template>
			<template #action>
				...
			</template>
		</NcEmptyContent>

		<!-- error message -->
		<NcEmptyContent v-show="!loading && error"
			:title="t('pandock', 'Error')">
			<template #icon>
				<AlertOctagonIcon />
			</template>
			<template #action>
				{{ error }}
			</template>
		</NcEmptyContent>

		<!-- converted content -->
		<div v-show="!loading && !error" class="file-content">
			<div v-for="(content, fileId, index) in fileContents" :key="fileId">
				<pre class="file-content-pre">{{ content }}</pre>
				<template v-if="index !== Object.keys(fileContents).length - 1">
					<br><hr><br>
				</template>
			</div>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { NcEmptyContent } from '@nextcloud/vue'
import AlertOctagonIcon from 'vue-material-design-icons/AlertOctagon.vue'
import DownloadIcon from 'vue-material-design-icons/Download.vue'

export default {
	name: 'FileContent',

	components: {
		AlertOctagonIcon,
		DownloadIcon,
		NcEmptyContent,
	},

	props: {
		fileIds: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			fileContents: {},
			error: null,
			loading: true,
		}
	},

	mounted() {
		this.getFileContents()
	},

	methods: {
		async convertFileContent(fileId) {
			const response = await axios({
				method: 'GET',
				url: generateUrl('/apps/pandoc/convertFile?fileId=' + fileId),
			})
			return response.data.content
		},

		async getFileContents() {
			for (const fileIdString of this.fileIds.split(',')) {
				const fileId = parseInt(fileIdString)
				if (!fileId || isNaN(fileId)) {
					console.error('Invalid fileId ', fileIdString)
					this.error = t('pandoc', 'Invalid fileId ' + fileIdString)
					this.loading = false
					break
				}
				try {
					this.fileContents[fileId] = await this.convertFileContent(fileId)
				} catch (e) {
					console.error('Failed to convert the content of file with fileId ', fileId)
					this.error = t('pandoc', 'Could not convert the content of file with fileId ' + fileId)
					this.loading = false
					break
				}
			}

			this.loading = false
		},
	},
}
</script>

<style scoped>
.file-content {
	max-width: 670px;
	margin: auto;
	position: relative;
	width: 100%;

	padding-top: 20px;
	padding-bottom: 20px;
}

.file-content-pre {
	white-space: pre-wrap;
}
</style>
