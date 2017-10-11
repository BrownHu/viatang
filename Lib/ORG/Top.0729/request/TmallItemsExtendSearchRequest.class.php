<?php
/**
 * TOP API: tmall.items.extend.search request
 * 
 * @author auto create
 * @since 1.0, 2013-10-11 16:33:10
 */
class TmallItemsExtendSearchRequest
{
	/** 
	 * 商品标签。支持多选过滤,auction_tag=auction_tag1,auction_tag2,不支持天猫精品库8578
	 **/
	private $auctionTag;
	
	/** 
	 * 品牌的id。支持多选过滤，brand=brand1,brand2
	 **/
	private $brand;
	
	/** 
	 * 前台类目id，支持多选过滤，cat=catid1,catid2
	 **/
	private $cat;
	
	/** 
	 * 后台类目id，category=categoryId
	 **/
	private $category;
	
	/** 
	 * 过滤搭配减价宝贝时，combo=1
	 **/
	private $combo;
	
	/** 
	 * 在宝贝页面中进行价格筛选的时候，如果填写了最高价格，就会显示该字段。
	 **/
	private $endPrice;
	
	/** 
	 * 宝贝卖家所在地，中文gbk编码
	 **/
	private $loc;
	
	/** 
	 * 是否多倍积分，1为多倍积分
	 **/
	private $manyPoints;
	
	/** 
	 * 过滤折扣宝贝时，miaosha=1
	 **/
	private $miaosha;
	
	/** 
	 * 是否需要spu聚合的开关:1为关闭，不传表示遵循后端聚合逻辑。默认不作spu聚合。
	 **/
	private $nspu;
	
	/** 
	 * 页码。取值范围：大于零的整数；最大值：100；默认值：1，即默认返回第一页数据。
	 **/
	private $pageNo;
	
	/** 
	 * 每页条数。取值范围：大于零的整数；最大值：100；默认值：40
	 **/
	private $pageSize;
	
	/** 
	 * 是否包邮，-1为包邮
	 **/
	private $postFee;
	
	/** 
	 * 以“属性id：属性值”的形式传入;
	 **/
	private $prop;
	
	/** 
	 * 表示搜索的关键字，例如搜索query=nike。当输入关键字为中文时，将对他进行URLEncode的UTF-8格式编码，如 耐克，那么q=%E8%80%90%E5%85%8B。
	 **/
	private $q;
	
	/** 
	 * 排序类型。类型包括：
s: 人气排序
p: 价格从低到高;
pd: 价格从高到低;
d: 月销量从高到低;
td: 总销量从高到低;
pt: 按发布时间排序.
	 **/
	private $sort;
	
	/** 
	 * 可以根据产品Id搜索属于这个spu的商品。
	 **/
	private $spuid;
	
	/** 
	 * 在宝贝页面中进行价格筛选的时候，如果填写了最低价格，就会显示该字段。
	 **/
	private $startPrice;
	
	/** 
	 * 是否货到付款，1为货到付款
	 **/
	private $supportCod;
	
	/** 
	 * 可以根据卖家id搜索属于该卖家的商品
	 **/
	private $userId;
	
	/** 
	 * 过滤vip宝贝时，vip=1
	 **/
	private $vip;
	
	/** 
	 * 显示旺旺在线卖家的宝贝时，wwonline=1
	 **/
	private $wwonline;
	
	private $apiParas = array();
	
	public function setAuctionTag($auctionTag)
	{
		$this->auctionTag = $auctionTag;
		$this->apiParas["auction_tag"] = $auctionTag;
	}

	public function getAuctionTag()
	{
		return $this->auctionTag;
	}

	public function setBrand($brand)
	{
		$this->brand = $brand;
		$this->apiParas["brand"] = $brand;
	}

	public function getBrand()
	{
		return $this->brand;
	}

	public function setCat($cat)
	{
		$this->cat = $cat;
		$this->apiParas["cat"] = $cat;
	}

	public function getCat()
	{
		return $this->cat;
	}

	public function setCategory($category)
	{
		$this->category = $category;
		$this->apiParas["category"] = $category;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function setCombo($combo)
	{
		$this->combo = $combo;
		$this->apiParas["combo"] = $combo;
	}

	public function getCombo()
	{
		return $this->combo;
	}

	public function setEndPrice($endPrice)
	{
		$this->endPrice = $endPrice;
		$this->apiParas["end_price"] = $endPrice;
	}

	public function getEndPrice()
	{
		return $this->endPrice;
	}

	public function setLoc($loc)
	{
		$this->loc = $loc;
		$this->apiParas["loc"] = $loc;
	}

	public function getLoc()
	{
		return $this->loc;
	}

	public function setManyPoints($manyPoints)
	{
		$this->manyPoints = $manyPoints;
		$this->apiParas["many_points"] = $manyPoints;
	}

	public function getManyPoints()
	{
		return $this->manyPoints;
	}

	public function setMiaosha($miaosha)
	{
		$this->miaosha = $miaosha;
		$this->apiParas["miaosha"] = $miaosha;
	}

	public function getMiaosha()
	{
		return $this->miaosha;
	}

	public function setNspu($nspu)
	{
		$this->nspu = $nspu;
		$this->apiParas["nspu"] = $nspu;
	}

	public function getNspu()
	{
		return $this->nspu;
	}

	public function setPageNo($pageNo)
	{
		$this->pageNo = $pageNo;
		$this->apiParas["page_no"] = $pageNo;
	}

	public function getPageNo()
	{
		return $this->pageNo;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setPostFee($postFee)
	{
		$this->postFee = $postFee;
		$this->apiParas["post_fee"] = $postFee;
	}

	public function getPostFee()
	{
		return $this->postFee;
	}

	public function setProp($prop)
	{
		$this->prop = $prop;
		$this->apiParas["prop"] = $prop;
	}

	public function getProp()
	{
		return $this->prop;
	}

	public function setQ($q)
	{
		$this->q = $q;
		$this->apiParas["q"] = $q;
	}

	public function getQ()
	{
		return $this->q;
	}

	public function setSort($sort)
	{
		$this->sort = $sort;
		$this->apiParas["sort"] = $sort;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setSpuid($spuid)
	{
		$this->spuid = $spuid;
		$this->apiParas["spuid"] = $spuid;
	}

	public function getSpuid()
	{
		return $this->spuid;
	}

	public function setStartPrice($startPrice)
	{
		$this->startPrice = $startPrice;
		$this->apiParas["start_price"] = $startPrice;
	}

	public function getStartPrice()
	{
		return $this->startPrice;
	}

	public function setSupportCod($supportCod)
	{
		$this->supportCod = $supportCod;
		$this->apiParas["support_cod"] = $supportCod;
	}

	public function getSupportCod()
	{
		return $this->supportCod;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		$this->apiParas["user_id"] = $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setVip($vip)
	{
		$this->vip = $vip;
		$this->apiParas["vip"] = $vip;
	}

	public function getVip()
	{
		return $this->vip;
	}

	public function setWwonline($wwonline)
	{
		$this->wwonline = $wwonline;
		$this->apiParas["wwonline"] = $wwonline;
	}

	public function getWwonline()
	{
		return $this->wwonline;
	}

	public function getApiMethodName()
	{
		return "tmall.items.extend.search";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
