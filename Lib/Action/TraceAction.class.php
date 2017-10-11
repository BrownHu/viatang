<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 	包裹查询
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class TraceAction extends HomeAction {
	
	public function index(){
		$this->assign ('title','包裹查询-全球国际包裹物流查询-viatang.com');
	    $this->assign ('keywords','包裹查询网站，物流查询工具，包裹查询，国际包裹查询，DHL查询，EMS查询，UPS查询，邮政小包查询');
        $this->assign ('description','提供全球国际物流跟踪查询，DHL、EMS、UPS、邮政小包查询，邮政包裹查询，中国邮政包裹查询，国际快件查询');	
		$this->display();
	}
}
?>