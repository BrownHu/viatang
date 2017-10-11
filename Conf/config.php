<?php
if (! defined ( 'THINK_PATH' )) exit ();
	
//导入其它配置
$alipay_config 	= require 'Conf/alipay.inc.php';
$db_config 					= require 'Conf/db.inc.php';
$email_config 		= require 'Conf/email.inc.php';
$home_config 			= require 'Conf/index.inc.php';
$miao59_config 	= require 'Conf/miao59.inc.php';
$misc_config 			= require 'Conf/misc.inc.php';
$paypal_config 	= require 'Conf/paypal.inc.php';
$qq_config 					= require 'Conf/qq.inc.php';
$taobao_config  = require 'Conf/taobao.inc.php';
$translator_config  = require 'Conf/translator.inc.php';
$license_domain_config  = require 'Conf/license.inc.php';


//项目配置
$config = array (
'APP_DEBUG' 				=> true, 
'RETURN_URL'				=> 'return_url', 
'URL_CASE_INSENSITIVE' 		=> true, //url不区分大小写
'URL_HTML_SUFFIX' 			=> '.html|.shtml', 
'URL_MODEL' 				=> 2,
'URL_ROUTER_ON'				=> true,

'ERROR_PAGE' 				=> '/Index/index.html', // 错误定向页面
'DEFAULT_THEME' 			=> 'default', // 默认模板主题名称

'LANG_AUTO_DETECT'			=> true,
'LANG_SWITCH_ON' 			=> true,
'DEFAULT_LANG'   			=> 'zh-cn',
'LANG_LIST'					=> 'en-us,zh-cn',
		
		
//缓存
'DATA_CACHE_TYPE' 			=> 'Memcache', //数据缓存方式 文件, file ,Eaccelerator, Memcache
'DATA_CACHE_TIME' 			=> 86400,   //数据缓存有效期 单位: 秒,1小时
'DATA_CACHE_SUBDIR'			=> true,   //启用缓存子目录
'DATA_PATH_LEVEL'			=> 2,	   //缓存目录级别
'HTML_CACHE_ON' 			=> true, 
'HTML_CACHE_TIME'       	=> - 1,    //时间单位是秒,30天
'HTML_READ_TYPE' 			=> 0,

/* 日志设置 */
'LOG_EXCEPTION_RECORD' 	=> false, // 是否记录异常信息日志(默认为开启状态)

//安全相关配置
'DES_KEY' 					=> 'QAZ122#@kl08', 
'MEMBER_INFO' 				=> 'm_Info', 
'MEMBER_AUTH_KEY' 			=> 'm_id', 
'MEMBER_NAME' 					=> 'm_name', 
'TOKEN_ON' 							=> false, 

'SESSION_AUTO_START'    	=> true,    				// 是否自动开启Session
'SESSION_TYPE'						=> 'FILE',					// FILE,Database

//登录相关
'COOKIE_EXPIRE' 					=> 1209600, 				// Cookie有效期,两周
'COOKIE_DOMAIN' 				=> $_SERVER ['HTTP_HOST'], 	// Cookie有效域名
'COOKIE_PATH' 					=> '/', 					// Cookie路径
'COOKIE_PREFIX' 					=> 'ULOWI_', 				// Cookie前缀 避免冲突
'COOKIE_USER_INFO' 			=> 'ulowi_auto_user', 
'COOKIE_REM_NAME' 			=> 'ulowi_rem_name', 

'CART_COUNT' 						=> 'ulowi_shoping_cart_count', 
'NUM_PER_PAGE' 					=> 30, //列表每页显示的记录数
'ADD_TO_PACKAGE'				=> 'ulowi_add_to_package',	

//性能相关配置
'TMPL_STRIP_SPACE'				=>	false,

//财务相关配置
'CUSTOM' 											=> 'custom', 				//报关费
'EXCHANGE_RATE' 				=> 'exchange_rate', 		//汇率
'SERVE_RATE' 							=> 'serve_rate', 			//服务费比例
'INSURE_RATE'	 					=> 'insure_rate', 			//保险费比例
'POINT_EXCHANGE_RATE' 		=> 'point_exchange_rate', 	//积分兑换比例
'POINT_EXCHANGE_LIMIT' 	=> 'point_exchange_limit', 	//积分兑换限额
'VIP1_SPEND' 						=> 'vip1_spend', 
'VIP1_RATE' 							=> 'vip1_rate', 
'VIP2_SPEND' 						=> 'vip2_spend', 
'VIP2_RATE' 							=> 'vip2_rate', 
'VIP3_SPEND' 						=> 'vip3_spend', 
'VIP3_RATE' 							=> 'vip3_rate' );

//合并输出配置
return array_merge ( $db_config, $config, $alipay_config,$email_config,$home_config, $misc_config,$paypal_config, $miao59_config,$qq_config,$taobao_config,$translator_config,$license_domain_config );
?>