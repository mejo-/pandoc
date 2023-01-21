<template>
	<div>
		<!-- loading -->
		<NcEmptyContent v-show="loading"
			:title="t('pandoc', 'Converting files')">
			<template #icon>
				<DownloadIcon />
			</template>
			<template #action>
				<NcProgressBar :value="loadingProgress" size="medium">
					{{ loadingProgress }}
				</NcProgressBar>
				<div class="load-message">
					{{ t('pandoc', 'Converting files:') }}
					{{ loadTotal ? `${loadCount} / ${loadTotal}` : '' }}
				</div>
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
import { showError } from '@nextcloud/dialogs'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcProgressBar from '@nextcloud/vue/dist/Components/NcProgressBar.js'
import AlertOctagonIcon from 'vue-material-design-icons/AlertOctagon.vue'
import DownloadIcon from 'vue-material-design-icons/Download.vue'

export default {
	name: 'FileContent',

	components: {
		AlertOctagonIcon,
		DownloadIcon,
		NcEmptyContent,
		NcProgressBar,
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
			loadCount: 0,
			loadTotal: 0,
		}
	},

	computed: {
		fileIdInts() {
			// Turn comma-separated fileIds into array, filter out non-numbers
			return this.fileIds.split(',').map((string) => {
				const id = parseInt(string)
				if (!id || isNaN(id)) {
					console.error('Invalid fileId ', string)
					showError(t('pandoc', 'Invalid fileId:') + ' ' + string)
					return undefined
				}
				return id
			}).filter(id => id !== undefined)
		},

		loadingProgress() {
			return this.loadTotal
				? this.loadCount / this.loadTotal * 100
				: 0
		},
	},

	mounted() {
		this.loadTotal = this.fileIdInts.length
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
			for (const fileId of this.fileIdInts) {
				try {
					this.fileContents[fileId] = await this.convertFileContent(fileId)
					this.loadCount += 1
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

.progress-bar {
	margin-top: 8px;
}

.load-message {
	color: var(--color-text-maxcontrast);
}
</style>
