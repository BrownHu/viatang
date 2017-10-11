<?php
/**
 +------------------------------------------------------------------------------
 * 悠乐网－－ 专业的代购平台
 * 帮助
 +------------------------------------------------------------------------------
 * @category   ulowi
 * @package  ulowi
 * @subpackage  
 * @author    soitun <stone@ulowi.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class HelpViewModel extends ViewModel {
    public $viewFields = array(
       'Help'=>array('id','title','content','sort','status'),
        'HelpType'  =>  array('caption'=>'category', '_on'=>'Help.category_id=HelpType.id')
    );
}
?>
