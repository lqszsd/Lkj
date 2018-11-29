<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29
 * Time: 13:11
 */
class phpExcel{
    public function __construct()
    {
        require_once '../../PHPExcel/PHPExcel.php';
        require_once '../../PHPExcel/PHPExcel/Writer/Excel2007.php';
    }
}