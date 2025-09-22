<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Pandoc;

use OCA\Pandoc\AppInfo\Application;
use OCP\Capabilities\ICapability;
use OCP\IL10N;
use OCP\IURLGenerator;

class Capabilities implements ICapability {
	public function __construct(
		private IURLGenerator $url,
		private IL10N $l10n,
	) {
	}

	public function getCapabilities(): array {
		$conversionEndpoint = $this->url->linkToOCSRouteAbsolute('files.conversionapi.convert');

		$menuItems = [
			[
				'name' => $this->l10n->t('Convert to Markdown (.md)'),
				'url' => $conversionEndpoint . '?' . http_build_query([
					'targetMimeType' => 'text/markdown',
				]),
				'method' => 'POST',
				'params' => ['file_id' => '{fileId}'],
				'filter' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			]
		];

		return [
			'declarativeui' => [
				Application::APP_ID => [
					'context-menu' => $menuItems,
				],
			],
		];
	}
}
