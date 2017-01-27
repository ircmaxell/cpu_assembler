/* TinyComputerCompiler */
/* asm.lex */

%{
#include "parser.h"
#include "asm.tab.h"

#define YY_DECL int yylex()
%}

%option noyywrap
%option pointer
%option caseless

digit       [0-9]
int_const   [0-9]+
string      [a-zA-Z][a-zA-Z0-9]*
hex_literal 0x[a-fA-F0-9]+
comment     "//"[^\n]*
whitespace	[ \n\r\t]*

%%

{comment}		{}
{whitespace}	{}

"MOV"			{ return INSTRUCTION_MOV; }
"ADD"			{ return INSTRUCTION_ADD; }
"SUB"			{ return INSTRUCTION_SUB; }
"AND"			{ return INSTRUCTION_AND; }
"OR"			{ return INSTRUCTION_OR; }
"XOR"			{ return INSTRUCTION_XOR; }
"NOT"			{ return INSTRUCTION_NOT; }
"INC"			{ return INSTRUCTION_INC; }
"DEC"			{ return INSTRUCTION_DEC; }
"PUSH"			{ return INSTRUCTION_PUSH; }
"POP"			{ return INSTRUCTION_POP; }
"CALL"          { return INSTRUCTION_CALL; }
"RETURN"        { return INSTRUCTION_RETURN; }

"JMP"			{ return INSTRUCTION_JUMP; }
"JMPZ"			{ return INSTRUCTION_JUMPZ; }
"JMPNZ"			{ return INSTRUCTION_JUMPNZ; }
"JMPS"			{ return INSTRUCTION_JUMPS; }
"JMPNS"			{ return INSTRUCTION_JUMPNS; }
"JMPC"			{ return INSTRUCTION_JUMPC; }
"JMPNC"			{ return INSTRUCTION_JUMPNC; }
"HALT"			{ return INSTRUCTION_HALT; }

"RA"			{ return REGISTER_RA; }
"RB"			{ return REGISTER_RB; }
"RC"			{ return REGISTER_RC; }
"RJ1"			{ return REGISTER_RJ1; }
"RJ2"			{ return REGISTER_RJ2; }
","				{ return COMMA; }
";"				{ return SEMICOLON; }
":"				{ return COLON; }
{hex_literal}	{ yylval.ival = (unsigned char) strtol(yytext, NULL, 0); return NUMERIC_LITERAL; }
{int_const}		{ yylval.ival = (unsigned char) atoi(yytext); return NUMERIC_LITERAL; }
{string}		{ yylval.sval = strdup(yytext); return IDENTIFIER; }
.				{ yyerror(yytext); }
