<?php


namespace Postfix\Log;

use Postfix\Log\MailLogParser;

class MailLogParserTest extends \PHPUnit_Framework_TestCase
{
    public function testBigLog()
    {
        $parser = new MailLogParser('<201404%s.');
        $parser->setLogFiles(array(__DIR__ . '/mail.info'));
        $all = $parser->search(array());
        $this->assertCount(5018, $all);
        $this->assertEquals(
            array(
                '<20140401235726.1B01A2A1C92@vps305.host223.example.com>' => array(
                    'code' => 'error',
                    'details' => 'Apr  2 01:57:26 vps305 postfix/smtp[16146]: 1B01A2A1C92: to=<rafael.basler@host62.example.com>, relay=mail.host62.example.com[10.0.0.187]:25, delay=0.66, delays=0.01/0/0.33/0.31, dsn=5.0.0, status=bounced (host mail.host62.example.com[10.0.0.187] said: 530 Relaying not allowed (in reply to RCPT TO'
                ),
            ),
            array_filter($all, function($a) {return $a['code'] != MailLogParser::STATUS_OK;})
        );
        $this->assertCount(4, $parser->search(array('02075405')));
        $this->assertCount(0, $parser->search(array('1111111')));
    }
}
