<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 快速代购
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
import ( 'ORG.Util.Snoopy' );
import ( '@.Action.ParseProduct' );

class ItemAction extends HomeAction {
	
	protected $post_area_code = ''; //收货地址编码，需查询淘宝， 用来通过淘宝查询运费时用
	
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
		$url = trim ( $_GET ['url'] );
		if (empty ( $url )) {
			$url = Session::get ( 'item_url' );
			Session::set ( 'item_url', null );
		}
		if (! empty ( $url )) {
			$url = str_replace ( '^', '/', $url );
			$url = str_replace ( '@', '==', $url );
			$this->assign ( 'url', base64_decode ( $url ) );
		}
		
		$this->display ( 'step1' );
	}
	
	// ---------------------------------------------------------------------------------------------
	// 抓取商品信息, 为了提升性能，这里暂时只做淘宝
	public function step2() {
		$page_url = trim ( $_POST ['itemUrl'] );
		
		if (! empty ( $page_url ) && (strlen ( $page_url ) > 0)) {
			$_cache_name = md5 ( $page_url . '_product_viatang' );
			if (F ( $_cache_name )) {
				$product = F ( $_cache_name );
			} else {
				$domain = parse_domain ( $page_url );
				if ((strtolower ( $domain ) == 'taobao.com') || (strtolower ( $domain ) == 'tmall.com')) {
					$id = $this->processId($page_url);
					$tao_html = $this->downloadHtml ( $page_url );
					$tao_html = iconv ( "gb2312", "UTF-8//IGNORE", $tao_html );
					$parser = new ParseProduct ();
					$product = $parser->analyse ( $domain, $tao_html, $id, $page_url, $this->post_area_code );
				} elseif (strtolower ( $domain ) == 'm.tmall.com' || strtolower ( $domain ) == 'm.taobao.com') {
					$product = $this->dataMobile ( $page_url, $domain );
				}else{
					$product = array();
				}
				if(!empty($product)) F ( $_cache_name, $product);
			}
			
			if(!empty($product)) {
				$this->ajaxReturn ( null, $_cache_name, 1 );
			}else{
				$this->ajaxReturn ( null, '输入的URL有误 ', 0 );
			}
			//$_SESSION ['item_fetch_product'] = $product;
			//$this->ajaxReturn ( null, '数据抓取成功，正在跳转...', 1 );
		}
		$this->ajaxReturn ( null, '输入的URL有误 ', 0 );
	}
	
	// ---------------------------------------------------------------------------------------------
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
	
	// ---------------------------------------------------------------------------------------------
	// 显示抓取结果
	public function loadItem() {
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
			
			// $result = $this->dao->data ( $product )->add ();
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
}