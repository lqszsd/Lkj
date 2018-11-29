<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/29
 * Time: 15:40
 */
require_once '../../PHPMailer-master/src/Exception.php';
require_once '../../PHPMailer-master/src/PHPMailer.php';
require_once '../../PHPMailer-master/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class phpEmail{
    public function __construct()
    {
        $this->email=new PHPMailer();
    }
    public function sendEmail(){
        //作用未知 文档上的
        $this->email->SMTPDebug=2;
        //使用SFtp服务器
        $this->email->isSMTP();
        $this->email->Host='';
        $this->email->SMTPAuth=true;
        $this->email->Username='';
        $this->email->Password='';
        $this->email->SMTPSecure='tls';
        $this->email->Port=587;

    }
}