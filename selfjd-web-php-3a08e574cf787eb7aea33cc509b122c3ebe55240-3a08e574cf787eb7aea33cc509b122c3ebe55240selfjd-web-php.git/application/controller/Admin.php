<?php
/**
 * 平台运营人员管理
 * User: www.shaoqi.net
 * Date: 2016/4/14
 * Time: 0:52
 */

namespace app\controller;


class Admin extends Init
{
    public function index(){
        $page = max(I('get.page/d'),1);
        $name = I('get.name/s');
        $where=[];
        $where['user_type']= ['in','0,1,3'];
        $where['del_flag']=0;
        if(!empty($name)){
            $where['name']=['like','%'.$name.'%'];
        }
        $user_obj=D('User');
        $total = $user_obj->where($where)->count();
        $list = $user_obj-> where($where)->order('user_type asc,create_at desc')->limit((($page-1)*C('page_limit')),C('page_limit'))->select();
        return $this->fetch('index',['list'=>$list,'page'=>1,'total'=>ceil($total/C('page_limit'))]);
    }

    /**
     * 用户信息编辑
     * @return mixed
     */
    public function add(){
        return $this->fetch('add');
    }

    /**
     * 用户信息编辑
     * @return mixed
     */
    public function edit(){
        $info = D('User')->where(['id'=>I('get.id/s')])->find();
        return $this->fetch('edit',['info'=>$info]);
    }

    /**
     * 用户删除
     */
    public function del(){
        D('User')->where(['id'=>I('post.id/s')])->setField(['del_flag'=>1]);
        D('SysUserRole')->where(['user_id'=>I('post.id/s')])->delete();
        return json_encode(['code'=>0,'用户删除成功']);
    }


    public function save(){
        $id = I('post.id');
        $obj = D('User');
        $data=[];
        if(empty($id)){
            // 创建用户
            //判断手机号或者登陆用户是否已经存在
            $data['login_name']=$where['login_name']  = I('post.login_name');
            $data['mobile']=$where['mobile']  = I('post.mobile');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $map['del_flag']  = 0;
            $info=$obj->where($map)->find();
            if(!empty($info)){
                return json_encode(['code'=>__LINE__,'msg'=>'手机号码或者登录用户名重复']);
            }
            $id = md5(parent::uuid());
            $data['id']=$id;
            $data['name']=I('post.name/s');
            $data['user_type']=I('post.user_type/d');
            $data['create_at']=date('Y-m-d H:i:s');
            $data['password']=md5(I('post.password/s'));
            $ref=$obj->data($data)->add();
        }else{
            // 保存用户信息
            $data['mobile']= I('post.mobile');
            $info = $obj->where(['del_flag'=>0,'mobile'=>$data['mobile'],'id'=>['neq',$id]])->find();
            if(!empty($info)){
                return json_encode(['code'=>__LINE__,'msg'=>'手机号码重复']);
            }
            $data['name']=I('post.name/s');
            $data['login_name']=I('post.login_name/s');
            $data['user_type']=I('post.user_type/d');
            $data['update_time']=date('Y-m-d H:i:s');
            $data['mobile']= I('post.mobile/s');
            if(!empty(I('post.password/s'))){
                $data['password']=md5(I('post.password/s'));
            }
            $ref=$obj->where(['id'=>$id])->setField($data);
        }
        if(empty($ref)){
            return json_encode(['code'=>__LINE__,'msg'=>'保存失败']);
        }
        // 删除原始数据
        $sys_obj=D('SysUserRole');
        $sys_obj->where(['user_id'=>$id])->delete();
        $reaf=$sys_obj->data(['user_id'=>$id,'role_id'=>C('user_type')[$data['user_type']]])->add();
        if(empty($reaf)){
            return json_encode(['code'=>__LINE__,'角色绑定失败']);
        }

        return json_encode(['code'=>0,'用户信息保存成功']);
    }
}