<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/10
 * Time: 9:47
 */

namespace app\model;


class Role extends Common
{
    protected $fields = ['id', 'name', 'remark'];
    protected $pk     = 'id';
    protected $tableName='sys_role';
}