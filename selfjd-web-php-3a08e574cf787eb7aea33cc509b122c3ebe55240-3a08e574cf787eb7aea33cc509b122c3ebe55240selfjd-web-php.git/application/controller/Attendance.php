<?php
/**
 * 考勤部分
 * User: Administrator
 * Date: 2016/4/18
 * Time: 23:07
 */

namespace app\controller;


class Attendance extends Init
{

    /**
     * 获取企业考勤模板详情
     */
    public function goTmplateById(){
        $tp="";
        if(array_key_exists("tp", $_REQUEST)){
            $tp = $_REQUEST["tp"];
        }
        $entId = parent::getEnterpriseIdByUserId();
        $tid = $_REQUEST["id"];
        $signTemp = M("KqSignTemplate");
        $tmpl = $signTemp->where("id='".$tid."'")->find();
        $tmpl["worktime_start"] = date("H:i:s", strtotime($tmpl["worktime_start"]));
        $tmpl["worktime_off"] = date("H:i:s", strtotime($tmpl["worktime_off"]));
        $tmpl["signin_start_time"] = date("H:i:s", strtotime($tmpl["signin_start_time"]));
        $tmpl["signout_end_time"] = date("H:i:s", strtotime($tmpl["signout_end_time"]));

        $dpt = M("department");
        $department = $dpt -> getById($tmpl["department_id"]);
        $departmentId = $department["id"];
        $departmentName = $department["name"];
        $this -> assign("depid", $departmentId);
        $this -> assign("depname", $departmentName);

        $parent = $tmpl["parent"];
        $tmpl["precisions"]=null;
        $tmpl["work_type"]=null;

        $this->assign("parent", $parent);
        $this->assign("tmp1", $tmpl);
        $this->assign("tmp2", $tmpl);
        $this->assign("tmp3", $tmpl);
        $this->assign("tmp4", $tmpl);
        $this-> assign("tid", $tid);
        $this->assign("owid",null);

        if($parent == "1"){
            $this->assign("tmp1", $tmpl);
        }else if($parent == "2"){
            $this->assign("tmp2", $tmpl);
        }else if($parent == "3"){
            $this->assign("tmp3", $tmpl);
        }else if($parent == "4"){
            //看是否开启
            $isOw = $tmpl["enable_overwork"];
            $owData = [];
            if($isOw == "0"){
                $ow = M("KqOverworkSetting");
                $owData  = $ow->where("kq_sign_template_id='".$tid."'")->find();
                $this->assign("owid", $owData["id"]);//加
            }
            if(!is_null($owData) && !empty($owData)){
                $tmpl["precisions"] = $owData["precisions"];
                $tmpl["work_type"] = $owData["work_type"];
            }else{
                $tmpl["precisions"] = "0";
                $tmpl["work_type"] = "0";
            }
            $this->assign("tmp4", $tmpl);
        }
        if(!empty($tp)){
            return $this->fetch('goTemplateByIdView');
        }
        return $this->fetch('goTemplateById');
    }

    /**
     * 考勤模板
     */
    public function template(){
        $signTemp = M("KqSignTemplate");
        $tmp1 = $signTemp->where("id=1")->find();
        $tmp1["worktime_start"] = date("H:i:s", strtotime($tmp1["worktime_start"]));
        $tmp1["worktime_off"] = date("H:i:s", strtotime($tmp1["worktime_off"]));
        $tmp1["signin_start_time"] = date("H:i:s", strtotime($tmp1["signin_start_time"]));
        $tmp1["signout_end_time"] = date("H:i:s", strtotime($tmp1["signout_end_time"]));
        $tmp2 = $signTemp->where("id=2")->find();
        $tmp2["worktime_start"] = date("H:i:s", strtotime($tmp2["worktime_start"]));
        $tmp2["worktime_off"] = date("H:i:s", strtotime($tmp2["worktime_off"]));
        $tmp2["signin_start_time"] = date("H:i:s", strtotime($tmp2["signin_start_time"]));
        $tmp2["signout_end_time"] = date("H:i:s", strtotime($tmp2["signout_end_time"]));
        $tmp3 = $signTemp->where("id=3")->find();
        $tmp3["worktime_start"] = date("H:i:s", strtotime($tmp3["worktime_start"]));
        $tmp3["worktime_off"] = date("H:i:s", strtotime($tmp3["worktime_off"]));
        $tmp3["signin_start_time"] = date("H:i:s", strtotime($tmp3["signin_start_time"]));
        $tmp3["signout_end_time"] = date("H:i:s", strtotime($tmp3["signout_end_time"]));
        $tmp4 = $signTemp->where("id=4")->find();
        $tmp4["worktime_start"] = date("H:i:s", strtotime($tmp4["worktime_start"]));
        $tmp4["worktime_off"] = date("H:i:s", strtotime($tmp4["worktime_off"]));
        $tmp4["signin_start_time"] = date("H:i:s", strtotime($tmp4["signin_start_time"]));
        $tmp4["signout_end_time"] = date("H:i:s", strtotime($tmp4["signout_end_time"]));
        $this->assign("tmp1", $tmp1);
        $this->assign("tmp2", $tmp2);
        $this->assign("tmp3", $tmp3);
        $this->assign("tmp4", $tmp4);
        return $this->fetch("template");
    }

    /**请假类型列表*/
    public function holiday(){
        $this->assign("start_date", null);
        $this->assign("end_date", null);
        $entperiseId = parent::getEnterpriseIdByUserId();
        $page = max(I('get.page', 0), 1);
        $model = M("IntegralExchange");
        $sql = "select a.id, a.name, a.ho_date, a.type from kq_holiday a where a.enterprise_id = '$entperiseId' ";
        if(array_key_exists("start_date", $_REQUEST) && !empty($_REQUEST["start_date"])){
            $st = $_REQUEST['start_date'];
            $sql = $sql." and a.ho_date >= '$st' ";
            $this->assign("start_date", $st);
        }
        if(array_key_exists("end_date", $_REQUEST) && !empty($_REQUEST["end_date"])){
            $et = $_REQUEST['end_date'];
            $sql = $sql." and a.ho_date <= '$et' ";
            $this->assign("end_date", $et);
        }
        $type = "";
        if(array_key_exists("type", $_REQUEST) && !empty($_REQUEST["type"])){
            $et = $_REQUEST['type'];
            $type = $et;
            $sql = $sql." and a.type = '$et' ";
        }
        $sql = $sql." order by a.ho_date asc ";
        $pagesql = $sql.' limit '. C("page_limit") . ' offset ' . ($page-1)*C('page_limit');
        $list = $model ->query($pagesql);
        $count = count($model->query($sql));
        $total =  ceil($count / C("page_limit"));
        $this -> assign('total',$total);
        $this->assign("type", $type);
        return $this->fetch('holiday', ['list' => $list, 'page' => $page, 'total' => $total]);
        //$this->fetch('holiday');
    }

    /*跳转到假期添加页面*/
    public function goaddholiday(){
        return $this->fetch("goaddholiday");
    }

    public function saveHoliday(){
        $position = M("KqHoliday");
        $data = $position -> create();

        if($data){
            $entperiseId = parent::getEnterpriseIdByUserId();
            //判断是否已存在
            $td = $data["ho_date"];
            $csql = " SELECT a.id FROM kq_holiday a WHERE TO_DAYS(a.ho_date) = TO_DAYS('$td') AND a.enterprise_id = '$entperiseId'  ";
            $clist = M("")->query($csql);
            if(!is_null($clist) && count($clist) > 0){
                return json_encode(['code' => 1, 'msg' => '新增失败：日期"'.$td.'"已存在']);
            }

            $data["id"] = md5(uniqid());
            if(empty($data["ho_date"])){
                unset($data["ho_date"]);
            }
            $data["enterprise_id"] = $entperiseId;
            $position->data($data)->add();
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
    }

    /**删除假期*/
    public function delholiday(){
        $id = $_REQUEST["id"];
        D('KqHoliday')->where(['id'=>$id])->delete();
        return json_encode(['code'=>0,'msg'=>'删除成功']);
    }

    /**
     * 启用考勤模板时调用
     */
    public function addtempl(){
        //测试递归


        $signTemp = M("KqSignTemplate");
        $data = $signTemp -> create();
        $entperiseId = parent::getEnterpriseIdByUserId();
        $postData = [];
        $postData["departmentIds"] = $data["department_id"];
        $postData["enterpriseId"] = $entperiseId;
        $postData["parent"] = $data["parent"];
        $postData["worktimeStart"] = '2000-01-01 '.$data["worktime_start"];
        $postData["worktimeOff"] = '2000-01-01 '.$data["worktime_off"];
        $postData["signinStartTime"] = '2000-01-01 '.$data["signin_start_time"];
        $postData["signoutEndTime"] = '2000-01-01 '.$data["signout_end_time"];
        $postData["absenceThreshold1"] = intval($data["absence_threshold1"]);
        $postData["absenceThreshold2"] = intval($data["absence_threshold2"]);

        //以下两个用于修改
        if(array_key_exists( 'sign_tmplate_id',$data)) {
            $postData["id"] = $data["sign_tmplate_id"];
        }
        if(array_key_exists( 'ow_tmplate_id',$data) && !empty($data["ow_tmplate_id"])) {
            $postData["setingId"] = $data["ow_tmplate_id"];
        }

        if(array_key_exists( 'flexible_threshold',$data)) {
            $postData["flexibleThreshold"] = intval($data["flexible_threshold"]);
        }
        if(!empty($data["absence_threshold1"]) || !empty($data["absence_threshold2"])){
            $postData["enableAbsence"] = 0;//开启了
        }else{
            $postData["enableAbsence"] = 1;
        }
        if(!empty($data["flexible_threshold"])){
            $postData["enableFlexible"] = 0;//开启了
        }else{
            $postData["enableFlexible"] = 1;
        }
        if(array_key_exists( 'late_threshold',$data)) {
            $postData["lateThreshold"] = intval($data["late_threshold"]);
        }
        if(!empty($data["late_threshold"]) || !empty($data["late_time"])){
            $postData["enableLateleave"] = 0;//开启了
        }else{
            $postData["enableLateleave"] = 1;
        }

        if(array_key_exists( 'late_time',$data)) {
            $postData["lateTime"] = intval($data["late_time"]);
        }
        if(array_key_exists( 'leave_threshold',$data)) {
            $postData["leaveThreshold"] = intval($data["leave_threshold"]);
        }
        if(array_key_exists( 'leave_time',$data)) {
            $postData["leaveTime"] = $data["leave_time"];
        }
        if(array_key_exists( 'template_type',$data)) {
            $postData["templateType"] = $data["template_type"];
        }
        if(array_key_exists( 'precisions',$data) && array_key_exists( 'work_type',$data)){
            $postData["enableOverwork"] = 0;
            if(array_key_exists( 'precisions',$data)) {
                $postData["precisions"] = $data["precisions"];
            }
            if(array_key_exists( 'work_type',$data)) {
                $postData["workType"] = $data["work_type"];
            }
        }else{
            $postData["enableOverwork"] = 1;
        }
        $postData["templateType"] = 1;

        //用作过滤模板
        $pnt = $data["parent"];
        if($pnt == "1"){
            $postData["enableFlexible"] = 1;
            if(array_key_exists( 'flexibleThreshold',$postData)) {
                unset($postData["flexibleThreshold"]);
            }

            $postData["enableLateleave"] = 1;
            if(array_key_exists( 'lateThreshold',$postData)) {
                unset($postData["lateThreshold"]);
            }
            if(array_key_exists( 'lateTime',$postData)) {
                unset($postData["lateTime"]);
            }
            if(array_key_exists( 'leaveThreshold',$postData)) {
                unset($postData["leaveThreshold"]);
            }
            if(array_key_exists( 'leaveTime',$postData)) {
                unset($postData["leaveTime"]);
            }

        }else if($pnt == "2"){
            $postData["enableLateleave"] = 1;
            if(array_key_exists( 'lateThreshold',$postData)) {
                unset($postData["lateThreshold"]);
            }
            if(array_key_exists( 'lateTime',$postData)) {
                unset($postData["lateTime"]);
            }
            if(array_key_exists( 'leaveThreshold',$postData)) {
                unset($postData["leaveThreshold"]);
            }
            if(array_key_exists( 'leaveTime',$postData)) {
                unset($postData["leaveTime"]);
            }
        }else if($pnt == "3"){
            $postData["enableFlexible"] = 1;
            if(array_key_exists( 'flexibleThreshold',$postData)) {
                unset($postData["flexibleThreshold"]);
            }
        }else if($pnt == "4"){
            $postData["enableFlexible"] = 1;
            if(array_key_exists( 'flexibleThreshold',$postData)) {
                unset($postData["flexibleThreshold"]);
            }
        }

        $json = parent::post_api_data('template.update', $postData);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
    }

    /**
     * 考勤统计
     */
    public function census()
    {
        $where = $searh_data = $info = [];
        $where['enterpriseId'] = parent::getEnterpriseIdByUserId();
        $where['startDate'] = $searh_data['mindate'] = $info['mindate'] = I('get.mindate/s', date('Y-m-d'));
        $where['endDate'] = $searh_data['maxdate'] = $info['maxdate'] = I('get.maxdate/s', date('Y-m-d'));
        if (!empty(I('get.name/s'))) {
            $where['employeName'] = $searh_data['name'] = I('get.name/s');
        }
        if (!empty(I('get.depSel/s'))) {
            $where['departmentId'] = $searh_data['depSel'] = I('get.depSel/s');
        }
        $json = parent::post_api_data('statisticsWeb.getListByEnterprise', $where);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return $this->error($json['solution']);
        }
        $infodata = [
            'list' => $json['results'],
            'total' => 1,
            'search' => $searh_data,
            'one' => [
                'mindate' => date('Y-m-01'),
                'maxdate' => date('Y-m-d', strtotime(date('Y-m-01') . ' +1 month -1 day'))
            ],
            'next' => [
                'mindate' => date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')),
                'maxdate' => date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'))
            ],
            'infourl'=>$info
        ];
        return $this->fetch('censuslist', $infodata);
    }

    /**
     * 企业考勤
     */
    public function enterprise()
    {
        // 默认当前
        $where = [];
        $where['id'] = parent::getEnterpriseIdByUserId();
        // 获取部门信息
        $json = parent::post_api_data('template.getListByEnterprise', $where);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return $this->error($json['solution']);
        }
        return $this->fetch('elist', ['list' => $json['results'], 'total' => 1]);
    }

    /**
     * 考勤统计导出
     */
    public function export()
    {
        $where = [];
        $where['enterpriseId'] = parent::getEnterpriseIdByUserId();
        $where['startDate'] = I('get.mindate/s', date('Y-m-d'));
        $where['endDate'] = I('get.maxdate/s', date('Y-m-d'));
        if (!empty(I('get.name/s'))) {
            $where['employeName'] = I('get.name/s');
        }
        if (!empty(I('get.depid/s'))) {
            $where['departmentId'] = I('get.depid/s');
        }
        $json = parent::post_api_data('statisticsWeb.getListByEnterprise', $where);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return $this->error($json['solution']);
        }
        $table_name = [
            '姓名',
            '工号',
            '部门	',
            '手机号',
            '出勤天数(天)',
            '迟到(次)',
            '早退(次)',
            '旷工(次)',
            '加班时长(小时)',
            '请假(小时)',
            '调休(小时)',
            '出差(天)'
        ];
        $data = [];
        foreach ($json['results'] as $k => $value) {
            $data[$k] = [
                $value['employeName'],
                $value['jobNumber'],
                $value['departmentName'],
                $value['employeMobile'],
                $value['signNum'],
                $value['lateNum'],
                $value['leaveEarlyNum'],
                $value['absenceNum'],
                $value['overReal'],
                $value['leaveSum'],
                $value['daysoffSum'],
                $value['travelDays']
            ];
        }
        parent::ExcleExport('企业考勤数据导出', $table_name, $data);
    }

    /**
     * 考勤统计明细
     */
    public function subsidiary()
    {
        $where = [];
        $where['enterpriseId'] = parent::getEnterpriseIdByUserId();
        $where['startDate'] = I('get.mindate/s', date('Y-m-d'));
        $where['endDate'] = I('get.maxdate/s', date('Y-m-d'));
        $where['employeId'] = I('get.employeid/s');
        $json = parent::post_api_data('statisticsWeb.getListByEmploye', $where);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return $this->error($json['solution']);
        }
        return $this->fetch('subsidiary', ['list' => $json['results'], 'total' => 1]);
    }

    /**
     * 考勤规则明细
     */
    public function regulation()
    {
        $where = [];
        $where['id'] = parent::getEnterpriseIdByUserId();
        $json = parent::post_api_data('template.getListByEnterprise', $where);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return $this->error($json['solution']);
        }
        $info = [];
        foreach ($json['results'] as $result) {
            if($result['id']==I('get.id')){
                $info = $result;
                break;
            }
        }
        return $this->fetch('regulation', $info);
    }

    /**
     * 考勤模板删除
     */
    public function del()
    {
        $id = I('post.id/s');
        $json = parent::post_api_data('template.delet', ['id' => $id]);
        $json = json_decode($json, true);
        if ($json['code'] != 100) {
            return json_encode(['code' => __LINE__, 'msg' => $json['solution']]);
        }
        return json_encode(['code' => 0, 'msg' => 'ok']);
    }

    /**
     * 模板数据添加
     */
    public function add()
    {

    }

    /**
     * 模板数据编辑
     */
    public function edit()
    {

    }
}