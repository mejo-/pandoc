<template>
	<NcAppSidebar
		:title="t('pandoc', 'Converted files')"
		@close="closeSidebar">
		<NcActionCheckbox
			v-for="file in convertedFiles"
			:key="file.fileId"
			:model-value="!file.hide"
			@change="toggleHideFile(file.fileId)">
			{{ file.name }}
		</NcActionCheckbox>
	</NcAppSidebar>
</template>

<script>
import { mapMutations, mapState } from 'vuex'
import NcActionCheckbox from '@nextcloud/vue/components/NcActionCheckbox'
import NcAppSidebar from '@nextcloud/vue/components/NcAppSidebar'

export default {
	name: 'PandocSidebar',

	components: {
		NcActionCheckbox,
		NcAppSidebar,
	},

	computed: {
		...mapState(['convertedFiles']),
	},

	methods: {
		...mapMutations(['closeSidebar', 'toggleHideFile']),
	},
}
</script>

<style scoped>
.app-sidebar-tab-desc {
	font-weight: bold;
}
</style>
