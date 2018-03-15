(function(obj){
/**
 * 全局的公有函数
 *
 * @property {function} cs.num(str) 判断此字符串是不是数字 <a href="#num">点击查看</a>
 * @property {function} cs.num_range(str,min,max) 判断是否在数字的范围 <a href="#num_range">点击查看</a>
 * @property {function} cs.port(str)  校验端口是否符合规范（数字必须在1~65535之间） <a href="#port">点击查看</a>
 * @property {function} cs.string(str)   校验字符串是否符合（不能包含无效字符） <a href="#string">点击查看</a>
 * @property {function} cs.string_same(s1,s2)   ??? <a href="#string_same">点击查看</a>
 * @property {function} cs.ssid(str)   检测ssid是否符合规则 <a href="#ssid">点击查看</a>
 * @property {function} cs.hex(str)   ??? <a href="#hex">点击查看</a>
 * @property {function} cs.ascii(str)   ??? <a href="#ascii">点击查看</a>
 * @property {function} cs.key(str,flag)   ??? <a href="#key">点击查看</a>
 * @property {function} cs.mac(str)   校验mac地址 <a href="#mac">点击查看</a>
 * @property {function} cs.mask(str)   校验子网掩码地址 <a href="#mask">点击查看</a>
 * @property {function} cs.ip(str)   校验IP地址 <a href="#ip">点击查看</a>
 * @property {function} cs.ip_subnet(s1,mn,s2)   ??? <a href="#ip_subnet">点击查看</a>
 * @property {function} cs.ip_range(s1,s2)   ??? <a href="#ip_range">点击查看</a>
 * @property {function} cs.ip_same(s1,s2)   ??? <a href="#ip_same">点击查看</a>
 * @property {function} cs.countdown(msg,time,options,callback)   倒计时通用函数 <a href="#countdown">点击查看</a>
 * @alias cs
 * @class
 * @example
 * // 比如调用num函数
 * cs.num('ye') // return 0
 * cs.ip('127.0.0.1') // return 99
 */
function cs(){
    this.version = '0.0.1bate';
    this.author = 'Yexk';
    this.company = 'carystudio';
}

/**
 * 判断此字符串是不是数字
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   字符串
 * @return   {Number} 
 * 0：不能为空。 <br/>
 * 1：无效，必须是数字 <br/>
 * @example
 * cs.num('carystudio'); // return 0
 */
cs.prototype.num = function(str){
	var ret = 99;	
	if(str == undefined || str=="") { ret = 0;  return ret; }//不能为空
	var reg=/^[0-9]*$/;
	if(!reg.test(str)) ret = 1;//无效，必须是数字
	return ret;
};

/**
 * 判断是否在数字的范围
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   数字值
 * @param    {Number}   min                   最小值
 * @param    {Number}   max                   最大值
 * @return   {Number}                         
 * 0： 不能为空 <br>
 * 1：无效，必须是数字 <br>
 * 2：无效，必须是min~max之间的数字 <br>
 * 99：有效 <br>
 */
cs.prototype.num_range = function (str,min,max){
	var ret = 99;		
	if(str == undefined || str=="") { ret = 0;  return ret; }//不能为空
	var reg=/^[0-9]*$/;
	if(!reg.test(str)) ret = 1;//无效，必须是数字
	if((parseInt(str)<min)||(parseInt(str)>max)) ret = 2;//无效，必须是min~max之间的数字
	return ret;
};

/**
 * 校验端口是否符合规范（数字必须在1~65535之间）
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   数字值
 * @return   {Number}                      
 * 0: 不能为空 <br/>
 * 1: 无效，必须是数字 <br/>
 * 2: 无效，必须是1~65535之间的数字 <br/>
 * 99: 有效
 */
cs.prototype.port = function (str){
	var ret = 99;		
	if(str == undefined || str=="") { ret = 0;  return ret; }//不能为空
	var reg=/^[0-9]*$/;
	if(!reg.test(str)) ret = 1;	//无效，必须是数字	
	if(parseInt(str)<1||parseInt(str)>65535) ret = 2;//无效，必须是1~65535之间的数字
	return ret;
};

/**
 * 校验字符串是否符合（不能包含无效字符）
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   字符串
 * @return   {Number}        
 * 0: 不能为空 <br/>
 * 1: 无效，包含了无效的字符 <br/> 
 * 99: 有效             
 * 
 */
cs.prototype.string = function (str){
	var ret = 99;		
	if(str == undefined || str=="") { ret = 0;  return ret; }//不能为空
	if(/[\xB7]/.test(str))	ret = 1;//无效，包含了无效的字符
	if(/[^\x00-\xff]/.test(str)) ret = 1;	//无效，包含了无效的字符
	
	var re1=/[^\x20-\x7D]/;
	var re2=/[\x20\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60]/;
	if(re1.test(str)||re2.test(str)) ret = 1;//无效，包含了无效的字符
	return ret;
};

/**
 * 判断两个字符串是否相同
 * @Author   Felix       <felix_chen@carystudio.com>
 * @DateTime 2017-12-21
 * @param    {String}   s1
 * @param    {String}   s2
 * @return   {Number}
 * 0: 是 <br/>
 * 1: 否 
 */
cs.prototype.string_same = function (s1,s2){
    if (undefined == s1 || undefined == s2) return 1;
	if (ip1==ip2) return 0;
	return 1;
};

/**
 * 检测ssid是否符合规则
 * 规则：不能超过32个汉字，不能含有特殊字符。
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   SSID字符串
 * @return   {Number}                         
 * 0: 不能为空 <br/>
 * 1: 无效，包含了无效的字符 <br/> 
 * 2: 无效，不能超过32个汉字 <br/> 
 * 99: 有效   
 */
cs.prototype.ssid = function (str){
	var ret = 99;		
	if(str == undefined || str==""){ ret = 0;  return ret; }
	var reg=/[\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60\x7E]/;
	if(reg.test(str)) ret = 1;	
	//for chinese
	var strlen = 0;
	for(var i = 0;i < str.length; i++){
		if(str.charCodeAt(i) > 255) strlen += 3;
		else strlen++;
	}	
	if(strlen>32) ret = 2;	
	return ret;
};

/**
 * ???
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   字符串
 * @return   {Number}                         返回0失败，1成功
 */
cs.prototype.hex = function (str){
	var reg=/[^A-Fa-f0-9]/;
	if(reg.test(str)) return 0;
	return 1;	
};

/**
 * ???
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   字符串
 * @return   {Number}                         返回0失败，1成功
 */
cs.prototype.ascii = function (str){
	var re1=/[^\x20-\x7D]/;
	var re2=/[\x20\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60]/;
	if(re1.test(str)||re2.test(str)) return 0;
	return 1;	
};
32
/**
 * ???
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-25
 * @param    {String}   str                   字符串
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，不是合法的MAC地址<br/> 
 * 2: 无效，必须是16进制编码的字符<br/> 
 * 3: 无效，不能包括空格<br/> 
 * 99: 有效
 */
cs.prototype.key = function (str,flag){
	var ret = 99;		
	if(str == undefined || str=="") { ret = 0;  return ret; }
	if(flag==1){
		if(!cs.hex(str)) ret = 1;
	} else{
		if(!cs.ascii(str)) ret = 2;
	} 
	return ret;
};

/**
 * 校验mac地址
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   str                   传入mac字符串	
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，不是合法的MAC地址<br/> 
 * 2: 无效，不是有效的MAC地址<br/> 
 * 3: 无效，不是有效的MAC地址<br/> 
 * 99: 有效
 */
cs.prototype.mac = function (str){
	var ret = 99;		
	if(undefined == str || str==":::::"){ ret = 0;  return ret; }
	var reg=/[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}/;
	if(!reg.test(str)) ret = 1;	
	if(str=="00:00:00:00:00:00"||str.toUpperCase()=="FF:FF:FF:FF:FF:FF") ret = 2;
	for(var k=0;k<str.length;k++){
		if((str.charAt(1)&0x01)||(str.charAt(1).toUpperCase()=='B')||(str.charAt(1).toUpperCase()=='D')||(str.charAt(1).toUpperCase()=='F'))
			ret = 3;
	}
	return ret;
};

/**
 * 校验子网掩码地址
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   str                   传入mac字符串	
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，不是合法的掩码地址<br/> 
 * 99: 有效
 */
cs.prototype.mask = function(str){
	var ret = 99;		
	if(undefined == str || str=="...") { ret = 0;  return ret; }
	var reg=/^(?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))$/;
	if(!reg.test(str)) ret = 1;
	var buf=str.split(".");
	if(!(buf[3]==0||buf[3]==128||buf[3]==192||buf[3]==224||buf[3]==240||buf[3]==248||buf[3]==252||buf[3]==254)) ret = 1;
	if(!(buf[2]==0||buf[2]==128||buf[2]==192||buf[2]==224||buf[2]==240||buf[2]==248||buf[2]==252||buf[2]==254||buf[2]==255)) ret = 1;
	if(!(buf[1]==0||buf[1]==128||buf[1]==192||buf[1]==224||buf[1]==240||buf[1]==248||buf[1]==252||buf[1]==254||buf[1]==255)) ret = 1;
	if(!( buf[0]==128||buf[0]==192||buf[0]==224||buf[0]==240||buf[0]==248||buf[0]==252||buf[0]==254||buf[0]==255)) ret = 1;
	if( (buf[1]==0 && (buf[2]!=0 || buf[3]!=0)) || (buf[2]==0 && buf[3]!=0 )) ret = 1;

	return ret;
};

/**
 * 校验IP地址
 * 
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   str                   传入mac字符串	
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，不是合法的IP地址<br/> 
 * 2: 无效，第1段必须是1~254之间的数字<br/> 
 * 3: 无效，第2、3段必须是0~254之间的数字<br/> 
 * 4: 无效，第4段必须是1~254之间的数字<br/> 
 * 99: 有效
 */
cs.prototype.ip = function (str){
	var ret = 99;
	if(undefined == str || str=="..." || str == "") { ret = 0;  return ret; }
	var reg=/^(?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))$/;
	if(!reg.test(str)) ret = 1;
	var buf=str.split(".");
	if(buf[0]<1||buf[0]>254) ret = 2;
	if(buf[1]>254||buf[2]>254) ret = 3;
	if(buf[3]<1||buf[3]>254) ret = 4;
	return ret;
};

/**
 * ???
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   s1                    ???
 * @param    {String}   mn                    ???
 * @param    {String}   s2                    ???
 * @return   {Number}
 * ???
 */
cs.prototype.ip_subnet = function (s1,mn,s2){
	var ip1=s1.split(".");
	var ip2=s2.split(".");
	var ip3=mn.split(".");
	for(var k=0;k<=3;k++){
		if((ip1[k]&ip3[k])!=(ip2[k]&ip3[k])) return 0;
	}
	return 1;
};

/**
 * 判断IP地址1是否大于IP地址2
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   s1                    IP地址1
 * @param    {String}   s2                    IP地址2
 * @return   {Number}
 * 0: 是 <br/>
 * 1: 否 
 */
cs.prototype.ip_range = function (s1,s2){
    if (undefined == s1 || undefined == s2) {
        return 1;
    }
	var ip1=s1.replace(/\.\d{1,3}$/,".");
	var ip2=s2.replace(/\.\d{1,3}$/,".");
	if (ip1>ip2) return 0;
	return 1;
};

/**
 * 判断两个ip地址是否相同
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2017-10-26
 * @param    {String}   s1                    IP地址1
 * @param    {String}   s2                    IP地址2
 * @return   {Number}
 * 0: 是 <br/>
 * 1: 否 
 */
cs.prototype.ip_same = function (s1,s2){
    if (undefined == s1 || undefined == s2) {
        return 1;
    }
    var ip1=s1.replace(/\.\d{1,3}$/,".");
    var ip2=s2.replace(/\.\d{1,3}$/,".");
    if (ip1==ip2) return 0;
    return 1;
};


/**
 * 返回定时器的步长时间
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-1-13
 * @param    {number}   time                    秒数
 * @return   {Number}
 * @example
 * var i = 0;
 * var Timer = setInterval(function(){
 *   i++;
 *   if（i>100）{
 *     clearInterval(Timer);
 *     // 进度条完成后的操作
 *   }
 * },cs.getTimer(10));
 * // 这个定时器十秒钟走完。
 */
cs.prototype.getTimer = function (time){
    return (parseInt(time)/100)*1000;
};

/**
 * 容量数据单位换算
 * @Author   Yexk       <yexk@carystudio.com>
 * @DateTime 2018-01-17
 * @param    {Number}   bytes                 字节数
 * @return   {String}                         换算后的单位
 * @example
 * console.log(cs.bytesToSize(1024)); // 1KB
 */
cs.prototype.bytesToSize = function(bytes) {
  if (bytes === 0) return '0 B';
    var k = 1000, // or 1024
        sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        i = Math.floor(Math.log(bytes) / Math.log(k));
 
   return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i]; 
};

/**
 * 校验域名
 *
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-05
 * @param    {String}   str                   传入域名字符串
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，不是合法的域名<br/>
 * 99: 有效
 */
cs.prototype.domain = function (str){
	var ret = 99;
	if(undefined == str || str=="") { ret = 0;  return ret; }
	// var reg=/^(?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))$/;
	var reg = /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/;
	if(!reg.test(str)) ret = 1;
	return ret;
};

/**
 * 时间戳转时间格式
 * @Author   Jeff       <yexk@carystudio.com>
 * @DateTime 2018-03-05
 * @param    {String}	时间戳
 * @param    {String}	时间格式
 * @return   {String}                         换算后的时间
 * @example
 * console.log(cs.bytesToSize(1024)); // 1KB
 */
cs.prototype.formatDate = function(date, fmt) {
	var data = parseInt(date+'000');
	var d = new Date(data);
	var o = {
		"M+": d.getMonth() + 1, //month
		"d+": d.getDate(),    //day
		"h+": d.getHours(),   //hour
		"m+": d.getMinutes(), //minute
		"s+": d.getSeconds(), //second
		"q+": Math.floor((d.getMonth() + 3) / 3),  //quarter
		"S": d.getMilliseconds() //millisecond
	}
	if (/(y+)/.test(fmt)) {
		fmt = fmt.replace(RegExp.$1,(d.getFullYear() + "").substr(4 - RegExp.$1.length));
	}
	for (var k in o) {
		if (new RegExp("(" + k + ")").test(fmt)) {
			fmt = fmt.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
		}
	}
	return fmt;
};

/**
 * 检测字符串长度是否符合规则
 * 规则：不能超过指定字符。
 *
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-3-12
 * @param    {String}   str                   字符串
 * @param    {Number}   num                   字符串
 * @return   {Number}
 * 0: 不能为空 <br/>
 * 1: 无效，包含了无效的字符 <br/>
 * 2: 无效，不能超过指定字符个数 <br/>
 * 99: 有效
 */
cs.prototype.srtLength = function (str,num){
	var ret = 99;
	if(str == undefined || str==""){ ret = 0;  return ret; }
	// var reg=/[\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60\x7E]/;
	// if(reg.test(str)) ret = 1;
	//for chinese
	var strlen = 0;
	for(var i = 0;i < str.length; i++){
		if(str.charCodeAt(i) > 255) strlen += 3;
		else strlen++;
	}
	if(strlen>num) ret = 2;
	return ret;
};

/**
 * 返回登录页面
 *
 * @Author   Jeff       <Jeff@carystudio.com>
 * @DateTime 2018-02-26
 * @param    {String}   null 空
 * @return   {Number}	null 无
 */
cs.prototype.loginOut= function (){
	top.location.href='/login/newlogout';
	// error();
};

obj.cs = new cs();
})(window);
