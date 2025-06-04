<?php

namespace OCA\Pandoc\Model;

class ConvertedFile {
	public function __construct(
		private string $name,
		private string $content,
	) {
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void {
		$this->content = $content;
	}
}
