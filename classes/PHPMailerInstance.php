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

class PHPMailerInstance extends PHPMailer\PHPMailer
{
    /**
     * The array of attachments.
     *
     * @var array
     */
    public $localAttachments = [];

    /**
     * The text of debug messages.
     *
     * @var string
     */
    static $messages = '';

    /**
     * Constructor.
     */
    public static function getInstance()
    {
        return new self(true);
    }

    /**
     * Add an local attachment
     * Attachment path start from root of the hostname
     *
     * @param string $path        Path to the attachment
     * @param string $name        Overrides the attachment name
     * @param string $encoding    File encoding (see $Encoding)
     * @param string $type        File extension (MIME) type
     * @param string $disposition Disposition to use
     *
     * @return bool
     */
    public function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        if (empty($name)) {
            $name = basename($path);
        }

        $this->localAttachments[] = compact('path', 'name', 'encoding', 'type', 'disposition');

        return true;
    }

    /**
     * Insert local attachments to mainframe
     *
     * @param string $rootLocalFiles
     *
     * @throws Exception
     *
     * @return void
     */
    public function insertAttachments($rootLocalFiles)
    {
        if (empty($this->localAttachments)) {
            return;
        }

        if (empty($rootLocalFiles) && empty($_SERVER['HTTP_HOST'])) {
            throw new Exception('Path of local files root not specified');
        } elseif (empty($rootLocalFiles) && ! empty($_SERVER['HTTP_HOST'])) {
            $rootLocalFiles = "http://{$_SERVER['HTTP_HOST']}/";
        }

        foreach ($this->localAttachments as &$attachment) {
			if (strpos($attachment['path'], 'http://') === false && strpos($attachment['path'], 'https://') === false) {
				$attachment['path'] = $rootLocalFiles . $attachment['path'];
			}

            //Check the file
            if (strpos(@get_headers($attachment['path'])[0], '200 OK') === false) {
                throw new Exception('Could not access file: ' . $attachment['path']);
            }
        }
    }

    /**
     * Log debug message
     *
     * @param string $str   Debug string to output
     * @param int    $level The debug level of this message; see DEBUG_* constants
     *
     * @return void
     */
    public static function logMessage($str, $level = 0)
    {
        //Normalize line breaks
        $str = preg_replace('/\r\n|\r/ms', "\n", $str);

        self::$messages .= gmdate('Y-m-d H:i:s') . "\t";

        //Trim trailing space
        self::$messages .= trim(
            //Indent for readability, except for trailing break
            str_replace(
                "\n",
                "\n                   \t                  ",
                trim($str)
            )
        );

        self::$messages .= "\n";
    }
}