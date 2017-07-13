<?php
/**
 * 公共方法
 * User: admin@shaoqi.net
 * Date: 2016/4/4
 * Time: 14:58
 */

namespace app\controller;


use org\Crypt;
use think\Controller;
use think\Cookie;

class Base extends Controller
{

    public function gopwd()
    {
        return $this->fetch("gopwd");
    }

    /**
     * 检查是否登陆
     */
    protected function checklogin()
    {
        $auth = cookie('kqauth');
        if (empty($auth)) {
            return false;
        }
        /*$auth= Crypt::decrypt($auth,C('sae_key'));
        if(empty($auth)){
            return false;
        }*/
        $auth = json_decode(base64_decode($auth), true);
        return $auth;
    }

    /**
     * 检查访问权限
     */
    protected function checkaccess()
    {

    }

    public function getprovince2(){
        $data = M('Province')->query("select id, name from province");
        return json_encode($data);
    }

    public function getcity2(){
        $pid = I('post.pid', 0);
        $data = M('Province')->query("SELECT a.id, a.name FROM city a WHERE a.province_id = '".$pid."'");
        return json_encode($data);
    }

    public function getdist2(){
        $pid = I('post.pid', 0);
        $data = M('Province')->query("SELECT a.id, a.name FROM district a WHERE a.city_id = '".$pid."'");
        return json_encode($data);
    }

    public function getcity()
    {
        $pid = I('post.pid', 0);
        $pid = intval($pid);
        if (empty($pid)) {
            return json_encode(['code' => __LINE__, 'msg' => '错误']);
        }
        $data = D('City')->where(['province_id' => $pid])->order('id')->getField('id,name');
        if (empty($data)) {
            return json_encode(['code' => __LINE__, 'msg' => '错误']);
        }
        return json_encode(['code' => 0, 'data' => $data]);
    }

    public function getdistrict()
    {
        $pid = I('post.pid', 0);
        $pid = intval($pid);
        if (empty($pid)) {
            return json_encode(['code' => __LINE__, 'msg' => '错误']);
        }
        $data = D('District')->where(['city_id' => $pid])->order('id')->getField('id,name');
        if (empty($data)) {
            return json_encode(['code' => __LINE__, 'msg' => '错误']);
        }
        return json_encode(['code' => 0, 'data' => $data]);
    }

    /**
     * 验证码
     * todo: 后期短信上验证码 cookie加密存储
     * @return mixed
     */
    public function mobilecode()
    {
        $mobile = I('post.mobile');
        if (empty($mobile)) {
            return json_encode(['code' => __LINE__, 'msg' => '手机号不可为空']);
        }
        if (!preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/', $mobile)) {
            return json_encode(['code' => __LINE__, 'msg' => '请输入正确的手机号码']);
        }
        $uid = D('User')->getFieldByMobile($mobile, 'id');
        if (!empty($uid)) {
            return json_encode(['code' => __LINE__, 'msg' => '手机号码已经被占用']);
        }
        $cookie_code = json_decode(base64_decode(urldecode(cookie('phonecode'))), true);
        if (empty($cookie_code) || ($cookie_code['time'] + 120) < time()) {
            // 发送短信
            $ref = file_get_contents(C('project_host'). ':8086/easyattendance/api?v=1.0&method=common.sendCheckCode&messageFormat=json&phone=' . $mobile . '&appKey=00001');
            $ref = json_decode($ref, true);
            if (empty($ref)) {
                return json_encode(['code' => __LINE__, 'msg' => '网络异常请重试']);
            }
            if ($ref['code'] != 100) {
                return json_encode(['code' => __LINE__, 'msg' => '网络异常请重试']);
            }
            cookie('phonecode',
                urlencode((base64_encode(json_encode(['code' => $ref['solution'], 'time' => time()])))));
            return json_encode(['code' => 0, 'msg' => '验证码已经发送请耐心等待']);
        } else {
            return json_encode(['code' => __LINE__, 'msg' => '操作错误']);
        }
    }

    protected function uuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12)
                . chr(125);// "}"
            return $uuid;
        }
    }

    /**
     * 远程请求
     *
     * @param        $fun   请求的方法
     * @param string $data 请求的数据
     * @param bool $isweb 是否web接口
     *
     * @return mixed
     */
    protected function post_api_data($fun, $data = '', $isweb = true, $timeout = 10)
    {
        $CURL_7_10_5 = 0x070A05;
        $CURL_7_16_2 = 0x071002;
        $curl = curl_version();
        $version = $curl['version_number'];
        $url = C('api_http_url') . '?v=1.0&method=' . $fun . '&messageFormat=json&appKey=00001';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (empty($data)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        } else {
            if (is_array($data)) {
                $data = http_build_query($data, null, '&');
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0) pc web');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($version < $CURL_7_16_2) {
            curl_setopt($ch, CURLOPT_TIMEOUT, ceil($timeout));
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, round($timeout * 1000));
        }
        if ($version < $CURL_7_16_2) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, ceil($timeout));
        } else {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, round($timeout * 1000));
        }
        $tmpInfo = curl_exec($ch);

        if (curl_errno($ch)) {
            // 异常处理

        }
        curl_close($ch);
        return $tmpInfo;
    }

    /*
     * 退出登录
     */
    public function loginout()
    {
        cookie('kqauth', null);
        Cookie::delete('kqauth');
        $this->redirect('login/index');
    }

    /**
     * 数据表导出
     *
     * @param $title  名称
     * @param $tab    表头
     * @param $data   数据
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    protected function ExcleExport($title, $tab, $data)
    {
        // 设置列名最多40个
        $cols = array(
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "AA",
            "AB",
            "AC",
            "AD",
            "AE",
            "AF",
            "AG",
            "AH",
            "AI",
            "AJ",
            "AK",
            "AL",
            "AM",
            "AN",
            "AO",
            "AP",
            "AQ",
            "AR",
            "AS",
            "AT",
            "AU",
            "AV",
            "AW",
            "AX",
            "AY",
            "AZ",
            "BA",
            "BB",
            "BC",
            "BD",
            "BE"
        );
        // 设置行宽
        $col_width = 25;
        vendor("PHPExcel");
        vendor("PHPExcel.IOFactory");
        $excel = new \PHPExcel();
        //设置Excel文件元数据
        $excel->getProperties()->setTitle($title)->setDescription("none");

        //默认列样式
        $style = array(
            'font' => array('bold' => true, 'color' => array('argb' => '00000000')),
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
        );
        //设置列宽度
        foreach ($cols as $col) {
            $excel->getActiveSheet()->getColumnDimension($col)->setWidth($col_width);
        }
        //设置基础表头信息
        $sheet = $excel->setActiveSheetIndex(0);
        foreach ($tab as $k => $v) {
            $sheet->setCellValue($cols[$k] . '1', $v);
        }
        // 设置内容
        foreach ($data as $k => $val) {
            $rows = $k + 2;
            foreach ($val as $i => $v) {
                $sheet->setCellValueExplicit(
                    $cols[$i] . $rows,
                    $v,
                    \PHPExcel_Cell_DataType::TYPE_STRING
                );
                if (is_numeric($v)) {
                    $excel->getActiveSheet()->getStyle($cols[$i] . $rows)->getNumberFormat()->setFormatCode("@");
                }
            }
        }
        $excel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $excel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->setOffice2003Compatibility(true);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * 电子表格导入 固定格式
     */
    protected function ExcleImport($file)
    {
        // 设置列名最多40个
        $cols = array(
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "AA",
            "AB",
            "AC",
            "AD",
            "AE",
            "AF",
            "AG",
            "AH",
            "AI",
            "AJ",
            "AK",
            "AL",
            "AM",
            "AN",
            "AO",
            "AP",
            "AQ",
            "AR",
            "AS",
            "AT",
            "AU",
            "AV",
            "AW",
            "AX",
            "AY",
            "AZ",
            "BA",
            "BB",
            "BC",
            "BD",
            "BE"
        );
        vendor("PHPExcel.Reader.Excel2007");
        vendor("PHPExcel.Reader.Excel5");
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($file)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($file)){
                return [];
            }
        }

        $excel = $PHPReader->load($file);
        $sheet = $excel->getSheet(0);

        $colnums = $sheet->getHighestColumn();
        $rownums = $sheet->getHighestRow();
        if (
            $sheet->getCell("A11")->getValue() != "员工名称" ||
            $sheet->getCell("B11")->getValue() != "职位名称" ||
            $sheet->getCell("C11")->getValue() != "部门名称" ||
            $sheet->getCell("D11")->getValue() != "性别" ||
            $sheet->getCell("E11")->getValue() != "手机号" ||
            $sheet->getCell("F11")->getValue() != "邮箱" ||
            $sheet->getCell("G11")->getValue() != "入职时间"
            )
        {
            return [];
        }
        $metadata = [];
        for($rowIndex = 12; $rowIndex <= $rownums; $rowIndex++){
            $rowDataInfo = array();

            for($colIndex = 0; $colIndex <= array_keys($cols,$colnums)[0]; $colIndex++){
                $cell = $sheet->getCell($cols[$colIndex].$rowIndex)->getValue();
                if($cell instanceof PHPExcel_RichText) {
                    $cell = $cell->__toString();
                }
                $rowDataInfo[] = $cell;
            }
            if (count($rowDataInfo) > 1) {
                /*if(!empty($rowDataInfo[0]) && !empty($rowDataInfo[4])) {
					if(preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/', $rowDataInfo[4])){
						$metadata[]= $rowDataInfo;
					}
                }*/
                $metadata[]= $rowDataInfo;
            }
        }

        return $metadata;
    }

    protected function excelTime($date, $time = false) {
        if(function_exists('GregorianToJD')){
            if (is_numeric( $date )) {
                $jd = GregorianToJD( 1, 1, 1970 );
                $gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );
                $date = explode( '/', $gregorian );
                $date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )
                    ."-". str_pad( $date [0], 2, '0', STR_PAD_LEFT )
                    ."-". str_pad( $date [1], 2, '0', STR_PAD_LEFT )
                    . ($time ? " 00:00:00" : '');
                return $date_str;
            }
        }else{
            $date=$date>25568?$date+1:25569;
            /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
            $ofs=(70 * 365 + 17+2) * 86400;
            $date = date("Y-m-d",($date * 86400) - $ofs).($time ? " 00:00:00" : '');
        }
        return $date;
    }

    //下载文件
    function download_file($file, $showname){
        if(is_file($file)){
            $length = filesize($file);
            $type = explode(".", $file)[1];
            header("Content-Description: File Transfer");
            header('Content-type: ' . $type);
            header('Content-Length:' . $length);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $showname . '"');
            }
            readfile($file);
            exit;
        } else {
            exit('文件已被删除！');
        }
    }

}