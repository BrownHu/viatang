<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 订单分享
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


class RecommentAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->share ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function share() {
		if (S ( 'RecommentJson' )) {
			$data_list = S ( 'RecommentJson' );
		} else {
			$DAO = new Model ();
			$oredr_share_sql = "SELECT p.id as id, p.user_name as un,  p.title as n, p.url as u, p.thumb as p, p.price1 as m, p.create_time as d, p.amount as c, p.seller as s FROM product p LEFT JOIN orders_saler os ON os.id=p.order_saler_id where os.status >0 " . " AND p.thumb !=''   order by p.create_time desc limit 0,20";
			$data_list = $DAO->query ( $oredr_share_sql );
			S ( 'RecommentJson', $data_list );
		}
		
		$result = $this->formate_to_str ( $data_list );
		echo '[' . $result . ']';
	}
	
	//------------------------------------------------------------------------------------------------
	//将数据集转换成json串
	private function formate_to_str($data_list) {
		$result = '';
		$count = count ( $data_list );
		for($i = 0; $i < $count; $i ++) {
			$un = $data_list [$i] ['un'];
			$data_list [$i] ['un'] = substr ( $un, 0, 1 ) . '***' . substr ( $un, strlen ( $un ) - 1, strlen ( $un ) );
			$data_list [$i] ['d'] = date ( 'm-d', $data_list [$i] ['d'] );
		}
		
		foreach ( $data_list as $key => $value ) {
			$result .= '{"id":' . $value ['id'] . ',"un":"' . str_replace ( '\\', '', $value ['un'] ) . '","n":"' . str_replace ( '\\', '', $value ['n'] ) . '","u":"' . $value ['u'] . '","p":"/Uploads/pic/product/' . $value ['p'] . '_s.jpg","m":' . $value ['m'] . ',"d":"' . $value ['d'] . '","c":' . $value ['c'] . ',"s":"' . $value ['s'] . '"},';
		}
		
		$result = rtrim ( $result, ',' );
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function shareEx() {
		if (S ( 'IndexProductList' )) {
			$ProductList = S ( 'IndexProductList' );
		} else {
			$ProdcutDAO = M ( 'Product' );
			$ProductList = $ProdcutDAO->where ( "thumb != '' AND image != ''" )->order ( 'create_time desc' )->limit ( '0,4' )->select ();
			S ( 'IndexProductList', $ProductList );
		}
	  
		return $this->formate_result($ProductList); 
	}
	
	//------------------------------------------------------------------------------------------------
	public function formate_result($a){
		$result = '';
		if(!empty($a)){
			foreach ($a as $i=>$item){
				$div = '<div id="rt_'.$i.'" onmouseover="on_hover(this.id,' . "'index_hover');" . '" onmouseout="on_out(this.id,'. "'index_hover');" . '" class="idx_ord_item left nobd">';
				$result = $result .  
				
				$item[''];
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'share' );
	}
}

?>