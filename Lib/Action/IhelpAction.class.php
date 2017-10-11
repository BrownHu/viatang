<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 帮助工具
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
load ( '@/simple_html_dom' );
class IhelpAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	//首页底部查询分类
	public function index() {
		echo $this->fetch ( 'index' );
	}
	
	//------------------------------------------------------------------------------------------------
	//首页代购商品类别
	public function cat() {
		echo $this->fetch ( 'Public:category' );
	}
	
	//------------------------------------------------------------------------------------------------
	//首页最新代购商品20条记录
	public function product() {		
		$ProdcutDAO = M ( 'Product' );
		$html = S ( 'IndexProductListHTML' );
		if (empty ( $html )) {
			$html = '';
			$ProductList = $ProdcutDAO->field ( 'id,title,thumb,image,price1' )->where ( "thumb != '' AND image != '' and recommend=1" )->order ( 'create_time desc' )->limit ( '0,20' )->select ();
			foreach ( $ProductList as $i => $product ) {
				$html = $html . "<div id='rt_{$i}' class='idx_ord_item left'><div class='middle index_recent_item'><a href='/show/" . $product ['id'] . ".html' target='_blank'><img src='http://img.".C('DOMAIN') . "/product/" . $product ['thumb'] . "_b.jpg'  onerror='this.src=". '"' . '/img/noimg80.gif' . '"'.  "' width='156' height='160' class='index_recent_img' /></a></div>" . "<div class='index_recent_title'><a href='/show/" . $product ['id'] . ".html' target='_blank'>" . getShortTitle ( $product ['title'], 20, true ) . "</a></div><div class='index_recent_price'>￥" . $product ['price1'] . "</div></div>";
			}
			$html = str_replace('|', "", $html);
			S ( 'IndexProductListHTML', $html );
		}
		
		//首页商品
		$produc_count = S ( 'Idx_success_count' );
		if (empty( $produc_count )) {
			$produc_count = $ProdcutDAO->count ();
			S ( 'Idx_success_count', $produc_count );
		}
		$produc_count = number_format ( $produc_count * 101, 0 );
		echo $html . '|' . $produc_count;
	}
	
	//------------------------------------------------------------------------------------------------
	//最新代购用户
	public function r() {
		$UserDAO = M ( 'User' );
		$NewDgLst = $UserDAO->field ( 'head_img' )->where ( "head_img != ''" )->order ( 'last_login desc' )->limit ( '9' )->select ();
		$result = '';
		foreach ( $NewDgLst as $vo ) {
			if( trim($vo['head_img']) != ''){
				$result .= '<div class="avatar"><img src="http://img.'.C('DOMAIN') .'/avatar/' . $vo ['head_img'] . '_m.jpg"  onerror="this.src=\'/Public/Images/0.gif\'" /></div>';
			}
		}
		
		$RegUserCount = S ( 'RegUserCount' );
		if (empty ( $RegUserCount )) {
			$RegUserCount = $UserDAO->count ();
			S ( 'RegUserCount', $RegUserCount );
		}
		$RegUserCount = $RegUserCount * 101;
		echo $result . '|' . $RegUserCount;
	}
	
	//------------------------------------------------------------------------------------------------
	//加载广告
	public function ad() {
		$type = trim ( $_GET ['t'] );
		$result = '';
		if (! empty ( $type )) {
			$DAO = M ( 'AdBanner' );
			$count = $DAO->where ( "type=$type AND status=1" )->count ();
			if ($count > 0) {
				$DataList = $DAO->where ( "type=$type AND status=1" )->select ();
				$i = rand ( 0, $count - 1 );
				$item = $DataList [$i];
				if ($item) {
					$gourl = base64_encode ( trim ( $item ['url'] ) );
					$gourl = str_replace ( '/', '^', $gourl );
					$gourl = str_replace ( '==', '@', $gourl );
					$result = '<a href="/go/' . $gourl . '.html" target="_blank"><img id="IndexAdBanner" src="http://img.'.C('DOMAIN') .'/ad/' . $item ['img'] . '.jpg" onload="showTopbnr(\'didxAdBanner\');" style="width:988px; height:90px;"></a>';
				}
			}
		}
		echo $result;
	}
	
	
	public function price(){
		$_url = $_GET['url'];
		$_html = crawl('http://localhost:3000/'.$_url);
		
		if (trim($_html) != ''){
			$domain = parse_domain ( $_url );
			if($domain == 'taobao.com'){
				echo $this->getTaobaoPrice($_html);
			}elseif($domain == 'tmall.com'){
				echo '0';//echo $this->getTmallPrice($_html);
			}
		}else{
			echo '0';
		}
	}
	
	private function getTaobaoPrice($_html){
		$_html = iconv ( "gb2312", "UTF-8//IGNORE", $_html );
		$dom = new simple_html_dom();
		$dom->load($_html);
		$_price = $dom->find('em.tb-rmb-num',0)->innertext;
		$pro_price = strip_tags($dom->find('em[id=J_PromoPriceNum]',0)->innertext);
		$dom->clear();
		return (!empty($pro_price)) ?$pro_price:$_price;
	}
	
	private function getTmallPrice($_html){
		echo $_html;exit;
		$dom = new simple_html_dom();
		$dom->load($_html);
		$_price_html = $dom->find('dl[id=J_StrPriceModBox]');
		if(!empty($pro_html)){
			$_price = strip_tags($_price_html->find('span.tm-price',0)->innertext);
		}
		$pro_html = $dom->find('dl[id=J_PromoPrice]',0);
		if(!empty($pro_html)){
			$pro_price = strip_tags($pro_html->find('span.tm-price',0)->innertext);
		}
		$dom->clear();
		return (!empty($pro_price)) ?$pro_price:$_price;
	}
}
?>