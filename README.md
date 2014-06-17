postfix-log-parser
==================

Parser for log files of Postfix MTA

One-pass parsing of log files with ability to filter only matching message ids.

    $parser = new \Postfix\Log\MailLogParser('<order%d.event%s.'); // mask of interesting messageId

    $parser->search(array()); // find all matching emails and their status

    $parser->search(array(1234, 'created')); // find emails about order 1234 and event 'created' in messageId

    $parser->search(array(1234, '%s')); // find emails about orderId 1234 in messageId

    $parser->setLogFiles(array('/var/log/mail.info.1')); // set specific log files
  
By default, parser looks for /var/log/mail.info and last archieved log file nearby.
  
