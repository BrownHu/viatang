<?php
//与业务相关的公共数组定义
if (!defined('THINK_PATH')) exit();

//后台订单状态
$order_status_array = array(
1 =>'未处理',
2 =>'已处理'
);

//后台商品状态
$product_status_array = array(
1 => '未处理',
2 => '处理中',
3 => '已订购',
4 => '已到货',
5 => '已打包',
6 => '已退单',
7 => '无货',
8 => '暂时缺货',
9 => '无效',
10 => '退换货处理中',
11 => '已退货',
12 => '已入库',
13 => '补款处理中',
14 => '二检完成',
15 => '超期订单',//入库起3个月
16 => '作废订单',//入库起4个月
17 => '已放入送货车',
18 => '二检中'	,
19 => '二检不通过'	
);

//会员商品状态
$product_status_array_user = array(
1 => '未处理',
2 => '处理中',
3 => '已订购',
4 => '已到货',
5 => '已打包',
6 => '已退单',
7 => '无货',
8 => '暂时缺货',
9 => '无效',
10 => '退换货处理中',
11 => '已退货',
12 => '已入库',
13 => '等待补款',
14 => '已到货',
15 => '超期订单',//入库起6个月
16 => '作废订单',//入库起12个月s
17 => '已放入送货车',
18 => '到货检查中'	,
19 => '到货检查中'		
);

//包裹状态
$package_status_array = array(
1 => '未处理',
2 => '处理中',
3 => '配送中',
4 => '已发货',
5 => '确定收货',
6 => '信息有误',
7 => '已撤销',
8 => '海关退包',
9 => '海关退包',
10 => '无法投递退包',
11 => '等待补款'
);

//转单状态
$b_chg_status_array = array(
1 => '申请中',
2 => '已接收',
3 => '已拒绝',
4 => '已删除'
);

//任务消息状态
$admin_msg_status_array = array(
1 => '未处理',
2 => '处理中',
3 => '已处理'
);

//任消息类别
$admin_msg_type_array = array(
1 => '订单',
2 => '包裹',
9 => '其它'
);

//包裹折扣
$package_serve_deduction_array = array(
0 => '无',
1 => '会员级别',
2 => '积分抵扣'
);

//会员级别
$user_level_array = array(
"普通" => '',
"黄金VIP" => 'hj',
"白金VIP" => 'bj',
"钻石VIP" => 'zs'
);

//
$product_refund_method_array = array(
0 => '无',
1 => '原单',
2 => '收款帐户',
3 => '现金',
4 => '拍卖',
9 => '其他'
);

//财务变更日志
$finace_log_type_array = array(
101 => '后台管理员充值',
102 => 'chinabank充值',
103 => 'paypal充值',
104 => 'motopay充值',
105 => '支付宝充值',	
106 => 'paypay撤销付款',
107 => '线下充值',
108 => '合并折扣帐户到现金帐户',
109 => '微信扫码支付',
110 => '微信JSAPI支付',
201 => '购物车结算',
202 => '商品退单',
203 => '商品数量更改',
204 => '订单补款',
301 => '包裹结算',
309 => '转运包裹结算',
302 => '包裹撤销',
303 => '确认收货，返还折扣',
304 => '确认收货，赠送折扣券',
305 => '确认收货，将节约的运费返还至折扣帐户',
306	=> '代充支付宝',
307 => '返运费差额',
308 => '撤销支付宝代充返款',
401 => '注册成功，赠送积分',
402 => '注册成功，领取折扣券',
403 => '上传头像，赠送积分',
404 => '提交建议，赠送积分',
405 => '通过推广链接注册，赠送礼金',
406 => '推广注册者消费满200，赠送推荐人礼金',
407 => '通过推广链接注册，赠送推荐人礼金',
408 => '积分兑换',
409 => '每日登录送积分',
501 => '退款提现',
502 => '其它扣款(补款)',
503 => '补国内运费',
504 => '补国际运费',
505 => '补商品差额',
506 => '退换货运费(内)',
507 => '撤销提现',
508 => '补国内运费(自助购退货)',
509 => '补国内运费(自助购换货)',
);

$support_type_array = array(
0 => '其他',
2 => '订单',
3 => '包裹',
4 => '建议'
);

$support_status_array = array(
0 => '处理中',
1 => '已处理',
2 => '无效'
);

//补款状态
$supplement_status_array = array(
1 => '等待用户处理',
2 => '已补款',
3 => '已拒绝'
);

//充值状态
$pay_status_array = array(
1 => '处理中',
2 => '已成功',
3 => '已失败'
);

//推广奖历
$spreader_register_consume = array(
1 => '10', //通过推广链接注册送5元
2 => '30' //通过推广链接注册者，消费满一定金额，送推广者25
);

$shipping_way_array = array(
1 => 'EMS',
2 => 'DHL',
3 => 'AIR 2kg以下',
4 => 'AIR 2kg以上',
5 => 'SAL水陆联运',
6 => 'DHL改EMS',
7 => '国内转送',
8 => '快递直送'
);

//自助购商品状态
$selfbuy_status = array(
0=>'等待收货',
1=>'已到货',
2=>'问题商品',
3=>'退货处理中',
4=>'已退货',
5=>'已入库',
6=>'已打包',
7=>'已放入送货车',
8=>'换货处理中',
9=>'退货补运费中',
10=>'换货补运费中',
11=>'无效'
);

//导航定位
$web_navi= array(
	'Index,index'=>'web_home',
	'Service,index'=>'web_daigou',
	'Service,selfbuy'=>'web_selfbuy',
	'Service,trans'=>'web_trans',
	'Service,vip'=>'web_vip',
	'Service,hkexpress'=>'web_trans',
	'Twelve,index'=>'web_twvel',
	'Twelve,item'=>'web_twvel',
	'Groupon,index'=>'web_groupon',
	'Groupon,item'=>'web_groupon',
	'Tao,index'=>'web_tao',
	'Tao,book'=>'web_tao',
	'Tao,item'=>'web_tao',
	'Group,index'=>'web_bbs',
	'Group,article'=>'web_bbs',
	'Review,index'=>'web_review',
	'Mall,index'=>'web_mall',
);
?>