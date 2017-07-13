<?php
/**
 * 用户角色管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 1:07
 */

namespace app\controller;


class Role extends Init
{
    /**
     * 角色列表
     */
    public function index(){
        return $this->fetch('index',['list'=>D('Role')->select()]);
    }

    public function add(){
        // 读取操作列表
        $memnu=D('SysMenu')->field(['id','parent_id','name','remarks'])->where(['is_show'=>1,'status'=>0])->select();
        $memnu_data=[];
        foreach ($memnu as $key=>$value){
            if(empty($value['parent_id'])){
                $memnu_data[$value['id']]=$value;
            }else{
                $memnu_data[$value['parent_id']]['sub'][$value['id']]=$value;
            }
        }
        return $this->fetch('add',['menu'=>$memnu_data]);
    }

    public function save(){
        // 如果是编辑，先删除对应关系，在插入
        $roleid = I('post.rid/s');
        $roleid = empty($roleid)?md5(parent::uuid()):$roleid;
        $menus = I('post.menus/a');
        $rolename = I('post.name/s');
        $remark = I('post.remark/s');
        if(empty($menus)){
            return json_encode(['info'=>'操作菜单不可为空','status'=>'n']);
        }
        if(empty($rolename)){
            return json_encode(['info'=>'角色名称不可为空','status'=>'n']);
        }
        $roleobj = D('Role');
        $db_id = $roleobj->getFieldById($roleid,'id');
        if(empty($db_id)){
            $roleobj->data(['name'=>$rolename,'remark'=>$remark,'id'=>$roleid])->add();
        }else{
            $roleobj->where(['id'=>$db_id])->setField(['name'=>$rolename,'remark'=>$remark]);
        }
        $obj=D('SysRoleMenu');
        $obj->where(['role_id'=>$roleid])->delete();
        $dataList=[];
        foreach ($menus as $menu){
            $dataList[] =['role_id'=>$roleid,'menu_id'=>$menu];
        }
        $ref=$obj->addAll($dataList);
        return json_encode(['info'=>'保存成功','status'=>'y']);
    }

    public function edit(){
        // 读取操作列表
        $roleid = I('get.rid/s');
        $obj=D('SysRoleMenu');
        $roleobj = D('Role');
        $db_info = $roleobj->getById($roleid);

        $checkmenu=$obj->field(['menu_id'])->where(['role_id'=>$roleid])->select();
        foreach ($checkmenu as $k=>$v){
            $checkmenu[$k]=$v['menu_id'];
        }
        $sysmenu_obj = D('SysMenu');
        $memnu=$sysmenu_obj->field(['id','parent_id','name','remarks'])->where(['is_show'=>1,'status'=>0])->select();
        $memnu_data=[];
        foreach ($memnu as $key=>$value){
            if(empty($value['parent_id'])){
                $memnu_data[$value['id']]=$value;
            }else{
                $memnu_data[$value['parent_id']]['sub'][$value['id']]=$value;
            }
        }
        return $this->fetch('edit',['menu'=>$memnu_data,'checkmenu'=>$checkmenu,'rid'=>$roleid,'info'=>$db_info]);
    }

    public function del(){
        return json_encode(['code'=>0,'msg'=>'ok']);
    }
}