<template>
	<NcContent app-name="pandoc">
		<PandocView />
	</NcContent>
</template>

<script>
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'
import PandocView from './views/PandocView.vue'

export default {
	name: 'App',

	components: {
		NcContent,
		PandocView,
	},
}
</script>
