<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-9
 * Time: 下午7:35
 */

namespace Application\Model;


use Zend\Log\Writer\Stream;

class Logger
{
    protected $_logger;

    function __construct()
    {
        $logdir = "log";
        // check if the log dir exists
        if (!file_exists($logdir)) {
            mkdir($logdir, 0777);
        }

        $time = ((new \DateTime())->format("Y-m-d"));
        $stream = fopen($logdir . "/system-".$time.".log", 'a', false);
        $writer = new Stream($stream);
        $this->_logger = new \Zend\Log\Logger();
        $this->_logger->addWriter($writer);
    }

    protected function getMsgString($code , $message){
        return "code: ". $code . " message: ".$message;
    }


    /**
     * @param $priority int
     * @param $code
     * @param $message
     * @return $this Logger
     */
    public function log( $priority, $code , $message){
        $this->_logger->log($priority, $this->getMsgString($code,$message));
        return $this;
    }


    public function emerg($code, $message)
    {
        return $this->log(\Zend\Log\Logger::EMERG, $code, $message);
    }

    public function alert($code, $message)
    {
        return $this->log(\Zend\Log\Logger::ALERT, $code, $message);
    }

    public function crit($code, $message)
    {
        return $this->log(\Zend\Log\Logger::CRIT, $code, $message);
    }

    public function err($code, $message)
    {
        return $this->log(\Zend\Log\Logger::ERR, $code, $message);
    }

    public function warn($code, $message)
    {
        return $this->log(\Zend\Log\Logger::WARN, $code, $message);
    }

    public function notice($code, $message)
    {
        return $this->log(\Zend\Log\Logger::NOTICE, $code, $message);
    }

    public function info($code, $message)
    {
        return $this->log(\Zend\Log\Logger::INFO, $code, $message);
    }

    public function debug($code, $message)
    {
        return $this->log(\Zend\Log\Logger::DEBUG, $code, $message);
    }


}