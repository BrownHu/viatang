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
class SelfpurchaseAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M ( 'CartAgent' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->redirect ( 'address' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function address() {
		$this->assign ('title','我的唯一收货地址-viatang.com');
	    $this->assign ('keywords','代邮商品详情,代寄,淘宝商品详情,国际转运,淘宝代寄,淘宝代邮,国内转运,唯唐代购,淘宝代购,海外华人代购,美国华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','提供一站式代购淘宝商品平台,专为海外华人留学生提供代购,代邮,代寄,国际转运商品服务,支持paypal付款.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function buy() {
		$this->assign ('title','提交购物清单-viatang.com');
	    $this->assign ('keywords','代邮商品详情,代寄,淘宝商品详情,国际转运,淘宝代寄,淘宝代邮,国内转运,唯唐代购,淘宝代购,海外华人代购,美国华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','提供一站式代购淘宝商品平台,专为海外华人留学生提供代购,代邮,代寄,国际转运商品服务,支持paypal付款.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$list =  M ( 'DeliverCompany' )->select();
			$this->assign('list',$list);
		}
		
		$this->display ();
	}
	
	
	
	//------------------------------------------------------------------------------------------------
	public function step3() {
		$user = Session::get ( C ( 'MEMBER_INFO' ) );
		if ($user) {
			$product ['user_id'] = $user ['id'];
			$product ['user_name'] = $user ['login_name'];
			$product ['title'] = trim ( $_POST ['title'] );
			$product ['count'] = trim ( $_POST ['productNum'] );
			$product ['count'] = (!is_numeric($product ['count']) || ($product ['count']<=0)) ? 1 : $product ['count'];
			$shiping_commpany =  trim ( $_POST ['shipingCompany'] );
			$trace_no = trim ( $_POST ['traceNo'] );
			$shiping_commpany = (strlen($shiping_commpany) > 1) ? ltrim($shiping_commpany,'-'):$shiping_commpany;
			$shiping_commpany = (strlen($shiping_commpany) > 1) ? rtrim($shiping_commpany,'-'):$shiping_commpany;
			$trace_no = (strlen($trace_no) > 1) ? ltrim($trace_no,'-'):$trace_no;
			$trace_no = (strlen($trace_no) > 1) ? rtrim($trace_no,'-'):$trace_no;
			
			if(($product ['title'] == '') || ($shiping_commpany == '') || ($trace_no == '')){
				$this->error('请完整填写快递公司名称，快递单号和商品名称后再提交');
				die();
			}
			
			$product ['remark'] = trim ( $_POST ['productRemark'] );
				
			$product['order_bat_id'] = time();
			$product ['send_time'] =  !empty($_POST ['express_date']) ? strtotime($_POST ['express_date']) : time();
			$product ['create_at'] =   time();
			
			$product ['shipping_company'] = trim($shiping_commpany);
			
			
			$_count = $this->countItemByTraceno(trim ($trace_no));
			
			$_do = false;
			$_message = '您提交的包裹运单号已存在，请勿重复提交!';
			$this->dao = M('ProductAgent'); 
			switch ($_count){
				case 0:	$product ['trace_no'] = trim ($trace_no);
						$_id = $this->dao->data ( $product )->add ();
						$admin = array('id'=>0,'loginame'=>'');
						$this->writeProductAgentLog($_id,0,$user['id'],$user['login_name'],'用户自主提交包裹',$admin);
						$_do = true;
						$_message = '恭喜，提交成功!您可在：个人中心-转运商品管理 中查看详情';
						break;
						
				case 1: $item = $this->loadItemByTraceno(trim ($trace_no));
						if($item ){
							if($item['user_id'] == 0){//无主包裹
								unset($product ['create_at']);// 禁更新创建日期
								unset($product ['count']);// 禁更新数量
								unset($product['order_bat_id']);
								
								$this->dao->where("id=".$item['id'])->save($product);
								$admin = array('id'=>0,'loginame'=>'');
								$this->writeProductAgentLog($item['id'],$item['status'],$item['user_id'],$item['user_name'],'用户(后)提交转运包裹信息，系统自动关联后并更新包裹重量商品名称等信息',$admin);
								$_do = true;
								$_message = '您提交的包裹已成功签收，现已自动关联到您的名下，您可在：个人中心-转运商品管理 中查看详情';
							}elseif( $item['user_id'] == $user ['id'] ){ 
								if($item['status'] == 0){//等待收货, 可以修改全部数据
									$product ['trace_no'] = trim ($trace_no);
									unset($product ['user_id']);
									unset($product ['user_name']);
									$_id = $this->dao->where("id=".$item['id'])->save( $product );
									$admin = array('id'=>0,'loginame'=>'');
									$this->writeProductAgentLog($_id,$item['status'],$user['id'],$user['login_name'],'“等待收货”订单，被用户成功更新包裹信息',$admin);
									$_do = true;
									$_message = '恭喜您，您提交的转运商品已更新成功!您可在：个人中心-转运商品管理 中查看详情';
								}else{
									$this->writeProductAgentLog($_id,$item['status'],$user['id'],$user['login_name'],'客户尝试修改处理中订单，系统已拒绝',$admin);
									$_do = false;
									$_message = '您提交的包裹已为您关联到账户中，您可在：个人中心-转运商品管理 中查看详情';
								}
							}
						}
						break;
			}
			
			if($_do){
				$this->assign ( 'product', $product );
				$this->assign('jumpUrl','/Selfpurchase/product');
				$this->success($_message);
			}else{
				$this->error($_message);
			}
		}
	}
	
	private function writeProductAgentLog($pid,$status,$user_id,$user_name,$remark,$admin){
		$item = array(	'product_id' 	=>	$pid,
				'status'		=>	$status,
				'admin_id'		=>	$admin['id'],
				'admin_name'	=>	$admin['loginame'],
				'user_id'		=>	$user_id,
				'user_name'		=>	$user_name,
				'remark'		=>	$remark,
				'create_at'		=>	time()
		);
		M("ProductAgentLog")->data($item)->add();
	}
	
	//------------------------------------------------------------------------------------------------
	private function countItemByTraceno($traceno){
		if(trim($traceno) != ''){
			return M('ProductAgent')->where("trace_no='$traceno'")->count();
		}
		
		return 0;
	}
	
	private function loadItemByTraceno($traceno){
		if(trim($traceno) != ''){
			return M('ProductAgent')->where("trace_no='$traceno'")->find();
		}
		
		return false;
		
	}
	
	
	//------------------------------------------------------------------------------------------------
	public function product() {
		$this->assign ('title','转运商品管理-viatang.com');
	    $this->assign ('keywords','转运网站,代邮,代寄,代购,国际转运,淘宝代寄,淘宝代邮,国内转运,唯唐代购,淘宝代购,海外华人代购,美国华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','提供一站式代购淘宝商品平台,专为海外华人留学生提供代购,代邮,代寄,国际转运商品服务,支持paypal付款.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		$IdAry = $_POST ['id'];
		if ($this->user) {
			$DAO = M ( 'ProductAgent' );
			$condition = '(status != 6) AND (status != 7) AND user_id=' . $this->user ['id'];
			$count = $DAO->where ( $condition )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'order_bat_id desc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			$this->redirect ( 'Public/login' );
		}
	}
	
	public function history(){
		$this->assign ('title','转运商品管理-viatang.com');
		$this->assign ('keywords','转运网站,代邮,代寄,代购,国际转运,淘宝代寄,淘宝代邮,国内转运,唯唐代购,淘宝代购,海外华人代购,美国华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
		$this->assign ('description','提供一站式代购淘宝商品平台,专为海外华人留学生提供代购,代邮,代寄,国际转运商品服务,支持paypal付款.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$this->dao = M ( 'ProductAgent' );
			$this->_list("user_id=".$this->user ['id'], 'id asc',C('NUM_PER_PAGE'));
			$this->assign('DataList',$this->view->get('list'));
			
			$this->display();
		}
	}
	
	public function updateTitle(){
		if($this->user){
			if(isset($_REQUEST['title'])){
				$this->dao = M ( 'ProductAgent' );
				$this->dao->execute("update __TABLE__ set title='".trim($_REQUEST['title'])."' where id=".$_REQUEST['id']);
				$this->display ( 'Public/result' );
			}else{
				$this->assign('id',$_REQUEST['id']);
				$this->display('title');
			}
		}
	}
	
	public function updateExpress(){
		if($this->user){
			if(isset($_REQUEST['com'])){
				$this->dao = M ( 'ProductAgent' );
				$this->dao->execute("update __TABLE__ set shipping_company='".trim($_REQUEST['com'])."' where id=".$_REQUEST['id']);
				$this->display ( 'Public/result' );
			}else{
				$this->assign('id',$_REQUEST['id']);
				$list =  M ( 'DeliverCompany' )->select();
				$this->assign('list',$list);
				$this->display('express');
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function del() {
		$IdAry = $_POST ['id'];
		if ($this->user && ! empty ( $IdAry ) && (count ( $IdAry ) > 0)) {
			$Idlst = implode ( ',', $IdAry );
			$this->dao->where ( "id in ($Idlst)" )->delete ();
		}
		$this->redirect ( 'buy' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 删除商品
	public function delProduct() {
		$id = $_POST ['id'];
		if ($this->user && ($id > 0)) {
			$DAO = M ( 'ProductAgent' );
			$DAO->where ( "id = $id" )->delete ();
		}
		$this->redirect ( 'product' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 提交商品列表
	public function checkout() {
		$IdAry = $_POST ['id'];
		if ($this->user && ! empty ( $IdAry ) && (count ( $IdAry ) > 0)) {
			$user_id = $this->user ['id'];
			$user_name = $this->user ['login_name'];
			
			// 更新数量和备注
			foreach ( $IdAry as $id ) {
				$count = trim ( $_POST ['count_' . $id] );
				$count = ($count && ($count > 0)) ? $count : 1;
				$mem = trim ( $_POST ['mem_' . $id] );
				$mem = ($mem != '请填写商品备注信息。') ? $mem : '';
				
				if (($count > 1) || ($mem != '')) {
					$data ['count'] = $count;
					$data ['remark'] = $mem;
					$this->dao->where ( "id=$id" )->save ( $data );
				}
				sleep ( 1 );
			}
			
			// 将购物车商品写入自助购订单
			$create_at = time ();
			$Idlst = implode ( ',', $IdAry );
			
			// 取得卖家列表
			$seller_list = $this->getSellerListById ( $Idlst, $user_id ); // 取得卖家列表
			//dump($seller_list);exit;
			if ($seller_list) {
				foreach ( $seller_list as $key => $item ) {
					$pids = $this->getProductListBySeller ( $item ['seller'], $user_id, $Idlst );
					
					$this->writeOrder ( $pids, $user_id, $user_name );
					sleep ( 1 );
				}
			}
			$this->dao->where ( "id in ($Idlst) AND user_id=$user_id" )->delete ();
			$this->redirect ( 'product' );
		} else {
			$this->redirect ( 'buy' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 加入送货车
	public function addtocart() {
		$IdAry = $_POST ['id'];
		if (! empty ( $IdAry ) && (count ( $IdAry ) > 0) && $this->user) {
			$user_id = $this->user ['id'];
			$user_name = $this->user ['login_name'];
			$DAO = M ( 'ProductAgent' );
			
			// 将购物车商品写入自助购订单
			$create_at = time ();
			$Idlst = implode ( ',', $IdAry );
			$DataList = $DAO->where ( "id in ($Idlst) AND user_id=$user_id" )->select ();
			
			$ShippingCartDAO = M ( 'ShippingCart' );			
			foreach ( $DataList as $item ) {
				$entity ['user_id'] = $user_id;
				$entity ['user_name'] = $user_name;
				$entity ['product_id'] = $item ['id'];
				$entity ['type'] = 2; // 1：代购，2：自助购
				$entity ['title'] = $item ['title'];
				$entity ['url'] = '';//$item ['url'];
				$entity ['img'] = '';//$item ['img'];
				$entity ['count'] = $item ['count'];
				$entity ['weight'] = $item ['weight'];
				$entity ['total_weight'] = $item ['weight'] * $item ['count'];
				$entity ['product_fee'] = 0;//$item ['price'] * $item ['count']; // 计算商品金额
				$entity ['service_rate'] = 0.1; // 自助购商品不收服务费
				$entity ['service_fee'] = 0; // 自助购商品不收服务费
				$entity ['create_at'] = $create_at;
				
				$entity ['shipping_company'] = $item ['shipping_company'];
				$entity ['trace_no'] = $item ['trace_no'];
				$entity ['remark'] = $item ['remark'];
				//dump($entity);exit;
				
				$count = $ShippingCartDAO->where ( 'product_id=' . $item ['id'] . ' AND type=2' )->count ();
				if ($count == 0) { // 这里防止重复添加
					$id = $ShippingCartDAO->data ( $entity )->add ();
				}
				$this->setStatus($item ['id'],7,$user_id);				
			}			
			
			$this->assign ( 'jumpUrl','/selfpurchase/product' );
			$this->success('已成功加入送货车!');
			
		} else {
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 送货车
	public function cart() {
		    $this->assign ('title','我的送货车-viatang.com');
	        $this->assign ('keywords','转运网站,代邮,代寄,代购,国际转运,淘宝代寄,淘宝代邮,国内转运,唯唐代购,淘宝代购,海外华人代购,美国华人代购,美国代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','提供一站式代购淘宝商品平台,专为海外华人、留学生提供代购,代邮,代寄,国际转运商品服务,支持paypal付款.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user) {
			$DAO = M ( 'ShippingCart' );
			$condition = 'user_id=' . $this->user ['id'];
			$count = $DAO->where ( $condition )->count ();
			if ($count > 0) {
				$p = new Page ( $count, 500 ); // C ( 'NUM_PER_PAGE' )
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_at desc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} else {
			$this->redirect ( 'Public/login' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 退回商品
	public function returnProduct() {
		$IdAry = $_POST ['id'];
		if (! empty ( $IdAry ) && (count ( $IdAry ) > 0) && $this->user) {
			$user_id = $this->user ['id'];
			foreach ( $IdAry as $id ) {
				$item = explode ( '.', $id );
				switch ($item [1]) {
					case 1 :
						$this->returnDaigouProduct ( $item [0], $user_id );
						$this->delCartItem ( $item [2], $user_id );
						break;
					case 2 :
						$this->returnSelfBuyProduct ( $item [0], $user_id );
						$this->delCartItem ( $item [2], $user_id );
						break;
				}
			}
			$this->redirect ( 'cart' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 物流息
	public function updateFreight() {
		$id = $_POST ['pid'];
		if ($this->user && $id && ($id > 0)) {
			$data ['shipping_company'] = trim ( urldecode ( $_POST ['company'] ), '-' );
			$traceno = trim ( urldecode ( $_POST ['trace_no'] ), '-' );
			$data ['trace_no'] = ltrim ( $traceno, '-' );
			
			M ( 'ProductAgent' )->where ( "id=$id AND status=0" )->save ( $data );
		}
		$this->display ( 'Public/result' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 物流息
	public function updateFreightEx() {
		$id = $_POST ['batid'];
		$seller = trim ( $_POST ['seller'] );
		if ($this->user && $id && ($id > 0)) {
			$data ['shipping_company'] = urldecode ( $_POST ['company'] );
			$traceno = urldecode ( $_POST ['trace_no'] );
			$data ['trace_no'] = trim ( $traceno, '-' );
			
			M ( 'ProductAgent' )->where ( "order_bat_id=$id AND status=0 AND seller='$seller'" )->save ( $data );
		}
		$this->display ( 'Public/result' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function loadFreight() {
		$id = $_GET ['id'];
		if ($this->user && $id && ($id > 0)) {
			$data ['id'] = $id;
			$data ['shipping_company'] = urldecode ( $_GET ['c'] );
			$data ['trace_no'] = urldecode ( $_GET ['no'] );
			$data ['status'] = $_GET ['s'];
			$this->assign ( 'entity', $data );
		}
		$this->display ( 'freight' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function showfreight() {
		if ($this->user) {
			$data ['batid'] = urldecode ( $_GET ['b'] );
			$seller = trim ( urldecode ( $_GET ['s'] ) );
			$seller = str_ireplace ( '^', '/', $seller );
			$data ['seller'] = base64_decode ( $seller );
			$this->assign ( 'entity', $data );
		}
		$this->display ( 'freight2' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function loadRemark() {
		$id = $_GET ['id'];
		if ($this->user && $id && ($id > 0)) {
			$entity = M ( 'ProductAgent' )->field ( 'id,remark,status' )->where ( "id=$id" )->find ();
			$this->assign ( 'entity', $entity );
		}
		
		$this->display ( 'remark' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function updateRemark() {
		$id = $_POST ['pid'];
		if ($this->user && $id && ($id > 0)) {
			$data ['remark'] = urldecode ( $_POST ['remark'] );
			
			M ( 'ProductAgent' )->where ( "id=$id AND status=0" )->save ( $data );
		}
		$this->display ( 'Public/result' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function item() {
		echo $this->fetch ( 'item' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function accept() {
		$supplementId = trim ( $_POST ['supplementid'] );
		if ($this->user && $supplementId) {
			$item = $this->loadSupplementFee ( $supplementId );
			if ($item) {
				// 第一步,先进行扣除处理
				$type = 509; // 默认为换货补款
				$needPay = $item ['shipping_fee'];
				$balance = $this->getUserBalance ( $this->user ['id'] ); // 取当前余额
				if ($balance >= $needPay) { // 余额够支付
					if ($item ['product_status'] == 9) {
						$type = 508;
					} elseif ($item ['product_status'] == 10) {
						$type = 509;
					}
					$upadteFinaceResult = $this->updateUserBalance ( $this->user ['id'], $this->user ['login_name'], $item ['id'], $needPay, $type );
				} else {
					$this->assign ( 'jumpUrl', '/My/pay.shtml' );
					$this->assign ( 'waitSecond', 30 );
					$this->error ( '您的帐户余额不足，请充值后重新操作！' );
				}
				
				// 第二步,置为已补款
				if ($upadteFinaceResult) {
					$item ['return_contact'] = trim ( $_POST ['contact'] );
					$item ['return_tel'] = trim ( $_POST ['tel'] );
					$item ['return_zip'] = trim ( $_POST ['zip'] );
					$item ['return_address'] = trim ( $_POST ['address'] );
					$item ['return_remark'] = trim ( $_POST ['remark'] );
					$item ['status'] = 1; // 已接受补款
					$item ['last_update'] = time (); // 已接受补款
					M ( 'SupplementShipping' )->where ( "id=$supplementId AND user_id=" . $this->user ['id'] )->save ( $item );
					
					// 第三步，更新商品状态
					if ($item ['product_status'] == 9) {
						$this->upateProductStatus ( $supplementId, 3 ); // 退货处理中
					} elseif ($item ['product_status'] == 10) {
						$this->upateProductStatus ( $supplementId, 8 ); // 换货处理中
					}
				}
			}
		}
		$this->redirect ( 'product' );
	}
	
	//------------------------------------------------------------------------------------------------
	public function deny() {
		$supplementId = trim ( $_POST ['supplementid'] );
		if ($this->user && $supplementId) {
			// 1,置拒绝标志
			$item ['status'] = 2;
			$item ['last_update'] = time ();
			M ( 'SupplementShipping' )->where ( "id=$supplementId AND user_id=" . $this->user ['id'] )->save ( $item ); // 更新为已拒绝
			                                                                                
			// 2,还原为问题商品
			$this->upateProductStatus ( $supplementId, 2 );
		}
		$this->redirect ( 'product' );
	}
	// --------------------------------------------------------------------------------------------------------
	
	//------------------------------------------------------------------------------------------------
	// 根据物品ID列表，取得卖家列表
	private function getSellerListById($ids, $uid) {
		$DataList = false;
		if (! empty ( $ids ) && (strlen ( $ids ) > 0)) {
			$DataList = M ( 'CartAgent' )->field ( 'seller' )->where ( "id in ($ids) AND user_id=$uid" )->Distinct ( true )->select ();
		}
		
		return $DataList;
	}
	
	//------------------------------------------------------------------------------------------------
	// 取卖家的商品id列表
	private function getProductListBySeller($sellser, $uid, $ids) {
		$result = '';
		if (! empty ( $sellser ) && ! empty ( $uid ) && ! empty ( $ids )) {
			$ids = ltrim ( $ids, ',' );
			$ids = rtrim ( $ids, ',' );
			
			$DAO = M ( 'CartAgent' );
			$DataList = $DAO->field ( 'id' )->where ( "user_id=$uid AND seller='$sellser' AND id in ($ids)" )->select ();
			foreach ( $DataList as $i => $item ) {
				$result .= ',' . $item ['id'];
			}
			
			$result = ltrim ( $result, ',' );
			$result = rtrim ( $result, ',' );
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	private function setStatus($pid,$sta,$uid){
		$DAO = new Model();
		$DAO->execute("UPDATE product_agent SET status=$sta WHERE id=$pid  AND user_id=$uid");
	}
	
	//------------------------------------------------------------------------------------------------
	// 产生订单
	private function writeOrder($pids, $uid, $user_name) {
		if (! empty ( $pids ) && $uid) {
			$ProductDAO = M ( 'ProductAgent' );
			$create_at = time ();
			$bat_id = $create_at; // 订购批次，这里同一时间提交的商品
			$DAO = M ( 'CartAgent' );
			$DataList = $DAO->where ( "id in ($pids) AND user_id=$uid" )->order ( 'seller' )->select ();
			//dump(DataList);
			foreach ( $DataList as $item ) {
				$product ['user_id'] = $uid;
				$product ['user_name'] = $user_name;
				$product ['order_bat_id'] = $bat_id; // 订购批次
				$product ['title'] = trim ( $item ['title'] );
				$product ['url'] = trim ( $item ['url'] );
				$product ['img'] = trim ( $item ['img'] );
				$product ['count'] = trim ( $item ['count'] );
				$product ['shipping_company'] = trim ( $item ['shipping_company'] );
				$product ['trace_no'] = trim ( $item ['trace_no'] );
				$product ['remark'] = trim ( $item ['remark'] );
				$product ['save_package'] = trim ( $item ['save_package'] );
				$product ['save_brand'] = trim ( $item ['save_brand'] );
				$product ['price'] = $item ['price'];
				$product ['seller'] = trim ( $item ['seller'] );
				$product ['admin_id'] = 0;
				$product ['admin_name'] = '';
				$product ['status'] = 0;
				$product ['create_at'] = $create_at;
				$product ['last_update'] = 0;
				
				//$product ['shipping_company'] = $item ['shipping_company'];
				//$product ['trace_no'] = $item ['trace_no'];
				//$product ['remark'] = $item ['remark'];
				
				$ProductDAO->data ( $product )->add (); // 添加到自助购商品里
			}
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 从自助购的购物车删 除
	private function removeFromCart($pids, $uid) {
		if (! empty ( $pids ) && $uid) {
			 $this->dao->whre ( "id in ($pids) AND user_id=$uid" )->delete ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function upateProductStatus($supplementId, $status) {
		if ($supplementId) {
			$item ['status'] = $status;
			$item ['last_update'] = time ();
			M ( 'ProductAgent' )->where ( "supplement_id=$supplementId" )->save ( $item );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 取用户余额
	private function getUserBalance($uid) {
		$result = 0;
		if (! empty ( $uid )) {
		 // 取帐户余额 ＝现金账户＋折扣账户
			$finance = D ( 'Finance' )->finace ( $uid );
			if ($finance) {
				$result = $finance ['money'] + $finance ['rebate'];
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 取出要补款金额
	private function getSupplementFee($id) {
		$result = 10;
		if ($id > 0) {
			$DAO = M ( 'SupplementShipping' );
			$item = $DAO->field ( 'shipping_fee' )->where ( "id=$id" )->find ();
			if ($item) {
				$result = $item ['shipping_fee'];
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	private function loadSupplementFee($id) {
		$item = false;
		if ($id > 0) {
			$item = M ( 'SupplementShipping' )->where ( "id=$id" )->find ();
		}
		return $item;
	}
	
	//------------------------------------------------------------------------------------------------
	// 这里结算，所以全部为扣款动作
	private function updateUserBalance($uid, $un, $oid, $order_fee, $type) {
		$result = false;
		$FinaceDAO = D ( 'Finance' );
		$finance = $FinaceDAO->finace ( $uid );
		if ($finance) {
			$balance = $finance ['money'] + $finance ['rebate']; // 取帐户余额
			                                                     // ＝现金账户＋折扣账户
			if ($balance >= $order_fee) { // 余额够支付
				$money_before = $finance ['money'];
				$rebate_before = $finance ['rebate'];
				$money_use = 0;
				$rebate_use = 0;
				$point = $finance ['point'];
				
				if ($finance ['money'] >= $order_fee) { // 优先使用现金账户
					$finance ['money'] = $finance ['money'] - $order_fee;
					$money_use = $order_fee;
				} else {
					$rebate_use = $order_fee - $finance ['money']; // 现金余额还差订单金额数
					$finance ['money'] = 0;
					$finance ['rebate'] = $finance ['rebate'] - $rebate_use;
					$money_use = $money_before;
				}
				$finance ['consumption_total'] = $finance ['consumption_total'] + $order_fee; // 消费总额
				$FinaceDAO->updateInfo ( $finance ); // 更新财务数据
				$this->writeFinaceLog ( $uid, $un, $oid, $money_use, $money_before, $rebate_use, $rebate_before, $point, '购物车结算', $type ); // 记财务变更记录
				$result = true;
			}
		}
		
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	/**
	 * 记财务变更记录，这里只记订单提交产生的消费日志 	
	 */
	private function writeFinaceLog($uid, $unam, $oid, $money, $mnybfr, $rebate, $rbtbfr, $pntbfr, $remark, $type = 509) {
		$data ['user_id'] = $uid;
		$data ['user_name'] = $unam;
		$data ['type_id'] = $type; // 购物车结算，见business.inc.php定义
		$data ['pay_id'] = 0;
		$data ['order_id'] = $oid;
		$data ['package_id'] = 0;
		$data ['product_id'] = 0;
		$data ['pointlog_id'] = 0;
		
		$data ['chagne_total'] = $money + $rebate;
		$data ['money'] = $money;
		$data ['money_before'] = $mnybfr;
		$data ['money_after'] = $mnybfr - $money; // 这里是消费，所以全记为减
		$data ['rebate'] = $rebate;
		$data ['rebate_before'] = $rbtbfr;
		$data ['rebate_after'] = $rbtbfr - $rebate;
		$data ['point'] = 0;
		$data ['point_before'] = $pntbfr;
		$data ['point_after'] = $pntbfr;
		
		$data ['remark'] = $remark;
		$data ['create_time'] = time ();
		
		M ( 'FinanceLog' )->data ( $data )->add ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 删除送货车商品
	private function delCartItem($id, $uid) {
		if (!empty($id) && !empty($uid)) {
			$DAO = new Model();
			$DAO->execute("DELETE FROM shipping_cart  WHERE id=$id AND user_id=$uid");
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function returnSelfBuyProduct($id, $uid) {
		if (!empty($id)  && !empty($uid)) {
			$DAO = new Model();
			$DAO->execute("UPDATE product_agent SET status=5 WHERE id=$id AND user_id=$uid");			
		}
	}
	
	//------------------------------------------------------------------------------------------------
	private function returnDaigouProduct($id, $uid) {
		if (!empty($id)  && !empty($uid)) {
			$DAO = new Model();
			$DAO->execute("UPDATE product SET status=12 WHERE id=$id AND user_id=$uid");
			$this->productLog ( $id, 12, 'status:17->12,用户将商品从送货车退回仓库' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 记代购商品日志
	private function productLog($id, $status, $remark = '') {
		if ($id && ($id > 0) && status) {
			$log ['product_id'] = $id;
			$log ['status'] = $status;
			$log ['remark'] = $remark;
			$log ['create_at'] = time ();
			M ( 'ProductLog' )->data ( $log )->add ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 从查询中取出商品id
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
	
	//------------------------------------------------------------------------------------------------
	// 解析淘宝商品属性
	private function parseTaobao($id) {
		if (! empty ( $id )) {
			import ( '@.ORG.Top.request.ItemGetRequest' );
			$token = getAppToken ();
			$req = new ItemGetRequest ();
			
			$req->setFields ( 'detail_url,title,nick,pic_url,price,express_fee' );
			$req->setNumIid ( $id );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$productAry = get_object_vars ( $Reponse->item );
			$product_info = array (
					'url' => $productAry ['detail_url'],
					'title' => $productAry ['title'],
					'price' => $productAry ['price'],
					'shipping_fee' => $productAry ['express_fee'],
					'seller' => $productAry ['nick'],
					'image' => $productAry ['pic_url'] 
			);
			return $product_info;
		} else {
			return false;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//通过snoopy采集数据
	private function snoopy($page_url){
		if(!empty($page_url)){
			import ( 'ORG.Util.Snoopy' );
			$snoopy = new Snoopy;
			$snoopy->fetch($page_url); //获取所有内容
			return  $snoopy->results; //显示结果
				
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//抓取淘宝移动端数据的方法
	private function dataMobile($page_url,$domain){
		$htmlstr = $this->snoopy($page_url);
		$htmlstr = iconv("gb2312","UTF-8//IGNORE",$htmlstr);
		
		$productinfo =array();
		$productinfo['url'] = $page_url;
		//匹配标题
		$preg = '/<title >(.*?)<\/title>/is';
		preg_match($preg, $htmlstr,$matches);
		$productinfo['title'] =$matches[1];
		//匹配价格
		$preg='/<strong class="oran">(.*?)<\/strong>/is';
		preg_match($preg, $htmlstr,$matches);
		$productinfo['price'] = $matches[1];
		if(!$productinfo['price']){
			$preg = '/<del class="gray">(.*?)<\/del>/is';
			preg_match($preg, $htmlstr,$matches);
			$productinfo['price'] = $matches[1];
		}
		$arr = explode('-', $productinfo['price']);
		if(count($arr)>1){$productinfo['price']=trim($arr[1]);}
		//匹配图片
		$preg = '/<p>\s*<img alt="(.*?)" src="(.*?)" \/>\s*<\/p>/is';
		preg_match($preg, $htmlstr,$matches);
		$productinfo['image'] = $matches[2];
		//匹配运费
		$preg = '/<p class=" even ">\s*运(.*?)<\/p>/is';
		preg_match_all($preg, $htmlstr,$matches);
		if(!$matches[1][0]){
			$preg = '/<p class=" odd ">\s*运(.*?)<\/p>/is';
			preg_match_all($preg, $htmlstr,$matches);
		}
		$shipping_fee = str_replace('\r\n', '', $matches[1][0]);
		preg_match('/快递(.*)/is', $shipping_fee,$matches);
		if($matches[1])
		{
			$shipping_fee =trim(str_replace(':', '', $matches[1]));
			$shipping_fee = str_replace('元', '', $shipping_fee);
			$shipping_fee = str_replace('EMS', ',', $shipping_fee);
			$shipping_fee = str_replace('平邮', ',', $shipping_fee);
			$shipparr = explode(',', $shipping_fee);
			$shipping_fee =trim($shipparr[0]);
				
		}else {$shipping_fee=0;}
		$productinfo['shipping_fee'] = $shipping_fee;
		
		//以下为抓取卖家
		$id =str_replace('http://a.'.$domain, '', $page_url);
		$id = explode('.', $id);
		$id = str_replace('/i', '', $id[0]);//获得id
		if($domain=='m.taobao.com')
			$url_taobao ="http://item.taobao.com/item.htm?spm=a2106.m944.1000384.58.FY1Tni&id=$id&_u=pbqutr69292&scm=1029.newlist-0.bts4.50031727&ppath=&sku=";
		if($domain=='m.tmall.com')
			$url_taobao ="http://detail.tmall.com/item.htm?spm=a2106.m944.1000384.7.FY1Tni&id=$id&source=dou&_u=pbqutr62845&scm=1029.newlist-0.bts4.50031727&ppath=&sku=";
		
		$tao_html = $this->snoopy($url_taobao);
		$tao_html = iconv("gb2312","UTF-8//IGNORE",$tao_html);
		//file_put_contents('Public/htmlstr.txt', $tao_html);
		//$tao_html=file_get_contents('Public/htmlstr.txt');
	
		if(strtolower ( $domain ) == 'm.taobao.com'){
			preg_match("/<a class=\"hCard fn\" (.*?)>(.*?)<\/a>/", $tao_html,$match);
			$seller_name =  $match[2];
		}if(strtolower ( $domain ) == 'm.tmall.com')
		{
			preg_match("/<span class=\"slogo\">(.*?)<\/a>/s", $tao_html,$match1);
	
			$match1[1]= str_replace("\r\n", '', $match1[1]);
			$seller_name=strip_tags($match1[1]);
			$seller_name = str_replace("品牌直销", '',trim($seller_name));
		}
		$productinfo['seller'] =$seller_name;
		return $productinfo;
	}
}
?>