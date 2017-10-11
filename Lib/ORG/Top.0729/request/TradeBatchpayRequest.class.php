<?php
/**
 * TOP API: taobao.trade.batchpay request
 * 
 * @author auto create
 * @since 1.0, 2013-10-11 16:33:10
 */
class TradeBatchpayRequest
{
	/** 
	 * 合并付款的订单号序列，订单状态为等待买家付款状态，订单号以'θ'号进行间隔，最多支持15笔订单同时付款。如果是单笔订单，则只是一个订单号
	 **/
	private $tids;
	
	private $apiParas = array();
	
	public function setTids($tids)
	{
		$this->tids = $tids;
		$this->apiParas["tids"] = $tids;
	}

	public function getTids()
	{
		return $this->tids;
	}

	public function getApiMethodName()
	{
		return "taobao.trade.batchpay";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->tids,"tids");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
