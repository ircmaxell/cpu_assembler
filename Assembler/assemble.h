#include "uthash.h"


typedef struct labelLocation {
	size_t offset;
	UT_hash_handle hh;
} labelLocation;

typedef struct jumpLabelLocation {
	char *label;
	size_t addressOffset;
	jumpLabelLocation *next;
} jumpLabelLocation;

typedef struct assembleContext {
	size_t addressOffset;
	size_t offset;
	char *result;
	labelLocation *labelLocations;
	struct jumpLabelLocations {
		jumpLabelLocation *head;
		jumpLabelLocation *tail;
	} jumpLabelLocations;
} assembleContext;

char *assemble(instructionList *list, size_t *size, unsigned short offset);