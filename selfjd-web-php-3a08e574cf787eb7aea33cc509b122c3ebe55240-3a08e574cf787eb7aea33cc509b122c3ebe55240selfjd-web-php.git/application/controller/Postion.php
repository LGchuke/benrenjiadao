<?php
/**
 * 设备管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 22:35
 */

namespace app\controller;


use think\Model;

class Postion extends Init
{
    /***
     * 员工列表
     */
    public function index()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $page = max(I('get.page', 0), 1);
        $where2      = [];
        $model = new Model("IntegralExchange");
        $lsql = 'SELECT a.id, a.name FROM position a  '
            .' WHERE a.enterprise_id = \''.$entperiseId.'\' and a.status = \'0\' ';

        $pagesql = $lsql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('posilist', ['list' => $list, 'page' => $page, 'total' => $total]);

    }

    public function goadd(){

        return $this->fetch("goadd");
    }

    public function save(){
        $position = M("position");
        $data = $position -> create();
        if($data){
            $entperiseId = parent::getEnterpriseIdByUserId();
            $data["id"] = md5(uniqid());
            $data["enterprise_id"] = $entperiseId;
            $data["status"] = "0";
            $position->data($data)->add();
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
    }

    public function del(){
        $id = $_REQUEST["id"];
        /*$bl = M("")->query("select status from position a where a.id = '$id' ");
        if(count($bl) > 0 && $bl["status"] == 2){
            return json_encode(['code'=>1,'msg'=>'默认职位不能被删除']);
        }*/
        D('position')->where(['id'=>$id])->setField(['status'=>1]);
        return json_encode(['code'=>0,'msg'=>'删除成功']);
    }

}