function resetDom(cls){	$(cls).each(function(index,eledom){ $('#'+eledom.id).removeClass('detail_0');});}
function ShowLayers1(n,m){ $('#ontent' + n).show();$('#li'+n).removeClass('pg_unselect');$('#li'+n).addClass('pg_select');	$('#ontent' + m).hide();$('#li'+m).removeClass('pg_select');$('#li'+m).addClass('pg_unselect');
if(n == 2){var count = $('#pllst li').size(); if(count == 0 ){ $('#getpling').show();$('#pjtitle').hide();$.get('/Mall/taobao_comment/u/'+plurl,function(data){ $('#getpling').hide();if(data != ''){ $('#pjtitle').show();$('#pllst').empty().append(data);$('#morereview').show();}else{  $('#pjtitle').show();$('#pllst').append('<li>暂无评论</li>');} });}}}
function showTopbnr(id){ $('#'+id).fadeIn(300);} $.get('/Mall/loadTopBanner/t/2.shtml',function(data){ if(data!=''){ $('#m0').append(data); } });$.get('/Mall/loadChlBanner/t/5.shtml',function(data){ if(data!=''){ $('#chlbanner').append(data).css("padding-bottom","14px"); } });
function changeimg(pic,width,height){var src =  $.base64Decode(pic);var img = "<img src='"+src+"' ";if(width!=0){img+=" width="+width;}	if(height!=0){img+=" height="+height;}img = img+" />";$('#detail_img').empty().append(img);}
function setcurrnet(id,cls){ $('.'+cls).removeClass('detail_current');$('#'+id).addClass('detail_current');}
function setSel(id,type,val){
	var is_disable =  parseInt($('#'+id).attr('tag'));
	if(is_disable == 1) return false;
	if(type == 1){ 
		$('.size_t').removeClass('detail_sel');
		$('.size_t').removeClass('detail_0');
		$('.size_t').addClass('detail_nrm');		
		
		var save_size = $('#sel_size').val();
		if(decodeURIComponent(val) == save_size){
			$('#sel_size').val('');
			resetDom('.color_t');
			return false;
		}
		$('#sel_size').val(decodeURIComponent(val));
		//取出选中颜色的尺码表
		var selcolor = $('#sel_color').val();
		if(selcolor != ''){
			var sizeList = new Array();
			var k= 0;
			for(i=0;i<propers.length;i++){
				if( (decodeURIComponent(propers[i].color) == selcolor) && (propers[i].quantity > 0)){
					sizeList[k] = decodeURIComponent(propers[i].size);
					k++;				
				}
			}
			
			//过滤选中颜色不支持的尺码
			if(sizeList.length>0){
				$('.size_t').each(function(index,eledom){
					var size_id = eledom.id;
					var size_cp = $('#'+size_id).html();
					var found = false;
					for(i=0;i<sizeList.length;i++){
						if( sizeList[i] == size_cp){
							found = true;
							break;
						}
					}
					if(!found){
						$('#'+size_id).addClass('detail_0');
						$('#'+size_id).attr('tag','1');
					}
				});
			}
		}
		
		//-----------------------------------------------------------------
		//对选中尺码对应的颜色进行处理
		//加载选中尺码的颜色表
		var colorList = new Array();
		var n = 0;
		for(i=0;i<propers.length;i++){
			if( (propers[i].size == val) && (propers[i].quantity > 0) ){
				colorList[n] = decodeURIComponent(propers[i].color);
				n++;
			}
		}
		
		//过滤选中尺码不支持的颜色
		$('.color_t').each(function(index,eledom){
			var color_id = eledom.id;
			var color_caption = $('#'+color_id).html();
			var found = false;			
			for(i=0;i<colorList.length;i++){
				if(colorList[i] == color_caption){
					found = true;
					break;
				}
			}
			if(!found){
				$('#'+color_id).addClass('detail_0');
				$('#'+color_id).attr('tag','1');
			}else{
				$('#'+color_id).removeClass('detail_0');
				$('#'+color_id).attr('tag','0');
			}
		});
	}
	
	if(type == 2){ 
		$('.color_t').removeClass('detail_sel');
		$('.color_t').removeClass('detail_0');$('.color_t').addClass('detail_nrm');
		
		var save_color = $('#sel_color').val();
		if(decodeURIComponent(val) == save_color){
			$('#sel_color').val('');
			resetDom('.size_t');
			return false;
		}
		$('#sel_color').val(decodeURIComponent(val));
		
		var selsize = $('#sel_size').val();
		//-----------------------------------------------------------------
		// 处理已选中尺码的颜色处理
		//对选中尺码对应的颜色进行处理
		//加载选中尺码的颜色表
		if(selsize != ''){
			var colorList = new Array();
			var n = 0;
			for(i=0;i<propers.length;i++){
				if( (decodeURIComponent(propers[i].size) == selsize) && (propers[i].quantity > 0) ){
					colorList[n] = decodeURIComponent(propers[i].color);
					n++;
				}
			}
		
			//过滤选中尺码不支持的颜色
			if(colorList.length >0){
				$('.color_t').each(function(index,eledom){
					var color_id = eledom.id;
					var color_caption = $('#'+color_id).html();
					var found = false;			
					for(i=0;i<colorList.length;i++){
						if(colorList[i] == color_caption){
							found = true;
							break;
						}
					}
					if(!found){
						$('#'+color_id).addClass('detail_0');
						$('#'+color_id).attr('tag','1');
					}
				});
			}
		}
		//----------------------------------------------------------------------------
		//取出选中颜色对应的尺码表
		var sizeList = new Array();
		var n = 0;
		for(i=0;i<propers.length;i++){
			if( (propers[i].color == val) && (propers[i].quantity > 0) ){
				sizeList[n] = decodeURIComponent(propers[i].size);
				n++;
			}
		}
		
		//过滤掉尺码表中，该颜色不支持的尺码
		$('.size_t').each(function(index,eledom){
			var size_id = eledom.id;
			var size_caption = $('#'+size_id).html();
			var found = false;			
			for(i=0;i<sizeList.length;i++){
				if(sizeList[i] == size_caption){
					found = true;
					break;
				}
			}
			if(!found){
				$('#'+size_id).addClass('detail_0');
				$('#'+size_id).attr('tag','1');
			}else{
				$('#'+size_id).removeClass('detail_0');
				$('#'+size_id).attr('tag','0');
			}
		});

	}
	
	//更新颜数量和价格
	$('#'+id).addClass('detail_sel');
	var size = $('#sel_size').val();
	var color = $('#sel_color').val();
	if( (size != '') || (color != '')){		
		for(i=0;i<propers.length; i++){
			if(color != ''){
				if(size != ''){
				  if( ( decodeURIComponent(propers[i].color) == color) && (decodeURIComponent(propers[i].size) == size) ){
					  $('#d_price').empty().html(propers[i].price);
					  $('#detail_count').empty().html(propers[i].quantity);
					  break;
				  }
				}else{
					 if( decodeURIComponent(propers[i].color) == color ){
					  $('#d_price').empty().html(propers[i].price);
					  $('#detail_count').empty().html(propers[i].quantity);
					  break;
				  	 }
				}
			}else if(size !=''){
				if( decodeURIComponent(propers[i].size) == size ){
					  $('#d_price').empty().html(propers[i].price);
					  $('#detail_count').empty().html(propers[i].quantity);
					  break;
				}
			}
		}
	}
}