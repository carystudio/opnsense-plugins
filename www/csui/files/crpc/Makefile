EXEC = crpc
OBJS = crpc.o md5.o
#CFLAGS += -g

all: $(EXEC)

.c.o:
	$(CC) $(CFLAGS) -c $(CFLAGS) $<

$(EXEC): $(OBJS)
	$(CC) $(CFLAGS) $(OBJS) -o $@

romfs:
	$(ROMFSINST) /sbin/$(EXEC)

clean:
	rm -f $(EXEC) *.elf *.o

install: crpc
	ls -l ${WRKSRC}
	cd ${WRKSRC}
	pwd
	cp crpc ${STAGEDIR}${PREFIX}/bin/
