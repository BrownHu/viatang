<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 *
 * 淘宝开放平台接口辅助工具 
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

//返回淘宝客的应用密钥
function getAppToken() {
	$token = new stdClass ();
	$DAO = M ( 'TaokeConfig' );
	$entity = $DAO->where ( "status=1" )->limit('1')->select ();
	
	if ($entity) {
		$token->nick = trim ( $entity ['nick'] );
		$token->appkey = trim ( $entity ['app_key'] );
		$token->secretKey = trim ( $entity ['securet_key'] );
	} 
	return $token;
}

function getAppToken2() {
	$token = new stdClass ();
	$token->nick = 'soitun_fan';
	$token->appkey = '21504334';
	$token->secretKey = 'a002c90d9a601d23ea98da26869de302';
	return $token;
}

//返回TopClient
function getTopClient($token, $result_format = 'json') {
	import ( '@.ORG.Top.TopClient' );
	$c = new TopClient ();
	$c->appkey = $token->appkey;
	$c->secretKey = $token->secretKey;
	$c->format = $result_format;
	$c->checkRequest = C ( 'CHECK_REQUEST' );
	return $c;
}
function taobao_taobaoke_t9($url){
	return 'http://s.click.taobao.com/t_9?p=mm_13532438_0_0&l='.urlencode($url).'&unid=0';
}
//获取商品列表
function getItemsRequest($nick, $field, $pagesize, $sort) {
	import ( '@.ORG.Top.request.TmallItemsExtendSearchRequest' );
	$req = new TmallItemsExtendSearchRequest ();
	$req->setCat ( $field );
	//$req->setNick ( $nick );
	$req->setPageSize ( $pagesize );
	//$req->setMallItem ( true );
	$req->setSort ( $sort );
	return $req;
}

//获取商品列表
function getItemsRequestTaoke($nick, $field, $pagesize, $sort) {
	import ( '@.ORG.Top.request.TaobaokeItemsGetRequest' );
	$req = new TaobaokeItemsGetRequest ();
	$req->setFields ( $field );
	$req->setNick ( $nick );
	$req->setPageSize ( $pagesize );
	$req->setMallItem ( true );
	$req->setSort ( $sort );
	return $req;
}

//转换商品网址为淘客网址
function getTaokeUrl($nick, $id) {
	import ( '@.ORG.Top.request.TaobaokeItemsConvertRequest' );
	$req = new TaobaokeItemsConvertRequest ();
	$req->setFields ( 'click_url' );
	$req->setNick ( $nick ); //C ( 'TAOBAO_NICK' ) 
	$req->setNumIids ( $id );
	return $req;
}

function getLogisticsTrace($trad_no, $seller) {
	import ( '@.ORG.Top.request.LogisticsTraceSearchRequest' );
	$req = new LogisticsTraceSearchRequest ();
	$req->setTid ( $trad_no );
	$req->setSellerNick ( $seller );
	return $req;
}

function getProductDetail($nick, $iid) {
	import ( '@.ORG.Top.request.TaobaokeItemsDetailGetRequest' );
	$req = new TaobaokeItemsDetailGetRequest ();
	$req->setNick ( $nick );
	$req->setNumIids ( $iid );
	$req->setFields ( 'iid,num_iid,detail_url,title,nick,type,cid,desc,pic_url,num,list_time,delist_time,stuff_status,location,price,express_fee,has_discount,freight_payer,seller_credit_score,shop_click_url,click_url,volume,stuff_status,has_invoice,auction_point' );
	$req->setOuterCode ( 'ulowi' );
	return $req;
}

//取商品详细情信息
function getItemDetail($iid) {
	import ( '@.ORG.Top.request.ItemGetRequest' );
	$req = new ItemGetRequest ();
	$req->setNumIid($iid);
	$req->setFields ( 'iid,num_iid,cid,detail_url,title,nick,type,desc,pic_url,num,delist_time,stuff_status,location,price,express_fee,freight_payer,item_img.url,volume,stuff_status,auction_point,sku.price,sku.quantity,sku.properties_name' );	
	return $req;
}

function getSellerInfo($nick) {
	import ( '@.ORG.Top.request.UserGetRequest' );
	$req = new UserGetRequest ();
	$req->setNick ( $nick );
	$req->setFields ( 'user_id,nick,consumer_protection,seller_credit,type' );
	return $req;
}

//将对象转换为数组
function objToAry($obj) {
	if (is_object ( $obj )) {
		$obj = get_object_vars ( $obj );
	}
	if (is_array ( $obj )) {
		foreach ( $obj as $i => $item ) {
			$obj [$i] = get_object_vars ( $item );
		}
	
	}
	return $obj;
}
?>