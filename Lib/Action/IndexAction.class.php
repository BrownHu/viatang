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

import ( '@.ORG.Top.TopClient' );
import ( '@.ORG.Top.request.TbkItemsGetRequest' );

class IndexAction extends HomeAction {
	
	function _initialize() {
		parent::_initialize();
	}

	//---------------------------------------------------------------------------------------------
	public function index() {		
		$this->setReview();
		$this->setAnnounce();
		$this->setTopSite();
		$this->setBanner();
		$this->catBanner();

		$this->assign('f1_keywords',explode(',',C('F1_KEYWORDS')));		
		$this->assign('f2_keywords',explode(',',C('F2_KEYWORDS')));
		$this->assign('f3_keywords',explode(',',C('F3_KEYWORDS')));
		$this->assign('f4_keywords',explode(',',C('F4_KEYWORDS')));
		$this->assign('f5_keywords',explode(',',C('F5_KEYWORDS')));
		$this->assign('topProducts',$this->getIndexTopProducts());
		$this->display();
	}

	public function getHotWebSite(){
		$this->setTopSite();
		$list = $this->view->get('SiteList');
		echo json_encode($list);exit;
	}
	
	private function setBanner(){
		$this->dao = M('AdBanner');
		$this->_list('type=2','',100);
		$this->assign ( 'HomeBanner', $this->view->get('list') );
	}

	private function catBanner(){
		$this->dao = M('AdBanner');
		$this->_list('type=9','sort asc',100);
		$this->assign ( 'CategoryBanner', $this->view->get('list') );
	}

	private function getIndexTopProducts(){
		$changeFlag = C('F_PRODUCT_CHANGE_FLAG');
		$products = F('Index_Top_Sort');

		if($changeFlag == 1){
			$products = empty($products)?array():$products;

			for($i=0 ; $i <5 ;$i++){
				$products[$i] = empty($products[$i])?array():$products[$i];
				for($j=0 ; $j <8 ;$j++){
					$products[$i][$j] = empty($products[$i][$j])?array():$products[$i][$j];

					$url = C('F'.($i+1).'_PRODUCT_0'.($j+1));

					if($products[$i][$j]['url'] || $products[$i][$j]['url']!=$url){
						$product = A('Item')->getTaobaoProductInfo($url);

						$pos = strpos($url,"?");
						if($pos>=0){
							$url = substr($url,0,$pos);
						}
						$url .= '?id='.$product['id'];

						$products[$i][$j] = array(
							'url' => $url,
							'title' => @$product['title'],
							'image' => @$product['image'],
							'price' => !empty($product['activity_price'])?$product['activity_price']:@$product['price'],
						);
					}
				}
			}

			F('Index_Top_Sort',$products);
			C('F_PRODUCT_CHANGE_FLAG','2');

			$filename = rtrim(realpath(dirname(APP_PATH)), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'Conf/home.inc.php';
			if( file_exists($filename) ){
				$config = require $filename;
				$config['F_PRODUCT_CHANGE_FLAG'] = 0;

				$content = '<?php if (!defined("THINK_PATH")) exit(); return array(';
				foreach ($config as $k=>$v) {
					$v1 = (is_numeric($v) || is_bool($v) ) ? ( is_numeric($v) ? ( $v>=1000000000 ? "'".$v."'":$v ) : ( $v ? 'true' : 'false') )  : "'$v'"; //数字大数值保存时也加引号
					$content .= "'$k'=>" . trim($v1) .',';
				}
				$content = rtrim($content,',') . '); ?>';

				file_put_contents($filename,$content,LOCK_EX);
			}

			$path = realpath(dirname(APP_PATH).DIRECTORY_SEPARATOR.'Ulowi'.DIRECTORY_SEPARATOR.'Runtime');

			if(file_exists($path.DIRECTORY_SEPARATOR.'~allinone.php')){
				unlink($path.DIRECTORY_SEPARATOR.'~allinone.php');
			}
			if(file_exists($path.DIRECTORY_SEPARATOR.'~runtime.php')){
				unlink($path.DIRECTORY_SEPARATOR.'~runtime.php');
			}
		}

		return $products;
	}

	//---------------------------------------------------------------------------------------------
	//评论
	private function setReview(){
		$DAO = new Model();
		$ReviewList = $DAO->query("SELECT a.user_id, a.user_name,a.create_time,a.content,a.country from comment a  WHERE a.is_display=1  ORDER BY a.create_time Desc LIMIT 4 ");
		$this->assign ( 'ReviewList', $ReviewList );
	}
	
	//----------------------------------------------------------------------------------------------
	//公告
	private function setAnnounce(){
		$AnnounceList = M ( 'Announce' )->field('id,title,last_update')->order ( 'last_update desc' )->limit ( '0,4' )->select ();
		$this->assign ( 'AnnounceList', $AnnounceList );
	}	
	
	private function setTopSite(){
		$_list = M ( 'Site' )->limit(20)->order('RAND()')->select();
		$this->assign ( 'SiteList', $_list );
	}
}
?>