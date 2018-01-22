var cur_id=0;
var cur_sub_id=0;
function Node(id, pid, name, url, target, opmode, usb_basic, ra0, csauth) 
{
	this.id = id;
	this.pid = pid;
	this.name = name;
	this.url = url;
	this.target = target;
	this.opmode = opmode;
	this.usb_basic = usb_basic;
	this.ra0 = ra0;
	this.csauth = csauth;
}

function Menu(objName) 
{
	this.obj = objName;
	this.aNodes = [];
}

// Adds a new node to the node array
Menu.prototype.add = function(id, pid, name, url, target, opmode, usb_basic, ra0, csauth)
{   
    this.aNodes[this.aNodes.length] = new Node(id, pid, name,url, target, opmode, usb_basic, ra0, csauth);
}

Menu.prototype.toString = function()
{
    var str = '';
  
    var n=0;
    var id='';
    var pid='';
    var name='';
    var url=''; 
	var target='';
	var opmode='';
	var usb_basic='';
	var ra0='';
	var csauth='';
    for (n; n<this.aNodes.length; n++) 
    {
        id = this.aNodes[n].id;
        pid = this.aNodes[n].pid;
        name = this.aNodes[n].name;
        url = this.aNodes[n].url;
		target = this.aNodes[n].target;
		opmode = this.aNodes[n].opmode;
		usb_basic = this.aNodes[n].usb_basic;
		ra0 = this.aNodes[n].ra0;
		csauth = this.aNodes[n].csauth;
        
        if(pid==0)
        {
            str += "<A href=";
            str += url;
            str += " target=";
			str += target;
			str += "><div id =";
            str += id;
			if(1==id || 2==id || 13==id || 14==id || 21==id || 22==id || 30==id || 31==id || 32==id ||33==id)
				str += "  class=left_title onmouseover=My_T_Over(";
			else
				str += "  class=left_title1 onmouseover=My_T_Over(";
            str += id;
            str += ")";
            str += " onmouseout=My_T_Out(";
            str += id;
            str += ")";
            str += " onclick=My_Open_T(";
            str += id;
            str += ")>";
			
			str += "<ul style='clear:both;'><li style='float:left;height:34px;'>";
			if (1==id)
				str += "<img id=img1 src=\"/images/icon_System_Status.png\" border=\"0\">";
			else if (2==id)
				str += "<img id=img2 src=\"/images/icon_Opmode.png\" border=\"0\">";
			else if (3==id)
				str += "<img id=img3 src=\"/images/icon_Network.png\" border=\"0\">";
			else if (4==id)
				str += "<img id=img4 src=\"/images/icon_Wireless.png\" border=\"0\">";
			else if (7==id&&usb_basic==1)
				str += "<img id=img7 src=\"/images/icon_USB_Storage.png\" border=\"0\">";
			else if (8==id)
				str += "<img id=img8 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (9==id)
				str += "<img id=img9 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (10==id)
				str += "<img id=img10 src=\"/images/icon_Wireless.png\" border=\"0\">";
			else if (13==id)
				str += "<img id=img13 src=\"/images/icon_Wireless.png\" border=\"0\">";
			else if (14==id)
				str += "<img id=img14 src=\"/images/icon_System_Status.png\" border=\"0\">";
			else if (15==id)
				str += "<img id=img15 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (16==id)
				str += "<img id=img16 src=\"/images/icon_QoS.png\" border=\"0\">";
			else if (17==id)
				str += "<img id=img17 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (18==id)
				str += "<img id=img18 src=\"/images/icon_System_Status.png\" border=\"0\">";
			else if (19==id)
				str += "<img id=img19 src=\"/images/icon_Network.png\" border=\"0\">";
			else if (20==id)
				str += "<img id=img20 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (21==id)
				str += "<img id=img21 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (22==id)
				str += "<img id=img22 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (23==id)
				str += "<img id=img23 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (30==id)
				str += "<img id=img30 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (31==id)
				str += "<img id=img31 src=\"/images/icon_Management.png\" border=\"0\">";
			else if (32==id)
				str += "<img id=img32 src=\"/images/icon_QoS.png\" border=\"0\">";
			else if (33==id)
				str += "<img id=img33 src=\"/images/icon_QoS.png\" border=\"0\">";
			else
			{
				if (opmode==1||opmode==3)
				{
					if (5==id)
						str += "<img id=img5 src=\"/images/icon_QoS.png\" border=\"0\">";
					else if (6==id)
						str += "<img id=img6 src=\"/images/icon_Firewall.png\" border=\"0\">";
				}
			}
			
			str += "</li>";
			str += "<li>";	
            str += name;
			str += "</li></ul>";
			
            str += "</div></A><ul id=submenu_";
            str += id;
            str += " class=dis >";
            str += "</ul>";
        }
        else
        {
            str = str.substring(0, str.length-5);  
            str += "<li style='clear:left;' id=";
            str += id;
            str += "  class=left_link";
            str += ">";
            str += "<img src='/images/submenu.png' algin='absmiddle'>&nbsp;&nbsp;<A href=";
            str += url;
            aid=id+"01";
            str += " id=";
            str += aid;
            str += " onclick=My_Open_A(";
            str += id;
            str += ",";
            str += aid;
            str += ")";			
			str += " hidefocus";
            str += " target=view> ";
            str += name;
            str += "</A>";
            str += "</li>";
            str += "</ul>";     
        }
    }
    return str;
}

function My_T_Over(id)
{
    var x=document.getElementById(id);
	if(1==id || 2==id || 13==id|| 14==id|| 21==id|| 22==id|| 30==id|| 31==id || 32==id ||33==id)
	{	
		if(1==id)
			document.getElementById("img1").src="/images/icon_System_Status.png";
		else if(2==id)
			document.getElementById("img2").src="/images/icon_Opmode.png";
		else if(13==id)
			document.getElementById("img13").src="/images/icon_Wireless.png";
		else if(14==id)
			document.getElementById("img14").src="/images/icon_System_Status.png";
		else if(21==id)
			document.getElementById("img21").src="/images/icon_Management.png";
		else if(22==id)
			document.getElementById("img22").src="/images/icon_Management.png";
		else if(30==id)
			document.getElementById("img30").src="/images/icon_Management.png";
		else if(31==id)
			document.getElementById("img31").src="/images/icon_Management.png";
		else if(32==id)
			document.getElementById("img32").src="/images/icon_QoS.png";
		else if(33==id)
			document.getElementById("img33").src="/images/icon_QoS.png";		
		x.className="left_title_over3";
	}
	else
	{
		if(cur_sub_id && (cur_id == id))
			x.className="left_title_over2";
		else
			x.className="left_title_over1";
	}
}

function My_T_Out(id)
{
    var x=document.getElementById(id);
    if(cur_id==id)
    {
		if(1==id || 2==id || 13==id || 14==id || 21==id ||22 == id || 30==id || 31==id || 32==id || 33==id)
		{	
			if(1==id)
				document.getElementById("img1").src="/images/icon_System_Status_ON.png";
			else if(2==id)
				document.getElementById("img2").src="/images/icon_Opmode_ON.png";
			else if(13==id)
				document.getElementById("img13").src="/images/icon_Wireless_ON.png";	
			else if(14==id)
				document.getElementById("img14").src="/images/icon_System_Status_ON.png";	
			else if(21==id)
				document.getElementById("img21").src="/images/icon_Management_ON.png";	
			else if(22==id)
				document.getElementById("img22").src="/images/icon_Management_ON.png";
			else if(30==id)
				document.getElementById("img30").src="/images/icon_Management_ON.png";	
			else if(31==id)
				document.getElementById("img31").src="/images/icon_Management_ON.png";
			else if(32==id)
				document.getElementById("img32").src="/images/icon_QoS.png";
			else if(33==id)
				document.getElementById("img33").src="/images/icon_QoS.png";
			x.className= "left_title_out3";
		}
		else
		{
			if (0==cur_sub_id && (3==id || 4==id || 5==id || 6==id || 7==id || 8==id || 9==id || 10==id || 15==id || 16==id || 17==id || 18==id || 19==id || 20==id || 21==id ||22==id ||23==id))
				x.className= "left_title1";
			else
				x.className= "left_title_out2";
		}
    }
    else
    {
		if(1==id || 2==id || 13==id || 14==id || 21==id ||22==id || 30==id || 31==id || 32==id || 33==id)
			x.className= "left_title";
		else
			x.className= "left_title1";
    }
}
function setWlanIdx(val){
	var postVar = { topicurl : "setting/setWebWlanIdx"};
	postVar['webWlanIdx'] = "'"+val+"'";
    postVar = JSON.stringify(postVar);
	$.ajax({  
       	type : "post",  
        url : " /cgi-bin/cstecgi.cgi",  
        data : postVar,  
        async : true,  
        success : function(Data){
			//top.frames['view'].location.reload();
		}
   	});	
}
function setMenuMode(val){
	var postVar = { topicurl : "setting/setMenuModeCfg"};
	postVar['menuMode'] = ""+val+"";
    postVar = JSON.stringify(postVar);
	$.ajax({  
       	type : "post",  
        url : " /cgi-bin/cstecgi.cgi",  
        data : postVar,  
        async : true,  
        success : function(Data){
			top.location.reload();
		}
   	});	
}
function My_Open_T(id)
{  
    var subnode;
    if(cur_id != id)
    {
		if(4==id){
			top.frames[0].wifiSelect=0;
			setWlanIdx(0);
		}else if(10==id){
			top.frames[0].wifiSelect=1;
			setWlanIdx(1);
		}
		
		if(30==id){
			setMenuMode(1);//to ap
		}else if(31==id){
			setMenuMode(0);//to ac
		}
		
        if(cur_id != 0)
        {			
			if (cur_sub_id != 0)
            {
				try{subnode=document.getElementById(cur_sub_id + "01");
					subnode.style.color = "";
					subnode.style.fontWeight = "";
				}catch(e){};
            }
			
          	try{document.getElementById("submenu_"+cur_id).className="dis";}catch(e){};
		  	
			if(1==cur_id || 2==cur_id || 13==cur_id || 14==cur_id || 21==cur_id || 22==cur_id || 30==cur_id || 31==cur_id || 32==cur_id  || 33==cur_id)
			{
				try{document.getElementById(cur_id).className="left_title";}catch(e){};
		  	}
			else
			{
				try{document.getElementById(cur_id).className="left_title1";}catch(e){};
			}
        }
        
        try{document.getElementById("submenu_"+id).className="block";}catch(e){};
		
		if(1==id || 2==id || 13==id || 14==id || 21==id || 22==id || 30==id || 31==id || 32==id || 33==id)
		{
			try{document.getElementById("img1").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img2").src="/images/icon_Opmode.png";}catch(e){};
			try{document.getElementById("img3").src="/images/icon_Network.png";}catch(e){};
			try{document.getElementById("img4").src="/images/icon_Wireless.png";}catch(e){};
			if (opmode==1||opmode==3)
			{
				try{document.getElementById("img5").src="/images/icon_QoS.png";}catch(e){};			
				try{document.getElementById("img6").src="/images/icon_Firewall.png";}catch(e){};
			}
			
			if (usb_basic==1)
			{
				try{document.getElementById("img7").src="/images/icon_USB_Storage.png";}catch(e){};
			}
			
			if (portalV2 == 1 && opmode == 1)
			{
				try{document.getElementById("img8").src="/images/icon_Management.png";}catch(e){};
			}
			
			try{document.getElementById("img9").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img10").src="/images/icon_Wireless.png";}catch(e){};
			try{document.getElementById("img13").src="/images/icon_Wireless.png";}catch(e){};
			try{document.getElementById("img14").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img15").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img16").src="/images/icon_QoS.png";}catch(e){};
			try{document.getElementById("img17").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img18").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img19").src="/images/icon_Network.png";}catch(e){};	
			try{document.getElementById("img20").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img21").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img22").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img23").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img30").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img31").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img32").src="/images/icon_QoS.png";}catch(e){};
			try{document.getElementById("img33").src="/images/icon_QoS.png";}catch(e){};
			if(1==id)
			{
				try{document.getElementById("img1").src="/images/icon_System_Status_ON.png";}catch(e){};
			}
			else if(2==id)
			{
				try{document.getElementById("img2").src="/images/icon_Opmode_ON.png";}catch(e){};
			}
			else if(13==id)
			{
				try{document.getElementById("img13").src="/images/icon_Wireless_ON.png";}catch(e){};
			}
			else if(14==id)
			{
				try{document.getElementById("img14").src="/images/icon_System_Status_ON.png";}catch(e){};
			}
			else if(16==id)
			{
				try{document.getElementById("img16").src="/images/icon_QoS_ON.png";}catch(e){};
			}
			else if(18==id)
			{
				try{document.getElementById("img18").src="/images/icon_System_Status_ON.png";}catch(e){};
			}
			else if(21==id)
			{
				try{document.getElementById("img21").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if(22==id)
			{
				try{document.getElementById("img22").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if(30==id)
			{
				try{document.getElementById("img30").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if(31==id)
			{
				try{document.getElementById("img31").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if(32==id)
			{
				try{document.getElementById("img32").src="/images/icon_QoS.png";}catch(e){};
			}
			else if(33==id)
			{
				try{document.getElementById("img33").src="/images/icon_QoS.png";}catch(e){};
			}
			try{document.getElementById(id).className ="left_title_out3";}catch(e){};
		}
		else 
		{
			try{document.getElementById("img1").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img2").src="/images/icon_Opmode.png";}catch(e){};
			try{document.getElementById("img3").src="/images/icon_Network.png";}catch(e){};
			try{document.getElementById("img4").src="/images/icon_Wireless.png";}catch(e){};
			
			if (opmode==1||opmode==3)
			{
				try{document.getElementById("img5").src="/images/icon_QoS.png";}catch(e){};
				try{document.getElementById("img6").src="/images/icon_Firewall.png";}catch(e){};
			}
			if (usb_basic==1)
			{
				try{document.getElementById("img7").src="/images/icon_USB_Storage.png";}catch(e){};
			}
			if (portalV2 == 1 && opmode == 1)
			{
				try{document.getElementById("img8").src="/images/icon_Management.png";}catch(e){};
			}
			
			try{document.getElementById("img9").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img10").src="/images/icon_Wireless.png";}catch(e){};
			try{document.getElementById("img13").src="/images/icon_Wireless.png";}catch(e){};
			try{document.getElementById("img14").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img15").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img16").src="/images/icon_QoS.png";}catch(e){};
			try{document.getElementById("img17").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img18").src="/images/icon_System_Status.png";}catch(e){};
			try{document.getElementById("img19").src="/images/icon_Network.png";}catch(e){};	
			try{document.getElementById("img20").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img21").src="/images/icon_Management.png";}catch(e){};	
			try{document.getElementById("img22").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img23").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img30").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img31").src="/images/icon_Management.png";}catch(e){};
			try{document.getElementById("img31").src="/images/icon_QoS.png";}catch(e){};
			
			if (3==id)
			{
				try{document.getElementById("img3").src="/images/icon_Network_ON.png";}catch(e){};
			}
			else if (4==id)
			{
				try{document.getElementById("img4").src="/images/icon_Wireless_ON.png";}catch(e){};
			}
			else if (7==id)
			{
				try{document.getElementById("img7").src="/images/icon_USB_Storage_ON.png";}catch(e){};
			}
			else if (8==id)
			{
				try{document.getElementById("img8").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if (9==id)
			{
				try{document.getElementById("img9").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if (10==id)
			{
				try{document.getElementById("img10").src="/images/icon_Wireless_ON.png";}catch(e){};
			}
			else if (15==id)
			{
				try{document.getElementById("img15").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if (16==id)
			{
				try{document.getElementById("img16").src="/images/icon_QoS.png";}catch(e){};
			}
			else if (17==id)
			{
				try{document.getElementById("img17").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if (18==id)
			{
				try{document.getElementById("img18").src="/images/icon_System_Status.png";}catch(e){};
			}
			else if (19==id)
			{
				try{document.getElementById("img19").src="/images/icon_Network_ON.png";}catch(e){};
			}
			else if (20==id)
			{
				try{document.getElementById("img20").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else if (21==id)
			{
				try{document.getElementById("img21").src="/images/icon_Management.png";}catch(e){};
			}
			else if (22==id)
			{
				try{document.getElementById("img22").src="/images/icon_Management.png";}catch(e){};
			}
			else if (23==id)
			{
				try{document.getElementById("img23").src="/images/icon_Management_ON.png";}catch(e){};
			}
			else
			{
				if (opmode==1||opmode==3)
				{
					if (5==id) 
						try{document.getElementById("img5").src="/images/icon_QoS_ON.png";}catch(e){};
					if (6==id)
						try{document.getElementById("img6").src="/images/icon_Firewall_ON.png";}catch(e){};
				}
			}
			
			try{document.getElementById(id).className ="left_title_over2";}catch(e){};
		}
		
        cur_id =id;
        cur_sub_id=cur_id+"01";
        try{subnode=document.getElementById(cur_sub_id + "01");
			if (subnode != null)
			{
				subnode.style.color = "#0095c5";
				subnode.style.fontWeight = "700";
			}
			else
			{
				cur_sub_id=0;
			}
		}catch(e){};
    }
	else
	{
		if(1==id || 2==id || 13==id || 14==id || 21==id || 22==id  || 30==id|| 31==id || 32==id || 33==id)
		{
			;
		}
		else
		{
			if(cur_id != 0)
			{
				if(cur_id==id && 0==cur_sub_id)
				{
					try{document.getElementById("submenu_"+id).className="block";}catch(e){};
					if(1==id || 2==id || 13==id || 14==id || 21==id || 22==id ||30==id||31==id || 32==id || 33==id)
					{
						;
					}
					else
					{
						try{document.getElementById(id).className ="left_title_over2";}catch(e){};
					}
					
					cur_id =id;
					cur_sub_id=cur_id+"01";
					try{subnode=document.getElementById(cur_sub_id + "01");
						if (subnode != null)
						{
							subnode.style.color = "#0095c5";
							subnode.style.fontWeight = "700";
						}
						else
						{
							cur_sub_id=0;
						}
					}catch(e){};
				}
				else
				{
					if (cur_sub_id != 0)
					{
						try{subnode=document.getElementById(cur_sub_id + "01");
						subnode.style.color = "";
						subnode.style.fontWeight = "";}catch(e){};
						
						try{document.getElementById("img3").src="/images/icon_Network.png";}catch(e){};
						try{document.getElementById("img4").src="/images/icon_Wireless.png";}catch(e){};

						if (opmode==1||opmode==3)
						{
							try{document.getElementById("img5").src="/images/icon_QoS.png";}catch(e){};
							try{document.getElementById("img6").src="/images/icon_Firewall.png";}catch(e){};
						}
						
						if (usb_basic==1)
						{
							try{document.getElementById("img7").src="/images/icon_USB_Storage.png";}catch(e){};
						}
						
						if (portalV2 == 1 && opmode == 1)
						{
							try{document.getElementById("img8").src="/images/icon_Management.png";}catch(e){};
						}	
						
						try{document.getElementById("img9").src="/images/icon_Management.png";}catch(e){};
						try{document.getElementById("img10").src="/images/icon_Wireless.png";}catch(e){};
						try{document.getElementById("img15").src="/images/icon_Management.png";}catch(e){};	
						try{document.getElementById("img17").src="/images/icon_Management.png";}catch(e){};	
						try{document.getElementById("img19").src="/images/icon_Network.png";}catch(e){};	
						try{document.getElementById("img20").src="/images/icon_Management.png";}catch(e){};	
						try{document.getElementById("img21").src="/images/icon_Management.png";}catch(e){};	
						try{document.getElementById("img22").src="/images/icon_Management.png";}catch(e){};
						try{document.getElementById("img23").src="/images/icon_Management.png";}catch(e){};
					}
					
					try{document.getElementById("submenu_"+cur_id).className="dis";}catch(e){};
					try{document.getElementById(cur_id).className="left_title1";}catch(e){};
					cur_sub_id=0;
				}
			}
		}
	}
	var x=document.getElementById(id);
	// x.parentNode.href=addTimestamp(x.parentNode.href);
}

function addTimestamp(url){
	return url;
	var _url = url;
	if(_url.indexOf('?') == -1){
		_url += '?timestamp=' + (new Date()).getTime();
	}else{
		if(_url.indexOf('timestamp') == -1)
			_url += "&timestamp=" + (new Date()).getTime();
		else
			_url=_url.replace(/timestamp=.*/ig,"timestamp=" + (new Date()).getTime());
	}

	return _url;
}

function My_Open_A(id,aid)
{  
    var x=document.getElementById(aid);
    if(cur_sub_id!=0)
    {
        var old=document.getElementById(cur_sub_id + "01");
        old.style.color = "";
        old.style.fontWeight = "";
		old.style.textDecoration = "none";
    }
	if(id == '80301') counts=0;
    x.style.color = "#0095c5";
    x.style.fontWeight = "700";
	x.style.textDecoration = "underline";
	// x.href=addTimestamp(x.href);
//	x.className = "left_link_current";
    cur_sub_id = id;
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

var stopEvent = function(e)
{
    e = e || window.event;
    if(e.preventDefault) 
	{
      	e.preventDefault();
      	e.stopPropagation();
    }
	else
	{
      	e.returnValue = false;
      	e.cancelBubble = true;
    }
}
  
function resultFun(data)
{
	if(data.length>0)
	{
		top.location.href='/login.asp';
	}
}

addEventHandler(window, "load", function() 
{	
	try{var logoutNode=document.getElementById("2013");
		addEventHandler(logoutNode, "click", function(e){
			if(confirm(JS_logout)){
				top.location.href='/formLogout.htm';
			}else{
				parent.frames["view"].window.location.reload();
			}
			e  = window.event || e; 
			var srcElement  =   e.srcElement || e.target; 
			if (window.event) {
				e.cancelBubble = true;
			}
			else {
				e.preventDefault();
				e.stopPropagation();
			}
			stopEvent(e);
		});
	}catch(e){} 
	} 
);

addEventHandler(window, "load", function() 
{	
	try{var logoutNode=document.getElementById("913");
		addEventHandler(logoutNode, "click", function(e){
			if(confirm(JS_logout)){
				top.location.href='/login/logout';
			}else{
				parent.frames["view"].window.location.reload();
			}
			e  = window.event || e; 
			var srcElement  =   e.srcElement || e.target; 
			if (window.event) {
				e.cancelBubble = true;
			}
			else {
				e.preventDefault();
				e.stopPropagation();
			}
			stopEvent(e);
		});
	}catch(e){} 
	} 
);

addEventHandler(window, "load", function() 
{	
	try{var manergerNode=document.getElementById("20");
		addEventHandler(manergerNode, "click", function(e){
			parent.frames["view"].window.location.reload();
			e  = window.event || e; 
			var srcElement  =   e.srcElement || e.target; 
			if (window.event) {
				e.cancelBubble = true;
			}
			else {
				e.preventDefault();
				e.stopPropagation();
			}
			stopEvent(e);
		});
	}catch(e){} 
	} 
);

addEventHandler(window, "load", function() 
{	
	try{var restoreNode=document.getElementById("2001");
		addEventHandler(restoreNode, "click", function(e){
			if(confirm(JS_restore)){
				var postVar = { topicurl : "setting/AcRestore"};
				postVar = JSON.stringify(postVar);
				$.ajax({  
					type : "post",  
					url : " /cgi-bin/cstecgi.cgi",  
					data : postVar,  
					async : false,  
					success : function(Data){
						var submitResponse = JSON.parse(Data);
						if(submitResponse['reserv']==1){
							top.location.reload();
						}else{
							alert(JS_restoreFaile);
						}
					}
				});
			}else{
				parent.frames["view"].window.location.reload();
			}
			e  = window.event || e; 
			var srcElement  =   e.srcElement || e.target; 
			if (window.event) {
				e.cancelBubble = true;
			}
			else {
				e.preventDefault();
				e.stopPropagation();
			}
			stopEvent(e);
		});
	}catch(e){} 
	} 
);

addEventHandler(window, "load", function() 
{	
	try{var uninstallNode=document.getElementById("2002");
		addEventHandler(uninstallNode, "click", function(e){
			if(confirm(JS_uninstall)){
				var postVar = { topicurl : "setting/uninstallAcPlugin"};
				postVar = JSON.stringify(postVar);
				$.ajax({  
					type : "post",  
					url : " /cgi-bin/cstecgi.cgi",  
					data : postVar,  
					async : false,  
					success : function(Data){
						var submitResponse = JSON.parse(Data);
						if(submitResponse['reserv']==1){
							top.location.href='/formLogout.htm';
						}else{
							alert(JS_uninstallFaile);
						}
					}
				});
			}else{
				parent.frames["view"].window.location.reload();
			}
			e  = window.event || e; 
			var srcElement  =   e.srcElement || e.target; 
			if (window.event) {
				e.cancelBubble = true;
			}
			else {
				e.preventDefault();
				e.stopPropagation();
			}
			stopEvent(e);
		});
	}catch(e){} 
	} 
);

	
function saveResultFun(data)
{
	if(data==1){
		alert(JS_saveCfgSuccess);
	}else{
		alert(JS_saveCfgFaile);
	}
}

addEventHandler(window, "load", function() 
{	
	try{var logoutNode=document.getElementById("912");
		addEventHandler(logoutNode, "click", function(e){
		if(1){
//			Ajax.getInstance('/goform/saveCurrConfig','',0,saveResultFun);
//			Ajax.get();
			var postVar = { topicurl : "setting/saveConfig"};
			postVar = JSON.stringify(postVar);
			$.ajax({  
				type : "post",  
				url : " /cgi-bin/cstecgi.cgi",  
				data : postVar,  
				async : false,  
				success : function(Data){
					var submitResponse = JSON.parse(Data);
					if(submitResponse['reserv']==1){
						alert(JS_saveCfgSuccess);
					}else{
						alert(JS_saveCfgFaile);
					}
				}
			});
		}else{
			parent.frames["view"].window.location.reload();
		}
		e  = window.event || e; 
		var srcElement  =   e.srcElement || e.target; 
		if (window.event) {
			e.cancelBubble = true;
		}
		else {
			e.preventDefault();
			e.stopPropagation();
		}
		stopEvent(e);
	});
	}catch(e){} 
	} 
);

addEventHandler(document, "mousemove", function(){	try{top.frames['title'].pageTimeoutDeal();}catch(e){}});
