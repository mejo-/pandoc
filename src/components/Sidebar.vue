<template>
	<NcAppSidebar :title="t('pandoc', 'Converted files')"
		@close="closeSidebar">
		<NcActionCheckbox v-for="file in convertedFiles"
			:key="file.fileId"
			:checked="!file.hide"
			@change="toggleHideFile(file.fileId)">
			{{ file.name }}
		</NcActionCheckbox>
	</NcAppSidebar>
</template>

<script>
import NcActionCheckbox from '@nextcloud/vue/dist/Components/NcActionCheckbox.js'
import NcAppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar.js'
import { mapMutations, mapState } from 'vuex'

export default {
	name: 'Sidebar',

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
