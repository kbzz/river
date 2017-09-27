<?php
$to      = 'test@test.com'; //sendmail测试邮件发送到的地址
$subject = 'UPUPW SendMail测试邮件'; 
$message = 'UPUPW提示! 本邮件源于UPUPW SendMail邮件测试，如果您收到这封邮件证明您的SendMail邮件功能已配置成功！'; 
$headers = 'From: UPUPW<webmaster@upupw.net>' . "\r\n" .  //sendmail.ini配置文件中用作服务的邮箱
    'Reply-To: webmaster@upupw.net' . "\r\n" .   //sendmail.ini配置文件中用作服务的邮箱
    'X-Mailer: PHP/' . phpversion();
if (mail($to, $subject, $message, $headers)) {  
echo "success!";
} else {  
echo "fail...";
}
?>