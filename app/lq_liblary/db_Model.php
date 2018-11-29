<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 14:21
 */
$db = db_Model::getInstance('localhost', 'root', 'root', 'class', 'utf8');
class db_Model extends db{
    protected $table_prefix="lw_";
    protected $table_name="test";
    protected function getTable(){
        return ['test'=>self::generateINT("测试数据",5,0,1)];
    }

}