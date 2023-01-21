<template>
	<NcContent app-name="pandoc">
		<PandocView />
		<Sidebar v-if="showSidebar" />
	</NcContent>
</template>

<script>
import { mapState } from 'vuex'
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import PandocView from './views/PandocView.vue'
import Sidebar from './components/Sidebar.vue'

export default {
	name: 'App',

	components: {
		NcContent,
		PandocView,
		Sidebar,
	},

	computed: {
		...mapState([
			'showSidebar',
		]),
	},
}
</script>
