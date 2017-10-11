<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐网－－ 专业的代购平台
 * 银行帐号业务模块
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @package  ulowi
 * @subpackage  
 * @author    soitun <stone@ulowi.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class FinanceModel extends Model {
	//加载指定用户编号的财务数据
	public function finace($uid){
		return $this->where("user_id='$uid' ")->find();
	}

	//更新财务信息
	public function updateInfo($obj){
		$this->where('id='.$obj['id'])->save($obj);
	}
}
?>