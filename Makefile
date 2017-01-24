CC=g++

CFLAGS += 
CINCLUDES += 

program := lex.yy.c asm.tab.c parser.c assemble.c main.c

compile: $(program) test.asm
	$(CC) $(CFLAGS) $(program) $(CINCLUDES) -ggdb -lfl -o test

lex.yy.c: asm.l
	flex asm.l

asm.tab.c: asm.y
	bison -d asm.y

test: compile
	./test test.asm

clean:
	rm lex.yy.c
	rm asm.tab.*
	rm test

