<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/13
 * Time: 1:24
 */

namespace app\kq\controller;


class Company extends Init
{
    /**
     * 企业列表
     */
    public function index(){
        $qyobj=D('Enterprise');

        return $this->fetch('index',['list'=>'','page'=>1,'total'=>'']);
    }

    /**
     * 企业信息详情
     */
    public function info(){

    }

    /**
     * 我的企业信息详情
     */
    public function myinfo(){

    }

    /**
     * 企业设备管理
     */
    public function device(){

    }
}