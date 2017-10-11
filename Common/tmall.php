<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 * 
 * 天猫商品价格获取 
 +------------------------------------------------------------------------------
 * @category  ulowi
 * @author    soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

require_once THINK_PATH.'/Lib/ORG/Util/Snoopy.class.php';

//----------------------------------------------------------------------------------------
//取得商品信息
function getItem($product_url){
	$id = getIdFromUrl ( $product_url );
		
	$url = 'http://mdskip.taobao.com/core/initItemDetail.htm?itemId=' . $id;	
	$snoopy = new Snoopy ();
	$snoopy->agent = 'Mozilla/5.0 (Windows NT 6.1; rv:30.0) Gecko/20100101 Firefox/30.0';
	$snoopy->referer = $product_url;
	$snoopy->rawheaders ["COOKIE"] = 't=58ebe9a9f80f8016599b5b70a5872740; cna=vXBCDGwdUQgCAbStNbRKsOBp; mt=ci%3D0_0; isg=3765566CDE370C2C66284F669BFEED49; tma=6906807.46027518.1405131901808.1405131901808.1405131901808.1; tmd=2.6906807.46027518.1405131901808.; uc1=cookie14=UoW3uip2HYabeQ%3D%3D; v=0; cookie2=3f568c6740c9ceb4c8833079f74d2bad; _tb_token_=cE4DwfDGwUep; ucn=center';
	
	$snoopy->fetch ( $url ); // 获取所有内容
	return $snoopy->results; // 显示结果
}

//----------------------------------------------------------------------------------------
// 解析URL中的ID
function getIdFromURL($url) {
	$UrlArry = parse_url ( $url );
	$query_str = $UrlArry ['query'];
	$query_array = explode ( '&', $query_str );
	$id = '';
	foreach ( $query_array as $query ) {
		$qur = explode ( '=', $query );
		if ($qur [0] == 'id') {
			$id = $qur [1];
			break;
		}
	}
	return $id;
}

//----------------------------------------------------------------------------------------
//从返回的商品信息json串中解析出价格
function getPrice($product_url){
	$result = getItem($product_url);//下载商品信息串
	
	//对结果预处理，去掉空格换行
	$result = str_replace(array("\r\n","\n","\r","\t",chr(9),chr(13)),'',$result);
	$result = str_replace('null', '"null"', $result);
	$result = str_replace('false', '"false"', $result);
	$result = str_replace('true', '"true"', $result);
	
	//对数字进行加双引号处理
	$mode="#\:([0-9]+)#m";
	preg_match_all($mode,$result,$s);
	$s = $s[1];
	if(count($s)>0){
		foreach($s as $v){
			$result = str_replace(':'.$v.',',':"'.$v.'",',$result);
			$result = str_replace(':'.$v.']',':"'.$v.'"]',$result);
		   	$result = str_replace(':'.$v.'}',':"'.$v.'"}',$result);
		}
	}

	$mode="#([0-9]+)\:#m";
	preg_match_all($mode,$result,$s);
	$s=$s[1];
	if(count($s)>0){
		foreach($s as $v){
			$result = str_replace(','.$v.':',',"'.$v.'":',$result);
			$result = str_replace('['.$v.':','["'.$v.'":',$result);
			$result = str_replace('{'.$v.':','{"'.$v.'":',$result);
		}
	}
	
	//转换成utf-8便于json解码
	$result = iconv('gb2312','utf-8',$result);
	$price_list = json_decode($result,true);
	return $price_list['defaultModel'];//['itemPriceResultDO']['priceInfo'];
}
?> 