<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 * 	淘宝功能测试模块
 +------------------------------------------------------------------------------ 
 * @copyright 上海子凌网络科技有限公司˾
 * @author    stone@ulowi.com
 * @version   1.0
 +------------------------------------------------------------------------------
 */
function get_object_vars_final($obj){
	if(is_object($obj)){
		$obj=get_object_vars($obj);
	}
	if(is_array($obj)){
		foreach ($obj as $key=>$value){
			$obj[$key]=get_object_vars_final($value);
		}
	}
	return $obj;
}

//------------------------------------------------------------------------------------------------
function curl($url, $postFields = null)
{	
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FAILONERROR, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	if (is_array($postFields) && 0 < count($postFields))
	{
		$postBodyString = "";
		foreach ($postFields as $k => $v)
		{
			$postBodyString .= "$k=" . urlencode($v) . "&";
		}
		unset($k, $v);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
	}
	
	$reponse = curl_exec($ch);
	
	if (curl_errno($ch)){
		throw new Exception(curl_error($ch),0);
	}else{
		$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (200 !== $httpStatusCode){
			echo $httpStatusCode;
			throw new Exception($reponse,$httpStatusCode);
		}
	}
	curl_close($ch);
	return $reponse;
}

import ( '@.ORG.Top.TopClient' );
//import ( '@.ORG.Top.request.ItemGetRequest' );
//import ( '@.ORG.Top.request.LogisticsTraceSearchRequest' );
//import ( '@.ORG.Top.request.TradeGetRequest' );

import ( '@.ORG.Top.request.TmallItemsExtendSearchRequest' );
//import ( '@.ORG.Top.request.TopatsItemcatsGetRequest' );

class TaobaoAction extends Action {
	/**
    +----------------------------------------------------------
	 * 默认操作
    +----------------------------------------------------------
	 */
	public function index() {
		$c = new TopClient ();
		$c->format = 'json';
		$c->checkRequest = false;
		
		//取商品详情列表
		$c->appkey = '12593084';
		$c->secretKey = '8b67d13eeb737970e533454255066333';
		$req = new TmallItemsExtendSearchRequest ();
		$req->setCat(50000697);
		//$req->setPageNo($pageNo)
		//$req->setQ('毛衣');
		$resp = $c->execute ( $req );
		dump ( $resp );
		
	}
	
	//------------------------------------------------------------------------------------------------
	public function getTrade(){
		$c = new TopClient ();
		$c->format = 'json';
		$c->checkRequest = false;
		
		$req = new TradeGetRequest;
		$req->setFields("orders.buyer_rate");
		$req->setTid(123456);
		//$resp = $c->execute($req, $sessionKey);
	}
	
	//------------------------------------------------------------------------------------------------
	public function oauth(){
		$code 				= $_REQUEST['code'];   //通过访问https://oauth.taobao.com/authorize获取code
		$grant_type 		= 'authorization_code';
		$redirect_uri 	= C('SERVER_URL').'/'."Taobo/oauth&state=1";  //此处回调url要和后台设置的回调url相同
		$client_id 			= '12593084';
		$client_secret 	= '8b67d13eeb737970e533454255066333';
		
		//请求参数
		$postfields= array(
				'grant_type'     	=> $grant_type,
				'client_id'     		=> $client_id,
				'client_secret' 	=> $client_secret,
				'code'          		=> $code,
				'redirect_uri'  	=> $redirect_uri
		);
		$url = 'https://eco.taobao.com/router/rest';//https://oauth.taobao.com/token';
			
		$token = get_object_vars_final(json_decode(curl($url,$postfields)));
		//print_r($token);
		exit;
		//查看授权是否成功
		if(isset($token['error'])){
			$this->assign('error',$token['error']);
			$this->assign('error_description',$token['error_description']);
		}else{
			$access_token = $token['access_token'];
			$this->assign('seeeion_key',$access_token);
		}
		
		//自动刷新令牌refresh_token
		$postfields2= array(
				'grant_type'     		=> 'refresh_token',
				'client_id'     			=> $client_id,
				'client_secret' 		=> $client_secret,
				'refresh_token'  	=>$token['refresh_token']
		);
		
		$url = 'https://oauth.taobao.com/token';
		$token = get_object_vars_final(json_decode(curl($url,$postfields2)));
		if(isset($token['error'])){
			echo '<div style="font-size:14px; margin-top:20px; color:red; text-align:center;">自动刷新淘宝授权失败，session 有效期为一天'.'---'.$token['error_description'].'</div>';
		}else{
			echo '成功';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function refresh_token(){
		$client_id = $this->setting['taobao_appkey'];//自己的APPKEY
		$client_secret = $this->setting['taobao_appsecret'];//自己的appsecret
		$refresh_token=$this->setting['tao_session'];//refresh_token
		$grant_type='refresh_token';
		//请求参数
		$postfields= array('grant_type'     => $grant_type,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'refresh_token'  =>$refresh_token
		);
		$url = 'https://oauth.taobao.com/token';
			
		$token = get_object_vars_final(json_decode(curl($url,$postfields)));
		print_r($token);
			
		if(!is_array($token)){
			$this->error('对不起，授权失败,授权不可用',U('items_collect/author_tao'));
		}
		if(isset($token['error'])){
			if($token['error_description']=='refresh times limit exceed'){
				$this->error('对不起，授权失败,自动刷新淘宝授权可用',U('items_collect/author_tao'));
				//jump(-1,'自动刷新淘宝授权可用');
			}
			else{
				$this->error('对不起，检测失败，请从新获取淘宝授权后再检测',U('items_collect/author_tao'));
			}
		}
		if(urldecode($token['taobao_user_nick'])==$this->setting['taobao_nick']){
			$this->success('恭喜您，授权成功',U('items_collect/author_tao'));
		}
		else{
			$this->error('对不起，授权失败,请核对后台淘宝账号是否正确',U('items_collect/author_tao'));
		}
		exit;
	}
	

}
?>