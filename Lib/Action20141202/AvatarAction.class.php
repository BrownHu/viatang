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

load ( '@/functions' );
class AvatarAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function upload() {
		if ($this->user && (! empty ( $_FILES ))) {
			$this->_upload ();
		} else {
			$this->ajaxReturn ( '', L('avatar_no_input_file'), 0 );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	// 文件上传
	protected function _upload() {
		$save_path = ULOWI_UPLOADS_PATH . "/pic/avatar/tmp/";
		createFolder ( $save_path );
		import ( "ORG.Net.UploadFile" );
		
		$upload = new UploadFile ();
		$upload->maxSize = 3292200; //设置上传文件大小
		$upload->allowExts = explode ( ',', 'jpg,gif,png,jpeg' ); //设置上传文件类型
		$upload->savePath = $save_path . '/'; //设置附件上传目录
		$upload->thumb = false; //设置需要生成缩略图，仅对图像文件有效
		$upload->imageClassPath = 'ORG.Util.Image'; // 设置引用图片类库包路径
		$upload->saveRule = uniqid; //设置上传文件规则
		

		if (! $upload->upload ()) {
			$ajaxMsg = $upload->getErrorMsg (); //捕获上传异常
			$uploadSuccess = false;
		} else { //取得成功上传的文件信息
			$uploadSuccess = true;
			$uploadList = $upload->getUploadFileInfo ();
			$file_name = $uploadList [0] ['savename'];
			$ajaxMsg = L('avatar_upload_succ');
		}
		
		$fileName = explode ( '.', $file_name );
		$result ['id'] = $fileName [0] . '.' . $fileName [1];
		$result ['file'] = "/Uploads/pic/avatar/tmp/" . $fileName [0] . '.' . $fileName [1];
		if ($uploadSuccess) {
			$this->ajaxReturn ( $result, $ajaxMsg, 1 );
		} else {
			$this->ajaxReturn ( false, $ajaxMsg, 0 );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//保存已上传的头像
	public function saveAvatar() {
		if ($this->user && isset ( $_GET )) {
			$type = isset ( $_GET ['type'] ) ? trim ( $_GET ['type'] ) : 'small';
			$pic_id = trim ( $_GET ['photoId'] );
			$today = date ( "Y/m/d" );
			$avatar_path = ULOWI_UPLOADS_PATH . "/pic/avatar/" . $today;
			createFolder ( $avatar_path );
			$img_src = ULOWI_UPLOADS_PATH . "/pic/avatar/tmp/$pic_id";
			
			$filename = explode ( '.', $pic_id );
			$image_name = $filename [0] . '_' . $this->user ['id'];
			
			//生成图片存放路径
			$new_avatar_path = ULOWI_UPLOADS_PATH . "/pic/avatar/tmp/" . $filename [0] . '_' . $type . '.jpg';
			
			try {
				$len = file_put_contents ( $new_avatar_path, file_get_contents ( "php://input" ) );
				$avtar_img = imagecreatefromjpeg ( $new_avatar_path );
				if (strtolower ( $type ) == 'big') {
					@imagejpeg ( $avtar_img, $avatar_path . '/' . $image_name . '_m.jpg', 80 );
				} else {
					@imagejpeg ( $avtar_img, $avatar_path . '/' . $image_name . '_s.jpg', 80 );
				}
				@unlink ( $img_src );
				@unlink ( $new_avatar_path );
			} catch ( Exception $e ) {
				Log::write ( L('avatar_upload_fail'), Log::ERR );
			}
			
			if ($this->user ['head_img'] == '') {
				$this->updatePoint ( $this->user ['id'] );
			}
			
			//记录大图的文件名，实际上大，小图文件名是一样，只是后缀不一样
			if (strtolower ( $type ) == 'big') {
				$this->user ['head_img'] = $today . '/' . $image_name;
				 M ( 'User' )->where ( 'id=' . $this->user ['id'] )->save ( $this->user );
				Session::set ( C ( 'MEMBER_INFO' ), $this->user );
			}
			
			$d = new pic_data ();
			$d->data->urls [0] = '/avatar_test/' . $new_avatar_path;
			$d->status = 1;
			$d->statusText = L('avatar_upload_succ');
			$msg = json_encode ( $d );
			echo $msg;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	public function camera() {
		if ($this->user && isset ( $_GET )) {
			$pic_id = trim ( $_GET ['photoId'] );
			$today = date ( "Y/m/d" );
			$avatar_path = ULOWI_UPLOADS_PATH . "/pic/avatar/$today";
			createFolder ( $avatar_path );
			$img_src = ULOWI_UPLOADS_PATH . "/pic/avatar/tmp/$pic_id";
			
			$filename = explode ( '.', $pic_id );
			$image_name = $filename [0] . '_' . $this->user ['id'];
			
			//生成图片存放路径
			$new_avatar_path = ULOWI_UPLOADS_PATH . "/pic/avatar/tmp/" . $filename [0] . '.jpg';
			
			try {
				file_put_contents ( $new_avatar_path, file_get_contents ( "php://input" ) );
				$avtar_img = imagecreatefromjpeg ( $new_avatar_path );
				@imagejpeg ( $avtar_img, $avatar_path . '/' . $image_name . '_m.jpg', 80 );
				@unlink ( $new_avatar_path );
				@unlink ( $img_src );
			} catch ( Exception $e ) {
				Log::write ( L('avatar_upload_fail'), Log::ERR );
			}
			
			if ($this->user ['head_img'] == '') {
				$this->updatePoint ( $this->user ['id'] );
			}else{
				@unlink ( ULOWI_UPLOADS_PATH . "/pic/avatar/".$this->user['head_img'].'_m.jpg' );
				@unlink ( ULOWI_UPLOADS_PATH . "/pic/avatar/".$this->user['head_img'].'_s.jpg' );
			}
			
			//记录大图的文件名，实际上大，小图文件名是一样，只是后缀不一样
			$this->user ['head_img'] = $today . '/' . $image_name;
			M ( 'User' )->where ( 'id=' . $this->user ['id'] )->save ( $this->user );
			Session::set ( C ( 'MEMBER_INFO' ), $this->user );
			
			$d = new pic_data ();
			$d->data->urls [0] = '/avatar_test/' . $new_avatar_path;
			$d->status = 1;
			$d->statusText = L('avatar_upload_succ');
			$msg = json_encode ( $d );
			echo $msg;
		}
	}
	
	//------------------------------------------------------------------------------------------------
	//更新积分
	private function updatePoint($uid) {
		$DAO = D ( 'Finance' );
		$finance = $DAO->where ( "user_id=$uid" )->find ();
		if ($finance) {
			$finance ['point'] = $finance ['point'] + 50;
			$DAO->updateInfo ( $finance );
			write_point_log ( $uid, $uid, 50, 403, '上传头像，赠送积分' );
		} else { //不存在财务信息，则创建
			$data ['user_id'] = $uid;
			$data ['money'] = 0;
			$data ['rebate'] = 0;
			$data ['point'] = 50;
			$data ['consumption_total'] = 0;
			$data ['consumption_point'] = 0;
			$data ['status'] = 1;
			$data ['last_update'] = time ();
			$DAO->data ( $data )->add ();
			write_point_log ( $uid, $uid, 50, 403, '上传头像，赠送积分' );
		}
	}
}

class pic_data {
	public $data;
	public $status;
	public $statusText;
	public function __construct() {
		$this->data->urls = array ();
	}
}
?>