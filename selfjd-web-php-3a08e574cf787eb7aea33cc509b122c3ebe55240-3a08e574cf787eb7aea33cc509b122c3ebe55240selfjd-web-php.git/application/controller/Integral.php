<?php
/**
 * 设备管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 22:35
 */

namespace app\controller;


use think\Model;

class Integral extends Init
{
    /***
     * 积分话费兑换明细
     */
    public function bill()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();

        $page = max(I('get.page', 0), 1);
        $where2      = [];
        $model = new Model("IntegralExchange");
        $lsql = 'SELECT a.id, a.exchange_time, a.employe_id, b.name as empname, a.point, b.mobile, c.name AS entpname, d.name AS deptname FROM integral_exchange a '
            . ' INNER JOIN employe b ON a.employe_id = b.id '
            .' INNER JOIN enterprise c ON b.enterprise_id = c.id '
            .' INNER JOIN department d ON b.department_id = d.id '
            .' where a.exchange_type = \'0\' ';

        if(!empty($entperiseId)){
            $lsql = $lsql.' and b.enterprise_id =  \''.$entperiseId.'\' ';
        }

        if (!empty(I('get.name'))) {
            $lsql = $lsql.' and b.name like \'%'.I('get.name').'%\' ';
        }

        if (!empty(I('get.mobile'))) {
            $lsql = $lsql.' and b.mobile like \'%'.I('get.mobile').'%\' ';
        }

        $pagesql = $lsql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('infos', ['list' => $list, 'page' => $page, 'total' => $total]);

    }

    /**
     * 员工积分获得明细
     */
    public function achieve ()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $page = max(I('get.page', 0), 1);
        $model = new Model("");
        $lsql = 'SELECT a.id, b.name AS empname, d.name AS deptname, b.mobile, a.amount, a.create_time FROM integral_recharge a   '
            . ' INNER JOIN employe b ON a.be_recharged_id = b.id '
            .' INNER JOIN enterprise c ON a.enterprise_id = c.id '
            .' INNER JOIN department d ON b.department_id = d.id '
            .' WHERE a.user_type = \'1\' ';

        if(!empty($entperiseId)){
            $lsql = $lsql.' and b.enterprise_id =  \''.$entperiseId.'\' ';
        }

        if (!empty(I('get.name'))) {
            $lsql = $lsql.' and b.name like \'%'.I('get.name').'%\' ';
        }
        if (!empty(I('get.mobile'))) {
            $lsql = $lsql.' and b.mobile like \'%'.I('get.mobile').'%\' ';
        }

        $pagesql = $lsql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('achieveInfos', ['list' => $list, 'page' => $page, 'total' => $total]);
    }

    /**
     * 企业积分重置明细
     * 在企业权限下面的
     */
    public function recharge ()
    {
        $this->assign("start_date", null);
        $this->assign("end_date", null);
        $entperiseId = parent::getEnterpriseIdByUserId();
        $page = max(I('get.page', 0), 1);
        $model = new Model("");
        $lsql = 'SELECT a.id, a.amount, a.create_time FROM integral_recharge a   '
            . '  INNER JOIN enterprise c ON a.enterprise_id = c.id  '
            .' WHERE a.user_type = \'0\' ';

        if(!empty($entperiseId)){
            $lsql = $lsql.' and c.id =  \''.$entperiseId.'\' ';
        }

        if(array_key_exists("start_date", $_REQUEST) && !empty($_REQUEST['start_date'])){
            $st = $_REQUEST['start_date'];
            $lsql = $lsql." and a.create_time >= '$st' ";
            $this->assign("start_date", $st);
        }
        if(array_key_exists("end_date", $_REQUEST) && !empty($_REQUEST['end_date'])){
            $et = $_REQUEST['end_date'];
            $lsql = $lsql." and a.create_time <= '$et' ";
            $this->assign("end_date", $et);
        }

        $pagesql = $lsql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('rechargeInfos', ['list' => $list, 'page' => $page, 'total' => $total]);
    }

    /**
     * 企业积分充值明细
     * 此处应该是超级管理员的权限下的
     */
    public function eplist ()
    {
        $page = max(I('get.page', 0), 1);
        $model = new Model("");
        $lsql = 'SELECT a.id, c.name, a.recharge_name, a.amount, a.create_time FROM integral_recharge a   '
            .' INNER JOIN enterprise c ON a.enterprise_id = c.id '
            .' WHERE a.user_type = \'0\'  ';

        if (!empty(I('get.name'))) {
            $name = I('get.name/s');
            $lsql = $lsql." and c.name like '%".$name."%' ";
        }

        $pagesql = $lsql.' order by a.create_time desc limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($lsql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        return $this->fetch('eplist', ['list' => $list, 'page' => $page, 'total' => $total]);
    }
}