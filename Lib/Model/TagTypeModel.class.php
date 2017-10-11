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
class TagTypeModel extends RelationModel {
	protected $_link = array (
		'tag' => array ( 'mapping_type'  => HAS_MANY, 
					 	 'class_name' 	 => 'Tag', 
					 	 'foreign_key' 	 => 'type_id', 
					     'mapping_name'  => 'tag',						  
					     'mapping_order' => 'id asc',
					      ) 
	);
}
?>