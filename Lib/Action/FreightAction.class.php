<?php
//运费模块
class FreightAction extends Action{
	//定义一个add操作方法
	 public function index(){
		$this->assign ('title','超低国际运费价目表—一站式代购淘宝商品平台-viatang.com');
	    $this->assign ('keywords','国际物流，国际运费，包裹运费，物流运费，DHL，EMS，UPS，邮政小包，国际物流折扣，淘宝代购，中国商品代购，服装代购，饰品代购，包包代购，图书代购，食品代购，生活用品代购');
        $this->assign ('description','专为海外华人、留学生提供一站式代购中国商品，免费代购淘宝商品，集中打包配送全球服务，享国际运费最低3折起');
		$this->display();//输出页面模板
	}
}