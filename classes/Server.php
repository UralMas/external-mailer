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

use PHPMailer\PHPMailer;

class Server
{
    /**
     * Instance of PHPMailerInstance
     *
     * @var PHPMailerInstance
     */
    private $mailer;

    /**
     * Constructor.
     *
     * @param string $mailerData Serialized PHPMailerInstance data
     */
    public function __construct($mailerData)
    {
        $this->mailer = @unserialize($mailerData);
    }

    /**
     * Send the mail
     *
     * @return string
     */
    public function getResultSendMail()
    {
        if ($this->mailer === false || ! $this->mailer instanceof PHPMailerInstance) {
            $result = [
                'type' => 'error',
                'message' => 'Send data is not instance of PHPMailerInstance'
            ];
        } else {
            $result = [];

            $mailer = $this->mailer;

            try {
                $mailer->Debugoutput = [
                    __NAMESPACE__ . '\PHPMailerInstance',
                    'logMessage'
                ];

                //Insert files
                if (! empty($mailer->localAttachments)) {
                    foreach ($mailer->localAttachments as $attachment) {
                        $mailer->addStringAttachment(
                            (string) file_get_contents($attachment['path']),
                            $attachment['name'],
                            $attachment['encoding'],
                            $attachment['type'],
                            $attachment['disposition']
                        );
                    }
                }

                $mailer->send();
				
                $result['type'] = 'success';
            } catch (PHPMailer\Exception $e) {
                $result['type'] = 'error';
            }

            $result['message'] = $mailer::$messages;
        }

        return json_encode($result);
    }
}