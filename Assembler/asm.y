%{
#include "parser.h"
#include <cstdio>
#include <iostream>

using namespace std;

int yylex();
int yyparse();
extern FILE *yyin;
instructionList *root;
%}

%define parse.error verbose

%union {
	char			*sval;
	unsigned char   ival;
	registerName 	rname;
	instruction 	*inst;
	instructionList *list;
}

%token 	INSTRUCTION_HALT
%token 	INSTRUCTION_MOV 
%token	INSTRUCTION_ADD INSTRUCTION_SUB
%token 	INSTRUCTION_AND INSTRUCTION_OR INSTRUCTION_XOR
%token 	INSTRUCTION_NOT
%token  INSTRUCTION_INC INSTRUCTION_DEC
%token  INSTRUCTION_PUSH INSTRUCTION_POP
%token 	INSTRUCTION_JUMP 
%token  INSTRUCTION_JUMPZ INSTRUCTION_JUMPNZ
%token  INSTRUCTION_JUMPS INSTRUCTION_JUMPNS
%token  INSTRUCTION_JUMPC INSTRUCTION_JUMPNC
%token 	REGISTER_RA REGISTER_RB REGISTER_RC REGISTER_RJ1 REGISTER_RJ2
%token <ival> NUMERIC_LITERAL
%token <sval> IDENTIFIER
%token 	COMMA COLON SEMICOLON

%type <rname> register_name
%type <inst> instruction instruction_move instruction_alu instruction_jump label halt
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

halt
	: INSTRUCTION_HALT SEMICOLON
		{ $$ = makeInst(INST_HALT); }
	;

instruction
	: instruction_move
	| instruction_alu
	| instruction_jump
	| halt
	;

instruction_jump
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
	| INSTRUCTION_PUSH register_name SEMICOLON
		{ $$ = makeInstReg(INST_PUSH, $2); }
	| INSTRUCTION_POP register_name SEMICOLON
		{ $$ = makeInstReg(INST_POP, $2); }
	;

instruction_move
	: INSTRUCTION_MOV register_name COMMA register_name SEMICOLON 
		{ $$ = makeInstRegReg(INST_MOV, $2, $4); }
	| INSTRUCTION_MOV register_name COMMA NUMERIC_LITERAL SEMICOLON
		{ $$ = makeInstRegNum(INST_MOV, $2, $4); }
	;

program
	: instruction
		{ $$ = makeInstList($1); root = $$; }
	| label
		{ $$ = makeInstList($1); root = $$; }
	| program instruction
		{ $$ = addInstructionToList($1, $2); root = $$; }
	| program label
		{ $$ = addInstructionToList($1, $2); root = $$; }
			
	;
%%

instructionList *parse(FILE *in) {
	yyin = in;
	do {
		yyparse();
	} while (!feof(yyin));
	return root;
}