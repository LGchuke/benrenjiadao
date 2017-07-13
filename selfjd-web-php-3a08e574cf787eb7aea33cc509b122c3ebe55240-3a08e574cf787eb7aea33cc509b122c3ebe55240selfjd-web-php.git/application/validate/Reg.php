<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/8
 * Time: 1:15
 */

namespace app\validate;


use think\Validate;

class Reg extends Validate
{
    protected $rule = [
        'mobile'  =>  ['require','unique:user'],
        'upwd' =>  'require|length:6,15',
    ];

    protected $regex=[
        'mobile'=>'/^0?(13|15|17|18|14)[0-9]{9}$/',
    ];

    protected $message = [
        'mobile.require'  =>  '手机号不可为空',
        'mobile.regex' =>  '登录密码不可为空',
        'mobile.checkmoile'=>'登录密码6到15位',
    ];
}