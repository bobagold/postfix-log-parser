<?php


namespace Postfix\Log;

class PostfixLogFiles implements \IteratorAggregate
{
    private $files = array('/var/log/mail.info', '/var/log/mail.log');

    public function getIterator()
    {
        $this->files[] = $this->getLastArchivedLog();
        return new \ArrayIterator($this->files);
    }

    private function getLastArchivedLog()
    {
        $filePath = '/tmp/mail.info';
        exec('f=`ls -t /var/log/mail.info*|head -n 2 |tail -n 1`; (xzcat "$f" || zcat "$f" || cat "$f") 2> /dev/null > '
            . escapeshellarg($filePath));
        return $filePath;
    }
}