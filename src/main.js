import Vue from 'vue'
import App from './App.vue'

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

const app = new Vue({
	el: '#content',
	render: h => h(App),
})

export default app
