<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 	订单提醒
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author     soitun <stone@zline.net.cn>
 * @copyright  上海子凌网络科技有限公司
 * @license    http://www.zline.net.cn/license-agreement.html
 * @link       http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
load ( '@/functions' );
class OrdernoticeAction extends Action {
	public function index(){
		$dao = M('MailTip');
		$_list = $dao->field('user_id')->where('tag=0')->group('user_id')->select();
		foreach ($_list as $_r){
			$_u_tip = $dao->where('user_id='.$_r['user_id'].' and tag=0')->order('status asc')->select();
			
			foreach ($_u_tip as $_t){
				$tpl = '';
				switch ($_t['status']){
					case 2 :$tpl='MailClz';break;
					case 3 :$tpl='MailYdg';break;
					case 10 :$tpl='MailThhclz';break;
					case 7 :$tpl='MailWh';break;
					case 9 :$tpl='MailWx';break;
					case 4 :$tpl='MailYdh';break;
					case 11 :$tpl='MailYth';break;
					case 11 :$tpl='MailZsqh';break;
				}
				
				$DataList = $this->loadProduct($_t['ids']);
				
				if($DataList){
					$this->assign('order_id',$DataList[0]['order_id']);
					$this->assign('product_id',$DataList[0]['id']);
					$this->assign('to',$DataList[0]['user_name']);
				}
				$this->assign('DataList',$DataList);
				$mail_content = $this->fetch('Public:'.$tpl,'utf-8','text/html',false);

				//echo $html;
				addToMailQuen('noreply@'.C('DOMAIN'),C('SITE_NAME'),C('MANG_NOTIFY_MAIL'),'订单提醒',$mail_content,'');
			}
		}
		
	}
	
	//加载商品信息
	private function loadProduct($ids){
		return M('Product')->where("id in ('$ids')")->select();
	}
}
?>