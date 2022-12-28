<template>
	<NcContent app-name="pandoc">
		<PandocView />
	</NcContent>
</template>

<script>
import { NcContent } from '@nextcloud/vue'
import PandocView from './views/PandocView.vue'

export default {
	name: 'App',

	components: {
		NcContent,
		PandocView,
	},
}
</script>
