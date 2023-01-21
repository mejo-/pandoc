<?php

namespace Unit\Service;

use OC\Files\Node\File;
use OC\Files\Node\Folder;
use OCA\Pandoc\Model\ConvertedFile;
use OCA\Pandoc\Service\ConvertService;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;
use PHPUnit\Framework\TestCase;

class ConvertServiceTest extends TestCase {
	private File $file;
	private Folder $userFolder;
	private ConvertService $service;

	protected function setUP(): void {
		$this->file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->getMock();

		$userFolder = $this->getMockBuilder(Folder::class)
			->disableOriginalConstructor()
			->getMock();
		$userFolder->method('getById')
			->willReturn([$this->file]);

		$rootFolder = $this->getMockBuilder(IRootFolder::class)
			->disableOriginalConstructor()
			->getMock();
		$rootFolder->method('getUserFolder')
			->willReturn($userFolder);

		$this->service = new ConvertService($rootFolder);
	}

	public function testConvertFileMarkdownToPlain(): void {
		$fromContent = '# Heading
**bold** text

* list
';
		$toContent = 'Heading

bold text

-   list
';
		$fileName = 'name';
		$convertedFile = new ConvertedFile($fileName, $toContent);
		$this->file->method('getName')
			->willReturn($fileName);
		$this->file->method('getContent')
			->willReturn($fromContent);
		self::assertEquals($convertedFile, $this->service->convertFile('user', 0, 'plain', 'markdown'));
	}

	public function testConvertInvalidInputFormat(): void {
		$fromContent = '';
		$this->expectException(NotPermittedException::class);
		$this->service->convertFile('user', 0, 'plain', 'invalid');
	}
}
