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
{int_const}		{ yylval.ival = (unsigned char) atoi(yytext); return NUMERIC_LITERAL; }
{string}		{ yylval.sval = strdup(yytext); return IDENTIFIER; }
.				{ yyerror(yytext); }
