<?php

require_once('./vendor/autoload.php');

try {
    if (! isset($_POST['mailer'])) {
        throw new Exception('Not data are send');
    }

    $mailerData = (string) $_POST['mailer'];

    $mailer = new \UralMas\ExternalMailer\Server($mailerData);

    echo $mailer->getResultSendMail();
} catch (Exception $e) {
    echo $e->getMessage();
}