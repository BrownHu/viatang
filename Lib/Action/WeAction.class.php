<?php
/**
+------------------------------------------------------------------------------
 * 国际转运系统
 *
 * 微信接口
+------------------------------------------------------------------------------
 * @category   ulowi
 * @author     soitun <stone@zline.net.cn>
 * @copyright  上海子凌网络科技有限公司
 * @license    http://www.zline.net.cn/license-agreement.html
 * @link       http://www.zline.net.cn/
+------------------------------------------------------------------------------
 */
use Com\Wechat;
import ( 'ORG.Wechat.Wechat' );
import ( 'ORG.Wechat.WechatAuth' );
load ( '@/functions' );
import ( 'ORG.Util.Page' );
class WeAction extends HomeAction {
//6UmXvxK4rRAbeF4DJlnHrDZmrhBFNTrcRP0vzxZ4hFY  EncodingAESKey
    protected $_server_url     = '';
    protected $wechat_token    = '';
    private   $requestCodeURL  = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    private   $redirect_url    = 'http://vt.daigoucms.cn/we/requestToken/do/';
    private   $accesstoken_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=__APPID__&secret=__SECRET__&code=__CODE__&grant_type=authorization_code';

    protected $goods;
    protected $express;
    protected $user;
    protected $address;
    protected $finance;
    protected $lang = LANG_SET;
    protected $country;
    protected $openid;
    // -------------------------------------------------------------------------------------------
    // 初始化
    function _initialize() {

        $action=ACTION_NAME;
        $this->wechat_token = 'ZLinkDaigouCMS';
        $this->_server_url = 'http://www.viatang.com/we/';//C('SITE_URL')
        if( !isset($_SESSION ['WechatState']) ){
            $_SESSION ['WechatState'] = md5 ( uniqid ( rand (), TRUE ) );
        }

        $NeedMember=array('cartCommit',"comment",'cartStep4','cartStep2','cart','memberInfo','member','arriveQuery','checkArrive','commitPackage','addtocart','address','goodM','parcel');
        if (in_array($action,$NeedMember,true)){
            if( isset($_SESSION ['WechatAuthOpenId'] )|| isset($_REQUEST['openid'])){
                $openid = isset($_SESSION ['WechatAuthOpenId'] )? $_SESSION ['WechatAuthOpenId'] : $_REQUEST['openid']  ;
                $this->user=$this->getUserByWechatOpenId($openid);
            }else{
                $this->processOpenid($action);
            }
        }

    }

    // -------------------------------------------------------------------------------------------
    private function getRequestCodeUrl($_second_redirect_uri){
        $_redirect_uri = $this->redirect_url . $_second_redirect_uri;
        $query = array(
            'appid'         => "wxdfaebc95aec271b5",
            'redirect_uri'  => $_redirect_uri,
            'response_type' => 'code',
            'scope'         => 'snsapi_base',
            'state'			=> md5 ( uniqid ( rand (), TRUE ) )
        );

        $query = http_build_query($query);
        return "{$this->requestCodeURL}?{$query}#wechat_redirect";
//        echo "{$this->requestCodeURL}?{$query}#wechat_redirect";
    }

    // -------------------------------------------------------------------------------------------
    // 获取微信AccessToken, 并从中取得openid
    // 将openid 传递到 do 所指定的方法中
    public function requestToken(){
        $_state = trim($_REQUEST ['state']);
        $_code  = trim($_REQUEST ["code"]);
        $openid = $this->getAuthAccessToken($_state, $_code);
        $do = $_REQUEST['do'];
        $this->redirect($do,array('openid'=>$openid));
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    private function getAuthAccessToken($state, $code) {
        //if ($state == $_SESSION ['WechatState']) {
        $appid = 'wxdfaebc95aec271b5';//trim ( C ( 'Wechat_APPID' ) );
//			$appid = trim ( C ( 'WECHAT_AppID' ) );
//			$secret = trim ( C ( 'WECHAT_AppSecret' ) );
        $secret = "862a68a8b553c46bd0dfe7e939fbd765";
        $token_url = str_replace('__APPID__', $appid, $this->accesstoken_url);
        $token_url = str_replace('__SECRET__', $secret, $token_url);
        $token_url = str_replace('__CODE__', $code, $token_url);
        $response = file_get_contents_ex($token_url);
        $_result = json_decode($response,true);
        /*hubing*/
//            var_dump($_result);
//            die();
        /*end*/

        if (isset($_result['errcode'])) { return false; }

        $_SESSION ["WechatAuthAccessToken"] = $_result ["access_token"];
        $_SESSION ['WechatAuthExpiresIn'] = time() + $_result ['expires_in'];
        $_SESSION ['WechatAuthOpenId'] = $_result ['openid'];
        return $_SESSION ['WechatAuthOpenId'];
        //}
        //return false;
    }

    // -------------------------------------------------------------------------------------------

    // 加载公告
    private function loadAnnounce(){
        $this->dao = M("Announce");
        $this->_list(array('language'=>$this->lang),'last_update desc',C('NUM_PER_PAGE'));
        $this->assign('AnnounceList',$this->view->get('list'));
    }

    // -------------------------------------------------------------------------------------------
    // 加载广告
    private function loadAd(){
        $this->dao = M("AdBanner");
        $this->_list(array('status'=>1, 'type'=>10, 'language'=>$this->lang),'sort asc',C ( 'NUM_PER_PAGE' ));
        $this->assign('AdList',$this->view->get('list'));
    }

    // -------------------------------------------------------------------------------------------
    // 运单跟踪
    public function track(){
        $this->dao = M('ExpressLog');
        if(isset($_REQUEST['code']) && (trim($_REQUEST['code']) != '') ){
            $code = trim($_REQUEST['code']);
            $this->_list("trace_code='$code'",'create_at asc',50);
            $this->assign('trace_code',$code);
        }

        $this->assign('openid',$_REQUEST['openid']);
        $this->display();
    }
//hubing start
//主页
    public function  home(){
        /*start*/
        $this->assign('headerType',"H");
        $HelpList = M ( 'Help' )->field('id,title')->where ( 'category_id=11' )->limit ( '1,5' )->order ( 'sort asc' )->select ();
        $AnnounceList = M ( 'Announce' )->field('id,title,last_update')->order ( 'last_update desc' )->limit ( '0,4' )->select ();
        $this->assign ( 'AnnounceList', $AnnounceList );
        $this->assign ( 'HelpList', $HelpList );
        $this->display('index');
        /*end*/
    }
    /*我的送货车*/
    public function  cart(){
        $this->assign('topContent','我的送货车');
        $DAO = M ( 'ShippingCart' );
        $condition = 'user_id=' . $this->user ['id'];
        $count = $DAO->where ( $condition )->count ();
        if ($count > 0) {
            $p = new Page ( $count, 8 ); // C ( 'NUM_PER_PAGE' )
            $p->setConfig ( 'first', '1' );
            $p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
            $page = $p->show ();
            $DataList = $DAO->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_at desc' )->select ();
            $this->assign ( 'DataList', $DataList );
            $this->assign ( 'page', trim($page) );
        }
        $this->display ();
    }

    /*展示待打包商品*/
    public function cartStep2(){
        $this->assign('topContent','打包运送');
        $DAO=M ('ShippingCart');
        $IdAry = $_REQUEST['id'];
        $ids = $this->buildIdstr ( $IdAry );
        if ($ids) {
            $weight_total = 0;
            $package_weight = 0;
            $count = $DAO->where("id in ($ids) AND user_id=" . $this->user ['id'])->count();
            $DataList = $DAO->field('title,count,img,total_weight,type')->where("id in ($ids) AND user_id=" . $this->user ['id'])->select();
            $this->assign('DataList', $DataList);
            $this->assign('count', $count);
            //计算商品总重量
            $weight_total = $this->computeProductWeight($ids); //商品重量
            $package_weight = $weight_total * 0.1; //包装重理
            $this->assign('weight_total', $weight_total);
            $this->assign('package_weight', $package_weight);
            $this->assign('ids', $ids); //传递打包商品id列表
            $this->display();
        }else{
            $this->actionFail();
        }

    }
    /*选择运输方式*/
    public function  cartStep3(){
        $this->assign('topContent','选择运输方式');
        $ids = trim ( $_POST ['ids'] );
            if($ids) {
                $countryList = M('DeliverZone')->where('status = 1')->order('sort asc')->select();
                //计算商品总重量
                $package_weight = $this->computeProductWeight($ids);
                $this->assign('PackageWeight', $package_weight * 1.1);
                $this->assign('CountryList', $countryList);
                $this->assign('InsureRate', $this->getInsureRate());
                $this->assign('ids', $ids); //将需打包商品id列表回传
                $this->display();
            }else{
                $this->actionFail();
            }
    }
//    选择收货地址
    public function cartStep4(){
        $this->assign('topContent','选择收货地址');

        $ids = trim ( $_POST ['ids'] );
        if ($ids ) {
            $wid = trim ( $_POST ['way_id'] ); //送货方式
            $insureTag = trim ( $_POST ['insure'] );

            $package_weight = floatval ( $this->computeProductWeight ( $ids ) * 1.1 );
            $product_fee = $this->computeProductFee ( $ids );
            $EntityFee = $this->doComputeFee ( $wid, $package_weight, $ids, $insureTag );
            $EntityFee ['productFee'] = $product_fee;
//            $EntityFee ['discount_fee'] = $_POST['discount_fee'];//抵扣的金额
//            $EntityFee ['ticket_code'] = trim($_POST['ticket_code']); //优惠券
            $EntityFee ['totalFee'] = $EntityFee ['totalFee'] - $_POST['discount_fee'];
            $InsureRate = $this->getInsureRate (); //保险比例
            $address = M('DeliverAddress')->where ( "id=$wid" )->find ();
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
//                $this->assign ( 'shipping_templete', $this->shipping_tpl[strtolower ( $address ['shipping_way'] )] );
            } else {
                $this->actionFail();
            }
        }
        $this->display();
    }
//    提交运单

    public function  cartCommit(){

        $ids = trim ( $_POST ['ids'] );
        $feeToken = trim ( $_POST ['fee_token'] );
        $shippingToken = trim($_POST['shipping_token']);

        if (empty ($feeToken) || empty($shippingToken)) { $this->actionFail();return; }
        $feeEntity = $this->DecodeInfo($feeToken);
        $shippingEntity = $this->DecodeInfo($shippingToken);
        if(empty($feeEntity) || empty($shippingEntity)){ $this->actionFail() ; return;}
        $packageWeight = floatval ( trim ( $_POST ['weight'] ) );
        $packageWeight = round ( $packageWeight, 2 );
//        var_dump($packageWeight);die;

        if ($this->user && $ids && is_numeric ( $packageWeight ) && ($packageWeight > 0) && $shippingEntity && $feeEntity && ($feeEntity ['totalFee'] > 0)) {

            $deliverId = intval($shippingEntity['deliver_id']);
            //运输方式不对，或超过限重
            if ( ($deliverId == 0) || ($packageWeight > $this->getLimitWeight ( $deliverId ) ) ) { $this->actionFail(); return;}

            //余额不够
            if (! $this->checkFinance ( $this->user ['id'], $feeEntity ['totalFee'] )) { $this->actionFail(); return; }
            //余额够结算
            //--------------------------------------------------------------------------------------
            // 1, 配送信息,送货方式，国家等
            $_deliverInfo = $this->buildDeliverInfo($this->user,$deliverId, trim ( $_POST ['zone_id'] ), $shippingEntity);
            if($_deliverInfo == false) { $this->actionFail(); return;}

            // 2, 收货人信息
            $_address = $this->processAddress( $_POST ['address_id']);
            if(empty($_address)){ $this->actionFail(); return; }

            //--------------------------------------------------------------------------------------
            // 3, 费用
            $_feeInfo = $this->buildFeeInfo($packageWeight, $this->countProduct ( $ids ), $_POST ['serve_rate'], $feeEntity, trim ( $_POST ['note'] ), $_POST ['insure_rate'], $_POST ['serve_cut']);
            if($_feeInfo == false) {$this->actionFail(); return;}

            //--------------------------------------------------------------------------------------------
            // 4, 保存打包时的运费价格 ( 用于区别，因调整运输方式价格时的情况 )
            $_wayPrice = $this->getWayPrice(intval(trim($deliverId)));
            if($_wayPrice == false){ $this->actionFail(); return;}

            //--------------------------------------------------------------------------------------------
            // 5, 写入包裹信息
            $package = array_merge($_deliverInfo,$_address, $_feeInfo,$_wayPrice);
            $oid =M ('Package')->data ( $package )->add ();
            //--------------------------------------------------------------------------------------------
            // 6, 生成包裹信息并进行财务结算
            if ($oid > 0) {
                $this->doFinace ( $this->user ['id'], $this->user ['login_name'], $oid, $feeEntity ['totalFee'] );

                //置优惠券已使用标志
//                $this->processTicket($this->user ['id'],$feeEntity ['ticket_code'] );
                $this->updateProductStatus ( $ids, $oid ); //更新商品状态
                $this->actionSuccess('parcel') ;
            } else {
                $this->actionFail();
            }
        } else {
            $this->actionFail();
        }
    }
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
    //从送货车删除
    private function delfromShippingCar($pid,$type,$uid){
        $DAO = new Model();
        $DAO->execute("DELETE FROM shipping_cart WHERE product_id=$pid AND type=$type AND user_id=$uid");
    }
    // 更新商品信息
    private function updateProduct($sql){
        $DAO = new Model();
        $DAO->execute($sql);
    }
    // 更新商品状态,并写入包裹号,这里的ids是shipping_cart的ids
    private function updateProductStatus($ids, $pgid) {
        $DataList = M ('ShippingCart')->where ( "id in ($ids)" )->select ();
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
    //包裹结算 ,oid 包裹编号
    private function doFinace($uid, $un, $oid, $total) {
        $finance = D( 'Finance' )->finace($uid);
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
            D( 'Finance' )->updateInfo($finance); //扣余额
            $remark = '包裹运输费用'; // . $total.'元,其中扣除现金帐户:'.$money_use.',扣除折扣帐户:'.$rebate_use;
            $this->writeFinaceLog ( $uid, $un, 0, $oid, $money_use, $money_befor, $reabet_befor, $finance ['point'], $remark, $rebate_use, 301 );
        }
    }
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
    // 取线路价格
    private function getWayPrice($wid){
        if($wid && is_numeric($wid)){
            $data = M('DeliverAddress')->field('start_price,continue_price')->where('id='.$wid)->find();
            return (!empty($data))?$data:false;
        }
        return false;
    }
    // 统计送货车商品数量
    private function countProduct($ids) {
        return ($ids) ?  M ('ShippingCart')->where ( "id in ($ids)" )->sum ( 'count' ) : 0;
    }
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
//                'ticket_amount'		=> 0.00,
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
    // 构造打包时的配送信息
    private function buildDeliverInfo($user,$did,$zid,$shp){
        if(empty($did) || empty($zid) || empty($shp) ) return false;
        return array('user_id' 		=> $user['id'],
            'user_name' 	=> $user['login_name'],
            'zone_id' 		=> $zid,
            'deliver_id' 	=> $did,
            'deliver_way' => urldecode(trim($shp['shipping_way'])),
            'deliver_area' => trim($shp['deliver_area']) );
    }
    //检查帐户余额是否足够结算
    //2013.5.12 by stone 只检查现金帐户余额
    private function checkFinance($uid, $total) {
        $finance = D( 'Finance' )->finace( $uid );
        return ($finance && ($finance ['money'] >= $total) )?true:false;
    }
    //取得限重
    private function getLimitWeight($id) {
        if (!is_numeric ( $id )) { return 2000; }
        $item =  M('DeliverAddress')->where ( "id=$id" )->find ();
        return ($item && is_numeric($item ['limit_weight'])) ? $item ['limit_weight'] * 1000 : 2000;
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
    //保险费比例
    private function getInsureRate() {
        $InsureRate = M ( 'FinaceConfig' )->where ( "item='" . C ( 'INSURE_RATE' ) . "'" )->find ();
        return ($InsureRate && ($InsureRate ['value'] > 0)) ? $InsureRate ['value']:5;
    }
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
            $DataList =  M('DeliverAddress')->where ( "status = '1' AND zone_id=$zid" )->order ( 'id' )->select ();

            foreach ( $DataList as $key => $value ) {
                if (($weight <= 8000) && ($value ['shipping_way'] == '海运')) {
                }elseif( ($weight <10100) && (trim($value ['shipping_way']) == '专线11-100kg') ){
                }elseif( ($weight >10000) && (trim($value ['shipping_way']) == '专线11kg以内') ){
                }elseif( ($weight <12000) && (trim($value ['shipping_way']) == '12Kg以上大货专线') ){
                }elseif( ($weight <21000) && (trim($value ['shipping_way']) == '21Kg以上大货专线') ){
                }elseif( ($weight <4000) && (($value ['shipping_way'] == 'SAL水陆联运') || ($value ['shipping_way'] == 'AIR 2kg以上') ) ){
                }else{
                    $result .= '<tr><td align="left" bgcolor="#FFFFFF"><input type="radio" name="pg_shipping_method[]"  value="' . $value ['id'] . '" onclick="shipping( ' . $value ['id'] . ');setLimitWeight(' . $value ['limit_weight'] * 1000 . ');" />' . $value ['shipping_way'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['start_price'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_weight'] . '</td>' . '<td bgcolor="#FFFFFF">' . $value ['continue_price'] . '</td>' . '<td bgcolor="#FFFFFF" style="color:#f60;font-weight:bold;">' . $value ['limit_weight'] . '</td></tr> ';
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
//    商品转运 计算费用
    public function computefee() {
        $id = trim ( $_POST ['wid'] );
        $weight = trim ( $_POST ['pw'] );
        $ids = trim ( $_POST ['ids'] );
        $insure = trim ( $_POST ['insure'] );
        //是否参加保险
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
    // 取报关费
    private function getCustomFee($id) {
        if (!is_numeric($id) ) { return 8; }
        $way =  M('DeliverAddress')->where ( "id=$id" )->find ();
        return ($way && is_numeric($way ['customfee']) ) ? $way ['customfee'] : 8;
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
    //计算打包商品的金额
    private function computeProductFee($ids) {
        return ($ids) ?  M ('ShippingCart')->where ( "id in ($ids) AND type=1" )->sum ( 'product_fee' ) : 0; //只统计代购商品结算时金额
    }
    //计算运费部份的服务费
    private function doShippingServiceFee($wid, $shippingFee) {
        if ($wid && $shippingFee) {
            $way = M('DeliverAddress')->where ( "id=$wid" )->find ();
            $serviceRate =($way && $way ['rate'] > 0) ?  $way ['rate'] : $this->getServiceRate () ;
            return ($shippingFee * $serviceRate) / 100;
        }
        return 0;
    }
// 服务费
    private function getServiceRate() {
        $entity = M ( 'FinaceConfig' )->where ( "item='serve_rate'" )->find ();
        return ($entity && is_numeric($entity ['value'])) ?$entity ['value']:0;
    }
    private function getShippingServiceRate($wid){
        if ($wid) {
            $way = M('DeliverAddress')->where ( "id=$wid" )->find ();
            $serviceRate = ($way && is_numeric($way ['rate']) ) ?  $way ['rate'] :0 ;
            return $serviceRate;
        }
        return 0;
    }

    //检查代购部份商品服务费
    private function checkDaigouProductServiceFee($ids,$wid) {
        $result = 0;
        if ($ids && ($ids != '')) {
            $DataList = M ('ShippingCart')->field ( 'product_fee,service_rate' )->where ( "id in ($ids) AND type=1 AND service_fee=0" )->select ();
            foreach ( $DataList as $item ) {
                $serviceRate = $this->getShippingServiceRate($wid);//$item ['service_rate'];
                $result = $result + ($item ['product_fee'] * $serviceRate) / 100;
            }
        }
        return $result;
    }
    //根据重量和运输方式计算运费
    private function doShippingFee($wid, $weight) {
        $result = 0;
        if ($wid && $weight) {
            $way = M('DeliverAddress')->where ( "id=$wid" )->find ();
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
    //计算打包商品总重量
    private function computeProductWeight($ids) {
        return ($ids) ? floatval ( M ('ShippingCart')->where ( "id in ($ids)" )->sum ( 'total_weight' ) ) : 0;
    }
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

    /*会员中心*/
    public function member(){
        $userInfo=M('User')->find($this->user['id']);
        $userInfo['head_img']=$userInfo['head_img']=="" ? "/Ulowi/Tpl/default/Public/images/avatar.png" : "/Uploads/pic/avatar/".$userInfo['head_img']."_m.jpg";
        $this->assign('userInfo',$userInfo);
        $this->assign('headerType',"M");
        $this->display();
    }
    //会员详细信息
    public function  memberInfo(){
        $this->assign('topContent','我的信息');
        $finance = D ( 'Finance' )->finace ( $this->user ['id'] );
        if ($finance) {
            $this->assign ( 'balance', $finance ['money'] ); // 余额
            $this->assign ( 'consumption_total', $finance ['consumption_total'] );
        }
        $userInfo=M('User')->find($this->user['id']);
        $userInfo['head_img']=$userInfo['head_img']=="" ? "/Ulowi/Tpl/default/Public/images/avatar.png" : "/Uploads/pic/avatar/".$userInfo['head_img']."_m.jpg";
        $this->assign('userInfo',$userInfo);
        $this->display('member_info');


    }
    //绑定用户到微信公众号
    public function bindViatang(){
        if($_SERVER['REQUEST_METHOD']=="POST"){
            $map['is_qquser']=2;
            $map['qq_openid']=$_REQUEST['qq_openid'];
            $username=trim($_REQUEST['login_name']);
            $password = htmlspecialchars ( trim ( $_REQUEST["password"] ) );
            $userEmail = base64_encode ( ulowi_encode ($username));
            $user = M ( "User" )->where ( "(active_status=1) AND (status=1) AND (is_qquser!=2)  AND (login_name='$username' OR email2='$userEmail' ) " )->find ();
            $password1 = md5($password);
            $password2 = ($user['salt']=='')?$password1:md5($password1.$user['salt']);
            if (!empty($user) &&  ($user['password'] == $password2 )) {
                $condition['login_name']=$username;
                $condition['password']=$password2;
                M('User')->where($condition)->save($map);
                $this->redirect('We/member');
            } else {
                $this->actionFail('member',3);
            }
        }else{
            $this->assign('topContent','绑定用户');
            $this->assign('openId',$_REQUEST['myId']);
            $this->display();
        }
    }
    // 到库查询
    public function arriveQuery(){
        $this->assign('topContent','到库查询');
        $this->display();
    }
//    到库查询 页面ajax
    public  function checkArrive(){
        $condition['trace_no']=$_REQUEST['id'];
        $res=M('ProductAgent' )->where($condition)->select();
        $back['res_code']=0;
        if($res==null){
            $back['res_code']=1;
        }
        $back['trace_no']=$res[0]['trace_no'];
        $back['title']=$res[0]['title'];
        $back['status']=$this->getStatus($res[0]['status']);
        $back['shipping_company']=$res[0]['shipping_company'];
        echo   json_encode($back);
    }
//   根据清单状态码返回中文提示
    private   function  getStatus($id){
        $message=null;
        switch ($id){
            case 0:
                $message="等待收货";
                break;
            case 1:
                $message="已到货";
                break;
            case 2:
                $message="问题商品";
                break;
            case 3:
                $message="退货处理中";
                break;
            case 4:
                $message="已退货";
                break;
            case 5:
                $message="已入库";
                break;
            case 8:
                $message="换货处理中";
                break;
            case 9:
                $message="退货补运费中";
                break;
            case 10:
                $message="换货补运费中";
                break;
            default:
                $message="服务异常";
        }
        return $message;
    }

    // 提交包裹清单
    public function commitPackage() {
        if($_SERVER['REQUEST_METHOD']=='POST') {
//        $user = Session::get ( C ( 'MEMBER_INFO' ) );
            $user=$this->user;
//            $user = array('id' => 9358, 'login_name' => 'wsh111');
            if ($user) {
                $product ['user_id'] = $user ['id'];
                $product ['user_name'] = $user ['login_name'];
                $product ['title'] = trim($_POST ['title']);
                $product ['count'] = trim($_POST ['productNum']);
                $product ['count'] = (!is_numeric($product ['count']) || ($product ['count'] <= 0)) ? 1 : $product ['count'];
                $shiping_commpany = trim($_POST ['shipingCompany']);
                $trace_no = trim($_POST ['traceNo']);
                $shiping_commpany = (strlen($shiping_commpany) > 1) ? ltrim($shiping_commpany, '-') : $shiping_commpany;
                $shiping_commpany = (strlen($shiping_commpany) > 1) ? rtrim($shiping_commpany, '-') : $shiping_commpany;
                $trace_no = (strlen($trace_no) > 1) ? ltrim($trace_no, '-') : $trace_no;
                $trace_no = (strlen($trace_no) > 1) ? rtrim($trace_no, '-') : $trace_no;
                $trace_no = preg_replace('/[ ]/', '', $trace_no);
                $trace_no = trim($trace_no);
                $product ['remark'] = trim($_POST ['productRemark']);
                $product['order_bat_id'] = time();
                $product ['send_time'] = !empty($_POST ['express_date']) ? strtotime($_POST ['express_date']) : time();
                $product ['create_at'] = time();

                $product ['shipping_company'] = trim($shiping_commpany);

                $_count = $this->countItemByTraceno(trim($trace_no));

                $_do = false;
                $_message = '您提交的包裹运单号已存在，请勿重复提交!';
                $DAO = M('ProductAgent');
                switch ($_count) {
                    case 0:
                        $product ['trace_no'] = trim($trace_no);
                        $_id = $DAO->data($product)->add();
                        $admin = array('id' => 0, 'loginame' => '');
                        $this->writeProductAgentLog($_id, 0, $user['id'], $user['login_name'], '用户自主提交包裹', $admin);
                        $_do = true;
                        $_message = '恭喜，提交成功!您可在：个人中心-转运商品管理 中查看详情';
                        break;

                    case 1:
                        $item = $this->loadItemByTraceno(trim($trace_no));
                        if ($item) {
                            if ($item['user_id'] == 0) {//无主包裹
                                unset($product ['create_at']);// 禁更新创建日期
                                unset($product ['count']);// 禁更新数量
                                unset($product['order_bat_id']);

                                $DAO->where("id=" . $item['id'])->save($product);
                                $admin = array('id' => 0, 'loginame' => '');
                                $this->writeProductAgentLog($item['id'], $item['status'], $item['user_id'], $item['user_name'], '用户(后)提交转运包裹信息，系统自动关联后并更新包裹重量商品名称等信息', $admin);
                                $_do = true;
                                $_message = '您提交的包裹已成功签收，现已自动关联到您的名下，您可在：个人中心-转运商品管理 中查看详情';
                            } elseif ($item['user_id'] == $user ['id']) {
                                if ($item['status'] == 0) {//等待收货, 可以修改全部数据
                                    $product ['trace_no'] = trim($trace_no);
                                    unset($product ['user_id']);
                                    unset($product ['user_name']);
                                    $_id = $DAO->where("id=" . $item['id'])->save($product);
                                    $admin = array('id' => 0, 'loginame' => '');
                                    $this->writeProductAgentLog($_id, $item['status'], $user['id'], $user['login_name'], '等待收货”订单，被用户成功更新包裹信息', $admin);
                                    $_do = true;
                                    $_message = '恭喜您，您提交的转运商品已更新成功!您可在：个人中心-转运商品管理 中查看详情';
                                } else {
//                                    $this->writeProductAgentLog($_id, $item['status'], $user['id'], $user['login_name'], '客户尝试修改处理中订单，系统已拒绝', $admin);
                                    $_do = false;
                                    $_message = '您提交的包裹已为您关联到账户中，您可在：个人中心-转运商品管理 中查看详情';
                                }
                            }
                        }
                        break;
                }
                $this->pageJump($_do,"commitPackage",3);
            }
        }else{
            $list = M('DeliverCompany')->select();
            $this->assign('topContent', '提交包裹清单');
            $this->assign('list', $list);
            $this->display();
        }
    }

    /*提交包裹清单相关*/
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
    /*提交包裹清单相关*/

    //加入送货车

    public function addtocart() {
        $IdAry = $_POST ['id'];
        $user_id = $this->user ['id'];
//            $user_id = 9358;
//            $user_name = "wsh111";
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
//            $this->redirect('We/goodM',array('op'=>'arrive'));
        $this->actionSuccess("goodM?op=arrive",3);
//            $this->success('已成功加入送货车!');


    }

    //修改商品状态

    private function setStatus($pid,$sta,$uid){
        $DAO = new Model();
        $DAO->execute("UPDATE product_agent SET status=$sta WHERE id=$pid  AND user_id=$uid");
    }

    // 物流查询
    public function waybillSearch(){
        $this->assign('topContent','国际快递查询');
        $this->display();
    }

    // 仓库地址
    public function vtaddress(){
        $this->assign('topContent','唯唐地址');
        $this->display();
    }

    // 用户地址管理
    public function address(){
        $request=$_REQUEST;
        $op=$request['op'];
        $DAO = M ( 'Address' );
        $condition=array('id'=>$request['id']);
        if($op=='edit'){
            $address=$DAO->where($condition)->select();
            $address=$address[0];
            $this->assign('address',$address);
            $this->assign('topContent','编辑地址');
            $this->display('address_edit');
        }elseif ($op=='delete'){
            $flag=$DAO->where($condition)->delete();
            $this->pageJump($flag,"address");
        }elseif($op=='add'){
            $this->assign('topContent','新增地址');
            $this->display('address_add');
        }elseif($op=='update'){
            $map = array();
            $keys = array('contact', 'phone', 'state', 'city', 'address', 'zip');
            for ($flag = 0; $flag < count($keys); $flag++) {
                $map[$keys[$flag]] = $_REQUEST[$keys[$flag]];
            }
            $country=explode("|",$_REQUEST['country']);
            $map['deliver_id']=$country[0];
            $map['country']=$country[1];
            $type=$_REQUEST['type'];
            if($type=='add') {
                $map['user_id'] = $this->user['id'];
//                $map['user_id'] =9358;
                $map['user_name'] = $this->user['login_name'];
//                $map['user_name'] = "wsh111";
                $flag=$DAO->data($map)->add();
                $this->pageJump($flag,"address");
            }elseif ($type=='edit'){
                $flag=$DAO->where($condition)->save($map);
                $this->pageJump($flag,"address");
            }
        }else{
            $this->assign('topContent','地址管理');
            $con=array('user_id'=>$this->user['id']);
            $DataList = $DAO->where($con)->limit(10)->select ();
            $this->assign ( 'DataList', $DataList );
            $this->display ();
        }
    }
    /*页面跳转*/
    public  function  pageJump($boll,$url,$time=3){
        if ($boll==true){
            $this->actionSuccess($url,$time);
        }else{
            $this->actionFail($url,$time);
        }
    }
    function  actionSuccess($url,$time=3){
        $this->assign('jumpUrl',$url);
        $this->assign('second',$time);
        $this->display('success');
    }
    function  actionFail($url='home',$time=3){
        $this->assign('jumpUrl',$url);
        $this->assign('second',$time);
        $this->display('error');
    }
    /*页面跳转*/
    //商品管理
    public function goodM(){
        $this->assign('topContent','商品管理');
        $module=$_REQUEST['op']=="" ? "not"  :$_REQUEST['op'];
        $DAO = M ( 'ProductAgent' );
        $user_id=$this->user['id'];

        $condition = "(status != 5) AND (status != 6) AND (status != 7) AND user_id=$user_id";
        $arrivedCount=$DAO->where("status=5 and user_id=$user_id")->count();
        $count = $DAO->where ( $condition )->count ();
        $this->assign('count',$count);
        $this->assign('arrivedCount',$arrivedCount);
        switch ($module){
            case "not":
                $p = new Page ( $count, 8 );
                $p->setConfig ( 'first', '1' );
                $p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
                $page = $p->show ();
                $DataList = $DAO->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'order_bat_id desc' )->select ();
                $this->assign ( 'DataList', $DataList );
                $this->assign ( 'page', trim($page) );
                $this->display ();
                break;
            case "arrive":
                $p = new Page ( $arrivedCount, 8 );
                $p->setConfig ( 'first', '1' );
                $p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
                $page = $p->show ();
                $DataList=$DAO->where("status=5 and user_id=$user_id")->limit ( $p->firstRow . ',' . $p->listRows )->select();
                $this->assign('DataList',$DataList);
                $this->assign ( 'page', trim($page) );
                $this->display('goodM_arrive');
                break;
            case  "del":
                $id=$_REQUEST['id'];
                $flag=$DAO->where("id=$id")->delete();
                $this->pageJump($flag,"goodM");
                break;
            case  "edit":
                $this->assign('topContent','商品修改');
                $traceId=$_REQUEST['id'];
                $result=$DAO->where("id=$traceId")->select();
                $this->assign('Data',$result[0]);
                $this->display('goodM_edit');
                break;
            case "update":
                $id=$_REQUEST['id'];
                $map['remark']=$_REQUEST['remark'];
                $flag=$DAO->where("id=$id")->save($map);
                $this->pageJump($flag,"goodM");
                break;
        }

    }

    // 我的包裹
    public function parcel() {
        $this->assign('topContent','我的包裹');
        $this->dao = M ( 'Package' );
        $user_id=$this->user['id'];
        $count = $this->dao->where ( "status<>7 AND user_id=$user_id" )->count ();
        if ($count > 0) {
            $p = new Page ( $count, 8 );
            $p->setConfig ( 'first', '1' );
            $p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
            $page = $p->show ();
            $DataList = $this->dao->where ( "status<>7 AND user_id=$user_id")->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
            $this->assign ( 'DataList', $DataList );
            $this->assign ( 'page', trim($page) );
            $this->assign('userId',$user_id);
        }
        $this->display ();
    }


//     包裹评论
    public function comment(){
//        $user_id=$this->user['id'];
//        $login_name=$this->user['login_name'];
        if($_SERVER['REQUEST_METHOD']=='POST'){
            $id=explode('.',trim($_REQUEST['id']));
            $id=$id[0];
            $PackageDAO = M('Package');
            $package = $PackageDAO->where("id=$id")->find();
            if($package){
                $data['package_id'] 	= $id;
                $data['package_no'] 	= $package['package_code'];
                $data['zone_id'] 	= $package['zone_id'];
                $data['way_id'] 	= $package['deliver_id'];
                $data['way_name'] 	= $package['deliver_way'];
                $data['country']	= $package['country'];
//                $data['user_id'] 	= $user_id;
                $data['user_id'] 	= $this->user['id'];
//                $data['user_name']	= "wsh111";
                $data['user_name']	= $this->user['login_name'];
                $data['content']	= trim($_REQUEST['review']);
                $data['ip']		= get_client_ip();

                $data['reply_content']	= '';
                $data['reply_time']	= 0;
                $data['admin_id']	= 0;
                $data['admin_name']	= '';
                $data['is_display']	= 0;
                $data['create_time']	= time();
                M('Package')->where("id=$id")->save(array('had_review'=>1));
                $flag=M('Comment')->data($data)->add();
                $this->pageJump($flag,"parcel");
            }
        }else{
            $this->assign('topContent','服务评论');
            $this->assign('id',$_REQUEST['id']);
            $this->display();
        }
    }
    // 撤销包裹
    public function cancel() {
        if ($this->user && isset( $_REQUEST ['id']) ) {
            $id=explode(".",$_REQUEST['id']);$id=intval($id[0]);
            $result = $this->updatePackageCancel ( $id, $this->user ['id'] );
            if ($result) {
                $this->writePackageLog ( $id, 7, 0, '', '用户撤销包裹' );
                $this->setProductYrk ( $id );
                $this->refund ( $id,0 ); //最后退款
            }
        }
        $this->redirect ( 'We/parcel' );
    }
    //退指定编号包裹的费用
    private function refund($id,$reserve_fee=0) {
        $package = M ('Package')->where ( "id=$id" )->find ();
        if ($package && ($package ['refund_flag'] == 0)) {
            $uid = $package ['user_id'];
            $un = $package ['user_name'];
            $refund = $package ['total_fee'] - $reserve_fee; //扣除手续费
            $finance = D( 'Finance' )->finace ( $package ['user_id'] );

            if ($finance) {
                $money_befor = $finance ['money'];
                $reabet_befor = $finance ['rebate'];
                $finance ['money'] = $finance ['money'] + $refund;
                $finance ['consumption_total'] = $finance ['consumption_total'] - $refund;
                $finance ['last_update'] = time ();

                //更新帐户余额
                D( 'Finance' )->where ( 'id=' . $finance ['id'] )->save ( $finance );

                //更新包裹已退款标志
                $package ['refund_flag'] = 1;
                M ('Package')->where ( 'id=' . $package ['id'] )->save ( $package );

                $remark = L('package_cancel_return');
                $this->writeFinaceLog ( $uid, $un, 0, $package ['id'], $refund, $money_befor, $reabet_befor, $finance ['point'], $remark, 0, 302 );
            }
        }
    }
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
    //更新包裹状态为已撤销
    private function updatePackageCancel($pgid, $uid) {
        $result = false;
        if ( $pgid  && $uid) {
            $package = M ('Package')->where ( "id=$pgid AND user_id=$uid" )->find ();
            if ($package && ($package ['status'] != 7)) {
                $package ['status'] = 7;
                $package ['last_update'] = time ();
                M ('Package')->where ( "id=$pgid" )->save ( $package );
                $result = true;
            }
        }
        return $result;
    }
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


//    hubing end
    // -------------------------------------------------------------------------------------------
    // 公告详情
    public function detail(){
        $this->dao = M('Announce');
        $this->_load();
        $this->assign("item",$this->view->get('vo'));
        $this->assign('openid',$_REQUEST['openid']);
        $this->display();
    }
    // -------------------------------------------------------------------------------------------
    // 微信入口
    public function index() {
        $WechatClient = !empty($this->wechat_token) ? new Wechat ( $this->wechat_token ) : false;
        $WechatRequest = ($WechatClient) ? $WechatClient->request () : false;
        if ($WechatRequest && is_array ( $WechatRequest)) {
            $_openid = $WechatRequest ['FromUserName'] ;

            if (strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_EVENT) {
                if (strtoupper ( $WechatRequest['Event'] ) == Wechat::MSG_EVENT_CLICK) {
                    if ($this->judgeUserByOpenid ($_openid) != false) {
                        $content = $this->eventRoute($WechatRequest,$_openid);
                    } else {
                        $content = '为了更好地为您服务，请先'
//									 . '<a href="'. $this->_server_url . 'register.html?oid='.$_openid.'">'
                            . '绑定系统帐号哦';
//									 . '</a>';
                    }
                    $WechatClient->replyText ( $content );
                }
            }elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_TEXT){
//				    暂不考虑其他关键字
                $content=$WechatRequest['Content'];
                $content= $content== "公众号测试" ? $this->_server_url."home" : "唯唐代购欢迎您";
                $WechatClient->replyText($content);

            }elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_IMAGE){
                $WechatClient->replyText("viatang");


            }
        }

//			$WechatClient = !empty($this->wechat_token) ? new Wechat ( $this->wechat_token ) : false;
//			$WechatRequest = ($WechatClient) ? $WechatClient->request () : false;
//
//			 if ($WechatRequest && is_array ( $WechatRequest)) {
//				$_openid = $WechatRequest ['FromUserName'] ;
//
//				if (strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_EVENT) {
//					if (strtoupper ( $WechatRequest['Event'] ) == Wechat::MSG_EVENT_CLICK) {
//						if ($this->getUserByWechatOpenId ($_openid) != false) {
//							$content = $this->eventRoute($WechatRequest,$_openid);
//						} else {
//							$content = '为了更好地为您服务，请先'
//									 . '<a href="'. $this->_server_url . 'register.html?oid='.$_openid.'">'
//									 . '绑定系统帐号哦'
//									 . '</a>';
//						}
//
//						$WechatClient->replyText ( $content );
//					}
//				}elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_TEXT){
//
//				}elseif(strtolower ( $WechatRequest ['MsgType'] ) == Wechat::MSG_TYPE_IMAGE){
//
//				}
//		}

    }
    /*问题回复*/
    public  function   showHelp(){
        $DAO = D('Help');
        $op=$_REQUEST['op'];
        switch ($op){
            case 'more':
                $this->assign('topContent','常见问题列表');
                $HelpList = M ( 'Help' )->where ( 'category_id=11' )->limit ( '1,20' )->order ( 'sort asc' )->select ();
                $this->assign('HelpList',$HelpList);
                $this->display();
                break;
            case "detail":
                $this->assign('topContent','问题详情');
                $id=$_REQUEST['id'];
                $entity = $DAO->where("id=$id")->find();
                $this->assign('faq',$entity);
                $this->display('help_detail');
        }
    }

    public function news(){
        $this->assign('topContent','新闻公告');
        $id=$_REQUEST['id'];
        $news=M ( 'Announce' )->find ($id);
        $this->assign('news',$news);
        $this->display();

    }
    // -------------------------------------------------------------------------------------------
    // 显示文章
    public function article(){
        $item = M('Help')->where("id=".$_REQUEST['did'])->find();
        $this->assign('item',$item);
        $this->assign('title',$item['title']);
        $this->assign('openid',$_REQUEST['openid']);
        $this->display();
    }

    // -------------------------------------------------------------------------------------------
    // 咨询
    public function consulation(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('cosulation.html');
        }

        if($openid){
            $this->assign('openid',$openid);
            $this->user = $this->getUserByWechatOpenId($openid);
            $this->assign('user',$this->user);
        }
        $this->display();
    }

    public function doConsulation(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('cosulation.html');
        }

        if($openid){
            if( (trim ( $_POST ['help_title'] ) == '') || (trim ( $_POST ['help_content'] ) == '') ){
                $url = '/wechat/consulation.html?openid='.$openid;
                $this->disWechatMessage(L('lang_error'), $url,false,1);
                return;
            }

            $this->user = $this->getUserByWechatOpenId($openid);
            $this->assign('user',$this->user);

            $data ['user_id'] = $this->user ['id'];
            $data ['user_name'] = $this->user ['login_name'];
            $data ['title'] = trim ( $_POST ['help_title'] );
            $data ['content'] = trim ( $_POST ['help_content'] );
            $data ['ip'] = get_client_ip ();
            $data ['create_at'] = time ();
            $data ['status'] = '0';
            $data ['customer_reply_tag'] = 1;

            M("Consultation")->data ( $data )->add ();
            $url = '/wechat/consulation.html?openid='.$openid;
            $this->disWechatMessage(L('lang_success'), $url,true,1);
        }
    }

    // -------------------------------------------------------------------------------------------
    // 财务记录
    public function finance_log(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('finance_log.html');
        }

        if($openid){
            $this->assign('openid',$openid);
            $this->user = $this->getUserByWechatOpenId($openid);
            if($this->user){
                $this->dao = M ( 'FinanceLog' );
                $count = $this->dao->where ( "user_id=" . $this->user ['id'] )->count ();
                if ($count > 0) {
                    $p = new Page ( $count, 50 );
                    $p->setConfig('first','1');
                    $p->setConfig('theme','%upPage% %first%  %linkPage%  %downPage%');
                    $page = $p->show ();
                    $DataList = $this->dao->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
                    $this->assign ( 'DataList', $DataList );
                    $this->assign ( 'page', trim($page) );
                }
            }
            $this->display ();
        }
    }

    //---------------------------------------------------------------------------------------------//

    // -------------------------------------------------------------------------------------------
    // 根据关键词进行分发回复
    private function eventRoute($_r,$oid){
        $url = $this->_server_url.'wareaddress.html?oid='.$oid;
        switch ($_r['EventKey']){
            case 'user_reg' : $content = '您的微信已经与86OF绑定过了哦 !点击<a href="'.$url.'"  >这里</a>查看仓库地址';
                break;
            case 'MY_WAREHOUSE': $content = ' Please click to check my address >><a href="'.$url.'">CLICK HERE</a><<';
                break;
            default: $content = '建设中,敬请期待...';
        }
        return $content;
    }

    // --------------------------------------------------------------------------------------------
    // 根据微信openid 注册用户信息
    public function register(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('register.html');
        }

        if($openid){
            $this->assign('openid',$openid);
            if ($this->getUserByWechatOpenId($openid)) {
                $url = '/wechat/wareaddress.html?openid='.$openid;
                $this->disWechatMessage('您的微信已经与86OF绑定过了哦，可以直接点击相应的菜单，来享受86OF提供的各种服务啦!', $url,false,1);
            }else{
                $this->assign('openid',$openid);
                $this->display();
            }
        }else{
            $this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', '',false,0);
        }
    }

    // --------------------------------------------------------------------------------------------
    // 仓库地址
    public function wareaddress(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('wareaddress.html');
        }
        $this->assign('openid',$openid);
        $this->assign('user',$this->getUserByWechatOpenId($openid));
        $this->display();
    }


    // --------------------------------------------------------------------------------------------
    //加载当前微信用户的仓库信息
    public function warehouse(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('warehouse.html');
        }

        $user = $this->getUserByWechatOpenId($openid);
        if($user){
            $this->assign('user',$user);
            $where = (isset($_REQUEST['ware_id']) && $_REQUEST['ware_id']!='0')?" AND ware_id=".$_REQUEST['ware_id'] :'';
            $ware_id = isset($_REQUEST['ware_id']) ? $_REQUEST['ware_id'] : 0;

            $this->dao = M("Express");
            $list = $this->_list("status=7 AND user_id=".$user['id'].$where,'id asc', 300);
            $_volume = $this->dao->where('status=7 AND user_id='.$user['id'].$where)->sum('volume');
            $_count  = $this->dao->where('status=7 AND user_id='.$user['id'].$where)->count();

            $this->assign('_count',$_count);
            $this->assign('volume',$_volume);
            $this->assign('list',$list);

            $this->assign('ware_id',$ware_id);
        }

        $this->setWareList();
        $this->assign('openid',$openid);
        $this->display();
    }

    // --------------------------------------------------------------------------------------------
    // 合箱转运
    public function hxzy(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('hxzy.html');
        }
        $this->assign('openid',$openid);
        $user = $this->getUserByWechatOpenId($openid);
        $this->assign("user",$user);

        $idary = $_POST['id'];

        //过滤掉没有申报的商品
        if($this->checkExpressReport($idary)){
            $url ='/wechat/warehouse.html?openid='.$_POST['openid'];
            $this->disWechatMessage('存在未申报货品的包裹，请补全后再提交', $url,false,1);
        }

        $ware_id = $_POST['ware_id'];
        if(empty($ware_id)){
            $url ='/wechat/warehouse.html?openid='.$_REQUEST['openid'];
            $this->disWechatMessage('请先按仓库过滤后再操作', $url,false,1);
            return;
        }

        if( (count($idary)>0) && ($ware_id>0)  && $user){
            $ids = implode(',', $idary);
            $this->dao = M("Express");
            $this->_list("id in ($ids)",'id asc', C('NUM_PER_PAGE'));
            $this->assign('DataList',$this->view->get('list'));
            $_volume = $this->dao->where("id in ($ids)")->sum('volume');
            $_count = $this->dao->where("id in ($ids)")->count();
            $_value_total = $this->computeExpressValueSum($ids);
            $_value_gst_fee = $_value_total *  $this->getCustomFee();
            $_value_gst_fee = round($_value_gst_fee,2);

            $this->assign('ids',$ids);
            $this->assign('PackageVolume',$_volume);
            $this->assign('count',$_count);
            $this->assign('PackageValue',$_value_total);
            $this->assign('value_gst_fee',$_value_gst_fee);

            //收货地址本
            $this->assign('AddressList',$this->getReceiveAddressList($user['id']));

            //报价列表
            $this->assign('price_list',$this->computeWareFee($ware_id, $_volume,$_value_gst_fee));
            $this->assign('balance',$this->getBalance($user));
            $this->display();
        }else{
            $url ='/wechat/warehouse.html?openid='.$_REQUEST['openid'];
            $this->disWechatMessage('生成运单出错', $url,false,1);
        }
    }

    //---------------------------------------------------------------------------------------------
    //设置帐户余额
    private function getBalance($user){
        if($user){
            $vo = $this->finance->finace($user['id']);
            return ($vo)?$vo['money']:0;
        }
        return 0;
    }

    //---------------------------------------------------------------------------------------------
    // 加载用户收货地址
    private function getReceiveAddressList($uid){
        return ($uid && is_numeric($uid))? M('Address')->where("user_id=$uid")->select() : array();
    }


    //---------------------------------------------------------------------------------------------
    // 计算仓库的各种费用
    // $ware_id ：仓库ID，
    // $volume : 体积
    // $_value_gst_fee : 申报价值对应的消费税
    private function computeWareFee($ware_id,$volume,$_value_gst_fee){
        $deliver_price = $this->address->where("(ware_id=$ware_id) and (status=1) and ($volume <= limit_weight)")->select();
        foreach ($deliver_price as &$r){
            $shipping_fee = ($volume > $r['customer_special_unit']) ? $volume * $r['customer_volume_unit_price'] : $r['customer_special_unit_price'];
            $shipping_gst_fee = ($r['include_customefee'] == 1) ? $shipping_fee * $this->getCustomFee():0;
            $r['shipping_fee'] = round($shipping_fee,2);//运费
            $r['shipping_gst_fee'] = round($shipping_gst_fee,2);//消费税

            $r['gst_fee_total'] = round($_value_gst_fee + $shipping_gst_fee,2);
            $r['pay_total'] = round($shipping_fee + $shipping_gst_fee + $_value_gst_fee + $r['customfee'],2);
        }
        return $deliver_price;
    }

    //---------------------------------------------------------------------------------------------
    // 计算快递的申报价值
    private function computeExpressValueSum($ids){
        return ($ids!='')?$this->express->where("id in ($ids)")->sum('value'):0;
    }

    //--------------------------------------------------------------------------------------------


    // -------------------------------------------------------------------------------------------
    //检查包裹是否有申报过
    private function checkExpressReport($ids){
        $result = false;
        if(!empty($ids)){
            foreach($ids as $r){
                $_count = $this->goods->where("trans_id=$r")->count();

                if($_count == 0){
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    // --------------------------------------------------------------------------------------------
    // 预处理仓库列表
    private function setWareList(){
        $_list = M('Ware')->where('status=1')->select();
        $this->assign('WareList',$_list);
    }

    // --------------------------------------------------------------------------------------------
    // 预处理openid
    public function processOpenid($redirect_uri){
        $url = $this->getRequestCodeUrl($redirect_uri);
        header ( "Location:$url" );
    }

    // --------------------------------------------------------------------------------------------
    // 认领包裹
    public function claim(){
        if(isset($_REQUEST['openid']) || isset($_SESSION ['WechatAuthOpenId']) ){
            $openid = isset($_REQUEST['openid'])? $_REQUEST['openid'] : $_SESSION ['WechatAuthOpenId'];
        }else{
            $this->processOpenid('claim.html');
        }

        $user = $this->getUserByWechatOpenId($openid);
        $this->assign('user',$user);
        $this->assign('openid',$openid);
        $this->display();
    }

    // -------------------------------------------------------------------------------------------
    // 申报
    public function declaration(){
        $trace_code = trim($_REQUEST['trace_code']);
        $openid = trim($_REQUEST['openid']);

        if(empty($openid) || ($trace_code == '') ){
            $url ='/wechat/claim.html?openid='.$openid;
            $this->disWechatMessage('跟踪单号为空或获取openid失败', $url,false,1);
        }else{
            $this->assign('openid',$openid);
            $additional = isset($_REQUEST['claim_tag']) ? ' and user_id=0' : '';
            $express = $this->findExpress($trace_code, $additional);
            if($express){
                $this->assign('express',$express);
                $user = $this->getUserByWechatOpenId($openid);
                $this->assign('user',$user);
                $this->assign('openid',$openid);
                if(isset($_REQUEST['claim_tag'])) $this->assign('claim','1');
                $this->display();
            }else{
                $url ='/wechat/claim.html';
                $this->disWechatMessage('您输入的单号不存在或已被认领过啦', $url,false,1);
            }
        }
    }

    // --------------------------------------------------------------------------------------------
    // 执行申报
    public function dodeclaration(){
        if(trim($_POST['name']) =='' ){
            $url ='/wechat/declaration.html?openid='.$_POST['openid'].'&trace_code='.$_POST['express_code'];
            $this->disWechatMessage('填写的申报信息有误 ', $url,false,1);
        }

        $_POST['count'] = (trim($_POST['count']) != '') ? $_POST['count'] : 1;
        $_POST['price'] = (trim($_POST['price']) != '') ? $_POST['price'] : 0;

        @$this->express->execute("update __TABLE__ set value=".$_POST['price'].", user_id=".$_POST['user_id']. ",user_name='".$_POST['login_name']."',service=0 where id=".trim($_POST['express_id']));
        @$this->writeExpressGoods(trim($_POST['express_id']), $_POST);

        if(isset($_REQUEST['claim'])){
            $url ='/wechat/claim.html?openid='.$_POST['openid'];
        }else{
            $url ='/wechat/warehouse.html?openid='.$_POST['openid'];
        }
        $this->assign('openid',$_REQUEST['openid']);
        $this->disWechatMessage('恭喜！操作成功', $url,true,1);
    }

    // --------------------------------------------------------------------------------------------
    // 写入包裹的申报信息
    private function writeExpressGoods($express_id,$goods){
        $item = array(
            'trans_id' => $express_id,
            'title' => $goods['name'],
            'count' => $goods['count'],
            'value' => $goods['price'],
            'create_time' => time()
        );
        $this->goods->data($item)->add();
    }

    // --------------------------------------------------------------------------------------------
    // 根据单号和用户查找快递
    private function findExpress($express_code, $additional){
        if($express_code != '' ){
            return M("Express")->where("trace_code='$express_code' $additional ")->find();
        }
        return false;
    }

    // --------------------------------------------------------------------------------------------
    // 进行绑定
    public function doBind(){
        if(!isset($_POST['openid']) || ($_POST['openid'] == '') || ( trim($_POST['user_name']) == '' ) || ( trim($_POST['password']) == '')  ){
            $url = '/wechat/register';
            $this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', $url,false);
        }else{
            $user_name = mysql_escape_string(trim($_POST['user_name']));
            $password = mysql_escape_string(trim($_POST['password']));
            $openid = mysql_escape_string(trim($_POST['openid']));
            $this->assign('openid',$openid);

            $user = M ( "User" )->where ( "(status=1) AND (login_name='$user_name') " )->find ();
            if (!empty($user) && ($user['password'] != '')  &&  ($user['password'] == md5(md5($password) . $user['salt'] ))) {
                M()->execute('UPDATE user SET last_login='.time()." ,wechat_openid='$openid'  WHERE id=".$user ['id']);
                $url = '/wechat/wareaddress.html?openid='.$openid;
                $this->disWechatMessage('恭喜您，您的微信已与86OF绑定，以后可以直接微信中享用86OF提供的服务啦！', $url,true,1);
            }else{
                $url = '/wechat/register/openid/'.$openid;
                $this->disWechatMessage('对不起，您输入的用户或密码不正确！', $url,false);
            }
        }
    }

    // --------------------------------------------------------------------------------------------
    // 通过微信注册
    public function doReg(){
        if(!isset($_POST['openid']) || ($_POST['openid'] == '') || ( trim($_POST['user_name']) == '' ) || ( trim($_POST['password']) == '')  ){
            $url = '/wechat/register';
            $this->disWechatMessage('读取OPENID失败，或没有填写您在86OF的帐户信息！', $url,false);
        }else{
            $user_name = mysql_escape_string(trim($_POST['user_name']));
            $password = mysql_escape_string(trim($_POST['password']));
            $openid = mysql_escape_string(trim($_POST['openid']));
            $tel =  mysql_escape_string(trim($_POST['tel']));
            $email =  mysql_escape_string(trim($_POST['email']));
            $this->assign('openid',$openid);

            $url = '/wechat/wareaddress.html?openid='.$openid;
            $_count = M ( "User" )->where ( "login_name='$user_name' " )->count ();

            if($_count > 0){
                $this->disWechatMessage('对不起，用户名已经存在了，请更换一个再试试！', $url,false);
            }else{
                $clientIp = get_client_ip ();

                $now = time ();
                $data = $this->buildUserData($user_name,$password,$email,$tel,$clientIp,$now,$openid);

                $uid = M ( "User" )->data ( $data )->add ();

                if (! empty ( $uid )) {
                    $this->makeFinace ( $uid ); //生成对应财务数据
                    $this->makeUserInfo($uid,$user_name,$tel);
                }
                $this->disWechatMessage('恭喜您，已注册成功,以后就可以直接在微信里享受86OF提供的服务啦', $url,true,0);
            }
        }
    }


    //------------------------------------------------------------------------------------------------
    //产生财务数据
    private function makeFinace($uid) {
        $data ['user_id'] = $uid;
        $data ['money'] = 0;
        $data ['rebate'] = 0;
        $data ['point'] = 0;
        $data ['consumption_total'] = 0;
        $data ['consumption_point'] = 0;
        $data ['status'] = 1;
        $data ['last_update'] = time ();

        D ( 'Finance' )->data ( $data )->add ();
    }

    // -------------------------------------------------------------------------------------------
    //更新用户
    private function makeUserInfo($uid,$un,$tel){

        $count = M('UserInfo')->where("user_id=$uid")->count();

        if( $count == 0 ){
            M('UserInfo')->data(array(
                'user_id'=>$uid,
                'user_name'=>$un,
                'tel'=>$tel
            ))->add();

        }
    }

    // -------------------------------------------------------------------------------------------
    // 显示微信端的操作消息
    private function disWechatMessage($msg,$url,$tag,$doCount='1'){
        $this->assign('jumpUrl',$url);
        $msgTitle = $tag ? '操作成功' : '操作失败';
        $class = $tag ? 'success' : 'panel-warning';
        $this->assign('panle_class',$class);
        $this->assign('docount',$doCount);
        $this->assign('waitSecond',5);
        $this->assign('msgTitle',$msgTitle);
        $this->assign('message', $msg);
        $this->display('Public/wechatsuccess');
    }

    // -------------------------------------------------------------------------------------------
    // 构造用户数据
    private function buildUserData($member,$password,$email,$tel,$clientIp,$now,$openid){
        $data = array();
        $salt = rand_string(12);
        $password1 = md5( $password );
        $data ['login_name'] 	= $member;
        $data ['password'] 		= md5($password1.$salt);
        $data ['salt'] 			= $salt;
        $data ['email'] 		= mysql_escape_string ( htmlspecialchars (trim($email)));
        $data ['email2'] 		= base64_encode(ulowi_encode(strtolower(trim($email))));
        $data ['refer_url'] 	= '';
        $data ['refer_domain'] 	= '';
        $data ['active_status'] = 1;
        $data ['spreader_id'] 	= 0;
        $data ['mobile']		= $tel;
        $data ['wechat_openid'] 	= $openid;

        $data ['status'] 		= 1;
        $data ['is_qquser'] 	= 0;	//这里记为非qq用户
        $data ['reg_ip'] 		= $clientIp;
        $data ['last_ip'] 		= $clientIp;
        $data ['create_time'] 	= $now;
        $data ['last_login'] 	= $now;

        return $data;
    }

    // --------------------------------------------------------------------------------------------
    // 根据微信openid 加载用户信息
    private function getUserByWechatOpenId($_openid) {
        if (! empty ( $_openid )) {
            $condition=array('qq_openid'=>$_openid,"is_qquser"=>2);
            $flag=M ( 'User' )->field ( 'id,login_name' )->where ($condition)->find ();
            if($flag){
                return  $flag;
            }else{
                $this->redirect("We/bindViatang/myId/$_openid");
            }
        }
        return false;
    }
    private   function  judgeUserByOpenid($_openid){
        $bool=false;
        if (! empty ( $_openid )) {
            $condition=array('qq_openid'=>$_openid,"is_qquser"=>2);
            $flag=M ( 'User' )->field ( 'id,login_name' )->where ($condition)->find ();
            $bool=$flag ?  true :false ;
        }
        return $bool;
    }
}
?>