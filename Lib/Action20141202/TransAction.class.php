<?php



// import ( "ORG.Util.HashMap" );
class TransAction extends Action {
	protected $user;
	public function index() {
		$this->user = M('User');
		$dao = M('ShopUsers');
		$finace = D('Finance');
		$_list = $dao->field('email,user_name,password,ec_salt,user_money')->select();	
		//echo $_list;exit;
		foreach ($_list as $_r){
			/*$item = array(
					'login_name'=> $_r['user_name'],
					'password'=> $_r['password'],
					'salt'=> $_r['ec_salt'],
					'salt'=> $_r['ec_salt'],
					'nick'=> $_r['user_name'],
					'email'=> $_r['email'],
					'email2'=> base64_encode(ulowi_encode($_r['email'])),
					);*/
			$_email2 = base64_encode(ulowi_encode($_r['email']));
			echo $_sql = "INSERT INTO `user` (`login_name`,`password`,`salt`,`nick`,`email`,`email2`) VALUES ('".$_r['user_name']."','".$_r['password']."','".$_r['ec_salt']."','".$_r['user_name']."','".$_r['email']."','".$_email2."');<br>";			
			
		}
		
		echo 'ok';
		//echo md5(md5('wangsh001').'6677');exit;
		//echo md5('viatang.dg'.'1111111');
	}
	
	public function mkfinace(){
		
		$finace = D('Finance');
		$_list = M()->query("select a.id,b.user_money from user as a , shop_users as b where a.email=b.email");
		//dump($_list);
		foreach ($_list as $_r){
			$item = array('user_id'=>$_r['id'],
					'money' => $_r['user_money']
					);
			$finace->data($item)->add();
		}
		echo 'ok';
	}
}


?>