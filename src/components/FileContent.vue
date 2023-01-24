<template>
	<div>
		<div v-show="!loading" class="buttons">
			<NcButton @click="copyContent">
				<template #icon>
					<CheckIcon v-if="copySuccess" :size="20" />
					<ContentPasteIcon v-else :size="20" />
				</template>
				{{ copyButtonText }}
			</NcButton>
			<NcActionButton icon="icon-menu-sidebar"
				:aria-label="t('pandoc', 'Open sidebar')"
				@click="toggleSidebar" />
		</div>

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
			<div v-for="(file, index) in filteredConvertedFiles"
				:key="file.fileId">
				<pre class="file-content-pre">{{ file.content }}</pre>
				<template v-if="index !== filteredConvertedFiles.length - 1">
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
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcProgressBar from '@nextcloud/vue/dist/Components/NcProgressBar.js'
import AlertOctagonIcon from 'vue-material-design-icons/AlertOctagon.vue'
import ContentPasteIcon from 'vue-material-design-icons/ContentPaste.vue'
import CheckIcon from 'vue-material-design-icons/Check.vue'
import DownloadIcon from 'vue-material-design-icons/Download.vue'
import { mapGetters, mapMutations, mapState } from 'vuex'
import CopyToClipboardMixin from '../mixins/CopyToClipboardMixin.js'

export default {
	name: 'FileContent',

	components: {
		AlertOctagonIcon,
		CheckIcon,
		ContentPasteIcon,
		DownloadIcon,
		NcActionButton,
		NcButton,
		NcEmptyContent,
		NcProgressBar,
	},

	mixins: [
		CopyToClipboardMixin,
	],

	props: {
		fileIds: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			error: null,
			loading: true,
			loadCount: 0,
			loadTotal: 0,
		}
	},

	computed: {
		...mapState(['convertedFiles']),

		...mapGetters([
			'displayedFileContents',
			'filteredConvertedFiles',
		]),

		copyButtonText() {
			if (this.copied) {
				return this.copySuccess
					? t('pandoc', 'Copied')
					: t('pandoc', 'Could not copy')
			}
			return t('pandoc', 'Copy to clipboard')
		},

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
		...mapMutations([
			'addConvertedFile',
			'toggleSidebar',
		]),

		async convertFileContent(fileId) {
			const response = await axios({
				method: 'GET',
				url: generateUrl('/apps/pandoc/convertFile?fileId=' + fileId),
			})
			return response.data
		},

		async getFileContents() {
			for (const fileId of this.fileIdInts) {
				try {
					const file = await this.convertFileContent(fileId)
					file.hide = false
					file.fileId = fileId
					this.addConvertedFile(file)
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

		copyContent() {
			this.copyToClipboard(this.displayedFileContents)
		},
	},
}
</script>

<style lang="scss" scoped>
.buttons {
	position: relative;
	display: flex;
	justify-content: flex-end;
	padding-top: 8px;
	padding-right: 8px;
	// make buttons float
	margin-bottom: -52px;
	z-index: 1;
}

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
