<?php

namespace OCA\Pandoc\Service;

use OC\Files\Node\File;
use OC\User\NoUserException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;
use Pandoc\Pandoc;

class ConvertService {
	private IRootFolder $rootFolder;

	public function __construct(IRootFolder $rootFolder) {
		$this->rootFolder = $rootFolder;
	}

	/**
	 * @param string $userId
	 * @param int    $fileId
	 * @param string $to
	 * @param string $from
	 *
	 * @return string
	 * @throws NotPermittedException
	 * @throws NotFoundException
	 * @throws LockedException
	 */
	public function convertFile(string $userId, int $fileId, string $to = 'plain', string $from = 'markdown'): string {
		try {
			$userFolder = $this->rootFolder->getUserFolder($userId);
		} catch (NotPermittedException | NoUserException $e) {
			throw new NotPermittedException($e->getMessage(), 0, $e);
		}

		$files = $userFolder->getById($fileId);

		if (count($files) <= 0 || !($files[0] instanceof File)) {
			throw new NotFoundException('File not found: ' . $fileId);
		}
		$file = $files[0];

		$fileContent = $file->getContent();

		if (!$fileContent) {
			throw new NotFoundException('File has empty content: ' . $fileId);
		}

		$pandoc = new Pandoc();
		return $pandoc
			->from($from)
			->input($file->getContent())
			->to($to)
			->run();
	}
}
