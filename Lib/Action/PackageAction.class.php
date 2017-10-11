<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 会员包裹管理
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author     soitun <stone@zline.net.cn>
 * @copyright  上海子凌网络科技有限公司
 * @license    http://www.zline.net.cn/license-agreement.html
 * @link       http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
load ( '@/functions' );

class PackageAction extends BaseAction {
	
	//----------------------------------------------------------------------------------------
	//包裹运输方式对应的模板文件
	protected $shipping_tpl = array('ems'=>'ems',
					 'air 2kg以上'=>'air',
					 'sal水陆联运'=>'sal',
					 '海运'=>'surface',
					 'dhl'=>'dhl');
	
	protected $default_url = '/Selfpurchase/cart.html';
	protected $cart;	//送货车
	protected $address;
	protected $finance;
	protected $min_serve_fee = 1.0;
	
	//----------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = M ('Package');
		$this->cart = M ('ShippingCart');
		$this->address = M('DeliverAddress');
		$this->finance = D( 'Finance' );
		$this->min_serve_fee = floatval(C('MIN_SERVICE_FEE'));
	}
	
	//----------------------------------------------------------------------------------------
	// 我的包裹首页
	public function index() {
		$this->redirect ( 'My/parcel' );
	}
	
	//----------------------------------------------------------------------------------------
	// 加载打包商品信息，显示相关费用
	public function package() {
	        $this->assign ('title','送货车商品提交打包结算-viatang.com');
		$IdAry = $_POST ['id'];
		if ($this->user &&  $IdAry && is_array($IdAry)  && (count ( $IdAry ) > 0)) {
			$ids = $this->buildIdstr ( $IdAry );
			$weight_total = 0;
			$package_weight = 0;
			$count = $this->cart->where ( "id in ($ids) AND user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$DataList = $this->cart->field ( 'title,count,img,total_weight,type' )->where ( "id in ($ids) AND user_id=" . $this->user ['id'] )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'count', $count );
				
				//计算商品总重量
				$weight_total = $this->computeProductWeight ( $ids ); //商品重量
				$package_weight = $weight_total * 0.1; //包装重理
				$this->assign ( 'weight_total', $weight_total );
				$this->assign ( 'package_weight', $package_weight );
				$this->assign ( 'ids', $ids ); //传递打包商品id列表
			} else {
				$this->assign ( 'count', 0 );
			}
			$this->display ();
		} else {
			$this->goError($this->default_url, L('package_load_error'));
		}
	}
	
	//----------------------------------------------------------------------------------------
	// 加载配送方式
	public function way() {
	        $this->assign ('title','包裹运输方式选择-viatang.com');
		$ids = trim ( $_POST ['ids'] );
		if ($this->user &&  $ids ) {
			$countryList = M ( 'DeliverZone' )->where ( 'status = 1' )->order ( 'sort asc' )->select ();
			
			//计算商品总重量
			$package_weight = $this->computeProductWeight ( $ids );
			$this->assign ( 'PackageWeight', $package_weight * 1.1 );
			$this->assign ( 'CountryList', $countryList );
			$this->assign ( 'InsureRate', $this->getInsureRate () );
			$this->assign ( 'ids', $ids ); //将需打包商品id列表回传
			
			// 列出当前用户的有效代金券
			$Djq_list = M ( 'Ticket' )->where ( 'user_id=' . $this->user ['id'] . ' and term>' . time () . ' and state=1 and use_type=2 ' )->select ();
			$this->assign ( 'Djq_list', $Djq_list );
		}
		$this->display ();
	}
	
	//----------------------------------------------------------------------------------------
	//收货地址
	public function address() {
	        $this->assign ('title','确认包裹收货地址-viatang.com');
		$ids = trim ( $_POST ['ids'] );
		if ($this->user &&  $ids ) {
			$wid = trim ( $_POST ['way_id'] ); //送货方式
			$insureTag = trim ( $_POST ['insure'] );
			
			$package_weight = floatval ( $this->computeProductWeight ( $ids ) * 1.1 );
			$product_fee = $this->computeProductFee ( $ids );
			$EntityFee = $this->doComputeFee ( $wid, $package_weight, $ids, $insureTag );
			$EntityFee ['productFee'] = $product_fee;
			$EntityFee ['discount_fee'] = $_POST['discount_fee'];//抵扣的金额
			$EntityFee ['ticket_code'] = trim($_POST['ticket_code']); //优惠券
			$EntityFee ['totalFee'] = $EntityFee ['totalFee'] - $_POST['discount_fee'];
			$InsureRate = $this->getInsureRate (); //保险比例
			
			$address = $this->address->where ( "id=$wid" )->find ();
			if ($address && $EntityFee) {
				$this->assign ( 'serve_cut', 0 );//服务费抵扣
				
				//以下参数用于提交生成包裹数据和结算时使用
				$this->assign ( 'ids', $ids ); //回传 需打包商品id列表
				$this->assign ( 'deliver_id', $wid ); //配送方式编号
				$this->assign ( 'zone_id', $address ['zone_id'] );
				$this->assign ( 'country', $address ['cname'] );
				$this->assign ( 'shipping_way', $address ['shipping_way'] ); //运输方式
				$this->assign ( 'deliver_area', $address ['ename'] );
				$this->assign ( 'weight', $package_weight ); //包裹重量，已加上皮
				$this->assign ( 'serve_rate', $address ['rate'] );
				$this->assign ( 'insure_rate', $InsureRate );
				$this->assign ( 'deduction_way', 1 ); //会员级别折扣
				
				//回传加密的费用字符串
				import ( 'ORG.Crypt.Des' );
				$des = new Des ();
				$feeStr = json_encode ( $EntityFee ); //对结算费用先序列化处理。
				$feeToen = base64_encode ( $des->encrypt ( $feeStr, C ( 'DES_KEY' ) ) );				
				$this->assign ( 'fee_token', $feeToen );
				
				//回传加密的运输方式
				$shippingEntity = array('deliver_id' 		=> $wid,
													 'zone_id' 			=> $address ['zone_id'],
													 'shipping_way'	=> urlencode($address ['shipping_way']),
													 'deliver_area' 	=> $address ['ename']);
				$shippingStr = json_encode ( $shippingEntity );
				$shippingToken = base64_encode ( $des->encrypt ( $shippingStr, C ( 'DES_KEY' ) ) );
				$this->assign('shipping_token',$shippingToken);
				
				//加载已填写的收货人地址列表
				$ReceiveList = M ( 'Address' )->where ( 'user_id=' . $this->user ['id'] )->select ();
				$this->assign ( 'AddressList', $ReceiveList );
				
				//加载配送区域
				$countryList =  M ( 'DeliverZone' )->where ( 'status = 1' )->order ( 'sort' )->select ();
				$this->assign ( 'CountryList', $countryList );
				
				//加载运单模板
				$this->assign ( 'shipping_templete', $this->shipping_tpl[strtolower ( $address ['shipping_way'] )] );
			} else {
				$this->goError($this->default_url, L('package_load_shipping_fail') );
			}
		}
		$this->display ();
	}
	
	//----------------------------------------------------------------------------------------
	// 点评
	public function review() {
		if ($this->user) {
			$w = trim ( $_REQUEST ['w'] );
			$addresStr = trim ( $_REQUEST ['adr'] );
			$aid = trim ( $_REQUEST ['aid'] );
			if ( $w  && (($addresStr != '') || ($aid > 0))) {
				if ($aid > 0) {
					$entity = M ( 'Address' )->where ( "id=$aid" )->find ();
					$this->assign ( 'address', $entity );
				} else {
					$item = str_replace ( '^', '/', $addresStr );
					$json = json_decode ( base64_decode ( $item ) );
					$entity = array ('contact' => urldecode ( $json->contact ), 'state' => urldecode ( $json->state ), 'city' => urldecode ( $json->city ), 'zip' => urldecode ( $json->zip ), 'address' => urldecode ( $json->address ), 'phone' => urldecode ( $json->phone ), 'country' => urldecode ( $json->country ) );
					$this->assign ( 'address', $entity );
				}				
				$this->display ( $w );
			}
		}
	}
	
	//----------------------------------------------------------------------------------------
	// 执行打包
	public function commit() {
        $this->assign ('title','送货车商品打包成功-viatang.com');				
		$ids = trim ( $_POST ['ids'] );		
		$feeToken = trim ( $_POST ['fee_token'] );		
		$shippingToken = trim($_POST['shipping_token']);
		
		if (empty ($feeToken) || empty($shippingToken)) { $this->goError($this->default_url,L('package_op_error') );return; }
		$feeEntity = $this->DecodeInfo($feeToken);
		$shippingEntity = $this->DecodeInfo($shippingToken);
		if(empty($feeEntity) || empty($shippingEntity)){ $this->goError($this->default_url,L('package_submit_fail') ); return;}		
		$packageWeight = floatval ( trim ( $_POST ['weight'] ) );
		$packageWeight = round ( $packageWeight, 2 );
						
		if ($this->user && $ids && is_numeric ( $packageWeight ) && ($packageWeight > 0) && $shippingEntity && $feeEntity && ($feeEntity ['totalFee'] > 0)) {
			
			$deliverId = intval($shippingEntity['deliver_id']);

			//运输方式不对，或超过限重
			if ( ($deliverId == 0) || ($packageWeight > $this->getLimitWeight ( $deliverId ) ) ) { $this->goError($this->default_url,L('package_over_limit') ); return;} 
			
			//余额不够
			if (! $this->checkFinance ( $this->user ['id'], $feeEntity ['totalFee'] )) { $this->goError('/My/pay.html',L('package_not_enough') ); return; } 
			
			//余额够结算
			//--------------------------------------------------------------------------------------
			// 1, 配送信息,送货方式，国家等
			$_deliverInfo = $this->buildDeliverInfo($deliverId, trim ( $_POST ['zone_id'] ), $shippingEntity);
			if($_deliverInfo == false) {$this->goError($this->default_url,L('package_zone_error') ); return;}
					
			// 2, 收货人信息
			$_address = $this->processAddress( $_POST ['address_id']);
			if(empty($_address)){ $this->goError($this->default_url,L('package_address_error') ); return; }
										
			//--------------------------------------------------------------------------------------
			// 3, 费用
			$_feeInfo = $this->buildFeeInfo($packageWeight, $this->countProduct ( $ids ), $_POST ['serve_rate'], $feeEntity, trim ( $_POST ['note'] ), $_POST ['insure_rate'], $_POST ['serve_cut']);
			if($_feeInfo == false) {$this->goError($this->default_url,L('package_fee_error') ); return;}
					
			//--------------------------------------------------------------------------------------------
			// 4, 保存打包时的运费价格 ( 用于区别，因调整运输方式价格时的情况 )
			$_wayPrice = $this->getWayPrice(intval(trim($deliverId)));
			if($_wayPrice == false){ $this->goError($this->default_url,L('package_data_error') ); return;}
					
			//--------------------------------------------------------------------------------------------
			// 5, 写入包裹信息
			$package = array_merge($_deliverInfo,$_address, $_feeInfo,$_wayPrice);					
			$oid =$this->dao->data ( $package )->add ();
					
			//--------------------------------------------------------------------------------------------
			// 6, 生成包裹信息并进行财务结算
			if ($oid > 0) {
				$this->doFinace ( $this->user ['id'], $this->user ['login_name'], $oid, $feeEntity ['totalFee'] );

				//置优惠券已使用标志
				$this->processTicket($this->user ['id'],$feeEntity ['ticket_code'] );
				$this->updateProductStatus ( $ids, $oid ); //更新商品状态
				$this->display ( 'result' );
			} else {
				$this->goError('/Order/yrk.html',L('package_submit_error') );
			}			
		} else {
			$this->goError('/Order/yrk.html',L('package_submit_error') );
		}
	}
	
	//----------------------------------------------------------------------------------------
	// 处理优惠券
	private function processTicket($uid, $code){
		//M('Ticket')->execute('update ticket set state=2 where user_id='.$uid." AND code='$code'");
		M()->execute("UPDATE ticket SET state=2, use_time=" . time () . "  WHERE state=1 AND  code='$code'");
		//echo "UPDATE ticket SET state=2, use_time=" . time () . "  WHERE state=1 AND  code='$code'";
	}


	//----------------------------------------------------------------------------------------
	//读取包裹收货人信息
	public function readnote() {
		if ($this->user && isset($_GET ['id']) && is_numeric(trim ( $_GET ['id'] ))) {			
			$entity = $this->dao->where ( "id=".trim ( $_GET ['id'] )." AND user_id=" . $this->user ['id'] )->find ();
			$this->assign ( 'entity', $entity );
			
			$countryList =  M( 'DeliverZone' )->where ( 'status = 1' )->order ( 'sort' )->select ();
			$this->assign ( 'CountryList', $countryList );
		}
		$this->display ( 'edit' );
	}
	
	//----------------------------------------------------------------------------------------
	//修改收货人
	public function note() {
		if ($this->user && isset($_POST ['id']) && is_numeric(trim ( $_POST ['id'] ))) {
			$data = array ('contact'=> trim ( $_POST ['u_a_name'] ),
									'country' 	=> trim ( $_POST ['u_a_country'] ),
			 						'city' 		=> trim ( $_POST ['u_a_city'] ),
			 						'address'	=> trim ( $_POST ['u_a_address'] ),
			 						'zip' 		=> trim ( $_POST ['u_a_zip'] ),
									'phone' 	=> trim ( $_POST ['u_a_phone'] ) );
			
			$this->dao->where ( "id=".trim ( $_POST ['id'] )." AND user_id=" . $this->user ['id'] )->save ( $data );
		}
		$this->display ( 'Public/result' );
	}
	
	//----------------------------------------------------------------------------------------
	// 撤销包裹
	public function cancel() {
		if ($this->user && isset($_POST ['id']) && is_numeric(trim ( $_POST ['id'] ))) {
			$result = $this->updatePackageCancel ( trim ( $_POST ['id'] ), $this->user ['id'] );
			if ($result) {
				$this->writePackageLog ( trim ( $_POST ['id'] ), 7, 0, '', '用户撤销包裹' );
				$this->setProductYrk ( trim ( $_POST ['id'] ) );
				$this->refund ( trim ( $_POST ['id'] ),0 ); //最后退款
			}
		}
		$this->redirect ( 'My/parcel' );
	}
	
	//----------------------------------------------------------------------------------------
	//针对退包将商品返回仓库
	public function returnWare(){
		if ($this->user && isset( $_POST ['id'] ) && is_numeric(trim ( $_POST ['id'] ))) {
			$result = $this->updatePackageCancel ( trim ( $_POST ['id'] ), $this->user ['id'] ); //更新为已撤销
			if ($result) {
				$this->writePackageLog ( trim ( $_POST ['id'] ), 7, 0, '', '用户撤销包裹' );
				$this->setProductYrk ( trim ( $_POST ['id'] ) );
				$this->refund ( trim ( $_POST ['id'] ),14 ); //最后退款, 这里扣除手续费14元
			}
		}
		$this->redirect ( 'My/parcel' );
	}
	
	//----------------------------------------------------------------------------------------
	//包裹详情
	public function detail() {
		$this->assign ('title','包裹详情-代购商品管理-viatang.com');
	    $this->assign ('keywords','代购商品管理,代购商品打包,包裹详情,商品清单,中国代购,代购中国商品,淘宝代购,海外华人代购,美国代购,美国代购网,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
        $this->assign ('description','唯唐代购商品管理,购物车商品打包,商品打包费用,DHL邮寄,EMS邮寄,中国邮政包裹,支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折起.');
		if ($this->user && isset($_GET ['id']) && is_numeric(trim ( $_GET ['id'] ))) {
			$entity = $this->dao->where ( "id=".trim ( $_GET ['id'] )." AND user_id=".$this->user ['id'] )->find ();
			$Way = $this->address->where ( 'id=' . $entity ['deliver_id'] )->find ();
			$this->assign ( 'entity', $entity ); //包裹信息
			$this->assign ( 'DeliverWay', $Way );
			
			if ($entity ['weight'] <= $Way ['start_weight']) {
				$shippingStr = $Way ['start_price'] . L('package_start_price');
			} else {
				$shippingStr = $Way ['start_price'] . ' + ' . $Way ['continue_price'] . ' * ' . ceil ( ($entity ['weight'] - $Way ['start_weight']) / $Way ['continue_weight'] );
				$shippingStr = $shippingStr . L('package_fee');
			}
			$this->assign ( 'ShippingStr', $shippingStr );
						
			//加载包裹的商品ID列表
			$product_lst = M('PackageProduct')->where('package_id='.$entity ['id'])->select();			
			$daigou_id_lst = array();
			$daiyun_id_lst = array();
			foreach ($product_lst as $p){
				if($p['type']==1){
					$daigou_id_lst[] = $p['product_id'];
				}elseif($p['type']==2){
					$daiyun_id_lst[] = $p['product_id'];
				}
			}
			
			//加载代购商品列表
			$id_lst = implode(',', $daigou_id_lst);
			$daigou = M ( 'Product' )->where("id in ($id_lst) AND user_id=".$this->user ['id'] )->select();
			$this->assign ( 'DataList', $daigou );
			
			//加载代运商品列表
			$daiyun_id = implode(',', $daiyun_id_lst);
			$daiyun = M ( 'ProductAgent' )->where("id in ($daiyun_id) AND user_id=".$this->user ['id'] )->select();
			$this->assign ( 'DataListAgent', $daiyun );
			
			//加载商品列表
			//$DataList =  M ( 'Product' )->where ( "package_id=".trim ( $_GET ['id'] )." AND status=5 AND user_id=".$this->user ['id'] )->select ();
			//$DataListSelfBuy = M ( 'ProductAgent' )->where ( "package_id=".trim ( $_GET ['id'] )." AND status=6 AND user_id=".$this->user ['id'] )->select ();
			
		}
		$this->display ();
	}
	
	//----------------------------------------------------------------------------------------
	// 确认收包
	public function confirm() {
		if ($this->user &&  isset($_GET ['id']) && is_numeric(trim ( $_GET ['id'] )) ) {
			$data = $this->dao->where ( "id=".trim ( $_GET ['id'] )." AND user_id=" . $this->user ['id'] )->find ();
			if ($data) {
				if (($data ['excess_money'] > 0) && ($data ['excess_money'] < $data ['shipping_fee']) && ($data ['refund_flag'] == 0)) {
					$this->processExcessMoney ( $data ['user_id'], $data ['user_name'], $data ['id'], $data ['excess_money'] );
					$data ['refund_flag'] = 1; //置已退款标志						  
				}
				$data ['status'] = 5; //确认收货
				$data ['last_update'] = time ();
				$this->dao->where ( "id=".trim ( $_GET ['id'] ) )->save ( $data );
				
				$this->writePackageLog ( trim ( $_GET ['id'] ), 5, 0, '', L('package_confirm') ); //写包裹日志
			}
		}
		
		$this->assign ( 'id', trim ( $_GET ['id'] ) );
		$this->display ( 'review' );
	}
	
	//-----------------------------------------------------------------------------------------------------------------------------------
	// 显示评论
	public function doreview(){
		if ($this->user){
			$this->assign ( 'id', trim ( $_GET ['id'] ) );
			$this->display ( 'review' );
		}
	}

	//-----------------------------------------------------------------------------------------------------------------------------------
	// 以下为内部函数
	//-----------------------------------------------------------------------------------------------------------------------------------
	
	//----------------------------------------------------------------------------------------
	// 构造打包时的配送信息
	private function buildDeliverInfo($did,$zid,$shp){
		if(empty($did) || empty($zid) || empty($shp) ) return false;
		return array('user_id' 		=> $this->user ['id'],
							'user_name' 	=> $this->user ['login_name'],
		 					'zone_id' 		=> $zid,
							'deliver_id' 	=> $did,
							'deliver_way' => urldecode(trim($shp['shipping_way'])),
							'deliver_area' => trim($shp['deliver_area']) );
	}

	//----------------------------------------------------------------------------------------
	// 包裹费用
	private function buildFeeInfo($p_weight,$count,$s_rate,$feeEntity,$note,$insure_rate,$serv_cut){
		if($p_weight && $count && $feeEntity && $insure_rate){
			return array('weight' 			=> $p_weight,
		 					'weight_guss' 		=> $p_weight,
		 					'weight_real' 		=> 0,
		 					'package_code' 	    => '',
		 					'product_num' 		=> $count,
		 					'product_fee' 		=> $feeEntity ['productFee'],
		 					'shipping_fee' 		=> $feeEntity ['shippingFee'],
		 					'serve_rate' 		=> $s_rate,
		 					'serve_fee' 		=> $feeEntity ['serviceFee'],
		 					'cutom_fee' 		=> $feeEntity ['customFee'],
		 					'status' 			=> 1,
		 					'custom_note' 		=> safeFilter ( $note ),
		 					'remarks' 			=> '',
				 			'reason' 			=> '',
		 					'insure_rate' 		=> $insure_rate,
		 					'insure_fee' 		=> $feeEntity ['insureFee'],
		 					'deduction_way' 	=> 1,
		 					'deduction_point'   => 0,
		 					'serve_cut_fee' 	=> $serv_cut,
		 					'total_fee' 		=> $feeEntity ['totalFee'],
		 					'rebate_fee' 		=> 0,
							'use_ticket'		=> (trim($feeEntity ['ticket_code'])=='')?0:1,
		 					'code'				=> trim($feeEntity ['ticket_code']),
							'ticket_amount'		=> $feeEntity ['discount_fee'],
		 					'excess_money' 	    => 0,
		 					'deliver_company'   => 0,
		 					'send_time_guess'   => $this->guessSend (),
		 					'send_time' 		=> 0,
		 					'create_time' 		=> time (),
		 					'last_update' 		=> 0 );
		}else{
			return false;
		}
	}
	
	//----------------------------------------------------------------------------------------
	// 解密前台回传信息
	private function DecodeInfo($info){
		try {
			import ( 'ORG.Crypt.Des' );
			$des = new Des ();
			$Token = base64_decode ( $info );
			$tokenStr = $des->decrypt($Token,C ( 'DES_KEY' ));
			return  convertJson2Array($tokenStr);
		} catch ( Exception $e ) {
			Log::write ( L('package_decode_fail'), Log::ERR );
			return false;
		}
	}
	
	//----------------------------------------------------------------------------------------
	// 显示出错信息
	private function goError($url,$msg){
		$this->assign ( 'jumpUrl', $url );
		$this->assign ( 'waitSecond', 30 );
		$this->assign ( 'msgTitle', L('package_op_fail') );
		$this->error ( $msg );
	}
	
	//----------------------------------------------------------------------------------------
	// 处理收货地址
	private function processAddress($wid){
			if ($wid == 0) {
				$data = array('country' => trim ( $_POST ['country'] ),
									'province'	=> trim ( $_POST ['province'] ),
									'city' 		=> trim ( $_POST ['city'] ),
									'address' 	=> trim ( $_POST ['address'] ),
									'contact' 	=> trim ( $_POST ['contact'] ),
									'phone' 	=> trim ( $_POST ['phone'] ),
									'zip' 			=> trim ( $_POST ['zip'] ) );
			} else {
				$address = M ( 'Address' )->where ( "id=$wid" )->find ();
				if ($address) {
					$data = array('country' => trim ( $address ['country'] ),
								'province' 	=> trim ( $address ['state'] ),
								'city' 		=> trim ( $address ['city'] ),
								'address' 	=> trim ( $address ['address'] ),
								'contact'	=> trim ( $address ['contact'] ),
								'phone' 	=> trim ( $address ['phone'] ),
								'zip' 			=> trim ( $address ['zip'] ) );
				}else{
					$data = array();
				}
			}
			return $data;
	}
	
	//----------------------------------------------------------------------------------------
	//根据 数据生成id串
	private function buildIdstr($idAry) {
		$result = '';
		if ($idAry && is_array ( $idAry )) {
			foreach ( $idAry as $id ) {
				$item = explode ( '.', $id );
				$result = $result . ',' . $item [2];
			}
			$result = ltrim ( $result, ',' );
		}
		return $result;
	}
	
	//----------------------------------------------------------------------------------------
	//处理包裹折扣,即包裹多收的运费在确认收货时，退还到折扣帐户
	private function processExcessMoney($uid, $un, $pid, $rebate) {
		if ( $uid  &&  $rebate  && ($rebate > 0)) {
			$finance = $this->finance->finace ( $uid);
			if ($finance) {
				$money_befor = $finance ['money'];
				$rebate_befor = $finance ['rebate'];
				$point_befor = $finance ['point'];
				
				$finance ['money'] = floatval($finance ['money']) + floatval($rebate)  ; //2013.5.12 将折扣返到现金帐户。
				$finance ['rebate'] = 0;
				$this->finance->updateInfo ( $finance ); 						

				//记财务日志
				$remark = L('package_confirm_return');
				$this->writeFinaceLog ( $uid, $un, 0, $pid, $rebate, $money_befor, $rebate_befor, $point_befor, $remark, 0, 303 ); //2013.5.12 将折扣返到现金帐户。
			}
		}
	}
	
	//----------------------------------------------------------------------------------------
	//更新包裹状态为已撤销
	private function updatePackageCancel($pgid, $uid) {
		$result = false;
		if ( $pgid  && $uid) {
			$package = $this->dao->where ( "id=$pgid AND user_id=$uid" )->find ();
			if ($package && ($package ['status'] != 7)) {
				$package ['status'] = 7;
				$package ['last_update'] = time ();
				$this->dao->where ( "id=$pgid" )->save ( $package );
				$result = true;
			}
		}
		return $result;
	}
	
	//----------------------------------------------------------------------------------------
	//计算打包商品总重量
	private function computeProductWeight($ids) {
		return ($ids) ? floatval ( $this->cart->where ( "id in ($ids)" )->sum ( 'total_weight' ) ) : 0;
	}
	
	//----------------------------------------------------------------------------------------
	//计算打包商品的金额
	private function computeProductFee($ids) {
		return ($ids) ?  $this->cart->where ( "id in ($ids) AND type=1" )->sum ( 'product_fee' ) : 0; //只统计代购商品结算时金额
	}
	
	//----------------------------------------------------------------------------------------
	// 统计送货车商品数量
	private function countProduct($ids) {
		return ($ids) ? $this->cart->where ( "id in ($ids)" )->sum ( 'count' ) : 0;
	}
	
	//----------------------------------------------------------------------------------------
	// 更新商品状态,并写入包裹号,这里的ids是shipping_cart的ids
	private function updateProductStatus($ids, $pgid) {
		$DataList = $this->cart->where ( "id in ($ids)" )->select ();
		$now = time();
		foreach ( $DataList as $item ) {			
			switch ($item ['type']) {
				case 1 :
					$this->updateProduct("UPDATE product SET status=5,package_id=$pgid,last_update=$now WHERE user_id=".$item ['user_id']." AND  id=".$item ['product_id'] );					
					$this->delfromShippingCar($item ['product_id'], 1, $item ['user_id']);
					$this->writeProductLog ( $item ['product_id'], 5, 0, 0, '', L('package_user_submit') ); //记商品日志
					break;
				case 2 :
					$this->updateProduct("UPDATE product_agent SET status=6,package_id=$pgid,last_update=$now WHERE user_id=".$item ['user_id']." AND id=".$item ['product_id'] );
					$this->delfromShippingCar($item ['product_id'], 2, $item ['user_id']);
					break;
			}
			$this->updateProduct("INSERT INTO package_product values($pgid,". $item['product_id']. "," . $item['count'] . "," . $item['type'] .")");
		}
	}
	//----------------------------------------------------------------------------------------
	//从送货车删除
	private function delfromShippingCar($pid,$type,$uid){
		$DAO = new Model();
		$DAO->execute("DELETE FROM shipping_cart WHERE product_id=$pid AND type=$type AND user_id=$uid");
	}

	//---------------------------------------------------------------------------------------
	// 更新商品信息
	private function updateProduct($sql){
		$DAO = new Model();
		$DAO->execute($sql);
	}

	//----------------------------------------------------------------------------------------
	//更新商品状态从已打包为已入库
	private function setProductYrk($pgid) {
		//代购商品
		$data ['status'] = 12;
		$data ['package_id'] = 0;
		$data ['had_second_count'] = 0;
		$data ['last_update'] = time ();
		
		//代收自助购商品
		$agent ['status'] = 5;
		$agent ['package_id'] = 0;
		$agent ['had_second_count'] = 0;
		$agent ['last_update'] = time ();
		
		$DAO = M ( 'Product' );
		$DataList = $DAO->where ( "package_id=$pgid" )->select ();
		foreach ( $DataList as $item ) {
			$this->writeProductLog ( $item ['id'], 12, 0, 0, '', L('package_user_cancel') . $pgid );
		}
		$DAO->where ( "package_id=$pgid" )->save ( $data );
		
		M ( 'ProductAgent' )->where ( "package_id=$pgid" )->save ( $agent );
	}
	
	//----------------------------------------------------------------------------------------
	//记包裹变更日志
	private function writePackageLog($id, $status, $adminid, $admin, $remark) {
		$entity = array('package_id' 		=> $id,
								'status' 				=> $status,
								'admin_id' 		=> $adminid,
								'admin_name' 	=> $admin,
								'remark' 			=> $remark,
								'create_time' 	=> time () );
		M ( 'PackageLog' )->data ( $entity )->add ();
	}
	
	//----------------------------------------------------------------------------------------
	//记商品变更日志
	private function writeProductLog($id, $status, $amount, $adminid, $adminm, $remark) {
		$entity = array('product_id' 		=> $id,
								'status' 				=> $status,
								'amount' 			=> $amount,
								'admin_id' 		=> $adminid,
								'admin_name' 	=> $adminm,
								'remark' 			=> $remark,
								'create_time' 	=> time ());
		M ( 'ProductLog' )->data ( $entity )->add ();
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
			$finance ['consumption_total'] = $finance ['consumption_total'] + $money_use;
			
			$money_use = 0 - $money_use;
			$rebate_use = 0 - $rebate_use;
			$finance ['last_update'] = time ();
			$this->finance->updateInfo($finance); //扣余额
			$remark = '包裹运输费用'; // . $total.'元,其中扣除现金帐户:'.$money_use.',扣除折扣帐户:'.$rebate_use;
			$this->writeFinaceLog ( $uid, $un, 0, $oid, $money_use, $money_befor, $reabet_befor, $finance ['point'], $remark, $rebate_use, 301 );
		}
	}
	
	//----------------------------------------------------------------------------------------
	//检查帐户余额是否足够结算
	//2013.5.12 by stone 只检查现金帐户余额
	private function checkFinance($uid, $total) {		
		$finance = $this->finance->finace( $uid );
		return ($finance && ($finance ['money'] >= $total) )?true:false;		
	}
	
	//----------------------------------------------------------------------------------------
	//增加会员积分
	private function addUserPoint($uid, $point) {
		$finance = $this->finance->finace($uid);
		if ($finance && $point) {
			$finance ['point'] = $finance ['point'] + $point;
			$this->finance->updateInfo( $finance );
		}
	}
	
	//----------------------------------------------------------------------------------------
	//估算发送包裹时间
	private function guessSend() {
		$send = time ();
		$now = date ( 'H', $send ); //当前时间 ,小时数
		$week = date ( 'w', $send );
		
		switch ($week) {
			case 1 :
			case 2 :
			case 3 :
				if ($now < 17) {
					$send = $send + 86400;
				} else {
					$send = $send + 86400 * 2;
				}
				break;
			case 4 :
				if ($now < 17) {
					$send = $send + 86400;
				} else {
					$send = $send + 86400 * 4; //周一发货
				}
				break;
			case 0 :
				$send = $send + 86400; //周一发包
				break;
			case 5 :
				$send = $send + 86400 * 3;
				break;
			case 6 :
				$send = $send + 86400 * 2;
		}
		return $send;
	}
	
	//----------------------------------------------------------------------------------------
	//退指定编号包裹的费用
	private function refund($id,$reserve_fee=0) {
		$package = $this->dao->where ( "id=$id" )->find ();
		if ($package && ($package ['refund_flag'] == 0)) {
			$uid = $package ['user_id'];
			$un = $package ['user_name'];
			$refund = $package ['total_fee'] - $reserve_fee; //扣除手续费
			$finance = $this->finance->finace ( $package ['user_id'] );
			
			if ($finance) {
				$money_befor = $finance ['money'];
				$reabet_befor = $finance ['rebate'];
				$finance ['money'] = $finance ['money'] + $refund;
				$finance ['consumption_total'] = $finance ['consumption_total'] - $refund;
				$finance ['last_update'] = time ();
				
				//更新帐户余额
				$this->finance->where ( 'id=' . $finance ['id'] )->save ( $finance );
				
				//更新包裹已退款标志
				$package ['refund_flag'] = 1;
				$this->dao->where ( 'id=' . $package ['id'] )->save ( $package );
				
				$remark = L('package_cancel_return');
				$this->writeFinaceLog ( $uid, $un, 0, $package ['id'], $refund, $money_befor, $reabet_befor, $finance ['point'], $remark, 0, 302 );
			}
		}
	}
		
	//----------------------------------------------------------------------------------------
	// 服务费
	private function getServiceRate() {
		$entity = M ( 'FinaceConfig' )->where ( "item='serve_rate'" )->find ();
		return ($entity && is_numeric($entity ['value'])) ?$entity ['value']:0;		
	}
	
	//----------------------------------------------------------------------------------------
	//更新会员等级
	private function updateUserLevel($uid) {
		$user = M ( 'User' )->where ( 'id=' . $uid )->find ();
		$FinanceCofDAO = M ( 'FinaceConfig' );
		$finance = $this->finance->finace (  $uid );
		if ($user && $FinanceCofDAO && $finance) {
			$total = $finance ['consumption_total'];
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
			M ( 'User' )->where ( 'id=' . $uid )->save ( $user );
		}
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
		$tr_hd = '<tr style="font-weight:bold;"><td align="left" width="100">运送方式</td><td>首重(g) </td><td>起价(￥) </td><td>续重(g) </td><td>续价(￥) </td><td>限重(kg)</td></tr> ';
		$emp_str = '<tr><td align="left" bgcolor="#FFFFFF"> - </td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>' . '<td bgcolor="#FFFFFF">0</td>'  . '</tr>';
		
		if ($zid) {
			$result = '';
			$DataList = $this->address->where ( "status = '1' AND zone_id=$zid" )->order ( 'id' )->select ();
			
			foreach ( $DataList as $key => $value ) {
				if (($weight <= 8000) && ($value ['shipping_way'] == '海运')) {
				}elseif( ($weight <10100) && (trim($value ['shipping_way']) == '专线11-100kg') ){					
				}elseif( ($weight >10000) && (trim($value ['shipping_way']) == '专线11kg以内') ){					
				}elseif( ($weight <12000) && (trim($value ['shipping_way']) == '12Kg以上大货专线') ){					
				}elseif( ($weight <21000) && (trim($value ['shipping_way']) == '21Kg以上大货专线') ){					
				}elseif( ($weight <4000) && (($value ['shipping_way'] == 'SAL水陆联运') || ($value ['shipping_way'] == 'AIR 2kg以上') ) ){					
				}else{
					$result .= '<tr><td align="left" bgcolor="#FFFFFF"><input type="radio" name="pg_shipping_method[]"  value="' . $value ['id'] . '" onclick="shipping( ' . $value ['id'] . ');checkDHL(\'' . $value ['shipping_way'] . '\');checkXMZX(\'' . $value ['shipping_way'] . '\');setLimitWeight(' . $value ['limit_weight'] * 1000 . ');" />' . $value ['shipping_way'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_price'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_price'] . '</td>' . '<td bgcolor="#FFFFFF" style="color:#f60;font-weight:bold;">' . $value ['limit_weight'] . '</td></tr> ';
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
		$id = trim ( $_POST ['wid'] );
		$weight = trim ( $_POST ['pw'] );
		$ids = trim ( $_POST ['ids'] );
		$insure = trim ( $_POST ['insure'] ); //是否参加保险
		
		if ($id && $weight && $ids) {
			$data = $this->doComputeFee ( $id, $weight, $ids, $insure );
			if ($data) {
				$this->ajaxReturn ( $data, L('package_cal_result'), 1 );
			} else {
				$this->ajaxReturn ( null, L('package_parameter_error'), 0 );
			}
		} else {
			$this->ajaxReturn ( null, L('package_parameter_error'), 0 );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//验证
	public function verifiDiscode(){
		if ($this->user && isset($_GET['c'])) {
			$code = mysql_escape_string(trim($_GET['c'])) ;
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

	//------------------------------------------------------------------------------------------------
	private function getVoucher($uid,$code){
		return ($code && $uid && is_numeric($uid)) ? M('Ticket')->where('user_id='.$uid ."  AND state=1 AND code='$code'")->find() : false;
	}

	//----------------------------------------------------------------------------------------
	//计算运费，服务费，保险费
	private function doComputeFee($wid, $pw, $ids, $tag) {
		if ($wid && $pw && $ids) {
			$shippingFee = $this->doShippingFee ( $wid, $pw ); //计算运费
			$ServiceFeeDaigou = $this->checkDaigouProductServiceFee ( $ids,$wid ); //核查代购部份商品的服务费
			$ServiceFeeShipping = $this->doShippingServiceFee ( $wid, $shippingFee ); //计算运费部份服务费
			$CustomFee = $this->getCustomFee ( $wid );
			$ProductFee = $this->computeProductFee ( $ids );
			if ($tag) {
				$InsureRate = $this->getInsureRate ();
				$InsureFee = (($ProductFee + $shippingFee) * $InsureRate) / 100; //保险费
			} else {
				$InsureFee = 0;
			}
			
			$totalFee = $shippingFee + $ServiceFeeDaigou + $ServiceFeeShipping + $CustomFee + $InsureFee +8;
			$data ['shippingFee'] = round ( $shippingFee, 2 );
			$data ['serviceFee'] = round ( $ServiceFeeDaigou + $ServiceFeeShipping, 2 );
			//if(floatval($data ['serviceFee']) < $this->min_serve_fee ) {$data ['serviceFee'] = $this->min_serve_fee;}
			$data ['insureFee'] = round ( $InsureFee, 2 ); //险费
			$data ['customFee'] = round ( $CustomFee, 2 );
			$data ['totalFee'] = round ( $totalFee, 2 ); //只保留两位小数
			$data ['serveFeeDaigou'] = round ( $ServiceFeeDaigou, 2 ); //代购商品的服务费
			$data ['serveFeeShiping'] = round ( $ServiceFeeShipping, 2 ); //运费部份服务费
			$data['package_material_fee'] = 8;
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
	//计算运费部份的服务费 
	private function doShippingServiceFee($wid, $shippingFee) {
		if ($wid && $shippingFee) {
			$way = $this->address->where ( "id=$wid" )->find ();
			$serviceRate =($way && $way ['rate'] > 0) ?  $way ['rate'] : $this->getServiceRate () ;
			return ($shippingFee * $serviceRate) / 100;
		}
		return 0;
	}
	
	private function getShippingServiceRate($wid){
		if ($wid) {
			$way = $this->address->where ( "id=$wid" )->find ();
			$serviceRate = ($way && is_numeric($way ['rate']) ) ?  $way ['rate'] :0 ;
			return $serviceRate;
		}
		return 0;
	}

	//----------------------------------------------------------------------------------------
	//检查代购部份商品服务费
	private function checkDaigouProductServiceFee($ids,$wid) {
		$result = 0;
		if ($ids && ($ids != '')) {
			$DataList = $this->cart->field ( 'product_fee,service_rate' )->where ( "id in ($ids) AND type=1 AND service_fee=0" )->select ();
			foreach ( $DataList as $item ) {
				$serviceRate = $this->getShippingServiceRate($wid);//$item ['service_rate'];
				$result = $result + ($item ['product_fee'] * $serviceRate) / 100;
			}
		}
		return $result;
	}
	
	//----------------------------------------------------------------------------------------
	//保险费比例
	private function getInsureRate() {
		$InsureRate = M ( 'FinaceConfig' )->where ( "item='" . C ( 'INSURE_RATE' ) . "'" )->find ();
		return ($InsureRate && ($InsureRate ['value'] > 0)) ? $InsureRate ['value']:5;
	}
	
	//----------------------------------------------------------------------------------------
	// 取报关费
	private function getCustomFee($id) {
		if (!is_numeric($id) ) { return 8; }		
		$way =  $this->address->where ( "id=$id" )->find ();
		return ($way && is_numeric($way ['customfee']) ) ? $way ['customfee'] : 8;
	}
	
	//----------------------------------------------------------------------------------------
	//取得限重
	private function getLimitWeight($id) {
		if (!is_numeric ( $id )) { return 2000; }
		$item = $this->address->where ( "id=$id" )->find ();
		return ($item && is_numeric($item ['limit_weight'])) ? $item ['limit_weight'] * 1000 : 2000;			
	}
	
	//--------------------------------------------------------------------------------------------------------------
	// 取线路价格
	private function getWayPrice($wid){
		if($wid && is_numeric($wid)){
			$data = $this->address->field('start_price,continue_price')->where('id='.$wid)->find();
			return (!empty($data))?$data:false;
		}
		return false;
	}
	
	//--------------------------------------------------------------------------------------------------------------
	//计算服务费
	public function do_serve_fee() {
		$ids = $_GET ['ids'];
		$shipp_fee = $_GET ['sf'];
		$product_fee = $this->computeProductFee ( $ids );
		$serve_rate = $this->getServiceRate ();
		
		$serve_fee = (($product_fee + $shipp_fee) * $serve_rate) / 100;
		echo  $serve_fee;
	}
	
 	public function loadDes(){
		$_wid = $_GET['id'];
		$_entity = $this->address->where("id=$_wid")->find();
		$_html = ' <tr style="font-weight:bold; height:30px;"><td width="100" align="left" >运输方式</td><td width="100" align="left" >时效</td><td  align="left">特点</td></tr>';
		if($_entity){
			$_html .=  '<tr><td  bgcolor="#FFFFFF" align="left">&nbsp;'.$_entity['shipping_way'].'</td><td align="left"  bgcolor="#FFFFFF">'.$_entity['limit_days'].'</td><td align="left"  bgcolor="#FFFFFF" style="padding:3px;"><div style="margin:3px; width:95%">'.$_entity['desc'].'</div></td></tr>';
		}
		echo $_html;
	} 
	

	
	//--------------------------------------------------------------------------------------------------------------
	// 空操作
	Public function _empty() {
		$this->redirect ( 'My/parcel' );
	}
}

?>