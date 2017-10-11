<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 商品导购
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

// import ( '@.ORG.59Miao.Api59miao' );
import ( 'ORG.Util.Page' );
load ( '@/toputils' );
load ( '@/functions' );
class MallAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		Session::set ( C ( 'RETURN_URL' ), MODULE_NAME . ',' . ACTION_NAME );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 子类
	public function so() {
		if (empty ( $_REQUEST ['q'] ) && empty ( $_REQUEST ['cid'] )) {
			return;
		}
		
		$pagesize = intval(C ( 'TAOKE_NUM_PER_PAGE' ));
		$PageNo = htmlspecialchars ( $_REQUEST ['p'] ); // 显示页
		$PageNo = (! empty ( $PageNo ) && is_numeric ( $PageNo )) ? $PageNo : 1;
		$PageNo = ($PageNo > 100) ? 100 : $PageNo;
		$kw = htmlspecialchars ( trim ( $_REQUEST ['q'] ) );
		$kw = $this->safeStr ( $kw );
		$cid = htmlspecialchars ( trim ( $_REQUEST ['cid'] ) );
		$cid = is_numeric ( $cid ) ? $cid : 0;
		$start_price = htmlspecialchars ( $_REQUEST ['sp'] ); // 起始价格
		$start_price = is_numeric ( $start_price ) ? $start_price : 0;
		$end_price = htmlspecialchars ( $_REQUEST ['ep'] ); // 结束价格
		$end_price = is_numeric ( $end_price ) ? $end_price : 49;
		$sort = $this->safeStr ( htmlspecialchars ( trim ( $_REQUEST ['sort'] ) ) );
		
		// 初始化
		$MallSoResult = array ();
		$CacheName = '';
		$query = '';
		$token = getAppToken2 ();
		$req = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,item_location,volume', $pagesize, 'commissionNum_desc' );
		dump();exit;
		if (! empty ( $cid )) {
			$cid = is_numeric ( $cid ) ? $cid : 16;
			$CacheName = 'Mall_cid_' . $cid . '_p' . $PageNo;
			$CacheTotal = 'Mall_so_total_cid_' . $cid;
			$CacheRecommt = 'Mall_cid_' . $cid;
			$req->setCid ( $cid );
			$CurrentPath = $this->getCategoryTitle ( $cid );
			$soUrl = "/cat/$cid/";
		} elseif (! empty ( $kw )) {
			$CacheName = 'Mall_kw_' . urldecode ( $kw ) . '_p' . $PageNo;
			$CacheTotal = 'Mall_so_total_kw_' . urldecode ( $kw );
			$CacheRecommt = 'Mall_kw_' . urldecode ( $kw );
			$req->setKeyword ( urldecode ( $kw ) );
			$CurrentPath = urldecode ( $kw );
			$soUrl = "/static/$CurrentPath/";
			$this->assign ( 'soearch_keyword', urldecode ( $kw ) );
		}
		
		// 是否过滤价格区间
		$filter_price = false;
		if (! empty ( $start_price ) && ! empty ( $end_price ) && ($start_price < $end_price)) {
			$CacheName = $CacheName . '_prc' . $start_price . $end_price;
			$query = "/sp/$start_price/ep/$end_price";
			$filter_price = true;
			$mp = $end_price;
		} else {
			if (! empty ( $_REQUEST ['mp'] )) {
				$mp = htmlspecialchars ( $_REQUEST ['mp'] );
			} else {
				$mp = 0;
			}
		}
		$mp = is_numeric ( $mp ) ? $mp : 0;
		$query = $query . "/mp/$mp";
		$this->assign ( 'mp', $mp );
		$soUrl = $soUrl . "mp/$mp";
		
		if (($sort == 'asc') || ($sort == 'desc')) {
			$CacheName = $CacheName . '_sort_' . $sort;
		}
		
		$MallSoResult = S ( $CacheName );
		$count = S ( $CacheTotal );
		if (empty ( $MallSoResult )) {
			$req->setPageNo ( $PageNo );
			if ($filter_price) {
				$req->setStartPrice ( $start_price );
				$req->setEndPrice ( $end_price );
			}
			$Reponse = getTopClient ( $token )->execute ( $req );
			$MallSoResult = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			$TotalResults = $Reponse->total_results;
			$count = ($TotalResults > $pagesize * 100) ? ($pagesize * 100) : $TotalResults;
			S ( $CacheName, $MallSoResult );
			S ( $CacheTotal, $count );
		}
		
		if (empty ( $MallSoResult )) {
			$s8_url = "http://s.taobao.com/search?q=" . urlencode ( iconv ( 'UTF-8', 'GBK//IGNORE', $kw ) ) . "&cat=$cid&ali_trackid=2:mm_13532438_0_0&search_type=item&unid=soitun_fan";
			$this->assign ( 's8_url', $s8_url );
			$HotData = F ( 'CatchHotData' );
			if (empty ( $HotData )) {
				$req->setSort ( 'commissionNum_desc' );
				$req->setKeyword ( '热卖' );
				$req->setPageSize ( 6 );
				$rep = getTopClient ( $token )->execute ( $req );
				$HotData = objToAry ( $rep->taobaoke_items->taobaoke_item );
			}
			$this->assign ( 'CatchHotData', $HotData );
		}
		
		// 热荐
		$CacheRecommt = $CacheRecommt . '-recomment';
		$RecommentData = F ( $CacheRecommt );
		if (empty ( $RecommentData )) {
			$req->setSort ( 'credit_desc' ); // 按信用倒排
			$req->setPageSize ( 5 );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$RecommentData = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			F ( $CacheRecommt, $RecommentData );
		}
		
		// 销量最多
		$CacheSalseHot = $CacheRecommt . '-hot';
		$SalesMost = F ( $CacheSalseHot );
		if (empty ( $SalesMost )) {
			$req->setSort ( 'commissionNum_desc' );
			$req->setPageSize ( 5 );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$SalesMost = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			F ( $CacheSalseHot, $SalesMost );
		}
		
		$p = new Page ( $count, $pagesize );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		str_replace('&', '/', $p->parameter);
		str_replace('=', '/', $p->parameter);
		$p->parameter = $p->parameter . $query;
		$PageBar = $p->show ();
		
		if ($filter_price) {
			$this->assign ( 'query', $query);
		}
		
		$this->assign ( 'sourl', $soUrl );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'RecommentData', $RecommentData );
		$this->assign ( 'SalesMost', $SalesMost );
		$this->assign ( 'MallSoResult', $MallSoResult );
		$this->assign ( 'PageBar', $PageBar );
		$this->assign ( 'PageNo', $PageNo );
		$this->assign ( 'TotalPages', $p->totalPages );
		$this->assign ( 'UlowiCurrentPath', $CurrentPath );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function shopItems() {
		$pagesize = C ( 'TAOKE_NUM_PER_PAGE' );
		$nicks = $this->safeStr ( htmlspecialchars ( trim ( $_REQUEST ['n'] ) ) );
		$PageNo = htmlspecialchars ( $_GET ['p'] ); // 显示页
		$PageNo = (! empty ( $PageNo ) && is_numeric ( $PageNo )) ? $PageNo : 1;
		$PageNo = ($PageNo > 100) ? 100 : $PageNo;
		
		if (! empty ( $nicks )) {
			
			$CacheName = md5 ( 'MALL_SAME_SHOP_' . $nicks );
			$MallSoResult = F ( $CacheName );
			if (empty ( $MallSoResult )) {
				$token = getAppToken ();
				$req = shopItemSearch ( $nicks );
				$rep = getTopClient ( $token )->execute ( $req );
				$req->setPageNo ( $PageNo );
				$Reponse = getTopClient ( $token )->execute ( $req );
				$MallSoResult = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
				$TotalResults = $Reponse->total_results;
				$count = ($TotalResults > $pagesize * 100) ? ($pagesize * 100) : $TotalResults;
				F ( $CacheName, $MallSoResult );
			} else {
				$count = count ( $MallSoResult );
			}
			
			$p = new Page ( $count, $pagesize );
			$p->setConfig ( 'first', '1' );
			$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
			$p->parameter = $p->parameter . "&n=$nicks";
			$PageBar = $p->show ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function categorylist() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail() {
		$id = trim ( $_GET ['id'] );
		redirect('http://item.taobao.com/item.htm?id='.$id);
		exit;
		if ($id && is_numeric ( $id ) && ($id != '13968765814')) {
			$token = getAppToken ();
			$c = getTopClient ( $token );
			$ItemCache = 'TAOBAO_ITEM_DETAIL_' . $id;
			if (S ( $ItemCache )) {
				$item = unserialize ( S ( $ItemCache ) );
				$seller_nick = $item ['seller_nick'];
				$cid = $item ['cid'];
			} else {
				$req = getItemDetail ( $id );
				$res = $c->execute ( $req );
				if ($res && $res->item) {
					$item ['iid'] = $res->item->iid;
					$item ['title'] = $res->item->title;
					$item ['imgs'] = objToAry ( $res->item->item_imgs->item_img );
					$seller_nick = $res->item->nick;
					$item ['seller_nick'] = $seller_nick;
					$item ['express_fee'] = $res->item->express_fee;
					// $item ['desc'] = $this->convertDes ( $res->item->desc );
					$item ['desc'] = $res->item->desc;
					$item ['type'] = $res->item->type;
					$item ['price'] = $res->item->price;
					$item ['pic_url'] = $res->item->pic_url;
					$item ['num'] = $res->item->num;
					$item ['state'] = $res->item->location->state;
					$item ['city'] = $res->item->location->city;
					$item ['stuff_status'] = $res->item->stuff_status;
					$item ['has_invoice'] = $res->item->has_invoice;
					$item ['detail_url'] = $res->item->detail_url;
					$cid = $res->item->cid;
					$item ['cid'] = $cid;
					$oskus = objToAry ( $res->item->skus->sku );
					$skus = array ();
					$i = 0;
					foreach ( $oskus as $sku ) {
						$skus [$i] ['price'] = $sku ['price'];
						$skus [$i] ['quantity'] = $sku ['quantity'];
						$propers = $sku ['properties_name'];
						$properAry = explode ( ';', $propers );
						if (strpos ( $properAry [0], '颜色' ) > 0) {
							$colorStr = str_replace ( '颜色:', '', $properAry [0] );
						} elseif (strpos ( $properAry [1], '颜色' ) > 0) {
							$colorStr = str_replace ( '颜色:', '', $properAry [1] );
						}
						
						$colorAry = explode ( ':', $colorStr );
						if ((strpos ( $properAry [0], '尺码' ) > 0) || (strpos ( $properAry [0], '尺寸' ) > 0)) {
							$sizeStr = $properAry [0];
						} else if ((strpos ( $properAry [1], '尺码' ) > 0) || (strpos ( $properAry [1], '尺寸' ) > 0)) {
							$sizeStr = $properAry [1];
						} else {
							$sizeStr = '';
						}
						$sizeAry = explode ( ':', $sizeStr );
						$skus [$i] ['color'] = urlencode ( $colorAry [count ( $colorAry ) - 1] );
						$skus [$i] ['size'] = urlencode ( $sizeAry [count ( $sizeAry ) - 1] );
						$i ++;
					}
					$item ['skus'] = json_encode ( $skus );
					
					// 颜色尺码表
					$colorList = array ();
					$sizeList = array ();
					foreach ( $skus as $sku ) {
						if (! empty ( $sku ['color'] ) && ! in_array ( $sku ['color'], $colorList )) {
							array_push ( $colorList, $sku ['color'] );
						}
						if (! empty ( $sku ['size'] ) && ! in_array ( $sku ['size'], $sizeList )) {
							array_push ( $sizeList, $sku ['size'] );
						}
					}
					
					$item ['color'] = $colorList;
					$item ['size'] = $sizeList;
					if (! S ( $ItemCache )) {
						S ( $ItemCache, serialize ( $item ) );
					}
				}
			}
			
			if (empty ( $item )) {
				$s8_url = "http://s.taobao.com/search?q=" . urlencode ( '热卖' ) . "&cat=$cid&ali_trackid=2:mm_13532438_0_0&search_type=item&unid=soitun_fan";
				$this->assign ( 's8_url', $s8_url );
				$HotData = S ( 'CatchHotData' );
				if (empty ( $HotData )) {
					$req1 = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,item_location,volume', 6, 'commissionNum_desc' );
					$req1->setSort ( 'commissionNum_desc' );
					$req1->setKeyword ( '热卖' );
					$req1->setPageSize ( 6 );
					$rep1 = $c->execute ( $req1 );
					$HotData = objToAry ( $rep1->taobaoke_items->taobaoke_item );
					S ( 'CatchHotData', $HotData );
				}
				$this->assign ( 'CatchHotData', $HotData );
			}
			// 获取掌柜基本信息
			/*
			 * $seller_cache = 'TAOBAO_SELLER_' . $seller_nick; $seller = F (
			 * $seller_cache ); if (empty ( $seller )) { $req2 = @getSellerInfo
			 * ( $seller_nick ); $seller = @$c->execute ( $req2 ); F (
			 * $seller_cache, $seller ); } if ($seller) { $plurl =
			 * $seller->user->user_id . "&auctionNumId=" . $id .
			 * "&showContent=2&currentPage=1&ismore=1&siteID=7"; }
			 */
			
			$CatchRelated = 'CACHE_SAME_CID_' . $cid;
			$sameCidProducts = S ( $CatchRelated );
			if (empty ( $sameCidProducts )) {
				$req3 = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,item_location,volume', 10, 'commissionNum_desc' );
				$req3->setCid ( $cid );
				$rep3 = $c->execute ( $req3 );
				$sameCidProducts = objToAry ( $rep3->taobaoke_items->taobaoke_item );
				S ( $CatchRelated, $sameCidProducts );
			}
			$this->assign ( 'RelatedProducts', $sameCidProducts );
			
			$CacheImgList = 'TAOBAO_ITEM_DETAIL_IMGLIST_' . $id;
			$imglst = S ( $CacheImgList );
			if (empty ( $imglst )) {
				$imglst = '';
				$i = 0;
				foreach ( $item ['imgs'] as $img ) {
					$imglst .= "<li id='imglst_$i' style='width:37px; padding:1px; margin:2px;' onmouseover='setcurrnet(this.id, \"imglst_sel\");'  class='left brd imglst_sel'><img src='../Public/images/grey.gif' lazy='y' original='" . $img ['url'] . "' width='35' height='35' onmouseover='changeimg(\"" . base64_encode ( $img ['url'] ) . "\",308,0);'></li>";
					$i ++;
				}
				if ($imglst != '') {
					$imglst = '<div style="width:310px;height:100%; overflow:hidden;" class="mrg14"><ul>' . $imglst . '</ul></div>';
					S ( $CacheImgList, $imglst );
				}
			}
			
			$this->assign ( 'imglst', $imglst );
			$this->assign ( 'item', $item );
			// $this->assign ( 'plurl', $plurl );
			$this->assign ( 'title', $item ['title'] );
			$this->display ();
		} else {
			$this->display ( 'Pubic:404' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function item() {
		$url = htmlspecialchars ( trim ( $_GET ['u'] ) );
		$url = str_replace ( '^', '/', $url );
		$url = base64_decode ( $url );
		
		if ($url != '') {
			header ( "Location:$url" );
		} else {
			header ( 'Location:/' );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function loadTopBanner() {
		$result = '';
		$category = htmlspecialchars ( trim ( $_GET ['t'] ) );
		$item = $this->ShowAd ( $category );
		if ($item) {
			$gourl = base64_encode ( trim ( $item ['url'] ) );
			$gourl = str_replace ( '/', '^', $gourl );
			$gourl = str_replace ( '==', '@', $gourl );
			$result = '<a href="/go/' . $gourl . '.html" target="_blank"><img id="mallTopbnr" src="http://img.'.C('DOMAIN') .'/ad/' . $item ['img'] . '.jpg" onload="showTopbnr(this.id);" style="width:988px; height:90px;display:none"></a>';
		}
		echo $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function loadChlBanner() {
		$result = '';
		$category = htmlspecialchars ( trim ( $_GET ['t'] ) );
		$key = urldecode ( htmlspecialchars ( trim ( $_GET ['k'] ) ) );
		$item = $this->loadAd ( $key, $category );
		if ($item) {
			$gourl = base64_encode ( trim ( $item ['url'] ) );
			$gourl = str_replace ( '/', '^', $gourl );
			$gourl = str_replace ( '==', '@', $gourl );
			$result = '<a href="/go/' . $gourl . '.html" target="_blank"><img id="mallChanlebnr" src="http://img.'.C('DOMAIN') .'/ad/' . $item ['img'] . '.jpg" onload="showTopbnr(this.id);" style="width:780px; height:250px;display:none"></a>';
		}
		echo $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function taobao_comment() {
		$plurl = htmlspecialchars ( $_GET ['u'] );
		$plurl = str_replace ( '^', '/', $plurl );
		$plurl = base64_decode ( $plurl );
		$result = '';
		if ($plurl != '') {
			$CacheUrl = 'TAOBAO_COMMENT_' . md5 ( $plurl );
			$result = F ( $CacheUrl );
			if (empty ( $result )) {
				$s = file_get_contents_ex ( "http://rate.taobao.com/detail_rate.htm?userNumId=" . $plurl );
				$s = str_replace ( 'TB.detailRate = ', '', $s );
				$s = trim ( mb_convert_encoding ( $s, "utf-8", "gb2312" ) );
				
				$web = json_decode ( $s );
				$arr = $this->json_to_array ( $web );
				$pjarr = $arr ['rateListInfo'] ['rateList'];
				
				foreach ( $pjarr as $row ) {
					$result .= '<li style="width:760px; display:block; line-height:20px; height:100%;overflow:hidden;padding-top:2px; border-bottom:1px solid #ddd;" class="middle">' . '  <div style="width:560px; line-height:20px;margin-top:5px; margin-bottom:5px;" class="left">' . $row->rateContent . '<br><font color="#999999">[' . $row->rateDate . ']</font>' . '  </div>' . '  <div style="width:180px; line-height:20px;margin-top:5px; margin-bottom:5px;" class="right">' . '     买家：' . $row->displayUserNick;
					
					if ($row->displayRatePic != '') {
						$result .= '<br><img src="http://img.'.C('DOMAIN') .'/level/' . $row->displayRatePic . '" />';
					}
					$result .= '  </div>' . '</li>';
				}
				F ( $CacheUrl, $result );
			}
		}
		echo $result;
	}
	
	//------------------------------------------------------------------------------------------------
	public function tcategory() {
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	// 加载首页类别
	public function indexcat() {
		echo $this->fetch ( 'indexcat' );
	}
	
	// -----------------------------------------------------------------------------
	private function getCategoryTitle($cid) {
		if (is_numeric ( $cid ) && ($cid > 0)) {
			$path = "CurrentPath_$cid";
			if (F ( $path )) {
				$CurrentPath = F ( $path );
			} else {
				$CategoryAry = require "Conf/converter.php";
				$len = count ( $CategoryAry );
				$CurrentPath = '';
				for($i = 1; $i < $len; $i ++) {
					$m = explode ( ",", $CategoryAry [$i] );
					$xiabiao = trim ( $m [0] );
					if ($xiabiao == $cid) {
						$CurrentPath = $m [2];
						break;
					}
				}
				if (strlen ( $CurrentPath ) > 0) {
					F ( $path, $CurrentPath );
				} else {
					F ( $path, '' );
				}
			}
		} else {
			$CurrentPath = '';
		}
		return $CurrentPath;
	}
	
	//------------------------------------------------------------------------------------------------
	private function getBrandList($key) {
		$BrandAry = require 'Conf/MallBrand.php';
		return $BrandAry [$key];
	}
	
	//------------------------------------------------------------------------------------------------
	// 类别搜索
	private function doSearch($cid, $fix, $PgNo, $var, $tpl, $sort) {
		$pagesize = C ( 'TAOKE_NUM_PER_PAGE' );
		$PageNo = (! empty ( $PgNo ) && is_numeric ( $PgNo )) ? $PgNo : 1;
		$PageNo = ($PageNo > 100) ? 100 : $PageNo;
		$CacheName = $fix . $cid . '_' . $PageNo;
		$CacheTotal = $fix . $cid . 'Total';
		$token = getAppToken2();
		$req = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,item_location,volume', $pagesize, 'commissionVolume_desc' );
		
		if (($sort == 'asc') || ($sort == 'desc')) { // 直接缓存排序后的数据，用空间换时间，提升效率
			$CacheName = $CacheName . '_sort_' . $sort;
		}
		
		$MallData = S ( $CacheName );
		$count = S ( $CacheTotal );
		if (empty ( $MallData )) {
			$req->setPageNo ( $PageNo );
			$req->setCid ( $cid );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$MallData = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			$TotalResults = $Reponse->total_results;
			$count = ($TotalResults > $pagesize * 100) ? ($pagesize * 100) : $TotalResults;
			S ( $CacheName, $MallData );
			S ( $CacheTotal, $count );
		}
		
		// 排序
		// if (($sort == 'asc') || ($sort == 'desc')) {
		// $MallData = list_sort_by ( $MallData, 'price', $sort );
		// }
		
		// 热荐
		$CacheRecommt = $fix . $cid . '-recomment';
		$RecommentData = F ( $CacheRecommt );
		if (empty ( $RecommentData )) {
			$req->setSort ( 'credit_desc' ); // 按信用倒排
			$req->setPageSize ( 5 );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$RecommentData = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			F ( $CacheRecommt, $RecommentData );
		}
		
		// 销量最多
		$CacheSalseHot = $fix . $cid . 'hot';
		$SalesMost = F ( $CacheSalseHot );
		if (empty ( $SalesMost )) {
			$req->setSort ( 'commissionNum_desc' );
			$req->setPageSize ( 5 );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$SalesMost = objToAry ( $Reponse->taobaoke_items->taobaoke_item );
			F ( $CacheSalseHot, $SalesMost );
		}
		$CurrentPath = $this->getCategoryTitle ( $cid );
		
		$p = new Page ( $count, $pagesize );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$p->parameter = $p->parameter . '&sort=' . $sort;
		$PageBar = $p->show ();
		
		$this->assign ( 'sort', $sort );
		$this->assign ( 'RecommentData', $RecommentData );
		$this->assign ( 'SalesMost', $SalesMost );
		$this->assign ( $var, $MallData );
		$this->assign ( 'PageBar', $PageBar );
		$this->assign ( 'PageNo', $PageNo );
		$this->assign ( 'TotalPages', $p->totalPages );
		$this->assign ( 'UlowiCurrentPath', $CurrentPath );
		
		$this->display ( $tpl );
	}
	
	//------------------------------------------------------------------------------------------------
	private function ShowAd($type) {
		$result = false;
		if (! empty ( $type ) && is_numeric ( $type )) {
			$DAO = M ( 'AdBanner' );
			$count = $DAO->where ( "type=$type AND status=1" )->count ();
			if ($count > 0) {
				$DataList = $DAO->where ( "type=$type AND status=1" )->select ();
				$i = rand ( 0, $count - 1 );
				$result = $DataList [$i];
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 根据关键词和类别自动加载广告
	private function loadAd($key, $type = 0) {
		$DAO = M ( 'AdBanner' );
		$result = false;
		$keyword = $this->safeStr ( $key );
		if (! empty ( $keyword )) {
			$count = $DAO->where ( "keyword like '%$keyword%' AND status=1" )->count ();
			if ($count > 0) {
				$DataList = $DAO->where ( "keyword like '%$keyword%' AND status=1" )->select ();
				$i = rand ( 0, $count - 1 );
				$result = $DataList [$i];
			}
		} elseif (! empty ( $type ) && is_numeric ( $type )) {
			$result = $this->ShowAd ( $type );
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	// 输出安全的html //输出安全的html
	private function safeStr($text, $tags = null) {
		$text = trim ( $text );
		$text = str_ireplace ( 'win.ini', '', $text );
		$text = str_ireplace ( 'passwd', '', $text );
		$text = str_ireplace ( '<?php', '', $text );
		$text = str_replace ( '?>', '', $text );
		$text = str_replace ( "'", '', $text );
		
		// 完全过滤注释
		$text = preg_replace ( '/<!--?.*-->/', '', $text );
		// 完全过滤动态代码
		$text = preg_replace ( '/<\?|\?' . '>/', '', $text );
		// 完全过滤js
		$text = preg_replace ( '/<script?.*\/script>/', '', $text );
		
		$text = str_replace ( '[', '', $text );
		$text = str_replace ( ']', '', $text );
		$text = str_replace ( '|', '', $text );
		// 过滤换行符
		$text = preg_replace ( '/\r?\n/', '', $text );
		// br
		$text = preg_replace ( '/<br(\s\/)?' . '>/i', '', $text );
		$text = preg_replace ( '/(\[br\]\s*){10,}/i', '', $text );
		// 过滤危险的属性，如：过滤on事件lang js
		while ( preg_match ( '/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat ) ) {
			$text = str_replace ( $mat [0], $mat [1], $text );
		}
		while ( preg_match ( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat ) ) {
			$text = str_replace ( $mat [0], $mat [1] . $mat [3], $text );
		}
		// 过滤错误的单个引号
		while ( preg_match ( '/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat ) ) {
			$text = str_replace ( $mat [0], str_replace ( $mat [1], '', $mat [0] ), $text );
		}
		// 转换其它所有不合法的 < >
		$text = str_replace ( '<', '', $text );
		$text = str_replace ( '>', '', $text );
		$text = str_replace ( '"', '', $text );
		// 反转换
		$text = str_replace ( '[', '', $text );
		$text = str_replace ( ']', '', $text );
		$text = str_replace ( '|', '', $text );
		// 过滤多余空格
		$text = str_replace ( '  ', ' ', $text );
		return $text;
	}
	
	//------------------------------------------------------------------------------------------------
	private function json_to_array($web) {
		$arr = array ();
		foreach ( $web as $k => $w ) {
			if (is_object ( $w ))
				$arr [$k] = $this->json_to_array ( $w ); // 判断类型是不是object
			else
				$arr [$k] = $w;
		}
		return $arr;
	}
	
	//------------------------------------------------------------------------------------------------
	private function convertDes($des) {
		if (trim ( $des ) != '') {
			$des = str_replace ( '"', '\'', $des );
			$des = str_ireplace ( 'alt=\'\'', '', $des );
			// $des = str_ireplace ( 'src=', ' original=', $des );
			// $des = str_ireplace ( '<img', '<img
		// src=\'../Public/images/grey.gif\' lazy=\'y\' ', $des );
		}
		return $des;
	}
	
	//------------------------------------------------------------------------------------------------
	// 根据类别加载数据列表
	private function getDataById($id, $CacheName, $count = 5) {
		$result = S ( $CacheName );
		if (empty ( $result )) {
			if (! empty ( $id )) {
				
					$token = getAppToken2();
					$c = getTopClient ( $token );
					$req = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,keyword_click_url', $count, 'commissionNum_desc' );
					$req->setPageSize ( $count );
					$req->setCid ( $id );
					$Obj = $c->execute ( $req );
					$result = objToAry ( $Obj->taobaoke_items->taobaoke_item );
				
				S ( $CacheName, $result );
			} else {
				$result = array ();
			}
		}
		return $result;
	}
	
	//------------------------------------------------------------------------------------------------
	private function callRemoteDataList($id,$count){
		try {
			import ( 'ORG.Util.Snoopy' );
			$client = new Snoopy ();
			$client->agent = "(compatible; MSIE 4.01; MSN2.5; AOL 4.0; Windows 98)";
			$client->rawheaders ["Pragma"] = "no-cache";
				
			$client->fetch(C('FETCH_PROX_URI').'lst/i/'.$id.'/c/'.$count);
			if($client->results != '0'){
				return json_decode($client->results,true);
			}else{
				return false;
			}
		}catch(Exception $e ){
			return false;
		}
	}
}

?>