<?php

return [
	'routes' => [
		// Convert controller
		['name' => 'convert#convertFile', 'url' => '/convertFile', 'verb' => 'GET',
			'requirements' => ['fileId' => '\d+']],

		// Default route (Vue.js frontend)
		['name' => 'start#index', 'url' => '/', 'verb' => 'GET'],
	]
];
