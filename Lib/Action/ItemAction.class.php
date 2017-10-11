<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 快速代购
 +------------------------------------------------------------------------------
 * @category   	ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 	上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      	http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
import ( '@.ORG.Top.TopClient' );
import ( '@.ORG.Top.request.ItemGetRequest' );
import ( '@.ORG.Top.request.TbkItemRecommendGetRequest' );
import ( '@.ORG.Top.request.TbkItemGetRequest' );

class ItemAction extends HomeAction {
	protected $product_url_taobao = 'http://onoshop.hz.taeapp.com/index.php/Index/daigoucms?id=__ID__&ip=101.93.213.182';//__IP__
	
	protected $post_area_code = ''; //收货地址编码，需查询淘宝， 用来通过淘宝查询运费时用
	protected $taobao_domain = array('m.tmall.com','m.taobao.com','taobao.com','tmall.com',
									 'detail.tmall.com','detail.taobao.com','yao.95095.com','ju.taobao.com');
	
	// ---------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize ();
		if ($this->user) {
			$this->dao = M ( 'ShopingCart' );
			$_list = $this->dao->where ( 'user_id=' . $this->user ['id'] )->select ();
			$this->assign ( 'cart_item_list', $_list );
		}

		$this->post_area_code = C ( 'POSTEAGE_FEE_CODE' );
	}
	
	// ---------------------------------------------------------------------------------------------
	// 对网址进行预处理
	public function index() {
		$this->redirect('step2');
	}
	
	// ---------------------------------------------------------------------------------------------
	// 抓取商品信息, 为了提升性能，这里暂时只做淘宝
	public function step2() {
		
		$itemUrl = '';
		if(isset($_REQUEST ['itemUrl'])){
			$itemUrl = '/item/detail.html?itemUrl=' . $_REQUEST ['itemUrl'];
		}else if(isset($_REQUEST ['id'])){
			$itemUrl =  '/item/detail/id/' .$_REQUEST ['id'];
			if(isset($_REQUEST ['currentPrice'])){
				$itemUrl = $itemUrl.'/currentPrice/'.$_REQUEST ['currentPrice'];
			}
			if(isset($_REQUEST ['title'])){
				$this->assign('title',$_REQUEST ['title']);
			}
		}
				
		$this->assign('itemUrl',$itemUrl);
		$this->display();
	}
	
	public function tt(){
		echo $this->getRelProduct('虾家有鱼');	
	}
	
	public function detail(){
		$page_url = trim ( $_REQUEST ['itemUrl'] );
		//echo $page_url;exit;
		
		if (! empty ( $page_url ) && (strlen ( $page_url ) > 0) || isset($_REQUEST['id'])) {
			$domain = parse_domain ( $page_url );
			
			$is_taobao = true;
			$currentPrice = !empty($_REQUEST ['currentPrice'])?$_REQUEST ['currentPrice']:0;
			if(!empty($domain)){
				if ( (strtolower ( $domain ) == 'taobao.com') || (strtolower ( $domain ) == 'tmall.com') || 
					 (strtolower ( $domain ) == 'detail.tmall.com') || (strtolower ( $domain ) == 'yao.95095.com') || 
					 (strtolower ( $domain ) == 'ju.taobao.com') || (strtolower ( $domain ) == 'm.tmall.com') ||
					 (strtolower ( $domain ) == 'world.taobao.com') || (strtolower ( $domain ) == 'world.tmall.com') || 
					 (strtolower ( $domain ) == 'm.taobao.com')
				   ) {
					$id = $this->processId($page_url);
					
					$product = $this->getDataForTaobao($id);
					//dump($product);exit;
					$this->getRecomm($id,'recomment_prodcut',1,7,false);
					//$this->getRelProduct($product['seller']);
					$this->assign('_price_i',$product['activity_price']);
					$this->assign('prop_count',count($product['props']));
				}else{
					$is_taobao = false;
					$product = array();
					$this->assign('product_url',$page_url);
					$this->assign('prop_count',0);
				}
			}else if(!empty($_REQUEST['id'])){
				$product = $this->getDataForTaobao($_REQUEST['id']);
		
				$this->getRecomm($_REQUEST['id'],'recomment_prodcut',1,7,false);
				//$this->getRelProduct($product['seller']);
				$this->assign('_price_i',$product['activity_price']);
				$this->assign('prop_count',count($product['props']));
			}else{
				$is_taobao = false;
				$product = array();
				$this->assign('product_url',$page_url);
				$this->assign('prop_count',0);
			}
				
			$product['currentPrice'] = !empty($_REQUEST ['currentPrice'])?$_REQUEST ['currentPrice']:$product['price'];
			$_price_i = $this->view->get(_price_i);
			$_price_i = empty($_price_i)||strtolower($_price_i)=='n'?$product['currentPrice']:$_price_i;
			$_price_i = empty($_price_i)?$product['price']:$_price_i;
		
			//if($this->isLimtBrand($product['title'])){
			//	$this->assign('is_limit_brand',1);
			//}
			$this->assign('title',$product['title']);
			$this->assign('is_taobao',$is_taobao);
			$this->assign('product',$product);
			$this->assign('_price_i',$_price_i);
		}
	    
		//$this->assign('see_also_url',$this->see_also_url);
		$this->assign ('title',$product['title'] );
		$this->assign ('keywords','淘宝代购,海外华人代购,'.$product['title']);
		$this->assign ('description','海外华人代购淘宝商品,'.$product['title']);
		$this->assign ('product_url',$page_url );
		$html = $this->fetch("detail");
		echo $html;
	}

	public function getTaobaoProductInfo($pageUrl){
		$id = $this->processId($pageUrl);
		$product = $this->getDataForTaobao($id);
		$product['id'] = $id;

		return $product;
	}
	
	// ---------------------------------------------------------------------------------------------
	// 预处理ID
	private function processId($page_url){
		$UrlAry = parse_url ( $page_url );
		$id = 0;
		if ((strtolower ( $UrlAry ['host'] ) == 'a.m.taobao.com') || (strtolower ( $UrlAry ['host'] ) == 'a.m.tmall.com')) {
			$path = $UrlAry ['path'];
			$path = str_replace ( '/i', '', $path );
			$id = str_replace ( '.htm', '', $path );
		} if((strtolower ( $UrlAry ['host'] ) == 'world.taobao.com') || (strtolower ( $UrlAry ['host'] ) == 'world.tmall.com') || (strtolower ( $UrlAry ['host'] ) == 'tw.tmall.com') || (strtolower ( $UrlAry ['host'] ) == 'tw.taobao.com')){
			$path = $UrlAry ['path'];
			$path = str_replace ( '/item/', '', $path );
			$id = str_replace ( '.htm', '', $path );
		} else {
			$queryStr = $UrlAry ['query'];
			$queryAry = explode ( '&', $queryStr );
			$id = $this->getIdFromQuery ( $queryAry );
		}
		return $id;
	}
	
	// ---------------------------------------------------------------------------------------------
	// 显示抓取结果
	public function loadItem() {
                  $this->assign ('title','商品信息提交-全球最专业海外华人代购,留学生代购中国商品网站-viatang.com');
	         $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
                  $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');

		if (isset($_GET['_k'])) {
	         $this->assign ( 'product', F($_GET['_k']) );
		}
		$this->display ( 'step2' );
	}
	
	// ---------------------------------------------------------------------------------------------
	// 下载页面
	private function downloadHtml($_url) {
		$snoopy = new Snoopy ();
		$snoopy->agent = 'Mozilla/5.0 (Windows NT 6.1; rv:30.0) Gecko/20100101 Firefox/30.0';
		$snoopy->fetch ( $_url );
		return $snoopy->results;
	}
	
	// ---------------------------------------------------------------------------------------------
	public function step3() {
		if ($this->user) {
			$product_url = (isset ( $_POST ['productUrl'] )) ? strtolower ( trim ( $_POST ['productUrl'] ) ) : '';
			
			$pre_process_price = (isset ( $_POST ['productPrice'] )) ? trim ( $_POST ['productPrice'] ) : 0;
			
			if (($pre_process_price != 0) && (strpos ( $pre_process_price, '-' ) !== false)) {
				$item_price = trim ( substr ( $pre_process_price, strpos ( $pre_process_price, '-' ) + 1, strlen ( $pre_process_price ) ) );
			} else {
				$item_price = floatval ( trim ( $pre_process_price ) );
			}
			
			if (($item_price == 0) || ($product_url == '')) {
				$this->ajaxReturn ( false, L ( 'item_price_or_url_error' ), 0 );
			}
			
			$item_count = (isset ( $_POST ['productNum'] ) && is_numeric ( trim ( $_POST ['productNum'] ) )) ? intval ( trim ( $_POST ['productNum'] ) ) : 1;
			$item_shipping_fee = (isset ( $_POST ['productSendPrice'] ) && is_numeric ( trim ( $_POST ['productSendPrice'] ) )) ? floatval ( trim ( $_POST ['productSendPrice'] ) ) : 10;
			
			$product ['url'] = $product_url;
			$product ['title'] = (isset ( $_POST ['productName'] )) ? trim ( $_POST ['productName'] ) : '';
			$product ['price'] = floatval ( $item_price );
			$product ['shipping_fee'] = $item_shipping_fee;
			$product ['amount'] = $item_count;
			//$product ['prop'] = trim ( $_POST ['prop'] );

			$product ['note'] = trim ( $_POST ['productRemark'] );
			$product ['reserv_package'] = (isset ( $_POST ['reserv_package'] ) && is_numeric ( $_POST ['reserv_package'] )) ? intval ( trim ( $_POST ['reserv_package'] ) ) : 0;
			$product ['reserv_brand'] = (isset ( $_POST ['reserv_brand'] ) && is_numeric ( $_POST ['reserv_brand'] )) ? intval ( trim ( $_POST ['reserv_brand'] ) ) : 0;
			$product ['is_emergency'] = (isset ( $_POST ['is_emergency'] ) && is_numeric ( $_POST ['is_emergency'] )) ? intval ( trim ( $_POST ['is_emergency'] ) ) : 0;
			
			$seller = trim ( $_POST ['seller'] );
			$seller = (empty ( $seller ) || $seller == '') ? parse_domain ( trim ( $_POST ['productUrl'] ), false ) : $seller;
			$product ['seller'] = ($seller == '') ? C ( 'DOMAIN' ) : $seller;
			$product ['total'] = floatval ( $item_price ) * $item_count + $item_shipping_fee;
			$product_image = trim ( $_POST ['image'] );
			$product ['image'] = $product_image; // 来源网站图片地址
			$product ['thumb'] = (strlen ( $product_image ) > 0) ? @$this->downImage ( $product_image ) : '';
			
			$product ['user_id'] = $this->user ['id'];
			$product ['user_name'] = $this->user ['login_name'];
			$product ['create_time'] = time ();
			
			if (! $this->checkExistsInCart ( $product ['url'], $product ['note'], $this->user ['id'], $item_count )) {
				$result = $this->dao->data ( $product )->add ();
			} else {
				$result = true;
			}
			
			if ($result !== false) {
				$count = $this->dao->where ( 'user_id=' . $this->user ['id'] )->count ();
				
				$_SESSION [C ( 'CART_COUNT' )] = $count;
				$this->ajaxReturn ( null, $count, 1 );
			} else {				
				$this->ajaxReturn ( null, '2', 0 );
			}
		} else {
		
			$this->ajaxReturn ( null, '3', 0 );
		}
	}
	
	// 检查购物车中是否已经存在
	private function checkExistsInCart($url, $remark, $uid, $count) {
		$_count = $this->dao->where ( "url='$url' and note='$remark' and user_id=" . $uid )->count ();
		if ($_count > 0) {
			$this->dao->execute ( "update " . $this->dao->getTableName () . " set amount=amount+$count where url='$url' and note='$remark' and user_id=$uid" );
			return true;
		}
		return false;
	}
	
	// ------------------------------------------------------------------------------------------------
	// 下载商品描述图片并成缩略图
	private function downImage($product_image) {
		$result = '';
		if (strlen ( $product_image ) > 0) {
			$year = date ( "Y" );
			$month = date ( "m" );
			$day = date ( "d" );
			$thumb_path = ULOWI_UPLOADS_PATH . "/pic/product/$year/$month/$day";
			@createFolder ( $thumb_path );
			$randnum = rand_string ( 16 );
			$image_name_pre = $randnum . "_" . $month . $day;
			$image_name = $thumb_path . '/' . $image_name_pre . "_b.jpg";
			$image_name_new = $thumb_path . '/' . $image_name_pre . "_s.jpg";
			
			$image_content = @file_get_contents_ex ( $product_image );
			
			if ($image_content) {
				@file_put_contents ( $image_name, $image_content );
				if (file_exists ( $image_name )) {
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
			$result = $year . '/' . $month . '/' . $day . '/' . $image_name_pre;
		}
		return $result;
	}
	
	public function change_item_count() {
		if ($this->user && isset ( $_REQUEST ['pid'] ) && is_numeric ( $_REQUEST ['pid'] ) && isset ( $_REQUEST ['c'] ) && is_numeric ( $_REQUEST ['c'] )) {
			$this->dao->execute ( "UPDATE " . $this->dao->getTableName () . " SET amount=" . $_REQUEST ['c'] . " WHERE id=" . $_REQUEST ['pid'] . " AND user_id=" . $this->user ['id'] );
		}
	}
	
	// ------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
	
	// ------------------------------------------------------------------------------------------------
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
	
	// ------------------------------------------------------------------------------------------------
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
	
	// ------------------------------------------------------------------------------------------------
	// 抓取淘宝移动端数据的方法
	private function dataMobile($page_url, $domain) {
		$id = str_replace ( 'http://a.' . $domain, '', $page_url );
		$id = explode ( '.', $id );
		$id = str_replace ( '/i', '', $id [0] ); // 获得id
		
		return $id;
	}
	
	// ------------------------------------------------------------------------------------------------
	private function getDataForTaobao($pid){
		$_url = str_replace('__ID__',$pid,$this->product_url_taobao);
		$result = crawl($_url);
		
		if($result != ''){
			return json_decode($result,true);
		}
		return array();
	}
	
	public function price_json(){
		echo 'var price_str=' . @file_get_contents($this->price_url_taobao.urlencode($resp->item->detail_url));
	}
	
	public function active_price(){
		$url = $_GET['url'];
		echo  @file_get_contents($url);
		//$script = '$("#p_price").val('.$price.').show();$("#tip_price").hide();$("#tip_youhui").show();'; 
		//echo $script;	
	}
	
	//获取淘宝商品套餐的价格
	private function getTaobaoSku($skus_obj){
		if(!empty($skus_obj) && !empty($skus_obj->item) && !empty($skus_obj->item->skus) && !empty($skus_obj->item->skus->sku)){
			$json = array();
			foreach ($skus_obj->item->skus->sku as $r){
				$price = round( ($r->price/$this->money_rate),2);
				$item = array('properties' => $r->properties,
						'quantity' => $r->quantity,
						'sku_id'=>$r->sku_id,
						'price' => $price);
				$json[] = $item;
			}
			return $json;
		}
		return false;
	
	}
	
	// ------------------------------------------------------------------------------------------------
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
	
	// ------------------------------------------------------------------------------------------------
	//检查属性标题是否存在
	private function checkCap($_c,$_ary){
		foreach ($_ary as $_r){
			if($_r['cap'] == $_c){
				return true;
			}
		}
		return false;
	}
	
	// ------------------------------------------------------------------------------------------------
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
	
	// ------------------------------------------------------------------------------------------------
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
	
	// ------------------------------------------------------------------------------------------------
	//推荐 商品
	private function getRecomm($id,$save,$type,$count,$debug=false){
		$c = new TopClient ();
		$c->format = 'json';
		$c->checkRequest = false;
		
		$c->appkey = '23184802';
		$c->secretKey = 'f7adb7fbc54c7fa1184d1d85197bd1aa';
		//取商品详情列表
		$req = new TbkItemRecommendGetRequest();
		$req->setFields('num_iid,title,pict_url,item_url');
		$req->setNumIid("$id");
		$req->setRelateType("$type");
		$req->setCount("$count");
		$req->setPlatform("1");
		$resp = $c->execute ( $req );
	    $p_ary = objectToArray($resp->results->n_tbk_item);
	    if($debug){
	    	dump($resp);
	    	exit;
	    }
		$this->assign($save,$p_ary);
		return $p_ary;
	}
	
	//------------------------------------------------------------------------------------------------------
	//转换商品网址为淘客网址
	private function getTaokeUrl($id) {
		import ( '@.ORG.Top.request.TaobaokeItemsConvertRequest' );
		$req = new TaobaokeItemsConvertRequest ();
		$req->setFields ( 'click_url' );
		$req->setNick ('soitun_fan');
		$req->setNumIids ( $id );
		return $req;
	}

	// ------------------------------------------------------------------------------------------------
	//关联 商品
	private function getRelProduct($seller){
		$_result = @file_get_contents($this->see_also_url.$seller);
		$p_ary = json_decode($_result,true);
		$this->assign('shop_prodcut',$p_ary);
		return $p_ary;
	}
	
	private function validateURL($url) {
		$preg="/^(https?:\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/";
		if(preg_match($preg,$url)){
			return true;
		}else {
			return false;
		}
	}
	
	public function recomment(){
		if(isset($_GET['id'])){
		  return $this->getRecomm($_GET['id'],'recomment_prodcut',1,7,false);	
		}
		
		return array();
	}
}