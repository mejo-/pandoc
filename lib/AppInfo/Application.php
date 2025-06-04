<?php

declare(strict_types=1);

namespace OCA\Pandoc\AppInfo;

use OCA\Collectives\Events\CollectivesLoadAdditionalScriptsEvent;
use OCA\Pandoc\Listeners\CollectivesLoadAdditionalScriptsListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'pandoc';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';
		$context->registerEventListener(CollectivesLoadAdditionalScriptsEvent::class, CollectivesLoadAdditionalScriptsListener::class);
	}

	public function boot(IBootContext $context): void {
		// Nothing to do
	}
}
