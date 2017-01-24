#include "parser.h"
#include "assemble.h"

static void debug(instructionList *list) {
	printf("List was created\n");
}

int main(int argc, char **argv) {
	FILE *in;
	instructionList *result;
	char *bytes;
	size_t i;
	size_t size;
	if(argc == 2) {
		if((in = fopen(argv[1], "rb")) != NULL) {
			result = parse(in);
			fclose(in);
			bytes = assemble(result, &size);
			printf("Result of %zu instructions (%zu bytes):", result->size, size);
			for (i = 0; i < size; i++) {
				printf(" 0x%02x", bytes[i]);
			}
			printf("\n");
		}
	}

	return 0;
}

