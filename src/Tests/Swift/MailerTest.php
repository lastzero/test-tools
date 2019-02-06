<?php

namespace TestTools\Tests\Swift;

use TestTools\TestCase\UnitTestCase;
use Swift_Message as Message;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package TestTools
 * @license MIT
 */
class MailerTest extends UnitTestCase
{
    /**
     * @var \TestTools\Swift\Mailer
     */
    protected $mailer;

    public function setUp(): void
    {
        $this->mailer = $this->get('swift.mailer');
    }

    public function testGetLastMessageException()
    {
        $this->expectException('\Swift_IoException');

        $this->mailer->clearLastMessage();
        $this->mailer->getLastMessage();
    }

    public function testSend()
    {
        $message = Message::newInstance();

        $message->setSubject('This is a test mail')
            ->setFrom('sender@example.com')
            ->setTo(array('test@example.com', 'foo@bar.com'))
            ->setBody('Mail Contents');

        $result = $this->mailer->send($message);

        $this->assertEquals(2, $result);

        $lastMessage = $this->mailer->getLastMessage();

        $this->assertEquals('text/plain', $lastMessage->getContentType());
        $this->assertEquals('Mail Contents', $lastMessage->getBody());
        $this->assertEquals('This is a test mail', $lastMessage->getSubject());
        $this->assertEquals(array('sender@example.com' => null), $lastMessage->getFrom());

        $this->mailer->clearLastMessage();
    }
}
