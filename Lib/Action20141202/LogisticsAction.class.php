<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 淘宝物流跟踪接口
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
load ( '@/toputils' );
class LogisticsAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$DAO = M ( 'OrderTrace' );
		$DAO->where ( "seller = 'taobao.com' OR seller='tmall.com' seller='m.taobao.com' OR seller='beta.item.taobao.com'" )->delete ();
		$DAO->where ( "trade_no = '' OR seller = ''" )->delete ();
		
		$now = time ();
		$lessTime = $now - 3600;
		$DataList = $DAO->where ( "trace_no='' AND order_status=0 AND last_update<$lessTime" )->limit ( 20 )->select ();
		
		foreach ( $DataList as $i => $item ) {
			if ($this->isYRK ( $item ['trade_no'] )) {
				$item ['order_status'] = 1;
				$item ['last_update'] = $now;
				$DAO->where ( 'id=' . $item ['id'] )->save ( $item );
			} else {
				$response = @$this->getTraceLog ( trim ( $item ['trade_no'] ), trim ( $item ['seller'] ) );
				if ($response && ! empty ( $response->out_sid )) {
					$item ['trace_no'] = $response->out_sid;
					$item ['company_name'] = $response->company_name;
					$item ['last_update'] = $now;
					$DAO->where ( 'id=' . $item ['id'] )->save ( $item );
				}
				$item ['last_update'] = $now;
				$DAO->where ( 'id=' . $item ['id'] )->save ( $item );
				sleep ( 1 );
			}
		}
	}
	
	//-----------------------------------------------------------------------------------------------------------
	//取得订单的物流跟踪信息
	private function getTraceLog($trad_no, $nick) {
		$result = false;
		if ((trim ( $trad_no ) != '') && (floatval ( trim ( $trad_no ) ) > 0) && (trim ( $nick ) != '')) {
			$c = getTopClient ();
			$req = getLogisticsTrace ( $trad_no, $nick );
			$result = $c->execute ( $req );
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	//更新已入库跟踪信息
	private function isYRK($trade_no) {
		$count = M ( 'Product' )->where ( "trade_no='$trade_no' AND status in (5,6,7,9,11,12,14,15,16,17)" )->count ();		
		return ($count > 0)  ? true :false;
	}
}
?>