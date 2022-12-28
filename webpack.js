const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	pandoc: path.join(__dirname, 'src', 'main.js'),
	collectives: path.join(__dirname, 'src', 'collectives.js'),
}

module.exports = webpackConfig
