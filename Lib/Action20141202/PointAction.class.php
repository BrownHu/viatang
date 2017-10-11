<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 积分功能模块
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class PointAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'My/point' );
	}
	
	//------------------------------------------------------------------------------------------------
	//积分兑换
	public function commit() {
		$point = $_POST ['point'];
		if ($this->user && ! empty ( $point )) {
			$this->error('您好，已停止积分兑换服务！');
			return;
			$DAO = D ( 'Finance' );
			$finance = $DAO->finace ( $this->user ['id'] );
			$unit = 0;
			if ($finance && ($point <= $finance ['point'])) {
				$unit = floor ( $point / 200 );
				if ($unit > 0) {
					$money_befor = $finance ['money'];
					$rebate_befor = $finance ['rebate'];
					$point_befor = $finance ['point'];
					
					$finance ['point'] = $finance ['point'] - $point;
					$finance ['rebate'] = $finance ['rebate'] + $unit;
					$DAO->updateInfo ( $finance );
					
					$remark = '积分兑换';
					$this->writeFinaceLog ( $this->user ['id'], $this->user ['login_name'], $money_befor, $rebate_befor, $point_befor, $remark, $unit, $point, 408 );
					$this->writePointLog ( $this->user ['id'], $this->user ['login_name'], $point, 408, $remark );
				}
			} 
		}
		$this->display('Public:result');
	}
	
	//------------------------------------------------------------------------------------------------
	public function exchange() {
		$user = Session::get ( C ( 'MEMBER_INFO' ) );
		if($user){
			$FinanceDAO = D ( 'Finance' );
			$finance = $FinanceDAO->finace ( $user ['id'] );
			$this->assign ( 'point', $finance ['point'] );
		}
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//记积分日志
	private function writePointLog($uid, $un, $point, $type, $remark) {
		$data ['user_id'] = $uid;
		$data ['user_name'] = $un;
		$data ['point'] = 0 - $point;
		$data ['type'] = $type;
		$data ['remark'] = $remark;
		$data ['create_time'] = time ();
		$DAO = M ( 'PointLog' );
		$DAO->data ( $data )->add ();
	}
	
	//------------------------------------------------------------------------------------------------
	//记财务变更记录
	private function writeFinaceLog($uid, $un, $mnybfr, $rbtbfr, $pntbfr, $remark, $reb, $point, $typ) {
		$data ['user_id'] = $uid;
		$data ['user_name'] = $un;
		$data ['type_id'] = $typ; //包裹结算，见business.inc.php定义
		$data ['pay_id'] = 0;
		$data ['order_id'] = 0; //这里记 订单号：商品号
		$data ['package_id'] = 0; //对应的包裹编号
		$data ['product_id'] = 0;
		$data ['pointlog_id'] = 0;
		
		$data ['chagne_total'] = 0;
		$data ['money'] = 0;
		$data ['money_before'] = $mnybfr;
		$data ['money_after'] = $mnybfr; //这里是退单，所以全记为加
		$data ['rebate'] = $reb;
		$data ['rebate_before'] = $rbtbfr;
		$data ['rebate_after'] = $rbtbfr + $reb;
		$data ['point'] = $point;
		$data ['point_before'] = $pntbfr;
		$data ['point_after'] = $pntbfr - $point;
		
		$data ['remark'] = $remark;
		$data ['create_time'] = time ();
		
		$DAO = M ( 'FinanceLog' );
		$DAO->data ( $data )->add ();
	}
}
?>