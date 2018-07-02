#ifndef _crpc_h_
#define _crpc_h_
#include <stdio.h>
#include <errno.h>
#include <unistd.h>
#include <stdlib.h>
#include <stdint.h>
#include <fcntl.h>
#include <string.h>
#include <assert.h>
#include <sys/time.h>
#include <sys/socket.h>
#include <sys/ioctl.h>
#include <sys/select.h>
#include <sys/types.h>
#include <netinet/in.h>
#include <net/if.h>
#include <arpa/inet.h>
#include <signal.h>
#include <sys/stat.h>
#include <syslog.h>
#include <ctype.h>
#include <getopt.h>
#include <netdb.h>
#include <setjmp.h>
#include <sys/wait.h>
#include <netinet/ip.h>
#include <net/ethernet.h>
#include <net/if_dl.h>
#include <net/if_types.h>
#include <signal.h>
int idebug = 0;
int ilog2file = 0;
int isdaemon = 1;
#define CRPC_PID "/var/run/crpc.pid"

char sHost[32] = "svr.d.carystudio.com";//"192.168.11.111";//"svr.d.carystudio.com";//120.76.40.233
short sPort = 9090;//7100;

char deviceName[17] = {0};

enum ERROR_VALUE {
	ERROR_NONE = 0x00,
	ERROR_SERVER_NETWORK = 0x01,
	ERROR_NOT_COMPLETE = 0x02,
	ERROR_SERVER_RECEIVE = 0x07,
	ERROR_SERVER_WRITE = 0x08,
	ERROR_SERVER_SOCKET = 0x0c,
	ERROR_SERVER_SET_SOCKET = 0x0d,
	ERROR_SERVER_SEND = 0x0e,
	ERROR_SERVER_OPEN = 0x0f,

	ERROR_LOCAL_SOCKET = 0x10,
	ERROR_LOCAL_SEND = 0x11,
	ERROR_LOCAL_RECEIVE = 0x12,
	ERROR_LOCAL_CONNECT = 0x13,
	ERROR_LOCAL_NETWORK = 0x14,
	ERROR_LOCAL_MEMORY = 0x15,
	
	ERROR_CLINET_MEMORY = 0x53,
	ERROR_CLIENT_THREAD = 0x54,
	ERROR_CLIENT_CMD_TYPE = 0x55,
	ERROR_CLIENT_SOCKET = 0x56,
	ERROR_CLIENT_SET_SOCKET = 0x57,
	ERROR_CLIENT_CONNECT = 0x58,
	ERROR_CLIENT_NETWORK = 0x59,
	ERROR_CLIENT_SEND = 0x5a,
	ERROR_CLIENT_RECEIVE = 0x5b,

	ERROR_SERVER_NOT_BUSY = 0xfc,
	ERROR_SERVER_BUSY = 0xfd,
	ERROR_COMPLETE = 0xfe,
	ERROR_END,
};

enum CRPC_TYPE {
	PKG_HEART_BEAT = 0x1,
	PKG_CONN_SERVER = 0x2,
	PKG_CONN_RSP = 0x3,
	PKG_SET_SERVER = 0x4,

	//reverse proxy data
	PKG_NEW_REVERSE = 0x50,
	PKG_NEW_REVERSE_RSP = 0x51,
	PKG_REVERSE_DATA = 0x52,
	PKG_CLOSE_REVERSE = 0x53,
	PKG_REVERSE_PAUSE = 0x54,
	PKG_REVERSE_RESUME = 0x55,
	PKG_MAX,
};

#define VERSION 0x0
#define CRPC_FLAG 0xEF
#define BUFSIZE	8192//16384
#define READSIZE 2048//5120

#define DATA_HEAD_SIZE	0x3
typedef struct data_frp {
	unsigned char flag; //起始标志 0xEF
	unsigned char version;// 版本
	unsigned char type; //包类型
}__attribute__((packed))data_frp_t;

typedef struct pkg_connect{
	char name[16];
}pkg_connect_t;

typedef struct pkg_connect_res{
	unsigned char result; //0:连接成功 1：拒绝连接
	unsigned int bw; //数据最大发送速度KB/s
}__attribute__((packed))pkg_connect_res_t;

typedef struct pkg_set_server{
	unsigned char host[32]; //服务器域名或IP
	uint16_t port; //服务器Port
}__attribute__((packed))pkg_set_server_t;

typedef struct pkg_new_reverse{
	unsigned int id; //这个连接的ID
	unsigned char ip[4]; //连接内网设备的 IP
	uint16_t port; //连接内网的端口
	uint16_t len; //数据长度
}__attribute__((packed))pkg_new_reverse_t;

typedef struct pkg_new_reverse_res{
	unsigned int id; //这个连接的ID
	unsigned char result; //0:连接成功 1：拒绝连接
}__attribute__((packed))pkg_new_reverse_res_t;

typedef struct pkg_reverse_data{
	unsigned int id ; //这个连接的ID
	uint16_t len; //数据长度
}__attribute__((packed))pkg_reverse_data_t;

typedef struct pkg_close_reverse{
	unsigned int id; //这个连接的ID
}pkg_close_reverse_t;

typedef struct pkg_pause_reverse{
	unsigned int id; //这个连接的ID
}pkg_pause_reverse_t;

typedef struct pkg_resume_reverse{
	unsigned int id; //这个连接的ID
}pkg_resume_reverse_t;

#define SAFE_FREE(p)				if(p)		{ free(p); p = NULL; }
#define SAFE_CLOSE(fd)				if(fd > 0)	{ close(fd); fd = -1; } 
#define SAFE_FCLOSE(fd)				if(fd)		{ fclose(fd); fd = NULL; }

#define KEEPALIVE_SEC	30

void md5(const uint8_t *initial_msg, size_t initial_len, uint8_t *digest);

#endif /*_crpc_h_*/
