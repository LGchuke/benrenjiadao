<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/4
 * Time: 23:07
 */

namespace app\model;


class User extends Common
{
    protected $fields = ['id', 'name', 'mobile', 'user_type','remark','create_at','login_name','password','created_by','del_flag','update_time'];
    protected $pk     = 'id';
    protected $tableName='sys_user';

    public function login(){
        $data=$this->data();
        $ref=$this->field(['id','mobile', 'name', 'password','login_name','user_type'])->where('login_name=:uname and del_flag=0')->bind('uname',$data['uname'])->find();
        if(empty($ref)){
            return ['code'=>__LINE__,'msg'=>'该登录名未注册，请注册后登录'];
        }
        if (md5($data['upwd'])==$ref['password']){
            return ['code'=>0,'data'=>$ref];
        }else{
            return ['code'=>__LINE__,'msg'=>'密码错误'];
        }
    }

    public function getUserTypeAttr($value,$data){
        $status = ['0'=>'超级管理员','1'=>'平台运营人员','3'=>'仓库管理员','4'=>'企业管理员'];
        return $status[$data['status']];
    }
}