<?php
/**
 * 设备管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 22:35
 */

namespace app\controller;


class Device extends Init
{

    /**
     * 企业设备管理
     */
    public function eplist()
    {
        $page = max(I('get.page/d'), 1);
        $name = I('get.name/s');
        $data = parent::post_api_data('enterpriseWeb.getList',
            ['pageNo' => $page, 'pageSize' => C('page_limit'), 'key1' => $name, 'key2'=>0] );
        $json = json_decode($data, true);
        if ($json['code'] != '100') {
            return $this->error($json['solution']);
        }

        return $this->fetch('eplist',
            ['list' => $json, 'page' => $page, 'total' => ceil($json['count'] / C('page_limit'))]);
    }

    /**
     * 设备绑定信息
     * @return mixed
     */
    public function blist()
    {
        $api_data             = [];
        $api_data['pageNo']   = max(I('get.page/d'), 1);
        $api_data['pageSize'] = C('page_limit');
        $api_data['key1']     = I('get.name');
        $api_data['key2']     = I('get.serial_number');
        $api_data['key3']     = I('get.bound_status/d', -1);
        $api_data['id']       = I('get.cid/s');
        if (empty($api_data['key1'])) {
            unset($api_data['key1']);
        }
        if (empty($api_data['key2'])) {
            unset($api_data['key2']);
        }
        if ($api_data['key3'] == -1) {
            unset($api_data['key3']);
        }
        $data = parent::post_api_data('deviceWeb.getListByEnterprise', $api_data);
        $json = json_decode($data, true);
        if ($json['code'] != '100') {
            return $this->error($json['solution']);
        }
        $page = parent::post_api_data('enterpriseWeb.getInfo', ['id' => $api_data['id']]);
        $page = json_decode($page, true);

        return $this->fetch('blist', [
            'list'  => $json,
            'page'  => $api_data['pageNo'],
            'name'  => $page['result']['name'],
            'cid'   => $api_data['id'],
            'total' => ceil($json['count'] / C('page_limit'))
        ]);
    }


    /**
     * 设置状态
     */
    public function change()
    {
        $type = I('post.type', 0);
        $id   = I('post.id');
        $page = parent::post_api_data('deviceWeb.changeBoundStatus', ['id' => $id, 'boundStatus' => $type]);
        $page = json_decode($page, true);
        if ($page['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => 'ok']);
        } else {
            return json_encode(['code' => $page['code'], 'msg' => $page['solution']]);
        }
    }

    /**
     * 设备管理员
     */
    public function admin()
    {
        $id           = I('get.id/s');
        $dev_obj      = D('KqDevice');
//        $devd = $dev_obj->getFieldById($id, 'enterprise_id,num');
        $devd = $dev_obj->where("id='$id'")->find();
        $enterpriseId = $devd["enterprise_id"];
        $devnum = $devd["num"];
        if (empty($enterpriseId)) {
            return $this->error('设备id不正确');
        }
        $obj = D('Enterprise')->getFieldBySysUserId($this->userinfo['id'], 'id');
        if ($obj != $enterpriseId) {
            return $this->error('无权限查看设备');
        }
        $data = ['id' => $id, 'enterpriseId' => $enterpriseId];
        if (!empty(I('get.name/s'))) {
            $data['name'] = I('get.name/s');
        }

        $m = M("");
        $sql = " SELECT a.id,a.name, a.position, a.mobile, b.id as rid, c.id as cid, b.is_admin, b.is_super_admin FROM employe "
            ."  a LEFT JOIN kq_device_person_rel b ON a.id = b.user_id and b.device_id = '$devnum' "
            ." left join kq_device c on b.device_id = c.num "
            ." WHERE a.del_flag = '0' and a.bound_flag='1' AND a.status = '0' AND a.enterprise_id = '$enterpriseId'";
        if (!empty(I('get.name/s'))) {
            $sql = $sql . " and a.name like '%".I('get.name/s')."%' ";
        }
        $page = $m->query($sql);

        /*$page = parent::post_api_data('deviceWeb.authorizedEmployeList', $data);
        $page = json_decode($page, true);*/

        return $this->fetch('admin',
            ['list' => $page, 'info' => $data, 'total' => ceil(count($page) / C('page_limit'))]);
    }

    /**
     * 绑定设备管理员
     */
    public function addadmin()
    {
        $data              = [];
        $data['id']        = I('post.cid/s');
        $rid = I('post.rid/s');
        $data['employeId'] = I('post.id/s');
        $dev_obj           = D('KqDevice');
        $enterpriseId      = $dev_obj->getFieldById($data['id'], 'enterprise_id');
        $dev_num = $dev_obj->getFieldById($data['id'], 'num');
        if (empty($enterpriseId)) {
            return json_encode(['code' => __LINE__, 'msg' => '设备id不正确']);
        }
        $obj = D('Enterprise')->getFieldBySysUserId($this->userinfo['id'], 'id');
        if ($obj != $enterpriseId) {
            return json_encode(['code' => __LINE__, 'msg' => '无权操作']);
        }
        $type            = I('post.type/d');
        $data['isAdmin'] = $type ? 1 : 2;
        //$page            = parent::post_api_data('deviceWeb.employeAuthorized', $data);
        $bindRel = M("KqDevicePersonRel");
        $bindData["user_id"] = I('post.id/s');
        $bindData["device_id"] = $dev_num;
        $bindData['is_admin'] = $type ? 1 : 2;
        $bindData['is_super_admin'] = 2;
        if(empty($rid)){//需要新增关系
            $bindRel->data($bindData)->add();
        }else{//需要解除绑定关系
            $bindData['id'] = $rid;
            $bindRel -> data($bindData)->save();
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
        /*$page            = json_decode($page, true);
        if ($page['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => 'ok']);
        } else {
            return json_encode(['code' => $page['code'], 'msg' => $page['solution']]);
        }*/

    }


    public function info()
    {
        $device_obj = D('KqDevice');
        $where      = [];
        $where['enterprise_id']=M('Enterprise')->getFieldBySysUserId($this->userinfo['id'], 'id');
        if (!empty(I('get.name'))) {
            $where['name'] = ['LIKE', I('get.name')];
        }
        if (!empty(I('get.serial_number'))) {
            $where['serial_number'] = I('get.serial_number');
        }
        if (I('get.bound_status', '-1') != '-1') {
            $where['bound_status'] = I('get.bound_status');
        }
        $page = max(I('get.page/d', 0), 1);
        if (!empty($where)) {
            $total = $device_obj->where($where)->count();
            $list  = $device_obj->where($where)->order('id')->select();
        } else {
            $total = $device_obj->count();
            $list  = $device_obj->order('id')->select();
        }

        return $this->fetch('info', ['list' => $list, 'page' => $page, 'total' => ceil($total / 30)]);
    }

    /**
     * 设备编辑
     */
    public function edit()
    {
        // 直接编辑信息
        $id          = I('get.id/s');
        $cid         = I('get.cid/s');
        $commpay_obj = D('Enterprise');
        $device_obj  = D('KqDevice');
        $info        = $device_obj->where(['enterprise_id' => $cid, 'id' => $id])->find();
        if (empty($info)) {
            $this->error('设备信息不存在');
        }
        $cname = $commpay_obj->getFieldById($cid, 'name');

        // 查询企业信息
        return $this->fetch('edit', ['info' => $info, 'name' => $cname]);
    }

    public function goeditDevice(){
        $id          = I('get.id/s');
        $device_obj  = D('KqDevice');
        $info = $device_obj -> where(['id'=>$id]) -> find();
        return $this->fetch("goeditDevice", ['info'=> $info]);
    }

    public function doeditDevice(){
        $data                 = [];
        //$data['id']           = I('post.id/s');
        $data['name']         = I('post.name/s');
        $data['serial_number'] = I('post.serial_number/s');
        $data['location'] = I('post.location/s');

        $id = I('post.id/s');
        $sn = I('post.serial_number/s');

        $device_obj  = D('KqDevice');
        $info        = $device_obj -> query(" SELECT a.id FROM kq_device a WHERE a.serial_number = '$sn' AND a.bound_status = '1' AND a.id != '$id'  ");
        if (!is_null($info) && count($info)>0) {
            return json_encode(['code' => 1, 'msg' => '该序列号已被使用，请重新确认信息']);
        }

        D('KqDevice')->where(['id'=>I('post.id/s')])->setField($data);
        return json_encode(['code' => 0, 'msg' => 'ok']);
    }

    /**
     * 设备信息保存编辑
     */
    public function doedit()
    {

        $data                 = [];
        $data['id']           = I('post.id/s');
        $data['name']         = I('post.name/s');
        $data['serialNumber'] = I('post.serial_number/s');
        $page                 = parent::post_api_data('deviceWeb.mod', $data);
        $page                 = json_decode($page, true);
        if ($page['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => 'ok']);
        } else {
            return json_encode(['code' => $page['code'], 'msg' => $page['solution']]);
        }
    }

    /**
     * 设备新增绑定
     */
    public function add()
    {
        $cid         = I('get.cid/s');
        $commpay_obj = D('Enterprise');
        $cname       = $commpay_obj->getFieldById($cid, 'name');

        return $this->fetch('add', ['name' => $cname, 'cid' => $cid]);
    }

    /**
     * 设备绑定
     */
    public function dobind()
    {
        $data                 = [];
        $data['name']         = I('post.name/s');
        $data['enterpriseId'] = I('post.cid/s');
        $data['serialNumber'] = I('post.serial_number/s');
        if (empty($data['name']) || empty($data['enterpriseId']) || empty($data['serialNumber'])) {
            return json_encode(['code' => __LINE__, 'msg' => '参数不正确']);
        }
        $page = parent::post_api_data('deviceWeb.create', $data);
        $page = json_decode($page, true);
        if ($page['code'] == 100) {
            return json_encode(['code' => 0, 'msg' => 'ok']);
        } else {
            return json_encode(['code' => $page['code'], 'msg' => $page['solution']]);
        }
    }
}