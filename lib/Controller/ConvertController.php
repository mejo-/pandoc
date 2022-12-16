<?php

namespace OCA\Pandoc\Controller;

use OCA\Pandoc\Service\ConvertService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IUserSession;

class ConvertController extends Controller {
	private ConvertService $convertService;
	private IUserSession $userSession;

	public function __construct(string $appName,
								IRequest $request,
								ConvertService $convertService,
								IUserSession $userSession) {
		parent::__construct($appName, $request);
		$this->convertService = $convertService;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int    $fileId
	 * @param string $to
	 * @param string $from
	 *
	 * @return DataResponse
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function convertFile(int $fileId, string $to = 'plain', string $from = 'markdown'): DataResponse {
		$userId = $this->userSession->getUser()->getUID();
		$output = $this->convertService->convertFile($userId, $fileId, $to, $from);

		return new DataResponse([
			'content' => $output,
		]);
	}
}
