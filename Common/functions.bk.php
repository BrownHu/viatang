<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 *
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

//------------------------------------------------------------------------------
//字符串安全过滤
function safe_convert($string) {
	if (is_array ( $string )) {
		foreach ( $string as $key => $val ) {
			$string [$key] = safe_convert ( $val );
		}
	} else {
		$string = preg_replace ( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', str_replace ( array ('&','"','\'','<','>' 
		), array ('&amp;','&quot;','&#039;','&lt;','&gt;' ), $string ) );
	}
	return $string;
}

//------------------------------------------------------------------------------
function stringToHex($s) {
	$r = "";
	$hexes = array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f" );
	for($i = 0; $i < strlen ( $s ); $i ++) {
		$r .= ($hexes [(ord ( $s {$i} ) >> 4)] . $hexes [(ord ( $s {$i} ) & 0xf)]);
	}
	return strtoupper ( $r );
}

//------------------------------------------------------------------------------
function HexToStr($Str) {
	$result = '';
	if (strlen ( trim ( $Str ) ) > 0) {
		$len = intval ( strlen ( $Str ) / 2 );
		for($i = 0; $i < $len; $i ++) {
			$result = $result . chr ( hexdec ( substr ( $Str, $i * 2, 2 ) ) );
		}
	}
	return $result;
}

//------------------------------------------------------------------------------
// 从url中提取主机或域名
function parse_domain($url, $skiphost = true) {
	$result = '';
	if (strlen ( trim ( $url ) ) > 0) {
		$urlArray = parse_url ( $url );
		$host = $urlArray ['host'];
		if ($skiphost) {
			$domain = explode ( '.', $host );
			$count = count ( $domain );
			for($i = 1; $i < count ( $domain ); $i ++) {
				$result = $result . '.' . $domain [$i];
			}
		} else {
			$result = $host;
		}
	}
	return ltrim ( $result, '.' );
}

//------------------------------------------------------------------------------
// 下载指定url文件的内容
function crawl($url) {
	$html = '';
	if (strlen ( $url ) > 0) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt ( $curl, CURLOPT_HEADER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; TheWorld)" );
		$data = curl_exec ( $curl );
		curl_close ( $curl );
		if (! $data) {
			$data = file_get_contents_ex ( $url );
		}
	}
	return trim ( $data );
}

//------------------------------------------------------------------------------
// 创建文件夹(多级目录)
function createFolder($path) {
	if (! file_exists ( $path )) {
		createFolder ( dirname ( $path ) );
		mkdir ( $path, 0777 );
	}
}

/**
 * +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * +----------------------------------------------------------
 *
 * @param string $len
 *        	长度
 * @param string $type
 *        	字串类型
 *        	0 字母 1 数字 其它 混合
 * @param string $addChars
 *        	额外字符
 *        	
 * @return string 
 * +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1 :
			$chars = str_repeat ( '0123456789', 3 );
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default :
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) { // 位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );
	}
	if ($type != 4) {
		$chars = str_shuffle ( $chars );
		$str = substr ( $chars, 0, $len );
	} else {
		// 中文随机字
		for($i = 0; $i < $len; $i ++) {
			$str .= msubstr ( $chars, floor ( mt_rand ( 0, mb_strlen ( $chars, 'utf-8' ) - 1 ) ), 1 );
		}
	}
	return $str;
}

//------------------------------------------------------------------------------
function js_alert($info, $url = '') {
	echo "<script language=\"JavaScript\">alert('$info');</script>";
	if ($url !== '') {
		echo "<meta http-equiv=\"Refresh\" content=\"0;URL={$url}\">";
		exit ();
	}
}

//------------------------------------------------------------------------------
// 文件日志
// 请注意服务器是否开通fopen配置
function write_file_log($file_name, $word) {
	$date = date ( 'Ymd', time () );
	$fp = fopen ( $file_name . "_$date.txt", "a" );
	flock ( $fp, LOCK_EX );
	fwrite ( $fp, "执行日期：" . strftime ( "%Y%m%d%H%M%S", time () ) . " : " . $word . "\n" );
	flock ( $fp, LOCK_UN );
	fclose ( $fp );
}

//------------------------------------------------------------------------------
// 记积分变化日志
function write_point_log($ui, $un, $point, $type, $remark) {
	$data ['user_id'] = $ui;
	$data ['user_name'] = $un;
	$data ['point'] = $point;
	$data ['type'] = $type;
	$data ['remark'] = $remark;
	$data ['create_time'] = time ();
	$DAO = M ( 'PointLog' );
	$DAO->data ( $data )->add ();
}

//------------------------------------------------------------------------------
function safeFilter($str) {
	$result = trim ( strtolower($str) );
	$result = str_ireplace ( "'", '', $result );
	$result = str_ireplace ( 'insert', '', $result );
	$result = str_ireplace ( 'update', '', $result );
	$result = str_ireplace ( 'dropt', '', $result );
	$result = str_ireplace ( 'delete', '', $result );
	
	$result = str_ireplace ( 'select', '', $result );
	$result = str_ireplace ( '<', '', $result );
	$result = str_ireplace ( '>', '', $result );
	$result = str_ireplace ( 'script', '', $result );
	$result = str_ireplace ( 'js', '', $result );
	$result = str_ireplace ( '%', '', $result );
	$result = str_ireplace ( 'php', '', $result );
	$result = str_ireplace ( '$', '', $result );
	$result = str_ireplace ( '$', '', $result );
	$result = preg_replace ( '/\r?\n/', '', $result );
	$text = str_replace ( '"', '', $text );
	$text = str_replace ( '[', '', $text );
	$text = str_replace ( ']', '', $text );
	$text = str_replace ( '|', '', $text );
	return trim ( $result );
}

//------------------------------------------------------------------------------
function get59MiaoConfig() {
	$result = array (
			'appKey' => C ( '59_appKey' ),
			'appSecret' => C ( '59_appSecret' ) 
	);
	return $result;
}

//------------------------------------------------------------------------------
function file_get_contents_ex($url) {
	$ctx = stream_context_create ( array (
			'http' => array (
					'timeout' => 20 
			) 
	) ); // 设置一个超时时间，单位为秒
	$result = @file_get_contents ( $url, 0, $ctx );
	return $result;
}

/**
 * +----------------------------------------------------------
 * 检查字符串是否是UTF8编码
 * +----------------------------------------------------------
 *
 * @param string $string
 *        	字符串
 *        	+----------------------------------------------------------
 * @return Boolean +----------------------------------------------------------
 */
function is_utf8($string) {
	return preg_match ( '%^(?:
		 [\x09\x0A\x0D\x20-\x7E]            # ASCII
	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string );
}

/**
 * 获取 IP 地理位置
 * 淘宝IP接口
 *
 * @return : array
 */
function getCity($ip) {
	$url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
	$ip = json_decode ( file_get_contents_ex ( $url ) );
	if (( string ) $ip->code == '1') {
		return false;
	}
	$data = ( array ) $ip->data;
	return $data;
}

//------------------------------------------------------------------------------
// curl 方式抓取文件内容
function curl_get_contents($url) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url ); // 设置访问的url地址
	                                     // curl_setopt($ch,CURLOPT_HEADER,1);
	                                     // //是否显示头部信息
	curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 ); // 设置超时
	curl_setopt ( $ch, CURLOPT_USERAGENT, _USERAGENT_ ); // 用户访问代理 User-Agent
	curl_setopt ( $ch, CURLOPT_REFERER, _REFERER_ ); // 设置 referer
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 ); // 跟踪301
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 返回结果
	
	$r = curl_exec ( $ch );
	curl_close ( $ch );
	return $r;
}

//------------------------------------------------------------------------------
// 将对象转换为数组
function objectToArray($e) {
	$e = ( array ) $e;
	foreach ( $e as $k => $v ) {
		if (gettype ( $v ) == 'resource')
			return;
		if (gettype ( $v ) == 'object' || gettype ( $v ) == 'array')
			$e [$k] = ( array ) objectToArray ( $v );
	}
	return $e;
}

//------------------------------------------------------------------------------
//以post方式将数据提交到远程
function postData($action, $formvars) {
	try {
		import ( 'ORG.Util.Snoopy' );
		$client = new Snoopy ();
		$client->agent = "(compatible; MSIE 4.01; MSN2.5; AOL 4.0; Windows 98)";
		$client->rawheaders ["Pragma"] = "no-cache";
		
		$client->submit ( $action, $formvars );
		return $client->results;
	} catch ( Exception $e ) {
		return false;
	}
}

//------------------------------------------------------------------------------
//添加到发邮件队列
function addToMailQuen($from, $from_name, $to, $title, $content, $tpl) {
	$result = false;
	//echo 'aaa';
	if (! empty ( $to ) && ! empty ( $title ) && (! empty ( $content ) || ! empty ( $tpl ))) {
		$formvars ['t'] = getServerToken ();
		$formvars ['mails'] = $to;
		$formvars ['content'] = $content;
		$formvars ['tpl'] = $tpl;
		$formvars ['title'] = $title;
		$formvars ['from'] = $from;
		$formvars ['from2'] = $from_name;
		$action = 'http://'.C('MAIL_SERVER').'/project/mailQueue.php';
		
		$result = postData ( $action, $formvars );
	}
	return $result;
}

//------------------------------------------------------------------------------
function getServerToken() {
	return strtolower ( md5 ( real_server_ip () . strtotime ( date ( 'Y-m-d H', time () ) ) ) );
}

//------------------------------------------------------------------------------
function real_server_ip() {
	static $serverip = NULL;
	
	if ($serverip !== NULL) {return $serverip;}
	
	if (isset ( $_SERVER )) {
		if (isset ( $_SERVER ['SERVER_ADDR'] )) {
			$serverip = $_SERVER ['SERVER_ADDR'];
		} else {
			$serverip = '0.0.0.0';
		}
	} else {
		$serverip = getenv ( 'SERVER_ADDR' );
	}
	
	return $serverip;
}

//------------------------------------------------------------------------------
//输出安全的html
function safeHtml($text, $tags = null){
	$text	=	trim($text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//过滤换行符
	$text	=	preg_replace('/\r?\n/','',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
	}
	//允许的HTML标签
	$text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
	$text	=	str_replace('"','&quot;',$text);
	//反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}

//------------------------------------------------------------------------------
function getUrl($url) {
	$ch = curl_init();
	// 设置 url
	curl_setopt($ch, CURLOPT_URL, $url);
	// 设置浏览器的特定header
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)", "Accept-Language: en-us,en;q=0.5"));
	
	// 页面内容我们并不需要
	curl_setopt($ch, CURLOPT_NOBODY, 1);	
	// 只需返回HTTP header
	curl_setopt($ch, CURLOPT_HEADER, 1);
	
	// 返回结果，而不是输出它
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	
	// 有重定向的HTTP头信息吗?
	if (preg_match("!Location: (.*)!", $output, $matches)) {
	         return $matches[1];
	} else {
	         return false;
    }
}

//------------------------------------------------------------------------------
//过滤xss跨站脚本
function remove_xss($val) {
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

		// @ @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		// @ @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	}

	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);

	$found = true; // keep replacing as long as the previous round replaced something
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}

//------------------------------------------------------------------------------
function long2str($v, $w) {
	$len = count ( $v );
	$n = ($len - 1) << 2;
	if ($w) {
		$m = $v [$len - 1];
		if (($m < $n - 3) || ($m > $n))
			return false;
		$n = $m;
	}
	$s = array ();
	for($i = 0; $i < $len; $i ++) {
		$s [$i] = pack ( "V", $v [$i] );
	}
	if ($w) {
		return substr ( join ( '', $s ), 0, $n );
	} else {
		return join ( '', $s );
	}
}

//------------------------------------------------------------------------------
function str2long($s, $w) {
	$v = unpack ( "V*", $s . str_repeat ( "\0", (4 - strlen ( $s ) % 4) & 3 ) );
	$v = array_values ( $v );
	if ($w) {
		$v [count ( $v )] = strlen ( $s );
	}
	return $v;
}

//------------------------------------------------------------------------------
function int32($n) {
	while ( $n >= 2147483648 )
		$n -= 4294967296;
	while ( $n <= - 2147483649 )
		$n += 4294967296;
	return ( int ) $n;
}

//------------------------------------------------------------------------------
function xxtea_encrypt($str, $key) {
	if ($str == "") {
		return "";
	}
	$v = str2long ( $str, true );
	$k = str2long ( $key, false );
	if (count ( $k ) < 4) {
		for($i = count ( $k ); $i < 4; $i ++) {
			$k [$i] = 0;
		}
	}
	$n = count ( $v ) - 1;

	$z = $v [$n];
	$y = $v [0];
	$delta = 0x9E3779B9;
	$q = floor ( 6 + 52 / ($n + 1) );
	$sum = 0;
	while ( 0 < $q -- ) {
		$sum = int32 ( $sum + $delta );
		$e = $sum >> 2 & 3;
		for($p = 0; $p < $n; $p ++) {
			$y = $v [$p + 1];
			$mx = int32 ( (($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4) ) ^ int32 ( ($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z) );
			$z = $v [$p] = int32 ( $v [$p] + $mx );
		}
		$y = $v [0];
		$mx = int32 ( (($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4) ) ^ int32 ( ($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z) );
		$z = $v [$n] = int32 ( $v [$n] + $mx );
	}
	return long2str ( $v, false );
}

//------------------------------------------------------------------------------
function xxtea_decrypt($str, $key) {
	if ($str == "") {
		return "";
	}
	$v = str2long ( $str, false );
	$k = str2long ( $key, false );
	if (count ( $k ) < 4) {
		for($i = count ( $k ); $i < 4; $i ++) {
			$k [$i] = 0;
		}
	}
	$n = count ( $v ) - 1;

	$z = $v [$n];
	$y = $v [0];
	$delta = 0x9E3779B9;
	$q = floor ( 6 + 52 / ($n + 1) );
	$sum = int32 ( $q * $delta );
	while ( $sum != 0 ) {
		$e = $sum >> 2 & 3;
		for($p = $n; $p > 0; $p --) {
			$z = $v [$p - 1];
			$mx = int32 ( (($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4) ) ^ int32 ( ($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z) );
			$y = $v [$p] = int32 ( $v [$p] - $mx );
		}
		$z = $v [$n];
		$mx = int32 ( (($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4) ) ^ int32 ( ($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z) );
		$y = $v [0] = int32 ( $v [0] - $mx );
		$sum = int32 ( $sum - $delta );
	}
	return long2str ( $v, true );
}
?>