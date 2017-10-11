<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 政策法规
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

class FaguiAction extends Action{
	
	//------------------------------------------------------------------------------------------------
	public function index(){
		$this->assign ('title','代购商品包装原则-viatang.com');
	    $this->assign ('keywords','淘宝代购，中国商品代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
        $this->assign ('description','海外华人、留学生一站式代购中国商品，商品集中打包配送至海外，国际运费最低3折起');
		$this->display();
	}

	//------------------------------------------------------------------------------------------------
	public function topic(){
		$this->assign ('title','代购商品发货规则-viatang.com');
	    $this->assign ('keywords','UPS发货规则，EMS发货规则，DHL发货规则，邮政小包发货规则，海关政策，禁运商品，国际包裹附加费');
        $this->assign ('description','代购商品范围及国际运输方式限制，UPS发货规则，EMS发货规则，DHL发货规则，邮政小包发货规则');
		$tpl = trim($_REQUEST['c']);
		if( $tpl == 'repackage'){
			$this->assign ('title','重新包装标准-viatang.com');
		}
		
		if($tpl == 'ups'){
			$this->assign ('title','UPS发货规则-viatang.com');
		}
		
		if( $tpl == 'ems'){
			$this->assign ('title','EMS发货规则-viatang.com');
		}
		
		if( $tpl == 'air'){
			$this->assign ('title','邮政小包发货规则-viatang.com');
		}
		
		if( $tpl == 'fee'){
			$this->assign ('title','UPS附加费-viatang.com');
		}
		
		if( $tpl == 'custom'){
			$this->assign ('title','海关政策-viatang.com');
		}
		
		if( $tpl == 'limit'){
			$this->assign ('title','禁限邮物品-viatang.com');
		}
		$tpl = (!empty($tpl))?$tpl:'index';
		$this->display($tpl);
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ( 'index' );
	}
}
?>