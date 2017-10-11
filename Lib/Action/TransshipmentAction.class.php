<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 	自助购模块
 +------------------------------------------------------------------------------ 
 * @copyright 上海子凌网络科技有限公司˾
 * @author    stone@ulowi.com
 * @version   1.0
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );
import ( 'ORG.Util.Page' );
class TransshipmentAction extends BaseAction {
	
	protected $address;
	protected $finance;
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M ( 'Transshipment' );
		$this->address = M('DeliverAddress');
		$this->finance = D( 'Finance' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'address' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function address() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function report() {
		if ($this->user) {
			$dao = M ( 'DeliverCompany' );
			$list = $dao->select();

			$this->assign('list',$list);
			$this->display ();
		}
	}

	//------------------------------------------------------------------------------------------------
	public function commit() {
		if ($this->user) {
			$data['in_deliver_com'] = $_POST['express'];
			$data['in_trace_number'] = $_POST['express_no'];
			$data['in_trace_date'] = $_POST['express_date'];
			$data['mem'] = $_POST['mem'];

			$count = $this->dao->where('in_trace_number='.$data['in_trace_number'])->count();
			if(!empty($count)){
				$this->error('快递单号已存在');
				return;
			}

			$data['in_trace_date'] = strtotime($data['in_trace_date']);

			$data['user_id'] = $this->user['id'];
			$data['user_name'] = $this->user['login_name'];
			$data['status'] = 0;
			$data['create_time'] = time();
			$this->dao->data($data)->add();
			$this->success('发货单提交成功');
		}
	}
	

	//----------------------------------------------------------------------------------------------
	// 等待审核
	public function ddsh(){
		if ($this->user) {
			$this->_list("status=0 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 在途包裹--》等待收货
	public function ddsf(){
		if ($this->user) {
			$this->_list("status=1 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 已到货
	public function ydh(){
		if ($this->user) {
			$this->_list("status=2 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 已入库
	public function yrk(){
		if ($this->user) {
			$this->_list("status=3 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 已入库
	public function jyz(){
		if ($this->user) {
			$this->_list("status=4 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 已入库
	public function yfh(){
		if ($this->user) {
			$this->_list("status=5 and user_id=".$this->user['id'],'', C('NUM_PER_PAGE'));
			$this->display();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// 打包
	public function package(){
		if ($this->user && isset($_GET['id'])) {
			$countryList = M ( 'DeliverZone' )->where ( 'status = 1' )->order ( 'sort asc' )->select ();
			
			$item = $this->dao->where("id=".$_GET['id'])->find();
			if($item){
				$this->assign ( 'PackageWeight', $item['weight']);
				$this->assign('package',$item);
			}else{
				$this->assign ( 'PackageWeight', 0);
			}
			$this->assign ( 'CountryList', $countryList );
			$this->assign ( 'InsureRate', $this->getInsureRate () );
			$this->display();
		}
	}
	
	public function contact(){
		if ($this->user){
			$this->assign('total_fee',trim($_POST ['total_fee']));
			$this->assign('way_id',$_POST ['way_id']);
			$this->assign('package_id',trim($_POST ['id']));
			$this->assign('weight',trim($_POST ['weight']));
			$this->assign('zone_id',trim($_POST ['zone_id']));
			
			//加载配送区域
			$countryList =  M ( 'DeliverZone' )->where ( 'status = 1' )->order ( 'sort' )->select ();
			$this->assign ( 'CountryList', $countryList );
			
			$this->display();
		}
	}
	
	public function submit(){
		if ($this->user){
			$total_fee = floatval ( trim ( $_POST ['total_fee'] ) );
			$finance = $this->finance->finace($this->user['id']);
			if( $finance && ($total_fee <= $finance[money]) ){
				$data['deliver_id'] = $_POST['way_id']; //配送方式
				$data['zone_id'] = $_POST['zone_id'];
				$data['total_fee'] = $_POST['total_fee'];
				$data['zone_id'] = $_POST['zone_id'];
				
				//国家
				$zone = $this->getZone($_POST['zone_id']);
				if($zone) $data['country'] = $zone['caption_cn'];
				
				// 配送方式
				$way = $this->getWay($_POST['way_id']);
				if($way) $data['deliver_way'] = $way['shipping_way'];
				$data['start_price'] = $_POST['start_price'];
				$data['continue_price'] = $_POST['continue_price'];
				
				$data['address'] = $_POST['address'];
				$data['contact'] = $_POST['contact'];
				$data['zip'] = $_POST['zip'];
				$data['tel'] = $_POST['phone'];
				
				$_fee = $this->doComputeFee($_POST['way_id'],$_POST['weight']);
				$data['cutom_fee'] = $_fee['customFee'];
				$data['shipping_fee'] = $_fee['shippingFee'];
				
				$data['status'] = 4;
				$data['last_update'] = time();
				
				$this->dao->where("id=".$_POST['package_id'])->save($data);
				$this->doFinace($this->user['id'], $this->user['login_name'], $_POST['package_id'], $_POST['total_fee']);
				$this->assign('jumpUrl','/Transshipment/yrk');
				$this->success('操作成功，请等待处理');
			}
			
		}
	}
	
	public function qrsh(){
		if ($this->user && isset($_GET['id'])){
			$this->dao->execute('Update '.$this->dao->getTableName().' set status=8 where id='.$_GET['id']);
			$this->submit('操作成功');
		}
	}
	
	//----------------------------------------------------------------------------------------
	//包裹结算 ,oid 包裹编号
	private function doFinace($uid, $un, $oid, $total) {
		$finance = $this->finance->finace($uid);
		if ($finance) {
			$money_befor = $finance ['money'];
			$reabet_befor = $finance ['rebate'];
			$money_use = 0;
			$rebate_use = 0;
				
			if (($total > 0) && ($finance ['money'] >= $total)) {
				$finance ['money'] = $finance ['money'] - $total;
				$money_use = $total;
			} elseif (($finance ['money'] + $finance ['rebate']) >= $total) {
				$less = $total - $finance ['money']; //差额
				$finance ['money'] = 0;
				$finance ['rebate'] = $finance ['rebate'] - $less;
				$money_use = $money_befor;
				$rebate_use = $less;
			}
				
			$money_use = 0 - $money_use;
			$rebate_use = 0 - $rebate_use;
			$finance ['last_update'] = time ();
			$this->finance->updateInfo($finance); //扣余额
			$remark = '转运包裹运输费用'; // . $total.'元,其中扣除现金帐户:'.$money_use.',扣除折扣帐户:'.$rebate_use;
			$this->writeFinaceLog ( $uid, $un, 0, $oid, $money_use, $money_befor, $reabet_befor, $finance ['point'], $remark, $rebate_use, 309 );
		}
	}
	
	public function del(){
		if ($this->user && isset($_GET['id']) && is_numeric($_GET['id'])) {
			$this->dao->where('id='.$_GET['id'])->delete();
			$this->success('操作完成');
		}
	}
	
	private function getZone($zone_id){
		if($zone_id){
			$item = M('DeliverZone')->where('id='.$zone_id)->find();
			if($item){
				return $item;
			}
		}
		return false;
	}
	
	private function getWay($wid){
		if($wid){
			$item = $this->address->where('id='.$wid)->find();
			if($item) {
				return $item;
			}
		}
		return false;
	}
	
	//----------------------------------------------------------------------------------------
	//记财务变更记录
	private function writeFinaceLog($uid, $unam, $oid, $pid, $money, $mnybfr, $rbtbfr, $pntbfr, $remark, $reb, $typ) {
		$entity = array('user_id' 				=> $uid,
				'user_name' 			=> $unam,
				'type_id' 				=> $typ, //包裹结算，见business.inc.php定义
				'pay_id' 				=> 0,
				'order_id' 				=> $oid, //这里记 订单号：商品号
				'package_id' 			=> $pid, //对应的包裹编号
				'product_id' 			=> $pid,
				'pointlog_id' 			=> 0,
	
				'chagne_total' 		=> $money,
				'money' 				=> $money,
				'money_before' 	=> $mnybfr,
				'money_after' 		=> $mnybfr + $money, //这里是退单，所以全记为加
				'rebate' 				=> $reb,
				'rebate_before' 	=> 0,
				'rebate_after' 		=> 0,
				'point' 					=> 0,
				'point_before' 		=> $pntbfr,
				'point_after' 			=> $pntbfr,
	
				'remark' 				=> $remark,
				'create_time' 		=> time () );
	
		M ( 'FinanceLog' )->data ( $entity )->add ();
	}
	
	//----------------------------------------------------------------------------------------------------------------------
	// 前台ajax 处理
	//----------------------------------------------------------------------------------------------------------------------
	
	//取得配送方式列表
	public function way_lst() {
		$zid = $_GET ['zid'];
		$weight = $_GET ['w'];
		if (empty ( $weight )) {
			$weight = 0;
		}
		$tr_hd = '<tr style="font-weight:bold;"><td align="left" width="100">运送方式</td><td>首重(kg) </td><td>起价(￥) </td><td>续重(kg) </td><td>续价(￥) </td><td>限重(kg)</td></tr> ';
		$emp_str = '<tr><td align="left" bgcolor="#FFFFFF"> - </td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>'  . '</tr>';
	
		if ($zid) {
			$result = '';
			$DataList = $this->address->where ( "status = '1' AND zone_id=$zid" )->order ( 'id' )->select ();
				
			foreach ( $DataList as $key => $value ) {
				if (($weight <= 8000) && ($value ['shipping_way'] == '海运')) {
				}elseif( ($weight <10100) && (trim($value ['shipping_way']) == '专线11-100kg') ){
				}elseif( ($weight >10000) && (trim($value ['shipping_way']) == '专线11kg以内') ){
				}elseif( ($weight <3000) && (($value ['shipping_way'] == 'SAL水陆联运') || ($value ['shipping_way'] == 'AIR 2kg以上') ) ){
				}else{
					$result .= '<tr><td align="left" bgcolor="#FFFFFF"><input type="radio" name="pg_shipping_method[]"  value="' . $value ['id'] . '" onclick="shipping( ' . $value ['id'] . ');checkDHL(\'' . $value ['shipping_way'] . '\');checkXMZX(\'' . $value ['shipping_way'] . '\');setLimitWeight(' . $value ['limit_weight']  . ');" />' . $value ['shipping_way'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_price'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_price'] . '</td>' . '<td bgcolor="#FFFFFF" style="color:#f60;font-weight:bold;">' . $value ['limit_weight'] . '</td></tr> ';
				}
			}
				
			if (strlen ( $result ) > 0) {
				echo $tr_hd . $result;
			} else {
				echo $tr_hd . $emp_str;
			}
		} else {
			echo $tr_hd . $emp_str;
		}
	}
	
	//----------------------------------------------------------------------------------------
	//计算运费、服务费、保险费
	public function computefee() {
		$id = trim ( $_REQUEST ['wid'] );
		$weight = trim ( $_REQUEST ['pw'] );

	
		if ($id && $weight) {
			$data = $this->doComputeFee ( $id, $weight);
			//dump($data);exit;
			if ($data) {
				$this->ajaxReturn ( $data, L('package_cal_result'), 1 );
			} else {
				$this->ajaxReturn ( null, L('package_parameter_error'), 0 );
			}
		} else {
			$this->ajaxReturn ( null, L('package_parameter_error'), 0 );
		}
	}
	
	//----------------------------------------------------------------------------------------
	//计算运费，服务费，保险费
	private function doComputeFee($wid, $pw) {
		if ($wid && $pw ) {
			$shippingFee = $this->doShippingFee ( $wid, $pw ); //计算运费
			$CustomFee = $this->getCustomFee ( $wid );
			/*if ($tag) {
				$InsureRate = $this->getInsureRate ();
				$InsureFee = ( $shippingFee * $InsureRate) / 100; //保险费
			} else {
				$InsureFee = 0;
			}*/
				
			$totalFee = $shippingFee +  $CustomFee ;//+ $InsureFee;
			$data ['shippingFee'] = round ( $shippingFee, 2 );
			//$data ['serviceFee'] = round ( $ServiceFeeDaigou + $ServiceFeeShipping, 2 );
			//if(floatval($data ['serviceFee']) < $this->min_serve_fee ) {$data ['serviceFee'] = $this->min_serve_fee;}
			//$data ['insureFee'] = round ( $InsureFee, 2 ); //险费
			$data ['customFee'] = round ( $CustomFee, 2 );
			$data ['totalFee'] = round ( $totalFee, 2 ); //只保留两位小数
			return $data;
		} else {
			return false;
		}
	}
	
	//----------------------------------------------------------------------------------------
	//根据重量和运输方式计算运费
	private function doShippingFee($wid, $weight) {
		$result = 0;
		if ($wid && $weight) {
			$way = $this->address->where ( "id=$wid" )->find ();
			if ($way) {
				if ($weight <= $way ['start_weight']) {
					$result = $way ['start_price'];
				} else {
					$unit = ceil ( ($weight - $way ['start_weight']) / $way ['continue_weight'] );
					$result = $way ['start_price'] + $way ['continue_price'] * $unit;
				}
			}
		}
		return $result;
	}
	
	
	//----------------------------------------------------------------------------------------
	// 取报关费
	private function getCustomFee($id) {
		if (!is_numeric($id) ) { return 8; }
		$way =  $this->address->where ( "id=$id" )->find ();
		return ($way && is_numeric($way ['customfee']) ) ? $way ['customfee'] : 8;
	}
	
	//----------------------------------------------------------------------------------------
	//保险费比例
	private function getInsureRate() {
		$InsureRate = M ( 'FinaceConfig' )->where ( "item='" . C ( 'INSURE_RATE' ) . "'" )->find ();
		return ($InsureRate && ($InsureRate ['value'] > 0)) ? $InsureRate ['value']:5;
	}
			
}
?>