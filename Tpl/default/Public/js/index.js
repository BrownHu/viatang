var t = n = 0, count;
function showAuto(){n = n >=(count - 1) ? 0 : ++n;$("#banner li").eq(n).trigger('click');}
function showTopbnr(id){ $('#'+id).fadeIn(300);} 	
$(document).ready(function(){
	$(".site-lst").jCarouselLite({btnNext: ".next", btnPrev: ".prev",visible:5,auto:1000, speed:1000});	
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
	  if(3 == item_id ){ 
		  $("#idx_scrolbox").css({"margin-left":"0px"});
		  renew = false;
	  }
	  item_id = item_id % 3 + 1;
	  if(!renew && (item_id == 1)){
		  item_id = 0;
		  renew = true;
	  }
	  mvFun();
  	}	
  	$.get('/ihelp/r.html',function(data){if(data != ''){var result = new Array(); result = data.split('|');$('#recent_buy').append(result[0]);$('#buyUserCount').empty().append(result[1]);}});  
  	$.get('/ihelp/ad/t/7.html',function(data){ if(data!=''){ $('#didxAdBanner').append(data);}});	
  	$("#idx_scrolbox").hover(function(){if(false == hover){clearInterval(tid)}; hover = true;},function () {if(true == hover) tid = setInterval(function() { mv_timeout(); }, 3000);hover = false;});$(".idx_ord_item").hover(function () {if(false == hover) clearInterval(tid);hover = true;},function () {if(true == hover) tid = setInterval(function() { mv_timeout(); }, 3000);hover = false;});
})

function setab(obj,show,hid){
	$('#i_tb li').removeClass('i_tab_hover');
	$(obj).addClass('i_tab_hover');
	$('#'+show).show();
	$('#'+hid).hide();
}
var gcount = 1;
var gtimerID = setInterval("slider()",5000);
function slider() {
	if( gcount == 1){
		$('#bn00').removeClass().addClass('bn00-1').addClass('nobd');
    	$('#bn01').removeClass().addClass('bn01-1').addClass('middle').addClass('nobd');
		
		$('#bn02').show('slow');
		$('#bn03').hide('slow');
                  $('#bn04').hide('slow');
		
		$('#slider_img_pos li').removeClass("gli_on").addClass('gli_no').addClass('left');
		$('#bt_01').removeClass().addClass('gli_on').addClass('left');
		gcount = 2;
	}else if(gcount == 2){
		$('#bn00').removeClass().addClass('bn00-2').addClass('nobd');
    	$('#bn01').removeClass().addClass('bn01-2').addClass('middle').addClass('nobd');
		
    	         $('#bn02').hide('slow');
		$('#bn03').show('slow');
                  $('#bn04').hide('slow');

		
		$('#slider_img_pos li').removeClass("gli_on").addClass('gli_no').addClass('left');
		$('#bt_02').removeClass().addClass('gli_on').addClass('left');
		 gcount = 3;
	}else if(gcount == 3){
		$('#bn00').removeClass().addClass('bn00-3').addClass('nobd');
    	$('#bn01').removeClass().addClass('bn01-3').addClass('middle').addClass('nobd');

    	         $('#bn02').hide('slow');
		$('#bn03').hide('slow');
                  $('#bn04').hide('slow');

		
		$('#slider_img_pos li').removeClass("gli_on").addClass('gli_no').addClass('left');
		$('#bt_03').removeClass().addClass('gli_on').addClass('left');
		 gcount = 1;
	}
}

$('#slider_img_pos').mouseover(function(){
	clearInterval(gtimerID);
}).mouseout(function(){
   gtimerID = setInterval("slider()",5000);
});
	