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
class phpEmail
{
    public function __construct()
    {
        $this->email = new PHPMailer();
        //作用未知 文档上的
        $this->email->SMTPDebug = 2;
        //使用SFtp服务器
        $this->email->isSMTP();
        $this->email->Host = '';
        $this->email->SMTPAuth = true;
        $this->email->Username = '';
        $this->email->Password = '';
        $this->email->SMTPSecure = 'tls';
        $this->email->Port = 587;
    }

    public function sendEmail()
    {
        $this->email->setFrom('from@example.com', 'Mailer');
        // Add a recipient
        $this->email->addAddress('ellen@example.com');               // Name is optional
        $this->email->addReplyTo('info@example.com', 'Information');
        $this->email->addCC('cc@example.com');
        $this->email->addBCC('bcc@example.com');
        $this->email->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        $this->email->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $this->email->isHTML(true);                                  // Set email format to HTML
        $this->email->Subject = 'Here is the subject';
        $this->email->Body = 'This is the HTML message body <b>in bold!</b>';
        $this->email->AltBody = 'This is the body in plain text for non-HTML mail clients';
        $this->email->send();
        echo 'Message has been sent';
    }
}