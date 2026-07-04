<template>
	<NcContent app-name="pandoc">
		<PandocView />
		<PandocSidebar v-if="showSidebar" />
	</NcContent>
</template>

<script>
import { mapState } from 'vuex'
import NcContent from '@nextcloud/vue/components/NcContent'
import PandocSidebar from './components/PandocSidebar.vue'
import PandocView from './views/PandocView.vue'

export default {
	name: 'App',

	components: {
		NcContent,
		PandocView,
		PandocSidebar,
	},

	computed: {
		...mapState([
			'showSidebar',
		]),
	},
}
</script>
