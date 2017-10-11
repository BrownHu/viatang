<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 *
 * 前台公共函数
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

require 'common.php';
//require 'license.php';
//$_defined_domain = require 'license.php';
//----------------------------------------------------------------------------------------
// 获取客户端IP地址
function get_client_ip(){
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	return($ip);
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" )) {
		if ($suffix && strlen ( $str ) > $length)
			return mb_substr ( $str, $start, $length, $charset ) . "...";
		else
			return mb_substr ( $str, $start, $length, $charset );
	} elseif (function_exists ( 'iconv_substr' )) {
		if ($suffix && strlen ( $str ) > $length)
			return iconv_substr ( $str, $start, $length, $charset ) . "...";
		else
			return iconv_substr ( $str, $start, $length, $charset );
	}
	$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all ( $re [$charset], $str, $match );
	$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	if ($suffix)
		return $slice . "…";
	return $slice;
}

function translator($txt){
	if($txt == '') return '';
	if(C('AUTO_TRANTASLATOR_PRODUCT') != 1) return $txt;
	
	$baidu_api = 'http://openapi.baidu.com/public/2.0/bmt/translate?client_id=';
	$_request = $baidu_api .C('BAIDU_TRANSLATOR_KEY')."&from=zh&to=".C('TRANTASLATOR_DIST_LANG')."&q=".urlencode($txt);
	
	$ctx = stream_context_create ( array ('http' => array ('timeout' => 20)) ); 
	$_response = @file_get_contents ( $_request, 0, $ctx );
	$result = json_decode($_response);
	$text = $result->trans_result;
	return $text[0]->dst;
}

//----------------------------------------------------------------------------------------
function toDate($time, $format = 'Y年m月d日 H:i:s') {
	if (empty ( $time )) {
		return '';
	}
	$format = str_replace ( '#', ':', $format );
	return date ( $format, $time );
}

//----------------------------------------------------------------------------------------
function firendlyTime($time) {
	if (empty ( $time )) {
		return '';
	}
	import ( '@.ORG.Date' ); //日期时间操作类目录与1.5不一样
	$date = new Date ( intval ( $time ) );
	return $date->timeDiff ( time (), 2 );
}

//----------------------------------------------------------------------------------------
function getTitleSize($count) {
	return (ceil ( $count / 10 ) + 11) . 'px';
}

//----------------------------------------------------------------------------------------
function rcolor() {
	$rand = rand ( 0, 255 );
	return sprintf ( "%02X", "$rand" );
}

//----------------------------------------------------------------------------------------
function rand_color() {
	return '#' . rcolor () . rcolor () . rcolor ();
}

//----------------------------------------------------------------------------------------
function byte_format($input, $dec = 0) {
	$prefix_arr = array ("B", "K", "M", "G", "T" );
	$value = round ( $input, $dec );
	$i = 0;
	while ( $value > 1024 ) {
		$value /= 1024;
		$i ++;
	}
	return round ( $value, $dec ) . $prefix_arr [$i];
}

//----------------------------------------------------------------------------------------
function getShortTitle($title, $length = 12, $suffix = false) {
	if (empty ( $title )) {
		return '...';
	}
	//  将OUTPUT_CHARSET 改为 DEFAULT_CHARSET
	$title = strtolower ( $title );
	$title = str_replace ( '<span class=h>', '', $title );
	$title = str_replace ( '</span>', '', $title );
	return  msubstr ( $title, 0, $length, C ( 'DEFAULT_CHARSET' ), $suffix );
}

/**
 +----------------------------------------------------------
 * 把返回的数据集转换成Tree
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array ();
	if (is_array ( $list )) {
		// 创建基于主键的数组引用
		$refer = array ();
		foreach ( $list as $key => $data ) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ( $list as $key => $data ) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId) {
				$tree [] = & $list [$key];
			} else {
				if (isset ( $refer [$parentId] )) {
					$parent = & $refer [$parentId];
					$parent [$child] [] = & $list [$key];
				}
			}
		}
	}
	return $tree;
}

/**
 +----------------------------------------------------------
 * 对查询结果集进行排序
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array ( $list )) {
		$refer = $resultSet = array ();
		foreach ( $list as $i => $data )
			$refer [$i] = &$data [$field];
		switch ($sortby) {
			case 'asc' : // 正向排序
				asort ( $refer );
				break;
			case 'desc' : // 逆向排序
				arsort ( $refer );
				break;
			case 'nat' : // 自然排序
				natcasesort ( $refer );
				break;
		}
		foreach ( $refer as $key => $val )
			$resultSet [] = &$list [$key];
		return $resultSet;
	}
	return false;
}

/**
 +----------------------------------------------------------
 * 在数据列表中搜索
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_search($list, $condition) {
	if (is_string ( $condition ))
		parse_str ( $condition, $condition );
		// 返回的结果集合
	$resultSet = array ();
	foreach ( $list as $key => $data ) {
		$find = false;
		foreach ( $condition as $field => $value ) {
			if (isset ( $data [$field] )) {
				if (0 === strpos ( $value, '/' )) {
					$find = preg_match ( $value, $data [$field] );
				} elseif ($data [$field] == $value) {
					$find = true;
				}
			}
		}
		if ($find)
			$resultSet [] = &$list [$key];
	}
	return $resultSet;
}

//-------------------------------------------------------------
// 以下与业务相关函数定义

//----------------------------------------------------------------------------------------
//取得用户头像
function getAvatar($uid) {
	if($uid && is_numeric($uid)){
		$entity = M ( 'User' )->where ( "id=$uid" )->find ();
		return ($entity) ? $entity ['head_img'] : '';
	}
	return '';
}

//----------------------------------------------------------------------------------------
//取得支出类别中文名称
function getSpendType($cid) {
	$result = '';
	global $finace_log_type_array;
	$result = $finace_log_type_array [$cid];
	return $result;
}

//----------------------------------------------------------------------------------------
//取得商品类别中文名称
function getProductType($cid) {
	$result = '其他';
	$entity = M ( 'ProductType' )->where ( "id=$cid" )->find ();
	if ($entity) {
		$result = $entity ['caption'];
	}
	return $result;
}

//----------------------------------------------------------------------------------------
//将用户名称中间用***代替
function getSortUserName($un) {
	$result = '';
	if (strlen ( $un ) > 0) {
		$result = msubstr ( $un, 0, 1, C ( 'DEFAULT_CHARSET' ), false ) . '***' . msubstr ( $un, strlen ( $un ) - 2, 1, C ( 'DEFAULT_CHARSET' ), false );
	}
	return $result;
}

//----------------------------------------------------------------------------------------
//根据网址取得网站中文名称
function getSiteName($url, $type = 0) {
	$result = '';
	if (strlen ( trim ( $url ) ) > 0) {
		$urlArray = parse_url ( $url );
		$host = $urlArray ['host'];
		$domainAry = explode ( '.', $host );
		$count = count ( $domainAry );
		$domain = $domainAry [$count - 2] . '.' . $domainAry [$count - 1];
		if ($type == 0) {
			switch (strtolower ( $domain )) {
				case 'taobao.com' :
					$result = '淘宝网';
					break;
				case 'tmall.com' :
					$result = '天猫';
					break;
				case '360buy.com' :
					$result = '京东';
					break;
				case 'dangdang.com' :
					$result = '当当';
					break;
				case 'm18.com' :
					$result = '麦网';
					break;
				case 'paipai.com' :
					$result = '拍拍';
					break;
				case 'amazon.com' :
					$result = '卓越亚玛逊';
					break;
				case 'eachnet.com' :
					$result = '易趣';
					break;
				case 'vancl.com' :
					$result = '凡客';
					break;
				default :
					$result = '其他';
			}
		} else {
			$result = $domain;
		}
	} else {
		if ($type == 0) {
			$result = '其他';
		} else {
			$result = '未知';
		}
	}
	return $result;
}

//----------------------------------------------------------------------------------------
//取订单运费
function getOrdShipping($oid) {
	if ($oid && ($oid > 0)) {
		$entity = M ( 'Orders' )->where ( "id=$oid" )->find ();
		return ($entity) ? $entity ['shipping_fee'] : 0;
	}
	return 0;
}

//----------------------------------------------------------------------------------------
function getVipRatte($level) {
	if (! empty ( $level )) {
		$entity = M ( 'FinaceConfig' )->where ( "item='vip" . $level . "_rate'" )->find ();
		return ($entity) ? $entity ['value'] : 1;
	}
	return 1;
}

//----------------------------------------------------------------------------------------
//取订单服务费
function getServeFee($oid) {
	if ( $oid && is_numeric($oid)) {
		return M ( 'Product' )->where ( "order_id=$oid" )->sum ( 'service_fee' );
	}
	return 0;
}

//----------------------------------------------------------------------------------------
function sumOrderProductFee($oid) {
	$result = 0;
	if ($oid) {
		$DataList = M ( 'Product' )->where ( "order_id=$oid" )->select ();
		foreach ( $DataList as $item ) {
			$result = $result + $item ['price'] * $item ['amount'];
		}
	}
	return $result;
}

//----------------------------------------------------------------------------------------
function getFashionCategory($id) {
	if ( $id && is_numeric($id)) {
		$entity = M ( 'FashionCategory' )->field ( 'caption' )->where ( "id=$id" )->find ();
		return ($entity) ? $entity ['caption'] : '';
	}
	
	return '';
}

//----------------------------------------------------------------------------------------
//加指定订单商家的补款记录
function loadSupplement($key) {
	return M ( 'SupplementShipping' )->where ( "batid_seller='$key' AND status=0" )->find (); //取一条没操过
}

//----------------------------------------------------------------------------------------
//取得指定订购批次物流
function getBatOrderTrace($tradeno) {
	$result = '';
	if (! empty ( $tradeno )) {
		$entity = M ( 'OrderTrace' )->field ( 'company_name,trace_no' )->where ( "trade_no=$tradeno" )->find ();
		if ($entity && ($entity ['company_name'] != '') && ($entity ['trace_no'] != '')) {
			$result = '快递公司:' . $entity ['company_name'] . ',&nbsp;&nbsp;&nbsp;&nbsp;跟踪单号:<a href="/tools/logistics.shtml?tno=' . $entity ['trace_no'] . '"  style="text-decoration:underline; color:#06F" title="点击跟踪"  target="_blank">' . $entity ['trace_no'] . '</a>';
		}
	}
	return $result;
}

?>