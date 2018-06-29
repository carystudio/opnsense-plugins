/* cprc: carystudio reverse proxy client.
 *
    Copyright (C) 2012-7 Carystudio; Heimi.Li <xxxx@carystudio.com>
    Copyright (C) 2012-7 Carystudio; Frankie Zha <xxxx@carystudio.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
*/

#include "crpc.h"

fd_set rfds;
int maxfd = 0;
int sockfd = 0, clientfd = 0;

//#define FD_SETSIZE 1024
#define FD_SIZE 128
int pauses[FD_SIZE] = {0};
int conns[FD_SIZE] = {0};
time_t timeStamp=0, timeRead=0;
size_t upBw = 102400;//100KByte/s
size_t pauseBw = 10240;//10KByte/s
size_t total_sec = 0, total_pause[FD_SIZE] = {0};

#if 1
#define echo(fmt, args...) do { \
	if (idebug){ printf("(%s:%d)=> " fmt, __FUNCTION__, __LINE__, ## args); } \
}while(0)

#else
#define echo(fmt, args...) do { \
	if (idebug){ \
		char logfile[128]={0};sprintf(logfile, "logs/%s.log", deviceName);\
		FILE *fp=fopen(logfile, "a");\
		if(fp) { \
			fprintf(fp, "[%s:%d]=> " fmt,__FUNCTION__, __LINE__, ##args); \
		} \
		fclose(fp);\
	} \
}while(0)
#endif

inline int hex(char *xx,int len){
	if (!idebug)
		return 0;
	int i=0;
	printf("buf:");
	for(i=0;i<len;i++)
	{
		unsigned char c = xx[i]; // must use unsigned char to print >128 value
		if( c< 16)
		{
			printf("0%x", c);
		}
		else
		{
			printf("%x", c);
		}
	}
	printf("\r\n");
	return 0;
}

void register_pid() {
	FILE * fh;
	fh = fopen(CRPC_PID, "w");
	assert(fh);
	fprintf(fh, "%d", getpid());
	fclose(fh);
}

static sigjmp_buf jmpbuf;
static void alarm_func()
{
	echo("timeout!!!\n");
	siglongjmp(jmpbuf, 1);
}

int gngetaddrinfo(char *HostName, struct sockaddr_in *sockaddr, int timeout)
{
	signal(SIGALRM, alarm_func);
	if(sigsetjmp(jmpbuf, 1) != 0)
	{
		alarm(0);//timout
		signal(SIGALRM, SIG_IGN);
		return 1;
	}

	alarm(timeout);//setting alarm

	struct addrinfo *answer, hint, *curr;
	char ipstr[16];   
	bzero(&hint, sizeof(hint));
	hint.ai_family = AF_INET;
	hint.ai_socktype = SOCK_STREAM;

	//int getaddrinfo( const char *hostname, const char *service, const struct addrinfo *hints, struct addrinfo **result );
	int ret = getaddrinfo(HostName, NULL, &hint, &answer);
	if (ret != 0) {
		fprintf(stderr,"getaddrinfo: %s\n", gai_strerror(ret));
		alarm(0);//timout
		signal(SIGALRM, SIG_IGN);
		return 1;
	}

	memcpy(sockaddr, (struct sockaddr_in *)(answer[0].ai_addr), sizeof(struct sockaddr_in));

#if 0//debug
	for (curr = answer; curr != NULL; curr = curr->ai_next) 
	{
		inet_ntop(AF_INET,
		&(((struct sockaddr_in *)(curr->ai_addr))->sin_addr),
		ipstr, 16);
		printf("%s\n", ipstr);
	}
#endif
	freeaddrinfo(answer);

	signal(SIGALRM, SIG_IGN);

	return 0;
}

void addLocalConn(int fd, unsigned int connid)
{
	//echo("add fd[%d] - connid[%d].\n", fd, conns[fd]);
	total_pause[fd]=0;
	pauses[fd]=0;
	conns[fd] = ntohl(connid);
}

void delLocalConn(int fd)
{
	//echo("del fd[%d] - connid[%d].\n", fd, conns[fd]);
	conns[fd] = -1;
	pauses[fd]=0;
	total_pause[fd]=0;
	close(fd);
}

int getLocalFd(unsigned int connid)
{
	int i;
	for ( i=sockfd+1; i<FD_SIZE; i++ )
	{
		if (conns[i]==ntohl(connid))
		{
			return i;
		}
	}
	return 0;
}

//检测是否断开
inline int check_sock(int sock)
{
    int error=-1;
    socklen_t len;
    len = sizeof(error);
    getsockopt(sock, SOL_SOCKET, SO_ERROR, (char*)&error, &len);
    return error;
}

int getDevMac(char *ifname, char *devmac)
{
#ifdef  __FreeBSD__
	struct ifreq *ifrp,ifr;
	struct ifconf ifc;
	char buffer[720],name[16];
	int socketfd,error,flags,len,space=0;
	unsigned char *ptr;
	ifc.ifc_len=sizeof(buffer);
	len=ifc.ifc_len;
	ifc.ifc_buf=buffer;

	socketfd=socket(AF_INET,SOCK_DGRAM,0);

	if((error=ioctl(socketfd,SIOCGIFCONF,&ifc))<0){
        printf("ioctl faild");
        return -2;
	}
	if(ifc.ifc_len<=len){
		ifrp=ifc.ifc_req;
		do{
			struct sockaddr *sa=&ifrp->ifr_addr;
			strcpy(ifr.ifr_name,ifrp->ifr_name);
			if(strcmp(ifrp->ifr_name,name)!=0){
				strcpy(name,ifrp->ifr_name);
				if(((struct sockaddr_dl *)sa)->sdl_type==IFT_ETHER){
					ptr = (unsigned char *)LLADDR((struct sockaddr_dl *)sa);
					sprintf(devmac, "%02x%02x%02x%02x%02x%02x",
						ptr[0], ptr[1], ptr[2],
						ptr[3], ptr[4], ptr[5]);
					break ;
				}
			}
			ifrp=(struct ifreq*)(sa->sa_len+(caddr_t)&ifrp->ifr_addr);
			space+=(int)sa->sa_len+sizeof(ifrp->ifr_name);
		}while(space<ifc.ifc_len);

	}

#else
	struct ifreq ifr;
	char *ptr;
	int skfd;

	if((skfd = socket(AF_INET, SOCK_DGRAM, 0)) < 0) {
		//error(E_L, E_LOG, T("getIfMac: open socket error"));
		return -1;
	}

	strncpy(ifr.ifr_name, ifname, IFNAMSIZ);
	if(ioctl(skfd, SIOCGIFHWADDR, &ifr) < 0) {
		close(skfd);
		//error(E_L, E_LOG, T("getIfMac: ioctl SIOCGIFHWADDR error for %s"), ifname);
		return -1;
	}

	ptr = (char *)&ifr.ifr_addr.sa_data;
	sprintf(devmac, "%02x%02x%02x%02x%02x%02x",
			(ptr[0] & 0377), (ptr[1] & 0377), (ptr[2] & 0377),
			(ptr[3] & 0377), (ptr[4] & 0377), (ptr[5] & 0377));

	close(skfd);
#endif
	return 0;
}

int getDevName(char *devmac, char *devname)
{
	int i=0;
	size_t len;
	unsigned char md5result[16]={0};
	unsigned char md5middle[16]={0};
#if 0
	//http://ascii.911cha.com
	char visible_char[53] = { \
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', \
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', \
		'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', \
		'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', \
		'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', \
		'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', \
		'y', 'z'};
#endif
	unsigned char map_char[103] = {0};
	map_char['0'] = 'e';
	map_char['1'] = '0';
	map_char['2'] = 'p';
	map_char['3'] = 'q';
	map_char['4'] = 's';
	map_char['5'] = 'r';
	map_char['6'] = '6';
	map_char['7'] = 'w';
	map_char['8'] = '8';
	map_char['9'] = 'x';
	map_char['a'] = 'm';
	map_char['b'] = 'c';
	map_char['c'] = 'h';
	map_char['d'] = 'o';
	map_char['e'] = 'v';
	map_char['f'] = 'p';

	len=strlen(devmac);
	//echo("device mac :%s\n", devmac);
	md5(devmac, len, md5result);

	for (i = 4; i < 12; i++)
		sprintf(md5middle, "%s%2.2x", md5middle, md5result[i]);

	//echo("md5 middle :%s\n", md5middle);
	for (i = 0; i < 16; i++)
		devname[i]=map_char[md5middle[i]];
	//echo("device name:%s\n", devname);

	return 0;
}

unsigned char sock_connect(int *socket_fd, char *server_host, short port)
{
	struct sockaddr_in socket_addr;
	int ret = 0;
	int fd = 0;
	unsigned char result = ERROR_NONE;

	echo("host:%s, port=%d.\n", server_host, port);
	memset(&socket_addr,0, sizeof(struct sockaddr_in));
	if (inet_aton (server_host, &(socket_addr.sin_addr)) == 0) 
	{
		ret = gngetaddrinfo(server_host, &socket_addr, 3);
		if(ret > 0)
		{
			result = ERROR_CLIENT_SOCKET;
			printf("[crpc %s:%d] gngetaddrinfo failed. (Errno : 0x%02x)\n", __FUNCTION__, __LINE__, result);
			goto out;
		}
		printf("[crpc %s:%d] host(%s), ip(%s)\n", __FUNCTION__, __LINE__, server_host, inet_ntoa(socket_addr.sin_addr));
	}
	
	if((fd = socket(AF_INET, SOCK_STREAM, 0)) < 0)
	{
		result = ERROR_CLIENT_SOCKET;
		printf("[crpc %s:%d] Client socket failed. (Errno : 0x%02x)\n", __FUNCTION__, __LINE__, result);
		goto out;
	}

	socket_addr.sin_family = AF_INET;
	socket_addr.sin_port = htons(port);
	
	if(connect(fd, (struct sockaddr *)&socket_addr, sizeof(struct sockaddr_in)) == -1)
	{
		result = ERROR_CLIENT_CONNECT;
		printf("[crpc %s:%d] Client socket connection failed. (Errno : 0x%02x)\n", __FUNCTION__, __LINE__, result);
		goto out;
	}
	*socket_fd = fd;
	echo("connect fd=%d OK\n", fd);
out:
	return result;
}


unsigned int crpc_compare_timer(time_t start, int timeval)
{
	time_t	cur_time;
	time(&cur_time);

	if (((unsigned int)cur_time - (unsigned int)start) == timeval)
		return 1;
	else
		return 0;
}

unsigned char crpc_safe_send(int fd, char *data, int len)
{
	//检测是否断开
	if(check_sock(fd)!= 0)
	{
		return ERROR_CLIENT_SOCKET;
	}
	
	time_t	start_time;
	int trans_size = 0;

	int total_len = 0;
	int result = ERROR_NONE;

	time(&start_time);
	total_len = 0;

	do{
		if (crpc_compare_timer(start_time, 3))
		{
			result = ERROR_CLIENT_SEND;
			goto out;
		}
		else
		{
			trans_size = write(fd, (unsigned char *)data + total_len , (len - total_len));
			echo("send fd[%d] len=%d.\n", fd, trans_size);
			if (trans_size > 0)
				total_len += trans_size;
			else
			{
				result = ERROR_CLIENT_SEND;
				goto out;
			}
			
		}
	} while (total_len != len);

out:
	return result;
}

//#define LOOP_READ
char svrBakBuf[BUFSIZE] = {0};
size_t svrBakLen = 0;

unsigned char server_receive(int fd)
{
	int cfd;
	int nread = 0, pos = 0; //read return -1 is ERROR.
	unsigned short len =0, total_len = 0, data_len = 0;
	unsigned char result = ERROR_SERVER_NETWORK;
	data_frp_t rsp_data;
	data_frp_t cmd_data;
	pkg_connect_res_t connect_res;
	pkg_new_reverse_res_t new_reverse_res;
	pkg_new_reverse_t new_reverse;
	pkg_reverse_data_t reverse_data;
	pkg_close_reverse_t close_reverse;
	pkg_pause_reverse_t pause_reverse;
	pkg_resume_reverse_t resume_reverse;
	
	char lo_addr[16] = {0};
	unsigned short lo_port = 80;
	unsigned char new_reverse_result = 0;
	int bak_len = 0;
	
#ifdef LOOP_READ
	char data[BUFSIZE] = {0};
	char *readBuf = (char*)malloc(0);
	char *reBuf = NULL;
	len = nread = 0;
	while( 1 )
	{
		memset(data, 0, BUFSIZE);
		nread = read(fd, data, BUFSIZE);
		echo("nread=%d.\n", nread);
		if (nread > 0)
		{
			reBuf = (char*)realloc(readBuf, len + nread);
			if ( NULL == reBuf )
			{
				printf("Can not realloc to read from server!\n");
				result = ERROR_LOCAL_MEMORY;
				goto out;
			}
			readBuf=reBuf;
			memcpy(readBuf+len,data,nread);
			len += nread;
		}
		else
		{
			result = ERROR_SERVER_SOCKET;
			goto out;
		}
		
		if ( nread < BUFSIZE )//read over
			break;
	}
#else //just read once
	char data[BUFSIZE] = {0};
	char readBuf[BUFSIZE] = {0};
	
	len = nread = 0;
	{
		if ( svrBakLen > 0 )
		{			
			memcpy(readBuf, svrBakBuf, svrBakLen);
			nread = read(fd, readBuf+svrBakLen, BUFSIZE-svrBakLen);
			nread += svrBakLen;
			echo("bak_nread=%d = %d(read)+%d(bak).\n", nread+svrBakLen, nread, svrBakLen);
			svrBakLen = 0;
		}
		else
		{
			nread = read(fd, readBuf, BUFSIZE);
			echo("org_nread=%d.\n", nread);
		}
		
		if (nread > 0)
		{
			len = nread;
		}
		else
		{
			result = ERROR_SERVER_SOCKET;
			goto out;
		}
	}
#endif

	pos = 0;
	while( pos + 3 < len ) //DATA_HEAD_SIZE = 3
	{
		memset(&cmd_data, 0, sizeof(data_frp_t));
		memcpy(&cmd_data, readBuf+pos, DATA_HEAD_SIZE);
		pos += 3;
		
		if ( cmd_data.flag != CRPC_FLAG )
		{
			echo("cmd_data.flag[0x%0x2] is not 0x%02x.\n", cmd_data.flag, CRPC_FLAG);
			result = ERROR_SERVER_RECEIVE;
			svrBakLen = 0;
			goto out;
		}
		
		switch(cmd_data.type)
		{
			case PKG_CONN_RSP:
				memset(&connect_res, 0, 5); //sizeof(pkg_connect_res_t)
				memcpy(&connect_res, readBuf+pos, 5);
				if ( 0 != connect_res.result)
				{	
					echo("connect to server refused.\n");
					result = ERROR_SERVER_RECEIVE;
					svrBakLen = 0;
					goto out;
				}
				echo("connect respone, up bw limit %dKBps.\n", ntohl(connect_res.bw)); 
				if ( ntohl(connect_res.bw) > 0 )
				{
					upBw = ntohl(connect_res.bw)*1024;
				}

				pos += 5;
				break;
			case PKG_NEW_REVERSE:
				if ( pos + 12 > len )//sizeof(pkg_new_reverse_t)=12
				{
					echo("package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3;
					goto out;
				}
				echo("new reverse.\n"); 
				
				memcpy(&new_reverse, readBuf+pos, 12);
				pos += 12;
				
				if ( pos + ntohs(new_reverse.len) > len )
				{
					echo("new reverse package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3+12;
					goto out;
				}

				if ( getLocalFd(new_reverse.id) > 0 )
				{
					new_reverse_result = 1;
                    echo("connid exists, send result refuse\n");
				}
				else
				{
					sprintf(lo_addr, "%d.%d.%d.%d", new_reverse.ip[0],new_reverse.ip[1],new_reverse.ip[2],new_reverse.ip[3]);
					lo_port = ntohs(new_reverse.port);//ntohs
					memset(data, 0, BUFSIZE);
					memcpy(data, readBuf+pos, ntohs(new_reverse.len));
					pos += ntohs(new_reverse.len);

					//strcpy(lo_addr, "192.168.10.1");
					if ( sock_connect(&cfd, lo_addr, lo_port) != ERROR_NONE )
					{
						echo("new local fd error.\n");
						new_reverse_result = 1;
					}
					else
					{
						echo("new local fd[%d] success.\n", cfd);						
						addLocalConn(cfd, new_reverse.id);
						if(ntohs(new_reverse.len)>0)
						{
							//send(cfd, data, ntohs(new_reverse.len), 0);
							result = crpc_safe_send(cfd, data, ntohs(new_reverse.len));
							if ( ERROR_NONE != result )
								goto out;
						}
					}
				}

				echo("new reverse respone.\n"); 
				memset(&rsp_data, 0, sizeof(data_frp_t));
				rsp_data.flag= CRPC_FLAG;
				rsp_data.version = VERSION;
				rsp_data.type = PKG_NEW_REVERSE_RSP;
								
				memset(&new_reverse_res, 0, 5);// sizeof(pkg_new_reverse_res_t)=5
				new_reverse_res.id = new_reverse.id;
				new_reverse_res.result = new_reverse_result;

				memset(data, 0, BUFSIZE);
				memcpy(data, &rsp_data, 3);
				memcpy(data+3, (char *)&new_reverse_res, 5);
				data_len = 3 + 5;
				
				//send(fd, data, data_len, 0);
				result = crpc_safe_send(fd, data, data_len);
				if ( ERROR_NONE != result )
					goto out;
				
				break;
			case PKG_REVERSE_DATA:
				if ( pos + 6 > len )//sizeof(pkg_reverse_data_t)=6
				{
					echo("package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3;
					goto out;
				}
				echo("reverse data.\n"); 

				memcpy(&reverse_data, readBuf+pos, 6);
				pos += 6;

				if ( pos + ntohs(reverse_data.len) > len )
				{
					echo ("reverse data package data not completed\n");
                    result = ERROR_NOT_COMPLETE;
					bak_len = 3+6;
					goto out;
				}

				cfd = getLocalFd(reverse_data.id);
				if ( cfd > 0 )
				{
					memset(data, 0, BUFSIZE);
					memcpy(data, readBuf+pos, ntohs(reverse_data.len));
					pos += ntohs(reverse_data.len);
					//send(cfd, data, ntohs(reverse_data.len), 0);
					result = crpc_safe_send(cfd, data, ntohs(reverse_data.len));
					if ( ERROR_NONE != result )
						goto out;
				}
				else
				{//connid不存在，通知server关闭socket
					echo("no connid[%d], close connectid socket.\n", ntohl(reverse_data.id)); 
					memset(&rsp_data, 0, sizeof(data_frp_t));
					rsp_data.flag= CRPC_FLAG;
					rsp_data.version = VERSION;
					rsp_data.type = PKG_CLOSE_REVERSE;
					
					memset(&close_reverse, 0, 4);
					close_reverse.id = reverse_data.id;
					
					memset(data, 0, BUFSIZE);
					memcpy(data, &rsp_data, 3);
					memcpy(data+3, (char *)&close_reverse, 4);//sizeof pkg_close_reverse_t = 4
					data_len = 3 + 4;
					
					//send(fd, data, data_len, 0);
					result = crpc_safe_send(fd, data, data_len);
					if ( ERROR_NONE != result )
					{
						result = ERROR_SERVER_SOCKET;
						goto out;
					}
					else
					{
						pos += ntohs(reverse_data.len);
					}
				}
			
				break;
			case PKG_CLOSE_REVERSE:
				if ( pos + 4 > len )//sizeof(pkg_close_reverse_t)=4
				{
					echo("package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3;
					goto out;
				}
				echo("close reverse.\n"); 

				memcpy(&close_reverse, readBuf+pos, 4);
				pos += 4;

				cfd = getLocalFd(close_reverse.id);
				if ( cfd > 0 )
				{
					delLocalConn(cfd);
				}

				break;
			case PKG_REVERSE_PAUSE:
				if ( pos + 4 > len )//sizeof(pkg_pause_reverse_t)=4
				{
					echo("reverse pause package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3;
					goto out;
				}
				echo("reverse pause package.\n");

				memcpy(&pause_reverse, readBuf+pos, 4);
				pos += 4;

				cfd = getLocalFd(pause_reverse.id);
				if ( cfd > 0 )
				{
					pauses[cfd] = 1;
				}

				break;
			case PKG_REVERSE_RESUME:
				if ( pos + 4 > len )//sizeof(pkg_resume_reverse_t)=4
				{
					echo("reverse resume package not completed.\n");
					result = ERROR_NOT_COMPLETE;
					bak_len = 3;
					goto out;
				}
				echo("reverse resume package.\n");

				memcpy(&resume_reverse, readBuf+pos, 4);
				pos += 4;

				cfd = getLocalFd(resume_reverse.id);
				if ( cfd > 0 )
				{
					pauses[cfd] = 0;
				}

				break;
			default:
				echo("unknow server cmd.\n");
				break;
		}
		
	}
	echo("done...\n");
out:
	if (ERROR_NOT_COMPLETE == result)
	{
		memcpy(svrBakBuf+svrBakLen, readBuf+pos-bak_len, len-pos+bak_len);
		svrBakLen = len-pos+bak_len;
		
		if ( svrBakLen > BUFSIZE ) //beyond size
		{
			svrBakLen=0;
			result = ERROR_SERVER_RECEIVE;
		}
	}
#ifdef LOOP_READ	
	SAFE_FREE(readBuf);
#endif
	return result;
}

unsigned char local_receive(int sfd, int cfd)
{
	int tsize =0, nread = 0, pos = 0;
	unsigned short len =0, data_len = 0;
	unsigned char result = ERROR_SERVER_NETWORK;
	unsigned int connid;
	data_frp_t rsp_data;
	pkg_reverse_data_t reverse_data;
	pkg_close_reverse_t close_reverse;
		
	char data[READSIZE] = {0};
	char dataStream[BUFSIZE] = {0};

	connid = conns[cfd];
	if ( connid <= 0 )
	{
		echo( "connid non exist.\n" );
		delLocalConn(cfd);
	}
	else
	{		
		len = nread = 0;
		do{
			timeStamp=time(NULL);
			//total&pause limit
			if (timeRead==timeStamp)
			{
				if ( (pauses[cfd] == 1 && total_pause[cfd] > pauseBw) 
					|| (pauses[cfd] == 0 && total_sec > upBw))
				{
					if ( pauses[cfd] == 1 )
						echo("socket[%d] sent %d Bps. limit %d Bps.\n", cfd, total_pause[cfd], pauseBw);
					else
						echo("total sent %d Bps. limit %d Bps.\n", total_sec, upBw);

					usleep(10000);
					break;
				}
			}
			else if ( timeRead != timeStamp )
			{
				timeRead=timeStamp;
				total_sec=0;
				total_pause[cfd]=0;
			}
			
			memset(data, 0, READSIZE);
			nread = read(cfd, data, READSIZE);
			//echo("fd[%d] read string=[%s], len[%d].\n", cfd, data, nread);
			echo("fd[%d] read string len[%d].\n", cfd, nread);
			if (nread > 0)
			{
				memset(&rsp_data, 0, sizeof(data_frp_t));
				rsp_data.flag= CRPC_FLAG;
				rsp_data.version = VERSION;
				rsp_data.type = PKG_REVERSE_DATA;
				
				memset(&reverse_data, 0, sizeof(pkg_reverse_data_t));
				reverse_data.id = htonl(connid);
				reverse_data.len = htons((unsigned short)nread);
				
				memset(dataStream, 0, BUFSIZE);
				memcpy(dataStream, (char *)&rsp_data, 3);
				memcpy(dataStream+3, (char *)&reverse_data, 6);
				memcpy(dataStream+3+6, data, nread);
				//tsize = send(sfd, dataStream, 3+6+nread, 0);
				result = crpc_safe_send(sfd, dataStream, 3+6+nread);
				if ( ERROR_NONE != result )
					goto out;
								
				total_sec += (3+6+nread);
				if ( pauses[cfd] == 1 )
					total_pause[cfd] += (3+6+nread);
			}
			else
			{//socket已关闭
				echo("to close socket. connid[%d].\n", connid);
				memset(&rsp_data, 0, sizeof(data_frp_t));
				rsp_data.flag= CRPC_FLAG;
				rsp_data.version = VERSION;
				rsp_data.type = PKG_CLOSE_REVERSE;
				
				memset(&close_reverse, 0, sizeof(pkg_close_reverse_t));
				close_reverse.id = htonl(connid);
				
				memset(dataStream, 0, BUFSIZE);
				memcpy(dataStream, &rsp_data, 3);
				memcpy(dataStream+3, &close_reverse, 4);
				//tsize = send(sfd, dataStream, 3+4, 0);
				result = crpc_safe_send(sfd, dataStream, 3+4);
				if ( ERROR_NONE != result )
					goto out;
								
				delLocalConn(cfd);
				break;
			}
		}while( 0/*nread == READSIZE*/ );//read over, support multi user, balance bw, it will reduce read read to 100KBps
	}
	result = ERROR_NONE;
out:	
	return result;
}

unsigned char crpc_connect(int fd)
{
	data_frp_t rsp_data;
	pkg_connect_t connect_req;
	char data[BUFSIZE] = {0};
	short int len = 0;
	size_t tsize = 0;

	memset(&rsp_data, 0, sizeof(data_frp_t));
	rsp_data.flag= CRPC_FLAG;
	rsp_data.version = VERSION;
	rsp_data.type = PKG_CONN_SERVER;
	memcpy(data, (char *)&rsp_data, 3);
	len += 3;
	
	memset(&connect_req, 0, sizeof(pkg_connect_t));
	strncpy(connect_req.name, deviceName, 16);
	memcpy(data+3, (char *)&connect_req, 16);
	len += 16;
	
	tsize = send(fd, data, len, 0);
	echo("send size [%d]\n", tsize);
	return 0;
}

unsigned char crpc_heartbeat(int fd)
{
	size_t tsize = 0;
	data_frp_t rsp_data;
	pkg_new_reverse_res_t new_reverse_res;

	memset(&rsp_data, 0, sizeof(data_frp_t));
	rsp_data.flag= CRPC_FLAG;
	rsp_data.version = VERSION;
	rsp_data.type = PKG_HEART_BEAT;

	tsize = send(fd, (char *)&rsp_data, 3, 0);
	echo("send size [%d]\n", tsize);
	return 0;
}

void init_config(int argc, char **argv)
{
	int			newpid;
	char			c;
	char devMac[16]={0};

	while ((c = getopt(argc, argv, "fdh:p:n:?")) != EOF) {
		switch(c) {
		case 'f':
			isdaemon = 0;
			break;
		case 'd':
			idebug = 1;
			break;
		case 'h':
			if (!optarg) {
				fprintf(stderr, "Invalid host\n");
				exit(-1);
			}
			strcpy(sHost, optarg);
			break;
		case 'p':
			if (!atoi(optarg)) {
				fprintf(stderr, "Invalid port %s\n", optarg);
				exit(-1);
			}
			sPort = atoi(optarg);
			break;
		case 'n':
			if (!optarg) {
				fprintf(stderr, "Invalid device name\n");
				exit(-1);
			}
			strcpy(deviceName, optarg);
			break;
		case '?':
			fprintf(stderr, "Usage: %s [-f] [-d] [-n device name] [-h host] [-p port]\n", argv[0]);
			exit(1);
			break;
		}

	}

	if ( 0 == strlen(deviceName) )
	{
		getDevMac("br0", devMac);
		getDevName(devMac, deviceName);
	}

	if (isdaemon) {
		//localback
		if( f_exists(CRPC_PID) )
			exit(-1);
		
		if ((newpid = fork()) < 0) {
			perror("fork");
			exit(-1);
		}
		if (newpid) { /* parent */
			exit(0);
		}
		fclose(stdin);
		fclose(stdout);
		fclose(stderr);
		setsid();
		setpgid(0, 0);
		openlog("crpc", LOG_PID, LOG_DAEMON);
	}

	signal(SIGCHLD, SIG_IGN);
	if (isdaemon) register_pid();
}

void sig_handler(int sig)
{
	int i;
	switch (sig)
	{
	case SIGSEGV:
	case SIGTERM:
	case SIGINT:
		for ( i=sockfd+1; i<FD_SIZE; i++ )
		{
			if (conns[i]>0)
			{
				delLocalConn(i);
			}
		}
		SAFE_CLOSE(sockfd);
		printf("crpc exit!!!\n");
		if (isdaemon) remove(CRPC_PID);
		exit(1);
	case SIGUSR1:
		break;
	default:
		break;
	}
}

int f_exists(const char *path)	// note: anything but a directory
{
	struct stat st;
	return (stat(path, &st) == 0) && (!S_ISDIR(st.st_mode));
}

int main(int argc, char **argv)
{
	struct sigaction sa;
	int rel = 0, i;
	unsigned char rsp_info = ERROR_NONE;
	struct	timeval tv;
	char echo_crpc_url[256]={0};
	
	init_config(argc, argv);
	
	printf("[crpc %s:%d] device url: [http://%s.d.carystudio.com:9080]\n\n", __FUNCTION__, __LINE__, deviceName);
	sprintf(echo_crpc_url, "echo 'http://%s.d.carystudio.com:9080' > /usr/local/opnsense/cs/tmp/crpc_url", deviceName);
	system(echo_crpc_url);

	sa.sa_handler = sig_handler;
	sa.sa_flags = 0;
	sigemptyset(&sa.sa_mask);
	sigaction(SIGTERM, &sa, NULL);
	sigaction(SIGINT, &sa, NULL);
	sigaction(SIGUSR1, &sa, NULL);

reConnServ:
	for ( i=sockfd+1; i<FD_SIZE; i++ )
	{
		if (conns[i]>0)
		{
			delLocalConn(i);
		}
	}
	SAFE_CLOSE(sockfd);
	rsp_info = sock_connect(&sockfd, sHost, sPort);
	if(rsp_info != ERROR_NONE)
	{
		sleep(10);
		goto reConnServ;
	}
	
	echo("connect server ok.\n\n");
	crpc_connect(sockfd);

	maxfd = sockfd;
	while (1) {
		FD_ZERO(&rfds);
		FD_SET(sockfd, &rfds);
		for ( i=sockfd+1; i<FD_SIZE; i++ )
		{
			if (conns[i]>0)
			{
			#if 0 //no check
				if ( check_sock(i)!= 0 )
				{
					echo( "del FIN fd[%d], connid[%d]\n", i, conns[i]);
					delLocalConn(i);
				}
				else
			#endif
				{
					echo( "add fdset fd[%d], connid[%d]\n", i, conns[i]);
					FD_SET(i, &rfds);
					maxfd = i;
				}
			}
		}
		tv.tv_sec	= KEEPALIVE_SEC;
		tv.tv_usec	= 0;
		rel = select(maxfd + 1, &rfds, NULL, NULL, &tv);
		if (rel < 0)
		{
			printf("[crpc %s:%d] select error. (Errno : 0x%02x)\n", __FUNCTION__, __LINE__, errno);
			sleep(10);
			goto reConnServ;
		}
		else if(rel == 0)
		{
			echo("timeout %d s.\n", KEEPALIVE_SEC);
			crpc_heartbeat(sockfd);
			usleep(10000);
			continue;
		}
		if(FD_ISSET(sockfd, &rfds))
		{
			echo("recv server data.\n");
			rsp_info = server_receive(sockfd);
			if ( ERROR_SERVER_SOCKET == rsp_info 
				|| ERROR_SERVER_RECEIVE == rsp_info )
			{
				echo("server fd close.\n");
				sleep(10);
				goto reConnServ;
			}
		}
		//echo("sockfd=%d, maxfd=%d.\n", sockfd, maxfd);
		for ( i=sockfd+1; i<maxfd+1; i++ )
		{
			//echo("conns[%d]=%d.\n", i, conns[i]);
			if (conns[i]>0)
			{
				if(FD_ISSET(i, &rfds))
				{
					echo("recv local.fd[%d] data.\n", i);
					rsp_info = local_receive(sockfd, i);
					if ( ERROR_SERVER_SOCKET == rsp_info )
					{
						echo("server fd close.\n");
						sleep(10);
						goto reConnServ;
					}
				}
			}
		}
	}
	
	return 0;
}

