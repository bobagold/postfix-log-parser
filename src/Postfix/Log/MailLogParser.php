<?php


namespace Postfix\Log;

if (!function_exists('ExtractValues')) {
    function ExtractValues($array, $column)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column);
        } else {
            $result = array();
            foreach (array_keys($array) as $x) {
                $result[] = isset($array[$x][$column]) ? $array[$x][$column] : null;
            }
            return $result;
        }
    }
}
/**
 * Class MailLogParser
 * @package Hoga\Mail
 * fetch matched messageIds and their status from postfix logs
 */
class MailLogParser
{
    const STATUS_OK = 'ok';
    const STATUS_WARNING = 'warning';
    const STATUS_PENDING = 'pending';
    const STATUS_ERROR = 'error';
    const STATUS_NOT_SENT = 'not_sent';

    private $pattern;
    private $logFiles = array();
    private $cache;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
        $this->logFiles = new PostfixLogFiles();
    }

    public static function format2regexp($string)
    {
        return str_replace(
            array(
                '%s',
                '%d',
                '%x',
                '%c'
            ),
            array(
                '(\w+)',
                '(\d+)',
                '([0-9a-fA-F]+)',
                '(.)'
            ),
            $string
        );
    }

    public function status($line)
    {
        $columns = explode(': ', $line, 3);
        if (!isset($columns[2])) {
            return self::STATUS_PENDING;
        }
        $info = $columns[2];
        $string = substr(strstr($info, "status="), strlen("status="));
        if (!$string) return self::STATUS_PENDING;
        if (!strncmp($string, 'sent', strlen('sent'))) return self::STATUS_OK;
        if (!strncmp($string, 'deferred', strlen('deferred'))) return self::STATUS_WARNING;
        if (!strncmp($string, 'bounced', strlen('bounced'))) return self::STATUS_ERROR;
        return $string;
    }

    public function detailedStatus($string)
    {
        return array('code' => $this->status($string), 'details' => rtrim($string));
    }

    public static function createPatternFromFormat($string)
    {
        return '/^' . self::format2regexp(preg_quote($string, '/')) . '/';
    }

    public function search(array $params)
    {
        $this->cache();
        if (!$params) return $this->cache;
        $pattern = vsprintf($this->pattern, $params);
        $regexp = self::createPatternFromFormat($pattern);
        $ret = array();
        foreach ($this->cache as $messageId => $status) {
            if (preg_match($regexp, $messageId)) {
                $ret[$messageId] = $status;
            }
        }
        return $ret;
    }

    public function setLogFiles(array $files)
    {
        $this->logFiles = $files;
    }

    protected function cache()
    {
        if (!is_null($this->cache)) return;
        //todo cache in memcache with key = $this->pattern for 1 minute
        //todo further optimizations: background task with pipe
        $regexp = self::createPatternFromFormat($this->pattern);
        $queues = $this->grepMessageIds($regexp);
        $this->cache = array_combine(ExtractValues($queues, 0),
            array_map(array($this, 'detailedStatus'), ExtractValues($queues, 1)));
    }

    protected function grepMessageIds($regexp)
    {
        $queues = array();
        foreach ($this->logFiles as $logFile) {
            if (!is_readable($logFile)) continue;
            $fp = fopen($logFile, 'r');
            if (!$fp) continue;
            while ($line = fgets($fp, 300)) {
                $columns = explode(': ', $line, 3);
                if (!isset($columns[2])) continue;
                $info = $columns[2];
                $queue = $columns[1];
                if (strpos($info, "message-id=") === 0) {
                    $messageId = substr($info, strlen("message-id="), -1);
                    if (!preg_match($regexp, $messageId)) continue;
                    $queues[$queue][0] = $messageId;
                }
                elseif (($pos = strpos($info, "status=")) && isset($queues[$queue])) {
                    $queues[$queue][1] = $line;
                }
            }
            fclose($fp);
        }
        return $queues;
    }
}