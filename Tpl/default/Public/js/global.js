function fast_buy(u,e){
  if(e){	
  	var pageurl = $.base64Encode($.trim(u)).replace(new RegExp("/","gm"),"^").replace(new RegExp("==","gm"),"@");
  }else{
	 var pageurl = $.trim(u);    	
  }
  lightboxframe('快速代购','/Item/index/url/'+pageurl+'.shtml',540,580,true);
  //window.location.href= '/Item/index/url/'+pageurl+'.html';
}

//u ：登录成功后，回调的URL地址,W,H :回调时要改变的窗口大小，t:回调成功后 是否立即关即窗口。
function login_min(u,w,h,t){
  var pageurl = $.trim(u);
  pageurl = '/Public/login_min/u/'+pageurl+'/w/'+w+'/h/'+h+'/t/'+t+'.shtml';
  lightboxframe('系统登录',pageurl,530,200,true);	
}

function on_hover(id,cls){ 
	$('#'+id).addClass(cls);
}

function on_out(id,cls){ 
  $('#'+id).removeClass(cls);
}

function showMenu(p,id){
	 var offset = $('#'+p).offset();	  
	 $('#'+id).css('left',offset.left);  
	 $('#'+p).css('z-index',2000);	
	 $('#'+p).css('position','relative');
	 $('#'+id).css('top',1);
	 $('#'+id).show();
	 $('#'+id).css('color','#000');
}

function hideMenu(id){ 
  $('#'+id).hide();
  $('#'+id).css('color','#dddddd');
}

$(function(){
	$("#CrawlUrl").focus(function(){
		if($("#CrawlUrl").val() == 'http://粘贴您想代购的中国购物网站的商品网址（URL）'){
			$("#CrawlUrl").val('');
			$('#CrawlUrl').css('color','#000');
		}
	}).blur(function(){		
		if($("#CrawlUrl").val().length <= 0){
			$("#CrawlUrl").val('http://粘贴您想代购的中国购物网站的商品网址（URL）');
			$('#CrawlUrl').css('color','#4D4E51');
		}else{ 
			$('#CrawlUrl').css('color','#000');
		}
	});
	
	$("#sel_url").focus(function(){
		if($("#sel_url").val() == 'http://粘贴您想代购的中国购物网站的商品网址（URL）'){
			$("#sel_url").val('');
			$('#sel_url').css('color','#000');
		}
	}).blur(function(){		
		if($("#sel_url").val().length <= 0){
			$("#sel_url").val('http://粘贴您想代购的中国购物网站的商品网址（URL）');
			$('#sel_url').css('color','#4D4E51');
		}else{ 
			$('#sel_url').css('color','#000');
		}
	});
	
		$("#keyword").focus(function(){
		if($("#keyword").val() == '请输入要搜索的商品名称'){
			$("#keyword").val('');
			$('#keyword').css('color','#000');
		}
	}).blur(function(){		
		if($("#keyword").val().length <= 0){
			$("#keyword").val('请输入要搜索的商品名称');
			$('#keyword').css('color','#4D4E51');
		}else{ 
			$('#keyword').css('color','#000');
		}
	});
			
	var f=function(){
		var d=$.trim($("#CrawlUrl").val());
		if(d.length<=0||d=="http://粘贴您想代购的中国购物网站的商品网址（URL）"){
			return false;
		}
		
		var j=new RegExp("http(s)?://([\\w-]+\\.)+[\\w-]+(/[\\w- ./?%&=]*)?");
		if(d.indexOf("http://")<0 && d.indexOf("https://")<0){
			d="http://"+d;
		}
		if(!j.test(d)){		
			return false;
		}

		$("#itemUrl").val($("#CrawlUrl").val());
		$("#CrawlUrl").val('http://粘贴您想代购的中国购物网站的商品网址（URL）');
		$('#CrawlUrl').css('color','#4D4E51');
		
		//var pageurl = $.base64Encode(d).replace(new RegExp("/","gm"),"^").replace(new RegExp("==","gm"),"@");
		//$.get('/Public/isLogin.html', function(result) { 
		//	if(parseInt(result) == 1){
				$('#daigouform').submit();
		//	}else{				
		//		window.location.href='/Public/login.html';
				//	login_min(',Item,index,url,"'+pageurl+'"',560,420,0);
		//	}		
		//});		
		return false;
	};
	
	$("#CrawlUrl").keydown(function(d){if(d.keyCode==13){f();return false;}});
	$("#CrawlBtn").click(f);
	
	$('.nav_menu_li').mouseover(function(){ $(this).addClass('nav_menu_li_on');});
	$('.nav_menu_li').mouseout(function(){ $(this).removeClass('nav_menu_li_on');});
	$('.link_li').mouseover(function(){ $('#'+this.id).addClass('link_hover');});
	$('.link_li').mouseout(function(){ $('#'+this.id).removeClass('link_hover');});
	$('.link_li_last').mouseover(function(){ $('#'+this.id).addClass('link_hover');});
	$('.link_li_last').mouseout(function(){ $('#'+this.id).removeClass('link_hover');});	
});


function changeNews(showid,hiddeid,changeid,url,show){
	var strs= new Array();
	strs=hiddeid.split(",");
	for (i=0;i<strs.length ;i++ ){
		$('#lst_'+strs[i]).hide();
		$('#tlt_'+strs[i]).removeClass('gg_title_on');
	}
	
	$('#lst_'+showid).show();
	$('#tlt_'+showid).addClass('gg_title_on');
	$('#'+changeid).attr('href',url);
	if(show){ $('#'+changeid).show();}else{ $('#'+changeid).hide();}
}
function showCategory(){
	  var tag = parseInt($('#sort').attr('tag'));
	  var nav = $('#pop_allsort').size();
	  if(!nav && (tag == 0)){
		  $('#sort').attr('tag','1');
		  $.get('/Ihelp/cat.shtml',function(data){ 
		  	  $('#sort').append(data);
			  $('.gcat').mouseover(function(){ $('.gcat').removeClass('sortlihover'); $('#'+this.id).addClass('sortlihover');	$('.allsort').css({'display':''});$('#sub_'+this.id).css({'display':'block'});});
		  });
	  }
      var offset = $('#sort').offset();	
	  $('#pop_allsort').css({'left':offset.left});
}

function setAimg(pic,width,height){
	var src =  $.base64Decode(pic);
	var img = "<img src='/Public/Images/grey.gif' lazy='y' original='"+src+"' ";
	if(width!=0){
		img+=" width="+width;
	}
	if(height!=0){
		img+=" height="+height;
	}
	img = img+" />";	
	document.write(img);	
}

function nofind(newImg){  
	var img=event.srcElement;  
	img.src= newImg; 
	img.onerror=null;
} 

function loadImg(id,maxwidth,maxheight){ 
	var w = $("#"+id).attr("width");	
	var h = $("#"+id).attr("height");	
	if(w > maxwidth){
		h = h*(maxwidth/w); 
		$("#"+id).attr("height",h);
		$("#"+id).attr("width",maxwidth);
	}else{
	    if(h > maxheight){
		  w = w * (maxheight/h); 
		  $("#"+id).attr("height",h);
		  $("#"+id).attr("width",maxwidth); 
		} 
	}
};
function chgtoptab(show){ 
	$('#toptab li').each(function(){
		$(this).remove('topsearch_on').addClass('topsearch_normal'); 
	});
	$('#tab'+show).removeClass('topsearch_normal').addClass('topsearch_on'); 
	$('.top-input-i').each(function(){
		$(this).hide();
	});
	$('#top'+show).show();
}
function dosearch(){ if($.trim($('#keyword').val()) != '') { $('#searchform').submit();}}