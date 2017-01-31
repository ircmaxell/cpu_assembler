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
	unsigned short offset;
	if(argc == 2) {
		if((in = fopen(argv[1], "rb")) != NULL) {
			result = parse(in, &offset);
			fclose(in);
			bytes = assemble(result, &size, offset);
			for (i = 0; i < size; i++) {
				printf(" 0x%02x,", (unsigned char) bytes[i]);
			}
			printf("\n");
		}
	}

	return 0;
}

