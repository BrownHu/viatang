<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 购物车
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );

class CartAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao =  M ( 'ShopingCart' ); 
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		    $this->assign ('title','我的购物车-代购商品管理-viatang.com');
	        $this->assign ('keywords','代购商品管理,购物车清单,购物车结算，商品管理，加入购物车，中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购购物车商品管理，购物车管理，购物车清单,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->_list('user_id=' . $this->user ['id'],'seller,shipping_fee', 1000);				 
			$count = count($this->view->get('list'));
			$total_fee= 0;
			$_list = $this->view->get('list');
			$_seller = '';
			
			foreach ($_list as $_r){
				$total_fee = $total_fee + ($_r['price'] * $_r['amount']);
					
				if($_seller == ''){
					$total_fee = $total_fee + $_r['shipping_fee'];
				}if( ($_seller != '') && ($_seller != $_r['seller']) ){
					$total_fee = $total_fee + $_r['shipping_fee']; //加上运费
				}
				
				$_seller = $_r['seller'];
			}
			$this->assign('cart_total_fee',$total_fee);			
			$this->assign ( 'count', $count );
			$this->display ();
		} 
	}

	//---------------------优惠券读取---------------------------------------------------------------------------
	public function getcartinfo(){
		$list = $this->dao->where('user_id=' . $this->user ['id'])->order('seller,shipping_fee')->select();
		$results = array();
		foreach($list as $info){
			$results[] = array(
				'id' => $info['id'],
				'title' => $info['title'],
				'url' => $info['url'],
				'price' => $info['price'],
				'amount' => $info['amount'],
				'image' => $info['image'],
			);
		}
		echo json_encode($results);exit;
	}

	//---------------------优惠券使用判断---------------------------------------------------------------------------
	public function delcartinfo(){
		$results = array('code' => 1,'msg' => '');
		$id = $_GET['id'];
		if($this->dao && $this->user && !empty($id)){
			$ret = $this->dao->where("id = ".$id." AND user_id=" . $this->user ['id'] )->delete();
			if(is_numeric($ret) || $ret != false) $results['code'] = 0;;
		}
		$this->success('操作成功');
		//echo json_encode($results);exit;
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		if ($this->user && $_POST) {
			$this->_deleteMore();
			$this->redirect ( 'index' );
		}	
	}
	
	//------------------------------------------------------------------------------------------------
	public function load() {
		if ($this->user ) {
			$this->_load(' and user_id='.$this->user['id']);			
		} 
		$this->display ( 'edit' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function update() {
		$id = trim ( $_POST ['id'] );
		if ($this->user  && $this->dao &&  $id  && is_numeric ( $id )) {
			$product ['url'] 						= trim ( $_POST ['productUrl'] );
			$product ['title'] 					= trim ( $_POST ['productName'] );
			$product ['price'] 					= floatval(trim ( $_POST ['productPrice'] ));
			$product ['shipping_fee'] 		= floatval( trim ( $_POST ['productSendPrice'] ));
			$product ['amount'] 				= intval(trim ( $_POST ['productNum'] ));
			$product ['note'] 					= trim ( $_POST ['productRemark'] );
			$product ['total'] 					= floatval ( trim ( $_POST ['productPrice'] ) ) * ( int ) (trim ( $_POST ['productNum'] )) + floatval ( trim ( $_POST ['productSendPrice'] ) );
			$product ['reserv_package'] 	= intval(trim ( $_POST ['reserv_package'] ));
			$product ['reserv_brand'] 		= intval(trim ( $_POST ['reserv_brand'] ));
			$product ['is_emergency'] 	= intval(trim ( $_POST ['is_emergency'] ));
			
			$this->dao->where ( 'user_id=' . $this->user ['id'] . ' AND id=' . $id )->save ( $product );
			$this->display ( 'Public/result' );
		} else {
			$this->redirect ( 'index' );
		}
	}
	
	public function update_count(){
		$id = trim ( $_GET ['id'] );
		if ($this->user  && $this->dao &&  $id  && is_numeric ( $id )) {
			$item = $this->dao->where('id='.$id)->find();
			if($item){
				$total = $item['price'] * $_GET['c'] + $item['shipping_fee'];
				$this->dao->execute('update '. $this->dao->getTableName(). ' set amount='.$_GET['c']. ',total='. $total.' where id='.$id . ' and user_id='.$this->user['id']);
				echo $total;
			}else{
				echo '0';
			}
		}else{
			echo '0';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 结算前进行费用估算
	public function pcheck() {
		    $this->assign ('title','购物车结算-viatang.com');
	        $this->assign ('keywords','代购商品管理,购物车清单,购物车结算，中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购购物车商品管理，购物车清单,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		$IdAry = $_POST ['id'];
		if ( $this->user && $IdAry && is_array($IdAry) && (count ( $IdAry ) > 0)) {
			$balance = $this->getUserBalance ( $this->user ['id'] );
			$this->assign ( 'balance', $balance );
			
			// 取购物车选中商品信息
			$Idlst = implode ( ',', $IdAry );
			$DataList = M ( 'ShopingCart' )->where ( "id in ($Idlst) AND user_id=" . $this->user ['id'] )->order ( 'seller,shipping_fee' )->select ();

			$sellers = array();
			foreach($DataList as $Data){
				if(!in_array($Data['seller'],$sellers)) $sellers[] = $Data['seller'];
			}
			//if(count($sellers)<=1) $serveFee = 10;
			//else if(count($sellers)==2) $serveFee = 18;
			//else if(count($sellers)==3) $serveFee = 24;
			//else $serveFee=0 //= (count($sellers)-2)*6+18;
			$serveFee=0;

			//$serveFee = $this->computeServeFee ( $Idlst, $this->user ['id'] );
			$this->assign ( 'serve_fee', $serveFee );
			$this->assign ( 'item_list', $DataList );
			$this->assign ( 'id_lst', $Idlst ); // 将要结算的商品id列表回传到页面
			$this->assign ( 'uid', $this->user ['id'] );
			
			$product_fee = $this->computeProductFee ( $Idlst, $this->user ['id'] ); // 商品的费用		                                                              
			// 列出当前用户的有效代金券
			$Djq_list = M ( 'Ticket' )->where ( 'user_id=' . $this->user ['id'] . ' and term>' . time () . ' and state=1 and use_type=1 and use_amount<=' . $product_fee )->select ();
			$this->assign ( 'Djq_list', $Djq_list );
			
			$this->display ( 'checkout' );
		} else {
			$this->redirect ( 'index' );
		}
	}
	
	// -----------------------------------------------------------------------------------------------
	// 结算
	// 2013.5.13 去掉折扣帐户抵服务费
	public function checkout() {
		    $this->assign ('title','商品结算成功-viatang.com');
	        $this->assign ('keywords','代购商品管理,购物车清单,购物车结算，paypal支付，外币支付，中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购购物车商品管理，购物车清单,提交购物车商品管理，支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		$ids = isset ( $_POST ['ids'] ) ? trim ( $_POST ['ids'] ) : false;
		if ($this->user &&  $ids && (strlen ( $ids ) > 0)  ) {			
			$uid = $this->user ['id'];
			$un  = $this->user ['login_name'];
			$pre_balance = $this->getUserBalance ( $uid ); // 取未结算时的帐户余额
			$pre_order_fee = $this->computeOrderFee ( $ids, $uid ); // 要结算的商品总金额(商品+运费+服务费)

			$ticket 	= false;		
			$mianzhi = 0;
			if (isset ( $_POST ['ticket_code'] ) && trim($_POST ['ticket_code'])  != "") {
				$ticket = $this->getVoucher($uid, trim ( $_POST ['ticket_code'] ));
				$mianzhi = ($ticket != false)? floatval($ticket ['mianzhi']):0;
			}

			// 根据卖家分别生成订单			
			if ($pre_balance >= $pre_order_fee) {
				$seller_list = $this->getSellerListById ( $ids,$uid ); // 这里有可能卖家信息为空,即用户手工录入商品时，卖家信息为空
				
				if ($seller_list && (is_array ( $seller_list ))) {
					$all_write = true;
					//$service_rate =  $this->getServiceRate ();
					foreach ( $seller_list as $i => $item ) {
						$pids = $this->getProductListBySeller ( $item ['seller'], $uid, $ids ); // 取指定卖家商品id列表,						
						
						$product_fee  = $this->computeProductFee ( $pids, $uid ); // 商品金额合计
						$shipping_fee = $this->getMaxShipping($uid,$pids,$item ['seller']) ; //$this->computeShppingFee ( $pids, $uid ); // 运费合计，20130605 by stone 相同卖家取最大运费作为订单的运费
						$serve_fee	 = 0.0;
				
						$discount = 0;
						$discount_way = 0;
					/* 	if ($ticket) {
							$discount  = ($serve_fee <=  $mianzhi ) ? $serve_fee :  $mianzhi ; //本次抵扣的金额, 只取小的，即抵扣的金额
							$serve_fee = ($serve_fee <=  $mianzhi ) ? 0 :  $serve_fee - $mianzhi ; 
							$mianzhi   = ($serve_fee <=  $mianzhi ) ? $mianzhi - $serve_fee  : 0 ; 							
							$discount_way = ($discount > 0)?2:0;
						} */
						
						
						$order_fee = round ( floatval($product_fee + $shipping_fee + $serve_fee  ), 2 ) ; // 订单金额合计
						
						if (floatval($order_fee) <= 0) {
							$this->assign ( 'jumpUrl', '/Cart/index.html' );
							$this->assign ( 'waitSecond', 30 );
							$this->error ( '部份订单金额有误，没有结算,请检查后重试，若问题一直出现，请联系客服!' );
							$all_write = false;
							break;
						}

						$balance = $this->getUserBalance ( $uid ); // 取当前余额
						$finance = $this->getUserFinace($uid);
						if (($balance >= $order_fee) && $finance) { 					
							if($this->updateUserBalance ( $uid, $un, $order_fee)){
								$product_count = $this->countProduct ( $pids, $uid );
								//$oid = $this->writeOrder ( $uid, $un, $product_count, $item ['seller'], $product_fee, $shipping_fee, 1, $order_fee, $discount, $ticket,$serveFee );//2013-06-06订单记卖家名称
								$oid = $this->writeOrder ( $uid, $un, $product_count, $item ['seller'], $product_fee, $shipping_fee, 1, $order_fee, $discount, $ticket );//2013-06-06订单记卖家名称
								$this->writeProduct ( $oid, $pids, $uid, $un ); 
								$this->writeFinaceLog ( $uid, $un, $oid, $order_fee, $finance['money'], 0, $finance['rebate'], 0, '购物车结算',$discount,$discount_way, $ticket); // 记财务变更记录
							}else{
								$all_write = false;
							}							
						} else {
							$all_write = false;
							break;
						}
					}
					if($ticket != false){$this->updateVoucher($uid,$ticket['code']);}
					$this->countCart ($uid); // 重新统计购物车
					if ($all_write) {
						$this->display ( 'success' ); // 提交成功，提交是否继续购物
					} else {
						$this->assign ( 'jumpUrl', '/My/pay.html' );
						$this->assign ( 'waitSecond', 30 );
						$this->error ( '部份商品没有结算成功没有扣款，请检查购物车！' );
					}
				} else { // 这里处理 seller 为空的订单
					$this->assign ( 'jumpUrl', '/Cart/index.html' );
					$this->assign ( 'waitSecond', 30 );
					$this->error ( '加载购物车数据出错，无法结算，请联系客服!' );
				}
			} else { // 财务信息为空提示充值
				$this->assign ( 'jumpUrl', '/My/pay.shtml' );
				$this->assign ( 'waitSecond', 30 );
				$this->error ( '您的帐户余额不足(code:PAY_002)，请充值后重新提交结算！' );
			}
		} else { // 购物车为空，直接回购物车首页，不作结算处理
			$this->redirect ( 'index' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//验证
	public function verifiDiscode(){
		if ($this->user && isset($_GET['c'])) {
			$code = trim($_GET['c']) ;
			$voucher = $this->getVoucher($this->user['id'],$code);
			if($voucher){
				echo $voucher['mianzhi'];
			}else{
				echo '0';
			}
		}else{
			echo '0';
		}
	}
		
  // ----------------------------------------------------------------------------------------------------
  // 以下为内部方法
  //------------------------------------------------------------------------------------------------
	
  //------------------------------------------------------------------------------------------------	
  private function updateVoucher($uid,$code){
  	if($uid && (trim($code) != '') ) {
  		M ( 'Ticket' )->execute ( "UPDATE ticket SET state=2, use_time=" . time () . "  WHERE state=1 AND  code='$code'");
  	}
  }	
	
  //------------------------------------------------------------------------------------------------
   private function getVoucher($uid,$code){
 		return ($code && $uid && is_numeric($uid)) ? M('Ticket')->where('user_id='.$uid ."  AND state=1 AND code='$code'")->find() : false;
   }
	
   //------------------------------------------------------------------------------------------------
	// 计算订单金额
	private function computeOrderFee($ids, $uid) {
		if($ids && $uid){
			$product_fee = $this->computeProductFee ( $ids, $uid ); 	// 商品金额合计
			$shipping_fee = $this->computeShppingFee ( $ids, $uid ); // 运费合计
			$serveFee = 0;// 2014-11-08 by stone 提交订单不扣服务费 ($product_fee * $this->getServiceRate ()) / 100; // 服务费
			$order_fee = $product_fee + $shipping_fee + $serveFee; // 订单金额合计
			return round ( $order_fee, 2 );
		}else{
			return 0;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function computeServeFee($ids, $uid) {
		$result = 0;
		if($ids && $uid){
			$result = floatval(( $this->computeProductFee ( $ids, $uid ) *  $this->getServiceRate ()) / 100);
			$result = ($result <= 0) ? 0.1 : $result; // 这里确保服务费不能为0
		}
		return round ( $result, 2 );
	}
	
	//------------------------------------------------------------------------------------------------
	// 更新财务数据，这里结算，所以全部为扣款动作
	private function updateUserBalance($uid, $un, $order_fee) {
		$result = false;
		$FinaceDAO = D ( 'Finance' );
		$finance = $FinaceDAO->finace ( $uid );
		
		if ($finance && (floatval ( $order_fee ) > 0) && (floatval ( $finance ['money'] ) >= floatval ( $order_fee ))) {
			$finance ['money'] = floatval( $finance ['money']) - floatval ( $order_fee );
			$finance ['consumption_total'] = floatval($finance ['consumption_total']) + floatval ( $order_fee ); // 消费总额				
			$FinaceDAO->updateInfo ( $finance ); // 更新财务数据
			$result = true;
		}
		
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 根据物品ID列表，取得卖家列表
	private function getSellerListById($ids,$uid) {
		return ($uid && $ids  && (strlen ( $ids ) > 0)) ?  M ( 'ShopingCart' )->field ( 'seller' )->where ( "id in ($ids) AND user_id=$uid  AND seller <> '' " )->Distinct ( true )->select () : array();
	}
	
	//------------------------------------------------------------------------------------------------
	// 取卖家的商品id列表
	private function getProductListBySeller($sellser, $uid, $ids) {
		$result = '';
		$ids = ltrim ( $ids, ',' );
		$ids = rtrim ( $ids, ',' );
		
		if (! empty ( $sellser ) && ! empty ( $uid ) && ! empty ( $ids )) {
			$sellser = $sellser ;
			$DataList = M ( 'ShopingCart' )->field ( 'id' )->where ( "user_id=$uid AND seller='$sellser' AND id in ($ids)" )->select ();
			foreach ( $DataList as  $item ) {$result .= ',' . $item ['id']; }
			$result = ltrim ( $result, ',' );
			$result = rtrim ( $result, ',' );
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 取用户现金余额
	private function getUserBalance($uid) {
		$result = 0;
		if (! is_null ( $uid ) && is_numeric ( $uid )) {
			$finance =  D ( 'Finance' )->finace ( $uid );
			$result = (!empty($finance)) ? $finance ['money'] : 0;		
		}
		return floatval ($result);
	}
	
	//------------------------------------------------------------------------------------------------
	private function getUserFinace($uid){
		return ($uid && is_numeric($uid)) ? D ( 'Finance' )->finace ( $uid ) : false;
	}
	
	//------------------------------------------------------------------------------------------------
	// 取折扣余额
	private function getUserRebate($uid) {
		$result = 0;
		if (! is_null ( $uid ) && is_numeric ( $uid )) {
			$finance = D ( 'Finance' )->finace ( $uid );
			$result = (!empty($finance)) ? $finance ['rebate'] : 0 ;
		}
		return floatval ( $result);
	}
	
	//------------------------------------------------------------------------------------------------
	// 统计商品数量
	private function countProduct($ids, $uid) {
		$ids = ltrim ( $ids, ',' );
		$ids = rtrim ( $ids, ',' );
		
		return  ((strlen ( $ids ) > 0) && $uid ) ? M ( 'ShopingCart' )->where ( "id in ($ids) AND user_id=$uid" )->sum ( 'amount' ) : 1;
	}
	
	//------------------------------------------------------------------------------------------------
	// 计算商品总价
	private function computeProductFee($ids, $uid) {
		$result = 0;
		$ids = ltrim ( $ids, ',' );
		$ids = rtrim ( $ids, ',' );
		
		if ((strlen ( $ids ) > 0) && ! empty ( $uid )) {
			$dataList = M ( 'ShopingCart' )->where ( "id in ($ids) AND user_id=$uid" )->select ();
			foreach($dataList as $item) {
				$result = $result + $item['price'] * $item['amount'];
			}
		}
		
		return floatval($result);
	}

	//------------------------------------------------------------------------------------------------
	// 计算运费总价
	private function computeShppingFee($ids, $uid) {
		$result = 0;
		$ids = ltrim ( $ids, ',' );
		$ids = rtrim ( $ids, ',' );
		
		if ($ids && (strlen ( $ids ) > 0) && $uid ) {
			$sellerList = $this->getSellerListById($ids, $uid);
			foreach ($sellerList as $seller){
				$result =$result + $this->getSellerShipping($uid, $seller['seller']);				
			}		
		}
		
		return floatval($result);
	}

	//------------------------------------------------------------------------------------------------
	// * 产生订单, 返回新生成的订单编号
	/*private function writeOrder($uid, $un, $num, $seller, $pfee, $sfee, $tax, $total,$discount=0, $vouncher,$service_fee=0) {
		if($uid && $un && $seller && $total && (floatval($total) >0)){
			$data ['user_id'] 				= $uid;
			$data ['user_name'] 			= $un;
			$data ['product_num'] 		= $num;
			$data ['content'] 				= mysql_escape_string(remove_xss($seller)); //订单对应的卖家
			$data ['product_fee'] 		= floatval($pfee);
			$data ['shipping_fee'] 		= floatval($sfee);
			$data ['tax'] 						= $tax;
			$data ['total'] 					= floatval($total);
			$data ['total_after_tax'] 		= floatval ( $total ) * floatval ( $tax ); ;
			$data ['status'] 					= 1; // 未处理
			$data ['service_rate']			= 0;//$this->getServiceRate();
			$data['service_fee']			= $service_fee;//floatval( ($pfee *  $this->getServiceRate()) / 100 ); //应收取的服务费
			$data ['create_time'] 			= time ();
			$data ['last_update'] 			= time ();
		
			$data ['ticket_id'] 				= ($vouncher != false)?$vouncher['id']:0;
			$data ['code'] 					= ($vouncher != false)?mysql_escape_string(remove_xss($vouncher['code'])):0 ;
			$data ['use_ticket'] 			= ($vouncher != false)?1:0;
			$data ['ticket_amount'] 	= $discount;//抵扣金额 
			$data ['ticket_mianzhi'] 	= ($vouncher != false)?$vouncher['mianzhi']:0;
			$data ['change_amount'] 	= $discount; // 该订单抵扣的金额 - 退单商品服务费 （刚开始退单商品不存在） 
		
			return M ( 'Orders' )->data ( $data )->add ();					
		}else{
			return false;
		}
	}*/
	
		//------------------------------------------------------------------------------------------------
	// * 产生订单, 返回新生成的订单编号
	private function writeOrder($uid, $un, $num, $seller, $pfee, $sfee, $tax, $total,$discount=0, $vouncher) {
		if($uid && $un && $seller && $total && (floatval($total) >0)){
			$data ['user_id'] 				= $uid;
			$data ['user_name'] 			= $un;
			$data ['product_num'] 		= $num;
			$data ['content'] 				= $seller; //订单对应的卖家
			$data ['product_fee'] 		= floatval($pfee);
			$data ['shipping_fee'] 		= floatval($sfee);
			$data ['tax'] 						= $tax;
			$data ['total'] 					= floatval($total);
			$data ['total_after_tax'] 		= floatval ( $total ) * floatval ( $tax ); ;
			$data ['status'] 					= 1; // 未处理
			$data ['service_rate']			= $this->getServiceRate();	
			$data['service_fee']			= floatval( ($pfee *  $this->getServiceRate()) / 100 ); //应收取的服务费
			$data ['create_time'] 			= time ();
			$data ['last_update'] 			= time ();
		
			$data ['ticket_id'] 				= ($vouncher != false)?$vouncher['id']:0;
			$data ['code'] 					= ($vouncher != false)?$vouncher['code']:0 ;
			$data ['use_ticket'] 			= ($vouncher != false)?1:0;
			$data ['ticket_amount'] 	= $discount;//抵扣金额 
			$data ['ticket_mianzhi'] 	= ($vouncher != false)?$vouncher['mianzhi']:0;
			$data ['change_amount'] 	= $discount; // 该订单抵扣的金额 - 退单商品服务费 （刚开始退单商品不存在） 
		
			return M ( 'Orders' )->data ( $data )->add ();					
		}else{
			return false;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//同卖家同订单最大运费
	private function getMaxShipping($uid,$ids,$seller){
		return ($uid && is_numeric($uid) && ($seller != '') ) ? M ( 'ShopingCart' )->where ( "id in ($ids) AND user_id=$uid  AND seller='$seller'" )->max ( 'shipping_fee' ) : 0; 
	}
	
	//------------------------------------------------------------------------------------------------
	//取指定卖家运费，同卖家同订单只取最大运费
	private function getSellerShipping($uid,$seller){
		return ($uid && is_numeric($uid) && ($seller != '') ) ? M( 'ShopingCart' )->where ( "user_id=$uid  AND seller='".$seller."'" )->max ( 'shipping_fee' ) : 0;
	}
	
	//------------------------------------------------------------------------------------------------
	// 写订单的商品信息
	// oid : 订单编号,$ids :购物车中商品编号列表,  way:抵扣方式
	private function writeProduct($oid, $ids, $uid, $un) {
		$ids = ltrim ( $ids, ',' );
		$ids = rtrim ( $ids, ',' );

		if ($oid && $uid && ! empty ( $ids ) && (strlen ( $ids ) > 0) ) {
			$DAO = M ( 'ShopingCart' );
			$max_shippingFee = $DAO->where ( "id in ($ids) and user_id=".$uid )->max ( 'shipping_fee' ); // 取出最大的运费，同卖家运费相同
			$dataList = $DAO->where ( "id in ($ids) AND user_id=".$uid )->select ();
			$serve_rate = $this->getServiceRate ();
			$now = time ();
			
			foreach($dataList as $item) {
				if(empty($item)){continue; }
				$data ['title'] 		= (!empty($item['title']) && (trim($item['title']) != '') ) ? $item['title']:'';
				$data ['url'] 			= (!empty($item['url']) && (trim($item['url']) != '') ) ? $item['url'] : '';
				$data ['price1'] 		= (!empty($item['price']) && is_numeric($item['price'])) ? floatval($item['price']) : 0;				
				$data ['amount'] 	= (!empty($item['amount']) && is_numeric(trim($item['amount'])))?intval($item['amount']) : 0;
				
				//($data ['title'] == '') || ($data ['url'] =='') ||   此类可以退单或手动置为无效
				if(  ($data ['price1'] == 0) || ($data ['amount'] == 0) ){continue;} //这里先过滤掉无效的订单商品
				
				$data ['user_id'] 			= $uid;
				$data ['user_name'] 		= $un;
				$data ['order_id'] 			= $oid;
				$data ['package_id'] 		= 0;
				$data ['order_saler_id'] 	= 0; // 购买批次号
				
				$data ['price2'] 				= 0; // 折后价
				$data ['weight'] 			= 0;
				$data ['shipping_fee'] 	= floatval($max_shippingFee);
				
				$data ['service_rate'] 		= 0;//$serve_rate; // 服务费比例
				$service_fee 					= ($item['price'] * $item['amount'] * $serve_rate) / 100; //服务费
				$data ['service_fee'] 		= 0;//floatval($service_fee); // 服务费
				$total 							= floatval ( $item['price'] ) * intval ( $item['amount'] ) + floatval ( $service_fee );
				$data ['total'] 				= $total; //重新计算商品总价
				$data ['seller'] 				= $item['seller'];
				$data ['custmer_note']	= $item['note'];
				
				$thumb_file = './Uploads/pic/product/' . $item['thumb'] . '_s.jpg';
				if (file_exists ( $thumb_file )) {
					$data ['thumb'] = $item['thumb'];
				} else {
					$data ['thumb'] = '';
				}
				$data ['image'] = $item['image'];
				$data ['img_height'] = $item['height'];
				
				$data ['reserv_package'] 	= intval($item['reserv_package']);
				$data ['reserv_brand'] 		= intval($item['reserv_brand']);
				$data ['is_emergency'] 		= intval($item['is_emergency']);
								                                   
				// 业务相关
				$data ['status'] 					= 1; // 未处理
				$data ['real_total'] 			= 0; // 实际采购金额
				$data ['storeage_no'] 		= ''; // 库位号
				$data ['buyer_id'] 				= 0;
				$data ['buyer_name'] 		= '';
				$data ['supplement_id'] 	= 0; // 补款
				$data ['supplement_fee'] 	= 0;
				$data ['refund'] 				= 0; // 退款
				$data ['refund_way'] 			= 0;
				$data ['refund_time'] 		= 0;
				$data ['reason'] 				= '';
				$data ['feedback'] 			= 0;
				$data ['discount'] 				= 0;
				$data ['discount_way'] 		= 0;
				
				$data ['create_time'] 			= $now;
				$data ['last_update'] 			= $now;				
				
				$ret = M ( 'Product' )->data ( $data )->add ();
				if(!$ret){LOG::write('提交订单写商品时出错， data:'.json_encode($data));}
			}
			$DAO->where ( "id in ($ids) AND user_id=".$uid )->delete (); // 清除购物车中结算完的商品
			return true;
		}else{
			return false;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 统计购物车
	private function countCart($uid) {
		if ($uid) {
			$count = M ( 'ShopingCart' )->where ( 'user_id=' . $uid )->count ();
			Session::set ( C ( 'CART_COUNT' ), $count );
		}
	}
	

	//------------------------------------------------------------------------------------------------
	// * 记财务变更记录，这里只记订单提交产生的消费日志
	private function writeFinaceLog($uid, $un, $oid, $money, $mnybfr, $rebate, $rbtbfr, $pntbfr, $remark, $discount=0,$discount_way=0,$vouncher) {
		if($uid){
			$data ['user_id'] 			= $uid; // 使用者的ID
			$data ['user_name'] 		= $un; // 使用者姓名
			$data ['type_id'] 			= 201; // 购物车结算，见business.inc.php定义
			$data ['pay_id'] 				= 0;
			$data ['order_id'] 			= $oid; // 当单的ID
			$data ['package_id'] 		= 0;
			$data ['product_id'] 		= 0; // 商品id
			$data ['pointlog_id'] 		= 0;
		
			$data ['chagne_total'] 	= $money + $rebate;
			$data ['money'] 			= $money;
			$data ['money_before'] = $mnybfr;
			$data ['money_after'] 	= $mnybfr - $money; // 这里是消费，所以全记为减
			$data ['rebate'] 			= $rebate;
			$data ['rebate_before'] 	= $rbtbfr;
			$data ['rebate_after'] 	= $rbtbfr - $rebate;
			$data ['point'] 				= 0;
			$data ['point_before'] 	= $pntbfr;
			$data ['point_after'] 		= $pntbfr;
			$data ['discount'] 			= $discount;
			$data ['discount_way'] 	= $discount_way;		
			$data ['remark'] 			= trim( $remark);	
				
			$data ['ticket_id'] 			= ($vouncher != false) ? $vouncher['id']:0;
			$data ['code'] 				= ($vouncher != false) ? trim($vouncher['code']) : '';
			$data ['use_ticket'] 		= ($vouncher != false) ? 1 : 0;
			$data ['ticket_amount'] = $discount;
			$data ['ticket_mianzhi'] = ($vouncher != false) ? $vouncher['mianzhi']:0;	
			$data ['create_time'] 		= time (); // 写入时间
			
			return  M ( 'FinanceLog' )->data ( $data )->add ();
		}else{
			return false;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//更新会员等级
	private function updateUserLevel($uid, $total) {
		$DAO = M ( 'User' );
		$user = $DAO->where ( 'id=' . $uid )->find ();
		$FinanceCofDAO = M ( 'FinaceConfig' );
		if ($user && $FinanceCofDAO) {
			$UserLevelCost_1 = $FinanceCofDAO->where ( "item='" . C ( 'VIP1_SPEND' ) . "'" )->find ();
			$UserLevelCost_2 = $FinanceCofDAO->where ( "item='" . C ( 'VIP2_SPEND' ) . "'" )->find ();
			$UserLevelCost_3 = $FinanceCofDAO->where ( "item='" . C ( 'VIP3_SPEND' ) . "'" )->find ();
			
			$vip1 = $UserLevelCost_1 ['value'];
			$vip2 = $UserLevelCost_2 ['value'];
			$vip3 = $UserLevelCost_3 ['value'];
			if (($total >= $vip1) && ($total < $vip2)) {
				$user ['level'] = 1;
			} elseif (($total >= $vip2) && ($total < $vip3)) {
				$user ['level'] = 2;
			} elseif ($total >= $vip3) {
				$user ['level'] = 3;
			} else {
				$user ['level'] = 0;
			}
			Session::set ( 'ulowi_user_level', $user ['level'] );
			$DAO->where ( 'id=' . $uid )->save ( $user );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 取商品服务费比例
	private function getServiceRate() {
		$entity = M ( 'FinaceConfig' )->where ( "item='serve_rate'" )->find ();
		return  ($entity && ($entity ['value'] > 0)) ? $entity ['value'] :10; 
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>