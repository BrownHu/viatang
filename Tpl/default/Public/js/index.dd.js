var t = n = 0, count;
$(document).ready(function(){	
	count=$("#banner_list a").length;
	$("#banner_list a:not(:first-child)").hide();
	$("#banner_info").html($("#banner_list a:first-child").find("img").attr('alt'));
	$("#banner_info").click(function(){window.open($("#banner_list a:first-child").attr('href'), "_blank")});
	$("#banner li").click(function() {
		var i = $(this).text() - 1;//获取Li元素内的值，即1，2，3，4
		n = i;
		if (i >= count) return;
		$("#banner_info").html($("#banner_list a").eq(i).find("img").attr('alt'));
		$("#banner_info").unbind().click(function(){window.open($("#banner_list a").eq(i).attr('href'), "_blank")})
		$("#banner_list a").filter(":visible").fadeOut(500).parent().children().eq(i).fadeIn(1000);
		document.getElementById("banner").style.background="";
		$(this).toggleClass("on");
		$(this).siblings().removeAttr("class");
	});
	t = setInterval("showAuto()", 4000);
	$("#banner").hover(function(){clearInterval(t)}, function(){t = setInterval("showAuto()", 4000);});
	$(".site-lst").jCarouselLite({btnNext: ".next", btnPrev: ".prev",visible:5,speed:500});
})

function showAuto()
{
	n = n >=(count - 1) ? 0 : ++n;
	$("#banner li").eq(n).trigger('click');
}
function showTopbnr(id){ $('#'+id).fadeIn(300);} 

$(document).ready(function(){ 
	$.get('/Ihelp/product.html',function(data){ 
			if(data !=''){
				var result = new Array(); 
				result=data.split('|'); 
				$('#idx_scrolbox').empty().append(result[0]); 
				$('#success_count').empty().append(result[1]);
			}
	}); 
	var renew= true;
	var item_id=0;
	var hover=false;
	var tid=setInterval(function(){mv_timeout();},4000);
	var mvFun = function(){ 
		$("#idx_scrolbox").animate({marginLeft:0-item_id * 648+"px"},{duration:800, queue:false}); 
		var count = parseInt($('#buyUserCount').html()); 
		$('#buyUserCount').empty().append(count+1);
	}
  var mv_timeout = function(){
	  if(4 == item_id ){ 
		  $("#idx_scrolbox").css({"margin-left":"0px"});
		  renew = false;
	  }
	  item_id = item_id % 4 + 1;
	  if(!renew && (item_id == 1)){
		  item_id = 0;
		  renew = true;
	  }
	  mvFun();
  }	
  $.get('/ihelp/r.html',function(data){if(data != ''){var result = new Array(); result = data.split('|');$('#recent_buy').append(result[0]);$('#buyUserCount').empty().append(result[1]);}});  
  $.get('/ihelp/ad/t/7.html',function(data){ if(data!=''){ $('#didxAdBanner').append(data);}});	
  $("#idx_scrolbox").hover(function(){if(false == hover){clearInterval(tid)}; hover = true;},function () {if(true == hover) tid = setInterval(function() { mv_timeout(); }, 3000);hover = false;});$(".idx_ord_item").hover(function () {if(false == hover) clearInterval(tid);hover = true;},function () {if(true == hover) tid = setInterval(function() { mv_timeout(); }, 3000);hover = false;});
});
