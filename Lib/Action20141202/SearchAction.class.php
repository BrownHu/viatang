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

import ( 'ORG.Util.Page' );
import ( 'ORG.Util.Snoopy' );
load ( '@/simple_html_dom' );
load ( '@/functions' );
load ( '@/tmall' );
class SearchAction extends Action {
	
	protected $search_url = 'http://s.taobao.com/search?ajax=true&_ksTS=1415329016877_731&callback=jsonp732&commend=all&ssid=s5-e&search_type=item&sourceId=tb.index&spm=1.7274553.1997520841.1&tab=all&bcoffset=1&s=0&q=';
	
	protected $taobao_detail_url = 'http://item.taobao.com/item.htm?id=';
	
	protected $tmall_detail_url = 'http://detail.tmall.com/item.htm?id=';
	
	protected $data_key = 's';
	
	protected $suggest_url = 'http://suggest.taobao.com/sug?code=utf-8&area=c2c&q=';
	
	// ------------------------------------------------------------------------------------------------
	public function index() {
		$pageNo = IntVal ( $_REQUEST ['p'] );
		$pageNo = ($pageNo > 0 && $pageNo <= 100) ? $pageNo : 1;
		$keyword = strip_tags ( trim ( $_REQUEST ['q'] ) );
		$keyword = rtrim($keyword,',');
		$sort = $_REQUEST['sort'];
		$page = $this->showpage ( 100 * 44, $keyword,$sort );
		$this->assign ( 'PageNo', $pageNo );
		$this->assign ( 'TotalPages', 100 );
		$this->assign ( 'soearch_keyword', $keyword );
		$this->assign('sort',$sort);
		$this->assign ( 'PageBar', $page );
		$this->display ();
	}
	
	public function loadResult(){
		$keyword = rtrim($_REQUEST['q'],',');
		$pageNo  = $_REQUEST['p'];
		$sort = $_REQUEST['sort'];
 		$_url = $this->search_url . $keyword . '&data-value=' . ($pageNo - 1) * 44;
 		$_url .= '&initiative_id=tbindexz_' . date('Y-h-d',time());
 		$_url .= '&data-key='.$this->data_key;
 		$_url .= '&sort='.$sort;
 		
 		$_cache_name = md5($_url);
 		if(F2($_cache_name)){
 			$product = F2($_cache_name);
 		}else{
			$html = $this->downloadHtml ( $_url );
			$html = iconv ( "gb2312", "UTF-8//IGNORE", $html );
		
			$_pos = strpos ( $html, '(' );
			$html = substr ( $html, $_pos + 1 );
			$html = substr ( $html, 0, strlen( $html)-2 );
			$product_list = json_decode ( $html, true );
			$product = array ();
			if ($product_list && isset ( $product_list ['mods'] ) && isset ( $product_list ['mods'] ['itemlist'] ) && isset ( $product_list ['mods'] ['itemlist'] ['data'] ) && isset ( $product_list ['mods'] ['itemlist'] ['data'] ['auctions'] )) {
				foreach ( $product_list ['mods'] ['itemlist'] ['data'] ['auctions'] as $_r ) {
					$product [] = array (
						'id' => $_r ['nid'],
						'price' => $_r ['view_price'],
						'detail_url' => $_r ['detail_url'],
						'domain' => $this->getDomain ( $_r ['detail_url'] ),
						'img' => $_r ['pic_url'],
						'title' => trim ( $_r ['raw_title'] ),
						'shop_url' => $_r ['shopLink'],
						'shop' => $_r ['nick'],
						'loc' => $_r ['item_loc'],
						'seller' => $_r ['nick']
					);
				}
				
				$_term_expire =time()+7*24*3600;
				F2($_cache_name,$product,$_term_expire);
			}
 		}
		
		$_result = '';
		if(!empty($product)){
			foreach ($product as $_p){
	            $_result .= "<LI class='product_item'>".
								"<div class='pic middle'>".
									"<img src='$_p[img]' width='220' style='cursor:pointer;' onclick=\"buy('$_p[detail_url]');\" />".
								"</div>".
								'<div class="price middle"><STRONG>￥'.$_p['price'].'</STRONG></div>'.
								'<div style="width:220px; height:40px; line-height:18px; padding-top:5px; overflow:hidden; margin-left:14px;" class="middle">'.
									"<a href=\"#nogo\" onclick=\"buy('$_p[detail_url]');\">".getShortTitle($_p['title'],25,true)."</a>".
								'</div>'.
								'<div style="width:220px; height:22px; line-height:18px; margin-left:14px;  text-align:left;" class="middle" >'.
									'<span style="display:inline-block; width:38px;" class="left">商家：</span><A href="'.$_p['shop_url'].'" style="text-decoration::none; color:#2e74d3;" target="_blank">'.$_p['seller'].'</A>'.
								'</div>'.
							'</LI>';
			}
			
		}
		echo $_result;
	}
	
	// ---------------------------------------------------------------------------------------------
	public function getSuggest(){
		$_json = '';
		header('Content-Type:text/html;charset=utf-8');
		if(isset($_GET['q']) && $_GET['q'] != ''){
			$_url = $this->suggest_url .trim($_GET['q']);
			$_html = $this->downloadHtml($_url);
			
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
	
	private function getDomain($_url) {
		$_result = 'taobao';
		switch (parse_domain ( $_url )) {
			case 'taobao.com' :
				$_result = 'taobao';
				break;
			case 'tmall.com' :
				$_result = 'tmall';
				break;
			default :
				$_result = 'taobao';
		}
		return $_result;
	}
	
	// ------------------------------------------------------------------------------------------------
	// 下载页面
	private function downloadHtml($_url) {
		$snoopy = new Snoopy ();
		$snoopy->agent = 'Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0';
		$snoopy->accept = 'text/javascript, application/javascript, application/ecmascript, application/x-ecmascript, */*; q=0.01';
		$snoopy->use_gzip = true;
		$snoopy->fetch ( $_url );
		return $snoopy->results;
	}
	
	// ------------------------------------------------------------------------------------------------
	private function showpage($_count, $_k,$_sort) {
		$p = new Page ( $_count, 44 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$p->parameter .= '&q=' . urlencode ( $_k ).'&sort='.$_sort;
		return $p->show ();
	}
}
?>