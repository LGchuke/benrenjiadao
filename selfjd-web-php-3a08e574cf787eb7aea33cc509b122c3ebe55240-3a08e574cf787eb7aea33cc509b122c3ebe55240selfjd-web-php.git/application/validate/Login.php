<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/4
 * Time: 23:17
 */

namespace app\validate;


use think\Validate;

class Login extends Validate
{
    protected $rule = [
        'uname'  =>  'require',
        'upwd' =>  'require|length:6,15',
    ];

    protected $message = [
        'uname.require'  =>  '登录用户名不可为空',
        'upwd.require' =>  '登录密码不可为空',
        'upwd.length'=>'登录密码6到15位',
    ];
}