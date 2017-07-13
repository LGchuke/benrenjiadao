<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/1
 * Time: 14:00
 */

namespace app\controller;

use org\Crypt;

class Login extends Base
{
    public function _initialize()
    {
        $islogin=parent::checklogin();
        if(!empty($islogin)){
            $this->redirect('index/index');
        }
    }

    /**
     * 登陆页面
     */
    public function index(){
        return $this->fetch('index');
    }

    public function dologin(){
        if(IS_POST) {
            $data=[];
            $data['uname']= I('post.uname');
            $data['upwd']= I('post.pwd');
            $obj = D('User');
            $check=$obj->validate('Login')->create($data);
            if(empty($check)){
                return json_encode(['code'=>-1,"msg"=>$obj->getError()]);
            }
            $res=$obj->login();
            if(empty($res) || !empty($res['code'])){
                return json_encode(['code'=>-1,"msg"=>empty($res['msg'])?'数据异常':$res['msg']]);
            }
            //将登陆成功的userId 放入session中
            if(!empty($res['data']['id'])){
                session("userId", $res['data']['id']);
            }
            // 判断身份，如果是企业主，则要判断 企业是否已经通过审核
            if($res['data']['user_type']==4){
                $status = D('Enterprise')->getFieldBySysUserId($res['data']['id'],'status');
                if($status==2){
                    //return json_encode(['code'=>-1,"msg"=>"等待审核"]);
                }elseif($status == 0){//如果是审核通过的，那么将user_id放到session中

                }
            }
            $auto = I('post.autologin',0);
            $auto = intval($auto);
            $kqauth = base64_encode(json_encode(['amuid'=>$res['data']['id'],'uname'=>$res['data']['login_name']]));
            cookie('kqauth',$kqauth);
            return json_encode(['code'=>1,"kqauth"=>$kqauth]);

        }else{
            return json_encode(['code'=>-1,"msg"=>"非法操作"]);
        }
    }
}