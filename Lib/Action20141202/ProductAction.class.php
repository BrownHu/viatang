<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 商品
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */


import ( 'ORG.Util.Page' );
class ProductAction extends Action {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		$this->display ();
	}

	//------------------------------------------------------------------------------------------------
	public function like() {
		$pid = $_GET ['pid'];
		if ($this->user && ! empty ( $pid )) {
			$like_count = $this->writeLike ( $this->user ['id'], $this->user['login_name'], $pid );
			$this->updatLike($like_count,$pid);
			echo $like_count;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function detail(){
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	//商品评论
	public function commit(){
		$pid = $_POST['pid'];
		$content = $_POST['content'];
		if ($this->user && (!empty($pid)) && (!empty($content))) {
			$DAO  = M('ProductReview');
			$icount = $DAO->where("user_id=".$this->user['id'] ." AND product_id=$pid")->count();
			if($icount == 0){
				$data['user_id'] 	= $this->user['id'];
				$data['user_name'] 	= $this->user['login_name'];
				$data['product_id'] = $pid;
				$data['content'] 	= $content;
				$data['create_at']	= time();

				$id = $DAO->data($data)->add();
				if(!empty($id)){
					$count =  $DAO->where('product_id='.$pid)->count();
					$this->updateReview($count,$pid);
				}
			}
		}
		$this->redirect("show/$pid");
	}

	//------------------------------------------------------------------------------------------------
	//ajax 获取商品评论列表 ,目前保留
	public function getReviewList(){
		$pid = $_GET['pid'];
		if(!empty($pid)){
			$DAO = M('ProductReview');
			$DataList = $DAO->where("product_id=$pid")->order('create_at desc')->select();
			$this->assign('DataList',$DataList);
		}
	}

	//------------------------------------------------------------------------------------------------
	public function _empty() {
		$this->redirect ( 'index' );
	}

	//-------------------------------------------------------------------------------------------
	//内部功能函数
	//-------------------------------------------------------------------------------------------
	private function updateReview($count, $pid){
		if (! empty ( $count )) {
			$DAO = M ( 'Product' );
			$data ['review_count'] = $count;
			$DAO->where ( 'id=' . $pid )->save ( $data );
		}
	}

	//------------------------------------------------------------------------------------------------
	private function updatLike($count, $pid) {
		if (! empty ( $count )) {
			$DAO = M ( 'Product' );
			$data ['like_count'] = $count;
			$DAO->where ( 'id=' . $pid )->save ( $data );
		}
	}

	//------------------------------------------------------------------------------------------------
	private function writeLike($uid,$un, $pid) {
		$DAO = M ( 'UserLike' );
		$count = $DAO->where ( 'user_id=' . $uid . ' AND product_id=' . $pid )->count ();
		if ($count == 0) {
			$data ['user_id'] = $uid;
			$data ['user_name'] = $un;
			$data ['product_id'] = $pid;
			$DAO->data ( $data )->add ();
		}
		return $DAO->where ( 'product_id=' . $pid )->count ();
	}
}

?>