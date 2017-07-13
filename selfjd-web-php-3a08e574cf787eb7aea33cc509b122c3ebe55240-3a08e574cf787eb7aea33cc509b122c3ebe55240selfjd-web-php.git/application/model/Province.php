<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/7
 * Time: 20:45
 */

namespace app\model;


class Province extends Common
{
    protected $fields = ['id', 'name'];
    protected $pk     = 'id';
    protected $tableName='province';
}