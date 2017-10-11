<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 快速代购
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/simple_html_dom' );
load ( '@/tmall' );
class ParseProduct {
	
	//------------------------------------------------------------------------------------------------
	//根据域名分析商品信息
	public function analyse($domain,$html,$id,$url,$are_code){
		switch (strtolower($domain)){
			case 'taobao.com':
				return $this->taobao($html,$id,$url,$are_code);
				break;
			case 'tmall.com':
				return $this->tmall($html,$id,$url,$are_code);
				break;	
			case 'paipai.com':
				return $this->paipai($html);
				break;
			case 'eachnet.com':
				return $this->eachnet($html);
				break;
			case 'amazon.com.cn':
				return $this->amazon($html);
				break;
			case 'amazon.cn':
				return $this->amazon($html);
				break;
			case 'z.cn':
				return $this->amazon($html);
				break;				
			case 'dangdang.com':
				return $this->dangdang($html);
				break;
			case 'china-pub.com':
				return $this->chinapub($html);
				break;
			case '360buy.com':
				return $this->buy360($html);
		}
	}

	//------------------------------------------------------------------------------------------------
	private function taobao($html,$id,$url,$are_code){
		
		$dom = new simple_html_dom();	
		$dom->load($html);
		
		//解析商品详情
		$product['shop_name'] 	= trim(strip_tags($dom->find('div.tb-shop-name a',0)->innertext));
		$product['shop_url'] 	= trim($dom->find('div.tb-shop-name a',0)->getAttribute('href'));
		$product['from_cn']		= '淘宝';
		$product['from_en']		= 'taobao';
		$product['url'] 		= $url;
		$product['title'] 		= trim($dom->find('title',0)->plaintext);//trim($dom->find('h3.tb-main-title',0)->plaintext);
		$product['seller'] 		= trim(strip_tags($dom->find('a.tb-seller-name',0)->innertext));
		$price_promotion  = 0;
		$price_org 		= $dom->find('strong[id=J_StrPrice]',0)->find('em.tb-rmb-num',0)->plaintext;
		
		$title = $product['title'];
		$product['title'] = str_replace('-淘宝网','',$title);
		
		$product['price'] =  $dom->find('strong[id=J_StrPrice]',0)->find('em.tb-rmb-num',0)->plaintext;
		$product['price_pro'] = $dom->find('em[id=J_Price]',0)->plaintext; 
		if($product['price_pro'] != '') {
			$product['price']= $product['price_pro'];
		}
		$product['props'] 		= array();
		//dump($product);exit;
		//商品属性列表
		$sku_list  = $dom->find('dl.J_Prop');
		foreach ($sku_list as $sku){
			$caption = $sku->find('dt.tb-property-type',0)->plaintext;

			$item = array();
			foreach ($sku->find('ul li') as $i){
				$item[] = $i->find('span',0)->plaintext;
			}
			if(($caption == null) || (trim($caption) == '') || (trim($caption) == '天猫分期')) continue;
			$product['props'][] = array('cap'=>$caption,'items'=>$item);
		}
		
		//运费
		$product['shipping_fee'] = $this->getShippingfee($id,$are_code);
		
		//商品图片列表
		$product['img_list'] = array();
		$imgs = $dom->find('ul[id=J_UlThumb]',0);
		foreach ($imgs->find('li') as $img){
			$product['img_list'][] = $img->find('img',0)->getAttribute('data-src');
		}
		$product['image'] = $dom->find('img[id=J_ImgBooth]',0)->getAttribute('data-src');
		
		// 获取描述
		$sub =  substr($html, strpos($html, 'g_config.dynamicScript("http://dsc.taobaocdn.com'),strpos($html,'</script>'));
		$sub1 = substr($sub,strpos($sub, '(')+1);
		$sub2 = substr($sub1,0,strpos($sub1,')'));
		
		$product['des'] = trim($sub2,'"');
		
		$dom->clear();
		return $product;
	}

	//------------------------------------------------------------------------------------------------
	private function tmall($html,$id,$url,$are_code){
	
		$dom = new simple_html_dom();
		$dom->load($html);
		
		$product['shop_name'] 	= trim(strip_tags($dom->find('a.slogo-shopname',0)->innertext));
		$product['shop_url'] 	= trim($dom->find('a.slogo-shopname',0)->getAttribute('href'));
		$product['from_cn']		= '天猫';
		$product['from_en']		= 'tmall';
		$product['url'] 		= $url;
		$product['title'] 		= trim($dom->find('title',0)->plaintext);//trim($dom->find('div.tb-detail-hd',0)->plaintext);
		$product['seller'] 		= urldecode(strip_tags( $dom->find('span.slogo-ww',0)->getAttribute('data-nick') ));
		$product['price'] 		= getPrice($url);
		$product['props'] 		= array();
		
		$title = $product['title'];
		$product['title'] = str_replace('-tmall.com天猫','',$title);
		//商品属性列表
		$sku_list  = $dom->find('dl.tb-prop');
		foreach ($sku_list as $sku){
			$caption = $sku->find('dt.tb-metatit',0)->plaintext;
			
			if(($caption == null) || (trim($caption) == '') || (trim($caption) == '天猫分期')) continue;
		    
			$item = array();
			foreach ($sku->find('ul li') as $i){
				$item[] = $i->find('span',0)->plaintext;
			}
			
			$product['props'][] = array('cap'=>$caption,'items'=>$item);
		}
		
		//运费		
		$product['shipping_fee'] = $this->getShippingfee($id,$are_code);
		
		//商品图片列表
		$product['img_list'] = array();
		$imgs = $dom->find('ul[id=J_UlThumb]',0);
		foreach ($imgs->find('li') as $img){
			$product['img_list'][] = $img->find('img',0)->getAttribute('src');
		}
		$product['image'] = $dom->find('img[id=J_ImgBooth]',0)->getAttribute('src');
				
		// 获取描述
		$sub =  substr($html, strpos($html, 'descUrl'));
		$sub1 = substr($sub,strpos($sub, ':')+1);
		$sub2 = substr($sub1,0,strpos($sub1,','));		
		$product['des'] = trim($sub2,'"');
		
		$dom->clear();	
		return $product;
	}
	
	//------------------------------------------------------------------------------------------------
	private function paipai($html){
		$product_info = json_decode($html,true);
		$product = array(
			'title'			=>	 $product_info['itemName'],
			'price'			=>	 $product_info['itemPrice'] / 100,
			'shipping_fee'	=> 	 $product_info['expressPrice'] / 100,
			'seller'		=>	 $product_info['sellerName'],
			'from_cn'		=> 	 '拍拍',
			'from_en'		=> 	 'paipai',
			'image'			=>	 $product_info['picLink']
		);
		return $product;
	}

	//------------------------------------------------------------------------------------------------
	private function dangdang($html){
		$price_regular = "/<p class=\"[^<\"\']*\">\"当 当 价：\"<span class=\"[^<\"\']*\">￥(.*?)<\/span><\/p>/";
		$price_regular2 = "/<span class=\"[^<\"\']*\">当当价：￥<b[^>]*>(.*?)<\/b><\/span>/";
		$title_regular = "/<a name=\"top_bk\"[^>]*>[^<\"\']*<\/a><b>(.*?)<\//";
		$title_regular2 = "/<a name=\"top_bk\"[^>]*>[^<\"\']*<\/a>(.*?)<\//";
		$image_regular = "/<img id=\'largePic\' src=\"([^\"\']*)\"[^>]*\/>/";
		$image_regular2 = "/name=\"bigpicture_bk\"><img src=\"([^\"\']*)\"[^>]*\/>/";
		
		$html = iconv("gb2312","UTF-8",$html);
		return $this->parase('dangdang.com',$title_regular,$title_regular2,$price_regular,$price_regular2,'','','',
		$image_regular,$image_regular2,$html);
	}

	//------------------------------------------------------------------------------------------------
	private function parase($domain,$title_regular,$title_regular2,$price_regular,$price_regular2,$shipping_regular,$shipping_regular2,
	$seller_regular,$image_regular,$image_regular2,$html){
		$title = '';
		$price = '';
		$shipping_fee = '';
		$seller = '';
		$image = '';

		if($title_regular){
			preg_match_all($title_regular,$html,$matches);
			$title = $matches[1][0];
			if(!$title && $title_regular2){
				preg_match_all($title_regular2,$html,$matches);
				$title = $matches[1][0];
			}
		}

		if($price_regular){
			preg_match_all($price_regular,$html,$matches);
			$price = $matches[1][0];
			if(!$price && $price_regular2){
				preg_match_all($price_regular2,$html,$matches);
				$price = $matches[1][0];
			}
		}

		if($shipping_regular){
			preg_match_all($shipping_regular,$html,$matches);
			$shipping_fee = $matches[1][0];
			if(!$shipping_fee && $shipping_regular2){
				preg_match_all($shipping_regular2,$html,$matches);
				$shipping_fee = $matches[1][0];
			}
		}

		if($seller_regular){
			preg_match_all($seller_regular,$html,$matches);
			$seller = $matches[1][0];
		}

		if($image_regular){
			preg_match_all($image_regular,$html,$matches);
			$image = $matches[1][0];
			if(!$image && $image_regular2){
				preg_match_all($image_regular2,$html,$matches);
				$image = $matches[1][0];
			}
		}

		if($seller == ''){
			$seller = $domain;
		}

		if($shipping_fee == ''){
			$shipping_fee = 0;
		}

		$product_info = array(
		'title'	=>	 $title,
		'price'	=>	 $price,
		'shipping_fee'	=> $shipping_fee,
		'seller'	=>	$seller,
		'image'	=>	$image
		);
		return $product_info;
	}

	//------------------------------------------------------------------------------------------------
	//京东只能采集标题，价格为图片
	private function buy360($html){
		$title_regular = "/<title>(.*?)<\/title>/";
		$html = iconv("gb2312","UTF-8",$html);

		if($title_regular){
			preg_match_all($title_regular,$html,$matches);
			$title = $matches[1][0];
		}

		$product_info = array(
		'title'	=>	 $title,
		'price'	=>	 $price,
		'shipping_fee'	=> $shipping_fee,
		'seller'	=>	$seller,
		'image'	=>	$image
		);
		return $product_info;
	}

	//------------------------------------------------------------------------------------------------
	private function chinapub($html){
		$title_regular = "/<title>(.*?)<\/title>/";
		$html = iconv("gb2312","UTF-8",$html);

		if($title_regular){
			preg_match_all($title_regular,$html,$matches);
			$title = $matches[1][0];
		}

		$product_info = array(
		'title'	=>	 $title,
		'price'	=>	 $price,
		'shipping_fee'	=> $shipping_fee,
		'seller'	=>	$seller,
		'image'	=>	$image
		);
		return $product_info;
	}

	//------------------------------------------------------------------------------------------------
	private function amazon($html){
		$price_regular = "/<span class=\"PriceCharacter\">¥<\/span><span class=\"OurPrice\">(.*?)<\/span>/";
		$price_regular2 = "/<span class=\"PriceCharacter\">¥<\/span><span class=\"OurPrice\">(.*?)<\/span><span class=\"SalePriceText\">/";
		$title_regular = "/<title>(.*?)<\/title>/";
		$image_regular = "/<img id=\"ImageShow\" alt=\"[^\"]*\" src=\"([^\"\']*)\"[^>]*\/>/";
		$html = iconv("gb2312","UTF-8",$html);

		return $this->parase('amazon.com.cn',$title_regular,'',$price_regular,$price_regular2,'','','',$image_regular,'',$html);
	}

	//------------------------------------------------------------------------------------------------
	private function eachnet($html){
		$price_regular = "/<strong>
												<script type=\"text\/javascript\">document.write\(formatPrice\(\'(.*?)\'\)\);<\/script>
											<\/strong>元/";
		$title_regular = "/<title>(.*?)<\/title>/";
		$shipping_regular = "/<dd id=\"itemShippingFee\"><p>(.*?)<p><\/dd>/";
		$seller_regular = "/<div class=\"sellerInfo\">
								<p>
								
									
										<a href=\"[^<\"\']*\"
											target=\"_blank\">(.*?)<\/a> （<script>[^<\"\']*<\/script>）
									
									
								
								<\/p>/";
		$html = iconv("gb2312","UTF-8",$html);
		return $this->parase('eachnet.com',$title_regular,'',$price_regular,'',$shipping_regular,'',$seller_regular,$image_regular,'',$html);
	}
	
	//------------------------------------------------------------------------------------------------
	// 获取运费
	private function getShippingfee($id,$are_code){
		$_url="http://delivery.taobao.com/detail/delivery_detail.do?callback=jsonp1869&itemId=$id&areaId=$are_code";
		$_html = $this->downloadHtml( $_url );;
		$_html = iconv ( "gb2312", "UTF-8//IGNORE", $_html );
		$_html = substr($_html, strpos($_html, '(')+1);	
		$_html = substr($_html, 0,strpos($_html, ')'));
		
		if($_html != ''){
			$_html = str_replace("'", '"', $_html);
			$_html = str_replace("&yen;", '', $_html);
			$shipping = json_decode($_html);
			$ary = explode(' ', $shipping->postDesc->default);
			return (count($ary)>=2 && ($ary[1]=='免运费'))?0:$ary[1];
		}
	
		return 0;
	}
	
	private function downloadHtml($_url){
		$snoopy = new Snoopy ();
		$snoopy->agent = 'Mozilla/5.0 (Windows NT 6.1; rv:30.0) Gecko/20100101 Firefox/30.0';
		$snoopy->fetch ( $_url );
		return $snoopy->results;
	}
}