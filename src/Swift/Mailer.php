<?php

declare(strict_types=1);

namespace TestTools\Swift;

use Swift_Mailer;
use Swift_Mime_Message;
use Swift_IoException;

/**
 * Test double for Swift Mailer
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Mailer extends Swift_Mailer
{
    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param Swift_Mime_Message $message
     * @param array $failedRecipients An array of failures by-reference
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        file_put_contents($this->getTempFilename(), serialize($message));

        $result = parent::send($message, $failedRecipients);

        return $result;
    }

    /**
     * Returns file name for storing last message in temp directory
     *
     * @return string
     */
    protected function getTempFilename()
    {
        $result = sys_get_temp_dir() . '/mail-' . md5(__FILE__) . '.ser';

        return $result;
    }

    /**
     * Returns the last message sent
     *
     * @throws Swift_IoException
     * @return Swift_Mime_Message
     */
    public function getLastMessage()
    {
        $filename = $this->getTempFilename();

        if (file_exists($filename)) {
            $result = unserialize(file_get_contents($filename));
        } else {
            throw new Swift_IoException('No last message found in "' . $filename . '" - did you call send()?');
        }

        return $result;
    }

    /**
     * Deletes the last message from temp directory
     */
    public function clearLastMessage()
    {
        $filename = $this->getTempFilename();

        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}
