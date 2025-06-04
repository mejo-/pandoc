<?php

namespace OCA\Pandoc\Model;

class ConvertedFile {
	public function __construct(
		private string $name,
		private string $content,
	) {
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function getContent(): string {
		return $this->content;
	}

	public function setContent(string $content): void {
		$this->content = $content;
	}
}
