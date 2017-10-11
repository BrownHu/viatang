<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 最新代购商品列表
 +------------------------------------------------------------------------------
 * @copyright 上海子凌网络科技有限公司
 * @author    stone
 * @version   1.0
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
class SeeAction extends HomeAction {
	
	//------------------------------------------------------------------------------------------------
	function _initialize() {
		parent::_initialize();
		$this->dao = D ( 'Product' );
		
		$ProductTypeList = S ( 'ProductTypeList' );
		if (empty ( $ProductTypeList )) {
			$ProductTypeList =  M ( 'ProductType' )->where ( 'status=1' )->select ();
			S ( 'ProductTypeList', $ProductTypeList );
		}
		$this->assign ( 'ProductTypeList', $ProductTypeList );
	}
	
	//------------------------------------------------------------------------------------------------
	public function index() {		
		$condition = "(thumb != '') AND (image != '')   AND (recommend=1)";//
		$count = $this->dao->relation ( true )->where ( $condition )->count ();

		$DataList = $this->dao->relation ( true )->field ( 'id,thumb,image,title,user_id,user_name,create_time,url,like_count,review_count,price1' )->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc,like_count desc' )->select ();
		
		$p = new Page ( $count, 52 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$page = $p->show ();
		
		$col = 4;
		$index = 0;
		$share_display = array ();
		foreach ( $DataList as $share ) {
			$mod = $index % $col;
			$share_display ['col' . $mod] [] = $share;
			$index ++;
		}
		
		$this->assign ( 'DataList', $share_display );
		$this->assign ( 'page', trim($page) );
		$this->assign ( 'TotalPages', $p->totalPages );
		$this->assign ( 'category', 0 );
		$this->display ();
	}
	
	//------------------------------------------------------------------------------------------------
	//显示详情
	//public function detail() {
		//$id = $_GET ['id'];
		//Session::set (C ( 'RETURN_URL' ), 'show,' . $id );
		//if (! empty ( $id ) && is_numeric ( $id )) {
			//$entity = $this->dao->where ( "id=$id and recommend=1" )->find ();
			//if ($entity) {
				//$this->assign ( 'entity', $entity );
				//$this->assign ( 'category', $entity ['product_type'] );
				//$u_id = $entity ['user_id'];
				
				//$LikeOrder = $this->dao->where ( "user_id=$u_id AND thumb != ''  AND image != ''  AND like_count > 0" )->order ( 'like_count desc' )->limit ( '0,9' )->select ();
				//$this->assign ( 'LikeOrder', $LikeOrder );
				
				//更多
				//$MoreList = S ( 'SEE_DETAIL_MORE' );
				//if (empty ( $MoreList )) {
					//$condition = "thumb != '' AND image != '' AND recommend=1 AND product_type=" . $entity ['product_type'];
					//$MoreList = $this->dao->relation ( true )->field ( 'id,thumb,image,title,user_id,user_name,create_time,url,like_count,review_count,price1' )->where ( $condition )->limit ( '0,12' )->order ( 'create_time desc, like_count asc' )->select ();
					//S ( 'SEE_DETAIL_MORE', $MoreList );
				//}
				
				//$col = 4;
				//$index = 0;
				//$share_display = array ();
				//foreach ( $MoreList as $share ) {
					//$mod = $index % $col;
					//$share_display ['col' . $mod] [] = $share;
					//$index ++;
				//}
				
				//$this->assign ( 'MoreList', $share_display );
				
				//加载商品的共同喜欢人
				//$LikeList = M ( 'UserLike' )->where ( 'product_id=' . $id )->limit ( 0, 26 )->select ();
				//$this->assign ( 'LikeList', $LikeList );
				
				//加载商品的评论列表
				//$user = Session::get ( C ( 'MEMBER_INFO' ) );
				//$condition = "product_id=$id";
				//if ($user) {
					//$condition .= ' AND (status>0 OR user_id=' . $user ['id'] . ' )';
				//} else {
					//$condition .= ' AND status>0 ';
				//}

				//$ReviewList =  M ( 'ProductReview' )->where ( $condition )->order ( 'create_at desc' )->select ();
				//$this->assign ( 'ReviewList', $ReviewList );
				
				//加载此用户的商品总数
				//$p_count = $this->dao->where ( "user_id=$u_id" )->count ();
				//$like_count = $this->dao->where ( "user_id=$u_id AND like_count >0 " )->count ();
				//$this->assign ( 'ProductCount', $p_count );
				//$this->assign ( 'LikeCount', $like_count );
				//$this->assign ( 'title', $entity ['title'] );
			//}else{
				//$this->redirect('index');
			//}
		//}
		//$this->display ();
	//}
	
	//------------------------------------------------------------------------------------------------
	public function s() {
		$this->assign( 'title','最新代购商品推荐—提供一站式代购淘宝商品平台—viatang.com');
		$this->assign ('keywords','代购，代购网站，淘宝代购，美国淘宝，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
		$this->assign ('description','最新淘宝代购商品推荐，为海外华人、留学生一站式提供代购中国商品，淘宝网不同店铺商品集中打包配送全球，享国际运费最低3折起');
		$c = $_REQUEST ['c'];
		$condition = "thumb != '' AND image != ''   AND recommend=1 ";
		if (! empty ( $c ) && is_numeric ( $c )) {
			$condition = $condition . " AND product_type=$c";
			$this->assign ( 'category', $c );
		} else {
			$this->assign ( 'category', 0 );
		}

		$count = $this->dao ->relation ( true )->where ( $condition )->count ();
		$p->parameter .= "&c=$c";
		$p = new Page ( $count, 52 );
		$p->setConfig ( 'first', '1' );
		$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
		$page = $p->show ();
		$DataList = $this->dao ->relation ( true )->field ( 'id,thumb,image,title,user_id,user_name,create_time,url,like_count,review_count,price1' )->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
		
		$col = 4;
		$index = 0;
		$share_display = array ();
		foreach ( $DataList as $share ) {
			$mod = $index % $col;
			$share_display ['col' . $mod] [] = $share;
			$index ++;
		}
		
		$this->assign ( 'DataList', $share_display );
		$this->assign ( 'page', trim($page) );
		$this->assign ( 'TotalPages', $p->totalPages );
		if(isset($_GET['t'])){
		    $this->assign('title',urldecode($_GET['t']).'淘宝代购-viatang.com');
		    $this->assign ('keywords',urldecode($_GET['t']).'服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
		}
		$this->display ( 'index' );
	 }
	
	//------------------------------------------------------------------------------------------------
	//指定用户的宝贝
	public function userProduct() {
		$uid = $_GET ['id'];
		if (! empty ( $uid ) && is_numeric ( $uid )) {
			$DataList = $this->dao ->where ( 'user_id=' . $uid )->limit ( '0,50' )->order ( 'id desc' )->select ();
			$this->assign ( 'DataList', $DataList );
		}
		$this->display ();
	}	
}
?>