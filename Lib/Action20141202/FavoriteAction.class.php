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

class FavoriteAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$url = trim ( $_GET ['url'] );
		if (empty ( $url )) {
			$url = Session::get ( 'item_url' );
			Session::set ( 'item_url', null );
		}
		$this->assign ( 'url', base64_decode ( $url ) );
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
			
			$html = crawl ( $page_url ); //下载页面	
			$html = iconv("gb2312","UTF-8//IGNORE",$html);
			$parser = new ParseProduct ();
			$product = $parser->analyse ( $domain, $html ); //分析页面，提取商品信息
			

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
	// 快速加收藏
	public function dofav(){
		if ($this->user ) {
			$product['title'] = $_POST['title'];
			$product['url'] = $_POST['url'];
			$product['price'] = $_POST['price'];
			$product['thumb'] = $_POST['img'];
			$product ['user_id'] = $this->user ['id'];
			$product ['user_name'] = $this->user ['login_name'];
			$product ['create_time'] = time ();
			$id= M ( 'FavoriteProduct' )->data($product)->add();
			$result = ($id && $id!=0)?1:0;
			$this->ajaxReturn ( null, '', 1 );
		}else{
			$this->ajaxReturn ( null, '',0 );
		}
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

}
?>