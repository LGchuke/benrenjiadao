<?php
/**
 * 设备管理
 * User: www.shaoqi.net
 * Date: 2016/4/10
 * Time: 22:35
 */

namespace app\controller;


use think\Model;
use org\Upload;

class Employe extends Init
{
    /***
     * 员工列表
     */
    public function index()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $page = max(I('get.page', 0), 1);
        $where2 = [];
        $model = new Model("IntegralExchange");
        $lsql = 'SELECT a.id, a.job_number, IF(c.hr_flag=0, c.hr_flag, 1) as hr_flag, a.name, a.sex, a.department_id, b.name as depname, a.position, a.venous_record_flag, a.status FROM employe a  '
            . ' INNER JOIN department b ON a.department_id = b.id '
            .' left join employe_hr_rel c on a.id = c.employe_id and a.enterprise_id = c.enterprise_id '
            . ' WHERE a.del_flag = "0" AND a.bound_flag = 1  and  a.enterprise_id = \'' . $entperiseId . '\' ';

        if (!empty(I('get.name'))) {
            $lsql = $lsql . ' and a.name like \'%' . I('get.name') . '%\' ';
        }
        $depid = I('get.depname');
        if (!empty($depid)) {
            $lsql = $lsql . ' and a.department_id = \'' . I('get.depname') . '\' ';
        }
        $pagesql = $lsql . ' order by a.job_number asc limit ' . C("page_limit") . ' offset ' . ($page - 1) * C('page_limit');
        $list = $model->query($pagesql);
        $count = count($model->query($lsql));
        $total = ceil($count / C("page_limit"));
        $this->assign('total', $total);

        $department = new Model();
        $depSql = " SELECT distinct a.id, a.name, a.parent FROM department a  "
            . " WHERE a.del_flag = '0' AND a.enterprise_id = '" . $entperiseId . "' ";
        $depList = $department->query($depSql);
        $this->assign("deplist", $depList);
        return $this->fetch('emplist', ['list' => $list, 'page' => $page, 'total' => $total]);
    }

    /***
     * 新增员工
     */
    public function goadd()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $department = new Model();
        $depSql = " SELECT DISTINCT a.id, a.name, a.parent FROM department a  "
            . " WHERE a.del_flag = '0' AND a.enterprise_id = '" . $entperiseId . "' ";
        $depList = $department->query($depSql);
        $this->assign("deplist", $depList);
        $posSql = "select a.id, a.name from position a where a.status = '0' and  a.enterprise_id = '" . $entperiseId . "' ";
        $posList = $department->query($posSql);
        $this->assign("poslist", $posList);
        return $this->fetch('goadd');
    }

    public function sendmsg($mobile, $name){
        $data = [];
        $entperiseId = parent::getEnterpriseIdByUserId();
        $ent = M("Enterprise");
        $obj = $ent->where("id='$entperiseId' ")->find();
//        $data['name'] = I('post.name/s');
//        $data['name'] = $name;
        $data['name'] = $obj["name"];
        $data['enterprise'] = $obj["name"];
        $data['phone'] = $mobile;
        $json = parent::post_api_data('common.sendAddEmployeSMS', $data);
    }

    /***
     * 编辑员工
     */
    public function goedit()
    {
        $EmpModel = new Model("employe");
        $empid = $_REQUEST["id"];
        $EmpModel->where("id='" . $empid . "'")->find();
        $empData = $EmpModel->data();
        if(array_key_exists("positive_date", $empData) && !is_null($empData["positive_date"])){
            $empData["positive_date"] = date("Y-m-d", strtotime($empData["positive_date"]));
        }
        if(array_key_exists("birthday", $empData) && !is_null($empData["birthday"])){
            $empData["birthday"] = date("Y-m-d", strtotime($empData["birthday"]));
        }
        if(array_key_exists("hiredate", $empData) && !is_null($empData["hiredate"])){
            $empData["hiredate"] = date("Y-m-d", strtotime($empData["hiredate"]));
        }
        $this->assign("employe", $empData);
        $entperiseId = parent::getEnterpriseIdByUserId();
        $department = new Model();
        $depSql = " SELECT distinct a.id, a.name, a.parent FROM department a  "
            . " WHERE a.del_flag = '0' AND a.enterprise_id = '" . $entperiseId . "' ";
        $depList = $department->query($depSql);
        $this->assign("deplist", $depList);

        $posSql = "select a.id, a.name from position a where a.status = '0' and  a.enterprise_id = '" . $entperiseId . "' ";
        $posList = $department->query($posSql);
        $this->assign("poslist", $posList);

        $hrsql = "SELECT a.hr_flag FROM employe_hr_rel a WHERE a.employe_id = '$empid' AND a.enterprise_id = '$entperiseId'";
        $hr = M("")->query($hrsql);

        if(!is_null($hr) && count($hr)>0 && $hr[0]["hr_flag"]=="0"){
            $this->assign("isHr", "1");
        }else{
            $this->assign("isHr", "0");
        }

//        return $this->fetch('goadd', ['deplist' => json_encode($depList) ]);
        return $this->fetch('goedit');
    }

    /**
     * 执行删除操作
     */
    public function godel()
    {
        D('Employe')->where(['id' => I('post.id/s')])->setField(['del_flag' => 1, 'status'=>1]);
        return json_encode(['code' => 0, '人员删除成功']);
    }

    /**
     * 批量删除操作
     */
    public function multdel()
    {
        $ids = $_REQUEST["ids"];
        $idArr = explode(",", $ids);
        foreach ($idArr as $item) {
            D('Employe')->where(['id' => $item])->setField(['del_flag' => 1, 'status'=>1]);
        }
        return json_encode(['code' => 0, '批量删除成功']);
    }

    /**
     * 执行离职操作
     */
    public function golz()
    {
        D('Employe')->where(['id' => I('post.id/s')])->setField(['status' => 1]);
        return json_encode(['code' => 0, '人员设置离职成功']);
    }

    /**
     * 跳转充值积分操作
     */
    public function gointegral()
    {
        $EmpModel = new Model("employe");
        $empid = $_REQUEST["id"];
        $EmpModel->where("id='" . $empid . "'")->find();
        $empData = $EmpModel->data();
        $this->assign("employe", $empData);
        $entperiseId = parent::getEnterpriseIdByUserId();
        $department = new Model();
        $depSql = " SELECT distinct a.id, a.name, a.parent FROM department a  "
            . " WHERE a.del_flag = '0' AND a.enterprise_id = '" . $entperiseId . "' ";
        $depList = $department->query($depSql);
        $this->assign("deplist", $depList);

        $posSql = "select a.id, a.name from position a where a.status = '0' and  a.enterprise_id = '" . $entperiseId . "' ";
        $posList = $department->query($posSql);
        $this->assign("poslist", $posList);
        return $this->fetch('gointegral');
    }

    /**
     * 执行兑换操作
     */
    public function dointegral()
    {
        $empid = $_REQUEST["id"];
        $inte = $_REQUEST["integral"];
        //先判断该企业是否有足够的积分
        $entperiseId = parent::getEnterpriseIdByUserId();
        $emtprise = M("Enterprise") -> where(["id"=> $entperiseId]) -> find();
        $intGap = intval($emtprise["integral"]) - intval($inte);
        if($intGap < 0){
            echo "failed";
        }else{
            M("Enterprise")->where(["id"=> $entperiseId])->data(['integral'=>$intGap])->save();
            $Employe = M("Employe");
            $Employe->where(["id"=> $empid])->setInc('integral', $inte);
            //更新完积分后插入积分充值记录表
            $inteRecharge = M("IntegralRecharge");
            $recharSQL = " select a.id as empid, a.name as empname, b.id as entid, b.name as entname from employe a inner join enterprise b on a.enterprise_id = b.id where a.status = '0' "
                . " and a.id ='" . $empid . "' limit 1 ";
            $recharData = $inteRecharge->query($recharSQL);

            $data["id"] = md5(uniqid());
            $data["user_type"] = "1";
            $data["recharge_id"] = $recharData[0]["entid"];
            $data["recharge_name"] = $recharData[0]["entname"];
            $data["enterprise_id"] = $recharData[0]["entid"];
            $data["enterprise_name"] = $recharData[0]["entname"];
            $data["be_recharged_id"] = $empid;
            $data["be_recharged_name"] = $recharData[0]["empname"];
            $data["amount"] = $inte;
            $data["create_time"] = date('Y-m-d H:i:s');
            $data["status"] = "0";
            $inteRecharge->data($data)->add();
            echo "ok";
        }
    }

    /**
     * 执行更新操作
     */
    public function doedit()
    {
        $entperiseId = parent::getEnterpriseIdByUserId();
        $Employe = M("Employe");
        $emd = $Employe->create();
        $empid = $emd["id"];
        //判断工号
        $jobno = $emd["job_number"];
        $sql = " select a.id from employe a where a.id != '$empid' and a.job_number = '$jobno' AND a.enterprise_id = '$entperiseId' AND a.bound_flag = '1' AND a.status = '0' AND a.del_flag = '0' ";
        $list = M("")->query($sql);
        if(!is_null($list) && count($list) >0){
            return json_encode(['code' => 1, 'msg' => '工号:'.$jobno.'已存在']);
        }
        if(array_key_exists("positive_date", $emd) && !empty($emd["positive_date"]) && !is_null($emd["positive_date"])
            && $emd["positive_date"] != "0000-00-00 00:00:00"){

        }else{
//            unset($emd["positive_date"]);
            $emd["positive_date"]=null;
        }
        if(array_key_exists("birthday", $emd) && !empty($emd["birthday"]) && !is_null($emd["birthday"])
            && $emd["birthday"] != "0000-00-00 00:00:00"){

        }else{
            //unset($emd["birthday"]);
            $emd["birthday"]=null;
        }
        if(array_key_exists("hiredate", $emd) && !empty($emd["hiredate"]) && !is_null($emd["hiredate"])
            && $emd["hiredate"] != "0000-00-00 00:00:00"){

        }else{
//                unset($data["positive_date"]);
            $emd["hiredate"]=null;
        }
        $isHr = "";
        if(array_key_exists("isHr", $emd)){
            $isHr = $emd["isHr"];
            unset($emd["isHr"]);
        }

        if ($emd) {
            $mobile = $emd["mobile"];
            $emd["login_name"] = $emd["mobile"];
            $mobSql = " select id from employe a where a.id != '$empid' and a.del_flag = '0' and a.status = '0' and a.bound_flag = '1' and a.mobile = '$mobile' ";
            $isExist = M("")->query($mobSql);
            if(count($isExist)>0){//存在数据
                return json_encode(['code' => 1, 'msg' => '手机号码已被占用']);
            }
            $Employe->data($emd)->save();
        }
        $hrsql = "select a.id from employe_hr_rel a where a.employe_id = '$empid' and a.enterprise_id= '$entperiseId' ";
        $hrr = M("EmployeHrRel");
        $hrdata = $hrr->query($hrsql);
        if($isHr == "on"){
            //修改HR关系表，如果没有记录，则新增
            $hrsd["employe_id"] = $empid;
            $hrsd["enterprise_id"] = $entperiseId;
            $hrsd["hr_flag"] = 0;
            if(!is_null($hrdata) && count($hrdata)>0 && !empty($hrdata[0]["id"])){
                //存在，更新即可
                $hrsd["id"] = $hrdata[0]["id"];
                $hrr -> data($hrsd) ->save();
            }else{
                $hrsd["id"] = md5(uniqid());
                $hrr -> data($hrsd) ->add();
            }
        }else{
            //如果有记录，则删除
            if(!is_null($hrdata) && count($hrdata)>0 && !empty($hrdata[0]["id"])){
                //存在，更新即可
                $hwhere = [];
                $hwhere["id"] = $hrdata[0]["id"];
                $hrr->where($hwhere)->delete();
            }
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
        //return $this->redirect(U('employe/index'));
    }

    /**
     * 执行新增操作
     */
    public function doadd()
    {
        $Employe = M("Employe");
        $data = $Employe->create();
        if ($data) {
            $isHr = "";

            //判断工号
            $jobno = $this::checkJobno($data["job_number"]);
            if($jobno){
                return json_encode(['code' => 1,'msg' => '工号:'.$data["job_number"].'已存在']);
            }

            if(array_key_exists("isHr", $data)){
                $isHr = $data["isHr"];
                unset($data["isHr"]);
            }
            $entperiseId = parent::getEnterpriseIdByUserId();
            $data["id"] = md5(uniqid());
            $data["create_at"] = date('Y-m-d H:i:s');
            $data["enterprise_id"] = $entperiseId;
            $data["login_name"] = $data["mobile"];

            $data["password"] = md5(888888);
            //create_by
            $data["bound_flag"] = "1";
            /*if(empty($data["positive_date"])){
                $data["positive_date"]= null;
            }*/
            if(array_key_exists("hiredate", $data) && !empty($data["hiredate"]) && !is_null($data["hiredate"])
                && $data["hiredate"] != "0000-00-00 00:00:00"){

            }else{
//                unset($data["positive_date"]);
                $data["hiredate"]=null;
            }
            if(array_key_exists("positive_date", $data) && !empty($data["positive_date"]) && !is_null($data["positive_date"])
                && $data["positive_date"] != "0000-00-00 00:00:00"){

            }else{
//                unset($data["positive_date"]);
                $data["positive_date"]=null;
            }
            if(array_key_exists("birthday", $data) && !empty($data["birthday"]) && !is_null($data["birthday"])
            && $data["birthday"] != "0000-00-00 00:00:00"){

            }else{
//                unset($data["birthday"]);
                $data["birthday"] = null;
            }
            $data["created_by"] = session("userId");
            $data["del_flag"] = "0";
            $data["integral"] = 0;
            $mobile = $data["mobile"];
            $mobSql = " select id, a.bound_flag from employe a where a.status = '0' and del_flag='0' and a.mobile = '$mobile' ";
            $isExist = M()->query($mobSql);
            if($isHr == "on"){//选择了
                //修改HR关系表，如果没有记录，则新增
                $hrsd["id"] = md5(uniqid());
                $hrsd["enterprise_id"] = $entperiseId;
                $hrsd["hr_flag"] = 0;
                $hrr = M("EmployeHrRel");
                $hrr -> data($hrsd) ->add();
            }
            if(count($isExist)>0){//存在数据
                if($isExist[0]["bound_flag"] == 1){
                    return json_encode(['code' => 1, 'msg' => '手机号码已被占用']);
                }else if($isExist[0]["bound_flag"] == 0){
                    $data["id"] = $isExist[0]["id"];
                    unset($data["mobile"]);
                    unset($data["login_name"]);
                    unset($data["password"]);
                    unset($data["create_at"]);
                    $Employe->data($data)->save();
                    return json_encode(['code' => 0, 'msg' => 'ok']);
                }

            }

            $sd = $this::sendmsg($mobile,$data["name"]);
            $Employe->data($data)->add();
        }
        return json_encode(['code' => 0, 'msg' => 'ok', 'name'=>$data["name"], "mobile"=> $mobile]);
        //return $this->redirect(U('employe/index'));
    }

    /**验证工号是否存在*/
    public function checkJobno($jobno){
        $entperiseId = parent::getEnterpriseIdByUserId();
        $sql = " select a.id from employe a where a.job_number = '$jobno' AND a.enterprise_id = '$entperiseId' AND a.bound_flag = '1' AND a.status = '0' AND a.del_flag = '0' ";
        $list = M("")->query($sql);
        if(!is_null($list) && count($list) >0){
            return true;
        }else{
            return false;
        }

    }

    public function uploademp(){
        /*$entperiseId = parent::getEnterpriseIdByUserId();
        $department = new Model();
        $depSql = " SELECT DISTINCT a.id, a.name, a.parent FROM department a  "
            . " WHERE a.del_flag = '0' AND a.enterprise_id = '" . $entperiseId . "' ";
        $depList = $department->query($depSql);
        $this->assign("deplist", $depList);
        $posSql = "select a.id, a.name from position a where a.status = '0' and  a.enterprise_id = '" . $entperiseId . "' ";
        $posList = $department->query($posSql);
        $this->assign("poslist", $posList);
        return $this->fetch('goadd');*/
        return $this->fetch("uploademp");
    }

    /**
     * 数据上传导入
     */
    public function import()
    {
        // 处理上传
        $id = md5(parent::uuid());
        $upload = new Upload(['maxSize' => 9145728,'autoSub'=>false,'callcack'=>false ,'exts' => ['xls', 'xlsx'], 'saveName' => $id, 'rootPath'=>UPLOAD_PATCH],
            'Local');
        $info = $upload->uploadOne($_FILES['file']);
        if(empty($info)){
            return json_encode(['code'=>1,'msg'=>$upload->getError()]);
        }
        //return json_encode(['code'=>0,'msg'=>$upload->getError()]);
        $file_data = UPLOAD_PATCH.$info['savename'];
        // 表格处理
        $data = parent::ExcleImport($file_data);
        unlink($file_data);
        $ref=0;
        $ret = [];
        if (!empty($data)) {
            // 读取职位 读取部门
            $entperiseId = parent::getEnterpriseIdByUserId();
            $department = M('Department')->where(['enterprise_id' => $entperiseId, 'del_flag'=>0])->getField('id,name');
            $position = M('Position')->where(['enterprise_id' => $entperiseId, 'status'=>0])->getField('id,name');
            $insert_data = [];
            foreach ($data as $value) {
                if(!empty($value[0]) && !empty($value[4])) {
                    if(!preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/', $value[4])){
                        $retItem = [];
                        $retItem["value"] = $value;
                        $retItem["msg"] = "手机号码格式错误";
                        $ret[] = $retItem;
                        continue;
                    }
                }else if(empty($value[0])){
                    $retItem = [];
                    $retItem["value"] = $value;
                    $retItem["msg"] = "员工名称为空";
                    $ret[] = $retItem;
                    continue;
                }else if(empty($value[4])){
                    $retItem = [];
                    $retItem["value"] = $value;
                    $retItem["msg"] = "手机号码为空";
                    $ret[] = $retItem;
                    continue;
                }

                $mbi = $value[4];
                $mobSql = " select id, a.bound_flag from employe a where a.status = '0' and del_flag='0' and a.mobile = '$mbi' ";
                $isExist = M()->query($mobSql);
                if(!is_null($isExist) && count($isExist)>0 && $isExist[0]["bound_flag"] == '1'){
                    $retItem = [];
                    $retItem["value"] = $value;
                    $retItem["msg"] = "手机号码已被注册绑定使用";
                    $ret[] = $retItem;
                    continue;
                }
                $id = M('Employe')->getFieldByMobile($value[4], 'id');
                $hiredt = parent::excelTime($value[6]);
                if(!empty($hiredt) && !is_null($hiredt) && $hiredt!='0000-00-00 00:00:00'){

                }else{
                    $hiredt = null;
                }
                if (is_null($isExist) || empty($isExist)) {
                    $insert_data[] = [
                        'id' => md5(parent::uuid()),
                        'num' => 0,
                        'name' => $value[0],
                        'job_number' => '0',
                        'enterprise_id' => $entperiseId,
                        'create_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>session("userId"),
                        'position'=>in_array($value[1],$position)?$value[1]:'普通员工',
                        'sex'=>(($value[3]=='男' || empty($value[3]))?0:1),
                        'department_id'=>in_array($value[2],$department)?array_keys($department,$value[2])[0]:array_keys($department,'未分配部门')[0],
                        'email'=>$value[5],
                        //'hiredate'=>empty(strtotime($value[6]))?date('Y-m-d H:i:s'):date('Y-m-d H:i:s',strtotime($value[6])),
                        'hiredate'=>$hiredt,
                        'login_name'=>$value[4],
                        //'password'=>md5($value[4]),
                        'password'=>md5(888888),
                        'mobile'=>$value[4],
                        'status'=>0,
                        'bound_flag'=>1
                    ];
                    $this::sendmsg($value[4], $value[0]);
                } else if(!is_null($isExist) && count($isExist)>0 && $isExist[0]["bound_flag"] == '0') {
                    M('Employe')->where(['id'=>$id])->setField(['position'=>in_array($value[1],$position)?$value[1]:'普通员工',
                        'sex'=>(($value[3]=='男' || empty($value[3]))?0:1),
                        'department_id'=>in_array($value[2],$department)?array_keys($department,$value[2])[0]:array_keys($department,'未分配部门')[0],
                        'email'=>$value[5],
                        'login_name'=>$value[4],
                        'enterprise_id' => $entperiseId,
                        //'password'=>md5($value[4]),
                        'created_by'=>session("userId"),
                        'hiredate'=>$hiredt,
                        'mobile'=>$value[4],
                        'name' => $value[0],
                        'status'=>0,
                        'bound_flag'=>1
                    ]);
                }
            }
            if(!empty($insert_data)) {
                $ref = M('Employe')->addAll($insert_data);
            }
        }
        return json_encode(['code'=>0,'msg'=>'导入成功','data'=>$ret]);
    }

    public function downloadTempl(){
        $filename = EXCEL_TEMPLATE_PATCH .'employe_import_template.xlsx';
        parent::download_file($filename, '员工导入模板.xlsx');
    }
}