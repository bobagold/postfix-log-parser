<?php


namespace Unit\Postfix\Log;

use Postfix\Log\MailLogParser;

class MailLogParserTest extends \PHPUnit_Framework_TestCase
{
    private $log =<<< EOT
Feb 25 03:46:36 vps305 postfix/pickup[1772]: 2376E65202A: uid=30 from=<project-backup@example.com>
Feb 25 03:46:36 vps305 postfix/cleanup[9466]: 2376E65202A: message-id=<20140225024636.2376E65202A@vps305.example.com>
Feb 25 03:46:36 vps305 postfix/qmgr[1267]: 2376E65202A: from=<project-backup@example.com>, size=33231, nrcpt=2 (queue active)
Feb 25 03:46:37 vps305 postfix/smtp[9469]: 2376E65202A: to=<erwin.portmann@host101.example.com>, relay=barracuda2.host101.example.com[212.59.149.16]:25, delay=1.4, delays=0.07/0/0.44/0.85, dsn=2.0.0, status=sent (250 Ok: queued as 651CA1198044)
Feb 25 03:46:37 vps305 postfix/smtp[9469]: 2376E65202A: to=<peter.warth@host101.example.com>, relay=barracuda2.host101.example.com[212.59.149.16]:25, delay=1.4, delays=0.07/0/0.44/0.85, dsn=2.0.0, status=sent (250 Ok: queued as 651CA1198044)
Feb 25 03:46:37 vps305 postfix/qmgr[1267]: 2376E65202A: removed
Mar 29 03:46:48 vps305 postfix/pickup[768]: 216F22A1CBC: uid=30 from=<project-backup@example.com>
Mar 29 03:46:48 vps305 postfix/cleanup[5438]: 216F22A1CBC: message-id=<20140329024648.216F22A1CBC@vps305.example.com>
Mar 29 03:46:48 vps305 postfix/qmgr[1261]: 216F22A1CBC: from=<project-backup@example.com>, size=49920, nrcpt=1 (queue active)
Mar 29 03:46:50 vps305 postfix/smtp[5505]: 216F22A1CBC: to=<project-backup@example.com>, relay=mailcleaner.example.com[46.4.4.104]:25, delay=1.8, delays=0.01/1.7/0.14/0, dsn=4.0.0, status=deferred (host mailcleaner.example.com[46.4.4.104] refused to talk to me: 421 Too many concurrent SMTP connections from this IP address; please try again later.)
Mar 29 03:51:53 vps305 postfix/qmgr[1261]: 216F22A1CBC: from=<project-backup@example.com>, size=49920, nrcpt=1 (queue active)
Mar 31 09:21:00 vps305 postfix/pickup[18138]: 608FE2A1CB9: uid=30 from=<project-backup@example.com>
Mar 31 09:21:00 vps305 postfix/cleanup[19067]: 608FE2A1CB9: message-id=<20140331072100.608FE2A1CB9@vps305.example.com>
Mar 31 09:21:00 vps305 postfix/qmgr[1271]: 608FE2A1CB9: from=<project-backup@example.com>, size=48331, nrcpt=1 (queue active)
Mar 31 09:21:00 vps305 postfix/pickup[18138]: 628AB2A1CBA: uid=30 from=<project-backup@example.com>
Mar 31 09:21:00 vps305 postfix/cleanup[19067]: 628AB2A1CBA: message-id=<20140331072100.628AB2A1CBA@vps305.example.com>
Mar 31 09:21:00 vps305 postfix/qmgr[1271]: 628AB2A1CBA: from=<project-backup@example.com>, size=48317, nrcpt=1 (queue active)
Mar 31 09:21:00 vps305 postfix/smtp[18250]: 608FE2A1CB9: to=<kueche@host103.example.com>, relay=none, delay=0.01, delays=0.01/0/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=host103.example.com type=AAAA: Host not found)
Mar 31 09:21:00 vps305 postfix/cleanup[19067]: 63ED52A1CBB: message-id=<20140331072100.63ED52A1CBB@vps305.example.com>
Mar 31 09:21:00 vps305 postfix/bounce[19074]: 608FE2A1CB9: sender non-delivery notification: 63ED52A1CBB
Mar 31 09:21:00 vps305 postfix/qmgr[1271]: 63ED52A1CBB: from=<>, size=50342, nrcpt=1 (queue active)
Mar 31 09:21:00 vps305 postfix/qmgr[1271]: 608FE2A1CB9: removed
Mar 31 09:21:38 vps305 postfix/smtp[18149]: 63ED52A1CBB: to=<project-backup@example.com>, relay=mailcleaner.example.com[46.4.4.104]:25, delay=38, delays=0/0/0.03/38, dsn=2.0.0, status=sent (250 OK id=1WUWWi-0000ig-Gu)
Mar 31 09:21:38 vps305 postfix/qmgr[1271]: 63ED52A1CBB: removed
EOT;

    public function testAllMatches()
    {
        $parser = new MailLogParser('<201403');
        $this->setLogText($parser, $this->log);
        $mails = $parser->search(array());
        //todo if several addresses in mail, take into account first of them (currently last)
        $this->assertEquals(array(
            '<20140329024648.216F22A1CBC@vps305.example.com>' => array(
                'code' => MailLogParser::STATUS_WARNING,
                'details' => 'Mar 29 03:46:50 vps305 postfix/smtp[5505]: 216F22A1CBC: to=<project-backup@example.com>, relay=mailcleaner.example.com[46.4.4.104]:25, delay=1.8, delays=0.01/1.7/0.14/0, dsn=4.0.0, status=deferred (host mailcleaner.example.com[46.4.4.104] refused to talk to me: 421 Too many concurrent SMTP connecti',
            ),
            '<20140331072100.608FE2A1CB9@vps305.example.com>' => array(
                'code' => MailLogParser::STATUS_ERROR,
                'details' => 'Mar 31 09:21:00 vps305 postfix/smtp[18250]: 608FE2A1CB9: to=<kueche@host103.example.com>, relay=none, delay=0.01, delays=0.01/0/0.01/0, dsn=5.4.4, status=bounced (Host or domain name not found. Name service error for name=host103.example.com type=AAAA: Host not found)',
            ),
            '<20140331072100.628AB2A1CBA@vps305.example.com>' => array(
                'code' => MailLogParser::STATUS_PENDING,
                'details' => '',
            ),
            '<20140331072100.63ED52A1CBB@vps305.example.com>' => array(
                'code' => MailLogParser::STATUS_OK,
                'details' => 'Mar 31 09:21:38 vps305 postfix/smtp[18149]: 63ED52A1CBB: to=<project-backup@example.com>, relay=mailcleaner.example.com[46.4.4.104]:25, delay=38, delays=0/0/0.03/38, dsn=2.0.0, status=sent (250 OK id=1WUWWi-0000ig-Gu)',
            ),

        ), $mails);
    }
    public function testSearch()
    {
        $parser = new MailLogParser('<201403%d.');
        $this->setLogText($parser, $this->log);
        $mails = $parser->search(array('29024648'));
        $this->assertEquals(array(
            '<20140329024648.216F22A1CBC@vps305.example.com>' => array(
                'code' => MailLogParser::STATUS_WARNING,
                'details' => 'Mar 29 03:46:50 vps305 postfix/smtp[5505]: 216F22A1CBC: to=<project-backup@example.com>, relay=mailcleaner.example.com[46.4.4.104]:25, delay=1.8, delays=0.01/1.7/0.14/0, dsn=4.0.0, status=deferred (host mailcleaner.example.com[46.4.4.104] refused to talk to me: 421 Too many concurrent SMTP connecti',
            ),
        ), $mails);
        $mails = $parser->search(array('1111111'));
        $this->assertEquals(array(), $mails);
    }

    private function setLogText(MailLogParser $parser, $log)
    {
        $filePath = '/tmp/test-mail.log';
        file_put_contents($filePath, $log);
        $parser->setLogFiles(array($filePath));
    }
}
