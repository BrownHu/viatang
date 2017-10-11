<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 	工具模块
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
load ( '@/functions' );
//include ("Conf/uc.inc.php");
//if (defined ( 'UC_API' )) {include_once 'uc/uc_client/client.php';}
class ToolsAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		Session::set ( c ( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
		
		$ZoneList = M ( 'DeliverZone' )->where ( 'status=1' )->order ( 'sort' )->select ();
		$this->assign ( 'ZoneList', $ZoneList );
	}
	
	//------------------------------------------------------------------------------------------------
	//尺码换算
	public function measure() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//汇率兑换
	public function forex() {
		$this->display ();
	}
	
	public function toll_est(){
		$shipping = 0;
		if(isset($_GET['w']) && is_numeric($_GET['w'])){
			$weight = floatval($_GET['w']);
			
			$DAO = M ( 'DeliverAddress' );
			$item = $DAO->where ('id=133')->find();
			
			if($item){
				if ($weight <= $item ['start_weight']) {
					$shipping = $item ['start_price'];
				} else {
					$unit = ceil ( ($weight - $item ['start_weight']) / $item ['continue_weight'] );
					$shipping = $item ['start_price'] + $item ['continue_price'] * $unit;
				}
			}
		}
		echo '总费用：$'.$shipping;
	}
	
	//------------------------------------------------------------------------------------------------
	//费用估算
	public function estimate() {
		if (! empty ( $_POST ['zone'] ) && ! empty ( $_POST ['amount'] ) && ! empty ( $_POST ['weight'] )) {
			$zone_id = $_POST ['zone'];
			$amount = $_POST ['amount'];
			$weight = $_POST ['weight'];
			
			if (empty ( $amount ) || ! is_numeric ( $amount ) || ! is_numeric ( $zone_id )) {
				return false;
			}
			$result = array ();
			$DAO = M ( 'DeliverAddress' );
			$AddressList = $DAO->field ( 'shipping_way,start_weight,start_price,continue_weight,continue_price,limit_weight' )->where ( "zone_id=$zone_id AND status='1'" )->select ();
			
			$i = 0;
			foreach ( $AddressList as $way ) {
				$shipping = 0;
				if ($weight <= $way ['start_weight']) {
					$shipping = $way ['start_price'];
				} else {
					$unit = ceil ( ($weight - $way ['start_weight']) / $way ['continue_weight'] );
					$shipping = $way ['start_price'] + $way ['continue_price'] * $unit;
				}
				
				$data ['way_cn'] = $way ['shipping_way'];
				$data ['product_fee'] = $amount;
				$data ['start_price'] = $way ['start_price'];
				$data ['continue_price'] = $way ['continue_price'];
				$service_fee = (($amount + $shipping) * $this->getServiceRate ()) / 100;
				$data ['service_fee'] = number_format ( $service_fee, 2 );
				$data ['shipping_fee'] = number_format ( $shipping, 2 );
				
				if ($zone_id != 5) { //非国内需加8元报关费
					$custom = $this->getcustom ();
				} else {
					$custom = 0;
				}
				$data ['custom'] = $custom;
				$data ['total'] = number_format ( $shipping + $service_fee + $custom + $amount, 2 );
				$result [$i] = $data;
				$i = $i + 1;
			}
			
			$this->assign ( 'result', $result );
			$this->assign ( 'PriceList', $AddressList );
		}
		//取该地区运费价格列表
		$this->assign ( 'caption', $_POST ['caption'] );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//返回运输方式列表
	public function way() {
		$id = $_GET ['id'];
		if ($id) {
			$result = '';
			$DataList = M ( 'DeliverAddress' )->field ( 'id,shipping_way' )->where ( "zone_id=$id AND status='1'" )->select ();
			foreach ( $DataList as $key => $value ) {
				$result .= '<option value="' . $value ['id'] . '">' . $value ['shipping_way'] . '</option>';
			}
			if (strlen ( $result ) > 0) {
				echo '<select name="way" id="freight_way"><option value="0">请选择</option>' . $result . '</select>';
			} else {
				echo '<select name="way" id="freight_way"><option value="0">暂不支持</option></select>';
			}
		} else {
			echo '<select name="way" id="freight_way"><option value="0">暂不支持</option></select>';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function logistics() {
		$trace_no = trim ( $_GET ['tno'] );
		if (! empty ( $trace_no )) {
			$this->assign ( 'trace_no', $trace_no );
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function assistant() {
		if (stripos ( $_SERVER ["HTTP_USER_AGENT"], "MSIE" )) {
			$tpl = 'assistant';
		} else if (stripos ( $_SERVER ["HTTP_USER_AGENT"], "firefox" )) {
			$tpl = 'assistant_firefox';
		} else if (stripos ( $_SERVER ["HTTP_USER_AGENT"], "Chrome" )) {
			$tpl = 'assistant_chrome';
		} else if (stripos ( $_SERVER ["HTTP_USER_AGENT"], "Safari" )) {
			$tpl = 'assistant_safari';
		} else {
			$tpl = 'assistant';
		}
		$this->display ( $tpl );
	}
	
	//------------------------------------------------------------------------------------------------
	//国内包裹跟踪查询结果
	public function trace_result() {
		$item_no = trim ( $_POST ['item_no'] );
		$com = trim ( $_POST ['com'] );
		$com = $this->getExpresName($item_no);
		if($com == false){
			$this->ajaxReturn(false,'失败',0);
		}
		$url = "http://www.kuaidi100.com/api?id=6e398ed5dda050e1&com=$com&nu=$item_no&show=2&muti=1";
		$r = file_get_contents_ex( $url );
		if (! empty ( $r )) {
			if (strpos ( $r, '单号不正确' ) > 0) {
				$this->ajaxReturn ( false, '失败', 0 );
			} else {
				$this->ajaxReturn ( $r, '成功', 1 );
			}
		} else {
			$this->ajaxReturn ( false, '失败', 0 );
		}
	}
	
	private function getExpresName($order){
		$name   = json_decode(file_get_contents_ex("http://www.kuaidi100.com/autonumber/auto?num={$order}"), true);
		$result = $name[0]['comCode'];
		if (empty($result)) {
			return false;
		}else{
			return $result;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function doware() {		
		$pid = strtoupper(trim ( $_REQUEST ['product_id'] ));
		$pid = trim ( $pid, '*' );
		$pid = str_replace('*', '', $pid);
		
		$store_no = trim ( $_REQUEST ['store_no'], '*' );
		$store_no = str_replace('*', '', $store_no);
		$result = $this->updateStoreNo($pid,$store_no);

		if( $result  != false){
			echo 'ok';
		}else{
			echo 'error';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function ware() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function ware_list() {
		$DAO = M ( 'Ware' );
		$DataList = $DAO->select ();
		$this->assign ( 'DataList', $DataList );
		
		$this->display ( 'warelist' );
	}
	
	//------------------------------------------------------------------------------------------------
	//更新商品库位
	private function updateStoreNo($pid, $store_no) {
		
		if(empty($pid) || empty($store_no)){
			return false;
		}

		$DAO = new Model();
		if (substr ($pid, 0, 1 ) == 'Z') {
			
			$id = substr ( $pid, 1, strlen ( $pid ) );
			$id = intval ( trim ( $id ) );
			echo $id;
			$result = $DAO->execute("update product_agent set store_no='$store_no',status=5  where id=$id");
			$rtl = $DAO->query("select count(*) from product_agent where store_no='$store_no' and id=$id and status=5");
			$result =($result == 0 && $rtl>0 ) || ($result >0) ?true:false;
		} else {
			$id = intval ( $pid );
			$result = $DAO->execute("update product set storeage_no='$store_no',status=12  where id=$id");
			$rtl = $DAO->query("select count(*) from product where storeage_no='$store_no' and id=$id and status=12");
			$result =($result == 0 && $rtl>0 ) || ($result >0) ?true:false;
		}
	
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//取服务费比例
	private function getServiceRate() {
		$DAO = M ( 'FinaceConfig' );
		$entity = $DAO->where ( "item='serve_rate'" )->find ();
		if ($entity) {
			return $entity ['value'];
		} else {
			return 10;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//取报关费
	private function getcustom() {
		$entity = M ( 'FinaceConfig' )->where ( "item='custom'" )->find ();
		return ($entity) ? $entity ['value'] : 8;		
	}

	//------------------------------------------------------------------------------------------------
	public function status(){
		echo 'ok';
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'Index/index.html' );
	}
}
?>