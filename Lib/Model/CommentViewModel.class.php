<?php

/**
 * Created by PhpStorm.
 * User: hu
 * Date: 2017/10/19
 * Time: 下午5:29
 */
class CommentViewModel extends   ViewModel{
    public $viewFields = array(
        'User'=>array('id','login_name','_type'=>'LEFT'),
        'Comment'  =>  array('content','create_time', '_on'=>'User.id=Comment.user_id')
    );
}