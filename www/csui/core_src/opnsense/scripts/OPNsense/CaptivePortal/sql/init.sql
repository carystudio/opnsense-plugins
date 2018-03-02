--
-- create new Captive Portal database
--

-- connected clients
create table cp_clients (
      zoneid int
,     sessionid varchar
,     authenticated_via varchar
,     username varchar
,     ip_address varchar
,     mac_address varchar
,     created number
,     deleted integer default (0)
,     primary key (zoneid, sessionid)
);

create index cp_clients_ip ON cp_clients (ip_address);
create index cp_clients_zone ON cp_clients (zoneid);

-- session (accounting) info
create table session_info (
      zoneid int
,     sessionid varchar
,     prev_packets_in integer
,     prev_bytes_in   integer
,     prev_packets_out integer
,     prev_bytes_out   integer
,     packets_in integer default (0)
,     packets_out integer default (0)
,     bytes_in integer default (0)
,     bytes_out integer default (0)
,     cur_bytes_in integer default (0)
,     cur_bytes_out integer default (0)
,     last_accessed integer
,     last_accounting integer
,     primary key (zoneid, sessionid)
);

-- session (accounting) restrictions
create table session_restrictions (
      zoneid int
,     sessionid varchar
,     session_timeout int
,     primary key (zoneid, sessionid)
) ;

--  accounting state, record the state of (radius) accounting messages
create table accounting_state (
      zoneid int
,     sessionid varchar
,     state varchar
,     primary key (zoneid, sessionid)
) ;

--  portal users
create table users (
      username varchar
,     password varchar
,     expire_time number
,     remain_time number
,     concurrent_logins integer default (0)
,     created number
,     deleted integer default (0)
,     primary key (username)
);
insert into users values ('WeChatUser', 'jkejr03uj24mfkjsskjf', 1924790400, 0, 1, 1519900764, 0);

--  wanwhiteset record
create table wanwhiteset (
      ip varchar
,     fwtable number
,     create_time number
,     expire_time number
,     delete_time number default (0)
,     primary key (ip,fwtable)
);
