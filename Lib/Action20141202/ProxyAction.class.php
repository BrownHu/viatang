<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 代理功能
 * 实现国外服务器连接国内服务器，通过国内服务器抓数据
 *
 +------------------------------------------------------------------------------
 * @category
 * @package
 * @subpackage
 * @author    soitun <stone@ulowi.com>
 * @version   Id
 +------------------------------------------------------------------------------
 */
load ( '@/functions' );
load ( '@/toputils' );
class ProxyAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$id = trim ( $_REQUEST ['i'] );		
		if ($id && is_numeric($id)) {
			$token = getAppToken2 ();
			import ( '@.ORG.Top.request.ItemGetRequest' );
			$req = new ItemGetRequest ();
			$req->setFields ( 'detail_url,title,nick,pic_url,price,express_fee' );
			$req->setNumIid ( $id );
			$Reponse = getTopClient ( $token )->execute ( $req );
			$productAry = get_object_vars ( $Reponse->item );
			if ($productAry) {
				$product_info = array (
						'url' => $productAry ['detail_url'],
						'title' => $productAry ['title'],
						'price' => $productAry ['price'],
						'shipping_fee' => $productAry ['express_fee'],
						'seller' => $productAry ['nick'],
						'image' => $productAry ['pic_url'] 
				);
				
				echo   json_encode ( $product_info ,JSON_UNESCAPED_UNICODE);
			} else {
				echo '0';
			}
		} else {
			echo '0';
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function lst(){
		$id = trim ( $_REQUEST ['i'] );
		$count = trim($_REQUEST ['c']);
		if (! empty ( $id ) && !empty($count)) {
			$token = getAppToken2();
			$c = getTopClient ( $token );
			$req = getItemsRequest ( $token->nick, 'num_iid,title,nick,pic_url,price,click_url,shop_click_url,keyword_click_url', $count, 'commissionNum_desc' );
			$req->setPageSize ( $count );
			$req->setCid ( $id );
			
			$Obj = $c->execute ( $req );
			dump($Obj->taobaoke_items->taobaoke_item);		
		} else {
			echo '0';
		}
	}
}
?>