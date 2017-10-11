<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 个人收藏
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


load ( '@/functions' );
import ( '@.Action.ParseProduct' );
import ( '@.ORG.Top.TopClient' );
import ( '@.ORG.Top.request.ItemGetRequest' );
import ( '@.ORG.Top.request.TbkItemRecommendGetRequest' );

class FavoriteAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$url = trim ( $_GET ['url'] );
		if (empty ( $url )) {
			$url = Session::get ( 'item_url' );
			Session::set ( 'item_url', null );
		}
		$this->assign ( 'url', urldecode(base64_decode ( $url )));
		$this->display ( 'step1' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function step2() {
		$page_url = trim ( $_POST ['itemUrl'] );
		$product_url = '';

		if (! empty ( $page_url ) && (strlen ( $page_url ) > 0) && $this->user) {
			$domain = parse_domain ( $page_url );
			if ($domain == 'amazon.com.cn' || $domain == 'amazon.cn') {
				$page_url = str_replace ( "detailApp?ref", "detailApp/ref", $page_url );
			}
			
			if (strtolower ( $domain ) == 'paipai.com') {
				$product_url = $page_url;
				$codeStr = $this->parsePath ( $page_url );
				$page_url = 'http://api.paipai.com/item/getItem.xhtml?charset=utf-8&format=json&pureData=1&itemCode=' . $codeStr;
			}

			if ((strtolower ( $domain ) == 'taobao.com') || (strtolower ( $domain ) == 'tmall.com') || (strtolower ( $domain ) == 'detail.tmall.com')|| (strtolower ( $domain ) == 'yao.95095.com')|| (strtolower ( $domain ) == 'ju.taobao.com')) {
				$id = $this->processId($page_url);
				$product = $this->getDataForTaobao($id,$page_url);
			} elseif (strtolower ( $domain ) == 'm.tmall.com' || strtolower ( $domain ) == 'm.taobao.com') {
				$product = $this->dataMobile ( $page_url, $domain );
			}else{
				$html = crawl ( $page_url ); //下载页面
				$html = iconv("gb2312","UTF-8//IGNORE",$html);dump($page_url);exit;
				$parser = new ParseProduct ();
				$product = $parser->analyse ( $domain, $html ); //分析页面，提取商品信息
			}




			if (strtolower ( $domain ) == 'paipai.com') {
				$this->assign ( 'productUrl', $product_url );
			} else {
				$this->assign ( 'productUrl', $page_url );
			}
			$this->assign ( 'product', $product );
		}
		
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function step3() {
		if ($this->user) {
			$DAO = M ( 'FavoriteProduct' );
			
			$product ['url'] = $_POST ['productUrl'];
			$product ['title'] = trim ( $_POST ['productName'] );
			$product ['price'] = trim ( $_POST ['productPrice'] );
			$product ['shipping_fee'] = trim ( $_POST ['productSendPrice'] );
			$product ['amount'] = trim ( $_POST ['productNum'] );
			$product ['note'] = trim ( $_POST ['productRemark'] );
			$product_image = trim ( $_POST ['image'] ); //来源网站图片地址
			$product ['image'] = $product_image;
			$seller = trim ( $_POST ['seller'] );
			
			if (empty ( $seller )) {
				$product ['seller'] = parse_domain ( trim ( $_POST ['productUrl'] ), false );
			} else {
				$product ['seller'] = $seller;
			}
			
			$item_price = trim ( $_POST ['productPrice'] ); //计算单件商品总价
			$item_count = trim ( $_POST ['productNum'] );
			$item_shipping_fee = trim ( $_POST ['productSendPrice'] );
			$product ['total'] = floatval ( $item_price ) * intval ( $item_count ) + floatval ( $item_shipping_fee );
			
			if (strlen ( $product_image ) > 0) {
				$product ['thumb'] = $this->downImage ( $product_image );
			} else {
				$product ['thumb'] = '';
			}
			
			$product ['user_id'] = $this->user ['id'];
			$product ['user_name'] = $this->user ['login_name'];
			$product ['create_time'] = time ();
			
			$DAO->data ( $product )->add ();
			$count = $DAO->where ( 'user_id=' . $this->user ['id'] )->count ();
			$sum = $DAO->where ( 'user_id=' . $this->user ['id'] )->sum ( 'total' );
			$shipping_fee = $DAO->where ( 'user_id=' . $this->user ['id'] )->sum ( 'shipping_fee' );
			$total = $sum + $shipping_fee;
			Session::set ( C ( 'CART_COUNT' ), $count );
			$this->assign ( 'cart_count', $count );
			$this->assign ( 'cart_total', $total );
			$this->assign ( 'product', $product );
			$this->display ( 'Public:result' );
		
		} else {
			$this->redirect ( 'Public/login_min' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		$IdAry = $_POST ['id'];
		if ($this->user && ! empty ( $IdAry ) && (count ( $IdAry ) > 0)) {
			$Idlst = implode ( ',', $IdAry );
			$DAO = M ( 'FavoriteProduct' );
			$DAO->where ( "id in($Idlst)" )->delete ();
		}
		$this->redirect ( 'My/favorite' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function delone() {
		$user = Session::get ( C ( 'MEMBER_INFO' ) );
		$id = $_GET ['id'];
		
		if ($user && ! empty ( $id )) {
			$DAO = M ( 'FavoriteProduct' );
			$DAO->where ( "id=$id" )->delete ();
		}
		$this->redirect ( 'My/favorite' );
	}
	
	//------------------------------------------------------------------------------------------------
	//将选中收藏添加到购物车
	public function addtocart() {
		$id = $_GET ['id'];
		
		if ($this->user && ! empty ( $id )) {
			$DAO = M ( 'FavoriteProduct' );
			$entity = $DAO->where ( "id=$id" )->find ();
			
			if ($entity) {
				$ShopCartDAO = M ( 'ShopingCart' );
				$ShopCartDAO->data ( $entity )->add ();
			}
		}
		$this->redirect ( 'Cart/index' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function addToCartBat() {
		$IdAry = $_POST ['id'];
		if ($this->user && ! empty ( $IdAry ) && (count ( $IdAry ) > 0)) {
			$Idlst = implode ( ',', $IdAry );
			$DAO = M ( 'FavoriteProduct' );
			$DataList = $DAO->where ( "id in($Idlst)" )->select ();
			
			$ShopCartDAO = M ( 'ShopingCart' );
			foreach ( $DataList as $i => $item ) {
				@$ShopCartDAO->data ( $item )->add ();
			}
		}
		$this->redirect ( 'Cart/index' );
	}
	
	//------------------------------------------------------------------------------------------------
	//添加网站
	public function addSite() {
		if ($this->user && isset ( $_POST )) {
			$DAO = M ( 'FavoriteSite' );
			$data ['caption'] = $_POST ['caption'];
			$data ['url'] = $_POST ['url'];
			$data ['last_update'] = time ();
			
			$id = $_POST ['id'];
			if (empty ( $id )) {
				$data ['user_id'] = $this->user ['id'];
				$data ['user_name'] = $this->user ['login_name'];
				$DAO->data ( $data )->add ();
			}else{
				$DAO->where("id=$id")->save($data);
			}
			
			$this->redirect ( 'My/shop' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//删除站点
	public function delsite() {
		$id = $_GET ['id'];
		
		if ($this->user && ! empty ( $id )) {
			$DAO = M ( 'FavoriteSite' );
			$DAO->where ( "id=$id" )->delete ();
		}
		$this->redirect ( 'My/shop' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function delsitebat() {
		$IdAry = $_POST ['id'];
		if ($this->user && ! empty ( $IdAry ) && (count ( $IdAry ) > 0)) {
			$Idlst = implode ( ',', $IdAry );
			$DAO = M ( 'FavoriteSite' );
			$DAO->where ( "id in($Idlst)" )->delete ();
		}
		$this->redirect ( 'My/shop' );
	}
	
	public function _empty() {
		$this->redirect ( 'index' );
	}
	
	/**---------------------------------------------------------------------------------------
	 * 内部函数
	 * ---------------------------------------------------------------------------------------
	 */
	
	//------------------------------------------------------------------------------------------------
	private function parsePath($url) {
		$result = $url;
		if (strlen ( $url ) > 0) {
			$UrlAry = parse_url ( $url );
			$pathStr = $UrlAry ['path'];
			$pathAry = explode ( '/', $pathStr );
			$codeStr = $pathAry [count ( $pathAry ) - 1];
			$codeAry = explode ( '-', $codeStr );
			$result_1 = $codeAry [0];
			$codeAry_1 = explode ( '?', $result_1 );
			$result = $codeAry_1 [0];
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//下载商品描述图片并成缩略图
	private function downImage($product_image) {
		$result = '';
		if (strlen ( $product_image ) > 0) {
			$year = date ( "Y" );
			$month = date ( "m" );
			$day = date ( "d" );
			
			$thumb_path = ULOWI_UPLOADS_PATH . "/pic/product/$year/$month/$day";
			createFolder ( $thumb_path );
			
			$randnum = rand_string ( 16 );
			$image_name =  $thumb_path . '/' .$randnum . "_" . $month . $day . "_b.jpg";
			$image_name_new =  $thumb_path . '/' .$randnum . "_" . $month . $day . "_s.jpg";
			
			$image_content = file_get_contents ( $product_image );
			if ($image_content) {
				file_put_contents ($image_name, $image_content );
				if (file_exists ( $image_name )) {
					//reduce_image ( $image_name,  $image_name_new );
					import ( 'ORG.Util.Image' );
					try {
						ini_set ( 'memory_limit', '60M' );
						Image::thumb ( $image_name, $image_name_new, '', 70, 70 );
						ini_set ( 'memory_limit', '16M' );
					} catch ( Exception $e ) {
						Log::write ( '生成商品缩略图时出错,可能是图片太大', Log::ERR );
					}
				}
			}
			$result = "$year/$month/$day/" . $randnum . "_" . $month . $day;
		}
		return $result;
	}
// 预处理ID
	private function processId($page_url){
		$UrlAry = parse_url ( $page_url );
		$id = 0;
		if (strtolower ( $UrlAry ['host'] ) == 'a.m.taobao.com') {
			$path = $UrlAry ['path'];
			$path = str_replace ( '/i', '', $path );
			$id = str_replace ( '.htm', '', $path );
		} else {
			$queryStr = $UrlAry ['query'];
			$queryAry = explode ( '&', $queryStr );
			$id = $this->getIdFromQuery ( $queryAry );
		}
		return $id;
	}
	// 抓取淘宝移动端数据的方法
	private function dataMobile($page_url, $domain) {
		$id = str_replace ( 'http://a.' . $domain, '', $page_url );
		$id = explode ( '.', $id );
		$id = str_replace ( '/i', '', $id [0] ); // 获得id

		if ($domain == 'm.taobao.com') {
			$url_taobao = "http://item.taobao.com/item.htm?id=$id";
			$domain = "taobao.com";
		}

		if ($domain == 'm.tmall.com') {
			$url_taobao = "http://detail.tmall.com/item.htm?id=$id";
			$domain = "tmall.com";
		}

		$tao_html = file_get_contents_ex ( $url_taobao );
		$tao_html = iconv ( "gb2312", "UTF-8//IGNORE", $tao_html );

		$parser = new ParseProduct ();
		$product = $parser->analyse ( $domain, $tao_html, $id, $url_taobao );
		return $product;
	}

	// ------------------------------------------------------------------------------------------------
	private function getDataForTaobao($pid,$_url){
		$c = new TopClient ();
		$c->format = 'json';
		$c->checkRequest = false;

		//取商品详情列表
		$c->appkey = C('APP_KEY');//'21559277';
		$c->secretKey = C('SECRET_KEY');//'9c3f61a894aa816a2eb433713db3c24f';
		$req = new ItemGetRequest();
		$req->setFields('detail_url,num_iid,title,nick,type,cid,seller_cids,props,input_pids,input_str,desc,pic_url,num,valid_thru,list_time,delist_time,stuff_status,location,price,post_fee,express_fee,ems_fee,has_discount,freight_payer,has_invoice,has_warranty,has_showcase,modified,increment,approve_status,postage_id,product_id,auction_point,property_alias,item_img,prop_img,sku,video,outer_id,is_virtual');
		$req->setNumIid($pid);
		$resp = $c->execute ( $req );


		$product['shop_name'] =  $resp->item->nick;
		$product['shop_url'] = '';
		$product['from_cn']		= '淘宝';
		$product['from_en']		= 'taobao';
		$product['url'] 		= $resp->item->detail_url;
		$product['title'] 		= $resp->item->title;
		$product['image'] 		= $resp->item->pic_url;

		$product['img_list'] = array();
		foreach ($resp->item->item_imgs->item_img as $_r){
			$product['img_list'][]	= $_r->url;
		}
		$product['seller'] 		= $resp->item->nick;

		//属性别名
		$_alias = $this->getPropAalias($resp->item->property_alias);
		$prop_imgs = array();
		foreach ($resp->item->prop_imgs->prop_img as $_r){
			$prop_imgs[$_r->properties] = str_replace(':', '_', $_r->url) ;
		}

		$product['props'] 		= array();
		foreach ($resp->item->skus->sku as $_r){
			$properties_name = $_r->properties_name;
			$sku_id = $_r->sku_id;
			$par = explode(';', $properties_name);
			foreach ($par as $_t ){
				$_tar = explode(':', $_t);
				$_key = $_tar[2];
				$_val = $_tar[3];
				$_k1  = $_tar[0].'_'.$_tar[1];

				$_prop = '';
				if(key_exists($_k1, $_alias)){ $_prop = $_alias[$_k1]; }
				$_val =($_prop != '')?$_prop:$_val;

				if( key_exists($_key, $product['props']) ){
					if(!$this->checkVal($_val,$product['props'][$_key]) ){
						$product['props'][$_key][] =   array('sku_id'=>$sku_id, 'val'=>$_val);
					}
				}else{
					$product['props'][$_key][] =  array('sku_id'=>$sku_id, 'val'=>$_val);
				}
			}
		}

		$product['shipping_fee'] = $resp->item->post_fee;
		$product['price'] = $resp->item->price;

		$product['price_promotion'] = $this->getPromPrice($_url);
		$product['des'] = $resp->item->desc;
		//$this->getRecomm($product['title']);
		return $product;
	}
	// 取得属性别名列表, 结果为: ['1627207_3232483']=>'军绿色'
	private function getPropAalias($prop_str){
		$prop_alias = explode(';', $prop_str);
		$_alias = array();
		foreach ($prop_alias as $_r){
			$_ary =  explode(':', $_r);
			$_k = $_ary[0].'_'.$_ary[1];
			$_alias[$_k] = $_ary[2];
		}
		return $_alias;
	}
	//获取优惠价格
	private function getPromPrice($_url){
		$data_list = getPrice($_url) ;

		$price_obj = $data_list['itemPriceResultDO']['priceInfo'];
		$_price = array();
		$i = 0;
		$_price_i = 0;
		foreach($price_obj as $_k=>$_r){
			if($i == 0) $_price_i = $_r['promotionList'][0]['price'];
			$_price['prom_'.$_k] =  $_r['promotionList'][0]['price'];
			$i++;
		}
		$this->assign('_price_i',$_price_i);

		$quantity_obj = $data_list['inventoryDO']['skuQuantity'];
		$_quantity = array();
		foreach ($quantity_obj as $_k => $_r){
			$_quantity['qty_'.$_k] = $_r['quantity'];
		}

		$this->assign('promotion_price',$_price);
		$this->assign('quantity_sku',$_quantity);
	}
	//检查给定值是否已经存在
	private function checkVal($_val, $ary){
		$_resut = false;
		if(is_array($ary) ){
			foreach ($ary as $_r){
				if(is_array($_r) && ($_val == $_r['val']) ) {
					$_result = true;
					break;
				}
			}
		}
		return $_result;
	}
	private function getIdFromQuery($query) {
		$result = '';

		if (! empty ( $query ) && is_array ( $query )) {
			foreach ( $query as $key => $value ) {
				$item = explode ( '=', $value );
				if (($item [0] == 'id') || ($item [0] == 'item_num_id') || ($item [0] == 'item_id')) {
					$result = $item [1];
					break;
				}
			}
		}
		return $result;
	}
}
?>