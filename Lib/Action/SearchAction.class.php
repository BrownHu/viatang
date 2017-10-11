<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 搜索
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import('ORG.Util.Page');
import ( '@.ORG.Top.TopClient' );
import ( '@.ORG.Top.request.TbkItemsGetRequest' );

class SearchAction extends HomeAction {
	
	protected $suggest_url = 'https://suggest.taobao.com/sug?code=utf-8&area=c2c&q=';
	
	// ------------------------------------------------------------------------------------------------
	public function index() {
		$pageNo = IntVal ( $_REQUEST ['p'] );
		$pageNo = ($pageNo > 0 && $pageNo <= 100) ? $pageNo : 1;
		$keyword = strip_tags ( trim ( $_REQUEST ['q'] ) );
		$keyword = rtrim($keyword,',');
		
		
		$pos = strpos(strtolower($keyword),strtolower('/Item/step2.html'));
		if(strpos(strtolower($keyword),strtolower(C('DOMAIN')))>0 && $pos>0){
			$keyword = substr($keyword,$pos);
			header('location:' . $keyword);
			exit;
		}

		if($this->validateURL($keyword)){			
			$id = $this->processId($keyword);
			if($id){
				header("location:/i/$id.html");
			}else{
				header('location:/Item/step2.html?itemUrl=' . urlencode($keyword));
			}
			exit;
		}else {
			$cid = intval($_REQUEST['cid']);

			if (isset($_REQUEST['_m']) && ($_REQUEST['_m'] == 'get')) {
				$keyword = urldecode($keyword);
			}
			
			$this->clean_xss($keyword);
			if($this->isLimtBrand($keyword)){
				$_html = '非常抱歉!没有找到符合条件的相关商品';
				$this->assign('limit_brand',$_html);
			}

			$sort = $_REQUEST['sort'];
			$pagesize = 48;
			
			$this->assign('functonName', date('YdmHis').'_'.time());
			$this->assign('curtime', time());
			$this->assign('PageSize', $pagesize);
			$this->assign('PageNo', $pageNo);
			$this->assign('TotalPages', 100);
			$this->assign('soearch_keyword', $keyword);
			$this->assign('sort', $sort);
			$page = $this->showpage(100 * $pagesize, $keyword, $sort);
			$this->assign('PageBar', $page);
			$this->assign('cid', $cid);

			$_hot_search_key = C('hot_search_key');
			if ($_hot_search_key) {
				$_search_ary = explode(',', $_hot_search_key);
				$this->assign('hot_search_key', $_search_ary);
			}

			$_query = '';
			foreach ($_REQUEST as $k=>$_r){
				if($k == 'sort') continue;
				$_query .= '&'.$k.'='.$_r;
			}
			//dump($_REQUEST);
			
			$_query = ltrim($_query,'&');
			$this->clean_xss($_query);
			$this->assign('url_query',$_query);
			
			$product['title'] = $keyword;
			$this->assign('product', $product);

			$this->display();
		}
	}
	
	public function loadResult(){
		$cid = $_REQUEST['cid'];
		$keyword = rtrim($_REQUEST['q'],',');
		$pageNo  = $_REQUEST['p'];
		$sort = $_REQUEST['sort'];
		$pageNo = ($pageNo && ($pageNo!= '') && is_numeric($pageNo))?$pageNo:1;
 		
		
 		$c = new TopClient ();
 		$c->format = 'json';
 		$c->checkRequest = false;
 		
 		//取商品详情列表
 		$c->appkey = C('APP_KEY');
 		$c->secretKey = C('SECRET_KEY');
 		
 		$req = new TbkItemsGetRequest();
 		$req->setFields('discount_price,zk_final_price,num_iid,title,nick,price,shop_type,pic_url,item_url,shop_url');
 		$req->setSort($sort);
 		$req->setKeyword(urldecode($keyword));
 		if($cid){
 			$req->setCid($cid);
 		}
 		$req->setPageSize(20);
 		$req->setPageNo($pageNo - 1);
 		$resp = $c->execute ( $req );
 		
			
		$product = array ();
		if ($resp->tbk_items && isset ( $resp->tbk_items->tbk_item)) {
				foreach ( $resp->tbk_items->tbk_item as $_r ) {
					$product [] = array (
						'id' => $_r->num_iid,
						'price' => $_r->price,
						'domain' => $_r->shop_type,
						'img' => $_r->pic_url,
						'title' => $_r->title,
						'detail_url' => $_r->item_url,
						'shop_url' => $_r->shop_url ,
						'shop' => $_r->nick,
						'loc' => $_r->nick,
						'seller' => $_r->nick
					);
				}
		}
		
		foreach ($product as $_p){
	       $_result .= "<LI class='product_item' tag='".$_p['id']."'>".
						 "<div class='pic middle'>".
							"<a href='/Item/step2.html?itemUrl=" . $_p['detail_url'] . "' target='_blank'>".
							   "<img src='" . $_p['img'] . "' style='cursor:pointer;width:220px; height:220px;'>" . 
							 "</a>".
						  "</div>".
						  "<div class='price middle' style='text-align:left;'>" .
						    "<STRONG>￥" . $_p['price'] . "</STRONG>" . 
						  "</div>".
						  "<div style='width:220px; height:40px; text-align:left; line-height:18px; padding-top:5px; overflow:hidden; ' class='middle'>" .
							 '<a href="/Item/step2.html?itemUrl=' . $_p['detail_url'] . '">' . 
							    getShortTitle($_p['title'],25,true) . 
							 "</a>" .
						  '</div>'.
						  '<div style="width:220px; height:22px; line-height:18px;   text-align:left;" class="middle" >'.
							 '<span style="display:inline-block; width:38px;" class="left"> ' . 
							 	'商家：' .
							 '</span>' . 
							 '<A href="' . $_p['shop_url']  . '" style="text-decoration::none; color:#2e74d3;" target="_blank">' . 
							 	$_p['seller'] . 
							  '</A>'.
						  '</div>'.
					'</LI>';
		}
			
		echo $_result;
	}
	
	public function getRecomment(){
		$_list = A('Item')->recomment();
		$_html = '';
		foreach($_list as $p){
			$_html .= '<li>'.
			'<div class="img">'.
			'<a href="/Item/step2.html?itemUrl='. $p['item_url'].'" target="_blank">'.
			'<img width="160" height="160" src="'. $p['pict_url'].'">'.
			'</a>'.
			'</div>'.
			'<div class="name" style="text-align:left"><a href="/Item/step2.html?itemUrl='. $p['item_url'].'" target="_blank">'. getShortTitle($p['title'],25,true).'</a>'.
			'</div>'.
			'</li>';
		}
		echo $_html;
	}
	
	// ---------------------------------------------------------------------------------------------
	public function getSuggest(){
		$_json = '';
		header('Content-Type:text/html;charset=utf-8');
		if(isset($_GET['q']) && $_GET['q'] != ''){
			$_url = $this->suggest_url .trim($_GET['q']);
			$_html = crawl($_url);
			
			$result = json_decode($_html,true);
			$array = array();
			foreach ($result['result'] as $_r){
				$array[] = array('cid' => $_r[1],
								 'cap' => $_r[0]);
			}
			$_json = json_encode($array,JSON_UNESCAPED_UNICODE);
		}
		echo $_json;
	}


	// ------------------------------------------------------------------------------------------------
	private function showpage($_count, $_k,$_sort) {
		$p = new Page ( $_count, 48 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$p->parameter .= '&q=' . urlencode ( $_k ).'&sort='.$_sort;
		return $p->show ();
	}


	function validateURL($url) {
		$preg="/^(https?:\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/";
		if(preg_match($preg,$url)){
			return true;
		}else {
			return false;
		}
	}
	
	function clean_xss(&$string, $low = False)
	{
		if (! is_array ( $string ))
		{
			$string = trim ( $string );
			$string = strip_tags ( $string );
			$string = htmlspecialchars ( $string );
			if ($low)
			{
				return True;
			}
			$string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
			$no = '/%0[0-8bcef]/';
			$string = preg_replace ( $no, '', $string );
			$no = '/%1[0-9a-f]/';
			$string = preg_replace ( $no, '', $string );
			$no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
			$string = preg_replace ( $no, '', $string );
			return True;
		}
		$keys = array_keys ( $string );
		foreach ( $keys as $key )
		{
			clean_xss ( $string [$key] );
		}
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
}
?>