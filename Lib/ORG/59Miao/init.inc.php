<?php
if (! defined ( 'IN_API59MIAO' )) {
	exit ( 'Access Defined' );
}
//设置时区
date_default_timezone_set ( 'Asia/Shanghai' );
define ( 'ROOT_PATH', substr ( dirname ( __FILE__ ), 0, - 7 ) );
require ROOT_PATH . 'library/config.php';
//自动加载类
function __autoload($_className) {
	require ROOT_PATH . 'library/' . $_className . '.class.php';
}
//获取appkey  appsecret 信息
$AppKeySecret = Api59miao_Toos::GetAppkeySecret ();
?>