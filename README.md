# ExternalMailer - Обёртка для PHPMailer, позволяющая отправлять письма через внешний сервер

Это может полезно в следующих случаях:
- На хостинге по причине безопасности отключена отправка писем через стандартные возможности
- Сайт, с которого необходимо отправлять письма, заражён вирусами, которые занимаются рассылкой спама, и хостер отключил возможность отправки писем
- У разных сайтов есть единый шлюз рассылки писем

## Installation & loading
PHPMailer is available on [Packagist](https://packagist.org/packages/uralmas/external-mailer), and installation via [Composer](https://getcomposer.org) is the recommended way to install ExternalMailer. Just add this line to your `composer.json` file:

```json
"uralmas/external-mailer": "~0.1.0"
```

or run

```sh
composer require uralmas/external-mailer
```

## Использование

Библиотека состоит из 2-х частей - клиента и сервера.
Клиент размещается на сайт, с которого необходимо отправить письма.
Сервер - на том сайте / IP, который служит шлюзом отправки.

### Client

У конструктора клиента 2 аргумента:
- Первый - это адрес, по которому находится скрипт, инициализирующий серверную часть
- Второй - адрес пути на сайте, на котором размещён клиент, с которого начинается адрес до прикладываемых локальных файлов (необязательный)
Т.е. надо в функции addAttachment() в пути к файлу указывать адрес файла, доступный из интернета (если указан root адреса файлов, то его можно опустить)

Debugoutput указывать не надо - он будет заменён на внутренний. Для вывода сообщений дебага использовать Client->getMessages().

```php
<?php

require_once('./vendor/autoload.php');

try {
	//First argument - the address on which the script is located sending mail
	//Sencond - root of filepath
    $sender = new UralMas\ExternalMailer\Client('http://server.ru/', 'http://client.ru/');

    $mail = $sender->createMailer();

    //Server settings										// Enable verbose debug output
    $mail->isSMTP();                                    	// Set mailer to use SMTP
    $mail->Host = 'smtp.mail.ru';  							// Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                             	// Enable SMTP authentication
    $mail->Username = 'user@mail.ru';                   	// SMTP username
    $mail->Password = 'password';                       	// SMTP password
    $mail->SMTPSecure = 'ssl';                          	// Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                  	// TCP port to connect to

    /* Other parameters and functions to configuration instanse of PHPMailer */
	
	//Send mails to Server part
    $result = $sender->send();

    foreach ($sender->getMessages() as $message) {
        echo $message;
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

### Server

```php
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
```

## License
This software is distributed under the [LGPL 2.1](http://www.gnu.org/licenses/lgpl-2.1.html) license. Please read LICENSE for information on the software availability and distribution.
