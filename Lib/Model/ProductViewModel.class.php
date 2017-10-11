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
class ProductViewModel extends ViewModel {
    public $viewFields = array(
       'Product'=>array('id','title','url','price1','user_name','product_type','seller','last_update','thumb','image','_type'=>'LEFT'),
       'OrdersSaler'  =>  array('rebate_total'=>'rebate', '_on'=>'OrdersSaler.id=Product.order_saler_id')
    );
}
?>
