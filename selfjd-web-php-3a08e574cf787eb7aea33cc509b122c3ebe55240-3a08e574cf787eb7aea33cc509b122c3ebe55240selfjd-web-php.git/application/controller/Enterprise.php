<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/14
 * Time: 0:20
 */

namespace app\controller;


class Enterprise extends Init
{
    /**
     * 企业列表
     *
     * @return mixed
     */
    public function index()
    {
        $page = max(I('get.page'), 1);
        $name = I('get.name');
        $status = I('get.status');
        $pd = [];
        $pd["pageNo"] = $page;
        $pd["pageSize"] = C('page_limit');
        $pd["key1"] = $name;
        if(empty($name)){
            unset($pd["key1"]);
        }
        $pd["key2"] = $status;
        if(is_null($status)){
            unset($pd["key2"]);
        }
        $data = parent::post_api_data('enterpriseWeb.getList', $pd);
        $json = json_decode($data, true);
        if ($json['code'] != '100') {
            return $this->error($json['solution']);
        }
        return $this->fetch('index',
            ['list' => $json, 'page' => $page, 'total' => ceil($json['count'] / C('page_limit'))]);
    }

    /**
     * 重设密码
     */
    public function pwd()
    {
        $json = parent::post_api_data('enterpriseWeb.resetPwd', ['id' => I('post.id')]);
        $json = json_decode($json, true);
        if ($json['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => $json['solution']]);
        } else {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
    }

    /**
     * 审核通过
     */
    public function pass()
    {
        $json = parent::post_api_data('enterpriseWeb.auditPass', ['id' => I('post.id')]);
        $json = json_decode($json, true);
        if ($json['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => $json['solution']]);
        } else {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
    }

    /**
     * 企业信息查看
     *
     * todo: 加入用户信息
     */
    public function info()
    {
        $id   = D('Enterprise')->getFieldBySysUserId($this->userinfo['id'],'id');
        $page = parent::post_api_data('enterpriseWeb.getInfo', ['id' => $id]);
        $page = json_decode($page, true);
        $suid = $this->userinfo['id'];
        $sysUser = D('sysUser')->where("id='$suid'") ->find();
        return $this->fetch('info', ['info' => $page, 'action' => 'info', 'login_name'=>$sysUser["login_name"]]);
    }

    public function edit()
    {
        $id   = D('Enterprise')->getFieldBySysUserId($this->userinfo['id'],'id');
        $page = parent::post_api_data('enterpriseWeb.getInfo', ['id' => $id]);
        $page = json_decode($page, true);

        $suid = $this->userinfo['id'];
        $sysUser = D('sysUser')->where("id='$suid'") ->find();

		$province = D('Province')->order('id')->getField('id,name');
        $city = D('City')->where(['province_id' => $page['result']['provinceId']])->order('id')->getField('id,name');
        $district=D('District')->where(['city_id' => $page['result']['cityId']])->order('id')->getField('id,name');
        return $this->fetch('editinfo', ['info' => $page, 'action' => 'info',
            'province'=>$province,'city'=>$city,'district'=>$district, 'login_name'=>$sysUser["login_name"]]);

    }

    /**
     * 用户信息保存
     */
    public function save()
    {
		$where=[];
        $where['id']=I('post.id');
        $where['name']=I('post.name');
        $where['email']=I('post.email');
        $where['teliphone']=I('post.teliphone');
        $where['mobile']=I('post.mobile');
        $where['scale']=I('post.scale');
        $where['logo']=I('post.logofile');
        $where['busiessLicense']=I('post.qiyefile');
        $where['provinceId']=I('post.provinceId');
        $where['cityId']=I('post.cityId');
        $where['districtId']=I('post.districtId');
        $where['address']=I('post.address');
        $where['summary']=I('post.summary');
        if(empty($where['logo'])){
            unset($where['logo']);
        }
        if(empty($where['busiessLicense'])){
            unset($where['busiessLicense']);
        }
        $json = parent::post_api_data('enterpriseWeb.mod',$where);
        $json = json_decode($json,true);
        if($json['code']==100){
           //return $this->success($json['solution']);
            $this->redirect('enterprise/info');
        }else{
            return $this->error($json['solution']);
        }
    }

    /**
     * 新增积分
     */
    public function points()
    {
        $json = parent::post_api_data('enterpriseWeb.integralRechargeToEnterprise',
            ['beRechargedId' => I('post.id/s'), 'num' => I('post.num/d'),'rechargeId'=>$this->userinfo['id']]);
        $json = json_decode($json, true);
        if ($json['code'] == '100') {
            return json_encode(['code' => 0, 'msg' => $json['solution']]);
        } else {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
    }

    /**
     * 企业审核不通过
     */
    public function unpass()
    {
        $json = parent::post_api_data('enterpriseWeb.auditUnPass', ['id' => I('post.id')]);
        $json = json_decode($json, true);
        if ($json['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => $json['solution']]);
        } else {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
    }

    /**
     * 查看单个企业
     */
    public function shows()
    {
        $id   = I('get.rid/s');
        $type = I('get.type/d');
        $page = parent::post_api_data('enterpriseWeb.getInfo', ['id' => $id]);
        $page = json_decode($page, true);
        if($page['code']!=100){
           return $this->error($page['solution']);
        }
        $suid   = D('Enterprise')->getFieldById($id,'sys_user_id');
        $sysUser = D('sysUser')->where("id='$suid'") ->find();
        return $this->fetch('shows', ['info' => $page, 'action' => $type, 'login_name'=>$sysUser["login_name"]]);
    }
}