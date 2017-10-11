<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 * 
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司 
 * @license   	http://www.zline.net.cn/license-agreement.html 
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
load ( '@/functions' );
class TrackAction extends BaseAction {
	
	protected $track_url = 'http://51track.cn/rest?n=';    
	
	public function track(){
		if ($this->user) {
		$track_code = trim($_REQUEST['n']);
	
		if($track_code != ''){
			$this->track_url .= $track_code; 
			$response = crawl($this->track_url);
			if($response != '0'){
				$item = json_decode($response);
				$html = '<table cellspacing="1"><tr><td style="background:#f2f2f2;">&nbsp;&nbsp;时间</td><td style="background:#f2f2f2;">&nbsp;&nbsp;地点</td><td style="background:#f2f2f2;">&nbsp;&nbsp;事件</td></tr>';
				foreach ($item->traces as $a){
					$html .= '<tr><td align="left">&nbsp;&nbsp;'.$a->acceptTime.'</td><td align="left">&nbsp;&nbsp;'.$a->acceptAddress.'</td><td align="left">&nbsp;&nbsp;'.$a->remark.'</td></tr>';
				}
				$html .= '</table>';
				echo $html;
			}else{
				echo L('track_no_info');
			}
		}else{
			echo L('track_no_trace');
		}
	}
	}	
}

?>