<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Pandoc;

use OCP\AppFramework\Services\IAppConfig;
use OCP\Files\Conversion\ConversionMimeProvider;
use OCP\Files\Conversion\IConversionProvider;
use OCP\Files\File;
use OCP\IBinaryFinder;
use OCP\IL10N;
use RuntimeException;

class ConversionProvider implements IConversionProvider {
	private ?array $typesTo = null;
	private ?array $typesFrom = null;

	public function __construct(
		private IL10N $l10n,
		private IAppConfig $appConfig,
		private IBinaryFinder $binaryFinder,
	) {
	}

	private function getPDFEngine(): string {
		$pdfEngine = $this->appConfig->getAppValueString('pdf_engine', '');
		if ($pdfEngine === '') {
			$pdfLatexBinary = $this->binaryFinder->findBinaryPath('pdflatex');
			if ($pdfLatexBinary !== false) {
				$pdfEngine = $pdfLatexBinary;
				$this->appConfig->setAppValueString('pdf_engine', $pdfEngine);
			}
		}
		return $pdfEngine;
	}

	public function getSupportedMimeTypes(): array {
		$types = [];
		foreach ($this->getPandocTypesFrom() as $mimeType => $input) {
			foreach ($this->getPandocTypesTo() as $output) {
				$types[] = new ConversionMimeProvider(
					$mimeType,
					$output['mime'],
					$output['extension'],
					$output['name'],
				);
			}
		}
		return $types;
	}

	public function convertFile(File $file, string $targetMimeType): mixed {
		$fileContent = $file->getContent();

		$from = $this->getPandocForMime($file->getMimeType(), false);
		$to = $this->getPandocForMime($targetMimeType, true);

		if ($from === null || $to === null) {
			throw new RuntimeException('Invalid source or target file format for pandoc conversion');
		}

		$pandoc = new \Pandoc\Pandoc;
		$pandoc
			->input($fileContent)
			->option('from', $from)
			->option('to', $to)
			->noStandalone();

		if ($this->getPDFEngine() !== '') {
			$pandoc->option('pdf-engine', $this->getPDFEngine());
		}

		return $pandoc
			->execute();
	}

	private function getPandocTypesFrom(): array {
		if ($this->typesFrom === null) {
			$this->typesFrom = [
				'text/markdown' => [
					'mime' => 'text/markdown',
					'pandoc' => 'gfm',
					'extension' => 'md',
					'name' => $this->l10n->t('Markdown (.md)'),
				],
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => [
					'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'pandoc' => 'docx',
					'extension' => 'docx',
					'name' => $this->l10n->t('Document (.docx)'),
				],
				'application/msword' => [
					'mime' => 'application/msword',
					'pandoc' => 'doc',
					'extension' => 'doc',
					'name' => $this->l10n->t('Word (.doc)'),
				],
				'text/html' => [
					'mime' => 'text/html',
					'pandoc' => 'html',
					'extension' => 'html',
					'name' => $this->l10n->t('HTML (.html)'),
				],
				'text/rtf' => [
					'mime' => 'text/rtf',
					'pandoc' => 'rtf',
					'extension' => 'rtf',
					'name' => $this->l10n->t('RTF (.rtf)'),
				],
				'text/plain' => [
					'mime' => 'text/plain',
					'pandoc' => 'txt',
					'extension' => 'txt',
					'name' => $this->l10n->t('Text (.txt)'),
				],
			];
		}

		return $this->typesFrom;
	}

	private function getPandocTypesTo(): array {
		if ($this->typesTo === null) {
			$this->typesTo = $this->getPandocTypesFrom();

			$this->typesTo['text_asciidoc'] = [
				'mime' => 'text/asciidoc',
				'pandoc' => 'asciidoc',
				'extension' => 'asciidoc',
				'name' => $this->l10n->t('Asciidoc (.asciidoc)'),
			];
			if ($this->getPDFEngine() !== '') {
				$this->typesTo['application/pdf'] = [
					'mime' => 'application/pdf',
					'pandoc' => 'pdf',
					'extension' => 'pdf',
					'name' => $this->l10n->t('PDF (.pdf)'),
				];
			}
		}

		return $this->typesTo;
	}

	private function getPandocForMime(string $mimeType, bool $targetMime): ?string {
		$type = $targetMime
			? $this->getPandocTypesTo()[$mimeType] ?? null
			: $this->getPandocTypesFrom()[$mimeType] ?? null;
		if ($type === null) {
			return null;
		}

		return $type['pandoc'] ?? null;
	}
}
