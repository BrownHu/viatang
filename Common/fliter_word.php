<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐代购系统(淘宝版)
 *
 * 关键词过滤功能
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

function filter_wordk($string) {
	$keywords= array(
	'<' => 'removed',
	'>' => 'removed',
	'fuck' => 'f**k',
	'Fuck' => 'F**k',
	'FUCK' => 'F**K',
	'cunt' => 'c**t',
	'Cunt' => 'C**t',
	'CUNT' => 'C**t',
	'bitch' => 'b**ch',
	'Bitch' => 'B**ch',
	'shit' => 's**t',
	'Shit' => 'S**t',
	'twat' => 't**t',
	'whore' => 'w***re',
	'他妈的' => 'TMD',
	'狗日的' => '狗X的',
	'操你妈' => '草泥马',
	'肏' => '*',
	'牛逼' => '牛X',
	'装逼' => '装X',
	'中出' => '*',
	'颜射' => '*',
	'萝莉' => '*',
	'人妻' => '*',
	'御姐' => '*',
	'正太' => '*',
	'屁眼' => '菊花',
	'躲猫猫' => '朵猫猫',
	'鸡巴' => 'JB',
	'菊爆' => '菊豹',
	'党' => 'party',
	'黨' => 'party',
	'党员' => '荡猿',
	'警察' => '井叉',
	'三鹿' => '三只鹿',
	'70码' => '欺实马',
	'Yamete' => '雅蔑蝶',
	'fuck you' => '法克鱿',
	'打飞机' => '达菲鸡',
	'前列腺' => '潜烈蟹',
	'Tokyo Hot' => '东京热',
	'松岛枫' => '松岛蜂',
	'武藤兰' => '舞腾狼',
	'春哥纯爷们' => '铁血真汉子',
	'曾哥纯爷们' => '铁血史泰龙',
	'菊花残' => '菊花蚕',
	'鸡巴毛' => '吉跋猫',
	'郭敬明' => 'GJM',
	'谢亚龙' => '獬犽龙',
	'叉腰肌' => '猹妖鸡',
	'达赖喇嘛' => '鞑癞瘌马',
	'90后' => '九岭猴',
	'傻逼' => '傻X',
	'高丽棒' => '稿栗蚌',
	'央视' => '央虱',
	'CCTV' => 'CCAV',
	'高也' => '心神不宁',
	'和谐社会' => '河蟹社会',
	'政协委员' => '症懈萎猿',
	'党和国家领导人' => '擎天柱',
	'社会主义' => '初级阶段',
	'胡锦涛' => 'JT Hu',
	'温家宝' => 'JB Wen',
	'习近平' => 'JP Xi',
	'赵紫阳' => 'ZY Zhao',
	'共产党' => '供铲挡',
	'中共' => 'CCP',
	'中国共产党' => 'CCP',
	'独裁' => '毒豺',
	'共匪' => '共X',
	'六四事件' => '1989',
	'政府' => 'Gov',
	'城管' => '城市管理者',
	'公安' => '公共安全专家',
	'马英九' => 'YJ Ma',
	'陈水扁' => 'SB Chen',
	'法轮功' => '邪教',
	'热比娅' => '恐怖分子',
	'维吾尔' => '少数民族'
	);
	return strtr($string, $keywords);
}

//实时和谐检测
function contain_badkey($to_chek_str){
	$badkey = "86daigou|86代购|86dg|panli|番丽|番丽代购|panli代购";
	
	if(preg_match("/$badkey/i",$to_chek_str)){
		return false;
	}else{
		return true;
	}
}

?>