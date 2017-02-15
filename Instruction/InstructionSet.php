<?php

declare(strict_types=1);

namespace TinyComputer;

class InstructionSet {
	public $instructions = [];
	public $namedInstructions = [];

	public function __construct() {
		$this->setAllInstructionsToNoOp();
		$this->loadInstructions();
	}

	protected function addInstructions(Instruction ...$instructions) {
		foreach ($instructions as $instruction) {
			$this->instructions[$instruction->code] = $instruction;
			$this->namedInstructions[$instruction->name] = $instruction;
		}
	}

	protected function setAllInstructionsToNoOp() {
		$this->namedInstructions['NOOP'] = new Instruction\NOOP;
		$this->instructions = array_fill(0, 0x7F, $this->namedInstructions['NOOP']);
	}

	protected function loadInstructions() {
		$it = new \DirectoryIterator(__DIR__ . '/Instruction');
		foreach ($it as $file) {
			if (!$file->isFile() || $file->getExtension() !== 'php') {
				continue;
			}
			$name = $file->getBaseName('.php');
			$class = __NAMESPACE__ . "\\Instruction\\$name";
			$this->addInstructions(... $class::factory());
		}
	}

}