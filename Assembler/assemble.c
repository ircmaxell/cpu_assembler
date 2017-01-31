#include <cstdio>
#include <cstdlib>
#include "parser.h"
#include "assemble.h"
#include "uthash.h"
#include "assert.h"

static void assembleInstruction(instruction *inst, assembleContext *context);
static void assembleALU(instruction *inst, assembleContext *context);
static void assembleJump(instruction *inst, assembleContext *context);
static void assembleMov(instruction *inst, assembleContext *context);
static void assemblePush(instruction *inst, assembleContext *context);
static void assemblePop(instruction *inst, assembleContext *context);
static void assembleCall(instruction *inst, assembleContext *context);
static void assembleReturn(instruction *inst, assembleContext *context);
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
	context->result[(context->offset)++] = (unsigned char) (0xFF & inst->arg2.value.byte);



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
		case INST_SUB:
		case INST_AND:
		case INST_OR:
		case INST_XOR:
		case INST_NOT:
		case INST_INC:
		case INST_DEC:
			assembleALU(inst, context);
			break;
		case INST_MOV:
			assembleMov(inst, context);
			break;
		case INST_PUSH:
			assemblePush(inst, context);
			break;
		case INST_POP:
			assemblePop(inst, context);
			break;
		case INST_CALL:
			assembleCall(inst, context);
			break;
		case INST_RETURN:
			assembleReturn(inst, context);
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

static void assembleALU(instruction *inst, assembleContext *context) {
	unsigned char encodedInstruction = 0x18; // 0 op
	switch (inst->type) {
		case INST_ADD:
			encodedInstruction = 0x10;
			break;
		case INST_SUB:
			encodedInstruction = 0x11;
			break;
		case INST_AND:
			encodedInstruction = 0x12;
			break;
		case INST_OR:
			encodedInstruction = 0x13;
			break;
		case INST_XOR:
			encodedInstruction = 0x14;
			break;
		case INST_NOT:
			encodedInstruction = 0x17;
			break;
		case INST_INC:
			encodedInstruction = 0x15;
			break;
		case INST_DEC:
			encodedInstruction = 0x16;
			break;
	}
	switch (TYPE_PAIR(inst->arg1.type, inst->arg2.type)) {
		case TYPE_PAIR(ARG_REGISTER, ARG_REGISTER):
			ASSEMBLE_REG_PAIR(encodedInstruction);
			break;
		case TYPE_PAIR(ARG_REGISTER, ARG_LITERAL):
			ASSEMBLE_WRITE_LITERAL(encodedInstruction + 0x10);
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
			context->result[(context->offset)++] = 0x30;
			break;
		case INST_JUMPZ:
			context->result[(context->offset)++] = 0x32;
			break;
		case INST_JUMPNZ:
			context->result[(context->offset)++] = 0x33;
			break;
		case INST_JUMPS:
			context->result[(context->offset)++] = 0x34;
			break;
		case INST_JUMPNS:
			context->result[(context->offset)++] = 0x35;
			break;
		case INST_JUMPC:
			context->result[(context->offset)++] = 0x36;
			break;
		case INST_JUMPNC:
			context->result[(context->offset)++] = 0x37;
			break;
	}
	
	switch (inst->arg1.type) {
		case ARG_STRING:
			addJumpLabelLocation(context, inst->arg1.value.string);
			context->result[(context->offset)++] = 0x00;
			context->result[(context->offset)++] = 0x00;
			break;
		case ARG_LITERAL:
			assert(inst->arg2.type == ARG_LITERAL);
			context->result[(context->offset)++] = inst->arg1.value.byte;
			context->result[(context->offset)++] = inst->arg2.value.byte;
			break;
		default:
			printf("Jump instruction not built for %d\n", inst->arg1.type);
			exit(-1);
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

static void assembleCall(instruction *inst, assembleContext *context) {
	size_t returnAddress = context->offset + 13;
	reallocResult(context, 13);
	context->result[(context->offset)++] = 0x03; // LOAD-I
	context->result[(context->offset)++] = REGISTER_WO(RC);
	context->result[(context->offset)++] = (unsigned char) ((returnAddress >> 8) & 0xFF);
	context->result[(context->offset)++] = 0x07; // push
	context->result[(context->offset)++] = REGISTER_RO(RC); // push
	context->result[(context->offset)++] = 0x03; // LOAD-I
	context->result[(context->offset)++] = REGISTER_WO(RC);
	context->result[(context->offset)++] = (unsigned char) (returnAddress & 0xFF);
	context->result[(context->offset)++] = 0x07; // push
	context->result[(context->offset)++] = REGISTER_RO(RC); // push
	context->result[(context->offset)++] = 0x30;
	addJumpLabelLocation(context, inst->arg1.value.string);
	context->result[(context->offset)++] = 0x00;
	context->result[(context->offset)++] = 0x00;
}

static void assembleReturn(instruction *inst, assembleContext *context) {
	reallocResult(context, 5);
	context->result[(context->offset)++] = 0x08; // pop
	context->result[(context->offset)++] = REGISTER_WO(RJ2); // push
	context->result[(context->offset)++] = 0x08; // pop
	context->result[(context->offset)++] = REGISTER_WO(RJ1); // push
	context->result[(context->offset)++] = 0x31; // jump-indirect
}

static void assemblePush(instruction *inst, assembleContext *context) {
	switch (inst->arg1.type) {
		case ARG_REGISTER:
			reallocResult(context, 2);
			context->result[(context->offset)++] = 0x07;
			context->result[(context->offset)++] = REGISTER_RO(inst->arg1.value.reg);	
			break;
		default:
			printf("PushPop instruction not built for %d\n", inst->arg1.type);
			exit(-1);
	}
}

static void assemblePop(instruction *inst, assembleContext *context) {
	switch (inst->arg1.type) {
		case ARG_REGISTER:
			reallocResult(context, 2);
			context->result[(context->offset)++] = 0x08;
			context->result[(context->offset)++] = REGISTER_WO(inst->arg1.value.reg);
			break;
		default:
			printf("Pop instruction not built for %d\n", inst->arg1.type);
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

