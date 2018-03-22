<?php
/**
 * ExternalMailer - PHP email creation and transport class for sending emails via an external address by PHPMailer.
 * PHP Version 5.5.
 *
 * @see       https://github.com/UralMas/ExternalMailer/ The ExternalMailer GitHub project
 *
 * @author    Ivan Chudakov (uralmas)
 * @copyright 2018 Ivan Chudakov
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace UralMas\ExternalMailer;

use ExternalMailer\ExternalMailer\Exception as ExternalMailerException;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Client
{
    /**
     * Array of instances of PHPMailer
     *
     * @var array
     */
    private $mails = [];

    /**
     * URL or IP, which real send mail via PHPMailer
     *
     * @var string
     */
    public $url;

    /**
     * Path of local files root
     *
     * @var string
     */
    public $rootLocalFiles;

    /**
     * Result of sending mails
     *
     * @var array
     */
    private $result;

    /**
     * Error messages from failing sending
     *
     * @var array
     */
    private $errorMessages = [];

    /**
     * Constructor.
     *
     * @param ?string $url URL or IP, which real send mail via PHPMailer
     * @param ?string $rootLocalFiles Path of local files root
     */
    public function __construct($url = null, $rootLocalFiles = null)
    {
        $this->url = $url;
        $this->rootLocalFiles = $rootLocalFiles;
    }

    /**
     * Get instance of PHPMailer
     *
     * @return PHPMailerInstance
     */
    public function createMailer()
    {
        //Create instance of PHPMailer
        $mail = PHPMailerInstance::getInstance();

        $this->mails[] = $mail;

        return $mail;
    }

    /**
     * Sending messages to ExternalMailer Server.
     *
     * @throws ExternalMailerException
     *
     * @return array
     */
    public function send()
    {
        if (empty($this->url)) {
            throw new ExternalMailerException('URL not specified');
        }

        if (empty($this->mails)) {
            throw new ExternalMailerException('No count mail, which prepare to send');
        }

        $this->clearResult();

        /* @var PHPMailerInstance $mail */
        foreach ($this->mails as $key => $mail) {
            $response = $this->request($mail);

            if ($response === false) {
                throw new ExternalMailerException('Server is not available');
            }

            $json = json_decode($response);

            switch ($json->type) {
                case 'error':
                    throw new ExternalMailerException($json->message);
                case 'fail':
                    $this->result['fail']++;
                    $this->errorMessages[] = $json->message;

                    break;
                default:
                    $this->result['success']++;
            }
        }

        return boolval($this->result['success']);
    }

    /**
     * Get all messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->errorMessages;
    }

    /**
     * Get result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Clear data
     *
     * @return void
     */
    public function clear()
    {
        $this->mails = [];
    }

    /**
     * Clear result after previous sending
     *
     * @return void
     */
    private function clearResult()
    {
        $this->result = [
            'fail' => 0,
            'success' => 0
        ];
    }

    /**
     * Request to send mail
     *
     * @param PHPMailerInstance $mail Instance of PHPMailer
     *
     * @throws ExternalMailerException
     *
     * @return string|bool
     */
    private function request(PHPMailerInstance $mail)
    {
        $mail->insertAttachments($this->rootLocalFiles);

        $postData = [
            'mailer' => serialize($mail)
        ];

        //Send mail
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
