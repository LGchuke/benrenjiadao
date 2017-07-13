<?php
/**
 * 设备管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 22:35
 */

namespace app\controller;


use think\Model;

class Department extends Init
{
    /***
     * 员工列表
     */
    public function index()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $model = new Model("enterprise");
        $entname = $model->where("id='".$entperiseId."'")->getField('name');
        $this -> assign('entname', $entname);

        $page = max(I('get.page', 0), 1);
        $where2 = [];
        $model = new Model("IntegralExchange");
        $lsql = 'SELECT a.id, a.job_number, IF(c.hr_flag=0, c.hr_flag, 1) as hr_flag, a.name, a.sex, a.department_id, b.name as depname, a.position, a.status FROM employe a  '
            . ' INNER JOIN department b ON a.department_id = b.id '
            .' left join employe_hr_rel c on a.id = c.employe_id and a.enterprise_id = c.enterprise_id '
            . ' WHERE a.del_flag = "0" AND a.bound_flag = 1  and  a.enterprise_id = \'' . $entperiseId . '\' ';

        $depid = I('get.depid');
        $depname = I('get.depname');
        if (!empty($depid)) {
            $lsql = $lsql . ' and a.department_id = \'' . $depid . '\' ';
        }
        $pagesql = $lsql . ' order by a.job_number asc limit ' . C("page_limit") . ' offset ' . ($page - 1) * C('page_limit');
        $list = $model->query($pagesql);
        $count = count($model->query($lsql));
        $total = ceil($count / C("page_limit"));
        $this->assign('total', $total);

        $dplist = $this::get_array(null, $entperiseId);
        $json = json_encode($dplist);
        $this->assign("deps", $json);

        return $this->fetch('deptlist', ['list' => $list, 'page' => $page, 'total' => $total, 'depname'=>$depname]);

    }

    function get_array($id, $emtid){
        $m = M("");
        $sql = "";
        if(is_null($id)){
//            $sql = "select id,name,IF(parent, parent, 0) AS pId from department where parent is null";
            $sql = "select id,name,parent AS pId from department where  del_flag ='0' and enterprise_id = '$emtid' AND parent is null";
        }else{
            $sql = "select id,name,parent AS pId from department where  del_flag ='0' and enterprise_id = '$emtid' AND parent= '$id'";
        }
        $result = $m->query($sql) ;//查询子类
        $arr = array();
        if($result){//如果有子类
            foreach($result as $item){
                $tmp["id"] = $item["id"];
                $tmp["name"] = $item["name"];
                $tmp["pId"] = $item["pid"];
                $tmp['children'] = $this::get_array($tmp["id"], $emtid);
                $arr[] = $tmp;
            }
            return $arr;
        }
    }

    function get_arrayExclude($id, $emtid){
        $m = M("");
        $sql = "";
        if(is_null($id)){
//            $sql = "select id,name,IF(parent, parent, 0) AS pId from department where parent is null";
            $sql = "select id,name,parent AS pId from department where NOT EXISTS ( SELECT 1 FROM kq_sign_template b WHERE b.department_id = department.id AND b.status = '0' ) and del_flag ='0' and enterprise_id = '$emtid' ";
        }else{
            $sql = "select id,name,parent AS pId from department where  NOT EXISTS ( SELECT 1 FROM kq_sign_template b WHERE b.department_id = department.id AND b.status = '0' ) and del_flag ='0' and enterprise_id = '$emtid' AND parent= '$id'";
        }
        $result = $m->query($sql) ;//查询子类
        $arr = array();
        if($result){//如果有子类
            foreach($result as $item){
                $tmp["id"] = $item["id"];
                $tmp["name"] = $item["name"];
                $tmp["pId"] = $item["pid"];
                $tmp['children'] = $this::get_arrayExclude($tmp["id"], $emtid);
                $arr[] = $tmp;
            }
            return $arr;
        }
    }

    /**
     * 选择层级部门
     */
    public function selectDep(){
        $entperiseId = parent::getEnterpriseIdByUserId();
        $list = $this::get_array(null, $entperiseId);
        $json = json_encode($list);
        //echo $json;
        $this->assign("deps", $json);
        $this-> fetch("selectDep");
    }

    /**
     * 选择层级部门不包括已选模板的部门
     */
    public function selectDepExcludeSeleted(){
        $entperiseId = parent::getEnterpriseIdByUserId();
        $list = $this::get_arrayExclude(null, $entperiseId);
        $json = json_encode($list);
        //echo $json;
        $this->assign("deps", $json);
        $this-> fetch("selectDep");
    }

    /***
     * 保存部门列表
     */
    public function save() {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $saveId = $_REQUEST["saveid"];
        $depName = $_REQUEST["depName"];
        $sql = "select * from department a where a.name = '$depName' and a.del_flag = '0' and a.enterprise_id = '$entperiseId' ";
        if(!empty($saveId)){
            $sql = $sql . " and a.id != '$saveId' ";
        }
        $nameExist = M("Department") -> query($sql);
        if(!is_null($nameExist) && count($nameExist)>0){
            return json_encode(['code' => 1, 'msg' => '该部门名称已存在!']);
        }

        $operTyep = $_REQUEST["operType"];
        $parentDepId = null;
        if(array_key_exists( 'parentDepId',$_REQUEST) && !empty($_REQUEST["parentDepId"])) {
            $parentDepId=$_REQUEST['parentDepId'];
        }

        $depNo = $_REQUEST["depNo"];
        $depOwner = null;
        if(array_key_exists( 'depOwner',$_REQUEST) && !empty($_REQUEST["depOwner"])) {
            $depOwner = $_REQUEST["depOwner"];
        }
        $Department = new Model("Department");
        $Department -> del_flag = "0";
        $Department -> enterprise_id = $entperiseId;
        $Department -> name = $depName;
        $Department -> no = $depNo;
        $Department -> parent = $parentDepId==-1 ? null : $parentDepId;
        $empManRel = new Model("EmployeManagerRel");
        if($operTyep == 'add'){//新增记录
//            $num = $Department -> query(" select max(num) as num from department ")[0]["num"];
            $newId = md5(uniqid());
            $Department -> id = $newId;
//            $Department -> num = $num+1;
            $Department -> add();
            $empManRel -> id = md5(uniqid());
            $empManRel -> department_id = $newId;
        }else{//编辑更新
            $Department -> id = $saveId;
            $Department -> save();
            $empManRel -> department_id = $saveId;
        }
        if(!is_null($depOwner) && !empty($depOwner)){
            $empManRel -> manager_id = $depOwner;
            $empManRel -> enterprise_id = $entperiseId;
            $empManRelList = $empManRel -> query(" select id from employe_manager_rel where enterprise_id = '".$entperiseId."' and department_id = '".$saveId."' limit 1  ");
            if(count($empManRelList) > 0){
                $empManRel -> id = $empManRelList[0]["id"];
                $empManRel -> save();
            }else{
                $empManRel -> id = md5(uniqid());
                $empManRel -> add();
            }
        }
//        return $this->redirect(U('department/index'));
        return json_encode(['code' => 0, 'msg' => '更新成功']);
    }

    public function checkName(){
        $depName = '';
        $curDepId='';
        $entperiseId = parent::getEnterpriseIdByUserId();
        if(array_key_exists( 'depName',$_REQUEST)) {
            $depName=$_REQUEST['depName'];
        }
        if(array_key_exists( 'curDepId',$_REQUEST)) {
            $curDepId=$_REQUEST['curDepId'];
        }
        $sql = "select * from department a where a.name = '$depName' and a.del_flag = '0' and a.enterprise_id = '$entperiseId' ";
        if(!empty($curDepId)){
            $sql = $sql . " and a.id != '$curDepId' ";
        }
        $nameExist = M("Department") -> query($sql);
        if(!is_null($nameExist) && count($nameExist)>0){
            return "has";
        }else{
            return "no";
        }
    }

    public function getParentDep(){
        $pId = "-1";
        if(array_key_exists( 'dpid',$_REQUEST)) {
            $pId=$_REQUEST['dpid'];
        }
        $data = M("")->query(" SELECT a.id, a.name as cname, a.no as cno, b.id, b.name FROM department a LEFT JOIN department b ON a.parent = b.id WHERE a.del_flag = '0' and  a.id ='$pId' ");
        return json_encode($data);
    }

    //获取所有的部门
    public function getAlldepartments(){
        $entperiseId = parent::getEnterpriseIdByUserId();
        $sql = " select a.id, a.name from department a where a.enterprise_id = '$entperiseId' and a.del_flag = '0' ";
        $data = M("") -> query($sql);
        return json_encode($data);
    }

    //获取部门负责人
    public function getDepOwners(){
        $pId = "-1";
        if(array_key_exists( 'dpid',$_REQUEST)) {
            $pId=$_REQUEST['dpid'];
        }
        $entperiseId = parent::getEnterpriseIdByUserId();
        $sql = " SELECT a.id, a.name FROM employe a  "
            ."  WHERE a.enterprise_id = '$entperiseId' AND a.department_id = '$pId' "
            ." AND a.bound_flag = '1' AND a.status = '0' AND a.del_flag = '0' ";
        $data = M("")->query($sql);
        return json_encode($data);
    }

    public function getDepartmentDate(){
        $pId = "0";
        $pName = "";
        $pLevel = "";
        $pCheck = "";
        if(array_key_exists( 'id',$_REQUEST)) {
            $pId=$_REQUEST['id'];
        }
        if(array_key_exists( 'level',$_REQUEST)) {
            $pLevel=$_REQUEST['level'];
        }
        if(array_key_exists('name',$_REQUEST)) {
            $pName=$_REQUEST['name'];
        }
        if ($pId==null || $pId=="") $pId = "0";
        if ($pLevel==null || $pLevel=="") $pLevel = "0";
        if ($pName==null) $pName = "";
        else $pName = $pName.".";
        $pId = htmlspecialchars($pId);
        $pName = htmlspecialchars($pName);
        $entperiseId = parent::getEnterpriseIdByUserId();
        //查询id
        $listSql = " SELECT a.id, a.name, a.parent, a.level, (SELECT COUNT(1) AS c FROM department b WHERE b.parent = a.id) AS hasc FROM department a "
                    ." WHERE a.del_flag = 0 and a.enterprise_id = '$entperiseId' ";

        if(empty($pId)){
            $listSql = $listSql ." AND a.parent IS NULL ";
        }else{
            $listSql = $listSql ." AND a.parent ='".$pId."' ";
        }
        $listSql = $listSql ." order by a.no asc ";
        $model = new Model();
        $list = $model ->query($listSql);

        $json = '';
        foreach($list as $item){
            $nId = $item["id"];
            $nName = $item["name"];
            $isParent = "true";
            $hasC = $item["hasc"];
            if($hasC == 0){
                $isParent = "false";
            }
            $json = $json . ",{ id: '".$nId."', name:'".$nName."', isParent: '".$isParent."'}";
        }
        if(!empty($json)){
            $json = substr($json, 1);
        }
        echo "[".$json."]";
    }

    /**
     * 点击编辑部门的时候ajax调用
     */
    public function getDepartmentById(){
        $depId = $_REQUEST['depId'];
        $entperiseId = parent::getEnterpriseIdByUserId();

        $listSql = " SELECT a.id, a.parent, (SELECT b.name FROM department b WHERE b.id = a.parent) AS parentName, a.no AS depNo, a.name AS depName, c.manager_id, "
                    ." d.name AS empName, d.id AS empId "
                    ." FROM department a  "
                    ." LEFT JOIN employe_manager_rel c ON a.id = c.department_id "
                    ." LEFT JOIN employe d ON d.department_id = a.id "
                    ." WHERE a.del_flag = '0'  "
                    ." AND a.enterprise_id = '".$entperiseId."'  ";
        /*if(!empty($depId)){
            $listSql = $listSql . " AND a.id = '".$depId."'";
        }*/
        $model = new Model();
        $list = $model -> query($listSql);
        $json = json_encode($list);
        echo $json;
    }

    /**
     * 点击新增部门的时候ajax调用
     */
    public function getDepartmentNewAdd(){
        $depId = $_REQUEST['depId'];
        $entperiseId = parent::getEnterpriseIdByUserId();

        $listSql = " select a.id, a.parent, a.name , a.no, a.enterprise_id, b.id as empid, b.name as empname from "
                ." department a left join  employe b on b.department_id = a.id and b.status = '0' "
                ." where a.del_flag = '0' "
                ." and a.enterprise_id = '".$entperiseId."' ";
        if(!empty($depId)){
//            $listSql = $listSql . " AND a.id = '".$depId."'";
        }else{
//            $listSql = $listSql. " AND a.parent is null ";
//            $listSql = $listSql. " AND a.id = '-1' ";
        }
        $model = new Model();
        $list = $model -> query($listSql);
        $json = json_encode($list);
        echo $json;
    }

    /**
     * 删除部门
     */
    public function del()
    {
        $id = $_REQUEST["id"];
        $data["id"] = $id;
        if(!empty($id)){
            //判断是否为保留
            /*$bl = M("")->query("select del_flag from department a where a.id = '$id' ");
            if(count($bl) > 0 && $bl["del_flag"] == 2){
                return 2;
            }*/

            //$json='{"code":"100","solution":"删除成功","message":"操作成功"}';
           $json = parent::post_api_data('department.delet', $data);
        }
        $json = json_decode($json, true);
        if ($json['code'] == 100) {
            return json_encode(['code' => 0, 'msg'=>'部门删除成功']);
        }else{
            return json_encode(['code' => 1, 'msg'=>$json['solution']]);
        }

    }

    public function goadddep(){
        $entperiseId = parent::getEnterpriseIdByUserId();
        $model = new Model("enterprise");
        $entname = $model->where("id='".$entperiseId."'")->getField('name');
        $this -> assign('entname', $entname);
        return $this->fetch("goadddep");
    }

    public function goeditdep(){
        $id = '';
        if(array_key_exists("id", $_REQUEST)  && !empty($_REQUEST["id"])){
            $id = $_REQUEST["id"];
        }
        $entperiseId = parent::getEnterpriseIdByUserId();
        $model = new Model("enterprise");
        $entname = $model->where("id='".$entperiseId."'")->getField('name');
        $this -> assign('entname', $entname);
        $this->assign("saveid", $id);

        $depOwner = D("EmployeManagerRel") -> where(["enterprise_id"=>$entperiseId, "department_id"=>$id])->limit(1)->find();
        $this-> assign("depOwnerId", $depOwner["manager_id"]);

        return $this->fetch("goeditdep");
    }
}