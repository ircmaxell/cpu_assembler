<?php

declare(strict_types=1);

namespace TinyComputer;
use TinyComputer\MicroCode as M;

class VM {
	
	protected $rom = [];
	protected $instructionSet;
	protected $ram = [];
	protected $pc = 0xC000;
	protected $sc = 0x7FFF;
	protected $counter = 0;
	protected $flags = 0;

	protected $instructionRegister = 0x00;
	protected $indirectionRegister = 0x00;
	protected $registers = [
		Register::RA => 0,
		Register::RB => 0,
		Register::RC => 0,
		Register::RJ1 => 0,
		Register::RJ2 => 0,
	];

	protected $incdec = 0;
	protected $aluResult = 0;

	protected $output = [
		0 => "",
		1 => "",
	];


	protected $dataBus = 0;
	protected $addressBus = 0;

	public function __construct(array $roms, InstructionSet $set, string $program) {
		$this->instructionSet = $set;
		$this->roms = $roms;

		for ($i = 0; $i < strlen($program); $i++) {
			$this->ram[0xC000 + $i] = ord($program[$i]);
		}

	}

	public function run() {
start:
		$ip = (($this->flags & 0x0F) << 11) | ($this->counter << 7) | $this->instructionRegister;

		$this->processABusRead($ip);
		$this->processDBusRead($ip);
		$this->processALURead($ip);

		$this->processABusWrite($ip);
		$this->processDBusWrite($ip);
		$this->processALUWrite($ip);

		$this->debug($ip);

		$this->addressBus = 0;
		$this->dataBus = 0;

		if (c(ord($this->roms['DBUS_ROM'][$ip]) << 16, M::HALT)) {
			return;
		}
	

		$this->tick();
		goto start;
	}


	protected function debug(int $ip) {
		printf(
			"Instruction {\n\tIP = %015b\n\tClock = %02X\n\tInst: %02X (%s)\n}\n", 
			$ip,
			$this->counter,
			$this->instructionRegister,
			$this->instructionSet->instructions[$this->instructionRegister]->name
		);

		printf("PC = %04X\nSC = %04X\nINCDEC = %04X\n", $this->pc, $this->sc, $this->incdec);
		printf(
			"Registers {\n\tA = %02X (%d)\n\tB = %02X (%d)\n\tC = %02X (%d)\n\tJ1 = %02X (%d)\n\tJ2 = %02X (%d)\n}\n",
			$this->registers[Register::RA],
			$this->registers[Register::RA],
			$this->registers[Register::RB],
			$this->registers[Register::RB],
			$this->registers[Register::RC],
			$this->registers[Register::RC],
			$this->registers[Register::RJ1],
			$this->registers[Register::RJ1],
			$this->registers[Register::RJ2],
			$this->registers[Register::RJ2]
		);
		$flag = new Flag($this->flags);
		$flagResult = [];
		if ($flag->isZero()) {
			$flagResult[] = "ZERO";
		}
		if ($flag->isSign()) {
			$flagResult[] = "SIGN";
		}
		if ($flag->isCarry()) {
			$flagResult[] = "CARRY";
		}
		printf (
			"ALU {\n\tResult = %02x (%d)\n\tflags = %02x (%s)\n}\n",
			$this->aluResult,
			$this->aluResult,
			$this->flags,
			implode(", ", $flagResult)
		);
		printf(
			"Bus {\n\tdata = %02X\n\taddress = %04X\n}\n",
			$this->dataBus,
			$this->addressBus
		);
		printf("Secondary Display: \n\t%s\n\t%s\n", $this->output[0], $this->output[1]);
		echo "\n\n";
		usleep(500000);
	}

	protected function processABusRead(int $ip) {
		$code = ord($this->roms['ABUS_ROM'][$ip]);
		switch ($code & 0b11100000) {
			case M::PC_O:
				$this->addressBus = $this->pc;
				break;
			case M::SC_O:
				$this->addressBus = $this->sc;
				break;
			case M::INCDEC_O:
				$this->addressBus = $this->incdec;
				break;
			case M::J_O:
				$this->addressBus = ($this->registers[Register::RJ1] << 8) | $this->registers[Register::RJ2];
				break;
			case M::x7FFF_O:
				$this->addressBus = 0x7FFF;
				break;
			case M::xC000_O:
				$this->addressBus = 0xC000;
				break;
		}
	}

	protected function processABusWrite(int $ip) {
		$code = ord($this->roms['ABUS_ROM'][$ip]);
		switch ($code & 0b00011100) {
			case M::PC_W:
				$this->pc = $this->addressBus;
				break;
			case M::SC_W:
				$this->sc = $this->addressBus;
				break;
			case M::INC_W:
				$this->incdec = ($this->addressBus + 1) % (1<<16);
				break;
			case M::DEC_W:
				$this->incdec = ($this->addressBus - 1) % (1<<16);
				break;
		}
		if (c($code, M::NEXT)) {
			$this->counter = -1;
		}
		if (c($code, M::INST_W)) {
			$this->instructionRegister = $this->dataBus;
		}
	}

	protected function processDBusRead(int $ip) {
		$code = ord($this->roms['DBUS_ROM'][$ip]) << 16;
		if (c($code, M::MEM_O)) {
			$this->dataBus = $this->ram[$this->addressBus];
		}
		if (c($code, M::RIO_X)) {
			$this->dataBus = $this->registers[
				$this->indirectionRegister & 0x0F
			];
		}
	}

	protected function processDBusWrite(int $ip) {
		$code = ord($this->roms['DBUS_ROM'][$ip]) << 16;
		if (c($code, M::J1_W)) {
			$this->registers[Register::RJ1] = $this->dataBus;
		}
		if (c($code, M::J2_W)) {
			$this->registers[Register::RJ2] = $this->dataBus;
		}
		if (c($code, M::MEM_W)) {
			if ($this->addressBus == 0x807E) {
				// append to output line
				$this->appendSecondaryDisplay($this->dataBus);
			} else {
				$this->ram[$this->addressBus] = $this->dataBus;
			}
		}
		if (c($code, M::RI_W)) {
			$this->indirectionRegister = $this->dataBus;
		}
		if (c($code, M::RIW_X)) {
			$this->registers[
				($this->indirectionRegister & 0xF0) >> 4
			] = $this->dataBus;
		}
	}

	protected function appendSecondaryDisplay(int $value) {
		if ($value == 0x0A) {
			$this->output[0] = $this->output[1];
			$this->output[1] = '';
			return;
		}
		if (strlen($this->output[1]) === 16) {
			$this->output[0] = $this->output[1];
			$this->output[1] = '';
		}
		$this->output[1] .= chr($value);
	}

	protected function processALURead(int $ip) {
		$code = ord($this->roms['ALU_ROM'][$ip]) << 8;
		if (c($code, M::ALU_O)) {
			$this->dataBus = $this->aluResult;
		}
		if (c($code, M::ALU_FLAG_O)) {
			$this->dataBus = $this->flags;
		}
	}

	protected function processALUWrite(int $ip) {
		$code = ord($this->roms['ALU_ROM'][$ip]) << 8;
		if (!c($code, M::ALU_W)) {
			return;
		}
		$a = $this->registers[Register::RA];
		switch ($code & (0x11111000 << 8)) {
			case M::ALU_ADD:
				$this->aluResult = $a + $this->dataBus;
				break;
			case M::ALU_SUB:
				$this->aluResult = $this->dataBus - $a;
				break;
			case M::ALU_INC:
				$this->aluResult = $this->dataBus + 1;
				break;
			case M::ALU_DEC:
				$this->aluResult = $this->dataBus - 1;
				break;
			case M::ALU_XOR:
				$this->aluResult = $a ^ $this->dataBus;
				break;
			case M::ALU_NOR:
				$this->aluResult = ~$this->dataBus;
				break;
			case M::ALU_AND:
				$this->aluResult = $a & $this->dataBus;
				break;
			case M::ALU_ZERO:
				$this->aluResult = 0;
				break;
			case M::ALU_OR:
				$this->aluResult = $a | $this->dataBus;
				break;
			case M::ALU_FF:
				$this->aluResult = 0xFF;
				break;
			case M::ALU_SELF:
				$this->aluResult = $this->dataBus;
				break;
		}
		$old = $this->aluResult;
		$this->aluResult = $this->aluResult % 0xFF;
		$this->flags = (0x80 & $this->aluResult ? Flag::SIGN : 0) 
				| (0 === $this->aluResult ? Flag::ZERO : 0) 
				| ($old > 0xFF ? Flag::CARRY : 0);
	}

	public function tick() {
		$this->counter = ($this->counter + 1) % 16;
	}

	public function reset() {
		$this->pc = 0xC000;
		$this->sc = 0x7FFF;
		$this->counter = 0;
	}

}

function m(int $code, int $mask, int $flag) {
	return ($code & $mask) === $flag;
}

function c(int $code, int $flag): bool {
	return ($code & $flag) === $flag;
}