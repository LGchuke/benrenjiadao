<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/1
 * Time: 13:59
 */

namespace app\controller;


class Index extends Init
{

    public function index(){
        // 读取权限列表
        // 读取权限列表
        $userTy = $this -> userinfo['user_type'];
        $role = isset(C('user_type')[$this->userinfo['user_type']])?C('user_type')[$this->userinfo['user_type']]:'';
        if(!empty($role)){
            $sysmenu_obj = D('SysMenu');
            $memnu=$sysmenu_obj->field(['sort','id','parent_id','name','remarks','href','icon'])->join('__SYS_ROLE_MENU__ srm','id = srm.menu_id','LEFT')->where(['is_show'=>1,'status'=>0,'srm.role_id'=>$role])->order(['sort'=>'asc'])->select();
            //$memnu=$sysmenu_obj->field(['id','parent_id','name','remarks','href','icon'])->where(['is_show'=>1,'status'=>0])->order(['sort'=>'asc'])->select();
            $memnu_data=[];
            foreach ($memnu as $key=>$value){
                if(empty($value['parent_id'])){
                    $memnu_data[$value['id']]['fa']=$value;
                }else{
                    $memnu_data[$value['parent_id']]['sub'][$value['id']]=$value;
                }
            }
            return $this->fetch('index',['menu'=>$memnu_data,'uname'=>$this->userinfo['name'],'rolename'=>C('user_type_txt')[$this->userinfo['user_type']]]);
        }else{
            return $this->redirect(U('login/index'));
        }

    }

    public function gopwd()
    {
        return $this->fetch("gopwd");
    }

    public function changepwd(){
        $userId = session("userId");
        $oldpwd = $_REQUEST["oldpwd"];
        $newpwd = $_REQUEST["newpwd"];
        $confirmpwd = $_REQUEST["confirmpwd"];

        $sysUser = M("SysUser");
        $user = $sysUser->where(["id"=>$userId, "password"=>md5($oldpwd)])->find();
        if(is_null($user)){
            return json_encode(['code' => 1, 'msg' => "原始密码不正确"]);
        }else{
            $sysUser->where(["id"=>$userId])->setField(["password"=>md5($newpwd)]);
            return json_encode(['code' => 0, 'msg' => "您的密码已重置为".$newpwd.""]);
        }

    }

    public function welcome(){
        $userTy = $this -> userinfo['user_type'];
        if($userTy == 1 || $userTy == 0){
            return $this->fetch('welcome_admin');
        }
        $entperiseId = parent::getEnterpriseIdByUserId();
        $model = M("Enterprise");
        $entname = $model->where("id='".$entperiseId."'")->getField('name');
        $this -> assign('entname', $entname);
        return $this->fetch('welcome');
    }
}