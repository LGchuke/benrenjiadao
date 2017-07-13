<?php
/**
 * 请假类型
 */

namespace app\controller;


use think\Model;

class Message extends Init
{

    public function feedback()
    {
        $this->assign("start_date", null);
        $this->assign("end_date", null);
        $page = max(I('get.page', 0), 1);
        $where2      = [];
        $model = new Model("IntegralExchange");
        $lsql = "SELECT a.id, a.created_at, a.content, b.name, b.mobile, c.name as entname FROM feedback a inner JOIN employe b ON a.user_id = b.id  " ;
        $lsql = $lsql . " inner JOIN enterprise c ON a.enterprise_id = c.id ";

        if(array_key_exists("start_date", $_REQUEST) && !empty($_REQUEST["start_date"])){
            $st = $_REQUEST['start_date'];
            $lsql = $lsql." and a.created_at >= '$st' ";
            $this->assign("start_date", $st);
        }
        if(array_key_exists("end_date", $_REQUEST) && !empty($_REQUEST["end_date"])){
            $et = $_REQUEST['end_date'];
            $lsql = $lsql." and a.created_at <= '$et' ";
            $this->assign("end_date", $et);
        }

        $pagesql = $lsql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('feedback', ['list' => $list, 'page' => $page, 'total' => $total]);

    }

}