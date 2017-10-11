<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 会员中心
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

import ( 'ORG.Util.Page' );
load ( '@/functions' );
class MyAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		    $this->assign ('title','我的唯唐-全球最专业海外华人代购,留学生代购中国商品网站-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			// 取帐户余额 ＝现金账户＋折扣账户
			$finance = D ( 'Finance' )->finace ( $this->user ['id'] );
			if ($finance) {
				$this->assign ( 'balance', $finance ['money'] ); // 余额
				$this->assign ( 'rebate', $finance ['rebate'] ); // 折扣
				$this->assign ( 'point', $finance ['point'] ); // 积分
				$this->assign ( 'consumption_total', $finance ['consumption_total'] );
			}
			if (strlen ( $this->user ['head_img'] ) == 0) {
				$this->assign ( 'upload_avatar', 1 );
			}
			
			// 统计商品和包裹
			$ProductDAO = M ( 'Product' );
			$wcl_ord_count = $ProductDAO->where ( "status=1 AND user_id=" . $this->user ['id'] )->count (); // 未处理
			$clz_ord_count = $ProductDAO->where ( "status=2 AND user_id=" . $this->user ['id'] )->count (); // 处理中
			$arrive_count = $ProductDAO->where ( "status=4 AND user_id=" . $this->user ['id'] )->count (); // 已到货
			$order_count = $ProductDAO->where ( "status=3 AND user_id=" . $this->user ['id'] )->count (); // 已订购
			$yrk_ord_count = $ProductDAO->where ( "status=12 AND user_id=" . $this->user ['id'] )->count (); // 已入库
			$zsqh_ord_count = $ProductDAO->where ( "status=8 AND user_id=" . $this->user ['id'] )->count (); // 暂时缺货
			$wh_ord_count = $ProductDAO->where ( "status=7 AND user_id=" . $this->user ['id'] )->count (); // 无货
			$wx_ord_count = $ProductDAO->where ( "status=9 AND user_id=" . $this->user ['id'] )->count (); // 无效
			$thhclz_ord_count = $ProductDAO->where ( "status=10 AND user_id=" . $this->user ['id'] )->count (); // 退换货处理中
			
			$PackageDAO = M ( 'Package' );
			$sent_count = $PackageDAO->where ( 'status=4 AND  user_id=' . $this->user ['id'] )->count ();
			$unprocess_count = $PackageDAO->where ( 'status=1 AND  user_id=' . $this->user ['id'] )->count (); // 未处理
			$clz_pg_count = $PackageDAO->where ( 'status=2 AND  user_id=' . $this->user ['id'] )->count (); // 处理中
			$yqr_pg_count = $PackageDAO->where ( 'status=5 AND  user_id=' . $this->user ['id'] )->count (); // 已确认
			$wx_pg_count = $PackageDAO->where ( 'status=6 AND  user_id=' . $this->user ['id'] )->count (); // 信息有误
			
			$unrd_count = M ( 'Notice' )->where ( 'user_id=' . $this->user ['id'] . ' AND tag=0' )->count (); // 未读短信		
			$_SESSION['my_unread_notice'] = $unrd_count;

			$unrd_ccount = M('Consultation')->where( 'user_id=' . $this->user ['id'] . " AND  admin_reply_tag=1")->count();
			$_SESSION['my_unread_consulation'] = $unrd_ccount;
			
			// 订单
			$this->assign ( 'wcl_count', $wcl_ord_count );
			$this->assign ( 'clz_count', $clz_ord_count );
			$this->assign ( 'arrive_count', $arrive_count );
			$this->assign ( 'order_count', $order_count );
			$this->assign ( 'yrk_count', $yrk_ord_count );
			$this->assign ( 'zsqh_count', $zsqh_ord_count );
			$this->assign ( 'wh_count', $wh_ord_count );
			$this->assign ( 'wx_count', $wx_ord_count );
			$this->assign ( 'thhclz_count', $thhclz_ord_count );
			
			// 包裹
			$this->assign ( 'clz_pg_count', $clz_pg_count );
			$this->assign ( 'yqr_pg_count', $yqr_pg_count );
			$this->assign ( 'wx_pg_count', $wx_pg_count );
			$this->assign ( 'sent_count', $sent_count );
			$this->assign ( 'unprocess_count', $unprocess_count );
			$this->assign ( 'unrd_count', $unrd_count ); // 短消息
			                                             
			// 公告
			$AnnounceList =  M ( 'Announce' )->order ( 'last_update desc' )->limit ( '5' )->select ();
			$this->assign ( 'AnnounceList', $AnnounceList );
			
			// 常见问题
			if (S ( 'MyHelpList' )) {
				$HelpList = S ( 'MyHelpList' );
			} else {
				$HelpList = M ( 'Help' )->where ( 'category_id=11' )->limit ( '5' )->order ( 'sort' )->select ();
				S ( 'MyHelpList', $HelpList );
			}
			$this->assign ( 'HelpList', $HelpList );
			
			// 统计冻结帐户金额
			// $PayTraceDAO = M('');
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 我要代购
	public function buy() {
		    $this->assign ('title','我要代购-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,我要代购,中国代购,代购中国商品,淘宝代购,亚马逊代购,京东代购,当当代购,天猫代购,海外华人代购,美国华人代购,美国代购,新加坡代购,马来西亚代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 我的订单
	public function order() {
		        $this->assign ('title','我的订单-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$to_packe_list = Session::get ( C ( 'ADD_TO_PACKAGE' ) );
			if ($to_packe_list) {
				$this->assign ( 'ToPackageList', $to_packe_list );
			}
			
			$DAO = M ( 'Product' );
			$condition = '(user_id=' . $this->user ['id'] . ')  AND (status != 6)  AND (status != 5)  AND (status != 0) AND (status != 17)  AND (status != -1)';
			$count = $DAO->where ( $condition )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( $condition )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'order_id desc,supplement_id asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				global $product_status_array_user;
				$this->assign ( 'productStaAry', $product_status_array_user );
			}
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 我的包裹
	public function parcel() {
		        $this->assign ('title','我的运单-一站式代购淘宝平台-viatang.com');
	            $this->assign ('keywords','代购,唯唐代购,代邮,代寄,国际转运,集中采购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
                $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'Package' );
			$count = $DAO->where ( "status<>7 AND user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( 'status<>7 AND user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				global $package_status_array;
				$this->assign ( 'PackageStatus', $package_status_array );
			}
			$this->display ();
		} 
	}

	//------------------------------------------------------------------------------------------------
	// 交易记录,默认加载订单记录
	public function log() {
		if ($this->user) {
			$DAO = M ( 'Product' );
			$count = $DAO->where ( "status<>0 AND user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				
				$DataList = $DAO->where ( 'status<>0 AND user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				global $product_status_array_user;
				$this->assign ( 'productStaAry', $product_status_array_user );
			}
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 充值
	public function pay() {
		if ($this->user) {
			$this->redirect ( 'Pay/paypal' ); // 直接跳转到paypal
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 退款
	public function refund() {
		    $this->assign ('title','申请提现-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$entity =  D ( 'Finance' )->finace ( $this->user ['id'] );
			if ($entity) {
				$this->assign ( 'can_refund', $entity ['money'] );
				$this->assign ( 'rebate', $entity ['rebate'] );
			}
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 评论
	public function review() {
		    $this->assign ('title','收货评论-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,收货评论,代购评价,代邮代寄评价,集中转运,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'Comment' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$sum = $DAO->where ( 'user_id=' . $this->user ['id'] )->sum ( 'point' );
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				
				$DataList = $DAO->where ( "user_id=" . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				$this->assign ( 'sum_point', $sum );
			}
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 积分
	public function point() {
		if ($this->user) {
			$DAO = M ( 'PointLog' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$sum = $DAO->where ( 'user_id=' . $this->user ['id'] )->sum ( 'point' );
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( "user_id=" . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
				
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
				$this->assign ( 'sum_point', $sum );
			}
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 咨询
	public function consultation() {
		    $this->assign ('title','我的咨询-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购咨询,代购介绍,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'Consultation' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'first', '1' );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				$DataList = $DAO->where ( "user_id=" . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_at desc' )->select ();
				
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$unrd_ccount = $DAO->where( "user_id=" . $this->user ['id'] . " AND admin_reply_tag=1")->count();
			$_SESSION['my_unread_consulation'] = $unrd_ccount;
			
			$DAO = M ( 'Notice' );
			$notice_count = $DAO->where ( 'user_id=' . $this->user ['id'] . ' AND tag=0'  )->count ();
			$_SESSION['my_unread_notice'] = $notice_count;
			
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 我要投诉
	public function complaint() {
		    $this->assign ('title','我要投诉-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,投诉管理,投诉,代购投诉,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 修改资料
	public function profile() {
		    $this->assign ('title','基本资料-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购用户资料,会员信息,会员资料,代购用户注册,免费注册,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'UserInfo' );
			$entity = $DAO->where ( 'user_id=' . $this->user ['id'] )->find ();
			if ($entity) {
				$this->assign ( 'profile', $entity );
			}
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 修改密码
	public function password() {
		    $this->assign ('title','用户密码修改-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购资料修改,登录密码修改,用户资料修改,免费会员注册,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 地址
	public function address() {
		    $this->assign ('title','收货地址管理-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购地址管理,收货地址,唯唐收货地址,免费会员注册,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','唯唐代购-全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'Address' );
			$count = $DAO->where ( "user_id=" . $this->user ['id'] )->count ();
			if ($count > 0) {
				$DataList = $DAO->where ( "user_id=" . $this->user ['id'] )->order ( 'id asc' )->select ();
				$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
				$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
				$page = $p->show ();
				
				$this->assign ( 'DataList', $DataList );
				$this->assign ( 'page', trim($page) );
			}
			$this->display ();
		} 
	}
	
	//订单提醒------------------------------------------------------------------------------------------------
	public function ordertip() {
		    $this->assign ('title','订单提醒-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购订单,淘宝订单,代购商品,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$email = ulowi_decode(base64_decode($this->user ['email2']));
			$email = substr ( $email, 0, 2 ) . '***' . substr ( $email, strpos ( $email, '@' ), strlen ( $email ) );
			$this->assign ( 'email', $email );
			
			$DAO = M ( 'OrderTip' );
			$u_id = $this->user ['id'];
			$entity = $DAO->where ( 'user_id=' . $u_id )->find ();
			$this->assign ( 'ord_tip', $entity );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 推广
	public function spread() {
		if ($this->user) {
			$this->assign ( 'spread_url', C ( 'SITE_URL' ) . '/?spreader=' . $this->user ['id'] );
			$DAO = M ( 'User' );
			$count = $DAO->where ( 'spreader_id=' . $this->user ['d'] )->count ();
			$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
			$p->setConfig ( 'first', '1' );
			$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
			$page = $p->show ();
			
			$DataList = $DAO->field ( 'login_name,create_time' )->where ( "spreader_id=" . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time asc' )->select ();
			$this->assign ( 'DataList', $DataList );
			$this->assign ( 'page', trim($page) );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 系统通知
	public function notice() {
		    $this->assign ('title','系统通知-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,代购消息,代购通知,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DAO = M ( 'Notice' );
			$count = $DAO->where ( 'user_id=' . $this->user ['id'] )->count ();
			$p = new Page ( $count, C ( 'NUM_PER_PAGE' ) );
			$p->setConfig ( 'first', '1' );
			$p->setConfig ( 'theme', '%upPage% %first%  %linkPage%  %downPage%' );
			$page = $p->show ();
			
			$DataList = $DAO->where ( 'user_id=' . $this->user ['id'] )->limit ( $p->firstRow . ',' . $p->listRows )->order ( 'create_time desc' )->select ();
			$unrd_ccount = M('Consultation')->where( 'user_id=' . $this->user ['id'] . " AND  admin_reply_tag=1")->count();
			$_SESSION['my_unread_consulation'] = $unrd_ccount;
			
			$this->assign ( 'DataList', $DataList );
			$this->assign ( 'page', trim($page) );
			$this->display ();
		} 
	}
	
	//收藏的商品------------------------------------------------------------------------------------------------
	public function favorite() {
		    $this->assign ('title','收藏的商品-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,淘宝商品收藏,代购商品收藏,喜欢的宝贝,加入购物车,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DataList = M ( 'FavoriteProduct' )->where ( 'user_id=' . $this->user ['id'] )->select ();
			if ($DataList) {
				$this->assign ( 'DataList', $DataList );
			}
			$this->display ();
		} 
	}
	
	//收藏的店铺------------------------------------------------------------------------------------------------
	public function shop() {
		    $this->assign ('title','收藏的店铺-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,淘宝店铺收藏,热卖店铺,收藏店铺,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$DataList = M ( 'FavoriteSite' )->where ( 'user_id=' . $this->user ['id'] )->select ();
			if ($DataList) {
				$this->assign ( 'DataList', $DataList );
			}
			$this->display ();
		} 
	}
	
	//邮箱修改------------------------------------------------------------------------------------------------
	public function email() {
		    $this->assign ('title','用户邮箱修改-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,注册会员,免费注册会员,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$email = ulowi_decode(base64_decode($this->user ['email2']));
			$email = substr ( $email, 0, 2 ) . '***' . substr ( $email, strpos ( $email, '@' ), strlen ( $email ) );
			$this->assign ( 'email', $email );
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 合并财务记录
	public function hbcw(){
		$user = Session::get ( C ( 'MEMBER_INFO' ) );
		if($user){
			$this->error('请联系客服为您处理');
			/* $DAO = D( 'Finance' );
			$finance = $DAO->finace ( $user['id']);
			if ($finance) {
				$money_befor  = $finance ['money'];
				$rebate_befor   = $finance ['rebate'];
				$point_befor     = $finance ['point'];
				$rebate 			=  0-$rebate_befor;
				$finance ['money'] = $money_befor + $rebate_befor;
				$finance ['rebate']  = 0;
				$DAO->updateInfo ( $finance ); 	//更新财务记录
				$this->writeFinaceLog($user['id'],$user ['login_name'],0,0,$rebate_befor,$money_befor,$rebate_befor,$point_befor,'转移折扣帐户金额到现金帐户',$rebate,108);
			}
			$this->success('合并成功'); */
		}
	}
	
	//会员头像------------------------------------------------------------------------------------------------
	public function avatar() {
		    $this->assign ('title','会员头像-一站式代购淘宝平台-viatang.com');
	        $this->assign ('keywords','代购,会员头像,会员资料,唯唐代购,中国代购,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
            $this->assign ('description','全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费3折.');
		if ($this->user) {
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	public function upavatar() {
		if ($this->user) {
			$this->display ();
		} 
	}
	
	//------------------------------------------------------------------------------------------------
	// 上传头像
	public function upload() {
		if ($this->user && ! empty ( $_FILES )) {
			$this->_upload ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 文件上传
	protected function _upload() {
		$year = date ( "Y" );
		$month = date ( "m" );
		$day = date ( "d" );
		$save_path = ULOWI_UPLOADS_PATH . "/pic/avatar/$year/$month/$day";
		
		import ( "ORG.Net.UploadFile" );
		$upload = new UploadFile ();
		$upload->maxSize = 3292200; // 设置上传文件大小
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' ); // 设置上传文件类型
		$upload->savePath = $save_path . '/'; // 设置附件上传目录
		$upload->thumb = true; // 设置需要生成缩略图，仅对图像文件有效
		$upload->imageClassPath = 'ORG.Util.Image'; // 设置引用图片类库包路径
		$upload->thumbPrefix = ',';
		$upload->thumbSuffix = '_m,_s'; // 生产2张缩略图,//设置需要生成缩略图的文件后缀
		$upload->thumbMaxWidth = '400,120'; // 设置缩略图最大宽度
		$upload->thumbMaxHeight = '400,120'; // 设置缩略图最大高度
		$upload->saveRule = uniqid; // 设置上传文件规则
		$upload->thumbRemoveOrigin = true; // 删除原图
		
		if (! $upload->upload ()) {
			$this->assign ( 'waitSecond', 5 );
			$this->assign ( 'message', $upload->getErrorMsg () ); // 捕获上传异常
			$this->display ( 'Public:result' );
		} else { // 取得成功上传的文件信息
			$uploadList = $upload->getUploadFileInfo ();
			$file_name = $uploadList [0] ['savename'];
		}
		
		$fileName = explode ( '.', $file_name );
		if (strlen ( $this->user ['head_img'] ) == 0) {
			$this->updatePoint ( $this->user ['id'] );
		}
		$this->user ['head_img'] = "$year/$month/$day/" . $fileName [0];
		M ( 'User' )->where ( 'id=' . $this->user ['id'] )->save ( $this->user );
		Session::set ( c ( 'MEMBER_INFO' ), $this->user );
		$this->display ( 'Public:result' );
	}
	
	//------------------------------------------------------------------------------------------------
	// 更新积分
	private function updatePoint($uid) {
		$DAO = D ( 'Finance' );
		$finance = $DAO->where ( "user_id=$uid" )->find ();
		if ($finance) {
			$finance ['point'] = $finance ['point'] + 50;
			$DAO->updateInfo ( $finance );
		} else { // 不存在财务信息，则创建
			$data ['user_id'] = $uid;
			$data ['money'] = 0;
			$data ['rebate'] = 0;
			$data ['point'] = 50;
			$data ['consumption_total'] = 0;
			$data ['consumption_point'] = 0;
			$data ['status'] = 1;
			$data ['last_update'] = time ();
			$DAO->data ( $data )->add ();
		}
	}
	
	
	//----------------------------------------------------------------------------------------
	//记财务变更记录
	private function writeFinaceLog($uid, $unam, $oid, $pid, $money, $mnybfr, $rbtbfr, $pntbfr, $remark, $reb, $typ) {
		$data ['user_id'] 		= $uid;
		$data ['user_name'] 	= $unam;
		$data ['type_id'] 		= $typ; //包裹结算，见business.inc.php定义
		$data ['pay_id'] 			= 0;
		$data ['order_id'] 		= $oid; //这里记 订单号：商品号
		$data ['package_id'] 	= $pid; //对应的包裹编号
		$data ['product_id'] 	= $pid;
		$data ['pointlog_id'] 	= 0;
	
		$data ['chagne_total'] 	= $money;
		$data ['money'] 			= $money;
		$data ['money_before'] = $mnybfr;
		$data ['money_after'] 	= $mnybfr + $money; 
		$data ['rebate'] 			= $reb;
		$data ['rebate_before'] 	= $rbtbfr;
		$data ['rebate_after'] 	= $rbtbfr + $reb;
		$data ['point'] 				= 0;
		$data ['point_before'] 	= $pntbfr;
		$data ['point_after'] 		= $pntbfr;
	
		$data ['remark'] 			= $remark;
		$data ['create_time'] 		= time ();
	
		M ( 'FinanceLog' )->data ( $data )->add ();
	}
	
}
?>