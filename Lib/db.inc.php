<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 * 
 * 数据库配置
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

if (!defined('THINK_PATH')) exit();
return  array(
'DB_TYPE'		=>	'mysql',
'DB_HOST'	=>	'localhost',
'DB_NAME'	=>	'daigoucms',
'DB_USER'		=>	'jiemeng_mysql',
'DB_PWD'		=>	'jiemeng_mysql',
'DB_PORT'		=>	'3306',
'DB_PREFIX'  => 	''    // 数据库表前缀
);
?>