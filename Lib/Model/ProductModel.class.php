<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐网－－ 专业的代购平台
 * 浏览宝贝
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @package  ulowi
 * @subpackage  
 * @author    soitun <stone@ulowi.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ProductModel extends RelationModel {
	protected $_link = array (
		'review' => array ( 'mapping_type'  => HAS_MANY, 
							'class_name' 	=> 'ProductReview', 
							'foreign_key' 	=> 'product_id', 
							'mapping_name' 	=> 'ProductReview',
							'condition'		=> 'status=1', 
							'mapping_order' => 'create_at desc',
							'mapping_limit' => 2 ) 
	);
}
?>