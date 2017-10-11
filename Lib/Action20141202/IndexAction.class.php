<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 首页
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class IndexAction extends HomeAction {

	function _initialize() {
		parent::_initialize();
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {		
		$this->setReview();
		$this->setAnnounce();
		$this->setFriendLink();
		$this->setZhekou();
		$this->setFashion();	
		parent::index();
	}

	//--------------------------------------------------------------------------------------------------------------
	//评论
	private function setReview(){
		//$ReviewList = S ( 'IndexReviewList' );
		//if (empty ( $ReviewList )) {
			$DAO = new Model();
			$ReviewList = $DAO->query("SELECT a.user_id, a.user_name,a.create_time,a.content,a.country from comment a  WHERE a.is_display=1  ORDER BY a.create_time Desc LIMIT 4 ");
		//	S ( 'IndexReviewList', $ReviewList );
		//}
		$ProductTypeList =  M ( 'ProductType' )->where ( 'status=1' )->limit('0,5')->select ();
		$this->assign('ProductTypeList',$ProductTypeList);
		
		$this->assign ( 'ReviewList', $ReviewList );
	}
	
	//------------------------------------------------------------------------------------------------
	//公告
	private function setAnnounce(){
		//$AnnounceList = S ( 'IndexAnnounceList' );
		//if (empty( $AnnounceList )) {
			$AnnounceList = M ( 'Announce' )->field('id,title,last_update')->order ( 'last_update desc' )->limit ( '0,3' )->select ();
			//S ( 'IndexAnnounceList', $AnnounceList );
		//}
		$this->assign ( 'AnnounceList', $AnnounceList );
	}
	
	//------------------------------------------------------------------------------------------------
	//友情链接
	private function setFriendLink(){
		$LinkList = S('ULOWI_SITE_LINK');
		if(empty($LinkList)){
			$LinkList =  M ( 'SiteLink' )->where('status=1')->select ();
			S('ULOWI_SITE_LINK',$LinkList);
		}
		$this->assign('SiteList',$LinkList);
	}
	
	//------------------------------------------------------------------------------------------------
	//折扣
	private function setZhekou(){
		$CampaignList = S ( 'WebIndexCampaign' );
		if (empty( $CampaignList)) {
			$CampaignList = M ( 'Campaign' )->field('url,title')->where ( 'status=1 AND hot=1' )->limit ( '0,3' )->order ( 'sort asc' )->select ();
			S ( 'WebIndexCampaign', $CampaignList );
		}
		$this->assign ( 'CampaignList', $CampaignList );
	}
	
	//------------------------------------------------------------------------------------------------
	//专题 
	private function setFashion(){
		$fasition = S('ULOWI_INDEX_FASHION');
		if(empty($fasition)){
			$fasition =  M ( 'Fashion' )->order('id desc')->limit('0,4')->select ();
			S('ULOWI_INDEX_FASHION',$fasition);
		}
		$this->assign('fastion',$fasition);
	}
}
?>