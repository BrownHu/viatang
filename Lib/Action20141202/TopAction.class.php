<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 	淘客网址转换模块
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/toputils' );
load ( '@/functions' );
class TopAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'click' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function click() {
		$url = strtolower( base64_decode(trim ( $_GET ['u'] )));
		$id = trim ( $_GET ['i'] );
		if (($url && $url != '') || ! empty ( $id )) {		
			if (! empty ( $url )) {
				if(strpos($url,'taobao')){
					$toUrl = taobao_taobaoke_t9 (  $url  );
				}else{
					$toUrl =   $url;
				}
			} else {
				$toUrl = taobao_taobaoke_t9('http://item.taobao.com/item.htm?id=' . $id);
			}
			header ( "Location:$toUrl" );
		} else {
			$this->display ( 'Public:nofund' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function item() {
		$url = trim ( $_GET ['u'] );
		$url = str_replace ( '^', '/', $url );
		$url = base64_decode ( $url );
		
		if ($url != '') {
			$href = "<a id=\"goto\" href=\"" . $url . "\">&nbsp;</a>";
		} else {
			$href = "<a id='goto' href='/'>&nbsp;</a>";
		}
		$this->assign ( 'url', $href );
		$this->display ();
	}
	
	// ----------------------------------------------------------------------------------
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