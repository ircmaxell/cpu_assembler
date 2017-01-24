#include <cstdio>
#include <cstdlib>
#include "parser.h"
#include "assemble.h"
#include "uthash.h"

static void assembleInstruction(instruction *inst, assembleContext *context);
static void assembleAdd(instruction *inst, assembleContext *context);
static void assembleJump(instruction *inst, assembleContext *context);
static void assembleMov(instruction *inst, assembleContext *context);
static void addJumpLabelLocation(assembleContext *context, char* label);
static void writeJumpLabelLocations(assembleContext *context);
static void reallocResult(assembleContext *context, size_t size);

#define TYPE_PAIR(a, b) (a << 8 | b)

#define REGISTER_RW(read_, write_) (REGISTER_RO(read_) | REGISTER_WO(write_))
#define REGISTER_RO(read_) (read_ & 0x0F)
#define REGISTER_WO(write_) ((write_ << 4) & 0xF0)

#define ASSEMBLE_REG_PAIR(inst_) \
	reallocResult(context, 2); 	\
	context->result[(context->offset)++] = (inst_);				\
	context->result[(context->offset)++] = REGISTER_RW(inst->arg2.value.reg, inst->arg1.value.reg);

#define ASSEMBLE_WRITE_LITERAL(inst_) \
	reallocResult(context, 3);				 \
	context->result[(context->offset)++] = (inst_);							 \
	context->result[(context->offset)++] = REGISTER_WO(inst->arg1.value.reg); \
	context->result[(context->offset)++] = inst->arg2.value.byte;



char *assemble(instructionList *list, size_t *size) {
	assembleContext context = {
		0,
		(char *) malloc(sizeof(char)),
		NULL,
		NULL,
		NULL,
	};

	size_t i;
	for (i = 0; i < list->size; i++) {
		assembleInstruction(list->elements[i], &context);
	}
	writeJumpLabelLocations(&context);
	context.result[context.offset++] = 0;
	*size = context.offset;
	return context.result;
}

static void assembleInstruction(instruction *inst, assembleContext *context) {
	switch(inst->type) {
		case INST_HALT:
			reallocResult(context, 1);
			context->result[context->offset++] = 0x00;
			break;
		case INST_ADD:
			assembleAdd(inst, context);
			break;
		case INST_MOV:
			assembleMov(inst, context);
			break;
		case INST_JUMP:
		case INST_JUMPZ:
		case INST_JUMPNZ:
		case INST_JUMPS:
		case INST_JUMPNS:
		case INST_JUMPC:
		case INST_JUMPNC:
			assembleJump(inst, context);
			break;
		case INST_LABEL: {
			char *key = inst->arg1.value.string;
			size_t size = strlen(key);
			labelLocation *location = NULL;
			HASH_FIND(hh, context->labelLocations, key, size, location);
			if (location != NULL) {
				printf("Multiple identical labels for '%s'\n", key);
				exit(-1);
			}
			location = (labelLocation*) malloc(sizeof(labelLocation));
			location->offset = context->offset;
			HASH_ADD_KEYPTR(hh, context->labelLocations, key, size, location);
			break;
		};
		default:
			printf("Assembly instruction not built for %d\n", inst->type);
			exit(-1);
	}
}

static void assembleAdd(instruction *inst, assembleContext *context) {
	switch (TYPE_PAIR(inst->arg1.type, inst->arg2.type)) {
		case TYPE_PAIR(ARG_REGISTER, ARG_REGISTER):
			ASSEMBLE_REG_PAIR(0x10);
			break;
		default:
			printf("Mov instruction not built for %d, %d\n", inst->arg1.type, inst->arg2.type);
			exit(-1);
	}
}

static void assembleJump(instruction *inst, assembleContext *context) {
	reallocResult(context, 3);
	switch (inst->type) {
		case INST_JUMP:
			context->result[(context->offset)++] = 0x20;
			break;
		case INST_JUMPZ:
			context->result[(context->offset)++] = 0x22;
			break;
		case INST_JUMPNZ:
			context->result[(context->offset)++] = 0x23;
			break;
		case INST_JUMPS:
			context->result[(context->offset)++] = 0x24;
			break;
		case INST_JUMPNS:
			context->result[(context->offset)++] = 0x25;
			break;
		case INST_JUMPC:
			context->result[(context->offset)++] = 0x26;
			break;
		case INST_JUMPNC:
			context->result[(context->offset)++] = 0x27;
			break;
	}
	
	switch (inst->arg1.type) {
		case ARG_STRING:
			addJumpLabelLocation(context, inst->arg1.value.string);
			context->result[(context->offset)++] = 0x00;
			context->result[(context->offset)++] = 0x00;
			break;
	}
}

static void assembleMov(instruction *inst, assembleContext *context) {
	switch (TYPE_PAIR(inst->arg1.type, inst->arg2.type)) {
		case TYPE_PAIR(ARG_REGISTER, ARG_REGISTER):
			ASSEMBLE_REG_PAIR(0x01);
			break;
		case TYPE_PAIR(ARG_REGISTER, ARG_LITERAL):
			ASSEMBLE_WRITE_LITERAL(0x03);
			break;
		default:
			printf("Mov instruction not built for %d, %d\n", inst->arg1.type, inst->arg2.type);
			exit(-1);
	}
}

static void addJumpLabelLocation(assembleContext *context, char* label) {
	jumpLabelLocation *location = (jumpLabelLocation*) malloc(sizeof(jumpLabelLocation));
	location->label = label;
	location->addressOffset = context->offset;
	location->next = NULL;
	if (context->jumpLabelLocations.head != NULL) {
		context->jumpLabelLocations.tail->next = location;
	} else {
		context->jumpLabelLocations.head = location;
	}
	context->jumpLabelLocations.tail = location;
}

static void writeJumpLabelLocations(assembleContext *context) {
	jumpLabelLocation *location = context->jumpLabelLocations.head;
	labelLocation *label = NULL;
	while (location != NULL) {
		HASH_FIND(hh, context->labelLocations, location->label, strlen(location->label), label);
		if (label == NULL) {
			printf("Could not find label for jump '%s'\n", location->label);
			exit(-1);
		}
		context->result[location->addressOffset] = (unsigned char) ((label->offset >> 8) & 0xFF);
		context->result[location->addressOffset + 1] = (unsigned char) (label->offset & 0xFF);
		location = location->next;
	}
}

static void reallocResult(assembleContext *context, size_t size) {
	context->result = (char*) realloc(context->result, context->offset + size + 1);
	if (context->result == NULL) {
		printf("Out of memory");
		exit(-1);
	}
}

