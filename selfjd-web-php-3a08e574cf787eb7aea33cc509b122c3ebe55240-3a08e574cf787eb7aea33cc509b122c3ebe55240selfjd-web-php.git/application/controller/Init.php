<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/4
 * Time: 22:04
 */

namespace app\controller;


use org\Upload;

class Init extends Base
{

    protected $userinfo = '';

    public function _initialize()
    {
        $login = parent::checklogin();
        if (empty($login)) {
            $this->redirect('login/index');
        }

        $this->userinfo = D('User')->getById($login['amuid']);
    }

    /**
     * 根据sys_user_id获取公司的ID
     */
    public function getEnterpriseIdByUserId()
    {
        $userId = session("userId");
        //return "1";//测试用
        if (!empty($userId)) {
            $entId = M('Enterprise')->getFieldBySysUserId($userId, 'id');
            return $entId;
        }
    }

    /**
     * 异步上传
     * 图片上传
     */
    public function uploadimg()
    {
        // 文件名称
        $id = md5(parent::uuid());
        $upload = new Upload(['maxSize' => 3145728,'autoSub'=>false,'callcack'=>false ,'exts' => ['jpg', 'gif', 'png', 'jpeg'], 'saveName' => $id, 'rootPath'=>UPLOAD_PATCH],
            'Local');
        $info = $upload->uploadOne($_FILES['file']);
        if (empty($info)) {
            return json_encode(['code'=>__LINE__,'msg'=>$upload->getError()]);
        } else {
            // 上传到接口上面
            $idk = I('post.id/s',1);
            $json=parent::post_api_data('common.uploadFile',['file'=>(substr(strrchr($info['savename'], '.'), 1).'@'.base64_encode(file_get_contents(UPLOAD_PATCH.$info['savename']))),'id'=>$idk]);
            $json = json_decode($json,true);
            unlink(UPLOAD_PATCH.$info['savename']);
            if($json['code']==100){
                return json_encode(['code'=>0,'msg'=>$json['result']['path']]);
            }else{
                return json_encode(['code'=>__LINE__,'msg'=>$json['solution']]);
            }
        }
    }
}