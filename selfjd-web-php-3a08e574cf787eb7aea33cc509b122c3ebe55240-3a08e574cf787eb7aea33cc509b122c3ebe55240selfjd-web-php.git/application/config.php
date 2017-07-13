<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'url_route_on' => true,
    'sae_key'=> pack('H*', 'd40e1a08a07fb2e21abe2abc5910f533'),
    'default_module'       =>    'home',
    'default_controller'=>'index',
    'api_http_url'=>'http://www.selfjd.com:8086/easyattendance/api',
    'api_http_url_test'=>'http://192.168.1.113:8080/easyattendance/api',
    'img_http_url'=>'http://www.selfjd.com:8086',
    'page_limit'=>10,
    // 用户类型角色定义
    'user_type'          => [
        0 => 'e456917fd9b03ddbcc89f5c17dc1ad48',
        1 => 'e2e44bdb11bfb3c0da26b2021fab5bd5',
        3 => '41136b217ae7e6f5ef806fe6eda8a6d6',
        4 => '90b0e926623d104f256ef4d2429819a6'
    ],
    'user_type_txt'          => [
        0 => '平台管理员',
        1 => '平台运营人员',
        3 => '仓库管理员',
        4 => '企业管理员'
    ]
    /*'log'          => [
        'type' => 'trace', // 支持 socket trace file
    ],*/
];
