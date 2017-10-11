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
		
		$ZoneList = M ( 'DeliverZone' )->where ( 'status=1' )->order ( 'sort,caption_en' )->select ();
		$this->assign ( 'ZoneList', $ZoneList );
	}
	
	//------------------------------------------------------------------------------------------------
	//尺码换算
	public function measure() {
		$this->assign ('title','尺码换算-代购中国商品尺码对比查询-viatang.com');
	    $this->assign ('keywords','代购，中国代购，淘宝代购，中国商品代购，美国代购，服装代购，饰品代购，包包代购，图书代购，日用品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
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
		$this->assign ('title','费用估算-代购商品全球国际运费查询-viatang.com');
	    $this->assign ('keywords','全球国际运费查询，物流运费查询，国际运费查询，中国邮政运费查询，DHL，EMS，UPS，邮政小包运费查询');
        $this->assign ('description','代购中国商品国际运费估算查询，EMS查询，DHL查询，AIR查询，UPS查询，邮政包裹查询，包裹查询地区：美国、新加坡、加拿大、英国、法国、德国、意大利、荷兰、澳大利亚、日本、新西兰、马来西亚、台湾、香港');
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
	// 终端入库API
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
	// 扫描条码识别，区分代购国际转运接口API
	public function scan(){
		$weight = $_REQUEST['w'];
		$code = $_REQUEST['c'];
		
		if (($weight != '') && ($code!= '')){
			//按代购识别
			$vo= M ( 'OrderTrace' )->where ( "(trace_no='$code') " )->find ();
		
			if ($vo) {
			 	$count = M ( 'Product' )->where ( "trade_no='".$vo['trade_no']."'" )->count ();
			 	
				//更新状态为已到货
				if($count > 0){
					M ( 'Product' )->execute("update __TABLE__ set status=4 where trade_no='" . $vo['trade_no'] . "'" );
					echo '1';
				}else{
					echo '0';
				} 
				 
				exit;
			} 
			
			//按国际转运进行识别
			$item = M('ProductAgent')->field('user_name,user_id,id,count,shipping_company')->where("trace_no='$code' ")->find();//AND status=0
			$weight *= 1000;
			if($item){				
				M('ProductAgent')->execute("update __TABLE__ set status=1,weight=$weight where trace_no='$code'");
				echo 'c:'.$item['count'].'|uid:'.$item['user_id'].'|id:'.$item['id'].'|expc:'.urlencode($item['shipping_company']).'|exp:'.$code.'|w:'.$weight.'|d:'.date('Y-m-d H:i',time()).'';
			}else{
				echo $this->writeProductAgent($weight, $code);
			}
		} 
	}
	
	//写入无主包裹
	private function writeProductAgent($weight,$code){
		$count = M('ProductAgent')->where("trace_no='$code'")->count();
		if($count == 0){
			$item = array('weight'			=>	$weight,
						  'total_weight'	=>	$weight,
						  'trace_no'		=>	$code,
						  'title'			=>	'待确认包裹，客人未提交',
						  'status'			=>	1,
						  'tag'				=>	1,	
						  'create_at'		=>  time()		 
						);
			return M('ProductAgent')->add($item);
		}
		return 0;
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

	public function costestimation(){
		$zone_id = $_POST ['zone'];
		$amount = empty($_POST ['amount'])?0:$_POST ['amount'];
		$weight = empty($_POST ['weight'])?0:$_POST ['weight'];
		$style = empty($_POST ['style'])?1:$_POST ['style'];
		$num = empty($_POST ['num'])?1:$_POST ['num'];

		$service_fee = 8;
		if($num <= 1) $service_fee = 8;
		//else if($num == 2) $service_fee = 18;
		//else $service_fee = 18 + ($num - 2)*6;

		if (!empty($zone_id) && is_numeric ($zone_id)) {
			$DAO = M ( 'DeliverAddress' );
			$AddressList = $DAO->field ( 'shipping_way,start_weight,start_price,continue_weight,continue_price,limit_weight,rate,limit_days' )->where ( "zone_id=$zone_id AND status='1'" )->select ();

			$i = 0;
			foreach ( $AddressList as $key => $way ) {
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
				//$service_fee = (($amount + $shipping) * $this->getServiceRate ()) / 100;
				$data ['service_fee'] = $service_fee;//number_format ( $service_fee, 2 );

				if ($zone_id != 5) { //非国内需加8元报关费
					$custom = $this->getcustom ();
				} else {
					$custom = 0;
				}
				$data ['custom'] = $custom;
				$data ['rate_fee'] = number_format ($amount*$way['rate']/100, 2 );
				$data ['total'] = floatval($data ['custom'])+floatval($shipping)+floatval($amount)+floatval($service_fee);
				$data ['total'] = number_format ($data ['total'], 2 );

				$data ['shipping_fee'] = number_format ( $shipping, 2 );

				foreach($data as $k => $value) $way[$k] = $value;

				$AddressList[$key] = $way;
			}

			$this->assign ( 'PriceList', $AddressList );
		}
		//取该地区运费价格列表
		$this->assign ( 'zone_id', $zone_id );
		$this->assign ( 'amount', $amount );
		$this->assign ( 'weight', $weight );
		$this->assign ( 'style', $style);
		$this->assign ( 'num', $num);
		$this->assign ('title','费用估算-代购商品全球费用估算查询-viatang.com');
	    $this->assign ('keywords','全球代购商品总费用估计查询，代购总费用查询，国际转运运费查询，中国邮政运费查询，DHL，EMS，UPS，邮政小包运费查询');
        $this->assign ('description','代购中国商品国际运费估算查询，EMS查询，DHL查询，AIR查询，UPS查询，邮政包裹查询，包裹查询地区：美国、新加坡、加拿大、英国、法国、德国、意大利、荷兰、澳大利亚、日本、新西兰、马来西亚、台湾、香港');
		$this->display ();
	}

	public function shippingfee(){
		$zoneId = $_GET ['zone_id'];
		$addressList = null;
		if(!empty($zoneId)){
			$dao = M('DeliverAddress');
			$addressList = $dao->where ( "zone_id=".$zoneId )->select();
		}

		$this->assign ( 'zone_id', $zoneId );
		$this->assign ( 'addressList', $addressList );
		$this->assign ('title','物流运费查询-代购商品全球国际运费查询-viatang.com');
	    $this->assign ('keywords','全球国际运费查询，物流运费查询，国际运费查询，中国邮政运费查询，DHL，EMS，UPS，邮政小包运费查询');
        $this->assign ('description','代购中国商品国际运费估算查询，EMS查询，DHL查询，AIR查询，UPS查询，邮政包裹查询，包裹查询地区：美国、新加坡、加拿大、英国、法国、德国、意大利、荷兰、澳大利亚、日本、新西兰、马来西亚、台湾、香港');
		$this->display ();
	}

	public function mailingrestrictions(){
		$this->assign ('title','邮寄限制-代购商品全球邮寄限制-viatang.com');
	    $this->assign ('keywords','全球代购商品邮寄限制查询，代购商品邮寄限制查询，代购中国邮寄限制，邮寄限制，DHL邮寄限制，EMS邮寄限制，UPS邮寄限制，邮政小包邮寄限制');
        $this->assign ('description','代购中国商品邮寄限制，EMS邮寄限制，DHL邮寄限制，AIR邮寄限制，UPS邮寄限制，邮政邮寄限制，包裹邮寄限制：美国、新加坡、加拿大、英国、法国、德国、意大利、荷兰、澳大利亚、日本、新西兰、马来西亚、台湾、香港');
		$this->display ();
	}

	public function packagetracking(){
		$this->assign ('title','包裹跟踪查询-国际包裹物流跟踪查询-viatang.com');
	    $this->assign ('keywords','国际包裹跟踪查询，全球国际包裹查询，包裹查询，国际包裹查询，中国邮政包裹查询，DHL，EMS，UPS，邮政小包包裹跟踪查询');
        $this->assign ('description','代购中国商品国际包裹跟踪查询，EMS包裹查询，DHL包裹查询，AIR包裹查询，UPS包裹查询，邮政包裹查询，包裹查询地区：美国、新加坡、加拿大、英国、法国、德国、意大利、荷兰、澳大利亚、日本、新西兰、马来西亚、台湾、香港');
		$this->display ();
	}

	public function sizeconversion(){
		$this->assign ('title','尺码换算-代购中国商品尺码对比查询-viatang.com');
	    $this->assign ('keywords','代购，中国代购，淘宝代购，中国商品代购，美国代购，服装代购，饰品代购，包包代购，图书代购，日用品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
		$this->display ();
	}

	public function evaluation(){
		$this->assign ('title','商品重量估算-代购中国商品估算重量-viatang.com');
	    $this->assign ('keywords','代购，中国代购，淘宝代购，中国商品代购，美国代购，服装代购，饰品代购，包包代购，图书代购，日用品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
		$this->display ();
	}

	public function paymentmethod(){
		$this->assign ('title','支付方式查询-代购中国商品支付方式介绍-viatang.com');
	    $this->assign ('keywords','代购，中国代购，淘宝代购，中国商品代购，美国代购，服装代购，饰品代购，包包代购，图书代购，日用品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
		$this->display ();
	}
}
?>