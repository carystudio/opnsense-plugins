/******************************************

**           js lib for using jquery

**           Date:2014-8-19

******************************************/



/*-----------check value---------------*/

var checkVaildVal = {

    isNumber: function(str) {

        var re = /^[0-9]*$/;

        if (!re.test(str))

            return 0;

        return 1;

    },



    IsVaildNumber: function(str, msg) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isNumber(str)) {

            alert(msg + JS_msg9);

            return 0;

        }

        return 1;

    },



    IsVaildNumberRange: function(str, msg, min, max) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isNumber(str)) {

            alert(msg + JS_msg9);

            return 0;

        }

        if ((parseInt(str) < min) || (parseInt(str) > max)) {

            alert(msg + JS_msg10 + min + "-" + max + JS_msg11);

            return 0;

        }

        return 1;

    },



    isAllChar: function(str) {

        if (/[\xB7]/.test(str))

            return 0;

        if (/[^\x00-\xff]/.test(str))

            return 0;

        return 1;

    },

    ischkHalf: function(str) {

        for (var i = 0; i < str.length; i++) {

            strCode = str.charCodeAt(i);

            if ((strCode > 65248) || (strCode == 12288))

                return 0;

        }

        return 1;

    },





    isHex: function(str) {

        var re = /[^A-Fa-f0-9]/;

        if (re.test(str))

            return 0;

        return 1;

    },

    isEnOrDig: function(str) {

        var re = /[^A-Za-z0-9]/;

        if (re.test(str))

            return 0;

        return 1;

    },

    isBlankCheck: function(v, m) {

        if ((v.length == 0) || (v.indexOf(" ") >= 0)) {

            alert(m + JS_msg73);

            return 0;

        }

        return 1;

    },

    isPortalString: function(str) {

        var re1 = /[^\x20-\x7D]/;

        var re2 = /[\x20\x22\x24\x25\x27\x2C\x3B\x3C\x3E\x5C\x60]/;

        if (re1.test(str) || re2.test(str))

            return 0;

        return 1;

    },

    isString: function(str) {

        var re1 = /[^\x20-\x7D]/;

        var re2 = /[\x20\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60]/;

        if (re1.test(str) || re2.test(str))

            return 0;

        return 1;

    },

    isString_N9: function(str) {

        var re1 = /[^\x21-\x7E]/;

        if (re1.test(str))

            return 0;

        return 1;

    },

    isDomain: function(str) {

        var postfix = /^([\w-]+\.)+((aero)|(asia)|(biz)|(cat)|(com)|(coop)|(edu)|(gov)|(info)|(jobs)|(mil)|(mobi)|(museum)|(name)|(net)|(org)|(pro)|(tel)|(travel)|(xxx)|(ac)|(ad)|(ae)|(af)|(ag)|(ai)|(al)|(am)|(an)|(ao)|(aq)|(ar)|(as)|(at)|(au)|(aw)|(ax)|(az)|(ba)|(bb)|(bd)|(be)|(bf)|(bg)|(bh)|(bi)|(bj)|(bm)|(bn)|(bo)|(br)|(bs)|(bt)|(bv)|(bw)|(by)|(bz)|(ca)|(cc)|(cd)|(cf)|(cg)|(ch)|(ci)|(ck)|(cl)|(cm)|(cn)|(co)|(cr)|(cu)|(cv)|(cx)|(cy)|(cz)|(de)|(dj)|(dk)|(dm)|(dz)|(ec)|(ee)|(eg)|(er)|(es)|(et)|(eu)|(fi)|(fj)|(fk)|(fm)|(fo)|(fr)|(ga)|(gb)|(gd)|(ge)|(gf)|(gg)|(gh)|(gi)|(gl)|(gm)|(gn)|(gp)|(gq)|(gr)|(gs)|(gt)|(gu)|(gw)|(gy)|(hk)|(hm)|(hn)|(hr)|(ht)|(hu))$/;

        var postfix1 = /^([\w-]+\.)+((id)|(ie)|(il)|(im)|(io)|(iq)|(ir)|(is)|(it)(je)|(jm)|(jo)|(jp)|(ke)|(kg)|(kh)|(ki)|(km)|(kn)|(kp)|(kr)|(kw)|(ky)|(kz)|(la)|(lb)|(lc)|(li)|(lk)|(lr)|(ls)|(lt)|(lu)|(lv)|(ly)|(ma)|(mc)|(md)|(me)|(mg)|(mh)|(mk)|(ml)|(mm)|(mn)|(mo)|(mp)|(mq)|(mr)|(ms)|(mt)|(mu)|(mv)|(mw)|(mx)|(my)|(mz)|(na)|(nc)|(ne)|(nf)|(ng)|(ni)|(nl)|(no)|(np)|(nr)|(nu)|(nz)|(om)|(pa)|(pe)|(pf)|(pg)|(ph)|(pk)|(pl)|(pm)|(pn)|(pr)|(ps)|(pt)|(pw)|(py)|(qa)|(re)|(ro)|(rs)|(ru)|(rw)|(sa)|(sb)|(sc)|(sd)|(se)|(sg)|(sh)|(si)|(sj)|(sk)|(sl)|(sm)|(sn)|(so)|(sr)|(st)|(su)|(sv)|(sy)|(sz)|(tc)|(td)|(tf)|(tg)|(th)|(tj)|(tk)|(tl))$/;

        var postfix2 = /^([\w-]+\.)+((tm)|(tn)|(to)|(tp)|(tr)|(tt)|(tv)|(tw)|(tz)|(ua)|(ug)|(uk)|(us)|(uy)|(uz)|(va)|(vc)|(ve)|(vg)|(vi)|(vn)|(vu)|(wf)|(ws)|(ye)|(yt)|(za)|(zm)|(zw))$/;

        if (!postfix.test(str) && !postfix1.test(str) && !postfix2.test(str))

        {

            return false;

        }

        return true;

    },

    isCommentString: function(str) {

        var re1 = /[^\x20-\x7D]/;

        var re2 = /[\x20\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60]/;

        if (re1.test(str) || re2.test(str))

            return 0;

        return 1;

    },



    isSSID: function(str) {

        //	var re1=/[^\x20-\x7D]/;

        var re2 = /[\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60\x7E]/;

        //	if(re1.test(str)||re2.test(str))

        if (re2.test(str))

            return 0;

        return 1;

    },



    strTrim: function(str) {

        str = str.replace(/^[\x20]*/, "");

        str = str.replace(/[\x20]*$/, "");

        str = str.replace(/[\x20]+/g, " ");

        return str;

    },



    IsVaildChineseString: function(str, msg) {



        var countChinese = 0;

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }



        if (str.length > 32) {

            alert(msg + JS_msg3);

            return 0;

        }



        if (!checkVaildVal.ischkHalf(str)) {

            alert(msg + JS_msg139);

            return 0;

        }

        for (var i = 0; i <= str.split("").length; i++) {

            if (/.*[^\u0000-\u00FF]+.*$/.test(str.split("")[i]))

                countChinese++;

        }

        if (str.length > 32 - (countChinese * 2)) {

            alert(msg + JS_msg140);

            return 0;

        }

        var reg0 = /[\x20\x22\x24\x25\x27\x2C\x2F\x3B\x3C\x3E\x5C\x60]/;

        if (reg0.test(str)) {

            alert(msg + JS_msg139);

            return 0;

        }

        //���ı�����Unicode

        var reg1 = /[\uff08\uff09\u3014\u3015\u3010\u3011\u2014\u2026\u2013\uff0e\u300a\u300b\u3008\u3009\u00b7\u00d7]/;

        var reg2 = /[\u3002\uff1f\uff01\uff0c\u3001\uff1b\uff1a\u300c\u300d\u300f\u2018\u2019\u201c\u201d]/;

        if (reg1.test(str) || reg2.test(str)) {

            alert(msg + JS_msg139);

            return 0;

        }

        return 1;

    },



    IsVaildUserString: function(str, msg) {

        if ((str == "") || (str.indexOf(" ") >= 0)) {

            alert(msg + JS_msg73);

            return 0;

        }

        if (!checkVaildVal.isEnOrDig(str)) {

            alert(msg + JS_msg74);

            return 0;

        }

        return 1;

    },

    IsVaildUsbString: function(str, msg, size, lang, flag) {

        if ((str == "") || (str.indexOf(" ") >= 0)) {

            alert(msg + JS_msg73);

            return 0;

        }

        var re, buf;

        if (flag == 1) { //����·����֧�֡����� 1�Ǵ���

            re = /[\x22\x24\x25\x27\x2A\x2C\x2F\x3A\x3B\x3C\x3E\x3F\x5C\x60\7C\x7E]/;

            buf = "";

        } else {

            re = /[\x22\x24\x25\x27\x2A\x2C\x2F\x3A\x3B\x3C\x3E\x5C\x60\7C\x7E]/;

            buf = "?";

        }

        if (re.test(str)) {

            if (lang == "cn")

                alert(msg + JS_msg144 + buf);

            else

                alert(msg + JS_msg145 + buf);

            return 0;

        }

        var countChinese = 0;

        if (lang == "cn") {

            for (var i = 0; i <= str.split("").length; i++) {

                if (/.*[^\u0000-\u00FF]+.*$/.test(str.split("")[i]))

                    countChinese++;

            }

            if (str.length > size - (countChinese * 2)) {

                alert(msg + JS_msg146 + size + " , " + JS_msg147);

                return 0;

            }

            //���ı�����Unicode

            var reg1 = /[\uff08\uff09\u3014\u3015\u3010\u3011\u2014\u2026\u2013\uff0e\u300a\u300b\u3008\u3009\u00b7\u00d7]/;

            var reg2 = /[\u3002\uff1f\uff01\uff0c\u3001\uff1b\uff1a\u300c\u300d\u300f\u2018\u2019\u201c\u201d]/;

            if (reg1.test(str) || reg2.test(str)) {

                alert(msg + JS_msg144);

                return 0;

            }

        } else {

            if (str.length > size) {

                alert(msg + JS_msg146 + size + "!");

                return 0;

            }

            if (!checkVaildVal.isAllChar(str)) {

                alert(msg + JS_msg144);

                return 0;

            }

        }

        return 1;

    },



    IsVaildString: function(str, msg, flag) {

        if (flag == 1 && str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isAllChar(str)) {

            alert(msg + JS_msg2);

            return 0;

        }

        if (flag == 1 && !checkVaildVal.isString(str)) {

            alert(msg + JS_msg6);

            if (str.length > 32) {

                alert(msg + JS_msg3);

                return 0;

            }

            return 0;

        } else if (flag == 2 && !checkVaildVal.isCommentString(str)) {

            alert(msg + JS_msg78);

            if (str.length > 20) {

                alert(msg + JS_msg4);

                return 0;

            }

            return 0;

        } else if (flag == 3 && !checkVaildVal.isString(str)) {

            alert(msg + JS_msg6);

            if (str.length > 128) {

                alert(msg + JS_msg5);

                return 0;

            }

            return 0;

        }

        return 1;

    },

    IsVaildPortal: function(str, msg) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isAllChar(str)) {

            alert(msg + JS_msg2);

            return 0;

        }

        if (!checkVaildVal.isPortalString(str)) {

            alert(msg + JS_msg7);

            if (str.length > 32) {

                alert(msg + JS_msg3);

                return 0;

            }

            return 0;

        }

        return 1;

    },

    IsVaildSSID: function(str, msg) {



        var countChinese = 0;

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }



        //	if(!checkVaildVal.isAllChar(str)){

        //		alert(msg+JS_msg2);

        //		return 0;   

        //	} 



        if (str.length > 32) {

            alert(msg + JS_msg3);

            return 0;

        }



        if (!checkVaildVal.ischkHalf(str)) {

            alert(msg + JS_msg129);

            return 0;

        }

        for (var i = 0; i <= str.split("").length; i++) {

            if (/.*[^\u0000-\u00FF]+.*$/.test(str.split("")[i]))

                countChinese++;

        }

        if (str.length > 32 - (countChinese * 2)) {

            alert(JS_msg130);

            return 0;

        }

        if (!checkVaildVal.isSSID(str)) {



            alert(msg + JS_msg129);

            return 0;

        }

        //���ı�����Unicode

        var reg1 = /[\uff08\uff09\u3014\u3015\u3010\u3011\u2014\u2026\u2013\uff0e\u300a\u300b\u3008\u3009\u00b7\u00d7]/;

        var reg2 = /[\u3002\uff1f\uff01\uff0c\u3001\uff1b\uff1a\u300c\u300d\u300f\u2018\u2019\u201c\u201d]/;

        if (reg1.test(str) || reg2.test(str)) {

            alert(msg + JS_msg129);

            return 0;

        }

        if (str.split("")[0] == " " || str.split("")[str.length - 1] == " ") {

            alert(msg + JS_msg8);

            return 0;

        }

        return 1;

    },



    IsVaildWiFiPass: function(str, msg, flag) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (flag == "ascii" && !checkVaildVal.isString(str)) {

            alert(msg + JS_msg6);

            return 0;

        }

        if (flag == "hex" && !checkVaildVal.isHex(str)) {

            alert(msg + JS_msg23);

            return 0;

        }

        return 1;

    },



    IsVaildWiFiPass_N9: function(str, msg, flag) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (flag == "ascii" && !checkVaildVal.isString_N9(str)) {

            alert(msg + JS_msg152);

            return 0;

        }

        if (flag == "hex" && !checkVaildVal.isHex(str)) {

            alert(msg + JS_msg23);

            return 0;

        }

        return 1;

    },



    IsVaildDomain: function(str, msg) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isDomain(str)) {

            alert(msg + JS_msg150);

            if (str.length > 32) {

                alert(msg + JS_msg3);

                return 0;

            }

            return 0;

        }

        return 1;

    },



    isPort: function(str) {

        if (!checkVaildVal.isNumber(str))

            return 0;

        if (parseInt(str) < 1 || parseInt(str) > 65535)

            return 0;

        return 1;

    },



    IsVaildPort: function(str, msg) {

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!checkVaildVal.isPort(str)) {

            alert(msg + JS_msg18);

            return 0;

        }

        return 1;

    },

    IsPortRange: function(s1, s2) {



        if (parseInt(s1) > parseInt(s2)) {

            alert(JS_msg87);

            return 0;

        }

        return 1;

    },

    isMAC: function(str) {

        //var re=/[A-Fa-f0-9]{12}/;

        var re = /[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}:[A-Fa-f0-9]{2}/;

        if (!re.test(str))

            return 0;

        return 1;

    },



    IsVaildMacAddr: function(str) {

        if (str.length != 17) {

            alert(JS_msg16);

            return 0;

        }

        if (!checkVaildVal.isMAC(str)) {

            alert(JS_msg16);

            return 0;

        }

        if (str == "00:00:00:00:00:00" || str.toUpperCase() == "FF:FF:FF:FF:FF:FF") {

            alert(JS_msg14);

            return 0;

        }

        for (var k = 0; k < str.length; k++) {

            if ((str.charAt(1) & 0x01) || (str.charAt(1).toUpperCase() == 'B') || (str.charAt(1).toUpperCase() == 'D') || (str.charAt(1).toUpperCase() == 'F')) {

                alert(JS_msg17);

                return 0;

            }

        }

        return 1;

    },



    IsVaildIpAddr: function(str, msg) {

        var re = /^(?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))$/;

        var buf;

        if (str == "") {

            alert(msg + JS_msg1);

            return 0;

        }

        if (!re.test(str)) {

            alert(msg + JS_msg61);

            return 0;

        }

        buf = str.split(".");

        if (buf[3] < 1 || buf[3] > 254) {

            alert(msg + JS_msg62);

            return 0;

        }

        return 1;

    },



    isIP: function(str) {

        var re = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;

        var buf;

        if (!re.test(str))

            return 0;

        buf = str.split(".");

        for (i = 0; i < 4; i++) {

            if (buf[i] < 0 || buf[i] > 255)

                return 0;

        }

        return 1;

    },



    isMask: function(str) {

        if (!checkVaildVal.isIP(str))

            return 0;

        var buf = str.split(".");

        if (!(buf[3] == 0 || buf[3] == 128 || buf[3] == 192 || buf[3] == 224 || buf[3] == 240 || buf[3] == 248 || buf[3] == 252 || buf[3] == 254))

            return 0;

        if (!(buf[2] == 0 || buf[2] == 128 || buf[2] == 192 || buf[2] == 224 || buf[2] == 240 || buf[2] == 248 || buf[2] == 252 || buf[2] == 254 || buf[2] == 255))

            return 0;

        if (!(buf[1] == 0 || buf[1] == 128 || buf[1] == 192 || buf[1] == 224 || buf[1] == 240 || buf[1] == 248 || buf[1] == 252 || buf[1] == 254 || buf[1] == 255))

            return 0;

        if (!(buf[0] == 128 || buf[0] == 192 || buf[0] == 224 || buf[0] == 240 || buf[0] == 248 || buf[0] == 252 || buf[0] == 254 || buf[0] == 255))

            return 0;

        return 1;

    },



    IsVaildMaskAddr: function(str, msg) {

        if (str == "") {

            alert(JS_msg79);

            return 0;

        }

        if (!checkVaildVal.isIP(str)) {

            alert(JS_msg79);

            return 0;

        }

        var buf = str.split(".");

        if (buf[0] == 255 && buf[1] == 255 && buf[2] == 255) {

            if (!(buf[3] == 0 || buf[3] == 128 || buf[3] == 192 || buf[3] == 224 || buf[3] == 240 || buf[3] == 248 || buf[3] == 252 || buf[3] == 254)) {

                alert(JS_msg79);

                return 0;

            }

        }

        if (buf[0] == 255 && buf[1] == 255 && buf[3] == 0) {

            if (!(buf[2] == 0 || buf[2] == 128 || buf[2] == 192 || buf[2] == 224 || buf[2] == 240 || buf[2] == 248 || buf[2] == 252 || buf[2] == 254 || buf[2] == 255)) {

                alert(JS_msg79);

                return 0;

            }

        }

        if (buf[0] == 255 && buf[2] == 0 && buf[3] == 0) {

            if (!(buf[1] == 0 || buf[1] == 128 || buf[1] == 192 || buf[1] == 224 || buf[1] == 240 || buf[1] == 248 || buf[1] == 252 || buf[1] == 254 || buf[1] == 255)) {

                alert(JS_msg79);

                return 0;

            }

        }

        if (buf[1] == 0 && buf[2] == 0 && buf[3] == 0) {

            if (!(buf[0] == 128 || buf[0] == 192 || buf[0] == 224 || buf[0] == 240 || buf[0] == 248 || buf[0] == 252 || buf[0] == 254 || buf[0] == 255)) {

                alert(JS_msg79);

                return 0;

            }

        }

        if (!((buf[0] == 255 && buf[1] == 255 && buf[2] == 255) || (buf[0] == 255 && buf[1] == 255 && buf[3] == 0) || (buf[0] == 255 && buf[2] == 0 && buf[3] == 0) || (buf[1] == 0 && buf[2] == 0 && buf[3] == 0))) {

            alert(JS_msg79);

            return 0;

        }

        return 1;

    },



    IsIpRange: function(startIP, endIP) {

        var ip1 = startIP.split(".");

        var ip2 = endIP.split(".");

        if (Number(ip1[0]) > Number(ip2[0])) {

            alert(JS_msg41);

            return 0;

        }

        if (ip1[0] == ip2[0]) {

            if (Number(ip1[1]) > Number(ip2[1])) {

                alert(JS_msg41);

                return 0;

            }

        }

        if (ip1[0] == ip2[0] && ip1[1] == ip2[1]) {

            if (Number(ip1[2]) > Number(ip2[2])) {

                alert(JS_msg41);

                return 0;

            }

        }



        if (ip1[0] == ip2[0] && ip1[1] == ip2[1] && ip1[2] == ip2[2]) {

            if (Number(ip1[3]) > Number(ip2[3])) {

                alert(JS_msg41);

                return 0;

            }

        }

        return 1;

    },



    IsIpSubnet: function(s1, mn, s2) {

        var ip1 = s1.split(".");

        var ip2 = s2.split(".");

        var ip3 = mn.split(".");

        for (var k = 0; k <= 3; k++) {

            if ((ip1[k] & ip3[k]) != (ip2[k] & ip3[k]))

                return 0;

        }

        return 1;

    },

    IsNotWanSubnet: function(s1, mn, s2) {

        var ip1 = s1.split(".");

        var ip2 = s2.split(".");

        var ip3 = mn.split(".");

        var count = 0;

        for (var k = 0; k < 3; k++)

            if ((ip1[k] & ip3[k]) == (ip2[k] & ip3[k]))

                count++;

        if (count == 3)

            return 0;

        else

            return 1;

    },

    IsNotDhcpPool: function(s1, s2, ip) {

        var ip1 = s1.split(".");

        var ip2 = s2.split(".");

        var ip3 = ip.split(".");



        if (ip === "")

            return 1;



        var iip1 = parseInt(ip1[0] * 256 * 256 * 256) + parseInt(ip1[1] * 256 * 256) + parseInt(ip1[2] * 256) + parseInt(ip1[3]);

        var iip2 = parseInt(ip2[0] * 256 * 256 * 256) + parseInt(ip2[1] * 256 * 256) + parseInt(ip2[2] * 256) + parseInt(ip2[3]);

        var iip3 = parseInt(ip3[0] * 256 * 256 * 256) + parseInt(ip3[1] * 256 * 256) + parseInt(ip3[2] * 256) + parseInt(ip3[3]);



        if (iip3 < iip1 || iip3 > iip2)

            return 1;

        else

            return 0;

    },

    IsSameIp: function(s1, s2) {

        ip1 = s1.replace(/\.\d{1,3}$/, ".");

        ip2 = s2.replace(/\.\d{1,3}$/, ".");

        if (ip1 == ip2) return 0;

        return 1;

    }

}



function uiPost(postVar)

{

    postVar = JSON.stringify(postVar);

    $(":input").attr('disabled', true);

    $.post(" /cgi-bin/cstecgi.cgi", postVar,

        function(Data) {

            resetForm();

        });

}

var lanip = '',
    wtime = 0;

function waitpage() {

    $("#div_body_setting").hide();

    $("#div_wait").show();

}



function do_count_down() {

    supplyValue('show_sec', wtime);

    //if(wtime == 0) {parent.location.href='http://'+lanip+'/login.asp'; return false;}

    if (wtime == 0) { location.href = addURLTimestamp(location.href); }

    if (wtime > 0) {
        wtime--;
        setTimeout('do_count_down()', 1000);
    }

}

function do_count_down5() {

    supplyValue('show_sec', wtime);

    if (wtime == 0) { top.location.reload(); }

    if (wtime > 0) {
        wtime--;
        setTimeout('do_count_down5()', 1000);
    }

}



function uiPost2(postVar) {

    postVar = JSON.stringify(postVar);

    $(":input").attr('disabled', true);



    $.post(" /cgi-bin/cstecgi.cgi", postVar,

        function(Data) {

            var responseJson = JSON.parse(Data);

            lanip = responseJson['lan_ip'];

            wtime = responseJson['wtime'];

            waitpage();

            do_count_down();

        });

}



function uiPost4(postVar) {

    postVar = JSON.stringify(postVar);

    $(":input").attr('disabled', true);



    $.post(" /cgi-bin/cstecgi.cgi", postVar,

        function(Data) {

            var responseJson = JSON.parse(Data);

            lanip = responseJson['lan_ip'];

            top.location.reload();

        });

}



function uiPost5(postVar) {

    postVar = JSON.stringify(postVar);

    $(":input").attr('disabled', true);



    $.post(" /cgi-bin/cstecgi.cgi", postVar,

        function(Data) {

            var responseJson = JSON.parse(Data);

            lanip = responseJson['lan_ip'];

            wtime = responseJson['wtime'];

            waitpage();

            do_count_down5();

        });

}

function chgLanguage(val) {

    var postVar = { topicurl: "setting/setLanguageCfg" };

    postVar['langType'] = val;

    postVar = JSON.stringify(postVar);

    $.ajax({

        type: "post",

        url: " /cgi-bin/cstecgi.cgi",

        data: postVar,

        async: false,

        success: function(Data) {

            setTimeout('top.location.reload()', 500);

        }

    });

    if (val != "")

        parent.frames["view"].document.getElementById("languageDiv").style.display = "none";

}



function setDisabled(objId, bool) {

    $(objId).attr("disabled", bool);

}



///////////jquery///////////////

function supplyValue(Name, Value) {

    var node;

    node = $("#" + Name);

    if (node[0] == undefined)

        node = $("input[name=" + Name + "]");



    var bigType = node[0].tagName || node.get(0).tagName;

    switch (bigType) {

        case 'TD':
            {}

        case 'DIV':
            {}

        case 'SPAN':
            {

                node.html(Value);

                break;

            }

        case 'SELECT':
            {

                node.val(Value);

                break;

            }

        case 'INPUT':
            {

                var smallType = node[0].type;

                switch (smallType) {

                    case 'text':

                    case 'hidden':

                    case 'password':
                        {

                            node.val(Value);

                            break;

                        }

                    case 'radio':
                        {

                            $("input:radio[name=" + Name + "][value='" + Value + "']").prop("checked", true);

                            break;

                        }

                    case 'checkbox':
                        {

                            if (Value == 1)

                                node.attr("checked", true);

                            else

                                node.attr("checked", false);

                            break;

                        }

                }

            }

    }

}

function setJSONValue(array_json) {

    if (typeof array_json != 'object') {
        return false;
    }

    var element;



    for (var i in array_json) {

        element = $("#" + i) || $("input[name=" + i + "]");

        if (element != null) {

            supplyValue(i, array_json[i]);

        }

    }

}





function CreateOptions(nodeName, optionValue, valueArray) {

    var Node = document.getElementById(nodeName),
        valueOptions;



    $('#' + nodeName).empty();

    Node.options.length = 0;

    if (valueArray == undefined) {

        valueOptions = optionValue;

    } else {

        valueOptions = valueArray;

    }



    for (var i = 0; i < optionValue.length; i++) {

        Node.options[i] = new Option(optionValue[i]);

        Node.options[i].value = valueOptions[i];



    }

}



function HWKeyUp(prefix, idx, event) {



    var obj = document.getElementsByName(prefix + idx);

    var nextidx = idx + 1;

    var keynum;



    if (window.event)

        keynum = event.keyCode;

    else if (event.which) // Netscape/Firefox/Opera

        keynum = event.which;



    if (keynum == 9 || keynum == 8) return;



    if (obj[0].value.length == 2) {

        obj = document.getElementsByName(prefix + nextidx);

        if (obj[0]) obj[0].focus();

        return;

    }

}

function CheckHex(keynum) {

    if (((keynum >= 96) && (keynum <= 105)) || ((keynum >= 48) && (keynum <= 57)) || ((keynum >= 65) && (keynum <= 70))) return true;

    return false;

}

function HWKeyDown(prefix, idx, event) {



    var obj = document.getElementsByName(prefix + idx);

    var previdx = idx - 1;



    if (window.event)

        keynum = event.keyCode;

    else if (event.which) // Netscape/Firefox/Opera

        keynum = event.which;



    if ((keynum == 9) || (keynum == 46) || (keynum == 8)) {

        if (obj[0].value.length == 0 && event.keyCode == 8) {

            obj = document.getElementsByName(prefix + previdx);

            if (obj[0]) obj[0].focus();

        }

        return 1;

    }

    return CheckHex(keynum);

}



//e->input event; o->input object; i->input number

function setFocusFirst(obj) {

    if (obj.createTextRange) { //IE

        var txt = obj.createTextRange();

        txt.moveStart('character', obj.value.length);

        txt.collapse(true);

        txt.select();

    } else

        obj.focus();

}

function setFocusLast(obj) {

    if (obj.setSelectionRange) { //FF

        obj.setSelectionRange(0, 0);

        obj.focus();

    } else

        obj.focus();

}

function setFocusAll(obj) {



    if (obj.createTextRange) { //IE

        var txt = obj.createTextRange();

        txt.moveStart("character", 0);

        txt.moveEnd("character", obj.value.length);

        txt.select();

    } else if (obj.setSelectionRange) { //FF

        obj.setSelectionRange(0, obj.value.length);

        obj.focus();

    }

}

function ipVali(e, n, i) {

    var co = e.keyCode;

    var sh = e.shiftKey;



    var inputs = document.getElementsByName(n);

    if (co == 8 || co == 16 || co == 46 || (co >= 48 && co <= 57) || (co >= 96 && co <= 105) || co == 116) {

        if (sh && co >= 48 && co <= 57)

            return false;

        if (co == 8) {

            if ((inputs[i].value == "") && (inputs[i - 1] != null))

                setFocusFirst(inputs[i - 1]); //?��

            return true;

        }

        if (co == 46) {

            if ((inputs[i].value == "") && (inputs[i + 1] != null))

                setFocusLast(inputs[i + 1]); //?

            return true;

        }

        /*if(inputs[i].value.length>=3){

        	if(inputs[i+1] != null)

        		setFocusAll(inputs[i+1]);

        }*/

    } else if (co == 9 || co == 37 || co == 39 || co == 110 || co == 190) {

        if (co == 9) return true;

        if (co == 37) {

            if (inputs[i].value != "")

                return true;

            else if (inputs[i - 1] != null)

                inputs[i - 1].focus();

            return false;

        }

        if (co == 39) {

            if (inputs[i].value != "")

                return true;

            else if (inputs[i + 1] != null)

                inputs[i + 1].focus();

            return false;

        }

        if (co == 110 || co == 190) {

            if (inputs[i].value.length > 0 && inputs[i + 1] != null)

                setFocusAll(inputs[i + 1]);

            return false;

        }

    } else {

        return false;

    }

}

function ipVali2(n, i) {

    var inputs = document.getElementsByName(n);

    if (inputs[i].value < 0 || inputs[i].value > 255) {

        alert(JS_msg60);

        setFocusAll(inputs[i]);

        return false;

    }

}

//url_filtering.asp

//ke neng bu yong yi xia 4 ge han shu

function scheduleTimeCheck(form_name) {

    if (form_name.time_all.checked == false) {

        if (!scheduleTimeRangeCheck(form_name.time_h1.value, 1)) return 1;

        if (!scheduleTimeRangeCheck(form_name.time_h2.value, 1)) return 1;

        if (!scheduleTimeRangeCheck(form_name.time_m1.value, 0)) return 1;

        if (!scheduleTimeRangeCheck(form_name.time_m2.value, 0)) return 1;

        if (!scheduleTimeCmpCheck(form_name.time_h1.value, form_name.time_h2.value, form_name.time_m1.value, form_name.time_m2.value)) return 1;

    }

    return 0;

}

function scheduleTimeRangeCheck(val, flag) {

    var t = /[^0-9]{1,2}/;

    if (t.test(val)) {

        alert(JS_msg9);

        return 0;

    }

    if (flag == 1) { //hour

        if (parseInt(val) < 0 || parseInt(val) > 23) {

            alert(JS_msg94);

            return 0;

        }

    } else { //minute

        if (parseInt(val) < 0 || parseInt(val) > 59) {

            alert(JS_msg95);

            return 0;

        }

    }

    return 1;

}



function scheduleTimeCmpCheck(v1, v2, v3, v4) {

    if (v1.length == 2 && v1.charAt(0) == 0)

        v1 = v1.charAt(1);

    if (v2.length == 2 && v2.charAt(0) == 0)

        v2 = v2.charAt(1);

    if (v3.length == 2 && v3.charAt(0) == 0)

        v3 = v3.charAt(1);

    if (v4.length == 2 && v4.charAt(0) == 0)

        v4 = v4.charAt(1);



    if (parseInt(v1) > parseInt(v2)) {

        alert(JS_msg96);

        return 0;

    }



    if (parseInt(v1) == parseInt(v2)) {

        if (parseInt(v3) > parseInt(v4)) {

            alert(JS_msg96);

            return 0;

        }

    }

    return 1;

}



function scheduleWeekCheck(form_name) {

    var i;

    if (form_name.week_all.checked == false) {

        for (i = 1; i <= 7; i++) {

            if (eval("form_name.week_" + i + ".checked") == true)

                return 0;

        }

        alert(JS_msg97);

        return 1;

    }

    return 0;

}



function scheduleTime(form_name) {

    if (form_name.time_all.checked == true) {

        form_name.time_h1.disabled = true;

        form_name.time_h2.disabled = true;

        form_name.time_m1.disabled = true;

        form_name.time_m2.disabled = true;

    } else {

        form_name.time_h1.disabled = false;

        form_name.time_h2.disabled = false;

        form_name.time_m1.disabled = false;

        form_name.time_m2.disabled = false;

    }

}





function scheduleWeek(form_name) {

    if (form_name.week_all.checked == true) {

        form_name.week_1.disabled = true;

        form_name.week_2.disabled = true;

        form_name.week_3.disabled = true;

        form_name.week_4.disabled = true;

        form_name.week_5.disabled = true;

        form_name.week_6.disabled = true;

        form_name.week_7.disabled = true;



        form_name.week_1.checked = true;

        form_name.week_2.checked = true;

        form_name.week_3.checked = true;

        form_name.week_4.checked = true;

        form_name.week_5.checked = true;

        form_name.week_6.checked = true;

        form_name.week_7.checked = true;

    } else {

        form_name.week_1.disabled = false;

        form_name.week_2.disabled = false;

        form_name.week_3.disabled = false;

        form_name.week_4.disabled = false;

        form_name.week_5.disabled = false;

        form_name.week_6.disabled = false;

        form_name.week_7.disabled = false;



        form_name.week_1.checked = false;

        form_name.week_2.checked = false;

        form_name.week_3.checked = false;

        form_name.week_4.checked = false;

        form_name.week_5.checked = false;

        form_name.week_6.checked = false;

        form_name.week_7.checked = false;

    }

}



function scheduleSyncTime(form_name) {

    var currentTime = new Date();



    var seconds = currentTime.getSeconds();

    var minutes = currentTime.getMinutes();

    var hours = currentTime.getHours();

    var month = currentTime.getMonth() + 1;

    var day = currentTime.getDate();

    var year = currentTime.getFullYear();



    var seconds_str = " ";

    var minutes_str = " ";

    var hours_str = " ";

    var month_str = " ";

    var day_str = " ";

    var year_str = " ";



    if (seconds < 10)

        seconds_str = "0" + seconds;

    else

        seconds_str = "" + seconds;



    if (minutes < 10)

        minutes_str = "0" + minutes;

    else

        minutes_str = "" + minutes;



    if (hours < 10)

        hours_str = "0" + hours;

    else

        hours_str = "" + hours;



    if (month < 10)

        month_str = "0" + month;

    else

        month_str = "" + month;



    if (day < 10)

        day_str = "0" + day;

    else

        day_str = day;



    var tmp1 = month_str + day_str + hours_str + minutes_str + year + " ";

    var tmp2 = hours_str + ":" + minutes_str + ":" + seconds_str;

    form_name.CurTime1.value = tmp1;

    form_name.CurTime2.value = tmp2;

}



function checkDate(str) {

    var month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    var week = [MM_week7, MM_week1, MM_week2, MM_week3, MM_week4, MM_week5, MM_week6];



    if ((str.substring(4, 5)) == " ") str = str.replace(" ", "");

    else str = str;



    var t = str.split(" ");

    for (var j = 0; j < 12; j++) {

        if (t[0] == month[j]) t[0] = j + 1;

    }

    return t[2] + "-" + t[0] + "-" + t[1] + "  " + t[3];

}



function combinMAC2(m1, m2, m3, m4, m5, m6) {

    var mac = m1.toUpperCase() + ":" + m2.toUpperCase() + ":" + m3.toUpperCase() + ":" + m4.toUpperCase() + ":" + m5.toUpperCase() + ":" + m6.toUpperCase();

    if (mac == ":::::")

        mac = "";

    return mac;



}



function combinIP(d) {

    if (d.length != 4) return d.value;

    var ip = d[0].value + "." + d[1].value + "." + d[2].value + "." + d[3].value;

    if (ip == "...")

        ip = "";

    return ip;

}



function decomIP2(ipa, ips, nodef, count) {

    var re = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;

    if (re.test(ips)) {

        var d = ips.split(".");

        for (i = 0; i < count; i++) {

            ipa[i].value = d[i];

            if (!nodef) ipa[i].defaultValue = d[i];

        }

        return true;

    }

    return false;

}





function decomIP(ipa, ips, nodef) {

    var re = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;

    if (re.test(ips)) {

        var d = ips.split(".");

        for (i = 0; i < 4; i++) {

            ipa[i].value = d[i];

            if (!nodef) ipa[i].defaultValue = d[i];

        }

        return true;

    }

    return false;

}



function getMaskLength(ipv4)

{

    var aIPsec = ipv4.split(".");

    var len = 0;

    for (var i = 0; i < 4; i++) {

        aIPsec[i] = parseInt(aIPsec[i]).toString(2);

    }

    var nIPaddr = aIPsec[0].toString() + aIPsec[1].toString() + aIPsec[2].toString() + aIPsec[3].toString();

    for (i = 0; i < nIPaddr.length; i++) {

        if (nIPaddr.charAt(i) == 1)

            len++;

    }

    return len;

}

function openWindow(url, windowName, wide, high) {

    if (document.all)

        var xMax = screen.width,
        yMax = screen.height;

    else if (document.layers)

        var xMax = window.outerWidth,
        yMax = window.outerHeight;

    else

        var xMax = 640,
        yMax = 500;



    var xOffset = (xMax - wide) / 2;

    var yOffset = (yMax - high) / 3;

    var settings = 'width=' + wide + ',height=' + high + ',screenX=' + xOffset + ',screenY=' + yOffset + ',top=' + yOffset + ',left=' + xOffset + ',resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes';

    var win = window.open(url, windowName, settings);

    win.opener = window;

}



function progressBar(oBt, oBc, oBg, oBa, oWi, oHi, oDr) {

    MWJ_progBar++;
    this.id = 'MWJ_progBar' + MWJ_progBar;
    this.dir = oDr;
    this.width = oWi;
    this.height = oHi;
    this.amt = 0;

    //write the bar as a layer in an ilayer in two tables giving the border

    document.write('<span id="progress_div" style="display:none"><table border="0" cellspacing="0" cellpadding="' + oBt + '">' +

        '<tr><td bgcolor="' + oBc + '">' +

        '<table border="0" cellspacing="0" cellpadding="0"><tr><td height="' + oHi + '" width="' + oWi + '" bgcolor="' + oBg + '">');

    if (document.layers) {

        document.write('<ilayer height="' + oHi + '" width="' + oWi + '"><layer bgcolor="' + oBa + '" name="MWJ_progBar' + MWJ_progBar + '"></layer></ilayer>');

    } else {

        document.write('<div style="position:relative;top:0px;left:0px;height:' + oHi + 'px;width:' + oWi + ';">' +

            '<div style="position:absolute;top:0px;left:0px;height:0px;width:0;font-size:1px;background-color:' + oBa + ';" id="MWJ_progBar' + MWJ_progBar + '"></div></div>');

    }

    document.write('</td></tr></table></td></tr></table></span>\n');

    this.setBar = resetBar; //doing this inline causes unexpected bugs in early NS4

    this.setCol = setColour;

}

function resetBar(a, b) {

    //work out the required size and use various methods to enforce it

    this.amt = (typeof(b) == 'undefined') ? a : b ? (this.amt + a) : (this.amt - a);

    if (isNaN(this.amt)) { this.amt = 0; }
    if (this.amt > 1) { this.amt = 1; }
    if (this.amt < 0) { this.amt = 0; }

    var theWidth = Math.round(this.width * ((this.dir % 2) ? this.amt : 1));

    //alert(theWidth);

    var theHeight = Math.round(this.height * ((this.dir % 2) ? 1 : this.amt));

    var theDiv = getRefToDivNest(this.id);
    if (!theDiv) {
        window.status = 'Progress: ' + Math.round(100 * this.amt) + '%';
        return;
    }

    if (theDiv.style) {
        theDiv = theDiv.style;
        theDiv.clip = 'rect(0px ' + theWidth + 'px ' + theHeight + 'px 0px)';
    }

    var oPix = document.childNodes ? 'px' : 0;

    theDiv.width = theWidth + oPix;
    theDiv.pixelWidth = theWidth;
    theDiv.height = theHeight + oPix;
    theDiv.pixelHeight = theHeight;

    if (theDiv.resizeTo) { theDiv.resizeTo(theWidth, theHeight); }

    theDiv.left = ((this.dir != 3) ? 0 : this.width - theWidth) + oPix;
    theDiv.top = ((this.dir != 4) ? 0 : this.height - theHeight) + oPix;

}



function setColour(a) {

    //change all the different colour styles

    var theDiv = getRefToDivNest(this.id);
    if (theDiv.style) { theDiv = theDiv.style; }

    theDiv.bgColor = a;
    theDiv.backgroundColor = a;
    theDiv.background = a;

}

function addURLTimestamp(url) {
    return url;
    var _url = url.split('#')[0];

    if (_url.indexOf('?') == -1) {

        _url += '?timestamp=' + (new Date()).getTime();

    } else {

        if (_url.indexOf('timestamp') == -1)

            _url += "&timestamp=" + (new Date()).getTime();

        else

            _url = _url.replace(/timestamp=.*/ig, "timestamp=" + (new Date()).getTime());

    }

    return _url;

}

function resetForm() {

    location.href = addURLTimestamp(location.href);

}

function getRefToDivNest(divID, oDoc) {

    if (!oDoc) { oDoc = document; }

    if (document.layers) {

        if (oDoc.layers[divID]) {
            return oDoc.layers[divID];
        } else {

            for (var x = 0, y; !y && x < oDoc.layers.length; x++) {

                y = getRefToDivNest(divID, oDoc.layers[x].document);
            }

            return y;
        }
    }

    if (document.getElementById) {
        return document.getElementById(divID);
    }

    if (document.all) {
        return document.all[divID];
    }

    return document[divID];

}

function showLanguageLabel() {

    //document.write("<div id=\"waitfor\"><span>Set Tips</span></div>");

    document.write("<div id=\"languageDiv\" style=\"display:none\"><table width=172 border=0 cellpadding=3 cellspacing=0>\
 < tr > < td colspan = 2 height = 12 > < /td></tr > \
\
        < tr > < td id = \"languageTitle1\" class=\"languageTitle\" onclick=\"chgLanguage(top.frames['title'].v_lang1)\">" + MM_lang_en + "</a></td>\
 < td > < img id = \"img_language1\" src=\"../style/language_check.gif\" border=0></td></tr>\
 < tr > < td id = \"languageTitle2\" class=\"languageTitle\" onclick=\"chgLanguage(top.frames['title'].v_lang2)\">" + MM_lang_cn + "</a></td>\
 < td > < img id = \"img_language2\" src=\"../style/language_no_check.gif\" border=0></td></tr>\
 < /table></div > ");

    window.onscroll = moveLanguagePosition;

}

function moveLanguagePosition() {

    document.getElementById("languageDiv").style.pixelTop = document.body.scrollTop;

}



function addEventHandler(target, type, func)

{

    if (target.addEventListener)

        target.addEventListener(type, func, false);

    else if (target.attachEvent)

        target.attachEvent("on" + type, func);

    else

        target["on" + type] = func;

}

addEventHandler(document, "mousemove", function() {
    try { top.frames['title'].pageTimeoutDeal(); } catch (e) {}
});



function stopDefault(e) {

    if (e && e.preventDefault)

        e.preventDefault();

    else

        window.event.returnValue = false;

    return false;

}



function userBrowser() {

    var browserName = navigator.userAgent.toLowerCase();

    if (/msie/i.test(browserName) && !/opera/.test(browserName)) {

        return "IE";

    } else if (/firefox/i.test(browserName)) {

        return "Firefox";

    } else if (/chrome/i.test(browserName) && /webkit/i.test(browserName) && /mozilla/i.test(browserName)) {

        return "Chrome";

    } else if (/opera/i.test(browserName)) {

        return "Opera";

    } else if (/mobile/i.test(browserName) && (/baidubrowser/i.test(browserName))) {

        return "mobBaidu";

    } else if (/mobile/i.test(browserName) && (/qqbrowser/i.test(browserName))) {

        return "mobQQ";

    } else if (/mobile/i.test(browserName) && (/ucbrowser/i.test(browserName))) {

        return "mobUC";

    } else if (/webkit/i.test(browserName) && !(/chrome/i.test(browserName) && /webkit/i.test(browserName) && /mozilla/i.test(browserName))) {

        return "Safari";

    } else if (/mobile/i.test(browserName) && !(/browser/i.test(browserName))) {

        return "mobSafari";

    } else {

        return "unKnow";

    }

}



function getGroupNameByGid(GroupCfg, Gid)

{

    var groupname = "";

    if (Gid == "1")

        groupname = MM_notgroup;

    else {

        for (var i = 0; i < GroupCfg.length; i++) {

            if (Gid == GroupCfg[i].ID)

                groupname = GroupCfg[i].NAME;

        }

    }

    return groupname;

}



function CreateGroupSelect(JsonGroupConfig) {

    var new_options, new_values;

    if (JsonGroupConfig.length <= 1) {

        new_values = ["0", "1"];

        new_options = [MM_all, MM_notgroup];

    } else {

        new_values = ["0", "1"];

        new_options = [MM_all, MM_notgroup];

        for (var i = 1; i < JsonGroupConfig.length; i++) {

            new_values.push(JsonGroupConfig[i].ID);

            new_options.push(JsonGroupConfig[i].NAME);

        }

    }

    CreateOptions('SELECT_group', new_options, new_values);

    supplyValue('SELECT_group', new_values[0]);

}
