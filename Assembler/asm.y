%{
#include "parser.h"
#include <cstdio>
#include <iostream>

using namespace std;

int yylex();
int yyparse();
extern FILE *yyin;
instructionList *root;
unsigned short offset = 0x0;
%}

%define parse.error verbose

%union {
	char			*sval;
	unsigned char   ival;
	unsigned short  address;
	registerName 	rname;
	instruction 	*inst;
	instructionList *list;
}

%token  DIRECTIVE_OFFSET

%token 	INSTRUCTION_HALT
%token 	INSTRUCTION_MOV 
%token	INSTRUCTION_ADD INSTRUCTION_SUB
%token 	INSTRUCTION_AND INSTRUCTION_OR INSTRUCTION_XOR
%token 	INSTRUCTION_NOT
%token  INSTRUCTION_INC INSTRUCTION_DEC
%token  INSTRUCTION_PUSH INSTRUCTION_POP
%token 	INSTRUCTION_CALL INSTRUCTION_RETURN
%token 	INSTRUCTION_JUMP 
%token  INSTRUCTION_JUMPZ INSTRUCTION_JUMPNZ
%token  INSTRUCTION_JUMPS INSTRUCTION_JUMPNS
%token  INSTRUCTION_JUMPC INSTRUCTION_JUMPNC
%token 	REGISTER_RA REGISTER_RB REGISTER_RC REGISTER_RJ1 REGISTER_RJ2
%token <ival> NUMERIC_LITERAL
%token <address> ADDRESS_LITERAL
%token <sval> IDENTIFIER
%token 	COMMA COLON SEMICOLON

%type <rname> register_name

%type <inst> instruction 
%type <inst> instruction_move 
%type <inst> instruction_alu  
%type <inst> instruction_stack
%type <inst> instruction_jump instruction_jump_direct_short instruction_jump_direct_long instruction_jump_label
%type <inst> label 
%type <inst> halt

%type <list> program



%start program

%%

register_name
	: REGISTER_RA   { $$ = RA; }
	| REGISTER_RB	{ $$ = RB; }
	| REGISTER_RC	{ $$ = RC; }
	| REGISTER_RJ1	{ $$ = RJ1; }
	| REGISTER_RJ2	{ $$ = RJ2; }
	;

label
	: IDENTIFIER COLON 
		{ $$ = makeInstIdentifier(INST_LABEL, $1); }
	;

instruction_stack
	: INSTRUCTION_PUSH register_name SEMICOLON
		{ $$ = makeInstReg(INST_PUSH, $2); }
	| INSTRUCTION_POP register_name SEMICOLON
		{ $$ = makeInstReg(INST_POP, $2); }
	| INSTRUCTION_CALL IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_CALL, $2); }
	| INSTRUCTION_RETURN SEMICOLON
		{ $$ = makeInst(INST_RETURN); }
	;

halt
	: INSTRUCTION_HALT SEMICOLON
		{ $$ = makeInst(INST_HALT); }
	;

instruction
	: instruction_move
	| instruction_alu
	| instruction_jump
	| instruction_stack
	| halt
	;

instruction_jump
	: instruction_jump_label
	| instruction_jump_direct_short
	| instruction_jump_direct_long
	;

instruction_jump_label
	: INSTRUCTION_JUMP IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMP, $2); }
	| INSTRUCTION_JUMPZ IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPZ, $2); }
	| INSTRUCTION_JUMPNZ IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPNZ, $2); }
	| INSTRUCTION_JUMPS IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPS, $2); }
	| INSTRUCTION_JUMPNS IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPNS, $2); }
	| INSTRUCTION_JUMPC IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPC, $2); }
	| INSTRUCTION_JUMPNC IDENTIFIER SEMICOLON
		{ $$ = makeInstIdentifier(INST_JUMPNC, $2); }
	;

instruction_jump_direct_short
	: INSTRUCTION_JUMP NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMP, 0, $2); }
	| INSTRUCTION_JUMPZ NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPZ, 0, $2); }
	| INSTRUCTION_JUMPNZ NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNZ, 0, $2); }
	| INSTRUCTION_JUMPS NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPS, 0, $2); }
	| INSTRUCTION_JUMPNS NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNS, 0, $2); }
	| INSTRUCTION_JUMPC NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPC, 0, $2); }
	| INSTRUCTION_JUMPNC NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNC, 0, $2); }
	;

instruction_jump_direct_long
	: INSTRUCTION_JUMP ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMP, ($2 >> 8) & 0xFF, $2 & 0xFF); }
	| INSTRUCTION_JUMPZ ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPZ, 0, $2); }
	| INSTRUCTION_JUMPNZ ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNZ, 0, $2); }
	| INSTRUCTION_JUMPS ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPS, 0, $2); }
	| INSTRUCTION_JUMPNS ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNS, 0, $2); }
	| INSTRUCTION_JUMPC ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPC, 0, $2); }
	| INSTRUCTION_JUMPNC ADDRESS_LITERAL SEMICOLON
		{ $$ = makeInstNumNum(INST_JUMPNC, 0, $2); }

instruction_alu
	: INSTRUCTION_ADD register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_ADD, $2, $4); }
	| INSTRUCTION_SUB register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_SUB, $2, $4); }
	| INSTRUCTION_AND register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_AND, $2, $4); }
	| INSTRUCTION_OR register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_OR, $2, $4); }
	| INSTRUCTION_XOR register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_XOR, $2, $4); }
	| INSTRUCTION_NOT register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_NOT, $2, $4); }
	| INSTRUCTION_INC register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_INC, $2, $4); }
	| INSTRUCTION_DEC register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_DEC, $2, $4); }
	;

instruction_move
	: INSTRUCTION_MOV register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_MOV, $2, $4); }
	| INSTRUCTION_MOV register_name COMMA NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstRegNum(INST_MOV, $2, $4); }
	;

directive
	: DIRECTIVE_OFFSET ADDRESS_LITERAL
		{ offset = $2; }
	;

program
	: instruction
		{ $$ = makeInstList($1); root = $$; }
	| label
		{ $$ = makeInstList($1); root = $$; }
	| directive
		{ $$ = makeInstList(); root = $$; }
	| program instruction
		{ $$ = addInstructionToList($1, $2); root = $$; }
	| program label
		{ $$ = addInstructionToList($1, $2); root = $$; }
	| program directive
		{ $$ = $1; }
	;
%%

instructionList *parse(FILE *in, unsigned short *rel_offset) {
	yyin = in;
	do {
		yyparse();
	} while (!feof(yyin));
	*rel_offset = offset;
	return root;
}