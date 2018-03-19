<?php

require_once('./vendor/autoload.php');

try {
    $sender = new UralMas\ExternalMailer\Client('http://test2.ru/server.php', 'http://test1.ru/');

    $mail = $sender->createMailer();

    //Server settings
    $mail->SMTPDebug = 2;
    $mail->Timeout = 30;
    $mail->CharSet = 'UTF-8';                           	// Enable verbose debug output
    $mail->isSMTP();                                    	// Set mailer to use SMTP
    $mail->Host = 'smtp.mail.ru';  							// Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                             	// Enable SMTP authentication
    $mail->Username = 'user@mail.ru';                   	// SMTP username
    $mail->Password = 'password';                       	// SMTP password
    $mail->SMTPSecure = 'ssl';                          	// Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                  	// TCP port to connect to

    //Recipients
    $mail->setFrom('uralmas1@mail.ru');
    $mail->addAddress('uralmas1@mail.ru');

    //Attachments
    $mail->addAttachment('testfile.txt');         			// Add attachments
    $mail->addAttachment('testfile.txt', 'testfile2.txt');	// Optional name

    //Content
    $mail->isHTML(true);                                  	// Set email format to HTML
    $mail->Subject = 'Test subject';
    $mail->Body    = 'Test mail from UralMas\ExternalMailer';

    $result = $sender->send();

    foreach ($sender->getMessages() as $message) {
        echo $message;
    }

    if ($result['success'] == 0) {
        $resultMessage = 'All mails not delivered';
    } else {
        $resultMessage = $result['success'] . ' message' . ($result['success'] > 1 ? 's' : '') . ' delivered';

        if ($result['fail']) {
            $resultMessage .= ', ' . $result['fail'] . ' message' . ($result['fail'] > 1 ? 's' : '') . ' failed';
        }
    }

    echo $resultMessage;
} catch (\Exception $e) {
    echo $e->getMessage();
}