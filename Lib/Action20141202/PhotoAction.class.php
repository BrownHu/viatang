<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 头像
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */
class PhotoAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function showPhoto() {
		if ($this->user) {
			$pid = trim ( $_GET ['pid'] );
			$type = trim ( $_GET ['type'] );
			
			$DataList = M ( 'Photo' )->field ( 'img' )->where ( "pid = $pid AND type=$type" )->select ();
			$result = '';
			foreach ( $DataList as $item ) {
				$pic = '/Uploads/pic/photo/' . $item ['img'] . '.jpg';
				$result = $result . '&nbsp;<a  href="' . $pic . '" class="taopic" pic="/Uploads/pic/photo/' . $item ['img'] . '.jpg" target="_blank">' . '<img src="/Admin/Tpl/default/Public/Images/pictureedit.gif" width="16" height="16"  border="0"/></a>';
			}
			echo $result;
		}
	}
}
?>