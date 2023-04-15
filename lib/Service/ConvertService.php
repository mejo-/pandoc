<?php

namespace OCA\Pandoc\Service;

use OC\Files\Node\File;
use OC\User\NoUserException;
use OCA\Pandoc\Model\ConvertedFile;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;
use Pandoc\Exceptions\InputFileNotFound;
use Pandoc\Exceptions\LogFileNotWriteable;
use Pandoc\Exceptions\PandocNotFound;
use Pandoc\Exceptions\UnknownInputFormat;
use Pandoc\Exceptions\UnknownOutputFormat;
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
	 * @throws InputFileNotFound
	 * @throws LockedException
	 * @throws LogFileNotWriteable
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 * @throws PandocNotFound
	 * @throws UnknownInputFormat
	 * @throws UnknownOutputFormat
	 */
	public function convertFile(string $userId, int $fileId, string $to = 'plain', string $from = 'gfm'): ConvertedFile {
		$pandoc = new Pandoc();

		$inputFormats = $pandoc->listInputFormats();
		if (!$inputFormats || !in_array($from, $inputFormats)) {
			throw new NotPermittedException('Format not found in input formats: ' . $from);
		}

		// Don't further sanitize output format ($to) as we have to accept paths for LUA writers.

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
		// TODO: only covers markdown files so far
		$fileName = $file->getName() === 'Readme.md'
			? basename(dirname($file->getPath()))
			: basename($file->getName(), '.md');

		return new ConvertedFile($fileName, $pandoc
			->input($fileContent)
			->option('from', $from)
			->option('to', $to)
			->noStandalone()
			->execute());
	}
}
