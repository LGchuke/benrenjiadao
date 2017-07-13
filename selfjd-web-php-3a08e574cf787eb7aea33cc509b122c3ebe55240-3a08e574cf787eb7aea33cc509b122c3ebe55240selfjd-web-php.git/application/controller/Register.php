<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/1
 * Time: 14:00
 */

namespace app\controller;


use org\Verify;
use think\Controller;

class Register extends Base
{

    /**
     * 注册页面
     * @return mixed
     */
    public function index()
    {
        $province = D('Province')->order('id')->getField('id,name');

        return $this->fetch('index', ['province' => $province]);
    }

    /**
     * 注册执行
     */
    public function doreg()
    {
        if (IS_POST) {
            $Verify = new Verify();
            $check=$Verify->check(I('post.authcode'));
            if(empty($check)){
                return json_encode(['code'=>__LINE__,'msg'=>'验证码错误']);
            }
            $id         = md5(parent::uuid());
            $obj        = D('User');
            if($obj->getFieldByMobile(I('post.mobile'),'id')){
                return json_encode(['code'=>__LINE__,'msg'=>'手机不可用']);
            }
            if($obj->getFieldByLoginName(I('post.regName'),'id')){
                return json_encode(['code'=>__LINE__,'msg'=>'登录名不可用']);
            }

            $ref        = $obj->data([
                'id'         => $id,
                'name'       => I('post.regName'),
                'mobile'     => I('post.mobile'),
                'user_type'  => 4,
                'create_at'  => date('Y-m-d H:i:s'),
                'login_name' => I('post.regName'),
                'password'   => md5(I('post.pwd')),
            ])->add();
            $enterprise = D('Enterprise');
            $ref2=$enterprise->data([
                'id'          => $id,
                'sys_user_id' => $id,
                'status'=>2,
                //'num'=>$enterprise->max('num')+rand(1,100),
                'name'        => I('post.companyname'),
                'email'       => I('post.email'),
                'mobile'      => I('post.mobile'),
                'teliphone'   => I('post.tel'),
                'province_id'=>I('post.province'),
                'city_id'=>I('post.city'),
                'district_id'=>I('post.district'),
                'address'=>I('post.companyaddr'),
                'created_time'=>date('Y-m-d')
            ])->add();
            if($ref && $ref2){
                // 插入数据到 部门表以及 职位表
                M('Position')->data(['name'=>'普通员工','id'=>$id,'status'=>0,'enterprise_id'=>$id])->add();
                M('Department')->data(['name'=>'未分配部门','no'=>9999,'level'=>0,'id'=>$id,'del_flag'=>0,'enterprise_id'=>$id])->add();
                $this->initholiday($id);
                return json_encode(['code' => 0, 'msg' => 'ok']);
            }else{
                $obj->delete($id);
                $enterprise->delete($id);
                return json_encode(['code' => __LINE__, 'msg' => '非法提交']);
            }
        } else {
            return json_encode(['code' => __LINE__, 'msg' => '非法提交']);
        }
    }

    public function initholiday($entperiseId){
        $sql = "select a.name, a.ho_date from kq_holiday_template a ";
        $tlist = M("")->query($sql);
        $exds = D('KqHoliday')->where(['enterprise_id'=>$entperiseId]) -> select();
        if(!is_null($exds) && count($exds)>0){
            //表示已经存在该企业的假期管理，至少是已经初始化过的了
        }else{
            $position = M("KqHoliday");
            foreach($tlist as $item){
                $data["id"] = md5(uniqid());
                $data["ho_date"] = $item["ho_date"];
                $data["name"] = $item["name"];
                $type = 1;
                if(strstr($item["name"], '周六')){
                    $data["type"] = "3";
                    $type = 3;
                }else if(strstr($item["name"], '周日')){
                    $data["type"] = "4";
                }else{
                    $data["type"] = "1";
                }
                $data["enterprise_id"] = $entperiseId;
//                $idtmp = md5(uniqid(). mt_rand(1,1000000));
                $idtmp = md5(time() . mt_rand(-100000,1000000));
                $dataList[] = array('id'=>$idtmp,
                    'ho_date'=>$item["ho_date"],
                    'name'=>$item["name"],
                    'type'=>$data["type"],
                    'enterprise_id'=>$entperiseId);
                //$position->data($data)->add();
            }
            $position->addAll($dataList);
        }
    }

    public function verify()
    {
        $Verify           = new Verify();
        $Verify->useNoise = false;
        $Verify->fontSize = 40;
        $Verify->imageH   = 80;
        $Verify->imageW   = 300;
        $Verify->length   = 4;
        $Verify->useCurve = false;
        //$Verify->fontttf  = '1.ttf';
        $Verify->entry();
    }

    /**
     * 注册前置
     */
    public function check()
    {
        $code  = I('post.pcode');
        $phone = I('post.mobile');
        if (empty($phone) || empty($code)) {
            return json_encode(['code' => __LINE__, 'msg' => '参数错误']);
        }
        if (!preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/', $phone)) {
            return json_encode(['code' => __LINE__, 'msg' => '手机号码格式不正确']);
        }
        $uid = D('User')->getFieldByMobile($phone, 'id');
        if (!empty($uid)) {
            return json_encode(['code' => __LINE__, 'msg' => '手机号码已经被占用']);
        }
        $cookie_code = json_decode(base64_decode(urldecode(cookie('phonecode'))), true);
        if (empty($cookie_code) || ($cookie_code['time'] + 600) < time()) {
            return json_encode(['code' => __LINE__, 'msg' => '短信验证码失效']);
        }
        if ($cookie_code['code'] != $code) {
            return json_encode(['code' => __LINE__, 'msg' => '短信验证码错误']);
        }

        return json_encode(['code' => 0, 'msg' => 'ok']);
    }
}