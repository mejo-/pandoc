import Vue from 'vue'
import Vuex, { Store } from 'vuex'

Vue.use(Vuex)

export default new Store({
	state: {
		convertedFiles: [],
		showSidebar: false,
	},

	getters: {
		filteredConvertedFiles: (state) => {
			return state.convertedFiles.filter(f => f.hide !== true)
		},

		displayedFileContents: (state, getters) => {
			return getters.filteredConvertedFiles.map(f => f.content)
				.join('\n\n')
		},
	},

	mutations: {
		closeSidebar: (state) => {
			state.showSidebar = false
		},

		toggleSidebar: (state) => {
			state.showSidebar = !state.showSidebar
		},

		addConvertedFile: (state, convertedFile) => {
			state.convertedFiles.push(convertedFile)
		},

		toggleHideFile: (state, fileId) => {
			state.convertedFiles.find(f => f.fileId === fileId).hide
				= !state.convertedFiles.find(f => f.fileId === fileId).hide
		},
	},
})
