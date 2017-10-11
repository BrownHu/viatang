<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 我的订单
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
class OrderAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		if ($this->user) {
			$this->redirect ( 'My/order' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 未处理
	public function wcl() {
		    $this->assign ('title','等待处理中商品-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			$this->loadProductListBySta(1,$this->user['id']);
			$this->display ();
		}	
	}
	
	//------------------------------------------------------------------------------------------------
	// 处理中
	public function clz() {
		    $this->assign ('title','处理中商品-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			$this->loadProductListBySta(2,$this->user['id']);
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 已订购
	public function ydg() {
		    $this->assign ('title','已订购商品-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			$this->loadProductListBySta(3,$this->user['id']);
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 已到货
	public function ydh() {
		    $this->assign ('title','已到货商品-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			$this->loadProductListBySta(4,$this->user['id']);
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 已入库
	public function yrk() {
		    $this->assign ('title','已入库商品-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			$this->loadProductListBySta(12,$this->user['id']);
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 历史订单
	public function history() {
		if ($this->user) {
			$this->loadProductListBySta(5,$this->user['id']);
			global $product_status_array_user;
			$this->assign ( 'productStaAry', $product_status_array_user );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 退单, 将商品返回到购物车
	public function cancle() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && ! empty ( $id ) && is_numeric($id)) {
			$entity = M ( 'Product' )->where ( "id=$id AND user_id=" . $this->user ['id'] )->find (); // 加载该商品数据
			                                                                      
			// 商品存在, 状态不为已退单, 且状态是 未处理，无货，暂时缺货，无效中的一种，才可以退单
			if ($entity && (($entity ['status'] == 1) || ($entity ['status'] == 7) || ($entity ['status'] == 8) || ($entity ['status'] == 9))) {
				//$this->writeToCart($entity);	//退到购物车					
				$this->refund ( $id ); // 退款（商品金额 + 服务费 + 运费）				                       				
				$this->refundForSupplement ( $id ); //退补款， status 7,无货时需处理补款, 无货分为：（1）未订购直接无货，（2）订购后无货				
				$this->setStatus($entity ['id'], 6, $entity['user_id']);
				$this->writeProductLog($entity ['id'], 6,  $entity['amount'], 0, '', '客户退单');
				$this->updateOrder ( $entity ['order_id'],3 ); // 商品退单后，检查是否同单商品全部已退单
			} else {
				$this->assign ( 'waitSecond', 10 );
				$this->assign ( 'jumpUrl', '/My/order.shtml' );
				$this->assign ( 'msgTitle', L('order_opration_fail') );
				$this->error ( L('order_in_process') );
			}			
		}
		$this->redirect ( 'My/order' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 删除
	public function del() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && ! empty ( $id )) {
			$this->setStatus($id, 0,  $this->user ['id']);
			$this->writeProductLog ( $id, 0, 0, 0, '', L('order_del_usr') . $this->user ['id'] );			
			$this->refund_yth ( $id ); // 进行退款处理
		}
		$this->redirect ( 'My/order' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 修改备注
	public function note() {
		$id = trim ( $_POST ['id'] );
		$note = trim ( $_POST ['note'] );
		if ($this->user && ! empty ( $id ) && !empty($note)) {
			M('Product')->execute('UPDATE product SET last_update='.time() .",custmer_note='".mysql_escape_string(htmlspecialchars(remove_xss($note)))."' WHERE id=".$id);
		}
		$this->display ( 'Public/result' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 加载备注数据
	public function load() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && ! empty ( $id )) {
			$entity = M ( 'Product' )->where ( 'id=' . $id )->find ();
			$this->assign ( 'product', $entity );
			$this->display ( 'My/note' );
		}		
	}
	
	//------------------------------------------------------------------------------------------------
	// 确认补款
	public function supplement() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && ! empty ( $id )) {
			$entity = M ( 'Product' )->where ( "id=$id AND user_id=" . $this->user ['id'] )->find ();
			if ($entity) {
				$FinanceDAO = D ( 'Finance' );
				$finance = $FinanceDAO->finace ( $entity ['user_id'] );
				$need_supplement = $entity ['supplement_fee'];
				
				if ($finance) {
					if ($finance ['money'] >= $need_supplement) {
						$remark = L('order_need_pay') . $need_supplement;
						$money_use = 0 - $need_supplement;
						$this->writeFinaceLog ( $this->user ['id'], $this->user ['login_name'], $entity ['order_id'], $entity ['id'], $money_use, $finance['money'], $finance['rebate'], $finance ['point'], $remark, 204, 0 );
							
						$finance ['money'] = $finance ['money'] - $need_supplement;
						$finance ['consumption_total'] = $finance ['consumption_total'] + $need_supplement;
						$finance ['last_update'] = time ();
						$FinanceDAO->updateInfo ( $finance );
						
						// 置补款信息为已补款
						$this->updateSupplement($entity ['supplement_id'],2);
						$this->updateProductSupplement ( $entity ['supplement_id'] ); // 同一补款的订单商品状态改为处理中，并记录商品状态变化日志
						$this->redirect ( 'My/order' );
					} else {
						$this->assign ( 'jumpUrl', '/My/pay.shtml' );
						$this->assign ( 'waitSecond', 30 );
						$this->assign ( 'msgTitle', L('order_opration_fail') );
						$this->error ( L('order_moneny_not_enough') );
					}					
				} else {
					$this->assign ( 'jumpUrl', '/My/pay.shtml' );
					$this->assign ( 'waitSecond', 30 );
					$this->assign ( 'msgTitle', L('order_opration_fail') );
					$this->error ( L('order_moneny_not_enough') );
				}
			} else {
				$this->assign ( 'jumpUrl', '/My/pay.html' );
				$this->assign ( 'waitSecond', 30 );
				$this->assign ( 'msgTitle', L('order_opration_fail') );
				$this->error ( L('order_op_fail') );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 取消补款
	public function denysup() {
		$id = trim ( $_GET ['id'] );
		if ($this->user && ! empty ( $id )) {
			$entity = M ( 'Product' )->where ( "id=$id AND user_id=" . $this->user ['id'] )->find ();
			if ($entity) {
				$this->setStatus($id, 9, $this->user['id']);//置为无效订单
				$this->writeProductLog ( $id, 9, 0, 0, '', L('order_user_cancel') . $id );
				$this->updateSupplement($entity ['supplement_id'] ,3);//标记为拒绝补款
			}
		}
		$this->redirect ( 'My/order' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 加入送货车
	public function addtocart() {
		$IdAry = $_POST ['id'];

		if (! empty ( $IdAry ) && is_array($IdAry) && (count ( $IdAry ) > 0) && $this->user) {			
			$Idlst = implode ( ',', $IdAry );
			$DataList = M ( 'Product' )->where ( "id in ($Idlst) AND user_id=".$this->user['id'] )->select ();
			foreach ( $DataList as $item ) {						
				$this->writeShippingCart($item);
				$this->setStatus($item ['id'],17,$item['user_id']);
				$this->writeProductLog($item ['id'],17,$item ['amount'],0,'','客户加入送货车');
			}
			$shiping_count = M("ShippingCart")->where("user_id=".$this->user['id'])->count();
			$_SESSION['ulowi_shipping_count'] = $shiping_count;
			$this->ajaxreturn($Idlst,$shiping_count,1);			
			//$this->redirect ( 'yrk' );
		}
	}
		
	//------------------------------------------------------------------------------------------------
	// 订单详情，用于财务记录
	public function detail() {
		$this->assign ('title','订单商品详情-订单管理-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		$oid = trim ( $_GET ['id'] );
		$type = trim ( $_GET ['t'] );
		$this->assign('type',$type);//用于判断是退单还是结算
		if ($this->user && $oid) {
			$DAO = M ( 'Product' );
			if ($type == 1) {
				$DataList = $DAO->where ( "order_id=$oid AND user_id=" . $this->user ['id'] )->select ();
				$order = M('Orders')->where('id='.$oid)->find();
			} elseif ($type == 0) {//退单
				$DataList = $DAO->where ( "id=$oid AND user_id=" . $this->user ['id'] )->select ();
				$item  = M('Product')->where('id='.$oid)->find();
				$order = M('Orders')->where('id='.$item['order_id'])->find();
				$count =  M('Product')->where('status <> 6 AND status <> 0 AND order='.$item['order_id'])->count();
				$this->assign('same_order_count',$count);
			}
			
			$this->assign('order',$order);
			$this->assign ( 'DataList', $DataList );			
			global $product_status_array_user;
			$this->assign ( 'productStaAry', $product_status_array_user );
		}
		$this->display ();
	}
	
	// -------------------------------------------------------------------------------------------------------------
	// 以下为内部函数
	
	//------------------------------------------------------------------------------------------------
	//写入送货车
	private function writeShippingCart($item){		
		if($item){
			$DAO = M ( 'ShippingCart' );
			$count = $DAO->where ( 'product_id=' . $item ['id'] . ' AND type=1' )->count ();
			if($count == 0){
				$entity ['user_id'] 			= $item['user_id'];
				$entity ['user_name'] 	= mysql_escape_string($item['user_name']);
				$entity ['product_id'] 	= $item ['id'];
				$entity ['type'] 				= 1; // 1：代购，2：自助购
				$entity ['title'] 				= mysql_escape_string($item ['title']);
				$entity ['url'] 				= mysql_escape_string($item ['url']);
				$entity ['img'] 				= $item ['image'];
				$entity ['count'] 			= $item ['amount'];
				$entity ['weight'] 			= $item ['weight'];
				$entity ['total_weight'] 	= $item ['weight'] * $item ['amount'];
				$entity ['product_fee'] 	= $item ['price1'] * $item ['amount'];
				$entity ['service_rate'] 	= $item ['service_rate'];
				$entity ['service_fee'] 	= $item ['service_fee'];
				$entity ['create_at'] 		= time();
				return $DAO->data ( $entity )->add ();
			}else{
				return false;
			}			
		}else{
			return false;
		}		
	}
	
	//------------------------------------------------------------------------------------------------
	//置商品状态
	private function setStatus($pid,$sta,$uid){
		if($pid && $uid){
			M('Product')->execute("UPDATE product SET status=$sta,last_update=".time()."  WHERE id=$pid  AND user_id=$uid");
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//置补款状态
	private function updateSupplement($id,$sta){
		if($id && is_numeric($id)){
			M ( 'Supplement' )->execute('UPDATE supplement SET status='.$sta .',last_update='.time().'  WHERE id='.$id);
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//写购物车
	private function writeToCart($item){
		if($item){
			$data ['user_id'] 		 = $item ['user_id'];
			$data ['user_name'] 	 = $item ['user_name'];
			$data ['title'] 			 = $item ['title'];
			$data ['url'] 				 = $item ['url'];
			$data ['price'] 			 = $item ['price1'];
			$data ['amount'] 		 = $item ['amount'];
			$data ['shipping_fee'] = $item ['shipping_fee'];
			$data ['total'] 			 = $item ['total'];
			$data ['seller'] 			 = $item ['seller'];
			$data ['note'] 			 = $item ['custmer_note'];
			$data ['thumb'] 		 = $item ['thumb'];
			$data ['image'] 			 = $item ['image'];
			$data ['create_time']  = time ();
			return M ( 'ShopingCart' )->data ( $data )->add (); // 将商品返回购物车
		}else{
			return false;
		}
	}

	//------------------------------------------------------------------------------------------------
	 //* 记财务变更记录，这里只记订单提交产生的消费日志
	private function writeFinaceLog($uid, $unam, $oid, $pid, $money, $mnybfr, $rbtbfr, $pntbfr, $remark, $typ, $reb=0) {
		if($uid){
			$data ['user_id'] 		= $uid;
			$data ['user_name'] 	= $unam;
			$data ['type_id'] 		= $typ; // 商品退单，见business.inc.php定义
			$data ['pay_id'] 			= 0;
			$data ['order_id'] 		= $oid; // 这里记 订单号：商品号
			$data ['package_id'] 	= 0;
			$data ['product_id'] 	= $pid;
			$data ['pointlog_id'] 	= 0;
		
			$data ['chagne_total'] 	= $money;
			$data ['money'] 		   	= $money;
			$data ['money_before'] = $mnybfr;
			$data ['money_after'] 	= $mnybfr + $money; // 这里是退单，所以全记为加
			$data ['rebate'] 			= $reb;
			$data ['rebate_before'] 	= $rbtbfr;
			$data ['rebate_after'] 	= $rbtbfr + $reb;
			$data ['point'] 				= 0;
			$data ['point_before'] 	= $pntbfr;
			$data ['point_after'] 		= $pntbfr;
		
			//这里是退单无须记相应的抵扣券
			$data['ticket_id']   =0;
			$data['code']   ='';
			$data['use_ticket']   = 0;
			$data['ticket_amount']   =0;
			$data['ticket_mianzhi']     = 0;
		
			$data ['remark'] = $remark;
			$data ['create_time'] = time ();

			return M ( 'FinanceLog' )->data ( $data )->add ();
		}else{
			return false;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 已退货退款,指采购后又退货
	private function refund_yth($id) {
		if(empty($id)){return;}
		$entity = M ( 'Product' )->where ( "id=$id" )->find (); // 加载该商品数据		
		if ($entity) {
			$refund = $entity ['refund'];
			$remark = '退货：' . $entity ['reason'];
			
			$FinanceDAO = D ( 'Finance' );
			$finance = $FinanceDAO->finace ( $entity ['user_id'] );
			if ($finance) {
				$this->writeFinaceLog ( $entity ['user_id'], $entity ['user_name'], $entity ['order_id'], $entity ['id'], $refund, $finance ['money'], $finance ['rebate'], $finance ['point'], $remark, 202, 0 );
				$finance ['money'] = $finance ['money'] + $refund; // 退还商品金额
				$finance ['consumption_total'] = $finance ['consumption_total'] - $refund; // 扣消费累计
				$finance ['last_update'] = time ();
				$FinanceDAO->updateInfo ( $finance ); // 更新余额				
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 退指定id商品的款项
	private function refund($id) {
		$DAO = M ( 'Product' );
		$entity = $DAO->where ( "id=$id" )->find (); // 加载该商品数据
		$order = M('Orders')->where('id='.$entity['order_id'])->find();//加载商品对应的订单
		$FinanceDAO = D ( 'Finance' );
		$finance = $FinanceDAO->finace ( $entity ['user_id'] );
		
		if ($entity && $order && $finance) {
			   //Log::write($finance['money']);
				$refund = 0;
				$discount = 0;
				$entity ['service_fee'] = 0;
				
				//计算商品金额
				$refund = floatval ( $entity ['price1'] ) * intval ( $entity ['amount'] );
				$rerund1 = $entity['refund']; 
								
				//计算运费
				if ($entity ['shipping_fee'] > 0) { // 该商品存在运费 , 不是退单也不是删除的同单同卖家
					$same_order_seller_count = $DAO->where ( 'status<>6 AND status<>0 AND order_id=' . $entity ['order_id'] . " AND seller='" . $entity ['seller'] . "'" )->count (); // 统计同订单是否还订购了别的商品
					if ($same_order_seller_count == 1) {
						$shipping = $entity ['shipping_fee'];
						$refund = $refund + $shipping;
					} else {
						$shipping = 0;
						$remark .= L('order_same_order');
					}
				}
				
				//Log::write('退款金额1:'.$rerund1. ', 计算出的退款金额:'.$refund);
				//Log::write('订单总金额：'.$order['total']);
				$refund = number_format($refund,2);
				//Log::write('退款金额1:'.$rerund1. ', 计算出的退款金额:'.$refund);
				if( $refund >0  ){
					Log::write('开始退款'.$refund);
					$remark .= L("order_price") . $entity ['price1'] . L('order_count') . $entity ['amount'] . L('order_shipping_fee') . $shipping.L('order_service_fee').$entity['service_fee'];
					$this->writeFinaceLog ( $entity ['user_id'], $entity ['user_name'], $entity ['order_id'], $entity ['id'], $refund, $finance['money'], $finance['rebate'], $finance['point'], $remark, 202, 0 );
					
					$finance['money'] = $finance['money']  + $refund;
					$finance ['consumption_total'] = $finance ['consumption_total'] - $refund; // 扣消费累计
					$finance ['last_update'] = time ();													
					$FinanceDAO->updateInfo ( $finance ); // 更新余额
					
				}   
		}else{
			Log::write('加载财务数据出错');
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//加载用户的指定状态订单
	private function loadProductListBySta($sta,$uid){
		if($uid && $sta && is_numeric($uid) && is_numeric($sta)){
			$DAO = M ( 'Product' );
			$count = $DAO->where ( "status=$sta AND user_id=$uid" )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( "status=$sta AND user_id=$uid" )->limit ( $p->firstRow . ',' . $p->listRows )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//更新订单抵扣退款
	private function updateOrderChanageAmt($order){
		if($order){
			M('Orders')->execute('Update orders SET change_amount='.$order['change_amount'] . '  WHERE id='.$order['id']);
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 记商品变更日志
	private function writeProductLog($id, $status, $amount, $adminid, $adminm, $remark) {
		$data ['product_id'] = $id;
		$data ['status'] = $status;
		$data ['amount'] = $amount;
		$data ['admin_id'] = $adminid;
		$data ['admin_name'] = $adminm;
		$data ['remark'] = $remark;
		$data ['create_time'] = time ();
		M ( 'ProductLog' )->data ( $data )->add ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 退补款金额
	private function refundForSupplement($pid) {	
		$ProductDAO = M ( 'Product' );
		$product = $ProductDAO->where ( "id=$pid" )->find ();
		
		if ($product) {//只退不存在同补款订单的商品
			$count = $ProductDAO->where ( 'status<>6 AND status<>0 AND order_id=' . $product ['order_id'] . " AND seller='" . $product ['seller'] . "' AND supplement_id>0 " )->count ();
			if ($count == 1) {
				$refund = $this->computeSupplementSum($product);
				$FinanceDAO = D ( 'Finance' );
				$finance = $FinanceDAO->where ( 'user_id=' . $product ['user_id'] )->find ();
				if($finance){
					$this->writeFinaceLog ( $product ['user_id'], $product ['user_name'], $product ['order_id'], $product ['id'], $refund, $finance ['money'], $finance ['rebate'], $finance ['point'], '退补款', 202, 0 );
					$finance ['money'] = $finance ['money'] + $refund;
					$finance ['consumption_total'] = $finance ['consumption_total'] - $refund;
					$finance ['last_update'] = time ();
					$FinanceDAO->updateInfo ( $finance ); // 更新余额
				}
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//统计已补款金额的和
	private function computeSupplementSum($product){
		if($product){
			$order_seller = $product ['order_id'] . '_' . $product ['id'];
			return M ( 'Supplement' )->where ( "order_seller='$order_seller' AND status=2" )->sum ( 'need_fund' );//1：未处理，2：已补款，3：拒绝补款，只统计已经补款成功的
		}else{
			return 0;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 更新补款商品状态为处理中
	private function updateProductSupplement($supplement_id) {
		if (! empty ( $supplement_id )) {
			$DAO = M ( 'Product' );
			$DataList = $DAO->where ( "supplement_id=$supplement_id" )->select ();
			$last_update = time ();
			foreach ( $DataList as $item ) {
				$DAO->execute("UPDATE product SET status=2,supplement_id=0,supplement_tag=1,last_update=$last_update WHERE id=".$item ['id']);
				$this->writeProductLog ( $item ['id'], 2, 0, 0, '', L('order_user_pay').'uid:' . $item ['user_id'] );
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 若同订单商品全部为已退单，则取消订单
	private function updateOrder($oid,$sta) {
		if ( $oid ) {
			$count = M ( 'Product' )->where ( 'order_id=' . $oid . ' AND status != 6' )->count ();
			if ($count == 0) {
				M ( 'Orders' )->execute('UPDATE orders SET status='.$sta.'  WHERE id='.$oid);
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'My/order' );
	}
}
?>