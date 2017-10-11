<?php
/**
 * TOP API: taobao.trade.create request
 * 
 * @author auto create
 * @since 1.0, 2013-10-11 16:33:10
 */
class TradeCreateRequest
{
	/** 
	 * 邮寄地址的id（当此参数不为空时收货地址详细信息已经确认，则new_divisioncode、new_postcode、new_address、new_name、new_mobile、new_phone将被无效。当有新的收货地址时，address_id则为空，收货地址将采用新的收货地址信息，则new_divisioncode、new_postcode、new_address、new_name、new_mobile、new_phone的值将被采用。）
	 **/
	private $addressId;
	
	/** 
	 * 买家留言
	 **/
	private $buyerMemo;
	
	/** 
	 * 一个或多个商品所有数量优惠id列表，多个商品用'θ'号不同宝贝优惠id，'θ'分割item_promotions可以为空并且必保持与num_iids严格一致
	 **/
	private $itemPromotions;
	
	/** 
	 * 新增收货地址的收件人地址（当address_id不为空时，此值传入无效，此时可以不传入）
	 **/
	private $newAddress;
	
	/** 
	 * 新的收货地址是否保存，如果设置为false，则将新的收货地址不保存到买家收货地址列表中，如果为true，则保存（此参数不传，默认是保存这个收货地址的）
	 **/
	private $newAddressStore;
	
	/** 
	 * 新增收货地址的区域码（通过调用接口得到标准的区域码。当address_id不为空时，此值传入无效，此时可以不传入）区位码严格按照提供的xml中省市区对应的区域码来设置。(TOP提供省市区转换区域码的xml文件)
	 **/
	private $newDivisioncode;
	
	/** 
	 * 新增收货地址的手机号码（当address_id不为空时，此值传入无效，此时可以不传入。new_mobile与new_phone至少一个不为空）
	 **/
	private $newMobile;
	
	/** 
	 * 新增收货地址的收件人姓名（当address_id不为空时，此值传入无效，此时可以不传入）
	 **/
	private $newName;
	
	/** 
	 * 新增收货地址的电话号码（当address_id不为空时，此值传入无效，此时可以不传入。new_mobile与new_phone至少一个不为空）
	 **/
	private $newPhone;
	
	/** 
	 * 新增收货地址的邮编（当address_id不为空时，此值传入无效，此时可以不传入）
	 **/
	private $newPostcode;
	
	/** 
	 * 商品的id序列，用'θ'间隔并列的商品id。(最多支持50个商品id)
	 **/
	private $numIids;
	
	/** 
	 * 一个或多个商品所有数量nums列表，多个商品用'θ'号不同宝贝num，'θ'分割nums不能为空并且必保持与num_iids严格一致。
	 **/
	private $nums;
	
	/** 
	 * medicine(商城医药馆)、MSHOP(手机店铺)和AGENT(第三方网站)、wap_medicine(天猫无线医药馆)、ownshop（商家官网）
	 **/
	private $orderFrom;
	
	/** 
	 * 订单的外部订单号，用来防止重复提交。
	 **/
	private $outId;
	
	/** 
	 * 淘宝客id（创建交易支持淘宝客可以传入该参数）
	 **/
	private $pid;
	
	/** 
	 * 创建交易时的物流方式。
可选值：ems, express, post, free
	 **/
	private $shippingType;
	
	/** 
	 * 店铺级优惠的id
	 **/
	private $shopPromotion;
	
	/** 
	 * 一件或者多件商品所有skuid列表，多个商品用'θ'号不同宝贝skuid，'θ'分割的sku若为空则用0表示，且分隔的序列必保持与num_iids严格一致。
	 **/
	private $skuIids;
	
	private $apiParas = array();
	
	public function setAddressId($addressId)
	{
		$this->addressId = $addressId;
		$this->apiParas["address_id"] = $addressId;
	}

	public function getAddressId()
	{
		return $this->addressId;
	}

	public function setBuyerMemo($buyerMemo)
	{
		$this->buyerMemo = $buyerMemo;
		$this->apiParas["buyer_memo"] = $buyerMemo;
	}

	public function getBuyerMemo()
	{
		return $this->buyerMemo;
	}

	public function setItemPromotions($itemPromotions)
	{
		$this->itemPromotions = $itemPromotions;
		$this->apiParas["item_promotions"] = $itemPromotions;
	}

	public function getItemPromotions()
	{
		return $this->itemPromotions;
	}

	public function setNewAddress($newAddress)
	{
		$this->newAddress = $newAddress;
		$this->apiParas["new_address"] = $newAddress;
	}

	public function getNewAddress()
	{
		return $this->newAddress;
	}

	public function setNewAddressStore($newAddressStore)
	{
		$this->newAddressStore = $newAddressStore;
		$this->apiParas["new_address_store"] = $newAddressStore;
	}

	public function getNewAddressStore()
	{
		return $this->newAddressStore;
	}

	public function setNewDivisioncode($newDivisioncode)
	{
		$this->newDivisioncode = $newDivisioncode;
		$this->apiParas["new_divisioncode"] = $newDivisioncode;
	}

	public function getNewDivisioncode()
	{
		return $this->newDivisioncode;
	}

	public function setNewMobile($newMobile)
	{
		$this->newMobile = $newMobile;
		$this->apiParas["new_mobile"] = $newMobile;
	}

	public function getNewMobile()
	{
		return $this->newMobile;
	}

	public function setNewName($newName)
	{
		$this->newName = $newName;
		$this->apiParas["new_name"] = $newName;
	}

	public function getNewName()
	{
		return $this->newName;
	}

	public function setNewPhone($newPhone)
	{
		$this->newPhone = $newPhone;
		$this->apiParas["new_phone"] = $newPhone;
	}

	public function getNewPhone()
	{
		return $this->newPhone;
	}

	public function setNewPostcode($newPostcode)
	{
		$this->newPostcode = $newPostcode;
		$this->apiParas["new_postcode"] = $newPostcode;
	}

	public function getNewPostcode()
	{
		return $this->newPostcode;
	}

	public function setNumIids($numIids)
	{
		$this->numIids = $numIids;
		$this->apiParas["num_iids"] = $numIids;
	}

	public function getNumIids()
	{
		return $this->numIids;
	}

	public function setNums($nums)
	{
		$this->nums = $nums;
		$this->apiParas["nums"] = $nums;
	}

	public function getNums()
	{
		return $this->nums;
	}

	public function setOrderFrom($orderFrom)
	{
		$this->orderFrom = $orderFrom;
		$this->apiParas["order_from"] = $orderFrom;
	}

	public function getOrderFrom()
	{
		return $this->orderFrom;
	}

	public function setOutId($outId)
	{
		$this->outId = $outId;
		$this->apiParas["out_id"] = $outId;
	}

	public function getOutId()
	{
		return $this->outId;
	}

	public function setPid($pid)
	{
		$this->pid = $pid;
		$this->apiParas["pid"] = $pid;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function setShippingType($shippingType)
	{
		$this->shippingType = $shippingType;
		$this->apiParas["shipping_type"] = $shippingType;
	}

	public function getShippingType()
	{
		return $this->shippingType;
	}

	public function setShopPromotion($shopPromotion)
	{
		$this->shopPromotion = $shopPromotion;
		$this->apiParas["shop_promotion"] = $shopPromotion;
	}

	public function getShopPromotion()
	{
		return $this->shopPromotion;
	}

	public function setSkuIids($skuIids)
	{
		$this->skuIids = $skuIids;
		$this->apiParas["sku_iids"] = $skuIids;
	}

	public function getSkuIids()
	{
		return $this->skuIids;
	}

	public function getApiMethodName()
	{
		return "taobao.trade.create";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->numIids,"numIids");
		RequestCheckUtil::checkNotNull($this->nums,"nums");
		RequestCheckUtil::checkNotNull($this->orderFrom,"orderFrom");
		RequestCheckUtil::checkNotNull($this->outId,"outId");
		RequestCheckUtil::checkNotNull($this->shippingType,"shippingType");
		RequestCheckUtil::checkNotNull($this->skuIids,"skuIids");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
