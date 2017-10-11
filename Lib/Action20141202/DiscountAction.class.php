<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 优惠促销
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( '@.ORG.59Miao.Api59miao' );
import ( 'ORG.Util.Page' );
load ( '@/functions' );

class DiscountAction extends HomeAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		
	 	//加载商品类别
		/*$CampaignList = S ( 'CampaignProductTypepro' );
		if (empty ( $CampaignList )) {
			$TypeList = M ( 'CampaignProducttype' )->where ( 'status=1 AND parent_cid=0' )->select ();
			S ( 'CampaignProductTypepro', $TypeList );
		}
		$this->assign ( 'TypeList', $CampaignList );*/
	}

	//------------------------------------------------------------------------------------------------	
	public function index() {
		$this->display();
		/*$PageNo = $_GET ['p'];
		$PageNo = safeFilter($PageNo);
		$PageNo = (! empty ( $PageNo ) && is_numeric($PageNo)) ? $PageNo : 1;
		$sid = !empty($_REQUEST['s'])?$_REQUEST['s']:'';
		$cid = !empty($_REQUEST['c'])?$_REQUEST['c']:'';
		$cache_key = 'ULOWI_DISCOUTN_SID'.$sid.'_CID_'.$cid.'_P_'.$PageNo;
		$list = S($cache_key);
		if(empty($list)){
			$client 	= new Api59miao ( get59MiaoConfig () );
			$fields 	= 'pid,title,click_url,seller_logo,start_time,end_time,sid,seller_name,seller_url,pic_url_1,pic_url_2,pic_url_3';		
			$result 	= $client->ListPromosListGet($fields,$sid,$cid,"$PageNo",'20'); 		
			$list     	= $result['promos']['promo'];
			//$list       = array_unique($list);
			$total      = $result['total_results'];
			S($cache_key,$list);
			S($cache_key.'_total',$total);
		}else{
			$total = S($cache_key.'_total');
		}
		$p = new Page ( $total, 20 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$page = $p->show ();
		
		$PageNo = ($PageNo > $total) ? $total : $PageNo;
		$this->assign ( 'PageNo', $PageNo );
		$this->assign ( 'TotalPages', $total);
		$this->assign('DataList',$list);
		$this->assign('page',$page);
		$this->assign ( 'title', L('discount_title') );
		$this->display ();*/
	} 
  	
	//------------------------------------------------------------------------------------------------
	private function getCategory(){
		$client = new Api59miao ( get59MiaoConfig () );
		$fields = 'cid,parent_cid,name';
		$list=$client->ListPromoCats($fields,'');
		$parent = $list['promo_cats']['promo_cat'];
		$category = array();
		foreach ($parent as $p){
			$data = $client->ListPromoCats($fields,$p['cid']);
			$category[] = $data['promo_cats']['promo_cat'];
		}
		
		foreach ($parent as $p){
			$item['cid'] = intval($p['cid']);
			$item['caption'] = $p['name'];
			$item['parent_cid'] = intval($p['parent_cid']);
			M('CampaignProducttype')->data($item)->add();
		}
		
		foreach ($category as $l){
			foreach($l as $r){
				if(trim($r['name']) == ''){continue;}
				if(trim($r['name']) == '1'){continue;}
				if(trim($r['cid']) == 0){continue;}
				$item['cid'] = $r['cid'];
				$item['caption'] = $r['name'];
				$item['parent_cid'] = $r['parent_cid'];
				M('CampaignProducttype')->data($item)->add();
			}
		}
	}	
}
?>