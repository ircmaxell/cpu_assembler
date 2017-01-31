#include <stdlib.h>
#include <stdio.h>
#include "parser.h"
#include "asm.tab.h"

instructionList *makeInstList(instruction *first) {
	instructionList *result = (instructionList*) malloc(sizeof(instructionList) + sizeof(instruction*));
	if (result == NULL) {
		printf("Memory allocation error");
		exit(-1);
	}
	result->size = 1;
	result->elements[0] = first;
	return result;
}

instructionList *makeInstList() {
	instructionList *result = (instructionList*) malloc(sizeof(instructionList));
	if (result == NULL) {
		printf("Memory allocation error");
		exit(-1);
	}
	result->size = 0;
	return result;
}

instructionList *addInstructionToList(instructionList *list, instruction *next) {
	list = (instructionList*) realloc(list, sizeof(instructionList) + (list->size + 1) * sizeof(instruction*));
	if (list == NULL) {
		printf("Memory allocation error");
		exit(-1);
	}
	list->elements[list->size] = next;
	list->size++;
	return list;
}

instruction *makeInst(instructionType type) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	return result;
}

instruction *makeInstReg(instructionType type, registerName a) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;

	result->arg1.type = ARG_REGISTER;
	result->arg1.value.reg = a;

	return result;
}

instruction *makeInstNum(instructionType type, unsigned char a) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	result->arg1.type = ARG_LITERAL;
	result->arg1.value.byte = a;
	return result;
}

instruction *makeInstRegReg(instructionType type, registerName a, registerName b) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	result->arg1.type = ARG_REGISTER;
	result->arg1.value.reg = a;
	result->arg2.type = ARG_REGISTER;
	result->arg2.value.reg = b;
	
	return result;
}

instruction *makeInstRegNum(instructionType type, registerName a, unsigned char b) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	result->arg1.type = ARG_REGISTER;
	result->arg1.value.reg = a;
	result->arg2.type = ARG_LITERAL;
	result->arg2.value.byte = b;
	return result;
}

instruction *makeInstNumNum(instructionType type, unsigned char a, unsigned char b) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	result->arg1.type = ARG_LITERAL;
	result->arg1.value.byte = a;
	result->arg2.type = ARG_LITERAL;
	result->arg2.value.byte = b;
	return result;
}

instruction *makeInstIdentifier(instructionType type, char *label) {
	instruction *result = (instruction*) malloc(sizeof(instruction));
	result->type = type;
	result->arg1.type = ARG_STRING;
	result->arg1.value.string = label;
	return result;
}

void yyerror(const char * message) {
	printf("Syntax error, unexpected '%s'\n", message);
}

