<?php

declare(strict_types=1);

namespace OCA\Pandoc\Listeners;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

/** @template-implements IEventListener<Event|BeforeTemplateRenderedEvent> */
class CollectivesLoadAdditionalScriptsListener implements IEventListener {
	public function handle(Event $event): void {
		Util::addScript('pandoc', 'pandoc-collectives');
	}
}
