<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 宣传页面(用于搜索引擎展示)
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class AdvertiseAction extends HomeAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();		
		$ZoneList = M ( 'DeliverZone' )->where ( 'status=1' )->order ( 'sort' )->select ();
		$this->assign ( 'ZoneList', $ZoneList );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		//评论
		if (S ( 'AderviseReviewList' )) {
			$ReviewList = S ( 'AderviseReviewList' );
		} else {
			$ReviewList =  M ( 'Comment' )->where ( "is_display=1" )->limit ( '6' )->order ( 'create_time desc' )->select ();
			S ( 'AderviseReviewList', $ReviewList );
		}
		$this->assign ( 'ReviewList', $ReviewList );		
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//费用说明 
	public function fy() {
		//评论
		if (S ( 'AderviseReviewList' )) {
			$ReviewList = S ( 'AderviseReviewList' );
		} else {
			$ReviewList = M ( 'Comment' )->where ( "is_display=1" )->limit ( '6' )->order ( 'create_time desc' )->select ();
			S ( 'AderviseReviewList', $ReviewList );
		}
		$this->assign ( 'ReviewList', $ReviewList );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//运费估算
	public function estimate() {
		if (! empty ( $_POST ['zone'] ) && ! empty ( $_POST ['way'] ) && ! empty ( $_POST ['amount'] ) && ! empty ( $_POST ['weight'] )) {
			$zone_id = $_POST ['zone'];
			$way_id = $_POST ['way'];
			$amount = $_POST ['amount'];
			$weight = $_POST ['weight'];
			$shipping = 0;
			$way = M ( 'DeliverAddress' )->where ( "id=$way_id" )->find ();
			
			if ($way) {
				if ($weight <= $way ['start_weight']) {
					$shipping = $way ['start_price'];
				} else {
					$unit = ceil ( ($weight - $way ['start_weight']) / $way ['continue_weight'] );
					$shipping = $way ['start_price'] + $way ['continue_price'] * $unit;
				}
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
				$data ['total'] = number_format ( $shipping + $service_fee + $custom, 2 );
				$this->assign ( 'entity', $data );
			}
			
			//取该地区运费价格列表
			$this->assign ( 'caption', $_POST ['caption'] );
			$PriceList = M ( 'DeliverAddress' )->field ( 'shipping_way,start_weight,start_price,continue_weight,continue_price' )->where ( "zone_id=$zone_id AND status='1'" )->select ();
			$this->assign ( 'PriceList', $PriceList );
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//返回运输方式列表
	public function way() {
		$id = $_GET ['id'];
		if ($id && is_numeric($id)) {
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
	//包裹追踪
	public function trace() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function freight() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function xy(){
		$this->display();	
	}
	
	//------------------------------------------------------------------------------------------------
	//取服务费比例
	private function getServiceRate() {
		$entity =  M ( 'FinaceConfig' )->where ( "item='serve_rate'" )->find ();
		return (!empty($entity)  ) ? $entity ['value'] : 10;
	}
	
	//------------------------------------------------------------------------------------------------
	//取报关费
	private function getcustom() {
		$entity = M ( 'FinaceConfig' )->where ( "item='custom'" )->find ();
		return (!empty($entity)  ) ? $entity ['value'] : 8;
	}

}
?>