<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/26
 * Time: 17:11
 */
class Db{
    protected static $instance=null;
    protected  $table_prolx="lq_";
    protected $table_name="";
    protected function __construct($db_info=[])
    {
        if(empty($table_name)){
            $table_name=__CLASS__;
        }
        try{
            $this->connect=new PDO('mysql:host='.$config['host'].';dbname='.$table_prolx);
        } catch (Exception $e){

        }

    }
}