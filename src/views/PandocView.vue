<template>
	<NcAppContent>
		<NcEmptyContent v-if="!fileIds" v-cloak :title="t('pandock', 'Error')">
			<template #icon>
				<AlertOctagonIcon />
			</template>
			<template #action>
				{{ t('pandoc', 'Missing fileIds (in URL query string)') }}
			</template>
		</NcEmptyContent>

		<FileContent
			v-if="fileIds"
			:file-ids="fileIds" />
	</NcAppContent>
</template>

<script>
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import AlertOctagonIcon from 'vue-material-design-icons/AlertOctagon.vue'
import FileContent from '../components/FileContent.vue'

export default {
	name: 'PandocView',

	components: {
		AlertOctagonIcon,
		NcAppContent,
		NcEmptyContent,
		FileContent,
	},

	data() {
		return {
			fileIds: null,
		}
	},

	mounted() {
		this.getFileIds()
	},

	methods: {
		getFileIds() {
			const urlParams = new URLSearchParams(window.location.search)
			this.fileIds = urlParams.get('fileIds')
		},
	},
}
</script>

<style scoped>
[v-cloak] {
	display: none;
}
</style>
