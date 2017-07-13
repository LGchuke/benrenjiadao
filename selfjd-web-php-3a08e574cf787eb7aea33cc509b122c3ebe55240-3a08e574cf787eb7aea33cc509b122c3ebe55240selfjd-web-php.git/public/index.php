<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', dirname(__DIR__) . '/application/');
// 开启调试模式
define('APP_DEBUG', true);
define('APP_MULTI_MODULE',false);
define('UPLOAD_PATCH',dirname(__FILE__).DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR);
define('EXCEL_TEMPLATE_PATCH',dirname(__FILE__).DIRECTORY_SEPARATOR.'excel'.DIRECTORY_SEPARATOR);
// 加载框架引导文件
require dirname(__DIR__) . '/thinkphp/start.php';
