<?php
/**
 +------------------------------------------------------------------------------
 * DaigouCMS 代购建站系统(淘宝版)
 *
 * 代金券管理
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @author    	soitun <stone@zline.net.cn>
 * @copyright 上海子凌网络科技有限公司
 * @license   	http://www.zline.net.cn/license-agreement.html
 * @link      		http://www.zline.net.cn/
 +------------------------------------------------------------------------------
 */

load ( '@/functions' );

class DaijinquanAction extends BaseAction {
	
	//------------------------------------------------------------------------------------------------
	public function index() {
		    $this->assign ('title','优惠券使用-viatang.com');
	             $this->assign ('keywords','优惠券,优惠券领取,优惠券使用,优惠券有效期,代购优惠券,淘宝代购优惠券,优惠券下载,代购中国商品,淘宝代购,海外华人代购,美国华人代购,美国代购,海外代购,代购网站,加拿大代购,留学生代购,服装代购,图书代购');
                      $this->assign ('description','唯唐代购优惠券领取，优惠券使用下载，全球最专业代购中国商品网站,专为海外华人留学生代购淘宝、亚马逊、京东等中国购物网商品.支持paypal、国际信用卡支付方式.批量下单,多件商品集中寄送,专享超低国际运费.');
		if ($this->user) {
			// 从type表里读出在有效期内的代金券
			$modle = new Model();
			$voucherTypes = $modle->query('select * from ticket_type where id not in(select type_id from ticket where user_id='.$this->user['id'].') and (status=1) and (term>' . time () .')  LIMIT 0,1 ');
			if ($voucherTypes) {
				$voucherType = $voucherTypes[0];
				$this->assign ( 'voucher', $voucherType );
				$count = M ( 'Ticket' )->where ( 'user_id=' . $this->user ['id'] . ' and type_id=' . $voucherType ['id'] )->count (); 
				$voucherUsedCount = M ( 'Ticket' )->where ( 'type_id=' . $voucherType ['id'] )->count ();
				$showApply = ( ($count == 0) && ($voucherUsedCount < $voucherType['count']) ) ? true : false;
				$this->assign ( 'showApply', $showApply );			
			}
			
			$DataList = M ( 'Ticket' )->where ( 'user_id=' . $this->user ['id']  . ' and term>'. time ())->select ();
			$this->assign ( 'DataList', $DataList );
			$this->display ();
		}
	}
	
	//------------------------------------------------------------------------------------------------
	/*
	 * 点击领取代金券的操作
	 */
	public function apply() {
		$type_id = $_POST ['type_id']; // 金券类别ID
		$id = 0;
		if ($this->user && isset ( $_POST ['lingqu'] ) && $type_id && is_numeric ( trim ( $type_id ) )) {
			$ticket_type = M ( 'TicketType' )->where ( 'id=' . $type_id )->find (); // 前类别的代金券
			if ($ticket_type) {
				$user_count = M ( 'Ticket' )->where ( 'type_id=' . $type_id . '  AND user_id=' . $this->user ['id'] )->count ();
				$onetype_count = M ( 'Ticket' )->where ( 'type_id=' . $type_id )->count (); // 实际被领取的数量
				
				if (($onetype_count < $ticket_type ['count']) && ($user_count == 0)) {
					$data ['type_id'] = $ticket_type ['id'];
					$data ['user_id'] = $this->user ['id'];
					$data ['user_name'] = mysql_escape_string ( htmlspecialchars ( trim ( $this->user ['login_name'] ) ) );
					$data ['mianzhi'] = floatval ( $ticket_type ['mianzhi'] );
					$data ['create_time'] = time ();
					$data ['term'] = $ticket_type ['term'];
					$data ['token'] = md5 ( md5 ( $this->user ['id'] . $this->user ['login_name'] . $ticket_type ['mianzhi'] . $ticket_type ['salt'] ) );
					$data ['code'] = rand_string ( 12 ) . $this->user ['id'] . time ();
					
					$ticket_type ['last_seq'] = $ticket_type ['last_seq'] + 1; // 代金券编号
					$data ['use_type'] = $ticket_type ['use_type'];
					$data ['use_amount'] = $ticket_type ['use_amount'];
					$data ['state'] = 1;
					
					$id = M ( 'Ticket' )->data ( $data )->add ();
					$last_seq = $ticket_type ['last_seq'] + 1;
					@M ( 'TicketType' )->execute ( 'UPDATE ticket_type SET last_seq=' . $last_seq . ' WHERE id=' . $type_id );
				}
			}
		}
		if ($id > 0) {
			$this->success ( L('daijinqan_succ') );
		} else {
			$this->error ( L('daijinqun_fail') );
		}
	}
	
	//------------------------------------------------------------------------------------------------
	Public function _empty() {
		$this->redirect ('Index/index' );
	}
}

?>
