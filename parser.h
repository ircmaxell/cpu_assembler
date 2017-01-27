#include <stdio.h>

typedef enum registerName {
	RA,
	RB,
	RC,
	RJ1,
	RJ2
} registerName;

#define RJ 5
#define RPC 6
#define RINCDEC 7
#define RINC 8
#define RDEC 9
#define RALU 10
#define RALU_RESULT 11
#define RALU_FLAGS 12
#define RMEMORY 13

typedef enum instructionType {
	INST_HALT,
	INST_ADD,
	INST_SUB,
	INST_AND,
	INST_OR,
	INST_XOR,
	INST_NOT,
	INST_INC,
	INST_DEC,
	INST_MOV,
	INST_PUSH,
	INST_POP,
	INST_JUMP,
	INST_JUMPZ,
	INST_JUMPNZ,
	INST_JUMPS,
	INST_JUMPNS,
	INST_JUMPC,
	INST_JUMPNC,
	INST_LABEL,
} instructionType;

typedef enum argumentType {
	ARG_LITERAL,
	ARG_REGISTER,
	ARG_STRING,
} argumentType;

typedef struct argument {
	argumentType type;
	union value {
		unsigned char 	byte;
		registerName 	reg;
		char 			*string;
	} value;
} argument;

typedef struct instruction {
	instructionType type;
	argument arg1;
	argument arg2;
	argument arg3;
	argument arg4;
} instruction;

typedef struct instructionList {
	size_t size;
	instruction *elements[0];
} instructionList;

instructionList *makeInstList(instruction *first);
instructionList *addInstructionToList(instructionList *list, instruction *next);

instruction *makeInst(instructionType type);
instruction *makeInstReg(instructionType type, registerName a);
instruction *makeInstNum(instructionType type, unsigned char a);
instruction *makeInstRegReg(instructionType type, registerName a, registerName b);
instruction *makeInstRegNum(instructionType type, registerName a, unsigned char b);
instruction *makeInstIdentifier(instructionType type, char *label);

void 			yyerror(const char*);
instructionList *parse(FILE *in);
