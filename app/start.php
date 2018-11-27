<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/26
 * Time: 14:02
 */
require_once 'lq_liblary/common_function.php';
require_once 'lq_liblary/db.php';
require_once '../config.php';
$request_url = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$request = str_replace($script_name, '', $request_url);
$request = ltrim($request, '/');
$request_array = explode('?', $request);
$request_info = explode('/', $request);
$controller = $request_info[0];
$action = $request_info[1];
require_once "./Controller/" . $controller . ".php";
//反射实例化类
/**
$class=new ReflectionClass($controller);
$instance=$class->newInstanceArgs($args);**/
try{
    $class=new $controller();
    $action=str_replace('.php','',$action);
    $class->$action();
} catch (Exception $e){
    echo '控制器不存在';
}