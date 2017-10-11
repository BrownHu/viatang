<?php
/**
 * TOP API: taobao.delivery.tradeorder.calculation request
 * 
 * @author auto create
 * @since 1.0, 2013-10-11 16:33:10
 */
class DeliveryTradeorderCalculationRequest
{
	/** 
	 * 买家地区ID
<br/><br/>
<font color = blue>
当需要同时计算多个订单的费用，买家地区ID之间用分号";"隔开。
</font>
<br/><br/>
<font color = red>
地区ID在计算运费的时候存在一个向上匹配的规则（区-->市-->省-->全国），
当传入的ID是区ID如果没有计算出该ID的运费则向上匹配按市计算运费一次类推直到取到全国的运费。
</font>
	 **/
	private $areaIds;
	
	/** 
	 * 商品所属类目ID


<br/><br/><font color =red >可以不输入，如果输入需要是大于等于0的整数；如果输入的值不是合法的数值当作没有输入类目信息</font><br/><br/>
<font color = blue>
请注意item_ids的第2点描述
。同unified_post说明</font>
	 **/
	private $categoryIds;
	
	/** 
	 * 忽略的运送方式列表，可选的值同selected_services
</font>
<br/><br/>
<font color = blue>
单个订单多个需要忽略的运送方式之间用逗号","隔开；多个订单时需要忽略的运送方式组之间用分号";"隔开
</font>

<br/><br/>
<font color = red>
意思就是当select_services选择了某种运送方式或该笔订单下的所有商品都支持某种运送方式，但是这里选择了忽略就不会计算该运送方式费用
</font>
<br/><br/>
<font color = blue>
请注意order_ids的第3点描述
</font>
	 **/
	private $ignoreServices;
	
	/** 
	 * 商品是否是否卖家包邮，必须为“true”或“false”
<br/><br/>
<font color = red>单个订单包含多个商品时商品是否是否卖家包邮用逗号隔开，多个订单时订单之间的数据用分号隔开 </font>
	 **/
	private $isSellerPays;
	
	/** 
	 * 商品购买数量,必须为大于0的整数
<br/><br/>
<font color = red>
单个订单包含多个商品时商品数量用逗号隔开，多个订单时订单之间的数据用分号隔开 
</font>
	 **/
	private $itemCounts;
	
	/** 
	 * 商品ID,为整数必须大于0,从这里开始包含下面的参数都是描述订单下面的商品信息，一个订单可以包含多个商品，商品之间的信息用逗号隔开;上面的参数是描述订单信息的参数 
<br/><br/>
<font color = red>
1、一个订单包含多个商品时商品ID用逗号隔开，多个订单之间之间用分号隔开
</font>

<br/><br/>
<font color = blue>
2、下面的必须参数的分号个数需要和order_ids相同；逗号个数需要根据订单一一与item_ids数量相同。
下面的可选参数如果部分订单需要填写值，部分订单不需要填写值，那么这个可选参数的分号个数必须和order_ids相同；如果下面的可选参数在一个订单中部分商品需要填写数据，部分不需要填写，那么这个可选参数的逗号个数必须和item_ids相同，如果可选参数什么都不需要填那么就忽略这个参数（相当于不给这个参数赋值） 
</font>
	 **/
	private $itemIds;
	
	/** 
	 * 商品体积单位是立方米

<br/><br/><font color =red >可以不输入，如果输入必须是有效的数值，数值的需要小于等于999.999999，大于等于0最多含6位小数的数值；如果输入的值不是合法的数值当前查询会失败</font><br/><br/>
<font color = blue>
请注意item_ids的第2点描述
，还需注意这个参数的值要么不输入，输入的话需要输入小于等于9999.999999大于等于0最多含6为小数的数值</font>
	 **/
	private $itemVolumes;
	
	/** 
	 * 商品重量单位是kg(千克)

<br/><br/><font color =red >可以不输入，如果输入必须是有效的数值，数值的需要小于等于9999.999大于等于0最多含3位小数的数值；如果输入的值不是合法的数值当前查询会失败</font><br/><br/>
<font color = blue>
请注意item_ids的第2点描述
，还需注意这个参数的值要么不输入，输入的话需要输入小于等于9999.999大于等于0最多含3位小数的数值</font>
	 **/
	private $itemWeights;
	
	/** 
	 * 订单标识号
<br/><br/>
<font color = red>
1、如果没有交易订单号，请填入一个能方便你确定该笔订单的标识，当批量查询订单运费时好确定订单和费用的对应关系
</font>
<br/><br/>
<font color = blue>2、当需要同时计算多个订单的费用，订单标识号之间用分号";"隔开。</font>
<br/>
<br/>
<font color = red> 3、下面的必须参数的分号个数必须和order_ids相同;下面的可选参数如果部分订单需要填写值，部分订单不需要填写值，那么这个可选参数的分号个数必须和order_ids相同，如果可选参数什么都不需要填那么就忽略这个参数（相当于不给这个参数赋值） </font>

<br/><br/>
4、分号和逗号都是半角的

<br/><br/>
5、填写入参的时候一个订单的数据需要根据分号一一对应输入，一个订单下的商品数据需要根据逗号一一对应输入
	 **/
	private $orderIds;
	
	/** 
	 * 买家购买商品时选择的运送方式。
<br/><br/>
<font color = red>运送方式主要有以下几种：<br/>express(快递)<br/>post(平邮)<br/>ems(EMS)</font>
<br/><br/>
<font color = bule>
单个订单选择的多种运送方式之间用逗号","隔开；多个订单时选择的各个运送方式组之间用分号";"隔开 
</font>
<br/><br/>
<font color = blue>
部分订单有值，部分没有的示例值
<br/>比如：(;post,express)、(ems;)
</font>
<br/>
<br/>
<font color = red>
当没有某笔订单输入需要计算的运送方式时表示对应的订单需要返回订单中所有商品支持的运送方式的交集，当然还要是ignore_services设置的运送方式之外的运送方式 
</font>
<br/><font color = red>
如果是卖家包邮，该字段不填 
</font>
<br/>
<br/>
<font color = blue>
请注意order_ids的第3点描述
</font>
	 **/
	private $selectedServices;
	
	/** 
	 * 宝贝SKU ID,可以为NULL或空字符，如果不为空 
必须是整数。如果输入的不是整数当作没有skuId处理
<br/><br/>
<font color = red>单个订单包含多个宝贝时用逗号隔开，多个订单时订单之间的数据用分号隔开 </font>

<br/><br/>
<font color = blue>
请注意item_ids的第2点描述
</font>
	 **/
	private $skuIds;
	
	/** 
	 * 商品所挂的运费模板ID，必须为正整数，当没有挂运费模板时请输入0，当宝贝是包邮（卖家承担运费）也必须输入0。
<br/><br/>
<font color = red>单个订单包含多个商品时商品所挂的运费服务模板ID用逗号隔开，多个订单时订单之间的数据用分号隔开 </font>
<br/><br/>
<font color = blue>
注：如果该商品没有挂运费服模板，必须输入0;当运费模板ID为0时如果有全国统一价（unified_post、unified_express、unified_ems）则取全国统一价格，如果全国统一价格也没有则当作宝贝包邮（卖家承担运费）
</font>
	 **/
	private $templateIds;
	
	/** 
	 * 全国统一EMS费用可以为NULL或空字符，不为空时且不是正整数 则当作空处理。
<br/><br/>
<font color = bule>
单位为分，写入的时候请注意把统一价从元单位转换成分
</font>
<br/>
<br/>
<font color = red>
同上
</font>

<br/><br/>
<font color = blue>
请注意item_ids的第2点描述
</font>
	 **/
	private $unifiedEms;
	
	/** 
	 * 全国统一快递费用可以为NULL或空字符，不为空时且不是正整数则当作空处理。 
<br/><br/>
<font color = bule>
单位为分，写入的时候请注意把统一价从元单位转换成分
</font>
<br/>
<br/>
<font color=red>规则同上</font>

<br/><br/>
<font color = blue>
请注意item_ids的第2点描述
</font>
	 **/
	private $unifiedExpress;
	
	/** 
	 * 全国统一快递费用可以为NULL或空字符，不为空时且不是正整数则当作空处理。
<br/><br/>
<font color = red>
单个订单包含多个商品时全国统一平邮费用用逗号隔开，多个订单时订单之间的全国统一平邮费用用分号隔开 
</font>

<br/><br/>
<font color = blue>
请注意item_ids的第2点描述。这里参数的示例值说明如果没有值输入，你要输入了一个分号或逗号也需要保持上面说提到的分号和逗号个数的匹配规则，若没有值最好就是不要给这个参数赋值</font>
<br/><br/>
<font color = bule>
单位为分，写入的时候请注意把统一价从元单位转换成分
</font>
	 **/
	private $unifiedPost;
	
	/** 
	 * 商品用户ID，必须为大于0的整数。
<br/><br/>
<font color = red>单个订单包含多个商品时商品的用户ID用逗号隔开，多个订单时订单之间的数据用分号隔开 </font>
	 **/
	private $userIds;
	
	private $apiParas = array();
	
	public function setAreaIds($areaIds)
	{
		$this->areaIds = $areaIds;
		$this->apiParas["area_ids"] = $areaIds;
	}

	public function getAreaIds()
	{
		return $this->areaIds;
	}

	public function setCategoryIds($categoryIds)
	{
		$this->categoryIds = $categoryIds;
		$this->apiParas["category_ids"] = $categoryIds;
	}

	public function getCategoryIds()
	{
		return $this->categoryIds;
	}

	public function setIgnoreServices($ignoreServices)
	{
		$this->ignoreServices = $ignoreServices;
		$this->apiParas["ignore_services"] = $ignoreServices;
	}

	public function getIgnoreServices()
	{
		return $this->ignoreServices;
	}

	public function setIsSellerPays($isSellerPays)
	{
		$this->isSellerPays = $isSellerPays;
		$this->apiParas["is_seller_pays"] = $isSellerPays;
	}

	public function getIsSellerPays()
	{
		return $this->isSellerPays;
	}

	public function setItemCounts($itemCounts)
	{
		$this->itemCounts = $itemCounts;
		$this->apiParas["item_counts"] = $itemCounts;
	}

	public function getItemCounts()
	{
		return $this->itemCounts;
	}

	public function setItemIds($itemIds)
	{
		$this->itemIds = $itemIds;
		$this->apiParas["item_ids"] = $itemIds;
	}

	public function getItemIds()
	{
		return $this->itemIds;
	}

	public function setItemVolumes($itemVolumes)
	{
		$this->itemVolumes = $itemVolumes;
		$this->apiParas["item_volumes"] = $itemVolumes;
	}

	public function getItemVolumes()
	{
		return $this->itemVolumes;
	}

	public function setItemWeights($itemWeights)
	{
		$this->itemWeights = $itemWeights;
		$this->apiParas["item_weights"] = $itemWeights;
	}

	public function getItemWeights()
	{
		return $this->itemWeights;
	}

	public function setOrderIds($orderIds)
	{
		$this->orderIds = $orderIds;
		$this->apiParas["order_ids"] = $orderIds;
	}

	public function getOrderIds()
	{
		return $this->orderIds;
	}

	public function setSelectedServices($selectedServices)
	{
		$this->selectedServices = $selectedServices;
		$this->apiParas["selected_services"] = $selectedServices;
	}

	public function getSelectedServices()
	{
		return $this->selectedServices;
	}

	public function setSkuIds($skuIds)
	{
		$this->skuIds = $skuIds;
		$this->apiParas["sku_ids"] = $skuIds;
	}

	public function getSkuIds()
	{
		return $this->skuIds;
	}

	public function setTemplateIds($templateIds)
	{
		$this->templateIds = $templateIds;
		$this->apiParas["template_ids"] = $templateIds;
	}

	public function getTemplateIds()
	{
		return $this->templateIds;
	}

	public function setUnifiedEms($unifiedEms)
	{
		$this->unifiedEms = $unifiedEms;
		$this->apiParas["unified_ems"] = $unifiedEms;
	}

	public function getUnifiedEms()
	{
		return $this->unifiedEms;
	}

	public function setUnifiedExpress($unifiedExpress)
	{
		$this->unifiedExpress = $unifiedExpress;
		$this->apiParas["unified_express"] = $unifiedExpress;
	}

	public function getUnifiedExpress()
	{
		return $this->unifiedExpress;
	}

	public function setUnifiedPost($unifiedPost)
	{
		$this->unifiedPost = $unifiedPost;
		$this->apiParas["unified_post"] = $unifiedPost;
	}

	public function getUnifiedPost()
	{
		return $this->unifiedPost;
	}

	public function setUserIds($userIds)
	{
		$this->userIds = $userIds;
		$this->apiParas["user_ids"] = $userIds;
	}

	public function getUserIds()
	{
		return $this->userIds;
	}

	public function getApiMethodName()
	{
		return "taobao.delivery.tradeorder.calculation";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->areaIds,"areaIds");
		RequestCheckUtil::checkNotNull($this->isSellerPays,"isSellerPays");
		RequestCheckUtil::checkNotNull($this->itemCounts,"itemCounts");
		RequestCheckUtil::checkNotNull($this->itemIds,"itemIds");
		RequestCheckUtil::checkNotNull($this->orderIds,"orderIds");
		RequestCheckUtil::checkNotNull($this->templateIds,"templateIds");
		RequestCheckUtil::checkNotNull($this->userIds,"userIds");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
