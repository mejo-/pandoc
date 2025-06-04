<?php

namespace OCA\Pandoc\Controller;

use OCA\Pandoc\Service\ConvertService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class ConvertController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ConvertService $convertService,
		private IUserSession $userSession,
		private IConfig $config,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	public function convertFile(int $fileId, ?string $to = null, string $from = 'gfm'): DataResponse {
		if (!$to) {
			$to = $this->config->getAppValue('pandoc', 'default_output_format', 'plain');
		}
		$userId = $this->userSession->getUser()->getUID();
		$convertedFile = $this->convertService->convertFile($userId, $fileId, $to, $from);

		return new DataResponse([
			'name' => $convertedFile->getName(),
			'content' => $convertedFile->getContent(),
		]);
	}
}
