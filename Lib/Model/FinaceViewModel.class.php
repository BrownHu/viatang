<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐网－－ 专业的代购平台
 * 随便看看模块，商品－购买批次 视图
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @package  ulowi
 * @subpackage  
 * @author    soitun <stone@ulowi.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class FinaceViewModel extends ViewModel {
    public $viewFields = array(
       'User'=>array('id','login_name','_type'=>'LEFT'),
       'Finance'  =>  array('consumption_total'=>'total', '_on'=>'User.id=Finance.user_id')
    );
}
?>
