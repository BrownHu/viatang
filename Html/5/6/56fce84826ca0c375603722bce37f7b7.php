<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" Content="zh-CN" /> 
<link rel="shortcut icon" href="/Ulowi/Tpl/default/Public/images/favicon.ico" alt="唯唐代购">
<title>如何跟踪运单</title>
<meta name="keywords" content="海外华人代购，代购中国，淘宝代购，中国商品代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购" />
<meta name="description" content="海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起" />
<link rel="stylesheet" type="text/css" href="/s/?g=g.css">
<script type="text/javascript" src="/s/?g=g.js"></script>
<meta property="qc:admins" content="57510775176611416751446375" />
</head>
<body>
<div class="block_top">
  <div style="width:988px; height:30px; overflow:hidden;" class="nobd middle">
    <div class="left nobd welcom block_t" style="text-align:left;">
      您好！游客&nbsp;请&nbsp;&nbsp;&nbsp<span style="display:inline-block;"><a href="/Public/login/go/News,detail.html" >登录</a></span>&nbsp;&nbsp;&nbsp; <span style="display:inline-block;"><a href="/wechat/login.html" target="_blank" >微信登录</a></span>&nbsp&nbsp&nbsp;<span style="display:inline-block;"><a href="/Register/index.html" >免费注册</a></span>&nbsp;
          </div>
    <div id="top_nav" class="top_nav block_t">
      <ul style="float:right;">     
        <li  id="MyAccount" onMouseOver="showMenu('MyAccount','aMyMenu');" onMouseOut="hideMenu('aMyMenu');"><a href="/My/index.html" style="color:#bbbbbb" ><span class="arrow_down_1">我的唯唐</span></a></li>
        <li class="top_nav_fg"><a href="/Cart/index.shtml">代购购物车(<span class="my_red">0</span>)</a></li>
        <li class="top_nav_fg"><a href="/review/index.html">用户评论</a></li>
        <li class="top_nav_fg"><a href="/help/index.html">帮助中心</a></li>
        <li class="top_nav_fg"><a href="http://tb.53kf.com/webCompany.php?arg=10067534&style=1" target="_blank">在线客服</a></li>
        <li class="top_nav_fg" id="Tools" onMouseOver="showMenu('Tools','ToolsMenu')" onMouseOut="hideMenu('ToolsMenu');"><span class="arrow_down_1" style="color:#bbbbbb;">常用工具</span></li>
      </ul>
    </div>
    <div id="aMyMenu" class="nav_menu">
      <ul onMouseOver="showMenu('MyAccount','aMyMenu')" onMouseOut="hideMenu('aMyMenu');" class="nav_menu_ul">
        <li id="nav_cp" class="nav_menu_li" ><a href="/My/index.html">我的唯唐</a></li>
        <li id="nav_ord" class="nav_menu_li" ><a href="/My/order.html">我的订单</a></li>
        <li id="nav_shp" class="nav_menu_li" ><a href="/My/parcel.html">我的运单</a></li>
        <li id="nav_pay" class="nav_menu_li" ><a href="/Pay/paypal.html">帐户充值</a></li>
        <li id="nav_fav" class="nav_menu_li" ><a href="/My/favorite.html">我的收藏</a></li>
      </ul>
    </div>
    <div id="ToolsMenu" class="nav_menu">
      <ul onMouseOver="showMenu('Tools','ToolsMenu')" onMouseOut="hideMenu('ToolsMenu');" class="nav_menu_ul">
        <li id="nav_trace" class="nav_menu_li"><a href="/Trace/index.html" target="_blank">包裹跟踪</a></li>
        <li id="nav_guess" class="nav_menu_li"><a href="/Tools/estimate.html" target="_blank">费用估算</a></li>
        <li id="nav_rule" class="nav_menu_li"><a href="/Tools/measure.html" target="_blank">尺码换算</a></li>
        <li id="nav_forex" class="nav_menu_li"><a href="http://qq.ip138.com/hl.asp" target="_blank">汇率计算</a></li>
      </ul>
    </div>
  </div>
</div>
<script type="text/javascript" src="/Public/Js/jQuery/Plug-in/jquery.form.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.artDialog.js?skin=default"></script> 
<script type="text/javascript" src="/Public/Js/jquery.autocomplete.min.js"></script>
<link rel="stylesheet" type="text/css" href="/Public/Css/jquery.autocomplete.css">
<div style="width:auto;height:auto;overflow:hidden;background-color:#fff">
<div id="head_t" style="width:988px;height:62px; margin-top:8px;" class="middle">
  <div id="logo" style="overflow:hidden; margin-top:0px;" class="left nobd"><a href="/"><img src="/Ulowi/Tpl/default/Public/images/logo.png" class="nobd" alt="唯唐代购"></a></div>
  <div style="width:556px; height:65px;overflow:hidden;" class="right nobd">
	<div id="toptab" style="width:556px; height:20px; overflow:hidden;margin-top:2px;">
    	<ul style="display:block;width:202px;line-height:20px; height:20px; padding-left:14px; margin:0px;font-size:14px;" class="left">
            <li id="tabbuy" class="left topsearch_on" onClick="chgtoptab('buy');" tag="1">代购</li>			
            <li id="tabsearch" class="left topsearch_normal"  onClick="chgtoptab('search');">搜索</li>
            <li id="tabselfbuy" class="left topsearch_normal"  onClick="chgtoptab('selfbuy');">国际转运</li>
        </ul>
    </div>
    <div style="width:556px; height:45px;overflow:hidden; ">
      <div id="topbuy" style="width:546px; overflow:hidden;" class="nobd right top-input-i">
	    <form action="/Item/step2.html" method="get" id="daigouform" target="_blank">
	    	<input type="hidden" name="itemUrl" id="itemUrl" value="" />
          	<div style="width:451px; margin-left:2px;overflow:hidden; margin-top:0px;" class="left nobd">
            	<input id="CrawlUrl" name="CrawlUrl" type="text" value="http://粘贴您想代购的中国购物网站的商品网址（URL）" class="yjtd_input fnt_lgt_gry" >
        	</div>
        	<div style="margin-top:0px;overflow:hidden;" class="nobd left">
				<img id="CrawlBtn" src="/img/btn-dg.gif" alt="我要代购" class="nobd" style="cursor:pointer;" > 
			</div>
		</form>
      </div>
      <div id="topsearch" style="width:546px; overflow:hidden; display:none;" class="nobd right top-input-i">
        <div style="width:451px; margin-left:2px;overflow:hidden; margin-top:0px;" class="left nobd">
          <form action="/search/index" method="post"  id="searchform">
          	  <input name="p" value="1" type="hidden">
	          <input id="keyword" name="q" type="text" value="请输入要搜索的商品名称" style="color:#4D4E51;" class="yjtd_input fnt_lgt_gry" >
          </form>
        </div>
        <div style="margin-top:0px;overflow:hidden;" class="nobd left"><img id="btnsearch" src="/img/btn-search.gif" alt="代购搜索" class="nobd" style="cursor:pointer;" onClick="dosearch();" > </div>
      </div>
      <div id="topselfbuy" style="width:546px; overflow:hidden; display:none;" class="nobd right top-input-i">
	    <form action="/Selfpurchase/step1" method="post" id="selfbuyform">
	    	<input type="hidden" name="url" id="selfUrl" value="" />
          	<div style="width:451px; margin-left:2px;overflow:hidden; margin-top:0px;" class="left nobd">
            	<input id="sel_url" name="itemurl" type="text" value="http://粘贴您想代购的中国购物网站的商品网址（URL）" class="yjtd_input fnt_lgt_gry" >
        	</div>
        	<div style="margin-top:0px;overflow:hidden;" class="nobd left">
				<img  src="/img/btn-dj.gif" alt="我要代寄" class="nobd" style="cursor:pointer;" onclick="doSelfBuy();" > 
			</div>
		</form>
      </div>
    </div>
  </div>
</div>
</div>
<div style="display:none;">
  <form action="/Item/loadItem" method="get" id="frm_daigou_result">
	<input type="hidden" name="_k" value="" id="daigou_result_page">
  </form>	
</div>
<script>

function doSelfBuy(){  
	var pageurl = $("#sel_url").val();  
	if (pageurl == '') { 
		alert('   请输入您自助购买的商品网址！');
	}else{
		if(pageurl == 'http://粘贴您想代购的中国购物网站的商品网址（URL）'){
			alert('请输入您自助购买的商品网址!');
			return false;
		}else{
			pageurl = $.base64Encode(pageurl).replace('/','^').replace('==','@'); 
			lightboxframe('国际转运','/Selfpurchase/step1/url/'+pageurl,540,425,true);
			//$('#selfUrl').val($('#sel_url').val(pageurl));
			//$('#selfbuyform').submit();
		}
	}
} 

function complete(result){		
    if (result.status == '1'){
		//window.location.href = '/Item/loadItem';		
		$('#daigou_result_page').val(result.info);
		$('#frm_daigou_result').submit();
		closeDialog();			
	}else{
		art.dialog({title:'一键代购',content: result.info,time: 2});
	}		
}

function closeDialog(){
	var list = art.dialog.list;
	for (var i in list) {
    	list[i].close();
	};
}

function dotipmsg(){
	art.dialog({title:'一键代购',content: '正在抓取商品数据，请稍等....<br><br><img src="/img/loading.gif" alt="商品加载" >',time: 30});
}
	
/*$('#daigouform').ajaxForm({
  beforeSubmit :dotipmsg,	
  success: complete,
  dataType: 'json'
});	*/

$('#keyword').autocomplete("/Search/getSuggest", {
		multiple: true,
		dataType: "json",
		parse: function(data) {
			return $.map(data, function(row) {
				return {
					data: row,
					value: row.cid,
					result: row.cap
				}
			});
		},
		
		formatItem: function(item) {
			return item.cap;
		}
}).result(function(e, item) {
		$("#searchform").submit();
});
</script>	
<div style="width:auto; height:52px; overflow:hidden; background:url(/Ulowi/Tpl/default/Public/images/top_bj.jpg);" alt="代购中国商品"  class="nobd mrg9">
  <div id="nav" class="middle nobd" style="height:52px; width:988px;">
    <div id="menu" class="left" style="height:52px;">
        <ul class="menu_ul nobd"> 
          <li id="mn_home"><a href="/">首页</a></li>   
          <li id="mn_service"><a href="/Service/index.html">代购</a></li>
          <li id="mn_zhuanyun"><a href="/Service/buy.html">国际转运</a></li>   
          <li id="mn_browse"><a href="/See/index.html">最新代购</a></li>
          <li id="mn_yunfei"><a href="/Freight/index.html">国际运费</a></li>
          <li id="mn_pinglun"><a href="/Review/index.html">用户评论</a></li>
          <li id="mn_zixun"><a href="/Article/index.html">购物资讯</a></li>
          <li style="width:2px;"></li>
        </ul>
    </div> 
	<div style="width:238px;text-indent:5px;line-height:35px; text-align:left;margin-top:11px;height:35px;" class="right transbox">
		<img src="/img/car.png" alt="代购购物车商品"  style="margin-top:2px;" class="left" />
		<div style="width:132px;" class="left">购物车共有<span id="global_cart_count" style="color:#ff3300;padding:2px;">0</span>样商品</div>
		<a href="/cart/index.html"><img src="/img/js.png" style="margin-top:7px;" class="left" alt="购物车结算"></a>
	</div>	
  </div>
</div>

<div id="m1" style="width:988px; height:100%; overflow:hidden;" class="middle nobd mrg14">
  <div style="width:988px; height:100%; overflow:hidden; margin:0px;" class="nobd">
    <div id="m1l" style="width:647px; min-height:486px; height:100%; overflow:hidden; padding-bottom:20px;" class="left bbrd">
      <div style="height:30px; width:100%;">
        <div style="margin-top:14px;margin-left:5px; text-align:left; text-indent:12px;" class="left">您所在的位置:&nbsp;&nbsp;<a href="/">首页</a>&nbsp;>&nbsp;资讯</div>
      </div>
      <div class="news_left middle" style="text-align:left; padding-bottom:6px;">
        <div align="center">
          <h2><span style="font-size:14px; font-family:Arial, Helvetica, sans-serif">如何跟踪运单</span></h2>
        </div>
        <div class="news_content1 middle">
          <span class="Apple-style-span" style="font-family:Verdana, Arial, Helvetica, sans-serif;line-height:24px;font-size:14px;color:#666666;">
<p style="margin-left:0px;">
	<span style="color:#333333;"><br />
在您提交运单后，我们将在1-2个工作日内将您的包裹寄出；<br />
如遇周日或国家法定节假日，由于邮局不收件需相应顺延。如包裹处理过程有特殊状况，我们也会与您及时联系。<br />
<br />
包裹发出后，您可以在“包裹跟踪查询”自行查询；<br />
提醒：EMS与AIR方式的数据更新一般有2-3个工作日的滞后，如果包裹刚发出，请您过几天查询；<br />
同时您也可以联系客服人员查询包裹。<br />
</span>
</p>
</span>        </div>
      </div>
    </div>
    
        <div id="faq" style="width:298px; height:200px; overflow:hidden; margin-top:14px;" class="brd">
        <div style="width:312px; height:32px; line-height:32px; overflow:hidden; vertical-align:middle;background:url(/Ulowi/Tpl/default/Public/images/tbg1.png) repeat-x; text-align:left; text-indent:15px;" class="nobd"><span class="fnt_b">常见问题</span><span style="display:inline-block; margin-right:30px;" class="right fnt_gry"><a href="/Help/index.shtml" target="_blank">更多</a></span></div>
        <div id="faq_lst" style="width:312px; height:160px;text-align:left; margin-top:10px;">
          <ul>
            <li><a href="/Help/detail/id/84.shtml" target="_blank"  title="代购商品范围">代购商品范围</a></li><li><a href="/Help/detail/id/243.shtml" target="_blank"  title="如何在国外买中文书">如何在国外买中文书</a></li><li><a href="/Help/detail/id/229.shtml" target="_blank"  title="为什么唯唐代购的国际运费比官网的低这么多呢？">为什么唯唐代购的国际运费比官网的低这么多</a></li><li><a href="/Help/detail/id/17.shtml" target="_blank"  title="我该如何选择快递方式？">我该如何选择快递方式？</a></li><li><a href="/Help/detail/id/33.shtml" target="_blank"  title="什么是代购，代购有哪些好处">什么是代购，代购有哪些好处</a></li><li><a href="/Help/detail/id/73.shtml" target="_blank"  title="马来西亚怎么淘宝？">马来西亚怎么淘宝？</a></li>          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="gw1 middle mrg14"></div>
<div style=" background: none repeat scroll 0% 0% #FFF; border-top: 1px solid #E6E6E6; border-bottom: 1px solid #E6E6E6;padding: 20px 0px; margin-top: 20px;" class="mrg14">
   <img src="/img/dibubaozhang.png"; alt="代购保障"; style=" text-align: center;" />
   </div>
<div id="gbottom" style="width:auto; height:auto; overflow:hidden; background:url(/img/bb.gif) repeat-x; alt="图标线条"  padding-bottom:10px;  border-top:1px solid #dddddd;">
  <div style="width:auto; margin-top:0px; background:#f2f2f2; height:3px;" class="nobd"></div>
  <div id="copyright" style="width:988px; height:auto; margin-top:5px;" class="middle nobd">
    <div id="top_nav" style="width:712px; height:32px; margin-top:8px;" class="middle">
      <ul class="middle">
        <li><a href="/About/index/s/1.shtml" style="color:#959493;">关于我们</a></li>
        <li class="top_nav_fg"><a href="/About/index/s/2.html" style="color:#959493;">联系我们</a></li>
        <li class="top_nav_fg"><a href="/About/index/s/7.html" style="color:#959493;">合作伙伴</a></li>
        <li class="top_nav_fg"><a href="/About/index/s/3.html" style="color:#959493;">友情链接</a></li>
        <li class="top_nav_fg"><a href="/FeedBack/index.html" style="color:#959493;">意见反馈</a></li>
        <li class="top_nav_fg"><a href="/About/index/s/5.html" style="color:#959493;">隐私声明</a></li>
        <li class="top_nav_fg"><a href="http://www.viatang.com/sitemap.html" target="_blank" style="color:#959493;">网站地图</a></li>		
      </ul>
    </div>
	<ul class="middle" >
    <div style="text-align:center; color:#959493; padding-bottom: 5px; margin-top: 1px;">©2013-2015 viatang.com 版权所有. 
       <a href="http://www.miibeian.gov.cn/state/outPortal/loginPortal.action" target="_blank" style="color:#959493;">沪ICP备14050218号-2</a></div><li>
	   <a style="color:#959493">上海唐悦信息技术有限公司 版权所有</a>
   <div style="text-align:center; color:#959493; padding-bottom: 20px; margin-top: 1px;"></div>
  </div>
</div>
<div style="display:none">
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 964633675;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/964633675/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-55147188-1', 'auto');
  ga('send', 'pageview');

</script>
</div>
<div style="display:none">
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3Fd827e8f704db0e475e30509e186166e3' type='text/javascript'%3E%3C/script%3E"));
</script>
</div>
</body>
<script type='text/javascript' src='http://tb.53kf.com/kf.php?arg=10067534&style=1'></script>
</html>